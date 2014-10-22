<?php
/*
 * This file is part of the Level 7 Systems Ltd. platform.
 *
 * (c) Kamil Adryjanek <kamil@level7systems.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class L7P_Admin
{
    public function __construct()
    {
        add_action('admin_menu', array( $this, 'menu' ), 5);
    }
    
    public function menu()
    {
        // add_menu_page('My Custom Page', 'My Custom Page', 'manage_options', 'my-top-level-slug');
        add_menu_page("Level7 Platform", "Level7 Platform", 'manage_options', 'l7-platform', null, null, 60);
        
        add_submenu_page( 'l7-platform', 'Pages', 'Pages', 'manage_options', 'l7-pages');
        add_submenu_page( 'l7-platform', 'Settings', 'Settings', 'manage_options', 'l7-settings');
        
        // TODO
        
    }
    
    public function level7_options() {
        echo '<div class="wrap">';
        echo '<p><strong>TO-DO: Add some config options...</p>';
        echo '</div>';
    }
}

return new L7P_Admin();