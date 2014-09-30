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
        function level7_options() {
            echo '<div class="wrap">';
            echo '<p><strong>TO-DO: Add some config options...</p>';
            echo '</div>';
        }
        
        add_menu_page("Level7", "Level7", 'manage_options', 'level7-menu', 'level7_options');
    
        // TODO
        
    }
}

return new L7P_Admin();