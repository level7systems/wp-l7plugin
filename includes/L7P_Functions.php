<?php
/*
 * This file is part of the Level 7 Systems Ltd. platform.
 *
 * (c) Kamil Adryjanek <kamil@level7systems.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


function l7p_get_option($option, $default = false)
{
    $options = get_option('level7platform_options', array());

    return array_key_exists($option, $options) ? $options[$option] : $default;
}

function l7p_is_auth()
{
    return is_user_logged_in();
}

function l7p_is_post_request() {
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

function l7p_get_session($key, $default = false)
{
    $key = "l7p_" . $key;
    return array_key_exists($key, $_SESSION) ? $_SESSION[$key] : $default;
}

function l7p_set_session($key, $val)
{
    $key = "l7p_" . $key;
    $_SESSION[$key] = $val;
}

function l7p_currency_symbol($value, $currency_iso, $decimal = 2, $minor = false)
{
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

function l7p_block_currency_form()
{
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