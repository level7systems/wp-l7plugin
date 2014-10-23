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
    
        $permalinks        = get_option( 'level7platform_permalinks' );
        $product_permalink = empty( $permalinks['product_base'] ) ? _x( 'product', 'slug', 'level7platform' ) : $permalinks['product_base'];
    
        register_post_type(
            'level7_page',
            apply_filters( 'level7platform_register_post_type_product',
                array(
                    'labels'              => array(
                        'name'               => __( 'Products', 'level7platform' ),
                        'singular_name'      => __( 'Product', 'level7platform' ),
                        'menu_name'          => _x( 'Products', 'Admin menu name', 'level7platform' ),
                        'add_new'            => __( 'Add Product', 'level7platform' ),
                        'add_new_item'       => __( 'Add New Product', 'level7platform' ),
                        'edit'               => __( 'Edit', 'level7platform' ),
                        'edit_item'          => __( 'Edit Product', 'level7platform' ),
                        'new_item'           => __( 'New Product', 'level7platform' ),
                        'view'               => __( 'View Product', 'level7platform' ),
                        'view_item'          => __( 'View Product', 'level7platform' ),
                        'search_items'       => __( 'Search Products', 'level7platform' ),
                        'not_found'          => __( 'No Products found', 'level7platform' ),
                        'not_found_in_trash' => __( 'No Products found in trash', 'level7platform' ),
                        'parent'             => __( 'Parent Product', 'level7platform' )
                    ),
                    'description'         => __( 'This is where you can add new products to your store.', 'level7platform' ),
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
