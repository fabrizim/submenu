<?php

static $submenu_current_item_text=null;
static $submenu_current_item_link=null;

function submenu_get_current_item_text($menu='')
{
    global $submenu_current_item_text;
		return $submenu_current_item_text;
}

function submenu_get_current_item_link($menu='')
{
    global $submenu_current_item_link;
		return $submenu_current_item_link;
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
		
		add_filter('nav_menu_css_class', 'submenu_nav_menu_css_class_filter', 10, 2);
    $menu = wp_nav_menu($options);
		remove_filter('nav_menu_css_class', 'submenu_nav_menu_css_class_filter', 10, 2);
		
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
		$levels = 0;
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
				else {
						if( !$li->getElementsByTagName('ul')->length ){
								return '';
						}
						$a = $li->getElementsByTagName('a')->item(0);
						$submenu_current_item_text = (string)$a->nodeValue;
						$submenu_current_item_link = $a->getAttribute('href');
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
    return apply_filters('submenu_html', $menu);
}

function submenu_nav_menu_css_class_filter($classes, $item)
{
		
		if( !is_singular() || in_array( get_post_type(), array('post','page') ) ){
				return $classes;
		}
		if( in_array('current_page_parent', $classes ) ){
				$classes = array_filter( $classes, 'submenu_remove_current_page_parent');
		}
		
		$archive_link = get_post_type_archive_link( get_post_type() );
		
		if( preg_replace('#/$#', '', $archive_link) === preg_replace('#/$#', '', $item->url) ){
				$classes[] = 'current_page_parent';
		}
		return $classes;
}

function submenu_remove_current_page_parent($cls)
{
		return $cls !== 'current_page_parent';
}

function submenu_add_class($node, $class)
{
    if( !is_a($node, 'DOMElement')) return;
    if( !submenu_has_class($node, $class) ){
        $node->setAttribute( 'class', $node->getAttribute('class').' '.$class);
    }
}

function submenu_remove_class($node, $class)
{
    if( !is_a($node, 'DOMElement')) return;
    if( submenu_has_class($node, $class) ){
        $classes = preg_split('/\s+/',$node->getAttribute('class'));
        $node->setAttribute('class', array_diff( $classes, array($class) ));
    }
}

function submenu_has_class($node, $class)
{
    if( !is_a($node, 'DOMElement')) return false;
    $classes = preg_split('/\s+/',$node->getAttribute('class'));
    return in_array( $class, $classes );
}