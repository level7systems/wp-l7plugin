<?php
/*
 * This file is part of the Level 7 Systems Ltd. platform.
 *
 * (c) Kamil Adryjanek <kamil@level7systems.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class L7P_Frontend 
{
    public function __construct() {
		add_action( 'frontend_enqueue_scripts', array( $this, 'styles' ) );
		add_action( 'frontend_enqueue_scripts', array( $this, 'scripts' ) );
	}

	/**
	 * Enqueue styles
	 */
	public function styles() {
		global $wp_scripts;

		wp_enqueue_style(
            'level7-login',
            plugins_url('/css/level7-login.css', __FILE__)
		);
		
		wp_enqueue_style(
            'level7-jquery-ui-css',
            plugins_url('/css/jquery-ui.css', __FILE__)
		);
	}


	/**
	 * Enqueue scripts
	 */
	public function scripts() {
		global $wp_query, $post;

		wp_enqueue_script(
            'level7-login',
            plugins_url('/js/level7-login.js', __FILE__),
            array('jquery', 'jquery-ui-dialog')
		);
		
	}
}

return new L7P_Frontend();