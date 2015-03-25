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
        echo "init query vars <br />";
        
        $permalinks = get_option(Level7Platform::OPTION_PERMALINKS);
        
        // Query vars to add to WP
        $this->query_vars = array(
            'rates'             => $permalinks['rates_page_slug'],
            'telephone_numbers' => $permalinks['virtual_numbers_page_slug'],
            'hardware'          => $permalinks['hardware_page_slug'],
            'manual'            => $permalinks['manual_page_slug'],
        );
        
        print_r($this->query_vars);
    }

    /**
     * Add endpoints for query vars
     */
    public function add_endpoints()
    {
        echo "add endpoints <br/>";
        foreach ($this->query_vars as $key => $var) {
            add_rewrite_endpoint($var, EP_ROOT | EP_PAGES);
            //add_rewrite_endpoint($var, EP_ALL);
        }
        
        // TODO: to be removed
        flush_rewrite_rules();
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
        echo "add query vars <br/>";

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
        
        $permalinks = get_option(Level7Platform::OPTION_PERMALINKS);

        echo "pre_get_posts <br/>";

        echo '<pre>';
        print_r($query);
        echo '</pre>';

        // TODO: to be fixed
        // query_posts('pagename=rates');
    }
}

return new L7P_Frontend();
