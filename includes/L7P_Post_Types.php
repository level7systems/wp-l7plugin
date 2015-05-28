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
        add_action('init', array(__CLASS__, 'register_post_types'), 5);
    }

    /**
     * Register core post types
     */
    public static function register_post_types()
    {
        if (post_type_exists('l7p_page')) {
            return;
        }

        register_post_type('l7p_page', array(
            'label' => __("Dynamic page templates", 'level7platform'),
            'description' => __('This is where you can manage Level7 platform page templates.', 'level7platform'),
            'labels' => array(
                'name'               => __( 'Dynamic page templates', 'level7platform' ),
                'singular_name'      => __( 'Dynamic page template', 'level7platform' ),
                'menu_name'          => _x( 'Page templates', 'Admin menu name', 'level7platform' ),
                'edit'               => __( 'Edit', 'level7platform' ),
                'edit_item'          => __( 'Edit dynamic page template', 'level7platform' ),
                'view'               => __( 'View template', 'level7platform' ),
                'view_item'          => __( 'View template', 'level7platform' ),
                'search_items'       => __( 'Search templates', 'level7platform' ),
                'not_found'          => __( 'No dynamic page templates found', 'level7platform' ),
                'not_found_in_trash' => __( 'No dynamic page templates found in trash', 'level7platform' )
            ),
            'public' => true,
            'exclude_from_search' => true,
            'description' => __('This is where you can manage Level7 platform page templates.', 'level7platform'),
            'show_in_menu' => 'l7-settings',
            'capability_type' => 'page',
            'map_meta_cap' => true,
            'capabilities' => array(
                // this need to be enabled for creating translation for templates
                'create_posts' => true,
                'delete_posts' => false,
                'delete_published_posts' => false,
            ),
            'supports' => array('title', 'editor', 'excerpt', 'thumbnail'),
            'rewrite' => false,
            'query_var' => false
            )
        );
    }
}

L7P_Post_Types::init();
