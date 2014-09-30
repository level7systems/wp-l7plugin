<?php
/*
 * This file is part of the Level 7 Systems Ltd. platform.
 *
 * (c) Kamil Adryjanek <kamil@level7systems.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class L7P_Shortcodes
{
    public static function init()
    {
        // shortcodes
        $shortcodes = array(
            'user_charge'          => __CLASS__ . '::user_charge',
            'user_unlimited'       => __CLASS__ . '::user_unlimited',
            'user_unlimited?int'   => __CLASS__ . '::user_unlimited_int',
        );
        
        foreach ($shortcodes as $shortcode => $function ) {
            add_shortcode($shortcode, $function );
            // add_shortcode(apply_filters( "{$shortcode}_shortcode_tag", $shortcode ), $function );
        }
        
    }
    
    public static function user_charge($atts)
    {
        if ( empty( $atts ) ) return '';
    
        // TODO
    
        $args = array(
            'posts_per_page' 		=> 1,
            'post_type'				=> 'product',
            'post_status' 			=> 'publish',
            'ignore_sticky_posts'	=> 1,
            'no_found_rows' 		=> 1
        );
    
        if ( isset( $atts['sku'] ) ) {
            $args['meta_query'][] = array(
                'key'     => '_sku',
                'value'   => $atts['sku'],
                'compare' => '='
            );
        }
    
        if ( isset( $atts['id'] ) ) {
            $args['p'] = $atts['id'];
        }
    
        $single_product = new WP_Query( $args );
    
		return ;
	}
	
	public static function user_unlimited($atts)
	{
	    // TODO
	}
	
	public static function user_unlimited_int($atts)
	{
	    // TODO
	}
}