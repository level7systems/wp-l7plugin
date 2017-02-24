<?php
/*
 * This file is part of the Level 7 Systems Ltd. platform.
 *
 * (c) Kamil Adryjanek <kamil@level7systems.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Description of L7P_Ajax
 *
 * @author kamil
 */
class L7P_Ajax
{
    public function __construct()
    {
        add_action('wp_ajax_nopriv_login_form', array($this, 'login_form'));
        add_action('wp_ajax_nopriv_register_form', array($this, 'register_form'));
        add_action('wp_ajax_nopriv_search_autocomplete', array($this, 'search_autocomplete'));
        add_action('wp_ajax_search_autocomplete', array($this, 'search_autocomplete'));
    }
    
    public function login_form()
    {
        $form = l7p_block_login_form();
        
        echo $form;

        wp_die();
    }
    
    public function register_form()
    {
        $form = l7p_block_register_form();

        echo $form;
        
        wp_die();
    }
    
    public function search_autocomplete()
    {
        $term =  sanitize_text_field($_POST['term']); 
        echo json_encode(l7p_get_chapters_keywords($term));
        wp_die();
    }
}

return new L7P_Ajax();
