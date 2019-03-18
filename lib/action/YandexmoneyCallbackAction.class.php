<?php

namespace wcf\action;

use wcf\action\AbstractAction;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\exception\SystemException;
use wcf\system\payment\type\IPaymentType;
use wcf\util\HTTPRequest;
use wcf\util\StringUtil;
use wcf\util\PasswordUtil;

/**
 * Handles YandexMoney callbacks.
 *
 * @author	**
 * @copyright	2017-20** **
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	***
 * @subpackage	system.payment.method
 * @category	***
 */
class YandexmoneyCallbackAction extends AbstractAction {

    /**
     * @see	\wcf\action\IAction::execute()
     */
    public function execute() {
        parent::execute();

        $secret = YM_SECRET; // секрет, который мы получили в первом шаге от яндекс.
        // check response
        $processor = null;
        try {
                // получение данных.
                $r = array(
                    'notification_type' => $_POST['notification_type'], // p2p-incoming / card-incoming - с кошелька / с карты
                    'operation_id' => $_POST['operation_id'], // Идентификатор операции в истории счета получателя.
                    'amount' => $_POST['amount'], // Сумма, которая зачислена на счет получателя.
                    'withdraw_amount' => $_POST['withdraw_amount'], // Сумма, которая списана со счета отправителя.
                    'currency' => $_POST['currency'], // Код валюты — всегда 643 (рубль РФ согласно ISO 4217).
                    'datetime' => $_POST['datetime'], // Дата и время совершения перевода.
                    'sender' => $_POST['sender'], // Для переводов из кошелька — номер счета отправителя. Для переводов с произвольной карты — параметр содержит пустую строку.
                    'codepro' => $_POST['codepro'], // Для переводов из кошелька — перевод защищен кодом протекции. Для переводов с произвольной карты — всегда false.
                    'label' => $_POST['label'], // Метка платежа. Если ее нет, параметр содержит пустую строку.
                    'sha1_hash' => $_POST['sha1_hash']                  // SHA-1 hash параметров уведомления.
                );

// проверка хеш
                if (!PasswordUtil::secureCompare(sha1($r['notification_type'] . '&' .
                    $r['operation_id'] . '&' .
                    $r['amount'] . '&' .
                    $r['currency'] . '&' .
                    $r['datetime'] . '&' .
                    $r['sender'] . '&' .
                    $r['codepro'] . '&' .
                    $secret . '&' .
                    $r['label']),$r['sha1_hash'])) {
                    throw new SystemException('Верификация не пройдена. SHA1_HASH не совпадает.');
                }

                // обработаем данные. нас интересует основной параметр label и withdraw_amount для получения денег без комиссии для пользователя.
// либо если вы хотите обременить пользователя комиссией - amount, но при этом надо учесть, что яндекс на странице платежа будет писать "без комиссии".
                $r['amount'] = floatval($r['amount']);
                $r['withdraw_amount'] = floatval($r['withdraw_amount']);

                if ($r['label'] === '') {
                    throw new SystemException('Нет доп. поля Label');
                }

                try {
                    // Разбираем переданные нами параметры
                    $r['label'] = unserialize($r['label']);
                } catch (Exception $e) {
                    throw new SystemException('Userialaze error', $e->getMessage());
                }

                // вытягиваем токен
                $token = $r['label']['t'];

                $tokenParts = explode(':', $token, 2);
                if (count($tokenParts) != 2) {
                    throw new SystemException('invalid custom item');
                }
                // get payment type object type
                $objectType = ObjectTypeCache::getInstance()->getObjectType(intval($tokenParts[0]));
                if ($objectType === null || !($objectType->getProcessor() instanceof IPaymentType)) {
                    throw new SystemException('invalid payment type id');
                }
                $processor = $objectType->getProcessor();

                // get status
                $status = 'completed';

                // Заносим платёж в БД
                $processor->processTransaction(
                    ObjectTypeCache::getInstance()->getObjectTypeIDByName(
                        'com.woltlab.wcf.payment.method', 'ga.kusok-piro.paymant.method.yandexmoney'
                    ),
                    $tokenParts[1], // Токен
                    $r['amount'], // Amount
                    $r['label']['c'], // Currency
                    $r['operation_id'], // Id операции
                    $status,
                    $r
                );

        } catch (SystemException $e) {
            @header('HTTP/1.1 500 Internal Server Error');
            echo $e->getMessage();
            exit;
        }
    }

}
