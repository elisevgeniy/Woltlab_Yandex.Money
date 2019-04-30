<?php

namespace wcf\system\payment\method;

use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * IPaymentMethod implementation for YandexMoney.
 * 
 * @author	**
 * @copyright	2017-20** **
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	***
 * @subpackage	system.payment.method
 * @category	***
 */
class YandexmoneyPaymentMethod extends AbstractPaymentMethod {

    /**
     * @see	\wcf\system\payment\method\IPaymentMethod::supportsRecurringPayments()
     */
    public function supportsRecurringPayments() {
        return false;
    }

    /**
     * @see	\wcf\system\payment\method\IPaymentMethod::getSupportedCurrencies()
     */
    public function getSupportedCurrencies() {
        return array(
            'RUB', // Russian Ruble
        );
    }

    /**
     * @see	\wcf\system\payment\method\IPaymentMethod::getPurchaseButton()
     */
    public function getPurchaseButton($cost, $currency, $name, $token, $returnURL, $cancelReturnURL, $isRecurring = false, $subscriptionLength = 0, $subscriptionLengthUnit = '') {

        // Создаём доп. параметр
        $label = [
            'c' => $currency,
            't' => $token,
        ];

        // Делвем из него строку
        $label = StringUtil::encodeHTML(serialize($label));

        if (!$isRecurring) {
            // subscribe button
            $button_code = '';

            if (YM_SECRET != '' && YM_WALLET != '') {

                $cost_ym = round($cost + $cost * (YM_COMMISSION_YM/100 / (1 + YM_COMMISSION_YM/100) ),2);
                $cost_card = round($cost / (1 - YM_COMMISSION_BANK_CARD/100),2);

                $button_code = '<form data-bem="{&quot;form&quot;:{}}" method="POST" '
                    . 'target="_blank" action="https://money.yandex.ru/quickpay/confirm.xml">'
                    . '<input class="native-input native-input_type_hidden" name="quickpay-form" value="small" type="hidden">'
                    . '<input class="native-input native-input_type_hidden" name="is-inner-form" value="true" type="hidden">'
                    . '<input class="native-input native-input_type_hidden" name="paymentType" value="PC" type="hidden">'
                    . '<input class="native-input native-input_type_hidden" name="receiver" value="' . StringUtil::encodeHTML(YM_WALLET) . '" type="hidden">'
                    . '<input class="native-input native-input_type_hidden" name="targets" value="' . StringUtil::encodeHTML($name) . '" type="hidden">'
                    . '<input class="native-input native-input_type_hidden" name="sum" value="' . $cost_ym . '" type="hidden">'
                    . '<input class="native-input native-input_type_hidden" name="successURL" value="' . $returnURL . '" type="hidden">'
                    . '<input class="native-input native-input_type_hidden" name="referer" value="' . StringUtil::encodeHTML(LinkHandler::getInstance()->getLink('PaidSubscriptionList', array('appendSession' => false))) . '" type="hidden">'
                    . '<input class="native-input native-input_type_hidden" name="need-email" value="true" type="hidden">'
                    . '<input class="native-input native-input_type_hidden" name="label" value="' . $label . '" type="hidden">'
                    . '<div class="widget-small__button">'
                    . '<button class="small" data-bem="{&quot;button2&quot;:{&quot;_tabindex&quot;:&quot;0&quot;}}" type="submit" autocomplete="off" tabindex="0">'
                    . '<span class="button2__text">' . WCF::getLanguage()->get('wcf.payment.yandexmoney.button.subscribe.yandexmoney') . ' (' . $cost_ym . 'р.)</span>'
                    . '</button>'
                    . '</div>'
                    . '</form>'
                    . '<form data-bem="{&quot;form&quot;:{}}" method="POST" '
                    . 'target="_blank" action="https://money.yandex.ru/quickpay/confirm.xml">'
                    . '<input class="native-input native-input_type_hidden" name="quickpay-form" value="small" type="hidden">'
                    . '<input class="native-input native-input_type_hidden" name="is-inner-form" value="true" type="hidden">'
                    . '<input class="native-input native-input_type_hidden" name="paymentType" value="AC" type="hidden">'
                    . '<input class="native-input native-input_type_hidden" name="receiver" value="' . StringUtil::encodeHTML(YM_WALLET) . '" type="hidden">'
                    . '<input class="native-input native-input_type_hidden" name="targets" value="' . StringUtil::encodeHTML($name) . '" type="hidden">'
                    . '<input class="native-input native-input_type_hidden" name="sum" value="' . $cost_card . '" type="hidden">'
                    . '<input class="native-input native-input_type_hidden" name="successURL" value="' . $returnURL . '" type="hidden">'
                    . '<input class="native-input native-input_type_hidden" name="referer" value="' . StringUtil::encodeHTML(LinkHandler::getInstance()->getLink('PaidSubscriptionList', array('appendSession' => false))) . '" type="hidden">'
                    . '<input class="native-input native-input_type_hidden" name="need-email" value="true" type="hidden">'
                    . '<input class="native-input native-input_type_hidden" name="label" value="' . $label . '" type="hidden">'
                    . '<div class="widget-small__button">'
                    . '<button class="small" data-bem="{&quot;button2&quot;:{&quot;_tabindex&quot;:&quot;0&quot;}}" type="submit" autocomplete="off" tabindex="0">'
                    . '<span class="button2__text">' . WCF::getLanguage()->get('wcf.payment.yandexmoney.button.subscribe.card') . ' ('. $cost_card . 'р.)</span>'
                    . '</button>'
                    . '</div>'
                    . '</form>'
                    . '<form data-bem="{&quot;form&quot;:{}}" method="POST" '
                    . 'target="_blank" action="https://money.yandex.ru/quickpay/confirm.xml">'
                    . '<input class="native-input native-input_type_hidden" name="quickpay-form" value="small" type="hidden">'
                    . '<input class="native-input native-input_type_hidden" name="is-inner-form" value="true" type="hidden">'
                    . '<input class="native-input native-input_type_hidden" name="paymentType" value="MC" type="hidden">'
                    . '<input class="native-input native-input_type_hidden" name="receiver" value="' . StringUtil::encodeHTML(YM_WALLET) . '" type="hidden">'
                    . '<input class="native-input native-input_type_hidden" name="targets" value="' . StringUtil::encodeHTML($name) . '" type="hidden">'
                    . '<input class="native-input native-input_type_hidden" name="sum" value="' . $cost . '" type="hidden">'
                    . '<input class="native-input native-input_type_hidden" name="successURL" value="' . $returnURL . '" type="hidden">'
                    . '<input class="native-input native-input_type_hidden" name="referer" value="' . StringUtil::encodeHTML(LinkHandler::getInstance()->getLink('PaidSubscriptionList', array('appendSession' => false))) . '" type="hidden">'
                    . '<input class="native-input native-input_type_hidden" name="need-email" value="true" type="hidden">'
                    . '<input class="native-input native-input_type_hidden" name="label" value="' . $label . '" type="hidden">'
                    . '<div class="widget-small__button">'
                    . '<button class="small" data-bem="{&quot;button2&quot;:{&quot;_tabindex&quot;:&quot;0&quot;}}" type="submit" autocomplete="off" tabindex="0">'
                    . '<span class="button2__text">' . WCF::getLanguage()->get('wcf.payment.yandexmoney.button.subscribe.mobile') . '</span>'
                    . '</button>'
                    . '</div>'
                    . '</form>';
            }

            return $button_code;
                    
        } else {
            throw new Exception('Recurring payments are not support by Yandex Money Payment system');
        }
    }

}
