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
        if (post_type_exists('level7_page')) {
            return;
        }
    
        // do_action( 'level7_register_level7_page_type' );
    
        // $permalinks        = get_option( 'level7platform_permalinks' );
        // $product_permalink = empty( $permalinks['product_base'] ) ? _x( 'product', 'slug', 'level7platform' ) : $permalinks['product_base'];
    
        register_post_type(
            'level7_page',
            array(
                'label'         => __("Page templates"),
                'labels'        => array(
            	
                ),
                'public'        => true,
                'description'   => __( 'This is where you can manage Level7 platform page templates.', 'level7platform' ),
                'show_in_menu'  => 'l7-settings',
                'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'custom-fields', 'page-attributes' ),
                'capabilities'  => array(
                    'create_posts' => false,
                )
            )
        );
        
        /*
        register_post_type(
            'level7_page',
            // apply_filters('level7platform_register_post_type_level7_page',
            array(
                'label'     => __('Page templates'),
                'labels'              => array(
                    'name'               => __( 'Page templates', 'level7platform' ),
                    'singular_name'      => __( 'Page template', 'level7platform' ),
                    'menu_name'          => _x( 'Page templates', 'Admin menu name', 'level7platform' ),
                    'add_new'            => __( 'Add Product', 'level7platform' ),
                    'add_new_item'       => __( 'Add New Product', 'level7platform' ),
                    'edit'               => __( 'Edit', 'level7platform' ),
                    'edit_item'          => __( 'Edit Page template', 'level7platform' ),
                    'new_item'           => __( 'New Product template', 'level7platform' ),
                    'view'               => __( 'View Page template', 'level7platform' ),
                    'view_item'          => __( 'View Page template', 'level7platform' ),
                    'search_items'       => __( 'Search Page templates', 'level7platform' ),
                    'not_found'          => __( 'No Page templates found', 'level7platform' ),
                    'not_found_in_trash' => __( 'No Page templates found in trash', 'level7platform' )
                ),
                'description'         => __( 'This is where you can manage Level7 platform page templates.', 'level7platform' ),
                'public'              => true,
                'show_ui'             => true,
                // 'capability_type'     => 'level7_page',
                'capability_type'     => 'manage_options',
                'map_meta_cap'        => true,
                'publicly_queryable'  => true,
                'exclude_from_search' => false,
                'hierarchical'        => false, // Hierarchical causes memory issues - WP loads all records!
                //'rewrite'             => $product_permalink ? array( 'slug' => untrailingslashit( $product_permalink ), 'with_front' => false, 'feeds' => true ) : false,
                'query_var'           => true,
                'supports'            => array( 'title', 'editor', 'excerpt', 'thumbnail', 'custom-fields', 'page-attributes' ),
                // 'has_archive'         => ( $shop_page_id = wc_get_page_id( 'shop' ) ) && get_post( $shop_page_id ) ? get_page_uri( $shop_page_id ) : 'shop',
                'show_in_nav_menus'   => true
            )
            //)
        );
        */
    }
}

L7P_Post_Types::init();
