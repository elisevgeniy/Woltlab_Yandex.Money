
set home_dir=%cd%

DEL /F /S /Q /A "%home_dir%\files.tar"
DEL /F /S /Q /A "%home_dir%\ga.kusok-piro.paymant.yandexmoney.tar"

#cd %home_dir%

"C:\Program Files (x86)\IZArc\IZARCC.exe" -a -r -P %home_dir%\files.tar ^
                                                    lib\action\YandexmoneyCallbackAction.class.php ^
                                                    lib\system\payment\method\YandexmoneyPaymentMethod.class.php

cd %home_dir%

"C:\Program Files (x86)\IZArc\IZARCC.exe" -a -P %home_dir%\ga.kusok-piro.paymant.yandexmoney.tar ^
                                                *.xml ^
                                                files.tar ^
                                                language\*.xml

DEL /F /S /Q /A "%home_dir%\files.tar"