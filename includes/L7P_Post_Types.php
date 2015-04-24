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
        add_action('init', array(__CLASS__, 'register_post_types'), 5);
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
        if (post_type_exists('level7platform_page')) {
            return;
        }

        register_post_type('level7platform_page', array(
            'label' => __("Level7 templates", 'level7platform'),
            'description' => __('This is where you can manage Level7 platform page templates.', 'level7platform'),
            'labels' => array(
            ),
            'public' => true,
            'description' => __('This is where you can manage Level7 platform page templates.', 'level7platform'),
            'show_in_menu' => 'l7-settings',
            'capability_type' => 'page',
            'map_meta_cap' => true,
            // TODO: implement as in product
            // TODO: implement custom capabilities 
            // TODO: this cause translation problem
//            'capabilities' => array(
//                // TODO: block preview
//                'show_posts' => false,
//                // TODO: this cause creating translation pages
//                'create_posts' => true,
//                'edit_posts' => true,
//                'delete_posts' => false,
//                'delete_published_posts' => false,
//            ),
            'supports' => array('title', 'editor', 'excerpt', 'thumbnail', 'custom-fields', 'page-attributes'),
            'rewrite' => false,
            'query_var' => true,
            // 'rewrite'             => $product_permalink ? array( 'slug' => untrailingslashit( $product_permalink ), 'with_front' => false, 'feeds' => true ) : false,
            /*
              'rewrite'               => array(
              'slug'       => 'level7platform_page',
              'with_front' => false
              ),
             */
            )
        );
    }
}

L7P_Post_Types::init();
