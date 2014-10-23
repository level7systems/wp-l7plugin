<?php
/*
 * This file is part of the Level 7 Systems Ltd. platform.
 *
 * (c) Kamil Adryjanek <kamil@level7systems.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class L7P_Install
{
    public function __construct()
    {
        register_activation_hook(L7P_PLUGIN_FILE, array($this, 'install'));
    }
    
    public function install()
    {
        
        // enable XmlRpc
        update_option('enable_xmlrpc', '1');
        
        // other settup
    }
}