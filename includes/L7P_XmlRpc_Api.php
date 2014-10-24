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
        'l7.ping'           => 'ping',
        'l7.setPricelists'  => 'setPricelists',
        // TODO: other methods
    );
    
    public function __construct()
    {
        add_filter('xmlrpc_methods', array($this, 'registerMethods'));    
    }
    
    public function registerMethods($methods)
    {
        foreach($this->methods as $ns => $method) {
            $methods[$ns] = array($this, $method);
        }
        
        return $methods;
    }
    
    // TODO: to be implemented
    
    public static function setPricelists(array $pricelists)
    {
        // TODO
        
        // returning errors to client
        // return new IXR_Error(500, "Some error");
        
        // TODO: add additional checks
        update_option('pricelists', $pricelists);
        
        return "OK";
    }
    
    public static function ping()
    {
        return "OK";
    }
    
}

return new L7P_XmlRpc_Api();