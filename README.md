# WordPress Pay - Test Suite

## Upgrade to PHP 5.6 on Ubuntu 14.04 LTS

https://joshtronic.com/2014/08/31/upgrade-to-php-56-on-ubuntu-1404-lts/
https://www.digitalocean.com/community/tutorials/how-to-upgrade-to-php-7-on-ubuntu-14-04
http://stackoverflow.com/questions/3945420/how-can-i-make-phpunit-selenium-run-faster
https://github.com/giorgiosironi/phpunit-selenium
http://codeception.com/11-12-2013/working-with-phpunit-and-selenium-webdriver.html#.VtgZZJPhDAw
http://www.sitepoint.com/using-the-selenium-web-driver-api-with-phpunit/

which Xvfb
which avconv

## Structure

/tests/woocommerce/2.5.3/mollie/
/tests/wp-e-commerce/3.11.2/mollie/

## CLI

`ln -s ~/Workspace/test-suite/wordpress ~/Websites/test.dev`
`vendor/bin/phpunit tests/FormidableTest.php --verbose --debug`
`vendor/bin/phpunit tests/GiveTest.php --verbose --debug`
`vendor/bin/phpunit tests/GravityFormsTest.php --verbose --debug`
`vendor/bin/phpunit tests/WordPressTest.php --verbose --debug`
`vendor/bin/phpunit tests/WooCommerceTest.php --verbose --debug`
`vendor/bin/phpunit tests/WPeCommerceTest.php --verbose --debug`

## Test

```bash
POST_ID=`wp post create --post_type=pronamic_gateway --post_title='ABN AMRO - iDEAL Easy - Test' --post_status=publish --porcelain`
wp post meta update $POST_ID '_pronamic_gateway_id' 'abnamro-ideal-easy'
wp post meta update $POST_ID '_pronamic_gateway_mode' 'test'
wp post meta update $POST_ID '_pronamic_gateway_ogone_psp_id' 'TESTiDEALEASY'

POST_ID=`wp post create --post_type=pronamic_gateway --post_title='Buckaroo - Test' --post_status=publish --porcelain`
wp post meta update $POST_ID '_pronamic_gateway_id' 'buckaroo'
wp post meta update $POST_ID '_pronamic_gateway_mode' 'test'
wp post meta update $POST_ID '_pronamic_gateway_buckaroo_website_key' $BUCKAROO_WEBSITE_KEY
wp post meta update $POST_ID '_pronamic_gateway_buckaroo_secret_key' $BUCKAROO_SECRET_KEY

POST_ID=`wp post create --post_type=pronamic_gateway --post_title='ICEPAY - Test' --post_status=publish --porcelain`
wp post meta update $POST_ID '_pronamic_gateway_id' 'icepay-ideal'
wp post meta update $POST_ID '_pronamic_gateway_mode' 'test'
wp post meta update $POST_ID '_pronamic_gateway_icepay_merchant_id' $ICEPAY_MERCHANT_ID
wp post meta update $POST_ID '_pronamic_gateway_icepay_secret_code' $ICEPAY_SECRET_CODE

POST_ID=`wp post create --post_type=pronamic_gateway --post_title='iDEAL Simulator - iDEAL Lite / Basic - test' --post_status=publish --porcelain`
wp post meta update $POST_ID '_pronamic_gateway_id' 'ideal-simulator-ideal-basic'
wp post meta update $POST_ID '_pronamic_gateway_mode' 'test'
wp post meta update $POST_ID '_pronamic_gateway_ideal_merchant_id' '123456789'
wp post meta update $POST_ID '_pronamic_gateway_ideal_sub_id' '0'
wp post meta update $POST_ID '_pronamic_gateway_ideal_hash_key' 'Password'

POST_ID=`wp post create --post_type=pronamic_gateway --post_title='ING Kassa Compleet - test' --post_status=publish --porcelain`
wp post meta update $POST_ID '_pronamic_gateway_id' 'ing-kassa-compleet'
wp post meta update $POST_ID '_pronamic_gateway_mode' 'test'
wp post meta update $POST_ID '_pronamic_gateway_ing_kassa_compleet_api_key' $ING_KASSA_COMPLEET_API_KEY

POST_ID=`wp post create --post_type=pronamic_gateway --post_title='Mollie - test' --post_status=publish --porcelain`
wp post meta update $POST_ID '_pronamic_gateway_id' 'mollie'
wp post meta update $POST_ID '_pronamic_gateway_mode' 'test'
wp post meta update $POST_ID '_pronamic_gateway_mollie_api_key' $MOLLIE_API_KEY

POST_ID=`wp post create --post_type=pronamic_gateway --post_title='MultiSafepay - test' --post_status=publish --porcelain`
wp post meta update $POST_ID '_pronamic_gateway_id' 'multisafepay-connect'
wp post meta update $POST_ID '_pronamic_gateway_mode' 'test'
wp post meta update $POST_ID '_pronamic_gateway_multisafepay_account_id' $MULTISAFEPAY_ACCOUNT_ID
wp post meta update $POST_ID '_pronamic_gateway_multisafepay_site_id' $MULTISAFEPAY_SITE_ID
wp post meta update $POST_ID '_pronamic_gateway_multisafepay_site_code' $MULTISAFEPAY_SITE_CODE

POST_ID=`wp post create --post_type=pronamic_gateway --post_title='Ingenico/Ogone - Test' --post_status=publish --porcelain`
wp post meta update $POST_ID '_pronamic_gateway_id' 'ogone-orderstandard'
wp post meta update $POST_ID '_pronamic_gateway_mode' 'test'
wp post meta update $POST_ID '_pronamic_gateway_ogone_psp_id' $OGONE_PSP_ID
wp post meta update $POST_ID '_pronamic_gateway_ogone_hash_algorithm' $OGONE_HASH_ALGORITHM
wp post meta update $POST_ID '_pronamic_gateway_ogone_sha_in_pass_phrase' $OGONE_SHA_IN_PASS_PHRASE
wp post meta update $POST_ID '_pronamic_gateway_ogone_sha_out_pass_phrase' $OGONE_SHA_OUT_PASS_PHRASE

POST_ID=`wp post create --post_type=pronamic_gateway --post_title='OmniKassa - Test' --post_status=publish --porcelain`
wp post meta update $POST_ID '_pronamic_gateway_id' 'rabobank-omnikassa'
wp post meta update $POST_ID '_pronamic_gateway_mode' 'test'
wp post meta update $POST_ID '_pronamic_gateway_omnikassa_merchant_id' '002020000000001'
wp post meta update $POST_ID '_pronamic_gateway_omnikassa_secret_key' '002020000000001_KEY1'
wp post meta update $POST_ID '_pronamic_gateway_omnikassa_key_version' '1'

POST_ID=`wp post create --post_type=pronamic_gateway --post_title='Pay.nl - Test' --post_status=publish --porcelain`
wp post meta update $POST_ID '_pronamic_gateway_id' 'pay_nl'
wp post meta update $POST_ID '_pronamic_gateway_mode' 'test'
wp post meta update $POST_ID '_pronamic_gateway_pay_nl_token' $PAY_NL_TOKEN
wp post meta update $POST_ID '_pronamic_gateway_pay_nl_service_id' $PAY_NL_SERVICE_ID

POST_ID=`wp post create --post_type=pronamic_gateway --post_title='Qantani - Test' --post_status=publish --porcelain`
wp post meta update $POST_ID '_pronamic_gateway_id' 'qantani'
wp post meta update $POST_ID '_pronamic_gateway_mode' 'test'
wp post meta update $POST_ID '_pronamic_gateway_qantani_merchant_id' $QANTANI_MERCHANT_ID
wp post meta update $POST_ID '_pronamic_gateway_qantani_merchant_key' $QANTANI_MERCHANT_KEY
wp post meta update $POST_ID '_pronamic_gateway_qantani_merchant_secret' $QANTANI_MERCHANT_SECRET

POST_ID=`wp post create --post_type=pronamic_gateway --post_title='Sisow - Test' --post_status=publish --porcelain`
wp post meta update $POST_ID '_pronamic_gateway_id' 'sisow-ideal'
wp post meta update $POST_ID '_pronamic_gateway_mode' 'test'
wp post meta update $POST_ID '_pronamic_gateway_sisow_merchant_id' $SISOW_MERCHANT_ID
wp post meta update $POST_ID '_pronamic_gateway_sisow_merchant_key' $SISOW_MERCHANT_KEY
wp post meta update $POST_ID '_pronamic_gateway_sisow_shop_id' $SISOW_SHOP_ID

POST_ID=`wp post create --post_type=pronamic_gateway --post_title='TargetPay - Test' --post_status=publish --porcelain`
wp post meta update $POST_ID '_pronamic_gateway_id' 'targetpay-ideal'
wp post meta update $POST_ID '_pronamic_gateway_mode' 'test'
wp post meta update $POST_ID '_pronamic_gateway_targetpay_layoutcode' $TARGETPAY_LAYOUTCODE
```

## Links

*	https://xtreamwayz.com/blog/2014-11-04-phpunit-selenium-2
*	http://codeception.com/11-12-2013/working-with-phpunit-and-selenium-webdriver.html
*	https://gist.github.com/luxcem/8240758

*	http://pietervogelaar.nl/ubuntu-14-04-install-selenium-as-service-headless
