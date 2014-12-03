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
    private $inline_tags = array();
    
    public function __construct()
    {
        // TODO
        $user_webproduct_code = l7p_get_option('luser_webproduct', 'v');
        $this->inline_tags = $this->get_inlines($user_webproduct_code);
        
        add_filter('the_content', array($this, 'parse_content'), 20);
    }
    
    public function parse_content($content)
    {
        // TODO: 
        if (is_single() || is_page()) {
            // parse for extra syntax
            
            $content = $this->apply_callbacks($content);
        }
        
        return $content;
    }
    
    
    
    private function apply_callbacks($content)
    {
        // static tags and blocks
        $regex = array(
            '/<\?/si',
            '/\?>/si',
            '/\|else\|/',
            '/\|endif\|/',
            '/\|endforeach\|/',
            // TODO
            '/<cms\s+id="block"\s+name="(.*)"\s*\/>/',
            '/<cms\s+id="slot"\s+name="(.*)"\s*\/>/',
        );
    
        $replace = array(
            '&lt;?',
            '?&gt;',
            '<?php else : ?>',
            '<?php endif ?>',
            '<?php endforeach ?>',
            // TODO
            '<?php include_component("cms","block",array("block_name" => "$1", "culture" => $sf_user->getCulture())) ?><?php include_slot("$1") ?>',
            '<?php include_slot("$1") ?>',
        );
    
        $content = preg_replace($regex, $replace, $content);
    
        $content = preg_replace_callback('/\`(.*)\`/msU', array($this, 'i18n'), $content);
        $content = preg_replace_callback('/href="(.*)"/mU', array($this, 'mysyntax_url'), $content);
        $content = preg_replace_callback('/\(([a-z,0-9,\%,\/,\.,\-,\_]+\|.*)\)/imU', array($this, 'mysyntax_image'), $content);
        $content = preg_replace_callback('/\%([A-Z,_]+)\%/imU', array($this, 'mysyntax_inline'), $content);
        $content = preg_replace_callback('/\|if\:(.*)\|/mU', array($this, 'mysyntax_cond'), $content);
        $content = preg_replace_callback('/\|foreach\:(.*)\|/mU', array($this, 'mysyntax_foreach'), $content);
        $content = preg_replace_callback('/<cms\s+id="(.*)"\s*\/>/mU', array($this, 'mysyntax_partial'), $content);
    
        return $content;
    }
    
    // TODO
    /**
     * Renders %INLINE_TAGS%
     */
    public function mysyntax_inline($m)
    {
        if (!isset($this->inline_tags[$m[1]])) {
            return '<span MySyntaxError="true" style="color: balck; background: #FFA500; font-weight: bold;">Error: Invalid inline tag:'.$m[1].'</div>';
        }
    
        return '<?php echo '.$this->inline_tags[$m[1]]['function'].' ?>';
    }
    
    /**
     * Renderes img tag
     */
    public static function mysyntax_image($m, $with_php_tag = true)
    {
        $temp = array();
    
        foreach (explode("|",$m[1]) as $part)
        {
            if (!trim($part)) continue;
    
            $temp[] = trim(trim($part),"'");
        }
    
        if (count($temp) < 2) return '<p MySyntaxError="true" style="color: red; font-weight: bold;">Missing parameters</p>';
    
        $options = (isset($temp[2])) ? ' '.$temp[2] : '';
    
        if (strpos($temp[0],"%CULTURE%") === false)
        {
            $src_img = $temp[0];
        }
        else
        {
            $src_img = str_replace("%CULTURE%","'.get_culture().'",$temp[0]);
        }
    
        if ($with_php_tag)
        {
            return "<?php echo image_tag('".$src_img."','alt=\"".$temp[1]."\"".$options."') ?>";
        }
        else
        {
            return "image_tag('".$src_img."','alt=\"".$temp[1]."\"".$options."')";
        }
    }
    
    /**
     * Returns page URL
     */
    public static function mysyntax_url($match)
    {
        $m = array();
    
        if (preg_match('/^http|^mailto|^sip|^tel|^javascript|^%PROTOCOL%|^%[A-Z,_]+%$|^%[A-Z,_]+%#[a-z,A-z]+$/', $match[1])) // External links
        {
            return 'href="'.$match[1].'"';
        }
        else if (preg_match('/^#/', $match[1])) // anchor
        {
            return 'href="'.$match[1].'"';
        }
        else if (preg_match('/^\{(.*)\}(#.*)?$/', $match[1], $m)) // Static routes
        {
            $route_name = strtolower($m[1]);
    
            if ($route_name == "entry_app")
            {
                return "href=\"<?php echo app_url(get_entry_app()).__('/app') ?>\"";
            }
            else if ($route_name == "extjs_newticket")
            {
                return 'href="<?php echo url_for("@extini?extini=newticket") ?>"';
            }
            else // static page
            {
                if (!sfRouting::getInstance()->hasRouteName($route_name))
                {
                    return 'href="'.$match[1].'"';
                }
    
                if (isset($m[2]))
                {
                    return 'href="<?php echo url_for(__("@'.$route_name.'")) ?>'.$m[2].'"';
                }
                else
                {
                    return 'href="<?php echo url_for(__("@'.$route_name.'")) ?>"';
                }
            }
        }
        else if (preg_match('/^\{(.*)\}(\?(.*))?$/', $match[1], $m)) // Static routes with param
        {
            $route_name = strtolower($m[1]);
    
            $param = $m[3];
    
            if (!sfRouting::getInstance()->hasRouteName($route_name))
            {
                return 'href="'.$match[1].'"';
            }
    
            return 'href="<?php echo url_for(__("@'.$route_name.'")."?'.$m[3].'") ?>"';
        }
        else // CMS
        {
            if ($match[1] == '/%CULTURE%/')
            {
                return 'href="<?php echo url_for(__("@cms")."?url=") ?>"';
            }
            else
            {
                $parts = explode("/",ltrim($match[1], "/"),2);
    
                if (count($parts) != 2)
                {
                    return 'href="'.$match[1].'"';
                }
    
                if ($parts[0] == 'en')
                {
                    $route_name = 'cms';
                }
                else
                {
                    $route_name = 'cms_'.$parts[0];
                }
    
                if (!sfRouting::getInstance()->hasRouteName($route_name))
                {
                    return 'href="'.$match[1].'"';
                }
    
                return 'href="<?php echo url_for("@'.$route_name.'?url='.$parts[1].'") ?>"';
            }
        }
    }
    
    /**
     * Returns __('') translation tag
     */
    public static function i18n($m)
    {
        $replace_from = array();
        $replace_to = array();
    
        $tokens = array();
    
        $_m = array();
    
        if (preg_match_all('/%[a-z,_]+%/iU',$m[1],$_m))
        {
            foreach ($_m[0] as $i => $token)
            {
                $i++;
    
                $_token = trim($token,"%");
    
                if (isset(self::$inline_tags[$_token]))
                {
                    $tag = self::$inline_tags[$_token]['function'];
                }
                else
                {
                    $tag = '"<!--- Inline tag \''.trim($token,"%").'\' not found -->"';
                }
    
                $replace_from[] = $token;
                $replace_to[] = '%'.$i.'%';
    
                $tokens[] = '"%'.$i.'%" => '.$tag;
            }
    
            $m[1] = str_replace($replace_from, $replace_to, $m[1]);
        }
    
        if ($tokens)
        {
            return '<?php echo __("'.str_replace('"','\"',$m[1]).'", array('.implode(",",$tokens).') ) ?>';
        }
        else
        {
            return '<?php echo __("'.str_replace('"','\"',$m[1]).'") ?>';
        }
    }
    
    /**
     * Returns IF/ELSE condition
     */
    public static function mysyntax_cond($m)
    {
        if (strpos($m[1],":"))
        {
            $temp = explode(":",$m[1]);
    
            switch ($temp[0])
            {
            	case 'currency':
            	    return '<?php if ($sf_user->getCurrency() == "'.$temp[1].'") : ?>';
            	    break;
            	case 'url':
            	    return '<?php if (is_url_match("'.$temp[1].'")) : ?>';
            	    break;
            	case 'has_slot':
            	    return '<?php if (has_slot("'.$temp[1].'")) : ?>';
            	    break;
            	case 'auth':
            	    return '<?php if ($sf_user->isAuthenticated()) : ?>';
            	    break;
            	default:
            	    return '<!-- unknown condition '.$temp[0].' --> <?php if (false) : ?>';
            }
        }
    
        switch ($m[1])
        {
        	case 'auth':
        	    return '<?php if ($sf_user->isAuthenticated()) : ?>';
        	    break;
    
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
        	    break;
        	case 'ddi_paid':
        	    return '<?php if (isset($paid) && $paid) : ?>';
        	    break;
        	case 'ddi_national':
        	    return '<?php if (isset($national) && $national) : ?>';
        	    break;
        	case 'ddi_ddi_city':
        	    return '<?php if (isset($cities) && $cities) : ?>';
        	    break;
        	case 'ddi_ddi_toll_free':
        	    return '<?php if (isset($toll_free) && $toll_free) : ?>';
        	    break;
        	case 'ddi_is_unlimited':
        	    return '<?php if (isset($ddi_data) && $ddi_data["package"]) : ?>';
        	    break;
        	case 'ddi_is_free':
        	    return '<?php if (isset($ddi_data) && $ddi_data["is_free"]) : ?>';
        	    break;
        	case 'ddi_has_area_code':
        	    return '<?php if (isset($ddi_data) && $ddi_data["area_code"]) : ?>';
        	    break;
        	case 'ddi_states':
        	    return '<?php if (isset($states) && $states) : ?>';
        	    break;
    
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
        	    return '<?php if (isset($phones) && $phones) : ?>';
        	    break;
        	case 'phone_in_stock':
        	    return '<?php if (isset($phone_data) && $phone_data["stock"] > 0) : ?>';
        	    break;
        	case 'phone_eol':
        	    return '<?php if (isset($phone_data) && $phone_data["active"] == 0) : ?>';
        	    break;
        	case 'phone_has_reviews':
        	    return '<?php if (isset($phone_data) && $phone_data["review_count"] > 0) : ?>';
        	    break;
    
        	    # Blog
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
                return '<!-- unknown condition '.$m[1].' --> <?php if (false) : ?>';
        }
    }
    
    /**
    * Returns FOREACH loop
    */
    public static function mysyntax_foreach($m)
    {
    	switch ($m[1])
        {
	        // Termination
            case 'term_letters':
                return '<?php foreach ((isset($letters) ? $letters : array()) as $firstletter => $countries) : ?>';
                break;
            case 'term_countries':
                return '<?php $letter_changed = true; foreach ((isset($countries) ? $countries : array()) as $country_name => $term_data) : ?>';
                break;
            case 'term_letter_routes':
                return '<?php foreach ((isset($term_data) ? $term_data : array()) as $route) : ?>';
                break;
            case 'term_routes':
                return '<?php foreach ((isset($routes) ? $routes : array()) as $term_name => $term_data) : ?>';
                break;
    
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
                return '<?php foreach ((isset($free) ? $free : array()) as $ddi_data) : ?>';
                break;
            case 'ddi_paid':
                return '<?php foreach ((isset($paid) ? $paid : array()) as $ddi_data) : ?>';
                break;
            case 'ddi_national':
                return '<?php foreach ((isset($national) ? $national : array()) as $ddi_data) : ?>';
                break;
            case 'ddi_city':
                return '<?php foreach ((isset($cities) ? $cities : array()) as $ddi_data) : ?>';
                break;
            case 'ddi_toll_free':
                return '<?php foreach ((isset($toll_free) ? $toll_free : array()) as $ddi_data) : ?>';
                break;
            case 'ddi_states':
                return '<?php foreach ((isset($states) ? $states : array()) as $state_data) : ?>';
                break;
    
            // Phones
            case 'phones':
                return '<?php foreach ((isset($phones) ? $phones : array()) as $phone_data) : ?>';
                break;
    
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
        return '<!-- failed to parse foreach '.$m[1].' --> <?php foreach (array() as $val) : ?>';
        }
    
        return '<!-- failed to parse foreach --> <?php foreach (array() as $val) : ?>';

    }

    /**
    * Gets partial
   */
    public static function mysyntax_partial($m)
    {
        $user = sfContext::getInstance()->getUser();
        
        $blocks = sfConfig::get('app_web-product_block');
        
        if (isset($blocks[$user->getWebProductCode()]))
        {
            $available_partials = array_merge($blocks['all'],$blocks[$user->getWebProductCode()]);
        }
        else
        {
            $available_partials = $blocks['all'];
        }
        
        if (!isset($available_partials[$m[1]]))
        {
            return '<div MySyntaxError="true" style="color: balck; padding: 5px; height: 40px; width: 100%; border: 1px solid red; background: #FFA500; font-weight: bold;">Error: invalid partial:<br/>'.$m[1].'</div>';
        }
        
        if (isset($available_partials[$m[1]]['partial']))
        {
            return '<?php include_partial("'.$available_partials[$m[1]]['partial'].'") ?>';
        }
        else if (isset($available_partials[$m[1]]['code']))
        {
            return '<?php '.$available_partials[$m[1]]['code'].' ?>';
        }
        else
        {
          return '<div MySyntaxError="true" style="color: balck; padding: 5px; height: 40px; width: 100%; border: 1px solid red; background: #FFA500; font-weight: bold;">Error: unable to process partial tag:<br/>'.$m[1].'</div>';
        }
    }
    
    private function get_inlines($web_product_code)
    {
        $inlines = array(
        	
        );
        
        if (in_array($web_product_code, array('v', 'r' , 'h'))) {
            $prod_code = 'v';
        } else {
            $prod_code = 'c';
        }
        
        if (isset($inlines[$prod_code])) {
            $available_inlines = array_merge($inlines['all'], $inlines[$prod_code]);
        } else {
            $available_inlines = $inlines['all'];
        }
        
        return $available_inlines;
    }
}

return new L7P_Content();