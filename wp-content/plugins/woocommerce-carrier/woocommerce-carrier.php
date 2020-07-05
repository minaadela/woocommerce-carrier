<?php
/*
Plugin Name: WooCommerce Carrier ID
Description: This is Coding Test for WordPress Backend Developers.
Version: 1.0.0
Author: Mina Adel
Author URI: mailto:mina.adelm@gmail.com
*/

if (! defined('WPINC')) {
    die();
}

require_once(plugin_basename('classes/class-woocommerce-carrier.php'));

function woocommerce_carrier_init()
{
    $plugin = new WooCommerce_Carrier();
    $plugin->run();
}

add_action('init', 'woocommerce_carrier_init');