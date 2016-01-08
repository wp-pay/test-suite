# WordPress Pay - Test Suite

# Test

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

POST_ID=`wp post create --post_type=pronamic_gateway --post_title='MultiSafepay - test' --post_status=publish --porcelain`
wp post meta update $POST_ID '_pronamic_gateway_id' 'multisafepay-connect'
wp post meta update $POST_ID '_pronamic_gateway_mode' 'test'
wp post meta update $POST_ID '_pronamic_gateway_multisafepay_account_id' $MULTISAFEPAY_ACCOUNT_ID
wp post meta update $POST_ID '_pronamic_gateway_multisafepay_site_id' $MULTISAFEPAY_SITE_ID
wp post meta update $POST_ID '_pronamic_gateway_multisafepay_site_code' $MULTISAFEPAY_SITE_CODE

POST_ID=`wp post create --post_type=pronamic_gateway --post_title='OmniKassa - Test' --post_status=publish --porcelain`
wp post meta update $POST_ID '_pronamic_gateway_id' 'rabobank-omnikassa'
wp post meta update $POST_ID '_pronamic_gateway_mode' 'test'
wp post meta update $POST_ID '_pronamic_gateway_omnikassa_merchant_id' '002020000000001'
wp post meta update $POST_ID '_pronamic_gateway_omnikassa_secret_key' '002020000000001_KEY1'
wp post meta update $POST_ID '_pronamic_gateway_omnikassa_key_version' '1'

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
