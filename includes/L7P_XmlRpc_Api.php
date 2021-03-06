<?php
/*
 * This file is part of the Level 7 Systems Ltd. platform.
 *
 * (c) Kamil Adryjanek <kamil@level7systems.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class L7P_XmlRpc_Api
{
    protected $methods = array(
        // to verify if pugin is enabled
        'l7.ping'           => 'ping',
        // TODO: other methods
        'l7.setSettings'    => 'setSettings',
        'l7.setPricelist'   => 'setPricelist',
        'l7.setRoutes'      => 'setRoutes',
        'l7.setDdi'         => 'setDdi',
        'l7.setDdiCountries'=> 'setDdiCountries',
        'l7.setDdiCountry'  => 'setDdiCountry',
        'l7.setPhones'      => 'setPhones',
        'l7.setChapters'    => 'setChapters',
        'l7.cacheClear'     => 'cacheClear'
    );
    
    public function __construct()
    {
        add_filter('xmlrpc_methods', array($this, 'registerMethods'));    
    }
    
    public function registerMethods($methods)
    {
        foreach($this->methods as $ns => $method) {
            $methods[trim($ns)] = array($this, trim($method));
        }
        
        return $methods;
    }
    
    /**
     * @param type $params
     * 
     * @return string
     */
    public function setSettings($params)
    {
        // verify token
        if (!$this->authorize($params[0])) {
            return $this->error;
        }
        
        l7p_update_option('settings', json_decode($params[1], true));
        
        return "OK";
    }
    
    public function setPricelist($params)
    {
        // verify token
        if (!$this->authorize($params[0])) {
            return $this->error;
        }
        
        l7p_update_option('pricelist', json_decode($params[1], true));
        
        return "OK";
    }
    
    public function setRoutes($params)
    {
        // verify token
        if (!$this->authorize($params[0])) {
            return $this->error;
        }
        
        l7p_update_option('routes', json_decode($params[1], true));
        
        return "OK";
    }
    
    public function setDdi($params)
    {
        // verify token
        if (!$this->authorize($params[0])) {
            return $this->error;
        }
        
        l7p_update_option('ddi', json_decode($params[1], true));
        
        return "OK";
    }
    
    // update data of single country
    public function setDdiCountry($params)
    {
        // verify token
        if (!$this->authorize($params[0])) {
            return $this->error;
        }
        
        $data = json_decode($params[1], true);

        $currency = $data['currency'];
        $countryCode = $data['country_code'];
        $stateCode = isset($data['state_code']) ? $data['state_code'] : null;

        if ($countryCode == 'US' && $stateCode) {
            l7p_set_ddi_state($stateCode, $currency, $data['data']);
        } else {
            l7p_set_ddi_country($countryCode, $currency, $data['data']);
        }
            
        return "OK";
    }
    
    public function setPhones($params)
    {
        // verify token
        if (!$this->authorize($params[0])) {
            return $this->error;
        }
        
        l7p_update_option('phones', json_decode($params[1], true));
        
        return "OK";
    }
    
    public function setChapters($params)
    {
        // verify token
        if (!$this->authorize($params[0])) {
            return $this->error;
        }
        
        l7p_update_option('chapters', json_decode($params[1], true));
        
        return "OK";
    }
    
    public function cacheClear($params)
    {
        // verify token
        if (!$this->authorize($params[0])) {
            return $this->error;
        }
        
        l7p_cache_clear();
        
        return "OK";
    }


    public function ping()
    {
        return "OK";
    }
    
    private function authorize($token)
    {
        if (empty($token)) {
            $this->error = new IXR_Error(401, __('Empty API token provided.'));
            return false;
        }
        
        $api_token = l7p_get_api_token();
        if ($api_token != $token) {
            $this->error = new IXR_Error(401, __('Incorrect API token provided.'));
            return false;
        }
        
        return true;
    }
    
}

return new L7P_XmlRpc_Api();