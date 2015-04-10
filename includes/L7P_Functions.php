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

function l7p_get_option($option, $default = null) {
    $option = "l7p_" . $option;
    return get_option($option, $default);
}

function l7p_get_settings($option, $default = null) {
    $setttings = l7p_get_option('settings');
    return isset($setttings[$option]) ? $setttings[$option] : $default;
}

function l7p_update_option($option, $value) {
    $option = "l7p_" . $option;
    return update_option($option, $value);
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

function l7p_update_session($key, $val) {
    $key = "l7p_" . $key;
    $_SESSION[$key] = $val;
}

function l7p_get_currency_names() {
    return array(
        'USD' => array('US$', 'US Dollar'),
        'GBP' => array('£', 'British Pound Sterling'),
        'EUR' => array('€', 'Euro'),
        'PLN' => array('zł', 'Polish Zloty'),
        'DKK' => array('DKr', 'Danish Krone'),
        'JPY' => array('¥', 'Japanese Yen'),
        'CAD' => array('Can$', 'Canadian Dollar'),
    );
}

function l7p_currency_symbol($value, $decimal = 2, $minor = false, $iso = null)
{
    if (!$iso) {
        $iso = l7p_get_session('currency', 'USD');
    }
  
    $value = floatval($value);
    $minors = array(
        'USD' => '¢',
        'GBP' => 'p.',
        'EUR' => 'cent.',
        'PLN' => 'gr.',
        'DKK' => 'øre',
        'JPY' => 'r',
        'CAD' => '¢',
    );
    
    $names = l7p_get_currency_names();
    $symbol = $minor ? $minors[$iso] : $names[$iso][0];
    
    if ($minor) {
        return number_format($value*100, $decimal)." ".$symbol;
    }
    
    if ($iso == 'PLN') {
        return number_format($value,$decimal)." ".$symbol;
    }
        
    return $symbol." ".number_format($value,$decimal);
}

function l7p_currency_name($currency_iso) {
    $names = l7p_get_currency_names();
    return isset($names[$currency_iso]) ? $names[$currency_iso][1] : "";
}


// allowed currencies
function l7p_get_currencies() {
    return l7p_get_settings('currencies', array('EUR', 'USD', 'JPY', 'GBP', 'PLN'));
}

function l7p_get_countries() {
    return l7p_get_settings('countries', array());
}

function l7p_get_pricelist() {
    return l7p_get_option('pricelist', array());
}
