<?php
/*
 * This file is part of the Level 7 Systems Ltd. platform.
 *
 * (c) Kamil Adryjanek <kamil@level7systems.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// displays charge for a SMS message
function l7p_inline_sms_charge()
{
    return 'l7p_currency_symbol(l7p_inline_charge(\'sms\'), 0, true)';
}

// displays monthly charge for a User account
function l7p_inline_user_charge()
{
    return 'l7p_currency_symbol(l7p_inline_charge(\'user\'), 0, true)';
}

// displays monthly charge for a Unlimited plan
function l7p_inline_user_unlimited()
{
    return 'l7p_currency_symbol(l7p_inline_charge(\'user-S\'), 0, true)';
}

// displays monthly charge for a Unlimited International plan
function l7p_inline_user_unlimited_int()
{
    return 'l7p_currency_symbol(l7p_inline_charge(\'user-A\'), 0, true)';
}

function l7p_inline_charge($service)
{
    if (!$currency = l7p_get_session('currency')) {
        $currency = l7p_get_option('currency', 'USD');
    }

    $charges = l7p_get_option('charges', array());
    $charge = array_key_exists($service, $charges) ? $charges[$service][$currency] : 0;

    return l7p_currency_symbol($charge, $currency);
}

// Termination

// displays fixed line termination cost for the current user
function l7p_inline_term_local_fixed()
{
    return '(isset($domestic) && isset($domestic[\'fixed\'])) ? (($domestic[\'fixed\'] * 100) <= 100) ? l7p_currency_symbol($domestic[\'fixed\'],1,true) : l7p_currency_symbol($domestic[\'fixed\']) : \'<!-- TERM_LOCAL_FIXED not defined -->\'';
}

// displays fixed line termination cost for the current user
function l7p_inline_term_local_mobile()
{
    return '(isset($domestic) && isset($domestic[\'mobile\'])) ? (($domestic[\'mobile\'] * 100) <= 100) ? l7p_currency_symbol($domestic[\'mobile\'],1,true) : l7p_currency_symbol($domestic[\'mobile\']) : \'<!-- TERM_LOCAL_MOBILE not defined -->\'';
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
    return 'isset($min_charge) ? l7p_currency_symbol($min_charge,1,true) : \'<!-- TERM_MIN_CHARGE not defined -->\'';
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

// TODO: those functions need heavy refactoring!

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
    return '(isset($state)) ? l7p_url_for(\'@numbers_state\', array(\'state\' => $state, \'country\' => l7p_get_country_name_from_query())) : l7p_url_for(\'@numbers\', array(\'country\' => $ddi_data[\'country_name\']))';
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
    return '(isset($min_price)) ? l7p_currency_symbol($min_price[\'Desk Phones\']) : \'<!-- PHONE_DESK_MIN_PRICE not defined -->\'';
}

// Phone - DECT category page URL
function l7p_inline_phone_dect_url()
{
    return 'l7p_url_for(\'@phones_group\', array(\'group\' => \'DECT Phones\'))';
}

// Phone - DECT min. price
function l7p_inline_phone_dect_min_price()
{
    return 'isset($min_price) ? l7p_currency_symbol($min_price[\'DECT Phones\']) : \'<!-- PHONE_DECT_MIN_PRICE not defined -->\'';
}

// Phone - conference category page URL
function l7p_inline_phone_conf_url()
{
    return 'l7p_url_for(\'@phones_group\', array(\'group\' => \'Conference Phones\'))';
}

// Phone - conference min. price
function l7p_inline_phone_conf_min_price()
{
    return 'isset($min_price) ? l7p_currency_symbol($min_price[\'Conference Phones\']) : \'<!-- PHONE_CONF_MIN_PRICE not defined -->\'';
}

// Phone - adaptors category page URL
function l7p_inline_phone_adaptor_url()
{
    return 'l7p_url_for(\'@phones_group\', array(\'group\' => \'VoIP Adaptors\'))';
}

// Phone - adaptors min. price
function l7p_inline_phone_adaptor_min_price()
{
    return 'isset($min_price) ? l7p_currency_symbol($min_price[\'VoIP Adaptors\']) : \'<!-- PHONE_ADAPTOR_MIN_PRICE not defined -->\'';
}

// Phone - accessories category page URL
function l7p_inline_phone_accessory_url()
{
    return 'l7p_url_for(\'@phones_group\', array(\'group\' => \'Accessories\'))';
}

// Phone - accessories min. price
function l7p_inline_phone_accessory_min_price()
{
    return 'l7p_currency_symbol($min_price[\'Accessories\'])';
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