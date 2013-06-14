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
        $options = array('theme_location'=>$options);
    }
    // need to print out the wp_menu_nav for the big buttons
    $options = array_merge(array (
        'menu'                  => '',
        'container'             => 'div',
        'container_class'       => 'sub-menu',
        'menu_class'            => 'sub-menu-ul',
        'fallback_cb'           => 'wp_page_menu',
        'before'                => '',
        'after'                 => '',
        'link_before'           => '',
        'link_after'            => '',
        'depth'                 => 0,
        'walker'                => '',
        'echo'                  => false
    ), $options);
    
    $menu = wp_nav_menu($options);
		
		if( !$menu ) return '';
		
    $doc = new DOMDocument('1.0', 'utf-8');
    $doc->loadXML($menu);
    // funk around with the document
    $nodes = $doc->getElementsByTagName('li');
    $active = false;
    
    foreach($nodes as $node){
        if( submenu_has_class( $node, 'current-menu-item' )     ||
            submenu_has_class( $node, 'current-menu-parent' )   ||
            submenu_has_class( $node, 'current_page_parent' )
        ){
            submenu_add_class( $node, 'is-active-item');
            foreach( $node->childNodes as $a){
                if( $a->tagName == 'a') submenu_add_class( $a, 'active-link');
            }
            $active = $node;
            break;
        }
    }
    if( !$active ){
        return '';
    }
    $parent = $node;
    while( ($parent = $parent->parentNode) && $parent != $doc ){
				// wait a minute there hot shot, does this have a sneaky
				// parent that didnt announce its activeness?
				if( $parent->tagName == 'li' ){
						$active = $parent;
				}
        submenu_add_class($parent, 'is-active-item');
    }
    
    // delete top level out
    $ul = $doc->getElementsByTagName('ul')->item(0);
    $toRemove = array();
    foreach( $ul->childNodes as $li ){
        if( !submenu_has_class( $li, 'is-active-item') ){
            $toRemove[] = $li;
        }
    }
    
    while( count($toRemove) ){
        $li = array_pop( $toRemove );
        foreach( $ul->childNodes as $node ){
            if( $node === $li ){
                $ul->removeChild( $node );
                break;
            }
        }
    }
    
    // $menu = $doc->saveXML($doc->documentElement);
		$menu = $doc->saveXML( $active->getElementsByTagName('ul')->item(0) );
    return $menu;
}

function submenu_add_class($node, $class){
    if( !is_a($node, 'DOMElement')) return;
    if( !submenu_has_class($node, $class) ){
        $node->setAttribute( 'class', $node->getAttribute('class').' '.$class);
    }
}

function submenu_remove_class($node, $class){
    if( !is_a($node, 'DOMElement')) return;
    if( submenu_has_class($node, $class) ){
        $classes = preg_split('/\s+/',$node->getAttribute('class'));
        $node->setAttribute('class', array_diff( $classes, array($class) ));
    }
}

function submenu_has_class($node, $class){
    if( !is_a($node, 'DOMElement')) return false;
    $classes = preg_split('/\s+/',$node->getAttribute('class'));
    return in_array( $class, $classes );
}