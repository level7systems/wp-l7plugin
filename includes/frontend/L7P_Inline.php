<?php
/*
 * This file is part of the Level 7 Systems Ltd. platform.
 *
 * (c) Kamil Adryjanek <kamil@level7systems.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// TODO:
function url_for()
{
    
}

// inlines

function l7p_inline_user_charge($atts)
{
    return l7p_inline_charge('user');
}

function l7p_inline_user_unlimited($atts)
{
    return l7p_inline_charge('user-S');
}

function l7p_inline_user_unlimited_int($atts)
{
    return l7p_inline_charge('user-A');
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

// Displays termination route first name (if different from previous one).
function l7p_inline_term_letter()
{
    return 'isset($firstletter) ? $firstletter : \'<!-- TERM_LETTER not defined -->\'';
}

// Displays mobile termination rate
function l7p_inline_term_mobile()
{
    return '(($term_data[\'mobile\'] * 100) <= 100) ? l7p_currency_symbol($term_data[\'mobile\'],1,true) : l7p_currency_symbol($term_data[\'mobile\'])';
}

// Displays fixed termination rate
function l7p_inline_term_fixed()
{
    return '(($term_data[\'fixed\'] * 100) <= 100) ? l7p_currency_symbol($term_data[\'fixed\'],1,true) : l7p_currency_symbol($term_data[\'fixed\'])';
}

function l7p_inline_term_countries()
{
    // TODO
}

// country url
function l7p_inline_term_route_url()
{
    return '\'?country=\'.$country_name';
    //return 'url_for(__(\'@country_rates\').\'?country=\'.$country_name)';
}

// display country name
function l7p_inline_term_route_country()
{
    return 'isset($country_name) ? $country_name : \'<!-- TERM_ROUTE_COUNTRY not defined -->\'';
}

// TODO
function l7p_inline_app_url()
{
    // TODO
    return "app_url(get_entry_app()).__('/app')";
}
# Phones
// desk category page URL
function l7p_inline_phone_desk_url()
{
    return url_for(__('@phones_group') . '?group=' . __('Desk Phones'));
}

// desk min. price
function l7p_inline_phone_desk_min_price()
{
    return isset($min_price) ? currency_symbol($min_price['Desk Phones']) : '<!-- PHONE_DESK_MIN_PRICE not defined -->';
}

// DECT category page URL
function l7p_inline_phone_dect_url()
{
    return url_for(__('@phones_group') . '?group=' . __('DECT Phones'));
}

// DECT min. price
function l7p_inline_phone_dect_min_price()
{
    return isset($min_price) ? currency_symbol($min_price['DECT Phones']) : '<!-- PHONE_DECT_MIN_PRICE not defined -->';
}

// conference category page URL
function l7p_inline_phone_conf_url()
{

    return url_for(__('@phones_group') . '?group=' . __('Conference Phones'));
}
/*
    PHONE_CONF_MIN_PRICE:
    function: "isset($min_price) ? currency_symbol($min_price['Conference Phones']) : '<!-- PHONE_CONF_MIN_PRICE not defined -->'"
    description: Phone - conference min. price
    PHONE_ADAPTOR_URL:
    function: "url_for(__('@phones_group').'?group='.__('VoIP Adaptors'))"
    description: Phone - adaptors category page URL
    PHONE_ADAPTOR_MIN_PRICE:
    function: "(isset($min_price)) ? currency_symbol($min_price['VoIP Adaptors']) : '<!-- PHONE_ADAPTOR_MIN_PRICE not defined -->'"
    description: Phone - adaptors min. price
    PHONE_ACCESSORY_URL:
    function: "url_for(__('@phones_group').'?group='.__('Accessories'))"
    description: Phone - accessories category page URL
    PHONE_ACCESSORY_MIN_PRICE:
    function: "currency_symbol($min_price['Accessories'])"
    description: Phone - accessories min. price
    PHONE_GROUP_NAME:
    function: "$sf_request->getParameter('group')"
    description: Phone - current group name
    PHONE_URL:
    function: "url_for(__('@phone_page').'?group='.$sf_request->getParameter('group').'&model='.$phone_data['name'])"
    description: Phone - URL to the current phone
    PHONE_NAME:
    function: "$phone_data['name']"
    description: Phone - name
    PHONE_MANUFACTURER:
    function: "$phone_data['manufacturer']"
    description: Phone - manufacturer
    PHONE_MODEL:
    function: "$phone_data['model']"
    description: Phone - model
    PHONE_SHORT_DESCRIPTION:
    function: "$phone_data['short_description']"
    description: Phone - short description
    PHONE_STOCK:
    function: "$phone_data['stock']"
    description: Phone - stock
    PHONE_PRICE:
    function: "currency_symbol($phone_data['price'])"
    description: Phone - price
    PHONE_THUMB_IMG:
    function: "$phone_data['thumb_img']"
    description: Phone - thumbnail image
    PHONE_IMG:
    function: "$phone_data['img']"
    description: Phone - image
    PHONE_GROUP_URL:
    function: "url_for(__('@phones_group').'?group='.$sf_request->getParameter('group'))"
    description: Phone - group URL
    PHONE_REVIEWS_COUNT:
    function: "$phone_data['review_count']"
    description: Phone - reviews count
    PHONE_RATING:
    function: "get_rating($phone_data['rating'])"
    description: Phone - ratings stars
    PHONE_REVIEWS_URL:
    function: "url_for(__('@phone_reviews').'?group='.$sf_request->getParameter('group').'&model='.$phone_data['name'])"
    description: Phone - reviews URL
    PHONE_DESCRIPTION:
    function: "str_replace('.pdf">','.pdf" target="_blank">',convert_markdown_text($phone_data['description']))"
    description: Phone - description
*/

