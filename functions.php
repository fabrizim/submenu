<?php

static $submenu_current_item_text=array();

function submenu_get_current_item_text($menu='')
{
    return $submenu_current_item_text[$menu];
}

function submenu_get_html($options=array())
{
    if( !is_array($options) ){
        if( !is_string( $options )){
            $options = '';
        }
        $options = array('menu'=>$options);
    }
    // need to print out the wp_menu_nav for the big buttons
    $options = array_merge(array (
		'menu'				=> '',
		'container'			=> 'div',
		'container_class'	=> 'sub-menu',
		'menu_class'		=> 'sub-menu-ul',
		'fallback_cb'		=> 'wp_page_menu',
		'before'			=> '',
		'after'				=> '',
		'link_before'		=> '',
		'link_after'		=> '',
		'depth'				=> 0,
		'walker'			=> '',
		'echo'				=> false
	), $options);
    
    $menu = wp_nav_menu($options);
    
    $doc = new DOMDocument();
	$doc->loadHTML($menu);
    
    $newDoc = new DOMDocument();
    $newDoc->formatOutput=true;
    
    $newDoc->loadXML('<ul></ul>');
	
	// funk around with the document
	$nodes = $doc->getElementsByTagName('li');
	
	foreach($nodes as $node){
        $classes = $node->getAttribute('class');
        if( preg_match('#(^|\s)(current\-menu\-item)(\s|$)#', $classes )){
            $submenu_current_item_text[$options['menu']] = (string)$node->firstChild->nodeValue;
            $node = $newDoc->importNode($node, true);
            $newDoc->documentElement->appendChild($node);
            break;
        }
	}
	
	$menu = $newDoc->saveXML();
	$menu = preg_replace('#^<\?xml.*?\?>#', '', $menu);
    return $menu;
}