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
        
        add_action('admin_head', array( $this, 'menu_head' ));
    }
    
    public function menu()
    {
        add_menu_page("Level7 Platform", "Pages", 'manage_options', 'l7-settings', null, null, 60);

        add_submenu_page( 'l7-settings', 'Settings', 'Settings', 'manage_options', 'l7-settings', array( $this, 'settings_page' ));
        add_submenu_page( 'l7-settings', 'Pages', 'Pages', 'manage_options', 'edit.php?post_type=page', array( $this, 'pages_page' ));
    }
    
    public function menu_head() {
        global $menu, $submenu, $parent_file, $submenu_file, $self, $post_type, $taxonomy;
    
        $to_highlight_types = array( 'shop_order', 'level7_page' );
    
        echo $post_type;
        
        if ( isset( $post_type ) ) {
            if ( in_array( $post_type, $to_highlight_types ) ) {
                $submenu_file = 'edit.php?post_type=' . esc_attr( $post_type );
                $parent_file  = 'woocommerce';
            }
    
            if ( 'product' == $post_type ) {
                $screen = get_current_screen();
    
                if ( $screen->base == 'edit-tags' && taxonomy_is_product_attribute( $taxonomy ) ) {
                    $submenu_file = 'product_attributes';
                    $parent_file  = 'edit.php?post_type=' . esc_attr( $post_type );
                }
            }
        }
    
        if ( isset( $submenu['woocommerce'] ) && isset( $submenu['woocommerce'][1] ) ) {
            $submenu['woocommerce'][0] = $submenu['woocommerce'][1];
            unset( $submenu['woocommerce'][1] );
        }
    
        if ( isset( $submenu['woocommerce'] ) && current_user_can( 'manage_woocommerce' ) ) {
            foreach ( $submenu['woocommerce'] as $key => $menu_item ) {
                if ( 0 === strpos( $menu_item[0], _x( 'Orders', 'Admin menu name', 'woocommerce' ) ) ) {
    
                    $menu_name = _x( 'Orders', 'Admin menu name', 'woocommerce' );
                    if ( $order_count = wc_processing_order_count() ) {
                        $menu_name .= ' <span class="awaiting-mod update-plugins count-' . $order_count . '"><span class="processing-count">' . number_format_i18n( $order_count ) . '</span></span>';
                    }
    
                    $submenu['woocommerce'][ $key ] [0] = $menu_name;
                    break;
                }
            }
        }
    }
    
    public function level7_options() {
        echo '<div class="wrap">';
        echo '<p><strong>TO-DO: Add some config options...</p>';
        echo '</div>';
    }
    
    public function pages_page() {
        echo '<div class="wrap">';
        echo '<p><strong>TO-DO: Add some config options...</p>';
        echo '</div>';
    }
    
    public function settings_page() {
        echo '<div class="wrap">';
        echo '<p><strong>TO-DO: Add some config options...</p>';
        echo '</div>';
    }
}

return new L7P_Admin();