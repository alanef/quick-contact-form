<?php
/**
 * @copyright (c) 2020.
 * @author            Alan Fuller (support@fullworks)
 * @licence           GPL V3 https://www.gnu.org/licenses/gpl-3.0.en.html
 * @link                  https://fullworks.net
 *
 * This file is part of  a Fullworks plugin.
 *
 *   This plugin is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU General Public License as published by
 *     the Free Software Foundation, either version 3 of the License, or
 *     (at your option) any later version.
 *
 *     This plugin is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU General Public License for more details.
 *
 *     You should have received a copy of the GNU General Public License
 *     along with  this plugin.  https://www.gnu.org/licenses/gpl-3.0.en.html
 *
 * Plugin Name: Quick Contact Form
 * Plugin URI: https://fullworks.net/products/quick-contact-form
 * Description: A really, really simple GDRP compliant contact form. There is nothing to configure, just add your email address and it's ready to go. But you then have access to a huge range of easy to use features.
 * Version: 8.0.3
 * Author: Fullworks
 * Author URI: https://fullworks.net/
 * Requires PHP: 5.6
 * Requires at least: 4.6
 * Text Domain: quick-contact-form
 * Domain Path: /languages
 *
 * Original Author: Aerin
 *
  *
*/

namespace Quick_Contact_Form;

use \Quick_Contact_Form\Control\Plugin;
use \Quick_Contact_Form\Control\Freemius_Config;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
if ( ! function_exists( 'Quick_Contact_Form\run_Quick_Contact_Form' ) ) {
	define( 'QUICK_CONTACT_FORM_PLUGIN_DIR', trailingslashit( plugin_dir_path( __FILE__ ) ) );
	define( 'QUICK_CONTACT_FORM_PLUGIN_FILE', plugin_basename( __FILE__ ) );
	define( 'QUICK_CONTACT_FORM_PLUGIN_NAME', 'quick-contact-form' );

// Include the autoloader so we can dynamically include the classes.
	require_once QUICK_CONTACT_FORM_PLUGIN_DIR . 'control/autoloader.php';


	function run_Quick_Contact_Form() {
		$freemius = new Freemius_Config();
		$freemius = $freemius->init();
		// Signal that SDK was initiated.
		do_action( 'quick_contact_form_fs_loaded' );

		register_activation_hook( __FILE__, array( '\Quick_Contact_Form\Control\Activator', 'activate' ) );

		register_deactivation_hook( __FILE__, array( '\Quick_Contact_Form\Control\Deactivator', 'deactivate' ) );

		/**
		 * @var \Freemius $freemius freemius SDK.
		 */
		$freemius->add_action( 'after_uninstall', array( '\Quick_Contact_Form\Control\Uninstall', 'uninstall' ) );

		$plugin = new Plugin( 'quick-contact-form',
			'8.0.1',
			$freemius );
		$plugin->run();
	}

	run_Quick_Contact_Form();
} else {
	die( esc_html__( 'Cannot execute as the plugin already exists, if you have a free version installed deactivate that and try again', 'quick-contact-form' ) );
}

