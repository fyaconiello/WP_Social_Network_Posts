<?php
require_once(sprintf("%s/../../../../wp-load.php", dirname(__FILE__)));
require_once(sprintf("%s/../wp_social_network_posts.php", dirname(__FILE__)));


if(class_exists("WP_Social_Network_Posts"))
{
	$plugin = new WP_Social_Network_Posts();
	$plugin->import_facebook_posts();
}