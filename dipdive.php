<?php
/**
Plugin Name: Dipdive
Plugin URI: http://dipdive.com
Description: Add Dipdive media to your posts and pages with ease.
Author: Dipdive
Version: 1.1.1
Author URI: http://dipdive.com

Copyright 2010 Dipdive (http://dipdive.com)

*/

require_once WP_PLUGIN_DIR . '/dipdive/classes/Dipdive.php';

function Dipdive() {
    global $Dipdive;
    $Dipdive = new Dipdive();
}

add_action( 'init', 'Dipdive' );
