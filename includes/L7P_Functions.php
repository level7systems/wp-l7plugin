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