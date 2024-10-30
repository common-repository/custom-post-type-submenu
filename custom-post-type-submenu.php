<?php
/*
Plugin Name: custom post type submenu
Plugin URI: 
Description: 
Version: 0.2
Author: Andreas Riedmüller
Author URI: http://gehirnstroem.at
License: GPL2
*/

/*  Copyright 2012  Laudanum  (email : mr.snow@houseoflaudanum.com )

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

	if ( ! is_admin() )
		add_filter('wp_nav_menu_objects', 'hol_nav_menu_objects', 10, 2);
	else
	{
		//add_filter('nav_menu_meta_box_object', 'hol_nav_menu_meta_box_object', 10, 2);
		add_action("admin_init", "hol_admin_init");
	}

	/* Add meta box to nav-menus page in admin */
	function hol_admin_init(){
		add_meta_box("add-custom-post-type", "Custom Post Types Submenu", "hol_add_custom_post_types_meta_to_admin", "nav-menus", "side", "low");
    }
    
	/* Callback for the meta box */
	function hol_add_custom_post_types_meta_to_admin(){
	
		/* get custom post types with archive support */
		
		$post_types = get_post_types( array( 'show_in_nav_menus' => true), 'object' );
			 
		/* hydrate the necessary properties for identification in the walker */
		/* http://codeseekah.com/2012/03/01/custom-post-type-archives-in-wordpress-menus-2/ */
		/* thanks soulseekah! */
		
		foreach ( $post_types as &$post_type ) {
			$post_type->classes = array();
			$post_type->type = $post_type->name;
			$post_type->object_id = $post_type->name;
			$post_type->title = $post_type->labels->name;
			$post_type->object = 'custom-post-type';
		}
	 
		/* the native menu checklist */
		$walker = new Walker_Nav_Menu_Checklist( array() );
		
		?>
	<div id="custom-post-type" class="posttypediv">
	  
		<div id="tabs-panel-custom-post-type" class="tabs-panel tabs-panel-active">
			<ul id="custom-post-type-checklist" class="categorychecklist form-no-clear">
		    <?php
		      echo walk_nav_menu_tree( array_map('wp_setup_nav_menu_item', $post_types), 0, (object) array( 'walker' => $walker) );
		    ?>
			</ul>
	    </div><!-- /.tabs-panel -->
	</div>
	    <p class="button-controls">
	    
		<span class="list-controls">
			<a href="/wordpress/wp-admin/nav-menus.php?selectall=1#custom-post-type" class="select-all">Select All</a>
		</span>
	    
	      <span class="add-to-menu">
	        <input type="submit" <?php disabled( $nav_menu_selected_id, 0 ); ?> class="button-secondary submit-add-to-menu right" value="<?php esc_attr_e('Add to Menu'); ?>" name="add-custom-post-type-menu-item" id="submit-custom-post-type" />
			<span class="spinner"></span>
	      </span>
	    </p>
	    <?php
	

	}


	/* Detect menu_items that are type_label==Category and add posts in that 
    category to menu_items as children of that menu_item */
	function hol_nav_menu_objects($sorted_menu_items, $args) {

	  foreach ($sorted_menu_items as $menu_item) {

	    if ( $menu_item->object == "custom-post-type" ) {    
	        
		// Where should it link to if there is no archive and no post?
		// later after the "foreach ( $children as $child )" loop the url gets set to the first childs url.
		$menu_item->url = '?';
		
		$menu_item_parent = $menu_item->ID;
		$term_id = $menu_item->object_id;
		
		//  go and get all posts in this category
		//  @todo abstract out post_type to use all post_types that support this taxonomy type
		$args = array(
		  "orderby" => "post_title",
		  "order" => "ASC",
		  "post_type" =>  $menu_item->type,
		  "numberposts" => -1,
		);
		
        $children = get_posts($args);
        global $post;
        $current_in_children = 0;
        $first_child_guid = false;
        $i = 0;
        
        foreach ( $children as $child ) {
          
          $current = 0;
          
          if ( $post->ID == $child->ID && ( is_single() || is_page() )) {
            $current = 1;
            $current_in_children = 1;
          }
          
          $child->ID = count($sorted_menu_items);
          $child->menu_item_parent = $menu_item_parent;
          $child->post_type = 'nav_menu_item';
          $child->url = $child->guid;
          $child->title = $child->post_title;
          $child->menu_order = $i++;
          $child->object = 'post';
          $child->type = 'post_type';
          $child->type_label = 'Post';
          $child->target = '';
          $child->attr_title = '';
          $child->description = '';
          $child->xfn = '';
          $child->current = $current;
          
          if(!$first_child_guid)
          	$first_child_guid = $child->guid;
          
			/*
			  we are missing these attributes
			  object_id
			  current 0:1
			  current_item_ancestor ''
			  current_item_parent ''
			*/
			
          $child->classes = array(
            'menu-item',
            'menu-item',
            'menu-item-type-post_type',
          );
          
          //  check for current-menu-item and current_post_item            
          if ( $current ) {
            $child->classes[] = "current-menu-item";
            $child->classes[] = "current_post_item";
          }
          array_push($sorted_menu_items, $child);
        }
        
        	 // If custom post type has an archive, the parent will link to the archive. If not it will redirect to the first child.
        	 $post_type_archive_link = get_post_type_archive_link($menu_item->type);
        	 if($post_type_archive_link)
		         $menu_item->url = $post_type_archive_link;
        	 elseif($first_child_guid)
	         	$menu_item->url = $first_child_guid;	         
        	
        	// Give the class current-menu-parent if a child has $current == 1
        	// Maybe there is another way to do that ?
        	if($current_in_children == 1)
        	{
	            $menu_item->classes[] = "current-menu-parent";
        	}
        
	    }
	  }
	  return $sorted_menu_items;
	}

?>