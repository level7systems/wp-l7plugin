<?php
/*
 * This file is part of the Level 7 Systems Ltd. platform.
 *
 * (c) Kamil Adryjanek <kamil@level7systems.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class L7P_Frontend
{

    public function __construct()
    {
        add_action('wp_enqueue_scripts', array($this, 'styles'));
        add_action('wp_enqueue_scripts', array($this, 'scripts'));
        
        add_filter('wp_title', array($this, 'filter_wp_title'), 10, 2);
        add_filter('widget_posts_args', array($this, 'filter_recent_posts_widget_parameters'));
        // remove rel cannonical
        add_filter('wpseo_canonical', '__return_false');
        // removes WP shortlinks from <heade>
        remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0);
        // removes WP extra feeds from <head>
        remove_action('wp_head', 'feed_links_extra', 3 );
    }

    /**
     * Enqueue styles
     */
    public function styles()
    {
        wp_enqueue_style(
            'level7-forms', plugins_url('/assets/css/frontend/forms.css', L7P_PLUGIN_FILE)
        );
        
        wp_enqueue_style(
            'level7-jquery-ui-css', plugins_url('/assets/css/jquery-ui.css', L7P_PLUGIN_FILE)
        );
        wp_enqueue_style(
            'jquery-loading-indicator', plugins_url('/assets/css/frontend/jquery.loading-indicator.css', L7P_PLUGIN_FILE)
        );
    }

    /**
     * Enqueue scripts
     */
    public function scripts()
    {
        wp_enqueue_script(
            'level7-jsonp', plugins_url('/assets/js/frontend/jquery.jsonp.js', L7P_PLUGIN_FILE), array('jquery')
        );
        wp_enqueue_script(
            'level7-forms', plugins_url('/assets/js/frontend/forms.js', L7P_PLUGIN_FILE), array('jquery', 'jquery-ui-dialog')
        );
        wp_enqueue_script(
            'jquery-loading-indicator', plugins_url('/assets/js/frontend/jquery.loading-indicator.min.js', L7P_PLUGIN_FILE), array('jquery')
        );
    }
    
    public function filter_wp_title($title, $sep)
    {
        $product_name = l7p_get_web_product_settings('name');
        
        if (l7p_is_manual_chapter_page()) {
            $chapter_title = l7p_get_chapter('chapter');
            return sprintf("%s Manual - %s", $product_name, $chapter_title);
        }
        
        if (l7p_is_rates_country_page()) {
            $country_name = l7p_get_country_name_from_query();
            return sprintf("%s Call Rates - %s", $product_name, $country_name);
        }
        
        if (l7p_is_telephone_numbers_country_page()) {
            $country_name = l7p_get_country_name_from_query();
            return sprintf("%s Telephone Numbers - %s", $product_name, $country_name);
        }
        return $title;
    }

    // recent posts widget order
    public function filter_recent_posts_widget_parameters($params)
    {
        $params['orderby'] = 'date';
        $params['order'] = 'DESC';
        
        return $params;
    }
}

return new L7P_Frontend();
