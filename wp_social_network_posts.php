<?php
/*
Plugin Name: Social Network Posts
Plugin URI: http://www.yaconiello.com/
Description: A simple wordpress plugin for pulling posts from social network
Version: 1.0
Author: Francis Yaconiello
Author URI: http://www.yaconiello.com
License: GPL2
*/
/*
Copyright 2012  Francis Yaconiello  (email : francis@yaconiello.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General License for more details.

You should have received a copy of the GNU General License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


if(!class_exists('WP_Social_Network_Posts'))
{
    class WP_Social_Network_Posts
    {
        // Used to create the inner meta boxes and in the save function
        var $_meta = array(
            'fbid' => array(
                'label' => 'Facebook ID',
                'help_text' => 'The ID from facebook\'s website.',
                'widget' => 'text'
            ),
        );
        
        /**
         * Construct the plugin object
         */
        function __construct()
        {
            // register actions
            add_action('init', array(&$this, 'init'));
			add_action('admin_init', array(&$this, 'admin_init'));
			add_action('admin_menu', array(&$this, 'add_menu'));
        } // END function __construct
        
        /**
         * Initialize the plugin
         */
        function init()
        {
            // register a custom post type
            register_post_type('social_network_post',
                array(
                    'labels' => array(
                        'name' => __(sprintf('%ss', ucwords(str_replace("_", " ", "social_network_post")))),
                        'singular_name' => __(ucwords(str_replace("_", " ", "social_network_post")))
                    ),
                    'public' => true,
                    'has_archive' => true,
                    'description' => __("Posts from Facebook and Twitter"),
                    'supports' => array(
                        'title', 'editor',
                    ),
                )
            ); // http://codex.wordpress.org/Function_Reference/register_post_type for more options
            add_action('save_post', array(&$this, 'save_post'));
        }

		/**
		 * hook into WP's admin_init action hook
		 */
		function admin_init()
		{
			// Set up the settings for this plugin
			$this->init_settings();
            // Add metaboxes
            add_action('add_meta_boxes', array(&$this, 'add_meta_boxes'));
		} // END static function activate

        /**
         * hook into WP's add_meta_boxes action hook
         */
        function add_meta_boxes()
        {
            // Add this metabox to every selected post
            add_meta_box( 
                'id_wp_sn_posts_section',
                sprintf('Social Network IDs'),
                array(&$this, 'add_inner_meta_boxes'),
                'social_network_post'
            );					
        } // END function add_meta_boxes()

		/**
		 * called off of the add_meta_boxes function
		 */		
		function add_inner_meta_boxes($post)
		{		
			// Render the job order metabox
			include(sprintf("%s/templates/sn_post_metabox.php", dirname(__FILE__)));			
		} // END function add_inner_meta_boxes($post)
		
        /**
        * Save the metaboxes for this custom post type
        */
        function save_post($post_id)
        {
            // verify if this is an auto save routine. 
            // If it is our form has not been submitted, so we dont want to do anything
            if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            {
                return;
            }

            if($_POST['post_type'] == 'social_network_post' && current_user_can('edit_post', $post_id))
            {
                foreach($this->_meta as $field => $data)
                {
                    // Update the post's meta field
                    update_post_meta($post_id, $field, $_POST[$field]);
                }
            }
            else
            {
                return;
            } // if($_POST['post_type'] == 'social_network_post' && current_user_can('edit_post', $post_id))
        } // END function save_post($post_id)
		
		/**
		 * add a menu
		 */		
		function add_menu()
		{
			add_options_page('WP Social Network Posts Settings', 'WP SN Posts', 'manage_options', 'wp_sn_posts', array(&$this, 'plugin_settings_page'));
		} // END function add_menu()
		
		/**
		 * Initialize some custom settings
		 */		
		function init_settings()
		{
			// register the settings for this plugin
			register_setting('wp_sn_posts-group', 'fb_app_id');
			register_setting('wp_sn_posts-group', 'fb_secret');
			register_setting('wp_sn_posts-group', 'fb_username');
		} // END function init_custom_settings()
		
		/**
		 * Menu Callback
		 */		
		function plugin_settings_page()
		{
			if(!current_user_can('manage_options'))
			{
				wp_die(__('You do not have sufficient permissions to access this page.'));
			}
			
			// Render the settings template
			include(sprintf("%s/templates/settings.php", dirname(__FILE__)));
		} // END function plugin_settings_page()
		
        /**
         * A function to initiate import of facebook posts
         */
        function import_facebook_posts()
		{
		    global $wpdb;
            //$this->renew_facebook_access_token();
            require_once(sprintf("%s/facebook/facebook.php", dirname(__FILE__)));
            $facebook = new Facebook(
                array(
                    'appId'  => get_option('fb_app_id'),
                    'secret' => get_option('fb_secret'),
                )
            );

            $url = '/' . get_option('fb_username') . '/posts?fields=id,name,created_time,message,story,type&limit=10';
            $page = $facebook->api($url);
            while(count($page['data']) > 0)
            {
                foreach($page['data'] as $fb_post)
                {
                    // Set up the post
                    $post = array(
                        'post_status' => 'publish',
                        'post_type' => 'social_network_post',
                        'post_title' => $fb_post['name'] ? $fb_post['name'] : ($fb_post['message'] ? $fb_post['message'] : $fb_post['story']),
                        'post_content' => $fb_post['message'] ? $fb_post['message'] : $fb_post['story'],
                        'post_author' => 1,
                        'filter' => true
                    );
                    print_r($post);
                    
                    // Try to get a post with this JobOrderID
                    $post_id = $wpdb->get_var(
                        sprintf(
                            "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '%s' AND meta_value = '%s' LIMIT 1",
                            "fbid",
                            $fb_post['id']
                        )
                    );
                    echo "\n" . $post_id . ":";
                    
                    // Insert or update a post
                    if($post_id != 0)
                    {
                        $post['ID'] = $post_id;
                        $post_id = wp_update_post($post);
                    }
                    else
                    {
                        $post_id = wp_insert_post($post);
                    }
                    echo $post_id . "\n";
                    
                    // If post_id 
                    if(!empty($post_id) && $post_id > 0)
                    {
                        // then update all of the meta
                        @update_post_meta($post_id, "fbid", $fb_post['id']);
                    } 
                }
                            
                $url_parts = parse_url($page['paging']['next']);
                $url = '/' . get_option('fb_username') . '/posts?' . $url_parts['query'];
                $page = $facebook->api($url);              
            }
		} // END function import_facebook_posts()

        /**
         * Activate the plugin
         */
        static function activate()
        {
            // Do nothing
        } // END static function activate

        /**
         * Deactivate the plugin
         */        
        static function deactivate()
        {
            // Do nothing
        } // END static function deactivate
    } // END class WP_Social_Network_Posts
} // END if(!class_exists('WP_Social_Network_Posts'))

if(class_exists('WP_Social_Network_Posts'))
{
    // Installation and uninstallation hooks
    register_activation_hook(__FILE__, array('WP_Social_Network_Posts', 'activate'));
    register_deactivation_hook(__FILE__, array('WP_Social_Network_Posts', 'deactivate'));

    // instantiate the plugin class
    $wp_social_network_posts_plugin = new WP_Social_Network_Posts(); 
	
	// Add a link to the settings page onto the plugin page
	if(isset($wp_social_network_posts_plugin))
	{
		// Add the settings link to the plugins page
		function plugin_settings_link($links)
		{ 
		  $settings_link = '<a href="options-general.php?page=wp_sn_posts">Settings</a>'; 
		  array_unshift($links, $settings_link); 
		  return $links; 
		}

		$plugin = plugin_basename(__FILE__); 
		add_filter("plugin_action_links_$plugin", 'plugin_settings_link');
	}		
}