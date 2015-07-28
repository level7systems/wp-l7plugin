<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class L7P_Query
{

    public $query_vars = array(
        'currency',
        'city',
        'country',
        'state',
        'group',
        'model',
        'chapter',
        'buy',
        'toll_free',
        'os',
        'token',
        'extini',
        'email'
    );

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

            if (l7p_has_currency($selected_currency)) {
                l7p_update_session('currency', $selected_currency);
                return L7P()->query->redirect_to_currency();
            }
        }
    }

    public function currency_template_redirect()
    {
        global $wp_query;

        $page = null;
        if (isset($wp_query->query_vars['pagename'])) {
            $page = l7p_get_page_by_pagename($wp_query->query_vars['pagename']);
        }

        // alternate for page_id
        if (isset($wp_query->query_vars['page_id']) && $wp_query->query_vars['page_id']) {

            $page = get_post($wp_query->query_vars['page_id']);

            // TODO: to be refactored
            // get original page
            // support for WPML plugin
            if (function_exists('icl_object_id') && $page) {

                global $sitepress;

                // if default lang is different than current lang
                if ($sitepress->get_default_language() != l7p_get_locale()) {

                    $original_page_id = icl_object_id($page->ID, 'page', false, $sitepress->get_default_language());
                    // translated page
                    $page = get_post($original_page_id);
                }
            }
        }

        if ($page) {

            // TODO: needs caching
            $currency_redirect_ids = l7p_get_option('currency_redirect_ids');

            $currency = strtolower(l7p_get_currency());
            if (isset($wp_query->query_vars[$currency])) {
                return;
            }

            if (!in_array($page->ID, $currency_redirect_ids)) {
                return;
            }

            return $this->redirect_to_currency();
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

        l7p_update_option('subscription_page_id', 2210);

//        l7p_pre($query->query_vars);

        $page_name = $query->query_vars['name'];
        $post_type = 'l7p_page';

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

            if (!$query->query_vars['currency']) {
                return $this->redirect_to_currency();
            }

            if (isset($query->query_vars['city'])) {

                // buying phone number for city
                if (isset($query->query_vars['buy'])) {

                    $country_code = l7p_get_country_code_from_query();
                    $city = l7p_get_city_name_from_query();

                    if (isset($query->query_vars['toll_free'])) {
                        $country_code .= '-Toll-Free';
                    }

                    l7p_update_session('extini', 'DdiAddWindow("' . $country_code . '","' . ucwords($city) . '");');

                    return $this->redirect_to_login();
                }
            }

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

            // skip web_product has disabled shop option
            if (!l7p_get_web_product_settings('has_shop')) {
                return;
            }

            if (!$query->query_vars['currency']) {
                return $this->redirect_to_currency();
            }

            if (isset($query->query_vars['model'])) {

                if (!l7p_has_phone($query->query_vars['model'])) {
                    return $this->error_404();
                }

                // buying phone
                if (isset($query->query_vars['buy'])) {

                    $phone = l7p_get_phone();
                    l7p_update_session('extini', 'PhonesGridWindow(); PhoneBuyWindowInit("' . $phone['pricelist_item_id'] . '");');
                    return $this->redirect_to_login();
                }

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
        } else if ($page_name == 'download') {

            if (isset($query->query_vars['os'])) {
                // download os
                $url = l7p_get_download_url($query->query_vars['os']);

                return l7p_redirect($url);
            }

            return $this->error_404();
        } else if ($page_name == 'confirmation') {

            if (isset($query->query_vars['token'])) {

                $response = l7p_confirm_account($query->query_vars['token']);
                if ($response['success']) {
                    l7p_set_success_flash_message($response['info']);
                } else {
                    l7p_set_error_flash_message(__('Invalid confirmation token.'));
                }

                return $this->redirect_to_login();
            }

            return $this->error_404();
        } else if ($page_name == 'activation') {

            if (isset($query->query_vars['token'])) {

                l7p_update_session('activation_token', $query->query_vars['token']);
                if (isset($_GET['message'])) {
                    l7p_set_success_flash_message($_GET['message']);
                }

                return $this->redirect_activation();
            } else {
                return $this->error_404();
            }
        } else if ($page_name == 'reset') {

            if (isset($query->query_vars['token'])) {

                $response = l7p_verify_reset_token($query->query_vars['token']);
                if ($response['success']) {
                    l7p_update_session('reset_token', $response['token']);
                    return $this->redirect_to_one_time_login();
                }

                l7p_set_error_flash_message(__($response['info']));
                return $this->redirect_to_login();
            }
            return $this->error_404();
        } else if ($page_name == 'resend') {

            if (isset($query->query_vars['email'])) {

                $response = l7p_ressend_confirmation_email($query->query_vars['email']);
                if ($response['success']) {
                    l7p_update_session('subscription_token', $response['token']);
                    return $this->redirect_to_one_time_login();
                } else {
                    l7p_set_error_flash_message(__($response['info']));
                }

                return $this->redirect_to_login();
            }
            return $this->error_404();
        } else if ($page_name == 'subscription') {

            if (isset($query->query_vars['token'])) {

                $response = l7p_verify_subscription_token($query->query_vars['token']);

                if ($response['success']) {
                    l7p_update_session('subscription_token', $query->query_vars['token']);
                    l7p_update_session('is_subscribed', $response['is_subscribed']);
                    l7p_set_success_flash_message(__($response['info']));

                    return $this->redirect_to_subscription();
                }
            }
            return $this->error_404();
        } else if ($page_name == 'ppc') {

            if (isset($query->query_vars['token'])) {

                $response = l7p_register_ppc_click($query->query_vars['token']);
                if ($response['success']) {
                    return l7p_redirect($response['redirect']);
                }
            }
            return $this->error_404();
        } else if ($page_name == 'extini') {

            if (isset($query->query_vars['extini'])) {

                if ($query->query_vars['extini'] == 'newticket') {
                    l7p_update_session('extini', "SupportSubmitWindow();");
                } else if (preg_match('/ticket\-([0-9]+)/', $query->query_vars['extini'], $m)) {
                    $ticket_id = $m[1];
                    l7p_update_session('extini', "SupportSubmitReplyWindow($ticket_id);");
                }
                return $this->redirect_to_login();
            }
            return $this->error_404();
        } else {
            $page_name = null;
        }

        if ($page_name) {

            $page = get_post(l7p_get_option(sprintf("%s_page_id", $page_name)));

            // TODO: to be refactored
            // support for WPML plugin
            if (function_exists('icl_object_id')) {

                global $sitepress;

                // if default lang is different than current lang
                if ($sitepress->get_default_language() != l7p_get_locale()) {

                    $translated_page_id = icl_object_id($page->ID, 'l7p_page', false);

                    // if translation does not exist
                    if (is_null($translated_page_id)) {
                        // errorr 404
                        return $this->error_404();
                    }
                    // translated page
                    $page = get_post($translated_page_id);
                }
            }

            // query for given post
            $query->is_page = true;
            $query->is_home = false;
            $query->is_singular = true;
            $query->set('post_type', $post_type);
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

    public function redirect_to_login()
    {
        $uri = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $page = get_post(l7p_get_option('login_page_id'));

        return l7p_redirect(sprintf("http://%s/%s/%s", $_SERVER['HTTP_HOST'], strtolower(l7p_get_locale()), $page->post_name));
    }

    public function redirect_to_one_time_login()
    {
        $uri = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $page = get_post(l7p_get_option('one_time_login_page_id'));

        return l7p_redirect(sprintf("http://%s/%s/%s", $_SERVER['HTTP_HOST'], strtolower(l7p_get_locale()), $page->post_name));
    }

    public function redirect_to_subscription()
    {
        $uri = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $page = get_post(l7p_get_option('subscription_page_id'));

        return l7p_redirect(sprintf("http://%s/%s/%s", $_SERVER['HTTP_HOST'], strtolower(l7p_get_locale()), $page->post_name));
    }

    public function redirect_activation()
    {
        $uri = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $page = get_post(l7p_get_option('activation_page_id'));

        return l7p_redirect(sprintf("http://%s/%s/%s", $_SERVER['HTTP_HOST'], strtolower(l7p_get_locale()), $page->post_name));
    }

    /**
     * Add endpoints for query vars
     */
    public function add_rewrite_rules()
    {
        $permalink = l7p_get_permalinks();
        $cultures = l7p_get_cultures();
        $currencies = l7p_get_currencies();
        $currencies_rule = strtolower(implode("|", $currencies));

        foreach ($cultures as $culture) {

            // downloads
            add_rewrite_rule("download-for-(windows|mac-osx|linux)/?$", 'index.php?name=download&os=$matches[1]', 'top');
            // rates
            add_rewrite_rule(sprintf("%s/([A-Z]{1}[\w\-\+]+)/?(%s)?$", $permalink[$culture]['rates'], $currencies_rule), 'index.php?name=rates&country=$matches[1]&currency=$matches[2]', 'top');
            // virtual numbers
            add_rewrite_rule(sprintf("%s/([A-Z]{1}[\w\-\+]+)/?(%s)?$", $permalink[$culture]['telephone_numbers'], $currencies_rule), 'index.php?name=telephone_numbers&country=$matches[1]&currency=$matches[2]', 'top');
            add_rewrite_rule(sprintf("%s/([A-Z]{1}[\w\-\+]+)/([\w\-\+]+)/?(%s)?$", $permalink[$culture]['telephone_numbers'], $currencies_rule), 'index.php?name=telephone_numbers&country=$matches[1]&state=$matches[2]&currency=$matches[3]', 'top');
            // buy virtual number /:permalink/:country/:city/:currency/buy
            add_rewrite_rule(sprintf("%s/([A-Z]{1}[\w\-\+]+)/([A-Z]{1}[\w\-\+]+)/(%s)/buy$", $permalink[$culture]['telephone_numbers'], $currencies_rule), 'index.php?name=telephone_numbers&country=$matches[1]&city=$matches[2]&currency=$matches[3]&buy=1', 'top');
            // buy virtual number /:permalink/:country/toll-free/:city/:currency/buy
            add_rewrite_rule(sprintf("%s/([A-Z]{1}[\w\-\+]+)/toll-free/([0-9]{3,4})/(%s)/buy$", $permalink[$culture]['telephone_numbers'], $currencies_rule), 'index.php?name=telephone_numbers&country=$matches[1]&city=$matches[2]&currency=$matches[3]&buy=1&toll_free=1', 'top');

            // if web_product has shop enabled
            if (l7p_get_web_product_settings('has_shop')) {
                // hardware
                add_rewrite_rule(sprintf("%s/([A-Z]{1}[\w\-\+]+)/?(%s)?$", $permalink[$culture]['hardware'], $currencies_rule), 'index.php?name=hardware&group=$matches[1]&currency=$matches[2]', 'top');
                add_rewrite_rule(sprintf("%s/([A-Z]{1}[\w\-\+]+)/([\w\-\+]+)/?(%s)?$", $permalink[$culture]['hardware'], $currencies_rule), 'index.php?name=hardware&group=$matches[1]&model=$matches[2]&currency=$matches[3]', 'top');
                // buy phone /:permalink/:group/:model/:currency/buy
                add_rewrite_rule(sprintf("%s/([A-Z]{1}[\w\-\+]+)/([\w\-\+]+)/(%s)/buy$", $permalink[$culture]['hardware'], $currencies_rule), 'index.php?name=hardware&group=$matches[1]&model=$matches[2]&currency=$matches[3]&buy=1', 'top');
            }

            // manual
            add_rewrite_rule(sprintf("%s/([\w\-\+]+)/?$", $permalink[$culture]['manual']), 'index.php?name=manual&chapter=$matches[1]', 'top');
        }

        // account confirmation
        add_rewrite_rule("c/([a-zA-Z0-9]{6,})$", 'index.php?name=confirmation&token=$matches[1]', 'top');

        // account activation 
        add_rewrite_rule("activate/([a-zA-Z0-9]{6,})$", 'index.php?name=activation&token=$matches[1]', 'top');
        // password reset
        add_rewrite_rule("reset/([a-zA-Z0-9]{20,})$", 'index.php?name=reset&token=$matches[1]', 'top');
        // resend confirmation email
        add_rewrite_rule("resend-confirmation-email/([-\._@a-zA-Z0-9]{4,})$", 'index.php?name=resend&email=$matches[1]', 'top');
        // extinit actions
        add_rewrite_rule("xi/([-a-zA-Z0-9]{4,20})$", 'index.php?name=extini&extini=$matches[1]', 'top');
        // subscription
        add_rewrite_rule("profile/([a-zA-Z0-9]{20,})$", 'index.php?name=subscription&token=$matches[1]', 'top');
        // ppc
        add_rewrite_rule("ppc/([0-9]{1,10})$", 'index.php?name=ppc&token=$matches[1]', 'top');

        // add endpoint for pages for each currency
        foreach ($currencies as $currency) {
            add_rewrite_endpoint(strtolower($currency), EP_PAGES);
        }

        // TODO: to be removed
        flush_rewrite_rules();
    }
}

return new L7P_Query();
