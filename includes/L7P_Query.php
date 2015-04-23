<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class L7P_Query
{

    public $query_vars = array('currency', 'country', 'state', 'group', 'model', 'chapter');

    public function __construct()
    {
        add_action('init', array($this, 'add_rewrite_rules'));
        add_action('init', array($this, 'currency_change'));
        add_action('template_redirect', array($this, 'currency_template_redirect'));

        if (!is_admin()) {
            add_filter('query_vars', array($this, 'add_query_vars'), 0);
            add_action('pre_get_posts', array($this, 'pre_get_posts'));
        }
    }

    public function currency_change()
    {
        if (l7p_is_post_request() && array_key_exists('currency', $_POST)) {

            // verify allowed currencied
            $selected_currency = strtoupper($_POST['currency']);
            $currencies = l7p_get_currencies();
            if (in_array($selected_currency, $currencies)) {
                l7p_update_session('currency', $selected_currency);
                return L7P()->query->redirect_to_currency();
            }
        }
    }
    
    public function currency_template_redirect()
    {
        global $wp_query;
        
        // l7p_pre($wp_query->query_vars);
        
        // TODO: currency handling for pages
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

        // needed for endpoint
        foreach (l7p_get_currencies() as $currency) {
            $vars[] = strtolower($currency);
        }

        return $vars;
    }

    public function pre_get_posts($query)
    {
        // we only want to affect the main query
        if (!$query->is_main_query()) {
            return;
        }

        // set locale based on url
        if (isset($query->query_vars['currency']) && $query->query_vars['currency']) {
            $currency = strtoupper($query->query_vars['currency']);
            $currencies = l7p_get_currencies();
            if (in_array($currency, $currencies)) {
                l7p_update_session('currency', $currency);
            }
        }

//        l7p_pre($query->query_vars);

        $page_name = $query->query_vars['name'];

        if ($page_name == "rates") {

            if (!$query->query_vars['currency']) {
                return $this->redirect_to_currency();
            }

            if (!isset($query->query_vars['country'])) {
                return $this->error_404();
            }

            if (!l7p_has_country($query->query_vars['country'])) {
                return $this->error_404();
            }

            // call rates country
            $page_name .= "_country";
        } else if ($page_name == 'telephone_numbers') {

            if (isset($query->query_vars['state'])) {
                // phone number state
                $page_name .= "_country";
            } else if (isset($query->query_vars['country'])) {
                // phone numbers country
                if (!l7p_has_country($query->query_vars['country'])) {
                    return $this->error_404();
                }

                if (l7p_get_country_code_from_query() == 'US') {
                    $page_name .= "_state";
                } else {
                    $page_name .= "_country";
                }
            } else {
                // errorr 404
                return $this->error_404();
            }
        } else if ($page_name == 'hardware') {

            if (!$query->query_vars['currency']) {
                return $this->redirect_to_currency();
            }

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

            if (isset($query->query_vars['chapter'])) {
                // manual chapter
                $page_name .= "_chapter";
            } else {
                return $this->error_404();
            }
        } else {
            $page_name = null;
        }

        if ($page_name) {

            // TODO: refactor
            $page = get_post(l7p_get_option(sprintf("%s_page_id", $page_name)));

            // query for given post
            $query->is_page = true;
            $query->is_home = false;
            $query->is_singular = true;
            $query->set('post_type', 'level7platform_page');
            $query->set('name', $page->post_name);
        }
    }

    private function error_404()
    {
        global $wp_query;

        $wp_query->set_404();
        status_header(404);
    }

    public function redirect_to_currency()
    {
        $uri = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $currencies = l7p_get_currencies();
        foreach ($currencies as $currency) {
            $uri = preg_replace(sprintf('#/%s/$#', strtolower($currency)), '/', $uri);
        }

        return l7p_redirect(sprintf("http://%s/%s", $uri, strtolower(l7p_get_currency())));
    }

    /**
     * Add endpoints for query vars
     */
    public function add_rewrite_rules()
    {
        $permalink = l7p_get_permalinks();
        $currencies = l7p_get_currencies();
        $currencies_rule = strtolower(implode("|", $currencies));

        // rates
        add_rewrite_rule(sprintf("%s/([\w\-\+]+)/?(%s)?$", $permalink['rates'], $currencies_rule), 'index.php?name=rates&country=$matches[1]&currency=$matches[2]', 'top');
        // wirtual numbers
        add_rewrite_rule(sprintf("%s/([\w\-\+]+)/?$", $permalink['telephone_numbers']), 'index.php?name=telephone_numbers&country=$matches[1]&lang=en', 'top');
        add_rewrite_rule(sprintf("%s/([\w\-\+]+)/([\w\-\+]+)/?$", $permalink['telephone_numbers']), 'index.php?name=telephone_numbers&country=$matches[1]&state=$matches[1]&lang=en', 'top');
        // hardware
        add_rewrite_rule(sprintf("%s/([\w\-\+]+)/?(%s)?$", $permalink['hardware'], $currencies_rule), 'index.php?name=hardware&group=$matches[1]&currency=$matches[2]', 'top');
        add_rewrite_rule(sprintf("%s/([\w\-\+]+)/([\w\-\+]+)/?(%s)?$", $permalink['hardware'], $currencies_rule), 'index.php?name=hardware&group=$matches[1]&model=$matches[2]&currency=$matches[3]', 'top');
        // manual
        add_rewrite_rule(sprintf("%s/([\w\-\+]+)/?$", $permalink['manual']), 'index.php?name=manual&chapter=$matches[1]', 'top');

        // TODO: add endpoint for pages for each currency
        foreach ($currencies as $currency) {
            add_rewrite_endpoint(strtolower($currency), EP_PAGES);
        }

        flush_rewrite_rules();
    }
}

return new L7P_Query();
