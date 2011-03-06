<?php

static $submenu_current_item_text=array();

function submenu_get_current_item_text($menu='')
{
    global $submenu_current_item_text;
    return $submenu_current_item_text[$menu];
}

function submenu_get_html($options=array())
{
    global $submenu_current_item_text;
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
            
            foreach( $node->childNodes as $a ){
                if( $a->tagName == 'a' ){
                    $submenu_current_item_text[$options['menu']] = $a->nodeValue;
                }
            }
            
            // lets not actually import this, lets grab all the children...
            $found = false;
            foreach( $node->childNodes as $child ){
                
                if( $child->tagName == 'div'){
                    foreach( $child->childNodes as $ul){
                        if( $ul->tagName == 'ul' ){
                            $child = $ul;
                            break;
                        }
                    }
                }
                
                if( $child->tagName == 'ul'){
                    foreach( $child->childNodes as $li ){
                        $found = true;
                        $li = $newDoc->importNode($li, true);
                        $newDoc->documentElement->appendChild($li);
                    }
                }
                // $node = $newDoc->importNode($node, true);
                // $newDoc->documentElement->appendChild($node);
             
            }
            if( !$found ){
                return '';
            }
            break;
        }
	}
	
	$menu = $newDoc->saveXML();
	$menu = preg_replace('#^<\?xml.*?\?>#', '', $menu);
    return $menu;
}