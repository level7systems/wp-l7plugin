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
        
        if (post_type_exists('l7p_page') && post_type_exists('l7p_release')) {
            // enable reviews for l7p_page
            if (post_type_supports('l7p_page', 'revisions') === false) {
                add_post_type_support('l7p_page', 'revisions');
            }
            return;
        }
        
        if (!post_type_exists('l7p_page')) {
            
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
                'capabilities' => array(),
                'supports' => array('title', 'editor', 'excerpt', 'thumbnail', 'revisions'),
                'rewrite' => false,
                'query_var' => false
                )
            );
        }
        
        if (!post_type_exists('l7p_release')) {
            register_post_type('l7p_release', array(
                'label' => __("Release notes posts", 'level7release'),
                'description' => __('This is where you can manage Level7 release notes.', 'level7release'),
                'labels' => array(
                    'name'               => __( 'Release notes', 'level7release' ),
                    'singular_name'      => __( 'Release note', 'level7release' ),
                    'menu_name'          => _x( 'Release notes', 'Admin menu name', 'level7release' ),
                    'edit'               => __( 'Edit', 'level7release' ),
                    'edit_item'          => __( 'Edit release note', 'level7release' ),
                    'view'               => __( 'View release notes', 'level7release' ),
                    'view_item'          => __( 'View release note', 'level7release' ),
                    'search_items'       => __( 'Search release notes', 'level7release' ),
                    'not_found'          => __( 'No release note found', 'level7release' ),
                    'not_found_in_trash' => __( 'No release note found in trash', 'level7release' )
                ),
                'public' => true, 
                'exclude_from_search' => true,
                'description' => __('This is where you can manage Level7 release notes.', 'level7platform'),
                'capability_type' => 'page',
                'map_meta_cap' => true,
                'capabilities' => array(), 
                'supports' => array('title', 'editor', 'excerpt', 'thumbnail'),
                'taxonomies' => array('l7p_release_category'),
                'rewrite' => false,
                'query_var' => true
                )
            );
        }
        if(!taxonomy_exists('l7p_release_category')) {
            $labels = array(
                'menu_name' => __( 'Category', 'l7-settings'  ),
            ); 
            register_taxonomy(
                    'l7p_release_category',
                    'l7p_release',
                    array(
                        'label' => __( 'Release note category' ),
                        'rewrite' => array( 'slug' => 'release-note-category' ),
                        'hierarchical' => true,
                        'labels' => $labels,
                        'show_ui' => true,
                        'show_admin_column' => true,
                        'query_var' => true,
                    )
            );
        }
    }
}

L7P_Post_Types::init();
