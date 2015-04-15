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

    public $query_vars = array();

    public function __construct()
    {
        add_action('wp_enqueue_scripts', array($this, 'styles'));
        add_action('wp_enqueue_scripts', array($this, 'scripts'));
        add_action('init', array($this, 'add_endpoints'));

        add_filter('query_vars', array($this, 'add_query_vars'), 0);
        // TODO: to be fixed ?
        add_action('pre_get_posts', array($this, 'pre_get_posts'));

        $this->init_query_vars();
    }

    /**
     * Enqueue styles
     */
    public function styles()
    {
        wp_enqueue_style(
            'level7-login', plugins_url('/assets/css/level7-login.css', L7P_PLUGIN_FILE)
        );

        wp_enqueue_style(
            'level7-jquery-ui-css', plugins_url('/assets/css/jquery-ui.css', L7P_PLUGIN_FILE)
        );
    }

    /**
     * Enqueue scripts
     */
    public function scripts()
    {
        wp_enqueue_script(
            'level7-login', plugins_url('/assets/js/level7-login.js', L7P_PLUGIN_FILE), array('jquery', 'jquery-ui-dialog')
        );
    }

    /**
     * Init query vars by loading options.
     */
    public function init_query_vars()
    {
        $permalinks = get_option(Level7Platform::OPTION_PERMALINKS);

        // Query vars to add to WP
        $this->query_vars = array(
            'rates' => $permalinks['rates'],
            'telephone_numbers' => $permalinks['telephone_numbers'],
            'hardware' => $permalinks['hardware'],
            'manual' => $permalinks['manual'],
        );
    }

    /**
     * Add endpoints for query vars
     */
    public function add_endpoints()
    {
        foreach ($this->query_vars as $key => $var) {
            add_rewrite_endpoint($var, EP_ROOT | EP_PAGES);
        }
    }

    /**
     * add_query_vars function.
     *
     * @access public
     * @param array $vars
     * @return array
     */
    public function add_query_vars($vars)
    {
        foreach ($this->query_vars as $key => $var) {
            $vars[] = $key;
        }

        return $vars;
    }

    /**
     * Get query vars
     *
     * @return array
     */
    public function get_query_vars()
    {
        return $this->query_vars;
    }

    public function pre_get_posts($query)
    {
        // we only want to affect the main query
        if (!$query->is_main_query()) {
            return;
        }
        
        $permalinks = get_option(Level7Platform::OPTION_PERMALINKS, array());

        foreach ($permalinks as $page_name => $value) {

            if (isset($query->query_vars[$value])) {

                $query_value = $query->query_vars[$value];
                if ($page_name == "rates") {

                    $parts = array_filter(explode("/", $query_value));
                    if (count($parts) == 1) {
                        // call rates country
                        $country = $query_value;
                        $page_name .= "_country";
                    } else {
                        return $this->error_404();
                    }
                } else if ($page_name == 'telephone_numbers') {

                    $parts = array_filter(explode("/", $query_value));
                    if (count($parts) == 1) {
                        // phone number country
                        $country = $query_value;
                        $page_name .= "_country";
                    } else if (count($parts) == 2) {
                        // phone numbers state
                        $country = $parts[0];
                        $state = $parts[1];
                        $page_name .= "_state";
                    } else {
                        // errorr 404
                        return $this->error_404();
                    }


                    // is country
                    /*
                      if (is_country($query_value)) {

                      }

                      if (is_state($query_value)) {

                      }
                     */
                } else if ($page_name == 'hardware') {
                    
                    $parts = array_filter(explode("/", $query_value));
                    if (count($parts) == 1) {
                        // hardware group
                        $group = $query_value;
                        $page_name .= "_group";
                    } else if (count($parts) == 2) {
                        // hardware model
                        $model = $parts[0];
                        $state = $parts[1];
                        $page_name .= "_model";
                    } else {
                        // errorr 404
                        return $this->error_404();
                    }
                    
                } else if ($page_name == 'manual') {
                    
                    $parts = array_filter(explode("/", $query_value));
                    if (count($parts) == 1) {
                        // manual chapter
                        $chapter = $query_value;
                        $page_name .= "_chapter";
                    } else {
                        return $this->error_404();
                    }
                }

                // query for given post
                $query->is_page = true;
                $query->is_home = false;
                $query->is_singular = true;
                $query->set('post_type', 'level7platform_page');
                $query->set('name', $page_name);
            }
        }

    }

    private function error_404()
    {
        global $wp_query;
        
        $wp_query->set_404();
        status_header(404);
    }
}

return new L7P_Frontend();
