<?php

class Submenu_Widget extends WP_Widget {

	function Submenu_Widget()
	{
		$widget_ops = array('classname' => 'widget_submenu', 'description' => __('Submenu Widget'));
		$control_ops = array('width' => 400, 'height' => 350);
		$this->WP_Widget('submenu', __('Submenu'), $widget_ops, $control_ops);
	}

	function widget( $args, $instance )
	{
		extract($args);
		$menu = submenu_get_html($instance['menu']);
		$title = $instance['title'];
		if( $instance['use_item_for_title']) $title = submenu_get_current_item_text($instance['menu']);
        
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base);
    if( empty( $menu ) ) return;
		echo $before_widget;
		if ( !empty( $title ) ) { echo $before_title . $title . $after_title; } ?>
		<div class="submenu"><?php echo $menu; ?></div>
		<?php
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
        $instance['menu'] = $new_instance['menu'];
        $instance['use_item_for_title'] = (bool) $new_instance['use_item_for_title'];
        return $instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'menu' => '', 'use_item_for_title' => '' ) );
		$title = strip_tags($instance['title']);
        $menu = $instance['menu'];
        $use_item_for_title['use_item_for_title'];
		$menus = get_registered_nav_menus();
?>
		<p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('menu'); ?>"><?php _e('Menu:'); ?></label>
            <select class="widefat" id="<?php echo $this->get_field_id('menu'); ?>" name="<?php echo $this->get_field_name('menu'); ?>">
                <option value="">(Default Menu)</option>
                <?php foreach( $menus as $key => $text ){ ?>
                <option value="<?php echo $key; ?>" <?php if( $key == $menu ){ ?>selected<?php } ?>><?php echo $text; ?></option>
                <?php } ?>
            </select>
        </p>

		<p><input id="<?php echo $this->get_field_id('use_item_for_title'); ?>" name="<?php echo $this->get_field_name('use_item_for_title'); ?>" type="checkbox" <?php checked(isset($instance['use_item_for_title']) ? $instance['use_item_for_title'] : 0); ?> />&nbsp;<label for="<?php echo $this->get_field_id('use_item_for_title'); ?>"><?php _e('Use Current Menu Item for Title'); ?></label></p>
<?php
	}
}

add_action('widgets_init', 'submenu_widgets_init');
function submenu_widgets_init()
{
    register_widget('Submenu_Widget');
}