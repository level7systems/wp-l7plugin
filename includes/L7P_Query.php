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
        'email',
        'user_id',
        'year'
    );

    public function __construct()
    {
        add_action('init', array($this, 'add_rewrite_rules'));
        add_action('init', array($this, 'currency_change'));
        
        add_action('shutdown', array($this, 'clear_flash_messages'));
        
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
    
    /**
     * Clear flash messages
     */
    public function clear_flash_messages()
    {
        $messages = l7p_get_flash_messages();
        foreach ($messages as $i => $message) {
            
            if ($message['lifetime'] == 0) {
                unset($messages[$i]);
                continue;
            }
            
            if ($message['lifetime'] > 0) {
                $messages[$i]['lifetime'] -= 1;
            }
        }
        
        l7p_set_flash_messages($messages);
    }

    /**
     * @global type $wp_query
     * @global type $sitepress
     * @return type
     */
    public function currency_template_redirect()
    {
        global $wp_query;
        
        if (isset($wp_query->query_vars['currency'])) {
            return;
        }

        $page = null;
        if (isset($wp_query->query_vars['pagename'])) {
            $page = l7p_get_page_by_pagename($wp_query->query_vars['pagename']);
        }

        // alternate for page_id
        if (isset($wp_query->query_vars['page_id']) && $wp_query->query_vars['page_id']) {

            $page = get_post($wp_query->query_vars['page_id']);
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

            $currency_redirect_ids = l7p_get_option('currency_redirect_ids');
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
        $currencies = l7p_get_currencies();
        foreach ($currencies as $currency) {
            $currency = strtolower($currency);
            if (isset($query->query_vars[$currency])) {
                $query->query_vars['currency'] = $currency;
            }
        }

        // set locale based on url
        if (isset($query->query_vars['currency']) && $query->query_vars['currency']) {
            $currency = strtoupper($query->query_vars['currency']);
            if (in_array($currency, $currencies)) {
                l7p_update_session('currency', $currency);
            }
        }
        
        // pagename
        $wp_pagename = $query->query_vars['pagename'];
        $pagename = $query->query_vars['name'];
        
        
        // redirect to currency page
        if (in_array($wp_pagename, array('pricing', 'rates', 'telephone-numbers', 'hardware')) && !$query->query_vars['currency']) {
            return $this->redirect_to_currency();
        }
        
        // redirect to currency page
        if (in_array($pagename, array('pricing', 'rates', 'telephone_numbers', 'hardware')) && !$query->query_vars['currency']) {
            return $this->redirect_to_currency();
        }
        if (in_array($pagename, array('release-notes'))) {
            return $this->redirect_to_release_note();
        }
        
        if ($pagename == "rates") {
            
            if (!isset($query->query_vars['country'])) {
                return $this->error_404();
            }

            if (!l7p_has_country($query->query_vars['country'])) {
                return $this->error_404();
            }

            // call rates country
            $pagename .= "_country";
        } else if ($pagename == 'telephone_numbers') {

            if (isset($query->query_vars['city'])) {

                // buying phone number for city
                if (isset($query->query_vars['buy'])) {

                    $country_code = l7p_get_country_code_from_query();
                    $city = l7p_get_city_name_from_query();

                    if (isset($query->query_vars['toll_free'])) {
                        $country_code .= '-Toll-Free';
                    }

                    l7p_update_session('extini', 'DdiAddWindow("' . $country_code . '","' . ucwords($city) . '");');
                    
                    l7p_set_success_flash_message(__("Please login to complete your purchase.", 'level7platform'));

                    return $this->redirect_to_login();
                }
            }

            if (isset($query->query_vars['state'])) {
                // phone number state
                $pagename .= "_country";
            } else if (isset($query->query_vars['country'])) {
                // phone numbers country
                if (!l7p_has_country($query->query_vars['country'])) {
                    return $this->error_404();
                }

                if (l7p_get_country_code_from_query() == 'US') {
                    $pagename .= "_state";
                } else {
                    $pagename .= "_country";
                }
            } else {
                // errorr 404
                return $this->error_404();
            }
        } else if ($pagename == 'hardware') {

            // skip web_product has disabled shop option
            if (!l7p_get_web_product_settings('has_shop')) {
                return;
            }

            if (isset($query->query_vars['model'])) {

                if (!l7p_has_phone($query->query_vars['model'])) {
                    return $this->error_404();
                }

                // buying phone
                if (isset($query->query_vars['buy'])) {
                    $phone = l7p_get_phone();
                    l7p_update_session('extini', 'PhonesGridWindow(); PhoneBuyWindowInit("' . $phone['pricelist_item_id'] . '");');
                    
                    l7p_set_success_flash_message(__("Please login to complete your purchase.", 'level7platform'));
                    
                    return $this->redirect_to_login();
                }

                // hardware model
                $pagename .= "_model";
            } else if (isset($query->query_vars['group'])) {
                // hardware group
                $pagename .= "_group";
            } else {
                // errorr 404
                return $this->error_404();
            }
        } else if ($pagename == 'manual') {

            if (isset($query->query_vars['chapter'])) {
                // manual chapter
                $pagename .= "_chapter";
            } else {
                return $this->error_404();
            }
        } else if ($pagename == 'download') {

            if (isset($query->query_vars['os'])) {
                // download os
                $url = l7p_get_download_url($query->query_vars['os']);

                return l7p_redirect($url);
            }

            return $this->error_404();
        } else if ($pagename == 'loginas') {

            if (isset($query->query_vars['token']) && isset($query->query_vars['user_id'])) {

                l7p_setcookie(sprintf("%s.auth", l7p_get_web_product_settings('app_key')), json_encode(array(
                    'user_id' => $query->query_vars['user_id'],
                    'user_token' => $query->query_vars['token'],
                )));
                
                return $this->redirect_to_app();
            }

            return $this->error_404();
        } else if ($pagename == 'confirmation') {

            if (isset($query->query_vars['token'])) {

                if (defined('L7_CONFIG_PATH')) {
                    l7p_setcookie('confirmation_token', $query->query_vars['token']);
                    return $this->redirect_to_login();
                }

                try {
                    l7p_confirm_account($query->query_vars['token']);
                    l7p_set_success_flash_message(__("Your account is now confirmed. Please use your email address and password to login.", 'level7platform'));
                } catch (RestException $e) {
                    l7p_set_error_flash_message($e->getMessage());
                } catch (Exception $e) {
                    l7p_set_error_flash_message(__('Invalid confirmation token.', 'level7platform'));
                }
                
                return $this->redirect_to_login();
            }

            return $this->error_404();
        } else if ($pagename == 'reset') {

            if (isset($query->query_vars['token'])) {
                l7p_setcookie('reset_token', $query->query_vars['token']);
                return $this->redirect_to_one_time_login();
            }
            return $this->error_404();
        } else if ($pagename == 'resend') {

            if (isset($query->query_vars['email'])) {

                try {
                    l7p_ressend_confirmation_email($query->query_vars['email']);
                    l7p_set_success_flash_message(sprintf(__("Confirmation email sent to %s.", 'level7platform'), $query->query_vars['email']));
                } catch (RestException $e) {
                    l7p_set_error_flash_message($e->getMessage());
                } catch (Exception $e) {
                    l7p_set_error_flash_message(__('Unable to resend confirmation e-mail.', 'level7platform'));
                }

                return $this->redirect_to_login();
            }
            return $this->error_404();
        } else if ($pagename == 'subscription') {

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
        } else if ($pagename == 'ppc') {

            if (isset($query->query_vars['token'])) {

                $landing_page = '';

                if (isset($_SERVER['HTTP_HOST']) && isset($_GET['lp']) && $_GET['lp']) {
                    $landing_page = sprintf("https://%s%s", $_SERVER['HTTP_HOST'], $_GET['lp']);
                }

                $response = l7p_register_ppc_click($query->query_vars['token'], $landing_page);
                if ($response['success']) {
                    l7p_setcookie('xl7ppc', $response['ppc_click_id']);
                    $redirect_url = ($landing_page) ? $landing_page : $response['redirect'];
                    if (isset($_GET['gclid'])) {
                        $redirect_url .= '?gclid=' . $_GET['gclid'];
                    }
                    return l7p_redirect($redirect_url);
                }
            }
            return $this->error_404();
        } else if ($pagename == 'agentclick') {

            if (isset($query->query_vars['token'])) {

                $response = l7p_register_agent_click($query->query_vars['token']);
                if ($response['success']) {
                    l7p_setcookie('xl7a', $response['agent_click_id']);
                    return l7p_redirect($response['redirect']);
                }
            }
            return $this->error_404();
        } else if ($pagename == 'extini') {

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
            $pagename = null;
        }
        
        if ($pagename) {

            $page = get_post(l7p_get_option(sprintf("%s_page_id", $pagename)));

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
            $query->set('post_type', 'l7p_page');
            $query->set('name', $page->post_name);
        }
    }

    private function error_404()
    {
        global $wp_query;
        
        $wp_query->set_404();
        status_header(404);
    }

    /**
     * Redirects to given page with currency suffix
     */
    public function redirect_to_currency()
    {
        $uri = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        if (!l7p_ends_with($uri, '/')) {
            $uri .= '/';
        }

        $currencies = l7p_get_currencies();
        foreach ($currencies as $currency) {
            $uri = preg_replace(sprintf('#/%s/$#', strtolower($currency)), '/', $uri);
        }

        return l7p_redirect(sprintf("%s://%s%s/", l7p_is_ssl() ? 'https' : 'http', $uri, strtolower(l7p_get_currency())), true);
    }

    public function redirect_to_login()
    {
        $page = get_post(l7p_get_option('login_page_id'));
        
        return l7p_redirect(sprintf("%s://%s/%s/", l7p_is_ssl() ? 'https' : 'http', $_SERVER['HTTP_HOST'], $page->post_name));
    }
    
    public function redirect_to_release_note()
    {
        $page = get_post(l7p_get_option('release_note_page_id'));

        return l7p_redirect(sprintf("%s://%s/%s/", l7p_is_ssl() ? 'https' : 'http', $_SERVER['HTTP_HOST'], $page->post_name));
    }
    
    public function redirect_to_app()
    {
        return l7p_redirect(sprintf("%s://%s/app/", l7p_is_ssl() ? 'https' : 'http', $_SERVER['HTTP_HOST']));
    }

    public function redirect_to_one_time_login()
    {
        $page = get_post(l7p_get_option('one_time_login_page_id'));

        return l7p_redirect(sprintf("%s://%s/%s/", l7p_is_ssl() ? 'https' : 'http', $_SERVER['HTTP_HOST'], $page->post_name));
    }

    public function redirect_to_subscription()
    {
        $page = get_post(l7p_get_option('subscription_page_id'));

        return l7p_redirect(sprintf("%s://%s/%s/", l7p_is_ssl() ? 'https' : 'http', $_SERVER['HTTP_HOST'], $page->post_name));
    }

    /**
     * Add endpoints for query vars
     */
    public function add_rewrite_rules()
    {
        
        $permalink = l7p_get_permalinks();
        $currencies = l7p_get_currencies();
        $currencies_rule = strtolower(implode("|", $currencies));

        // downloads
        add_rewrite_rule("download-for-(windows|mac-osx)/?$", 'index.php?name=download&os=$matches[1]', 'top');
        // rates
        // /:permalink/:country/:currency/
        add_rewrite_rule(sprintf("%s/([A-Z]{1}[\w\-\+]+)/?(%s)?$", $permalink['rates'], $currencies_rule), 'index.php?name=rates&country=$matches[1]&currency=$matches[2]', 'top');
        // virtual numbers
        // /:permalink/:phone/:currency/
        add_rewrite_rule(sprintf("%s/([A-Z]{1}[\w\-\+]+)/?(%s)?$", $permalink['telephone_numbers'], $currencies_rule), 'index.php?name=telephone_numbers&country=$matches[1]&currency=$matches[2]', 'top');
        // /:permalink/:phone/:country/:currency/
        add_rewrite_rule(sprintf("%s/([A-Z]{1}[\w\-\+]+)/([\w\-\+]+)/?(%s)?$", $permalink['telephone_numbers'], $currencies_rule), 'index.php?name=telephone_numbers&country=$matches[1]&state=$matches[2]&currency=$matches[3]', 'top');
        // /:permalink/:country/:city/buy/:currency/
        add_rewrite_rule(sprintf("%s/([A-Z]{1}[\w\-\+]+)/([A-Z]{1}[\w\-\+]+)/buy/?(%s)?$", $permalink['telephone_numbers'], $currencies_rule), 'index.php?name=telephone_numbers&country=$matches[1]&city=$matches[2]&currency=$matches[3]&buy=1', 'top');
        // /:permalink/:country/toll-free/:city/buy/:currency/
        add_rewrite_rule(sprintf("%s/([A-Z]{1}[\w\-\+]+)/toll-free/([0-9]{3,4})/buy/?(%s)?$", $permalink['telephone_numbers'], $currencies_rule), 'index.php?name=telephone_numbers&country=$matches[1]&city=$matches[2]&currency=$matches[3]&buy=1&toll_free=1', 'top');

        // if web_product has shop enabled
        if (l7p_get_web_product_settings('has_shop')) {
            // hardware
            // /:permalink/:group/:currency/
            add_rewrite_rule(sprintf("%s/([A-Z]{1}[\w\-\+]+)/?(%s)?$", $permalink['hardware'], $currencies_rule), 'index.php?name=hardware&group=$matches[1]&currency=$matches[2]', 'top');
            // /:permalink/:group/:model/:currency/
            add_rewrite_rule(sprintf("%s/([A-Z]{1}[\w\-\+]+)/([\w\-\+]+)/?(%s)?$", $permalink['hardware'], $currencies_rule), 'index.php?name=hardware&group=$matches[1]&model=$matches[2]&currency=$matches[3]', 'top');
            // /:permalink/:group/:model/buy/:currency/
            add_rewrite_rule(sprintf("%s/([A-Z]{1}[\w\-\+]+)/([\w\-\+]+)/buy/?(%s)?$", $permalink['hardware'], $currencies_rule), 'index.php?name=hardware&group=$matches[1]&model=$matches[2]&currency=$matches[3]&buy=1', 'top');
        }

        // manual
        add_rewrite_rule(sprintf("%s/([\w\-\+!]+)/?$", $permalink['manual']), 'index.php?name=manual&chapter=$matches[1]', 'top');
        
        //release-notes
        //:permalink/:year/
        add_rewrite_rule(sprintf("%s/([0-9]{4,4})/?$", $permalink['release-notes']), 'index.php?name=release-notes&year=$matches[1]', 'top');
        
        // login as
        add_rewrite_rule("loginas/([0-9]+)/([a-zA-Z0-9]+)$", 'index.php?name=loginas&user_id=$matches[1]&token=$matches[2]', 'top');
        // account confirmation
        add_rewrite_rule("c/([a-zA-Z0-9]{6,})$", 'index.php?name=confirmation&token=$matches[1]', 'top');
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
        // agent click
        add_rewrite_rule("a/([0-9]{1,10})$", 'index.php?name=agentclick&token=$matches[1]', 'top');

        // add endpoint for pages for each currency
        foreach ($currencies as $currency) {
            add_rewrite_endpoint(strtolower($currency), EP_PAGES);
        }
    }
}

return new L7P_Query();
