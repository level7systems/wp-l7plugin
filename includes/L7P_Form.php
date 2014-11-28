<?php

/*
 * This file is part of the Level 7 Systems Ltd. platform.
 *
 * (c) Kamil Adryjanek <kamil@level7systems.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

function text_input($args)
{
    $name = isset($args['name']) ? esc_attr($args['name']) : 'undefined';
    $section = isset($args['section']) ? esc_attr($args['section']) : false;
    $value = isset($args['value']) ? esc_attr($args['value']) : '';
    $placeholder = isset($args['placeholder']) ? esc_attr($args['placeholder']) : '';
    $pre = isset($args['pre']) ? esc_attr($args['pre']) : false;
    $help = isset($args['help']) ? esc_attr($args['help']) : false;
    
    // field section
    if ($section) {
        $name = sprintf("%s[%s]", $section, $name);
    }
    
    // pre field 
    if ($pre) {
        echo $pre;
    }
    
    echo "<input type='text' name='$name' value='$value' placeholder='$placeholder' />";
    
    if ($help) {
        echo "<p><small>$help</small></p>";
    }
}