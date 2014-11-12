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
        add_menu_page("Level7 Platform", "Level7 Platform", 'manage_options', 'l7-settings', null, null, 60);

        add_submenu_page( 'l7-settings', 'Settings', 'Settings', 'manage_options', 'l7-settings', array($this, 'settings_page'));
    }
    
    public function settings_page() {
        
        register_setting('level7platform_options');
        
        // Add a section to the permalinks page
        add_settings_section( 'level7platform_main', __( 'Product permalink base', 'level7platform' ), array( $this, 'settings' ), 'level7platform' );
        
        // Add our settings
        add_settings_field(
            'level7platform_product_category_slug',      	// id
            __( 'Product category base', 'level7platform' ), 	// setting title
            array( $this, 'product_category_slug_input' ),  // display callback
            'level7platform',                 				// settings page
            'level7platform_main'                  				// settings section
        );
        add_settings_field(
            'level7platform_product_test_slug',      		// id
            __( 'Product tag base', 'level7platform' ), 	// setting title
            array( $this, 'product_category_slug_input' ),  // display callback
            'level7platform',                 				// settings page
            'level7platform_main'                  				// settings section
        );
        add_settings_field(
            'level7platform_product_xxx_slug',      	// id
            __( 'Product attribute base', 'level7platform' ), 	// setting title
            array( $this, 'product_category_slug_input' ),  // display callback
            'level7platform',                 				// settings page
            'level7platform_main'                  				// settings section
        );
        
        echo '<div class="wrap">';
        echo '<p><strong>TO-DO: Add some config options...</p>';
        echo '<form>';
        settings_fields('level7platform_options');
        do_settings_sections('level7platform');
        submit_button("Save options", 'primary');
        echo '</form>';
        echo '</div>';
    }
    
    /**
     * Show a slug input box.
     */
    public function product_category_slug_input() {
        $permalinks = get_option( 'level7platform_permalinks' );
        ?>
    		<input name="level7platform_product_category_slug" type="text" class="regular-text code" value="<?php if ( isset( $permalinks['category_base'] ) ) echo esc_attr( $permalinks['category_base'] ); ?>" placeholder="<?php echo _x('product-category', 'slug', 'level7platform') ?>" />
    		<?php
    	}
    	
    	public function settings() {
    	    echo wpautop( __( 'These settings control the permalinks used for products. These settings only apply when <strong>not using "default" permalinks above</strong>.', 'woocommerce' ) );
    	
    	    $permalinks = get_option( 'woocommerce_permalinks' );
    	    $product_permalink = $permalinks['product_base'];
    	
    	    // Get shop page
    	    $shop_page_id 	= wc_get_page_id( 'shop' );
    	    $base_slug 		= ( $shop_page_id > 0 && get_page( $shop_page_id ) ) ? get_page_uri( $shop_page_id ) : _x( 'shop', 'default-slug', 'woocommerce' );
    	    $product_base 	= _x( 'product', 'default-slug', 'woocommerce' );
    	
    	    $structures = array(
    	                    0 => '',
    	                    1 => '/' . trailingslashit( $product_base ),
    	                    2 => '/' . trailingslashit( $base_slug ),
    	                    3 => '/' . trailingslashit( $base_slug ) . trailingslashit( '%product_cat%' )
    	    );
    	    ?>
    			<table class="form-table">
    				<tbody>
    					<tr>
    						<th><label><input name="product_permalink" type="radio" value="<?php echo $structures[0]; ?>" class="wctog" <?php checked( $structures[0], $product_permalink ); ?> /> <?php _e( 'Default', 'woocommerce' ); ?></label></th>
    						<td><code><?php echo home_url(); ?>/?product=sample-product</code></td>
    					</tr>
    					<tr>
    						<th><label><input name="product_permalink" type="radio" value="<?php echo $structures[1]; ?>" class="wctog" <?php checked( $structures[1], $product_permalink ); ?> /> <?php _e( 'Product', 'woocommerce' ); ?></label></th>
    						<td><code><?php echo home_url(); ?>/<?php echo $product_base; ?>/sample-product/</code></td>
    					</tr>
    					<?php if ( $shop_page_id ) : ?>
    						<tr>
    							<th><label><input name="product_permalink" type="radio" value="<?php echo $structures[2]; ?>" class="wctog" <?php checked( $structures[2], $product_permalink ); ?> /> <?php _e( 'Shop base', 'woocommerce' ); ?></label></th>
    							<td><code><?php echo home_url(); ?>/<?php echo $base_slug; ?>/sample-product/</code></td>
    						</tr>
    						<tr>
    							<th><label><input name="product_permalink" type="radio" value="<?php echo $structures[3]; ?>" class="wctog" <?php checked( $structures[3], $product_permalink ); ?> /> <?php _e( 'Shop base with category', 'woocommerce' ); ?></label></th>
    							<td><code><?php echo home_url(); ?>/<?php echo $base_slug; ?>/product-category/sample-product/</code></td>
    						</tr>
    					<?php endif; ?>
    					<tr>
    						<th><label><input name="product_permalink" id="woocommerce_custom_selection" type="radio" value="custom" class="tog" <?php checked( in_array( $product_permalink, $structures ), false ); ?> />
    							<?php _e( 'Custom Base', 'woocommerce' ); ?></label></th>
    						<td>
    							<input name="product_permalink_structure" id="woocommerce_permalink_structure" type="text" value="<?php echo esc_attr( $product_permalink ); ?>" class="regular-text code"> <span class="description"><?php _e( 'Enter a custom base to use. A base <strong>must</strong> be set or WordPress will use default instead.', 'woocommerce' ); ?></span>
    						</td>
    					</tr>
    				</tbody>
    			</table>
    			<script type="text/javascript">
    				jQuery(function(){
    					jQuery('input.wctog').change(function() {
    						jQuery('#woocommerce_permalink_structure').val( jQuery(this).val() );
    					});
    	
    					jQuery('#woocommerce_permalink_structure').focus(function(){
    						jQuery('#woocommerce_custom_selection').click();
    					});
    				});
    			</script>
    			<?php
    		}
    	    	
}

return new L7P_Admin();
