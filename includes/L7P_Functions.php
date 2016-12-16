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
    $settings = l7p_get_option('settings');
    return isset($settings[$option]) ? $settings[$option] : $default;
}

function l7p_update_settings($option, $value)
{
    $settings = l7p_get_option('settings');
    $settings[$option] = $value;
    l7p_update_option('settings', $settings);
}

function l7p_get_web_product_settings($option, $default = null)
{
    $settings = l7p_get_option('settings');
    if (!isset($settings['web_product'][$option])) {
        return false;
    }
    return $settings['web_product'][$option];
}

function l7p_update_web_product_settings($option, $value)
{
    $settings = l7p_get_option('settings');
    $settings['web_product'][$option] = $value;
    l7p_update_option('settings', $settings);
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

// downloads
function l7p_get_download()
{
    return l7p_get_settings('download', array());
}

function l7p_get_download_url($os)
{
    $downloads = l7p_get_download();

    if ($os == 'mac-osx') {
        return $downloads[$os]['x64'];
    }

    // linux
    if ($os == 'linux' && preg_match('/x86_64/i', $_SERVER['HTTP_USER_AGENT'])) {
        return $downloads[$os]['x64'];
    }

    // win
    if ($os == 'windows' && preg_match('/WOW64|Win64/i', $_SERVER['HTTP_USER_AGENT'])) {
        return $downloads[$os]['x64'];
    }

    // x86
    return $downloads[$os]['x86'];
}

function l7p_get_config()
{
    return l7p_get_option('config', array());
}

function l7p_get_api_token()
{
    $config = l7p_get_config();
    return isset($config['api_key']) ? $config['api_key'] : '';
}

function l7p_get_permalinks($culture = null)
{
    $permalinks = l7p_get_option('permalinks');

    $cultures = l7p_get_cultures();

    $defaults = array(
        'rates' => 'voip-call-rates',
        'telephone_numbers' => 'telephone-numbers',
        'manual' => 'manual',
        'terms' => 'terms-and-conditions'
    );

    // if web product has shop enabled
    if (l7p_get_web_product_settings('has_shop')) {
        $defaults['hardware'] = 'hardware';
    }

    $result = array();
    foreach ($cultures as $culture) {
        foreach ($defaults as $name => $permalink) {
            $result[$culture][$name] = isset($permalinks[$culture . '_' . $name]) ? $permalinks[$culture . '_' . $name] : $defaults[$name];
        }
    }

    return $result;
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
        'SEK' => array('kr', 'Swedish Krone'),
    );
}

function l7p_currency_symbol($value, $decimal = 2, $minor = false, $iso = null)
{
    if (!$iso) {
        $iso = l7p_get_currency();
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
        'SEK' => 'öre',
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

function l7p_get_cultures()
{
    return l7p_get_settings('cultures', array('en'));
}

function l7p_has_culture($culture_name)
{
    $culture_name = strtolower($culture_name);
    $cultures = l7p_get_cultures();

    return in_array($culture_name, $cultures);
}

function l7p_get_culture()
{
    return l7p_get_locale();
}

// allowed currencies
function l7p_get_currencies()
{
    return l7p_get_settings('currencies', array());
}

function l7p_get_currency($auto_discover = false)
{
    $currency = l7p_get_session('currency', false);

    // if geoip module enabled
    if (!$currency && function_exists('geoip_country_code_by_name')) {
        // try go country by addr
        $country_code = l7p_get_geo();
        $country_code = strtolower($country_code);
        $currencies = l7p_get_currencies();
        if ($country_code && array_key_exists($country_code, $currencies)) {
            $currency = $currencies[$country_code];
        }
    }

    return $currency ? : 'USD';
}

function l7p_currency_name($currency_iso)
{
    $names = l7p_get_currency_names();
    return isset($names[$currency_iso]) ? $names[$currency_iso][1] : "";
}

function l7p_has_currency($currency_name)
{
    $currency_name = strtoupper($currency_name);
    $currencies = l7p_get_currencies();

    return in_array($currency_name, $currencies);
}

// return country code
function l7p_get_geo()
{
    // allow X-L7p-Geo-Ip header
    if ($geo_ip = l7p_get_http_header("X-L7p-Geo-Ip")) {
        return $geo_ip;
    }
    
    $country_code = '';
    // get remote address
    $remote_addr = l7p_get_remote_addr();
    // try to get country by addr
    if (function_exists('geoip_country_code_by_name')) {
        $country_code = @geoip_country_code_by_name($remote_addr);
    }

    if (!$country_code) {
        $country_code = 'US';
    }

    return $country_code;
}

function l7p_get_geo_state()
{
    // get remote address
    $remote_addr = l7p_get_remote_addr();
    $geoip = array();
    if (function_exists('geoip_record_by_name')) {
        $geoip = @geoip_record_by_name($remote_addr);
    }

    return (isset($geoip['region']) && $geoip['region']) ? $geoip['region'] : 'AL';
}

function l7p_get_countries($locale = null)
{
    if ($locale === null) {
        $locale = l7p_get_locale();
    }

    if (!l7p_has_culture($locale)) {
        throw new Exception(sprintf("Locale: %s does not supported.", $locale));
    }

    $countries = l7p_get_settings('countries', array());
    if (!isset($countries[$locale])) {
        return array();
    }

    $countries = $countries[$locale];
    asort($countries);

    return $countries;
}

function l7p_get_countries_urlized()
{
    $countries = l7p_get_countries();
    $countries_urlized = array();
    foreach ($countries as $country_code => $country_name) {
        $countries_urlized[$country_code] = l7p_urlize($country_name);
    }

    return $countries_urlized;
}

function l7p_country_name($country_code)
{
    $countries = l7p_get_countries();
    $country_code = strtoupper($country_code);

    if (!isset($countries[$country_code])) {
        return null;
    }

    return $countries[$country_code];
}

function l7p_has_country($country_name, $urlized = true)
{
    if ($urlized) {
        $countries = l7p_get_countries_urlized();
    } else {
        $country_name = strtr($country_name, array('-' => ' '));
        $countries = l7p_get_countries();
    }

    return in_array($country_name, $countries);
}

function l7p_get_states()
{
    return l7p_get_settings('states', array());
}

function l7p_get_currency_from_query()
{
    global $wp_query;

    if (!isset($wp_query->query_vars['currency'])) {
        return null;
    }
    
    $currency = $wp_query->query_vars['currency'];
    $currencies = l7p_get_currencies();
    if (!in_array(strtoupper($currency), $currencies)) {
        return null;
    }

    return $currency;
}

function l7p_get_city_name_from_query()
{
    global $wp_query;

    if (!isset($wp_query->query_vars['city'])) {
        return '';
    }

    return strtr($wp_query->query_vars['city'], array('-' => ' '));
}

function l7p_get_country_code_from_query()
{
    $country_name = l7p_get_country_name_from_query();
    $country_name_urlized = l7p_urlize($country_name);
    $countries_urlized = l7p_get_countries_urlized();
    $country_code = strtolower(array_search($country_name_urlized, $countries_urlized));

    return strtoupper($country_code);
}

function l7p_get_country_name_from_query()
{
    global $wp_query;

    if (!isset($wp_query->query_vars['country'])) {
        return '';
    }

    if ($wp_query->query_vars['country'] == 'United-States') {
        return 'United States';
    }

    $countries_urlized = l7p_get_countries_urlized();
    $country_name_urlized = l7p_urlize($wp_query->query_vars['country']);
    $country_code = array_search($country_name_urlized, $countries_urlized);

    return l7p_country_name($country_code);
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

    return isset($wp_query->query_vars['state']) ? strtr($wp_query->query_vars['state'], array('-' => ' ')) : '';
}

function l7p_get_phone_name_from_query()
{
    global $wp_query;

    return isset($wp_query->query_vars['model']) ? strtr($wp_query->query_vars['model'], array('-' => ' ')) : '';
}

function l7p_get_phone_group_name_from_query()
{
    global $wp_query;

    return isset($wp_query->query_vars['group']) ? strtr($wp_query->query_vars['group'], array('-' => ' ')) : '';
}

function l7p_get_chapter_name_from_query()
{
    global $wp_query;

    return isset($wp_query->query_vars['chapter']) ? $wp_query->query_vars['chapter'] : '';
}

function l7p_get_pricelist($key = false)
{
    $pricelist = l7p_get_option('pricelist', array());

    if ($key) {
        return isset($pricelist[$key]) ? $pricelist[$key] : array();
    }

    return $pricelist;
}

function l7p_get_pricelist_domestic($key = false)
{
    $pricelist = l7p_get_pricelist();
    $currency = l7p_get_currency();
    $country_code = l7p_get_geo();

    $domestic = 0;
    if (isset($pricelist['domestic'][$currency][$country_code])) {
        $domestic = $pricelist['domestic'][$currency][$country_code];
    }

    if ($key && isset($domestic[$key])) {
        return $domestic[$key];
    }

    return $domestic;
}

function l7p_get_pricelist_letters()
{
    $allowed_letters = array("A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "Y", "Z");
    $currency = l7p_get_currency();
    $countries = l7p_get_countries();
    $pricelist = l7p_get_pricelist();

    if (!isset($pricelist['rates'])) {
        return array();
    }
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

function l7p_get_pricelist_min_charge()
{
    $pricelist = l7p_get_pricelist();
    $currency = l7p_get_currency();

    if (isset($pricelist['min_charges'][$currency])) {
        return $pricelist['min_charges'][$currency];
    }

    return 0;
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
    if ($type == 'free') {
        $ddi = l7p_get_option('ddi', array());

        if (isset($ddi[$type])) {
            return ksort($ddi[$type]);
        }
    }

    if ($type == 'paid') {
        $ddi = l7p_get_option('ddi', array());
        $currency = l7p_get_currency();

        if (isset($ddi[$type][$currency])) {

            $ddi_countries = $ddi[$type][$currency];
            $countries = array();
            foreach ($ddi_countries as $key => $data) {
                $countries[$key] = l7p_country_name($data['country_code']);
            }
            array_multisort($countries, SORT_ASC, $ddi_countries);

            return $ddi_countries;
        }
    }
    return array();
}

function l7p_get_ddi_country($country_code, $data, $key = false)
{
    $currency = l7p_get_currency();

    $state_code = l7p_get_state_code_from_query();

    $ddi = l7p_get_ddi_countries();
    $country_data = $ddi[$currency][$country_code];

    if ($state_code && $data != 'ddi_data') {
        $country_data = isset($country_data[$state_code]) ? $country_data[$state_code] : array();
    }

    if (!$key) {
        return isset($country_data[$data]) ? $country_data[$data] : array();
    }

    return isset($country_data[$data][$key]) ? $country_data[$data][$key] : array();
}

function l7p_get_phones()
{
    $currency = l7p_get_currency();
    $locale = l7p_get_locale();
    $group = l7p_get_phone_group_name_from_query();
    $phones = l7p_get_option('phones', array());

    if ($group) {
        return isset($phones[$locale][$currency][$group]) ? $phones[$locale][$currency][$group] : array();
    }

    return isset($phones[$locale][$currency]) ? $phones[$locale][$currency] : array();
}

function l7p_get_phone($attr = null)
{
    $phones = l7p_get_phones();
    $name = l7p_get_phone_name_from_query();

    if (is_null($attr)) {
        return isset($phones[$name]) ? $phones[$name] : array();
    }
    return isset($phones[$name][$attr]) ? $phones[$name][$attr] : array();
}

function l7p_has_phone($phone_name)
{
    $phones = l7p_get_phones();
    $phone_name = strtr($phone_name, ['-' => ' ']);

    return isset($phones[$phone_name]);
}

function l7p_get_min_price($group_name)
{
    $phones = l7p_get_option('phones', array());
    $currency = l7p_get_currency();
    $locale = l7p_get_locale();

    $min_price = 0;
    if (!isset($phones[$locale][$currency][$group_name])) {
        return $min_price;
    }

    foreach ($phones[$locale][$currency][$group_name] as $phone) {
        if ($phone['price'] < $min_price || !$min_price) {
            $min_price = $phone['price'];
        }
    }

    return $min_price;
}

function l7p_get_chapters()
{
    // TODO
    $locale = l7p_get_locale();
    $chapters = l7p_get_option('chapters', array());

    return isset($chapters) ? $chapters : array();
}

function l7p_get_chapter($attr)
{
    $chapters = l7p_get_chapters();
    $name = l7p_get_chapter_name_from_query();
    
    if (strpos($name, "_")) {
        $parts = explode("_", $name);
        $manual_type = array_shift($parts);
        $name = implode("_", $parts);
    } else {
        $manual_type = 'Manual';
    }

    if ($attr == 'toc') {
        return isset($chapters[$manual_type]['index']) ? $chapters[$manual_type]['index'] : '';
    }
    return isset($chapters[$manual_type][$name][$attr]) ? $chapters[$manual_type][$name][$attr] : '';
}

function l7p_get_routes()
{
    $login_page = get_post(l7p_get_option('login_page_id'));

    return array(
        'login' => sprintf('/%s/', $login_page->post_name),
        'country_rates' => '/:permalink_rates/:country/:currency/',
        'numbers' => '/:permalink_telephone_numbers/:country/:currency/',
        'numbers_state' => '/:permalink_telephone_numbers/:country/:state/:currency/',
        'number_buy' => '/:permalink_telephone_numbers/:country/:city/buy/:currency/',
        'number_buy_toll_free' => '/:permalink_telephone_numbers/:country/toll-free/:city/buy/:currency/',
        'phone_page' => '/:permalink_hardware/:group/:model/:currency/',
        'phones_group' => '/:permalink_hardware/:group/:currency/',
        'phone_buy' => '/:permalink_hardware/:group/:model/buy/:currency/',
        'manual' => '/:permalink_manual/:chapter/',
        'terms' => '/:permalink_terms/',
        'download' => '/download-for-:os/'
    );
}

function l7p_has_route($route_name)
{
    $route_name = ltrim($route_name, '@');
    $routes = l7p_get_routes();
    return array_key_exists($route_name, $routes);
}

function l7p_url_for($route_name, $params = array(), $absolute = false)
{
    $routes = l7p_get_routes();
    $permalinks = l7p_get_permalinks();
    // locale
    $locale = isset($params['locale']) ? $params['locale'] : l7p_get_locale();
    $route_name = ltrim($route_name, '@');
    $replace_pairs = array();
    foreach ($permalinks[$locale] as $key => $permalink) {
        $replace_pairs[sprintf(':permalink_%s', $key)] = $permalink;
    }

    foreach ($params as $key => $param) {
        // urlize special characters
        $param = l7p_urlize($param);
        $replace_pairs[':' . $key] = $param;
    }

    // add currency id not set
    if (!isset($replace_pairs[':currency'])) {
        $replace_pairs[':currency'] = strtolower(l7p_get_currency());
    }

    $url = strtr($routes[$route_name], $replace_pairs);

    // WPML integration
    if (function_exists('icl_get_current_language')) {
        $lang = icl_get_current_language();
        $url = '/' . $lang . $url;
    }

    // absolute url
    if ($absolute) {
        $base_url = network_site_url();
        $url = $base_url . $url;
    }

    return $url;
}

function l7p_redirect($url, $permanent = false)
{
    header('Location: ' . $url, true, $permanent ? 301 : 302);
    exit();
}

function l7p_get_page_by_pagename($pagename)
{
    $pages = get_posts(array('name' => $pagename, 'post_type' => 'page'));
    return count($pages) > 0 ? $pages[0] : null;
}

function l7p_get_page()
{
    // TODO: shortcut method for retrieving L7P pages
}

function l7p_pre($var)
{
    echo '<pre>';
    var_dump($var);
    echo '</pre>';
}

function l7p_add_settings_field($id, $title, $callback, $page, $section = 'default', $args = array())
{
    $cultures = l7p_get_cultures();

    foreach ($cultures as $i => $culture) {

        $l7p_id = $culture . '_' . $id;
        $name = $args['name'];
        $l7p_title = !$i ? $title : '';
        $l7p_args = $args;
        $l7p_args['name'] = $culture . '_' . $name;
        $l7p_args['pre'] = '/' . $culture . '/';
        $l7p_args['value'] = isset($l7p_args['value'][$culture][$name]) ? $l7p_args['value'][$culture][$name] : '';
        $l7p_args['help'] = !$i && isset($args['help']) ? $args['help'] : '';

        add_settings_field($l7p_id, $l7p_title, $callback, $page, $section, $l7p_args);
    }
}

function l7p_urlize($text)
{
    include_once('L7P_Transliterator.php');

    return L7P_Transliterator::urlize($text, '-');
}

function l7p_do_shortcode($content)
{
    return L7P_Content::parse_content($content);
}

function l7p_confirm_account($token)
{
    $url = strtr(':url/customers/:token/confirmation', array(
        ':url' => l7p_rest_api_url(),
        ':token' => $token
    ));

    return l7p_send_curl($url, "POST");
}

function l7p_verify_reset_token($token)
{
    $url = strtr(':url/:token', array(
        ':url' => l7p_form_verify_reset_token_action(),
        ':token' => $token
    ));

    return l7p_send_curl($url);
}

function l7p_ressend_confirmation_email($email)
{
    $url = strtr(':url/:email', array(
        ':url' => l7p_form_resend_confirmation_email_action(),
        ':email' => $email
    ));

    return l7p_send_curl($url);
}

function l7p_verify_subscription_token($token)
{
    $url = strtr(':url/:token', array(
        ':url' => l7p_form_subscription_action(),
        ':token' => $token
    ));

    return l7p_send_curl($url);
}

function l7p_register_ppc_click($token)
{
    $params = array(
        'method' => 'ppc',
        'id' => $token,
        'referer' => $_SERVER['HTTP_REFERER'],
        'user_agent' => $_SERVER['HTTP_USER_AGENT'],
        'ip' => l7p_get_remote_addr()
    );

    $url = l7p_api_url() . '?' . http_build_query($params);

    return l7p_send_curl($url);
}

function l7p_register_agent_click($token)
{
    $params = array(
        'method' => 'agentclick',
        'id' => $token,
        'referer' => $_SERVER['HTTP_REFERER'],
        'user_agent' => $_SERVER['HTTP_USER_AGENT'],
        'ip' => l7p_get_remote_addr()
    );

    $url = l7p_api_url() . '?' . http_build_query($params);

    return l7p_send_curl($url);
}

function l7p_send_curl($url, $method = "GET")
{
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_URL => $url,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT => 'Level7 WP plugin',
        CURLOPT_HTTPHEADER => array('Content-Type: application/json'),
        CURLOPT_FOLLOWLOCATION => true
    ));
    $json = curl_exec($curl);

    // if JSONP was returned
    if (!in_array($json[0], array('[', '{'))) {
        $json = substr(trim($json, '();'), strpos($json, '(') + 1);
    }
    
    // decode JSON response
    $json = json_decode($json, true);
    
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    if (!in_array($httpCode, array(200, 201, 204))) {
        
        if (isset($json['errors']) && !empty($json['errors'])) {
            
            $messages = [];
            foreach ($json['errors'] as $field) {
                $messages[] = $field['message'];
            }
            throw new RestException(implode("<br/>", $messages));
        }
        throw new RestException($json['message']);
    }

    if (!$json) {
        return array(
            'success' => false,
            'info' => curl_error($curl)
        );
    }

    curl_close($curl);

    return $json;
}

function l7p_set_success_flash_message($message)
{
    add_flash_message('success', $message);
}

function l7p_set_error_flash_message($message)
{
    add_flash_message('error', $message);
}

function l7p_get_success_flash_message()
{
    $messages = l7p_get_flash_messages();
    if (isset($messages['success'])) {
        return $messages['success']['message'];
    }
    
    return '';
}

function l7p_get_error_flash_message()
{
    $messages = l7p_get_flash_messages();
    if (isset($messages['error'])) {
        return $messages['error']['message'];
    }
    
    return '';
}

function add_flash_message($key, $message)
{
    $messages = l7p_get_session('flash_messages', $messages);;
    $messages[$key] = array(
        'lifetime' => 2,
        'message' => $message
    );
    
    l7p_set_flash_messages($messages);
}

function l7p_get_flash_messages()
{
    return l7p_get_session('flash_messages', array());
}

function l7p_set_flash_messages($messages)
{
    l7p_update_session('flash_messages', $messages);
}

function l7p_set_activation_message($message)
{
    return l7p_update_session('activation_message', $message);
}

function l7p_get_activation_message()
{
    return l7p_get_session('activation_message', '');
}

function l7p_get_level7_domain()
{
    return l7p_get_settings('l7_tld');
}

function l7p_rest_api_url()
{
    return l7p_get_settings('rest_api_url');
}

function l7p_api_url()
{
    return sprintf("https://%s/%s/api", l7p_get_level7_domain(), l7p_get_web_product_settings('domain'));
}

function l7p_form_confirm_action()
{
    return sprintf("https://%s/%s/en/c", l7p_get_level7_domain(), l7p_get_web_product_settings('domain'));
}

function l7p_form_verify_reset_token_action()
{
    return sprintf("https://%s/%s/en/reset", l7p_get_level7_domain(), l7p_get_web_product_settings('domain'));
}

function l7p_form_resend_confirmation_email_action()
{
    return sprintf("https://%s/%s/en/r", l7p_get_level7_domain(), l7p_get_web_product_settings('domain'));
}

function l7p_form_subscription_action()
{
    return sprintf("https://%s/%s/en/profile", l7p_get_level7_domain(), l7p_get_web_product_settings('domain'));
}

function l7p_image_tag($source, array $options = array())
{
    if (!$source) {
        return '';
    }

    $absolute = false;
    if (isset($options['absolute'])) {
        unset($options['absolute']);
        $absolute = true;
    }

    $options['src'] = l7p_image_path($source, $absolute);

    if (!isset($options['alt'])) {
        $path_pos = strrpos($source, '/');
        $dot_pos = strrpos($source, '.');
        $begin = $path_pos ? $path_pos + 1 : 0;
        $nb_str = ($dot_pos ? $dot_pos : strlen($source)) - $begin;
        $options['alt'] = ucfirst(substr($source, $begin, $nb_str));
    }

    if (isset($options['size'])) {
        list($options['width'], $options['height']) = explode('x', $options['size'], 2);
        unset($options['size']);
    }

    $html = '';
    foreach ($options as $key => $value) {
        $html .= ' ' . $key . '="' . $value . '"';
    }

    return sprintf("<img %s />", $html);
}

function l7p_image_path($source, $absolute = true)
{
    $path = '/images/';
    $url = 'http://static.ssl7.net';

    return $absolute ? $url . $path . $source : $path . $source;
}

function l7p_setcookie($name, $value = 0, $expire = 0, $path = "/", $domain = null, $secure = false)
{
    setcookie($name, $value, $expire, $path, $domain, $secure);
}

function l7p_hascookie($name)
{
    return isset($_COOKIE[$name]);
}

function l7p_starts_with($haystack, $needle)
{
    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
}

function l7p_ends_with($haystack, $needle)
{
    return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
}

function l7p_is_manual_chapter_page()
{
    global $wp_query;

    return isset($wp_query->query_vars['chapter']);
}

function l7p_is_rates_country_page()
{
    global $wp_query;
    $position = (strpos($wp_query->query_vars['name'], 'country-rates'));
    return isset($wp_query->query_vars['name']) && $position === 0;
}

function l7p_is_telephone_numbers_country_page()
{
    global $wp_query;
    
    $position = (strpos($wp_query->query_vars['name'], 'country-telephone-numbers'));
    return isset($wp_query->query_vars['name']) && $position === 0;
}

function l7p_is_hardware_group_page()
{
    global $wp_query;
    
    $position = (strpos($wp_query->query_vars['name'], 'hardware-group'));
    return isset($wp_query->query_vars['name']) && $position === 0;
}

function l7p_is_hardware_phone_details_page()
{
    global $wp_query;

    $position = (strpos($wp_query->query_vars['name'], 'hardware-model'));
    return isset($wp_query->query_vars['name']) && $position === 0;
}

function l7p_get_remote_addr()
{
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    }

    return $_SERVER['REMOTE_ADDR'];
}

function l7p_cache_clear()
{
    // support for WP Super Cache
    if (function_exists("wp_cache_clear_cache")) {
        wp_cache_clear_cache();
    }
}

/**
 * Return http header
 * 
 * @param string $headerName
 * 
 * @return string|null
 */
function l7p_get_http_header($headerName)
{
   $headerName = strtoupper(strtr($headerName, ['-' => '_']));
   
   if (function_exists('getallheaders')) {
       $allheaders = getallheaders();
   } else {
       $allheaders = [];
       foreach ($_SERVER as $name => $value) {
           if (substr($name, 0, 5) == 'HTTP_') {
               $allheaders[substr($name, 5)] = $value;
           }
       }
   }
   
   $headers = array();
   foreach ($allheaders as $name => $value) {
       $headers[strtoupper(strtr($name, ['-' => '_']))] = $value;
   }
   
   if (array_key_exists($headerName, $headers)) {
       return $headers[$headerName];
   }

   return null;
}

function l7p_is_ssl()
{
    if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
        return true;
    }
    
    return is_ssl();
}
