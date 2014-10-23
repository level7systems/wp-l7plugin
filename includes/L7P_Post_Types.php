<?php
/*
 * This file is part of the Level 7 Systems Ltd. platform.
 *
 * (c) Kamil Adryjanek <kamil@level7systems.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class L7P_Post_Types
{
    /**
     * Hook in methods
     */
    public static function init()
    {
        // add_action( 'init', array( __CLASS__, 'register_taxonomies' ), 5 );
        add_action( 'init', array( __CLASS__, 'register_post_types' ), 5 );
    }
    
    /**
     * Register taxonomies.
     */
    public static function register_taxonomies()
    {
        
    }
    
    /**
     * Register core post types
     */
    public static function register_post_types()
    {
        if ( post_type_exists('') ) {
            return;
        }
    
        // do_action( 'level7_register_level7_page_type' );
    
        $permalinks        = get_option( 'woocommerce_permalinks' );
        $product_permalink = empty( $permalinks['product_base'] ) ? _x( 'product', 'slug', 'woocommerce' ) : $permalinks['product_base'];
    
        register_post_type(
            'level7_page',
            apply_filters( 'woocommerce_register_post_type_product',
                array(
                    'labels'              => array(
                        'name'               => __( 'Products', 'woocommerce' ),
                        'singular_name'      => __( 'Product', 'woocommerce' ),
                        'menu_name'          => _x( 'Products', 'Admin menu name', 'woocommerce' ),
                        'add_new'            => __( 'Add Product', 'woocommerce' ),
                        'add_new_item'       => __( 'Add New Product', 'woocommerce' ),
                        'edit'               => __( 'Edit', 'woocommerce' ),
                        'edit_item'          => __( 'Edit Product', 'woocommerce' ),
                        'new_item'           => __( 'New Product', 'woocommerce' ),
                        'view'               => __( 'View Product', 'woocommerce' ),
                        'view_item'          => __( 'View Product', 'woocommerce' ),
                        'search_items'       => __( 'Search Products', 'woocommerce' ),
                        'not_found'          => __( 'No Products found', 'woocommerce' ),
                        'not_found_in_trash' => __( 'No Products found in trash', 'woocommerce' ),
                        'parent'             => __( 'Parent Product', 'woocommerce' )
                    ),
                    'description'         => __( 'This is where you can add new products to your store.', 'woocommerce' ),
                    'public'              => true,
                    'show_ui'             => true,
                    'capability_type'     => 'product',
                    'map_meta_cap'        => true,
                    'publicly_queryable'  => true,
                    'exclude_from_search' => false,
                    'hierarchical'        => false, // Hierarchical causes memory issues - WP loads all records!
                    'rewrite'             => $product_permalink ? array( 'slug' => untrailingslashit( $product_permalink ), 'with_front' => false, 'feeds' => true ) : false,
                    'query_var'           => true,
                    'supports'            => array( 'title', 'editor', 'excerpt', 'thumbnail', 'comments', 'custom-fields', 'page-attributes' ),
                    'has_archive'         => ( $shop_page_id = wc_get_page_id( 'shop' ) ) && get_post( $shop_page_id ) ? get_page_uri( $shop_page_id ) : 'shop',
                    'show_in_nav_menus'   => true
                )
            )
        );
    }
}

L7P_Post_Types::init();
