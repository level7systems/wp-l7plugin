<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class L7P_Query
{

    public function __construct()
    {
        if (!is_admin()) {
            add_action('pre_get_posts', array($this, 'pre_get_posts'));
        }
    }

    /**
     * 
     * 107243 - Call Rates [EN] /rates/
     * 107339 - Call Rates [ES] /es/tarifas/
     * 
     * 107222 - Telephone Numbers EN] /telephone-numbers/usd
     * 107354 - Telephone Numbers Country [EN] /telephone-numbers/COUNTRY/CURRENCY
     */
    public function pre_get_posts($query)
    {
        // we only want to affect the main query
        if (!$query->is_main_query()) {
            return;
        }

        if (!preg_match('#/$#', $_SERVER['REQUEST_URI'])) {
            return $this->redirect_to($_SERVER['REQUEST_URI'].'/');
        }

        $currencyUrl = false;

        if (preg_match('#^/hardware/#', $_SERVER['REQUEST_URI'])) {
            $currencyUrl = true;
        }

        if (preg_match('#^/es/hardware/#', $_SERVER['REQUEST_URI'])) {
            $currencyUrl = true;
        }

        if (preg_match('#^/rates/$#', $_SERVER['REQUEST_URI'])) {
            $currencyUrl = true;
        }

        if (preg_match('#^/es/tarifas/$#', $_SERVER['REQUEST_URI'])) {
            $currencyUrl = true;
        }

        if (preg_match('#^/telephone-numbers/$#', $_SERVER['REQUEST_URI'])) {
            $currencyUrl = true;
        }

        if ($currencyUrl) {
            $temp = explode("/", trim($_SERVER['REQUEST_URI'],'/'));
            $lastPart = array_pop($temp);
            $lastPart = strtoupper($lastPart);
            $currencies = l7p_get_currencies();
            if (!in_array($lastPart, $currencies)) {
                $defaultCurrency = strtolower(l7p_get_currency());

                return $this->redirect_to(rtrim($_SERVER['REQUEST_URI'],'/').'/'.$defaultCurrency.'/');
            }
        }

        $m = [];

        // hardware
        if (preg_match('#^/hardware/(usd|eur|gbp|pln)/#', $_SERVER['REQUEST_URI'])) {

            $page = get_post(107224);

            $query->is_404 = false;
            $query->is_page = true;
            $query->is_home = false;
            $query->is_singular = true;
            $query->set('name', $page->post_name);
        }

        if (preg_match('#^/es/hardware/(usd|eur|gbp|pln)/#', $_SERVER['REQUEST_URI'])) {

            $page = get_post(107388);

            $query->is_404 = false;
            $query->is_page = true;
            $query->is_home = false;
            $query->is_singular = true;
            $query->set('name', $page->post_name);
        }

        // /hardware/GROUP/usd
        if (preg_match('#^/hardware/([a-zA-Z\-]+)/(usd|eur|gbp|pln)/#', $_SERVER['REQUEST_URI'], $m)) {

            if (!l7p_get_phone_group_name_from_query()) {
                return $this->error_404();
            }

            $page = $this->getPage(107395, $query);
        }

        // /hardware/GROUP/PHONE/usd
        if (preg_match('#^/hardware/([a-zA-Z\-]+)/([0-9a-zA-Z\-]+)/(usd|eur|gbp|pln)/#', $_SERVER['REQUEST_URI'])) {

            if (!l7p_get_phone_item()) {
                return $this->error_404();
            }

            $page = $this->getPage(107401, $query);
        }

        // rates
        if (preg_match('#^/rates/(usd|eur|gbp|pln)/#', $_SERVER['REQUEST_URI'])) {
            $page = $this->getPage(107243, $query);
        }

        if (preg_match('#^/es/tarifas/(usd|eur|gbp|pln)/#', $_SERVER['REQUEST_URI'])) {
            $page = $this->getPage(107339, $query);
        }

        // /telephone-numbers/usd
        if (preg_match('#^/telephone-numbers/(usd|eur|gbp|pln)/#', $_SERVER['REQUEST_URI'])) {
            $page = $this->getPage(107222, $query);
        }

        // /telephone-numbers/country/usd
        if (preg_match('#^/telephone-numbers/[a-zA-Z\-]+/(usd|eur|gbp|pln)/#', $_SERVER['REQUEST_URI'])) {

            $countryCode = l7p_get_ddi_country_code();

            $pageId = ($countryCode == 'US') ? 107377 /* US States */ : 107354 /* Country page */;

            $page = $this->getPage($pageId, $query);
        }

        $countries = l7p_countries_i18n(l7p_get_culture());

        if (!isset($countries['US'])) {
            return;
        }

        $usa = str_replace(' ','-',$countries['US']);

        // /telephone-numbers/United-States/STATE/usd
        if (preg_match('#^/telephone-numbers/'.$usa.'/[a-zA-Z\-]+/(usd|eur|gbp|pln)/#', $_SERVER['REQUEST_URI'])) {

            $page = get_post(107354);
            
            $query->is_404 = false;
            $query->is_page = true;
            $query->is_home = false;
            $query->is_singular = true;
            $query->set('name', $page->post_name);
        }
    }

    private function getPage($id, &$query)
    {
        $page = get_post($id);

        $query->is_404 = false;
        $query->is_page = true;
        $query->is_home = false;
        $query->is_singular = true;
        $query->set('name', $page->post_name);

        return $page;
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
    public function redirect_to($url)
    {
        $uri =  sprintf("https://%s/%s", $_SERVER['HTTP_HOST'], ltrim($url, '/'));

        return l7p_redirect($uri);
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
}

return new L7P_Query();
