<?php
/*
 * This file is part of the Level 7 Systems Ltd. platform.
 *
 * (c) Kamil Adryjanek <kamil@level7systems.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// get two letter code for the current language
function l7p_inline_culture()
{
    return 'l7p_get_';
}

// get three letter currency code
function l7p_inline_currency()
{
    return 'l7p_get_currency()';
}

// get two letter country code for the current user.
function l7p_inline_get_geo()
{
    return 'l7p_get_geo()';
}

// get country name for the current user.
function l7p_inline_country_name()
{
    return 'l7p_country_name(l7p_get_geo())';
}

// get country name for the current user.
function l7p_inline_country_code()
{
    return 'l7p_get_geo()';
}

// get two letter state code for the current user.
function l7p_inline_state()
{
    return 'l7p_get_geo_state()';
}

// displays charge for a SMS message
function l7p_inline_sms_charge()
{
    return 'l7p_currency_symbol(l7p_inline_charge(\'sms\'))';
}

// displays monthly charge for a User account
function l7p_inline_user_charge()
{
    return 'l7p_currency_symbol(l7p_inline_charge(\'user\'))';
}

// displays monthly charge for a Unlimited plan
function l7p_inline_user_unlimited()
{
    return 'l7p_currency_symbol(l7p_inline_charge(\'user-S\'))';
}

// displays monthly charge for a Unlimited International plan
function l7p_inline_user_unlimited_int()
{
    return 'l7p_currency_symbol(l7p_inline_charge(\'user-A\'))';
}

function l7p_inline_charge($service)
{
    if (!$currency = l7p_get_session('currency')) {
        $currency = l7p_get_currency();
    }

    $currency = strtoupper($currency);
    $charges = l7p_get_settings('charges', array());

    return isset($charges[$currency][$service]) ? $charges[$currency][$service] : 0;
}

// Termination
// displays fixed line termination cost for the current user
function l7p_inline_term_local_fixed()
{
    return '($fixed = l7p_get_pricelist_domestic(\'fixed\')) ? (($fixed * 100) <= 100) ? l7p_currency_symbol($fixed,1,true) : l7p_currency_symbol($fixed) : \'<!-- TERM_LOCAL_FIXED not defined -->\'';
}

// displays fixed line termination cost for the current user
function l7p_inline_term_local_mobile()
{
    return '($mobile = l7p_get_pricelist_domestic(\'mobile\')) ? (($mobile * 100) <= 100) ? l7p_currency_symbol($mobile,1,true) : l7p_currency_symbol($mobile) : \'<!-- TERM_LOCAL_MOBILE not defined -->\'';
}

// displays termination name rate
function l7p_inline_term_name()
{
    return '(isset($term_name)) ? __($term_name) : \'<!-- TERM_NAME not defined -->\'';
}

// displays per minute termination rate
function l7p_inline_term_prefixes()
{
    return '(isset($term_data) && isset($term_data[\'patterns\'])) ? implode(", ",$term_data[\'patterns\']) : \'<!-- TERM_PREFIXES not defined -->\'';
}

// displays per minute termination rate
function l7p_inline_term_min_price()
{
    return 'isset($term_data) && isset($term_data[\'rate\']) ? (($term_data[\'rate\'] * 100) <= 100) ? l7p_currency_symbol($term_data[\'rate\'],1,true) : l7p_currency_symbol($term_data[\'rate\']) : \'<!-- TERM_MIN_PRICE not defined -->\'';
}

// displays minimim per minute termination charge
function l7p_inline_term_min_charge()
{
    return 'isset($min_charge) ? l7p_currency_symbol($min_charge,1,true) : l7p_currency_symbol(l7p_get_pricelist_min_charge(),1,true)';
}

// displays termination route first name (if different from previous one)
function l7p_inline_term_letter()
{
    return 'isset($firstletter) ? $firstletter : \'<!-- TERM_LETTER not defined -->\'';
}

// displays mobile termination rate
function l7p_inline_term_mobile()
{
    return '(($term_data[\'mobile\'] * 100) <= 100) ? l7p_currency_symbol($term_data[\'mobile\'],1,true) : l7p_currency_symbol($term_data[\'mobile\'])';
}

// displays fixed termination rate
function l7p_inline_term_fixed()
{
    return '(($term_data[\'fixed\'] * 100) <= 100) ? l7p_currency_symbol($term_data[\'fixed\'],1,true) : l7p_currency_symbol($term_data[\'fixed\'])';
}

// display country name
function l7p_inline_term_route_country()
{
    return 'isset($country_name) ? $country_name : l7p_get_country_name_from_query()';
}

// termination route name
function l7p_inline_term_route_name()
{
    return '(isset($term_name)) ? __($term_name) : \'<!-- TERM_ROUTE_NAME not defined -->\'';
}

// displays termination route per minut rate
function l7p_inline_term_route_rate()
{
    return '(($term_data[\'rate\'] * 100) <= 100) ? l7p_currency_symbol($term_data[\'rate\'],1,true) : l7p_currency_symbol($term_data[\'rate\'])';
}

// displays termination route  connection fee
function l7p_inline_term_conn_fee()
{
    return '(($term_data[\'connection\'] * 100) <= 100) ? l7p_currency_symbol($term_data[\'connection\'],1,true) : l7p_currency_symbol($term_data[\'connection\'])';
}

// displays termination route prefixes
function l7p_inline_term_route_prefixes()
{
    return 'implode(", ",$term_data[\'patterns\'])';
}

// country url
function l7p_inline_term_route_url()
{
    return 'l7p_url_for(\'@country_rates\', array(\'country\' => $country_name))';
}
# DDIs
// displays DDI country two letter code

function l7p_inline_ddi_country_code()
{
    return 'strtolower($ddi_data[\'country_code\'])';
}

// displays DDI country name
function l7p_inline_ddi_country_name()
{
    return 'isset($ddi_data[\'country_code\']) ? l7p_country_name($ddi_data[\'country_code\']) : l7p_get_country_name_from_query()';
}

// displays DDI country telephone code
function l7p_inline_ddi_country_tel_code()
{
    return 'l7p_get_ddi_country(l7p_get_country_code_from_query(), \'ddi_data\', \'country_tel_code\'); ';
}

// displays DDI area code
function l7p_inline_ddi_area_code()
{
    return '$ddi_data[\'area_code\']';
}

// displays DDI city name
function l7p_inline_ddi_city_name()
{
    return '$ddi_data[\'city\']';
}

// displays DDI country URL
function l7p_inline_ddi_country_url()
{
    return 'l7p_url_for(\'@numbers\', array(\'country\' => l7p_country_name($ddi_data[\'country_code\'])))';
}

// displays DDI setup fee
function l7p_inline_ddi_setup_fee()
{
    return 'l7p_currency_symbol($ddi_data[\'NRC\'])';
}

// displays DDI monthly subscription fee
function l7p_inline_ddi_monthly_fee()
{
    return 'l7p_currency_symbol($ddi_data[\'MRC\'])';
}

// displays DDI per minute usage fee
function l7p_inline_ddi_min_rate()
{
    return 'l7p_currency_symbol($ddi_data[\'min_rate\'])';
}

// displays buy DDI url
function l7p_inline_ddi_buy_url()
{
    return 'l7p_url_for(\'@number_buy\', array(\'country\' => l7p_get_country_name_from_query(), \'city\' => $ddi_data[\'city\']))';
}

// displays buy DDI url
function l7p_inline_ddi_toll_free_buy_url()
{
    return 'l7p_url_for(\'@number_buy_toll_free\', array(\'country\' => l7p_get_country_name_from_query(), \'city\' => $ddi_data[\'area_code\']))';
}

// displays buy DDI in selected U.S. state url
function l7p_inline_ddi_state_url()
{
    return '(isset($state_data) && isset($state_data[\'state_name\'])) ? l7p_url_for(\'@numbers_state\', array(\'state\' => $state_data[\'state_name\'], \'country\' => l7p_get_country_name_from_query())) : \' \'';
}

// displays buy DDI in selected U.S. state url
function l7p_inline_ddi_state_name()
{
    return '(isset($state_data) && isset($state_data[\'state_name\'])) ? $state_data[\'state_name\'] : \' \'';
}
# Phones
// Phone - desk category page URL

function l7p_inline_phone_desk_url()
{
    return 'l7p_url_for(\'@phones_group\', array(\'group\' => \'Desk Phones\'))';
}

// Phone - desk min. price
function l7p_inline_phone_desk_min_price()
{
    return '($min_price = l7p_get_min_price(\'Desk Phones\')) ? l7p_currency_symbol($min_price) : \'<!-- PHONE_DESK_MIN_PRICE not defined -->\'';
}

// Phone - DECT category page URL
function l7p_inline_phone_dect_url()
{
    return 'l7p_url_for(\'@phones_group\', array(\'group\' => \'DECT Phones\'))';
}

// Phone - DECT min. price
function l7p_inline_phone_dect_min_price()
{
    return '($min_price = l7p_get_min_price(\'DECT Phones\')) ? l7p_currency_symbol($min_price) : \'<!-- PHONE_DECT_MIN_PRICE not defined -->\'';
}

// Phone - conference category page URL
function l7p_inline_phone_conf_url()
{
    return 'l7p_url_for(\'@phones_group\', array(\'group\' => \'Conference Phones\'))';
}

// Phone - conference min. price
function l7p_inline_phone_conf_min_price()
{
    return '($min_price = l7p_get_min_price(\'Conference Phones\')) ? l7p_currency_symbol($min_price) : \'<!-- PHONE_CONF_MIN_PRICE not defined -->\'';
}

// Phone - adaptors category page URL
function l7p_inline_phone_adaptor_url()
{
    return 'l7p_url_for(\'@phones_group\', array(\'group\' => \'VoIP Adaptors\'))';
}

// Phone - adaptors min. price
function l7p_inline_phone_adaptor_min_price()
{
    return '($min_price = l7p_get_min_price(\'VoIP Adaptors\')) ? l7p_currency_symbol($min_price) : \'<!-- PHONE_ADAPTOR_MIN_PRICE not defined -->\'';
}

// Phone - accessories category page URL
function l7p_inline_phone_accessory_url()
{
    return 'l7p_url_for(\'@phones_group\', array(\'group\' => \'Accessories\'))';
}

// Phone - accessories min. price
function l7p_inline_phone_accessory_min_price()
{
    return '($min_price = l7p_get_min_price(\'Accessories\'])) ? l7p_currency_symbol($min_price) : \'<!-- PHONE_ACCESSORY_MIN_PRICE not defined -->\'';
}

// Phone - current group name
function l7p_inline_phone_group_name()
{
    // TODO
    return 'l7p_get_phone_group_name_from_query()';
}

// Phone - URL to the current phone
function l7p_inline_phone_url()
{
    return 'l7p_url_for(\'@phone_page\', array(\'group\' => l7p_get_phone_group_name_from_query(), \'model\' => $phone_data[\'name\']))';
}

// Phone - URL for buying current phone
function l7p_inline_phone_buy_url()
{
    return 'l7p_url_for(\'@phone_buy\', array(\'group\' => l7p_get_phone_group_name_from_query(), \'model\' => isset($phone_data) ? $phone_data[\'name\'] : l7p_get_phone(\'name\')))';
}

// Phone - name
function l7p_inline_phone_name()
{
    return 'isset($phone_data) ? $phone_data[\'name\'] : l7p_get_phone(\'name\')';
}

// Phone - manufacturer
function l7p_inline_phone_manufacturer()
{
    return 'isset($phone_data) ? $phone_data[\'manufacturer\'] : l7p_get_phone(\'manufacturer\')';
}

//  Phone - model
function l7p_inline_phone_model()
{
    return 'isset($phone_data) ? $phone_data[\'model\'] : l7p_get_phone(\'model\')';
}

// Phone - short description
function l7p_inline_phone_short_description()
{
    return 'isset($phone_data) ? $phone_data[\'short_description\'] : l7p_get_phone(\'short_description\')';
}

// Phone - stock
function l7p_inline_phone_stock()
{
    return 'isset($phone_data) ? $phone_data[\'stock\'] : l7p_get_phone(\'stock\')';
}

// Phone - price
function l7p_inline_phone_price()
{
    return 'l7p_currency_symbol(isset($phone_data) ? $phone_data[\'price\'] : l7p_get_phone(\'price\'))';
}

// Phone - thumbnail image
function l7p_inline_phone_thumb_img()
{
    return 'isset($phone_data) ? $phone_data[\'thumb_img\'] : l7p_get_phone(\'thumb_img\')';
}

// Phone - image
function l7p_inline_phone_img()
{
    return 'isset($phone_data) ? $phone_data[\'img\'] : l7p_get_phone(\'img\')';
}

// Phone - group URL
function l7p_inline_phone_group_url()
{
    return 'l7p_url_for(\'@phones_group\', array(\'group\' => l7p_get_phone_group_name_from_query()))';
}

// Phone - reviews count
function l7p_inline_phone_reviews_count()
{
    return 'isset($phone_data) ? $phone_data[\'review_count\'] : l7p_get_phone(\'review_count\')';
}

// Phone - ratings stars
function l7p_inline_phone_rating()
{
    // TODO
    return 'get_rating(isset($phone_data) ? $phone_data[\'rating\'] : l7p_get_phone(\'rating\'))';
}

// Phone - reviews URL
function l7p_inline_phone_reviews_url()
{
    // TODO: verify
    return 'l7p_url_for(\'@phone_reviews\', array(\'group\' => l7p_get_phone_group_name_from_query(), \'model\' => $phone_data[\'name\']))';
}

// Phone - description
function l7p_inline_phone_description()
{
    return 'str_replace(\'.pdf">\',\'.pdf" target="_blank">\',convert_markdown_text(l7p_get_phone(\'description\'())';
}
# Manual
// Manual - name

function l7p_inline_manual_name()
{
    return 'l7p_get_chapter(\'name\');';
}

// Manual - full title
function l7p_inline_manual_title()
{
    return 'l7p_get_chapter(\'title\');';
}

// Manual - chapter short description
function l7p_inline_manual_description()
{
    return 'l7p_get_chapter(\'description\');';
}

// Manual - Table of Contents
function l7p_inline_manual_toc()
{
    return 'l7p_get_chapter(\'toc\');';
}

// Manual - chapter title
function l7p_inline_manual_chapter()
{
    return 'l7p_get_chapter(\'chapter\');';
}

// Manual - content
function l7p_inline_manual_content()
{
    return 'l7p_get_chapter(\'content\');';
}

function l7p_inline_download_for_windows_url()
{
    return 'l7p_url_for(\'download\', array(\'os\' => \'windows\'))';
}

function l7p_inline_download_for_mac_url()
{
    return 'l7p_url_for(\'download\', array(\'os\' => \'mac-osx\'))';
}

function l7p_inline_download_for_linux_url()
{
    return 'l7p_url_for(\'download\', array(\'os\' => \'linux\'))';
}

function l7p_inline_form_security_token()
{
    return 'L7P_Form::hidden_input(array(\'name\' => \'extini\', \'value\' => l7p_get_session(\'extini\')))';
}

function l7p_inline_form_activation_token()
{
    return 'L7P_Form::hidden_input(array(\'name\' => \'activation_token\', \'value\' => l7p_get_activation_token()))';
}

function l7p_inline_form_login_action()
{
    return 'l7p_api_url()';
}

function l7p_inline_form_register_action()
{
    return 'l7p_api_url()';
}

function l7p_inline_form_activation_action()
{
    return 'l7p_api_url()';
}

function l7p_inline_activation_url()
{
    return 'l7p_activation_url()';
}

function l7p_inline_form_activation_url()
{
    return 'L7P_Form::hidden_input(array(\'name\' => \'activation_url\', \'value\' => l7p_activation_url()))';
}

function l7p_inline_flash_message()
{
    return 'l7p_get_flash_message()';
}

function l7p_inline_activation_message()
{
    return 'l7p_get_activation_message()';
}

function l7p_inline_package_type_value()
{
    return 'isset($package_type_value) ? $package_type_value : \'<!-- PACKAGE_TYPE_VALUE not defined -->\'';
}

function l7p_inline_package_type_label()
{
    return 'isset($package_type_label) ? $package_type_label : \'<!-- PACKAGE_TYPE_LABEL not defined -->\'';
}

function l7p_inline_package_route_value()
{
    return 'isset($package_route_value) ? $package_route_value : \'<!-- PACKAGE_ROUTE_VALUE not defined -->\'';
}

function l7p_inline_package_route_label()
{
    return 'isset($package_route_label) ? $package_route_label : \'<!-- PACKAGE_ROUTE_LABEL not defined -->\'';
}
