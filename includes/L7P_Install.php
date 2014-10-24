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
        
        // create pages
        $this->create_pages();
        
        // other settup
    }
    
    public function create_pages()
    {
        $pages = apply_filters( 'level7platform_create_pages', array(
            'pricing' => array(
                'name'    => _x('pricing', 'Page slug', 'level7platform' ),
                'title'   => _x('Pricing', 'Page title', 'level7platform' ),
                // TODO
                'content' => ''
            ),
            'rates' => array(
                'name'    => _x('rates', 'Page slug', 'level7platform' ),
                'title'   => _x('Rates', 'Page title', 'level7platform' ),
                // TODO:
                'content' => ''
            ),
        ));
    
        foreach ( $pages as $key => $page ) {
            l7_create_page(esc_sql($page['name']), 'level7platform_' . $key . '_page_id', $page['title'], $page['content']);
        }
    }
}

return new L7P_Install();