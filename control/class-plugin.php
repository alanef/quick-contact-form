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
 */

namespace Quick_Contact_Form\Control;

use Quick_Contact_Form\UI\Admin\Admin;
use Quick_Contact_Form\UI\Admin\Admin_Settings;
use Quick_Contact_Form\UI\User\FrontEnd;

class Plugin {

	private $plugin_name;
	private $version;
	/**
	 * @param \Freemius $freemius Object for freemius.
	 */
	private $freemius;

	public function __construct( $plugin_name, $version, $freemius ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->freemius    = $freemius;
	}

	public function run() {
		$this->set_locale();
//		$this->settings_pages();
		$this->define_admin_hooks();
//		$this->define_public_hooks();
		update_option('qcf_legacy_free',true);
		require_once QUICK_CONTACT_FORM_PLUGIN_DIR . 'legacy/quick-contact-form.php';
	}

	private function set_locale() {
		add_action( 'init', function() {
			load_plugin_textdomain(
				$this->plugin_name,
				false,
				basename(QUICK_CONTACT_FORM_PLUGIN_DIR ). '/languages/'
			);
		});
	}

	private function settings_pages() {
		$settings = new Admin_Settings( $this->plugin_name, $this->version, $this->freemius );
		$settings->hooks();
	}

	private function define_admin_hooks() {
		$admin = new Admin( $this->plugin_name, $this->version, $this->freemius );
		$admin->hooks();
	}

	private function define_public_hooks() {
		$public = new FrontEnd( $this->plugin_name, $this->version, $this->freemius);
		$public->hooks();
	}
}
