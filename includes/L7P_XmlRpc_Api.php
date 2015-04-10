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
        
        'l7.setCharges'     => 'setCharges',
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
    
    public function setSettings($settings)
    {
        // TODO: add additional checks
        l7p_update_option('settings', $settings);
        
        return "OK";
    }
    
    public function setPricelist($pricelist)
    {
        // returning errors to client
        // return new IXR_Error(500, "Some error");
        
        // TODO: add additional checks
        l7p_update_option('pricelist', $pricelist);
        
        return "OK";
    }
    
    public function ping()
    {
        return "OK";
    }
    
}

return new L7P_XmlRpc_Api();