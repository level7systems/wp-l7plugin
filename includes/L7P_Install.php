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
        add_filter( 'plugin_action_links_' . L7P_PLUGIN_BASENAME, array( $this, 'add_action_links' ) );
        
        register_activation_hook(L7P_PLUGIN_FILE, array($this, 'install'));
        register_deactivation_hook(L7P_PLUGIN_FILE, array($this, 'uninstall'));
    }
    
    // action links
    public function add_action_links($links)
    {
        $action_links = array(
            'settings'	=>	sprintf('<a href="%s" title="%s">%s</a>',
                admin_url('admin.php?page=l7-settings'),
                esc_attr(__( 'View Level7 platform settings', 'level7platform')),
                __('Settings', 'level7platform')
            )
        );
        
        return array_merge($action_links, $links);
    }
    
    public function install()
    {
        // enable XmlRpc
        update_option('enable_xmlrpc', '1');

        // create roles and capabilities
        $this->create_roles();
        
        // rewrite rules
        L7P()->query->add_rewrite_rules();
        
        // flush rules after install
        flush_rewrite_rules();
    }
    
    public function uninstall()
    {
        global $wpdb;
        
        // delete created pages or wp_trash_post
        wp_delete_post(l7p_get_option('pricing_page_id'));
        wp_delete_post(l7p_get_option('rates_page_id'));
        wp_delete_post(l7p_get_option('telephone_numbers_page_id'));
        wp_delete_post(l7p_get_option('hardware_page_id'));
        
        // delete options
        $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE 'l7p_%';");
        
        // delete posts + data
        $wpdb->query( "DELETE FROM {$wpdb->posts} WHERE post_type IN ('level7_page', 'l7p_page');" );
        $wpdb->query( "DELETE FROM {$wpdb->postmeta} meta LEFT JOIN {$wpdb->posts} posts ON posts.ID = meta.post_id WHERE wp.ID IS NULL;" );
    }
    
    // add roles and capabilities
    private function create_roles()
    {
        global $wp_roles;
        
        // TODO: add capabilities: 
    }


    
    private function get_pages_contents()
    {



    }
}
