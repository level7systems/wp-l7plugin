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
        $id = isset($args['id']) ? esc_attr($args['id']) : '';
        $type = isset($args['type'])? esc_attr($args['type']) : 'text';
        $section = isset($args['section']) ? esc_attr($args['section']) : false;
        $value = isset($args['value']) ? esc_attr($args['value']) : '';
        $placeholder = isset($args['placeholder']) ? 'placeholder="' . esc_attr($args['placeholder']) . '"' : ''; 
        $pre = isset($args['pre']) ? esc_attr($args['pre']) : false;
        $post = isset($args['post']) ? esc_attr($args['post']) : false;
        $required = isset($args['required']) && $args['required'] ? ' required' : '';
        $help = isset($args['help']) ? esc_attr($args['help']) : false;
        $class = isset($args['class']) ? "class='".$args['class']."'" : '';
        $style = isset($args['style']) ? "style='".$args['style']."'" : '';

        // field section
        if ($section) {
            $name = sprintf("%s[%s]", $section, $name);
        }

        // pre field 
        if ($pre) {
            echo $pre;
        }

        echo "<input type='$type' id='$id' name='$name' value='$value' $placeholder $required $class $style />";

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
        $id = isset($args['id']) ?  "id='" .esc_attr($args['id']) : '';
        $class = isset($args['class']) ? esc_attr($args['class']) : $name;
        $section = isset($args['section']) ? esc_attr($args['section']) : false;
        $choices = isset($args['choices']) ? $args['choices'] : array();
        $value = isset($args['value']) ? esc_attr($args['value']) : false;
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

        echo "<select $id class='$class' name='$name' $style >";
        foreach ($choices as $id => $label) {
            if (!$value && strlen($id) == 0) {
                echo "<option value='$id' disabled selected>$label</option>";
            } else if ($value == $id) {
                echo "<option value='$id' selected>$label</option>";
            } else {
                echo "<option value='$id'>$label</option>";
            }
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
