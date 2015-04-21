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
// TODO
        $user_webproduct_code = l7p_get_option('user_webproduct', 'v');

        add_filter('the_content', array($this, 'parse_content'), 20);
    }

    public function parse_content($content)
    {
        if (is_single() || is_page()) {
// parse for extra syntax
// strip PHP tags to avoid injcections
            $content = preg_replace("#<\?.*?(\?>|$)#s", "", $content);
// 
            $content = $this->apply_callbacks($content);

// execute PHP functions
            ob_start();
            $content = eval("?> " . $content);
            $content = ob_get_clean();
        }

        return $content;
    }

    private function apply_callbacks($content)
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

// TODO: url
// $content = preg_replace_callback('/href="(.*)"/mU', array($this, 'url'), $content);
// image
        $content = preg_replace_callback('/\(([a-z,0-9,\%,\/,\.,\-,\_]+\|.*)\)/imU', array($this, 'image'), $content);
// statements
        $content = preg_replace_callback('/\[if (.*)\]/mU', array($this, 'if_statement'), $content);
        $content = preg_replace_callback('/\[foreach (.*)\]/mU', array($this, 'foreach_statement'), $content);
// blocks
        $content = preg_replace_callback('/\[block (.*)\]/imU', array($this, 'block'), $content);
// inlines
        $content = preg_replace_callback('/\[([A-Z,_]+)\]/imU', array($this, 'inline'), $content);

        return $content;
    }

// TODO
    /**
     * Renders %INLINE_TAGS%
     */
    public function inline($m)
    {
        $tag = $m[1];
        $inline = "l7p_inline_" . $tag;
        if (!is_callable($inline)) {
            return '<span L7PSyntaxError="true" style="color: balck; background: #FFA500; font-weight: bold;">Error: Undefined shortcode tag:' . $tag . '</div>';
        }

        return '<?php echo ' . call_user_func($inline) . '; ?>';
    }

    public function block($m)
    {
        $tag = $m[1];
        $block = "l7p_block_" . $tag;
        if (!is_callable($block)) {
            return '<span L7PSyntaxError="true" style="color: balck; background: #FFA500; font-weight: bold;">Error: Undefined block:' . $tag . '</div>';
        }

        return sprintf("<?php echo call_user_func('%s') ?>", $block);
    }

    /**
     * TODO
     */
    public static function image($m, $with_php_tag = true)
    {
        $temp = array();

        foreach (explode("|", $m[1]) as $part) {
            if (!trim($part))
                continue;
            $temp[] = trim(trim($part), "'");
        }

        if (count($temp) < 2)
            return '<p MySyntaxError="true" style="color: red; font-weight: bold;">Missing parameters</p>';

        $options = (isset($temp[2])) ? ' ' . $temp[2] : '';

        if (strpos($temp[0], "%CULTURE%") === false) {
            $src_img = $temp[0];
        } else {
            $src_img = str_replace("%CULTURE%", "'.get_culture().'", $temp[0]);
        }

        if ($with_php_tag) {
            return "<?php echo image_tag('" . $src_img . "','alt=\"" . $temp[1] . "\"" . $options . "') ?>";
        }
        return "image_tag('" . $src_img . "','alt=\"" . $temp[1] . "\"" . $options . "')";
    }

    /**
     * TODO
     */
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
                if (!sfRouting::getInstance()->hasRouteName($route_name)) {
                    return 'href="' . $match[1] . '"';
                }

                if (isset($m[2])) {
                    return 'href="<?php echo url_for(__("@' . $route_name . '")) ?>' . $m[2] . '"';
                } else {
                    return 'href="<?php echo url_for(__("@' . $route_name . '")) ?>"';
                }
            }
        } else if (preg_match('/^\{(.*)\}(\?(.*))?$/', $match[1], $m)) { // Static routes with param
            $route_name = strtolower($m[1]);

            $param = $m[3];

            if (!sfRouting::getInstance()->hasRouteName($route_name)) {
                return 'href="' . $match[1] . '"';
            }

            return 'href="<?php echo url_for(__("@' . $route_name . '")."?' . $m[3] . '") ?>"';
        } else { // CMS
            if ($match[1] == '/%CULTURE%/') {
                return 'href="<?php echo url_for(__("@cms")."?url=") ?>"';
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

                if (!sfRouting::getInstance()->hasRouteName($route_name)) {
                    return 'href="' . $match[1] . '"';
                }

                return 'href="<?php echo url_for("@' . $route_name . '?url=' . $parts[1] . '") ?>"';
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

            case 'login_email':
                return '<?php if ($sf_flash->has("login-email")) : ?>';
                break;

            case 'subscribed':
                return '<?php if (isset($subscriber) && $subscriber->getIsSubscribed()) : ?>';
                break;

// Termination
            case 'term_has_local':
                return '<?php if (isset($domestic) && isset($domestic["fixed"]) && isset($domestic["mobile"])) : ?>';
                break;
            case 'term_unlimited_local':
                return '<?php if (isset($package_routes) && (in_array(get_geo()."-L",$package_routes) || in_array(get_geo()."-M",$package_routes))) : ?>';
                break;
            case 'term_local_mobile_free':
                return '<?php if (isset($package_routes) && in_array(get_geo()."-M",$package_routes)) : ?>';
                break;
            case 'term_local_fixed_free':
                return '<?php if (isset($package_routes) && in_array(get_geo()."-L",$package_routes)) : ?>';
                break;
            case 'term_is_unlimited':
                return '<?php if (isset($term_data) && $term_data["fixed-package"]) : ?>';
                break;
            case 'term_route_unlimited':
                return '<?php if (isset($term_data) && $term_data["package"]) : ?>';
                break;
            case 'term_route_conn_fee':
                return '<?php if (isset($term_data) && $term_data["connection"]) : ?>';
                break;
            case 'term_next_letter':
                return '<?php if ($letter_changed) :;$letter_changed = false ?>';
                break;

// Fax
            case 'fax_next_letter':
                return '<?php if ($fax_letter_changed) :;$fax_letter_changed = false ?>';
                break;

// DDIs
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
                
            case 'ddi_states':
                return '<?php '
                . '$states = l7p_get_ddi_country(l7p_get_country_code_from_query(), \'states\');'
                . 'if (isset($states) && $states):'
                . ' ?>';

// Phones
            case 'phone_has_desk':
                return '<?php if (isset($min_price) && $min_price["Desk Phones"]) : ?>';
                break;
            case 'phone_has_dect':
                return '<?php if (isset($min_price) && $min_price["DECT Phones"]) : ?>';
                break;
            case 'phone_has_conference':
                return '<?php if (isset($min_price) && $min_price["Conference Phones"]) : ?>';
                break;
            case 'phone_has_adaptor':
                return '<?php if (isset($min_price) && $min_price["VoIP Adaptors"]) : ?>';
                break;
            case 'phone_has_accessory':
                return '<?php if (isset($min_price) && $min_price["Accessories"]) : ?>';
                break;
            case 'phones':
                return '<?php '
                    . '$phones = l7p_get_phones();'
                    . 'if (isset($phones) && $phones):'
                    . ' ?>';
                
            case 'phone_in_stock':
                return '<?php '
                    . 'if (isset($phone_data) && $phone_data["stock"] > 0):'
                    . ' ?>';
                
            case 'phone_eol':
                return '<?php '
                    . 'if (isset($phone_data) && $phone_data["active"] == 0):'
                    . ' ?>';
                
            // TODO: to be removed
            case 'phone_has_reviews':
                return '<?php if '
                    . '(isset($phone_data) && $phone_data["review_count"] > 0):'
                    . ' ?>';

# Blog
// TODO: to be removed
            case 'blog_has_tag':
                return '<?php if (isset($tag) && $tag) : ?>';
                break;
            case 'blog_has_posts':
                return '<?php if (isset($pager) && $pager->getResults()) : ?>';
                break;

            case 'blog_post_has_comments':
                return '<?php if (isset($post) && $post->countPublishedComments() > 0) : ?>';
                break;
            case 'blog_has_to_paginate':
                return '<?php if (isset($pager) && $pager->haveToPaginate()) : ?>';
                break;
            case 'blog_pager_first_page':
                return '<?php if (isset($pager) && $pager->getPage() == 1) : ?>';
                break;
            case 'blog_pager_current_page':
                return '<?php if (isset($pager) && isset($page_no) && $page_no == $pager->getPage()) : ?>';
                break;
            case 'blog_pager_not_last':
                return '<?php if (isset($pager) && isset($page_no) && $page_no != $pager->getCurrentMaxLink()) : ?>';
                break;
            case 'blog_pager_last':
                return '<?php if (isset($pager) && $pager->getPage() == $pager->getCurrentMaxLink()) : ?>';
                break;
            case 'blog_recent':
                return '<?php if (isset($recent) && $recent) : ?>';
                break;
            case 'blog_tags':
                return '<?php if (isset($tags) && $tags) : ?>';
                break;

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
// Termination
            case 'term_letters':
                return '<?php '
                    . '$letters = l7p_get_pricelist_letters();'
                    . 'foreach ((isset($letters) ? $letters : array()) as $firstletter => $countries):'
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
// Fax
            case 'fax_letters':
                return '<?php foreach ((isset($fax_letters) ? $fax_letters : array()) as $fax_firstletter => $fax_countries) : ?>';
                break;
            case 'fax_countries':
                return '<?php $fax_letter_changed = true; foreach ((isset($fax_countries) ? $fax_countries : array()) as $country_name => $fax_country_data) : ?>';
                break;
            case 'fax_routes':
                return '<?php foreach ((isset($fax_country_data) ? $fax_country_data : array()) as $fax_data) : ?>';
                break;

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
                    . '$states = l7p_get_ddi_country(l7p_get_country_code_from_query(), \'states\'); '
                    . 'foreach ((isset($states) ? $states : array()) as $state_data):'
                    . ' ?>';

// Phones
            case 'phones':
                return '<?php '
                    . '$phones = l7p_get_phones();'
                    . 'foreach ((isset($phones) ? $phones : array()) as $phone_data):'
                    . ' ?>';

# Blog
            case 'blog_posts':
                return '<?php foreach ($pager->getResults() as $post) : ?>';
                break;
            case 'blog_pager':
                return '<?php foreach ($pager->getLinks() as $page_no) : ?>';
                break;
            case 'blog_recent':
                return '<?php foreach ((isset($recent) ? $recent : array()) as $post) : ?>';
                break;
            case 'blog_tags':
                return '<?php foreach ((isset($tags) ? $tags : array()) as $tag) : ?>';
                break;
            case 'post_tags':
                return '<?php foreach ((isset($post) ? $post->getPostHasTags() : array()) as $post_has_tag) : ?>';
                break;

// Sitemap
            case 'sitemap':
                return '<?php foreach ((isset($sitemap) ? $sitemap : array()) as $sitemap_data) : ?>';
                break;


            default:
                return '<!-- failed to parse foreach ' . $m[1] . ' --> <?php foreach (array() as $val) : ?>';
        }

        return '<!-- failed to parse foreach --> <?php foreach (array() as $val) : ?>';
    }
}

return new L7P_Content();
