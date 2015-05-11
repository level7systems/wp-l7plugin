<?php
/*
 * This file is part of the Level 7 Systems Ltd. platform.
 *
 * (c) Kamil Adryjanek <kamil@level7systems.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class L7P_Form
{
    public static function label($args)
    {
        $id = isset($args['id']) ? esc_attr($args['id']) : 'undefined';
        $label = isset($args['label']) ? esc_attr($args['label']) : 'undefined';
        
        echo "<label for='$id'>$label</label>";
    }
    
    public static function input($args)
    {
        $name = isset($args['name']) ? esc_attr($args['name']) : 'undefined';
        $id = isset($args['id']) ? esc_attr($args['id']) : $name;
        $type = isset($args['type'])? esc_attr($args['type']) : 'text';
        $section = isset($args['section']) ? esc_attr($args['section']) : false;
        $value = isset($args['value']) ? esc_attr($args['value']) : '';
        $placeholder = isset($args['placeholder']) ? esc_attr($args['placeholder']) : '';
        $pre = isset($args['pre']) ? esc_attr($args['pre']) : false;
        $post = isset($args['post']) ? esc_attr($args['post']) : false;
        $help = isset($args['help']) ? esc_attr($args['help']) : false;
        $style = isset($args['style']) ? "style='".$args['style']."'" : '';

        // field section
        if ($section) {
            $name = sprintf("%s[%s]", $section, $name);
        }

        // pre field 
        if ($pre) {
            echo $pre;
        }

        echo "<input type='$type' id='$id' name='$name' value='$value' placeholder='$placeholder' $style />";

        // post field
        if ($post) {
            echo $post;
        }

        if ($help) {
            echo "<p><small>$help</small></p>";
        }
    }

    public static function hidden_input($args)
    {
        $args['type'] = 'hidden';
        return self::input($args);
    }
    
    public static function text_input($args)
    {
        $args['type'] = 'text';
        return self::input($args);
    }
    
    public static function password_input($args)
    {
        $args['type'] = 'password';
        self::input($args);
    }
    
    public static function select($args)
    {
        $name = isset($args['name']) ? esc_attr($args['name']) : 'undefined';
        $id = isset($args['id']) ? esc_attr($args['id']) : $name;
        $section = isset($args['section']) ? esc_attr($args['section']) : false;
        $choices = isset($args['choices']) ? $args['choices'] : array();
        $value = isset($args['value']) ? esc_attr($args['value']) : '';
        $pre = isset($args['pre']) ? esc_attr($args['pre']) : false;
        $post = isset($args['post']) ? esc_attr($args['post']) : false;
        $help = isset($args['help']) ? esc_attr($args['help']) : false;
        $style = isset($args['style']) ? "style='".$args['style']."'" : '';

        // field section
        if ($section) {
            $name = sprintf("%s[%s]", $section, $name);
        }

        // pre field 
        if ($pre) {
            echo $pre;
        }

        echo "<select id='$id' name='$name' $style />";
        foreach ($choices as $id => $label) {
            echo "<option value='$id'>$label</option>";
        }
        echo "</select>";
        
        // post field
        if ($post) {
            echo $post;
        }

        if ($help) {
            echo "<p><small>$help</small></p>";
        }
    }
}
