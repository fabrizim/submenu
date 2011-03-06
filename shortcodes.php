<?php

add_shortcode('submenu', 'submenu_shortcode');
function submenu_shortcode($atts, $content='', $code='')
{
    echo submenu_get_html(@$atts['menu']);
}