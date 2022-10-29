<?php

/**
 * @copyright (c) 2019.
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

class Freemius_Config
{
    public function init()
    {
        global  $quick_contact_form_fs ;
        
        if ( !isset( $quick_contact_form_fs ) ) {
            // Include Freemius SDK.
            require_once QUICK_CONTACT_FORM_PLUGIN_DIR . 'vendor/freemius/wordpress-sdk/start.php';
            $quick_contact_form_fs = fs_dynamic_init( array(
                'id'             => '6357',
                'slug'           => 'quick-contact-form',
                'type'           => 'plugin',
                'public_key'     => 'pk_eed929374c64c0b651ef518c80be8',
                'is_premium'     => false,
                'has_addons'     => false,
                'has_paid_plans' => true,
                'trial'          => array(
                'days'               => 14,
                'is_require_payment' => true,
            ),
                'navigation'     => 'tabs',
                'menu'           => array(
                'slug'    => 'quick-contact-form',
                'contact' => false,
                'support' => false,
                'parent'  => array(
                'slug' => 'options-general.php',
            ),
            ),
                'is_live'        => true,
            ) );
        }
        
        return $quick_contact_form_fs;
    }

}