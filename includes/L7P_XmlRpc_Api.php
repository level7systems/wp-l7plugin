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
        'l7.setPhones'      => 'setPhones',
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
    
    public function setSettings($params)
    {
        // TODO: add additional checks
        l7p_update_option('settings', json_decode($params, true));
        
        return "OK";
    }
    
    public function setPricelist($params)
    {
        // returning errors to client
        // return new IXR_Error(500, "Some error");
        
        // TODO: add additional checks
        l7p_update_option('pricelist', json_decode($params, true));
        
        return "OK";
    }
    
    public function setRoutes($params)
    {
        l7p_update_option('routes', json_decode($params, true));
        
        return "OK";
    }
    
    public function setDdi($params)
    {
        l7p_update_option('ddi', json_decode($params, true));
        
        return "OK";
    }
    
    public function setDdiCountries($params)
    {
        l7p_update_option('ddi_countries', json_decode($params, true));
        
        return "OK";
    }
    
    public function setPhones($params)
    {
        l7p_update_option('phones', json_decode($params, true));
        
        return "OK";
    }
    
    public function ping()
    {
        return "OK";
    }
    
}

return new L7P_XmlRpc_Api();