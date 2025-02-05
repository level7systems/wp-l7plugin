<?php
/*
 * This file is part of the Level 7 Systems Ltd. platform.
 *
 * (c) Kamil Adryjanek <kamil@level7systems.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class L7P_Content
{

    public function __construct()
    {
        add_filter('the_excerpt', array('L7P_Content', 'parse_content'), 20);
        add_filter('the_content', array('L7P_Content', 'parse_content'), 20);
    }

    public static function parse_content($content)
    {
// parse for extra syntax
// strip PHP tags to avoid injcections
        $content = preg_replace("#<\?.*?(\?>|$)#s", "", $content);
        $content = L7P_Content::apply_callbacks($content);

// execute PHP functions
        ob_start();
        $content = eval("?> " . $content);
        $content = ob_get_clean();

        return trim($content);
    }

    private static function apply_callbacks($content)
    {
// static tags and blocks
        $regex = array(
            '/<\?/si',
            '/\?>/si',
            '/\[else\]/',
            '/\[\/if\]/',
            '/\[\/foreach\]/'
        );

        $replace = array(
            '&lt;?',
            '?&gt;',
            '<?php else : ?>',
            '<?php endif ?>',
            '<?php endforeach ?>',
        );

        $content = preg_replace($regex, $replace, $content);

// url
        $content = preg_replace_callback('/href="(.*)"/mU', array('L7P_Content', 'url'), $content);
// image
        $content = preg_replace_callback('/\(([a-z,0-9,\%,\/,\.,\-,\_]+\|.*)\)/imU', array('L7P_Content', 'image'), $content);
// statements
        $content = preg_replace_callback('/\[if (.*)\]/mU', array('L7P_Content', 'if_statement'), $content);
        $content = preg_replace_callback('/\[foreach (.*)\]/mU', array('L7P_Content', 'foreach_statement'), $content);
// blocks
        $content = preg_replace_callback('/\[block (.*)\]/imU', array('L7P_Content', 'block'), $content);
// inlines
        $content = preg_replace_callback('/\[([0-9A-Z,_]+)\]/imU', array('L7P_Content', 'inline'), $content);

        return $content;
    }

// TODO
    /**
     * Renders %INLINE_TAGS%
     */
    public static function inline($m)
    {
        $tag = $m[1];
        $inline = "l7p_inline_" . strtolower($tag);
        if (!is_callable($inline)) {
            return '<span L7PSyntaxError="true" style="color: balck; background: #FFA500; font-weight: bold;">Error: Undefined shortcode tag:' . $tag . '</div>';
        }

        return '<?php echo ' . call_user_func($inline) . '; ?>';
    }

    public static function block($m)
    {
        $tag = $m[1];
        $block = "l7p_block_" . strtolower($tag);
        if (!is_callable($block)) {
            return '<span L7PSyntaxError="true" style="color: balck; background: #FFA500; font-weight: bold;">Error: Undefined block:' . $tag . '</div>';
        }

        return sprintf("<?php echo call_user_func('%s') ?>", $block);
    }

    public static function image($m)
    {
        $temp = array();
        foreach (explode("|", $m[1]) as $part) {
            if (!trim($part))
                continue;
            $temp[] = trim(trim($part), "'");
        }

        if (count($temp) < 2) {
            return '<p MySyntaxError="true" style="color: red; font-weight: bold;">Missing parameters</p>';
        }
        
        $options = array('absolute' => true);
        if (strpos($temp[0], "%CULTURE%") === false) {
            $src_img = $temp[0];
        } else {
            $src_img = str_replace("%CULTURE%", "'.l7p_get_culture().'", $temp[0]);
        }
        
        $options['alt'] = $options['title'] = $temp[1];
        $img = l7p_image_tag($src_img, $options);

        return "<?php echo '$img'; ?>";
    }

    public static function url($match)
    {
        $m = array();

        if (preg_match('/^http|^mailto|^sip|^tel|^javascript|^%PROTOCOL%|^%[A-Z,_]+%$|^%[A-Z,_]+%#[a-z,A-z]+$/', $match[1])) { // External links
            return 'href="' . $match[1] . '"';
        } else if (preg_match('/^#/', $match[1])) { // anchor
            return 'href="' . $match[1] . '"';
        } else if (preg_match('/^\{(.*)\}(#.*)?$/', $match[1], $m)) { // Static routes
            $route_name = strtolower($m[1]);

            if ($route_name == "entry_app") {
                return "href=\"<?php echo app_url(get_entry_app()).__('/app') ?>\"";
            } else if ($route_name == "extjs_newticket") {
                return 'href="<?php echo url_for("@extini?extini=newticket") ?>"';
            } else { // static page
                if (!l7p_has_route($route_name)) {
                    return 'href="' . $match[1] . '"';
                }

                if (isset($m[2])) {
                    return 'href="<?php echo l7p_url_for(__("@' . $route_name . '")) ?>' . $m[2] . '"';
                } else {
                    return 'href="<?php echo l7p_url_for(__("@' . $route_name . '")) ?>"';
                }
            }
        } else if (preg_match('/^\{(.*)\}(\?(.*))?$/', $match[1], $m)) { // Static routes with param
            $route_name = strtolower($m[1]);

            parse_str($m[3], $params);

            if (!l7p_has_route($route_name)) {
                return 'href="' . $match[1] . '"';
            }

            $url = l7p_url_for($route_name, $params);
            return "href=<?php echo '$url' ?>";
        } else { // CMS
            if ($match[1] == '/%CULTURE%/') {
                return 'href="<?php echo l7p_url_for(__("@cms")."?url=") ?>"';
            } else {
                $parts = explode("/", ltrim($match[1], "/"), 2);

                if (count($parts) != 2) {
                    return 'href="' . $match[1] . '"';
                }

                if ($parts[0] == 'en') {
                    $route_name = 'cms';
                } else {
                    $route_name = 'cms_' . $parts[0];
                }

                if (!l7p_has_route($route_name)) {
                    return 'href="' . $match[1] . '"';
                }

                return 'href="<?php echo l7p_url_for("@' . $route_name . '?url=' . $parts[1] . '") ?>"';
            }
        }
    }

    /**
     * Returns IF/ELSE condition
     */
    public static function if_statement($m)
    {
        $params = explode(" ", $m[1]);
        $condition = array_shift($params);

        if (!is_callable($condition)) {
            $func = "l7p_" . $condition;
        }

        if (is_callable($func)) {
            if ($params) {
                return sprintf("<?php if (%s(%s)) : ?>", $func, implode(", ", $params));
            }
            return sprintf("<?php if (%s()) : ?>", $func);
        }

        switch ($condition) {

            // forms
            
            // deprecated
            case 'package_route_selected':
                return '<?php '
                    . '$country_code = isset($package_route_value) ? substr($package_route_value, 0, stripos($package_route_value, "-")) : "";'
                    . 'if ($country_code == l7p_get_geo()): '
                    . '?>';
                
            case 'package_country_selected':
                return '<?php '
                    . '$country_code = isset($package_country_value) ? $package_country_value : "";'
                    . 'if ($country_code == l7p_get_geo()): '
                    . '?>';
                
            case 'is_subscribed':
                return '<?php '
                    . '$is_subscribed = l7p_get_session(\'is_subscribed\', false);'
                    . 'if ($is_subscribed): '
                    . '?>';

            // termination
            case 'term_has_local':
                return '<?php '
                    . '$domestic = l7p_get_pricelist_domestic(); '
                    . 'if (isset($domestic["fixed"]) && isset($domestic["mobile"])): '
                    . '?>';

            case 'term_unlimited_local':
                return '<?php '
                    . '$package_routes = l7p_get_pricelist(\'package_routes\'); '
                    . 'if (isset($package_routes) && (in_array(l7p_get_geo()."-L", $package_routes) || in_array(l7p_get_geo()."-M", $package_routes))): '
                    . '?>';

            case 'term_local_mobile_free':
                return '<?php '
                    . '$package_routes = l7p_get_pricelist(\'package_routes\'); '
                    . 'if (in_array(l7p_get_geo()."-M", $package_routes)): '
                    . '?>';

            case 'term_local_fixed_free':
                return '<?php '
                    . '$package_routes = l7p_get_pricelist(\'package_routes\'); '
                    . 'if (in_array(l7p_get_geo()."-L",$package_routes)): '
                    . '?>';
            case 'term_is_mobile_free':
                return '<?php if (isset($term_data) && $term_data["mobile-package"]) : ?>';
            case 'term_is_unlimited':
                return '<?php if (isset($term_data) && $term_data["fixed-package"]) : ?>';

            case 'term_route_unlimited':
                return '<?php if (isset($term_data) && $term_data["package"]) : ?>';

            case 'term_route_conn_fee':
                return '<?php if (isset($term_data) && $term_data["connection"]) : ?>';

            case 'term_next_letter':
                return '<?php if ($letter_changed) :;$letter_changed = false ?>';

// Bundle
            case 'ddi_is_included':
                return '<?php if (isset($bundleCountryData) && $bundleCountryData["ddi-package"]) : ?>';

            case 'boudle_route_is_included':
                return '<?php if (isset($bundleRouteData) && $bundleRouteData["package"]) : ?>';

// Fax
            case 'fax_next_letter':
                return '<?php if ($fax_letter_changed) :;$fax_letter_changed = false ?>';

// DDIs
            case 'ddi_has_geographic':
                return '<?php if (l7p_ddi_has_geographic()) : ?>';

            case 'ddi_has_national':
                return '<?php if (l7p_ddi_has_national()) : ?>';

            case 'ddi_has_mobile':
                return '<?php if (l7p_ddi_has_mobile()) : ?>';

            case 'ddi_has_tollfree':
                return '<?php if (l7p_ddi_has_tollfree()) : ?>';

            case 'ddi_free':
                return '<?php if (isset($free) && $free) : ?>';

            case 'ddi_paid':
                return '<?php if (isset($paid) && $paid) : ?>';

            case 'ddi_national':
                return '<?php '
                    . '$national = l7p_get_ddi_country(l7p_get_country_code_from_query(), \'national\');'
                    . 'if (isset($national) && $national):'
                    . ' ?>';

            case 'ddi_ddi_city':
                return '<?php '
                    . '$cities = l7p_get_ddi_country(l7p_get_country_code_from_query(), \'cities\');'
                    . 'if (isset($cities) && $cities):'
                    . ' ?>';

            case 'ddi_ddi_toll_free':
                return '<?php '
                    . '$toll_free = l7p_get_ddi_country(l7p_get_country_code_from_query(), \'toll_free\');'
                    . 'if (isset($toll_free) && $toll_free):'
                    . ' ?>';

            case 'ddi_is_unlimited':
                return '<?php if (isset($ddi_data) && $ddi_data["package"]) : ?>';

            case 'ddi_is_free':
                return '<?php if (isset($ddi_data) && $ddi_data["is_free"]) : ?>';

            case 'ddi_has_area_code':
                return '<?php if (isset($ddi_data) && $ddi_data["area_code"]) : ?>';


// Phones
            case 'phone_has_desk':
                return '<?php if (isset($min_price) && $min_price["Desk Phones"]) : ?>';
                
            case 'phone_has_dect':
                return '<?php if (isset($min_price) && $min_price["DECT Phones"]) : ?>';
                
            case 'phone_has_conference':
                return '<?php if (isset($min_price) && $min_price["Conference Phones"]) : ?>';
                
            case 'phone_has_adaptor':
                return '<?php if (isset($min_price) && $min_price["VoIP Adaptors"]) : ?>';
                
            case 'phone_has_accessory':
                return '<?php if (isset($min_price) && $min_price["Accessories"]) : ?>';
                
            case 'phones':
                return '<?php '
                    . '$phones = l7p_get_phones();'
                    . 'if (count($phones) > 0):'
                    . ' ?>';

            default:
                return '<!-- unknown condition ' . $m[1] . ' --> <?php if (false) : ?>';
        }

        return '<!-- unknown condition ' . $condition . ' --> <?php if (false) : ?>';
    }

    /**
     * Returns FOREACH loop
     */
    public static function foreach_statement($m)
    {
        switch ($m[1]) {
// forms
            case 'countries':
                return '<?php '
                    . '$countries = l7p_get_countries();'
                    . 'foreach ($countries as $country_code => $country_name): '
                    . ' ?>';
                
            // depreacted
            case 'package_route_options':
                return '<?php '
                    . '$register_settings = l7p_get_settings(\'register\');'
                    . '$term_routes = isset($register_settings[\'routes\']) ? $register_settings[\'routes\'] : array();'
                    . '$package_routes = array();'
                    . 'foreach ($term_routes as $id => $country_code) { $package_routes[$id] = l7p_country_name($country_code);} '
                    . 'asort($package_routes);'
                    . 'foreach ($package_routes as $package_route_value => $package_route_label):'
                    . ' ?>';
                
            case 'package_country_options':
                return '<?php '
                    . '$register_settings = l7p_get_settings(\'register\');'
                    . '$term_routes = isset($register_settings[\'routes\']) ? $register_settings[\'routes\'] : array();'
                    . '$package_countries = array(\'\' => "Please select one...");'
                    . 'foreach ($term_routes as $id => $country_code) { $package_countries[$country_code] = l7p_country_name($country_code);} '
                    . 'asort($package_countries);'
                    . 'foreach ($package_countries as $package_country_value => $package_country_label):'
                    . ' ?>';

            case 'package_type_options':
                return '<?php '
                    . '$currency = l7p_get_currency();'
                    . '$register_settings = l7p_get_settings(\'register\');'
                    . '$package_types = isset($register_settings[\'package_types\'][$currency]) ? $register_settings[\'package_types\'][$currency] : array();'
                    . 'foreach ($package_types as $package_type_value => $package_type_label):'
                    . ' ?>';
            
            // Bundle
            case 'bundle_letters':
                return '<?php '
                    . '$letters = l7p_get_bundle_letters();'
                    . 'foreach ((isset($letters) ? $letters : array()) as $firstletter):'
                    . ' ?>';
            case 'bundle_data':
                return '<?php '
                    . '$bundleData = l7p_get_bundle_data();'
                    . 'foreach ($bundleData as $firstletter => $bundleCountries) :'
                    . ' ?>';

            case 'bundle_countries':
                return '<?php '
                    . 'foreach ($bundleCountries as $bundleCountry => $bundleCountryData) :'
                    . ' ?>';

            case 'bundle_country_routes':
                return '<?php '
                    . 'foreach ($bundleCountryData["routes"] as $bundleRouteName => $bundleRouteData) :'
                    . ' ?>';

            // Termination
            case 'term_letters':
                return '<?php '
                    . '$letters = l7p_get_pricelist_letters();'
                    . 'foreach ((isset($letters) ? $letters : array()) as $firstletter):'
                    . ' ?>';

            case 'term_countries':
                return '<?php '
                    . '$letter_changed = true; foreach ((isset($countries) ? $countries : array()) as $country_name => $term_data):'
                    . ' ?>';

            case 'term_letter_routes':
                return '<?php '
                    . 'foreach ((isset($term_data) ? $term_data : array()) as $route):'
                    . ' ?>';

            case 'term_routes':
                return '<?php '
                    . '$country_code = l7p_get_country_code_from_query(); $routes = l7p_get_pricelist_country($country_code);'
                    . 'foreach ((isset($routes) ? $routes : array()) as $term_name => $term_data):'
                    . ' ?>';

            case 'term_int_data':
                return '<?php '
                    . '$termIntData = l7p_get_int_termination();'
                    . 'foreach ($termIntData as $firstletter => $intTermCountries) :'
                    . ' ?>';

            case 'term_int_countries':
                return '<?php '
                    . 'foreach ($intTermCountries as $termCountryName => $termCountryData):'
                    . ' ?>';
// Fax
            case 'fax_letters':
                return '<?php foreach ((isset($fax_letters) ? $fax_letters : array()) as $fax_firstletter => $fax_countries) : ?>';
                
            case 'fax_countries':
                return '<?php $fax_letter_changed = true; foreach ((isset($fax_countries) ? $fax_countries : array()) as $country_name => $fax_country_data) : ?>';
                
            case 'fax_routes':
                return '<?php foreach ((isset($fax_country_data) ? $fax_country_data : array()) as $fax_data) : ?>';

// DDI
            case 'ddi_free':
                return '<?php '
                    . '$free = l7p_get_ddi(\'free\'); '
                    . 'foreach ((isset($free) ? $free : array()) as $ddi_data):'
                    . ' ?>';

            case 'ddi_paid':
                return '<?php '
                    . '$paid = l7p_get_ddi(\'paid\'); '
                    . 'foreach ((isset($paid) ? $paid : array()) as $ddi_data):'
                    . ' ?>';

            case 'ddi_national':
                return '<?php '
                    . '$national = l7p_get_ddi_country(l7p_get_country_code_from_query(), \'national\');'
                    . 'foreach ((isset($national) ? $national : array()) as $ddi_data):'
                    . ' ?>';

            case 'ddi_city':
                return '<?php '
                    . '$cities = l7p_get_ddi_country(l7p_get_country_code_from_query(), \'cities\'); '
                    . 'foreach ((isset($cities) ? $cities : array()) as $ddi_data):'
                    . ' ?>';

            case 'ddi_toll_free':
                return '<?php '
                    . '$toll_free = l7p_get_ddi_country(l7p_get_country_code_from_query(), \'toll_free\'); '
                    . 'foreach ((isset($toll_free) ? $toll_free : array()) as $ddi_data):'
                    . ' ?>';

            case 'ddi_states':
                return '<?php '
                    . '$states = l7p_get_us_states(); '
                    . 'foreach ((isset($states) ? $states : array()) as $stateName):'
                    . ' ?>';

            case 'ddi_int_data':
                return '<?php '
                    . '$ddiIntData = l7p_get_int_origination();'
                    . 'foreach ($ddiIntData as $firstletter => $intOriginationCountries) :'
                    . ' ?>';

            case 'ddi_int_countries':
                return '<?php '
                    . 'foreach ($intOriginationCountries as $ddiCountryLink => $ddiCountryData):'
                    . ' ?>';

            case 'ddi_country_letters':
                return '<?php '
                    . '$ddiCountryLetters = l7p_get_origination_country_letters();'
                    . 'foreach ($ddiCountryLetters as $ddiCountryLetter):'
                    . ' ?>';

            case 'ddi_city_letters':
                return '<?php '
                    . '$ddiCityLetters = l7p_get_origination_city_letters();'
                    . 'foreach ($ddiCityLetters as $ddiCityLetter):'
                    . ' ?>';

            case 'ddi_country_data':
                return '<?php '
                    . '$ddiCountryData = l7p_get_ddi_country_data();'
                    . 'foreach ($ddiCountryData as $cityLetter => $ddiCountryCities):'
                    . ' ?>';

            case 'ddi_country_cities':
                return '<?php '
                    . 'foreach ($ddiCountryCities as $ddiCityData):'
                    . ' ?>';

            case 'ddi_country_national':
                return '<?php '
                    . '$ddiCountryData = l7p_get_ddi_country_national();'
                    . 'foreach ($ddiCountryData as $ddiCityData):'
                    . ' ?>';

            case 'ddi_country_mobile':
                return '<?php '
                    . '$ddiCountryData = l7p_get_ddi_country_mobile();'
                    . 'foreach ($ddiCountryData as $ddiCityData):'
                    . ' ?>';

            case 'ddi_country_tollfree':
                return '<?php '
                    . '$ddiCountryData = l7p_get_ddi_country_tollfree();'
                    . 'foreach ($ddiCountryData as $ddiCityData):'
                    . ' ?>';


// Phones
            case 'phones':
                return '<?php '
                    . '$phones = l7p_get_phones();'
                    . 'foreach ((isset($phones) ? $phones : array()) as $phone_data):'
                    . ' ?>';

            default:
                return '<!-- failed to parse foreach ' . $m[1] . ' --> <?php foreach (array() as $val) : ?>';
        }

        return '<!-- failed to parse foreach --> <?php foreach (array() as $val) : ?>';
    }
}

return new L7P_Content();
