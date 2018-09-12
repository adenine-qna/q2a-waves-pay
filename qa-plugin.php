<?php
/*
  Plugin Name: Waves Pay
  Plugin URI:
  Plugin Description: Waves and Waves-based token payment gateway
  Plugin Version: 1.0
  Plugin Date: 2018-08-30
  Plugin Author: Abdullah Daud
  Plugin Author URI: https://github.com/chelahmy
  Plugin License: GPLv2
  Plugin Minimum Question2Answer Version: 1.5
  Plugin Update Check URI:
*/

if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
	header('Location: ../../');
	exit;
}

qa_register_plugin_layer(
	'qa-waves-pay-layer.php', // PHP file containing layer class 
	'Waves Pay Layer' // human-readable name of layer
);

qa_register_plugin_module(
	'page', // type of module
	'qa-waves-assets.php', // PHP file containing module class
	'qa_waves_assets_page', // name of module class
	'Waves Assets Page' // human-readable name of module
);

qa_register_plugin_module(
	'page', // type of module
	'qa-waves-acct.php', // PHP file containing module class
	'qa_waves_acct_page', // name of module class
	'Waves Account Page' // human-readable name of module
);

qa_register_plugin_module(
	'page', // type of module
	'qa-waves-payments.php', // PHP file containing module class
	'qa_waves_payments_page', // name of module class
	'Waves Payments Page' // human-readable name of module
);

qa_register_plugin_phrases(
    'qa-waves-pay-lang-*.php', // pattern for language files
    'plugin_waves_pay_desc' // prefix to retrieve phrases
);

