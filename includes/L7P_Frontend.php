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

        add_filter('wp_title', array($this, 'filter_wp_title'), 20, 2);
        add_filter('pre_get_document_title', array($this, 'filter_wp_title'), 20, 2);
        add_filter('widget_posts_args', array($this, 'filter_recent_posts_widget_parameters'));
        // removes WP shortlinks from <heade>
        remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0);
		// rewrite for plugin pages Yoast SEO canonicals tag
		add_filter( 'wpseo_canonical', function ( $canonical ) {
			$l7p_pages = array(
				(int) l7p_get_option( 'pricing_page_id' ),
				(int) l7p_get_option( 'rates_page_id' ),
				(int) l7p_get_option( 'telephone_numbers_page_id' ),
				(int) l7p_get_option( 'hardware_page_id' ),
				(int) l7p_get_option( 'support_page_id' ),
				(int) l7p_get_option( 'login_page_id' ),
				(int) l7p_get_option( 'one_time_login_page_id' ),
				(int) l7p_get_option( 'recover_page_id' ),
				(int) l7p_get_option( 'subscription_page_id' ),
				(int) l7p_get_option( 'register_page_id' ),
				(int) l7p_get_option( 'manual_search_page_id' )
			);

			$pages_with_currency_redirect_ids = l7p_get_option( 'currency_redirect_ids' );
			array_merge( $l7p_pages, $pages_with_currency_redirect_ids );
			if ( get_post_type() === 'l7p_page' || in_array( (int) get_the_ID(), $l7p_pages, true ) ) {
				$home_url = get_home_url();
				$uri = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );
				if ( preg_match( '/\.(es|it|de|at)$/', $home_url ) ) {
					$uri = preg_replace('/(usd|eur|gbp)$/', $uri,'eur');
					//$l7_default_currency = 'eur';
				} else {
					//default currency is USD
					$uri = preg_replace('/(usd|eur|gbp)$/', $uri,'usd');
					//$l7_default_currency = 'usd';
				}

				return $home_url . $uri;
			}

			return $canonical;
		} );
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
        $admin_ajax_url = admin_url( 'admin-ajax.php');  
        wp_localize_script( 'level7-forms', 'ajax_options', array('admin_ajax_url' => $admin_ajax_url) ); 
    }
    
    public function filter_wp_title($title, $sep)
    {
        $product_name = l7p_get_web_product_settings('name');
        
        if (l7p_is_manual_chapter_page()) {
            $chapter_title = l7p_get_chapter('chapter');
            $chapter_name = l7p_get_chapter('name');
            return sprintf("%s %s - %s", $product_name, $chapter_name,  $chapter_title);
        }
        
        if (l7p_is_rates_country_page()) {
            $country_name = l7p_get_country_name_from_query();
            return sprintf(__("%s Call Rates - %s", 'level7platform'), $country_name, $product_name );
        }
        
        if (l7p_is_telephone_numbers_country_page()) {
            $country_name = l7p_get_country_name_from_query();
            $state_name = l7p_get_state_name_from_query();
            if ($state_name) {
                return sprintf(__("%s, %s Telephone Numbers - %s", 'level7platform'), $state_name, $country_name, $product_name);
            } else {
                return sprintf(__("%s Telephone Numbers - %s", 'level7platform'), $country_name, $product_name);
            }
        }

        if (l7p_is_hardware_group_page()) {
            $group_name = l7p_get_phone_group_name_from_query();
            return sprintf("%s - %s", __($group_name, 'level7platform'), $product_name);
        }
        
        if (l7p_is_hardware_phone_details_page()) {
            $group_name = l7p_get_phone_group_name_from_query();
            $phone_name = l7p_get_phone_name_from_query();
            return sprintf("%s - %s - %s", __($group_name, 'level7platform'), $phone_name, $product_name);
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
