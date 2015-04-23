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

function l7p_get_option($option, $default = null)
{
    $option = "l7p_" . $option;
    return get_option($option, $default);
}

function l7p_get_settings($option, $default = null)
{
    $setttings = l7p_get_option('settings');
    return isset($setttings[$option]) ? $setttings[$option] : $default;
}

function l7p_update_option($option, $value)
{
    $option = "l7p_" . $option;
    return update_option($option, $value);
}

function l7p_is_auth()
{
    return is_user_logged_in();
}

function l7p_is_post_request()
{
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

function l7p_get_locale()
{
    return substr(get_locale(), 0, 2);
}

function l7p_get_session($key, $default = false)
{
    $key = "l7p_" . $key;
    return array_key_exists($key, $_SESSION) ? $_SESSION[$key] : $default;
}

function l7p_update_session($key, $val)
{
    $key = "l7p_" . $key;
    $_SESSION[$key] = $val;
}

function l7p_get_permalinks()
{
    $permalinks = get_option(Level7Platform::OPTION_PERMALINKS);
    
    return array(
        'rates' => isset($permalinks['rates']) ? $permalinks['rates'] : 'voip-call-rates',
        'telephone_numbers' => isset($permalinks['telephone_numbers']) ? $permalinks['telephone_numbers'] : 'telephone-numbers',
        'hardware' => isset($permalinks['hardware']) ? $permalinks['hardware'] : 'hardware',
        'manual' => isset($permalinks['manual']) ? $permalinks['manual'] : 'manual',
    );
}

function l7p_get_currency_names()
{
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
    $decimal = intval($decimal);
    
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
        return number_format($value * 100, $decimal) . " " . $symbol;
    }

    if ($iso == 'PLN') {
        return number_format($value, $decimal) . " " . $symbol;
    }

    return $symbol . " " . number_format($value, $decimal);
}

function l7p_currency_name($currency_iso)
{
    $names = l7p_get_currency_names();
    return isset($names[$currency_iso]) ? $names[$currency_iso][1] : "";
}

function l7p_get_currency()
{
    return l7p_get_session('currency', 'USD');
}

// allowed currencies
function l7p_get_currencies($culture = false)
{
    return l7p_get_settings('currencies', array('EUR', 'USD', 'JPY', 'GBP', 'PLN'));
}

function l7p_get_countries()
{
    return l7p_get_settings('countries', array());
}

function l7p_has_country($country_name)
{
    $country_name = strtr($country_name, ['+' => ' ']);
    $countries = l7p_get_countries();
    
    return in_array($country_name, $countries);
}

function l7p_get_states()
{
    return l7p_get_settings('states', array());
}

function l7p_get_country_code_from_query()
{
    $country_name = l7p_get_country_name_from_query();
    $countries = l7p_get_countries();
    $country_code = strtolower(array_search($country_name, $countries));

    return strtoupper($country_code);
}

function l7p_get_country_name_from_query()
{
    global $wp_query;

    return isset($wp_query->query_vars['country']) ? strtr($wp_query->query_vars['country'], array('+' => ' ')) : '';
}

function l7p_get_state_code_from_query()
{
    $state_name = l7p_get_state_name_from_query();
    $states = l7p_get_states();
    $state_code = strtolower(array_search($state_name, $states));

    return strtoupper($state_code);
}

function l7p_get_state_name_from_query()
{
    global $wp_query;

    return isset($wp_query->query_vars['state']) ? strtr($wp_query->query_vars['state'], array('+' => ' ')) : '';
}

function l7p_get_phone_name_from_query()
{
    global $wp_query;

    return isset($wp_query->query_vars['model']) ? strtr($wp_query->query_vars['model'], array('+' => ' ')) : '';
}

function l7p_get_phone_group_name_from_query()
{
    global $wp_query;

    return isset($wp_query->query_vars['group']) ? strtr($wp_query->query_vars['group'], array('+' => ' ')) : '';
}

function l7p_get_chapter_name_from_query()
{
    global $wp_query;

    return isset($wp_query->query_vars['chapter']) ? strtr($wp_query->query_vars['chapter'], array('+' => ' ')) : '';
}

function l7p_get_pricelist()
{
    return l7p_get_option('pricelist', array());
}

function l7p_get_pricelist_letters()
{
    $allowed_letters = array("A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "Y", "Z");
    $currency = l7p_get_currency();
    $countries = l7p_get_countries();
    $pricelist = l7p_get_pricelist();
    $rates = $pricelist['rates'];
    $package_routes = $pricelist['package_routes'];

    $culture_rates = [];
    foreach ($countries as $country_code => $country_name) {
        if (!isset($rates[$country_code])) {
            continue;
        }

        if (!isset($rates[$country_code]['mobile'])) {
            $rates[$country_code]['mobile'] = $rates[$country_code]['fixed'];
        }

        // TODO: add currency for each country
        $culture_rates[$country_name]['fixed'] = $rates[$country_code]['fixed'][$currency];
        $culture_rates[$country_name]['mobile'] = $rates[$country_code]['mobile'][$currency];

        $culture_rates[$country_name]['fixed-package'] = (in_array($country_code . "-L", $package_routes)) ? true : false;
        $culture_rates[$country_name]['mobile-package'] = (in_array($country_code . "-M", $package_routes)) ? true : false;
    }

    ksort($culture_rates);

    $letters = array();
    foreach ($culture_rates as $country_name => $data) {
        $firstletter = $country_name[0];

        if (!in_array($firstletter, $allowed_letters)) {
            continue;
        }

        $letters[$firstletter][$country_name] = $data;
    }

    return $letters;
}

function l7p_get_pricelist_country($country_code)
{
    $currency = l7p_get_currency();
    $routes = l7p_get_pricelist_routes();

    return isset($routes[$currency][$country_code]) ? $routes[$currency][$country_code] : array();
}

function l7p_get_pricelist_routes()
{
    return l7p_get_option('routes', array());
}

function l7p_get_ddi_countries()
{
    return l7p_get_option('ddi_countries', array());
}

function l7p_get_ddi($type = 'free')
{
    if (in_array($type, array('free', 'paid'))) {
        $ddi = l7p_get_option('ddi', array());
        $currency = l7p_get_currency();
        
        return isset($ddi[$type][$currency]) ? $ddi[$type][$currency] : array();
    }
    return array();
}

function l7p_get_ddi_country($country_code, $data, $key = false )
{
    $currency = l7p_get_currency();
    
    $state_code = l7p_get_state_code_from_query();

    $ddi = l7p_get_ddi_countries();
    $country_data = $ddi[$currency][$country_code];

    if ($state_code) {
        $country_data = $ddi[$currency][$country_code][$state_code];
    }
    
//    l7p_pre($ddi[$currency]['US']);
    
    if (!$key) {
        return isset($ddi[$currency][$country_code][$data]) ? $ddi[$currency][$country_code][$data] : array();
    }
    
    return isset($ddi[$currency][$country_code][$data][$key]) ? $ddi[$currency][$country_code][$data][$key] : array();
}

function l7p_get_phones()
{
    $currency = l7p_get_currency();
    $locale = l7p_get_locale();
    $group = l7p_get_phone_group_name_from_query();
    $phones =  l7p_get_option('phones', array());
    
    if ($group) {
        return isset($phones[$locale][$currency][$group]) ? $phones[$locale][$currency][$group] : array();
    }
    
    return isset($phones[$locale][$currency]) ? $phones[$locale][$currency] : array();
}

function l7p_get_phone($attr)
{
    $phones = l7p_get_phones();
    $name = l7p_get_phone_name_from_query();
    
    return $phones[$name][$attr];
}

function l7p_get_chapters()
{
    // TODO
    $locale = l7p_get_locale();
    $chapters =  l7p_get_option('chapters', array());
    
    return isset($chapters) ? $chapters : array();
}

function l7p_get_chapter($attr)
{
    $chapters = l7p_get_chapters();
    $name = l7p_get_chapter_name_from_query();
    
    $parts = explode("_", $name);
    $manual_type = array_shift($parts);
    $name = implode("_", $parts);
    
    if ($attr == 'toc') {
        return $chapters[$manual_type]['index'];
    }
    
    return $chapters[$manual_type][$name][$attr];
}

function l7p_get_routes()
{
    return array(
        'country_rates'    => '/:permalink_rates/:country/:currency',
        'numbers'          => '/:permalink_telephone_numbers/:country',
        'numbers_state'    => '/:permalink_telephone_numbers/United-States/:state',
        'phone_page'       => '/:permalink_hardware/:group/:model',
        'phones_group'     => '/:permalink_hardware/:group',
        'manual'           => '/:permalink_manual/:chapter'
    );
}

function l7p_has_route($route_name)
{
    $route_name = ltrim($route_name, '@');
    $routes = l7p_get_routes();
    return array_key_exists($route_name, $routes);
}

function l7p_url_for($route_name, $params)
{
    $route_name = ltrim($route_name, '@');
    $replace_pairs = array();
    foreach (l7p_get_permalinks() as $key => $permalink) {
        $replace_pairs[sprintf(':permalink_%s', $key)] = $permalink;
    }

    foreach ($params as $key => $param) {
        $replace_pairs[':' . $key] = strtr($param, array(' ' => '+'));
    }
    
    // add currency
    $replace_pairs[':currency'] = strtolower(l7p_get_currency());

    $routes = l7p_get_routes();
    
    $url = strtr($routes[$route_name], $replace_pairs);
    
    // WPML integration
    if (function_exists('icl_get_current_language')) {
        $lang = icl_get_current_language();
        $url = '/' . $lang . $url;
    }

    return $url;
}

function l7p_redirect($url, $permanent = false)
{
    header('Location: ' . $url, true, $permanent ? 301 : 302);
    exit();
}

function l7p_pre(array $var)
{
    echo '<pre>';
    print_r($var);
    echo '</pre>';
}