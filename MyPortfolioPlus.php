<?php
/*
Plugin Name: myPortfolio Plus
Plugin URI: http://www.screensugar.co.uk/2010/09/my-portfolio-plus/
Description: A Portfolio driven by project post types for WordPress 3.0 and above.
Author: Shaun Bohannon
Version: 1.0.4
Author URI: http://www.screensugar.co.uk
License: GPL2
*/
require_once("AppSTW.php");
require_once("WPSS_Project.php");
require_once("template-tags.php");

class MyPortfolioPlus
{
    const WP_OPTION_GROUP = "wpss_myportfolio-option-group";	
	
	var $meta_fields = array("sugar-url", "sugar-clientname", "sugar-date");
	var $pluginDir;
	var $pluginUrl;
	var $templateDir;
	var $thumbGen;
	
	function MyPortfolioPlus()
	{
		$this->pluginDir = dirname( __FILE__ );
		$this->pluginUrl = "/wp-content/plugins/my-portfolio-plus";
		$this->templateDir = $this->pluginDir . "/views/";
		$this->thumbGen = new AppSTW();
		
		// Register custom post types
		register_post_type('project', array(
			'label' => __('Projects'),
			'singular_label' => __('Project'),
			'labels' => array(
			'name' => __( 'Projects' ),
			'singular_name' => __( 'Project' ),
			'add_new' => __( 'Add New' ),
			'add_new_item' => __( 'Add New Project' ),
			'edit' => __( 'Edit' ),
			'edit_item' => __( 'Edit Project' ),
			'new_item' => __( 'New Project' ),
			'view' => __( 'View Project' ),
			'view_item' => __( 'View Project' ),
			'search_items' => __( 'Search Projects' ),
			'not_found' => __( 'No projects found' ),
			'not_found_in_trash' => __( 'No projects found in Trash' ),
			'parent' => __( 'Parent Project' ),
			),
			'public' => true,
			'show_ui' => true, // UI in admin panel
			'_builtin' => false, // It's a custom post type, not built in
			'_edit_link' => 'post.php?post=%d',
			'capability_type' => 'post',
			'hierarchical' => false,
			'rewrite' => array("slug" => "project"), // Permalinks
			'query_var' => "project", // This goes to the WP_Query schema
			'supports' => array('title','author', 'excerpt', 'editor', 'thumbnail' /*,'custom-fields'*/) // Let's use custom fields for debugging purposes only
		));
		
		add_filter("manage_edit-project_columns", array(&$this, "edit_columns"));
		add_action("manage_posts_custom_column", array(&$this, "custom_columns"));
		
		$tax_labels = array(
		    'name' => _x( 'Platforms', 'taxonomy general name' ),
		    'singular_name' => _x( 'Platform', 'taxonomy singular name' ),
		    'search_items' =>  __( 'Search Platforms' ),
		    'all_items' => __( 'All Platforms' ),
		    'parent_item' => __( 'Parent Platform' ),
		    'parent_item_colon' => __( 'Parent Platform:' ),
		    'edit_item' => __( 'Edit Platform' ), 
		    'update_item' => __( 'Update Platform' ),
		    'add_new_item' => __( 'Add New Platform' ),
		    'new_item_name' => __( 'New Platform Name' ),
		  );
		
		// Register custom taxonomy
		register_taxonomy("platform", array("project"), array(
		"hierarchical" => true, 
		"labels" => $tax_labels,
		"rewrite" => array('slug' => "projects/platform")
		));
		
		//register_taxonomy_for_object_type('project', 'platform');
		
		// Admin interface init
		add_action("admin_init", array(&$this, "admin_init"));
		add_action("template_redirect", array(&$this, 'template_redirect'));
		
		// Insert post hook
		add_action("wp_insert_post", array(&$this, "wp_insert_post"), 10, 2);
		
		//Wp Head Hook
		add_action('wp_head', array(&$this, 'portfolio_headers'));
		
		//Wp Admin Head Hook
		add_action('admin_head', array(&$this, 'sugar_admin_header'));
		
		//Add Menu Item
		add_action('admin_menu', array(&$this, 'my_portfolio_menu'));
		//Add Admin Settings
		add_action('admin_init',  array(&$this, 'register_mysettings'));
		//Add Admin Notices
		add_action('admin_notices',  array(&$this, 'show_notices'));
		
		//Default Options
		register_activation_hook( __FILE__, 'activate_my_portfolio' );
	}
	
	function admin_init() 
	{
		// Custom meta boxes for the edit project screen
		add_meta_box("sugar-meta", "Project Details", array(&$this, "meta_details"), "project", "side", "low");
	}
	
	function show_notices()
	{
		//6395cc8341c2892
		//ae206
		$notices = "";
		
		//Checks to ensure STW details are entered in Options Page
		if(get_option('wpss_stw_access') == null || get_option('wpss_stw_access') == null)
		{
			$notices .= "<p>The plugin will not function properly until you add the API details for Shrink The Web on the <a href='edit.php?post_type=project&page=myportfolio-options'>options page</a>.</p>";
		}
		
		//Checks that Thumbnail Directory is writeable
		if(!$this->is__writable($this->thumbGen->thumbDir))
		{
			$notices .= "<p>Thumbnail Directory is not writeable. This directory should be created automatically when the plugin is activated. Try re-activating the plugin, and then check if your thumbnail directory is writable 'uploads/webimages'</p>";
		}
		
		if ($notices != "")
		{
			echo "<div class='error'><h3>myPortfolio Plus Errors</h3>".$notices."</div>";
		}
		
		
	}
	
	function activate_my_portfolio()
	{
		add_option("wpss_projects_title", 'Projects |');
		add_option("wpss_show_platforms", 'on');	
		flush_rewrite_rules();
	}
	
	function my_portfolio_menu() 
	{
		add_submenu_page('edit.php?post_type=project', __('Options','options'), __('Options','options'), 
		'manage_options', 'myportfolio-options', array(&$this, 'my_portfolio_options'));
	}
	
	function my_portfolio_options() 
	{
		if (!current_user_can('manage_options'))  
		{
			wp_die( __('You do not have sufficient permissions to access this page.') );
		}
		require_once("incl/options.incl.php");
	}
	
	function register_mysettings() 
	{ // whitelist options
		register_setting( self::WP_OPTION_GROUP, 'wpss_projects_title' );
		register_setting( self::WP_OPTION_GROUP, 'wpss_show_platforms' );
		register_setting( self::WP_OPTION_GROUP, 'wpss_stw_access' );
		register_setting( self::WP_OPTION_GROUP, 'wpss_stw_secret' );
	}
	
	function sugar_admin_header()
	{
		require_once("incl/adminhead.incl.php");
	}
	
	function edit_columns($columns)
	{
		$columns = array(
			"cb" => "<input type=\"checkbox\" />",
			"title" => "Project Title",
			"sugar_description" => "Description",
			"sugar_url" => "URL",
			"sugar_clientname" => "Client Name",
			"sugar_date" => "Completion Date",
		);
		
		return $columns;
	}
	
	function custom_columns($column)
	{
		global $post;
		switch ($column)
		{
			case "sugar_description":
				the_excerpt();
				break;
			case "sugar_url":
				$custom = get_post_custom();
				echo $custom["sugar-url"][0];
				break;
			case "sugar_clientname":
				$custom = get_post_custom();
				echo $custom["sugar-clientname"][0];
				break;
			case "sugar_date":
				$custom = get_post_custom();
				echo $custom["sugar-date"][0];
				break;
		}
	}
	
	//Insert stuff into header where needed
	function portfolio_headers()
	{
		//Could check for existence of css and js in user template here
		require_once("incl/header.incl.php");
	}
	
	// Template selection
	function template_redirect()
	{
		global $wp;

		//print_r($wp->query_vars);
		if (array_key_exists("post_type", $wp->query_vars) &&
			$wp->query_vars["post_type"] == "project")
		{
			//include(TEMPLATEPATH . "/project.php");
			if( '' == locate_template( array( 'portfolio/single-project.php' ), true ) ) 
			{
				include( $this->templateDir . "single-project.php");
				die();
			}
		}
		if (array_key_exists("taxonomy", $wp->query_vars) &&
			$wp->query_vars["taxonomy"] == "platform")
		{
			if( '' == locate_template( array( 'portfolio/taxonomy-platform.php' ), true ) ) 
			{
				include( $this->templateDir . "taxonomy-platform.php");
				die();
			}
		}
		if (array_key_exists("pagename", $wp->query_vars) &&
			$wp->query_vars["pagename"] == "projects")
		{
			if( '' == locate_template( array( 'portfolio/projects.php' ), true ) ) 
			{	
				//Edit title of projects page
				add_filter('wp_title', array(&$this, 'edit_projects_page_title'));
				include( $this->templateDir . "projects.php");
				die();
			}
		}
		
		
	}
	
	function edit_projects_page_title()
	{
		return  get_option('wpss_projects_title')." ";
	}
	
	// When a post is inserted or updated
	function wp_insert_post($post_id, $post = null)
	{
		if ($post->post_type == "project")
		{
			// Loop through the POST data
			foreach ($this->meta_fields as $key)
			{
				$value = @$_POST[$key];
				if (empty($value))
				{
					delete_post_meta($post_id, $key);
					continue;
				}

				// If value is a string it should be unique
				if (!is_array($value))
				{	
					// Update meta
					if (!update_post_meta($post_id, $key, $value))
					{
						// Or add the meta data
						add_post_meta($post_id, $key, $value);
					}
				}
				else
				{
					// If passed along is an array, we should remove all previous data
					delete_post_meta($post_id, $key);
					
					// Loop through the array adding new values to the post meta as different entries with the same name
					foreach ($value as $entry)
						add_post_meta($post_id, $key, $entry);
				}
			}
		}
		flush_rewrite_rules();
	}
	
	// Admin post meta contents
	function meta_details()
	{
		global $post;
		$custom = get_post_custom($post->ID);
		$url = $client = $date = "";
		
		if(array_key_exists("sugar-url", $custom))
			$url = $custom["sugar-url"][0];
			
		if(array_key_exists("sugar-clientname", $custom))
			$client = $custom["sugar-clientname"][0];
			
		if(array_key_exists("sugar-date", $custom))
			$date = $custom["sugar-date"][0];	
			
		//Include Editor  
		require_once("incl/editor.incl.php");
	}
	
	public function getImage($url)
	{	
		$imageSrc = $this->thumbGen->getXLargeThumbnail($url);
		if($imageSrc != null)
			return $this->thumbGen->thumbUri.$imageSrc;
		else
			return $this->pluginUrl."/img/noimage.png";
	}
	
	//Checks folder is writable
	function is__writable($path) 
	{
	    if ($path{strlen($path)-1}=='/') // recursively return a temporary file path
	        return $this->is__writable($path.uniqid(mt_rand()).'.tmp');
	    else if (is_dir($path))
	        return $this->is__writable($path.'/'.uniqid(mt_rand()).'.tmp');
	    // check tmp file for read/write capabilities
	    $rm = file_exists($path);
	    $f = @fopen($path, 'a');
	    if ($f===false)
	        return false;
	    fclose($f);
	    if (!$rm)
	        unlink($path);
	    return true;
	}
	
}

// Initiate the plugin
add_action("init", "MyPortfolioPlusInit");
function MyPortfolioPlusInit() 
{ 
	global $myPortfolio;
	$myPortfolio = new MyPortfolioPlus();
}