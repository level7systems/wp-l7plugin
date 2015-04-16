<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class L7P_Query
{

    public $query_vars = array('country', 'state', 'group', 'model', 'chapter');

    public function __construct()
    {
        add_action('init', array($this, 'add_rewrite_rules'));

        if (!is_admin()) {
            add_filter('query_vars', array($this, 'add_query_vars'), 0);
            add_action('pre_get_posts', array($this, 'pre_get_posts'));
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
        foreach ($this->query_vars as $key) {
            $vars[] = $key;
        }

        return $vars;
    }

    public function pre_get_posts($query)
    {
        // we only want to affect the main query
        if (!$query->is_main_query()) {
            return;
        }

//        echo '<pre>';
//        print_r($query->query_vars);
//        echo '</pre>';

        $page_name = $query->query_vars['name'];

        if ($page_name == "rates") {

            if (isset($query->query_vars['country'])) {
                // call rates country
                $page_name .= "_country";
            } else {
                return $this->error_404();
            }
        } else if ($page_name == 'telephone_numbers') {

            if (isset($query->query_vars['state'])) {
                // phone number state
                $page_name .= "_state";
            } else if (isset($query->query_vars['country'])) {
                // phone numbers country
                $page_name .= "_country";
            } else {
                // errorr 404
                return $this->error_404();
            }
        } else if ($page_name == 'hardware') {

            if (isset($query->query_vars['model'])) {
                // hardware model
                $page_name .= "_model";
            } else if (isset($query->query_vars['group'])) {
                // hardware group
                $page_name .= "_group";
            } else {
                // errorr 404
                return $this->error_404();
            }
        } else if ($page_name == 'manual') {

            $parts = array_filter(explode("/", $query_value));
            if (count($parts) == 1) {
                // manual chapter
                $page_name .= "_chapter";
            } else {
                return $this->error_404();
            }
        } else {
            $page_name = null;
        }

        if ($page_name) {

            // query for given post
            $query->is_page = true;
            $query->is_home = false;
            $query->is_singular = true;
            $query->set('post_type', 'level7platform_page');
            $query->set('name', $page_name);
        }
    }

    private function error_404()
    {
        global $wp_query;

        $wp_query->set_404();
        status_header(404);
    }

    /**
     * Add endpoints for query vars
     */
    public function add_rewrite_rules()
    {
        $permalink = l7p_get_permalinks();

        // rates
        add_rewrite_rule(sprintf("^%s/([\w\-\+]+)/?$", $permalink['rates']), 'index.php?name=rates&country=$matches[1]', 'top');
        // wirtual numbers
        add_rewrite_rule(sprintf("^%s/([\w\-\+]+)/?$", $permalink['telephone_numbers']), 'index.php?name=telephone_numbers&country=$matches[1]', 'top');
        add_rewrite_rule(sprintf("^%s/([\w\-\+]+)/([\w\-\+]+)/?$", $permalink['telephone_numbers']), 'index.php?name=telephone_numbers&country=$matches[1]&state=$matches[1]', 'top');
        // hardware
        add_rewrite_rule(sprintf("^%s/([\w\-\+]+)/?$", $permalink['hardware']), 'index.php?name=hardware&group=$matches[1]', 'top');
        add_rewrite_rule(sprintf("^%s/([\w\-\+]+)/([\w\-\+]+)/?$", $permalink['hardware']), 'index.php?name=hardware&group=$matches[1]&model=$matches[2]', 'top');
        // manual
        add_rewrite_rule(sprintf("^%s/([\w\-\+]+)/?$", $permalink['manual']), 'index.php?name=manual&chapter=$matches[1]', 'top');
    }
}

return new L7P_Query();
