<?php
/*
 * This file is part of the Level 7 Systems Ltd. platform.
 *
 * (c) Kamil Adryjanek <kamil@level7systems.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// utils

function l7p_get_option($option, $default = false) {
    $options = get_option('level7platform_options', array());
    return array_key_exists($option, $options) ? $options[$option] : $default;
}

function l7p_is_auth() {
    return is_user_logged_in();
}

function l7p_is_post_request() {
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

function l7p_get_session($key, $default = false) {
    $key = "l7p_" . $key;
    return array_key_exists($key, $_SESSION) ? $_SESSION[$key] : $default;
}

function l7p_set_session($key, $val) {
    $key = "l7p_" . $key;
    $_SESSION[$key] = $val;
}

function l7p_currency_symbol($value, $currency_iso, $decimal = 2, $minor = false) {
    $minors = array(
        'USD' => '¢',
        'GBP' => 'p.',
        'EUR' => 'cent.',
        'PLN' => 'gr.',
        'DKK' => 'øre',
        'JPY' => 'r',
        'CAD' => '¢',
    );
    
    $names = array(
        'USD' => array('US$', 'US Dollar'),
        'GBP' => array('£', 'British Pound Sterling'),
        'EUR' => array('€', 'Euro'),
        'PLN' => array('zł', 'Polish Zloty'),
        'DKK' => array('DKr', 'Danish Krone'),
        'JPY' => array('¥', 'Japanese Yen'),
        'CAD' => array('Can$', 'Canadian Dollar'),
    );
    
    $symbol = $minor ? $minors[$currency_iso] : $names[$currency_iso][0];
    
    if ($minor) {
        return number_format($value*100, $decimal)." ".$symbol;
    }
    
    if ($currency_iso == 'PLN') {
        return number_format($value,$decimal)." ".$symbol;
    }
        
    return $symbol." ".number_format($value,$decimal);
}


// allowed currencies
function l7p_get_currencies() {
    return array('EUR', 'USD', 'JPY', 'GBP', 'PLN');
}

// blocks

function l7p_block_currency_form() {
    $currency = l7p_get_session('currency', 'USD');
    
    if (l7p_is_post_request() && array_key_exists('currency', $_POST)) {
        
        // verify allowed currencied
        $currency = $_POST['currency'];
        if (in_array($currency, l7p_get_currencies())) {
            l7p_set_session('currency', $currency);
        } else {
            $currency = l7p_get_session('currency', 'USD');
        }
    }
    
    ob_start();
    ?>
        <form method="post" action="" class="currency-form">
            <select name="currency" id="currency" onchange="this.form.submit()">
                <option value="EUR"<?php if ($currency == 'EUR'): ?>selected="selected"<?php endif; ?>>Euro</option>
                <option value="USD"<?php if ($currency == 'USD'): ?>selected="selected"<?php endif; ?>>US Dollar</option>
                <option value="JPY"<?php if ($currency == 'JPY'): ?>selected="selected"<?php endif; ?>>Japanese Yen</option>
                <option value="GBP"<?php if ($currency == 'GBP'): ?>selected="selected"<?php endif; ?>>British Pound Sterling</option>
                <option value="PLN"<?php if ($currency == 'PLN'): ?>selected="selected"<?php endif; ?>>Polish Zloty</option>
            </select>
        </form>
    <?php 
    
    $content = ob_get_clean();
    
    return $content;
}

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

// TODO
function l7p_inline_app_url()
{
    // TODO
    return  "app_url(get_entry_app()).__('/app')";
}

# Phones

// desk category page URL
function l7p_inline_phone_desk_url() {
    return url_for(__('@phones_group').'?group='.__('Desk Phones'));
}

// desk min. price
function l7p_inline_phone_desk_min_price() {
    return isset($min_price) ? currency_symbol($min_price['Desk Phones']) : '<!-- PHONE_DESK_MIN_PRICE not defined -->';
}

// DECT category page URL
function l7p_inline_phone_dect_url() {
    return url_for(__('@phones_group').'?group='.__('DECT Phones'));
}
               
// DECT min. price
function l7p_inline_phone_dect_min_price() {
    return isset($min_price) ? currency_symbol($min_price['DECT Phones']) : '<!-- PHONE_DECT_MIN_PRICE not defined -->';
}

// conference category page URL
function l7p_inline_phone_conf_url() {

    return url_for(__('@phones_group').'?group='.__('Conference Phones'));
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

