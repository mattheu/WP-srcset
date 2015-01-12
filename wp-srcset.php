<?php
/*
Plugin Name: WP Srcset
Description: Automatic high resolution retina images using srcset.
Version: 1.1.0
Author: Matthew Haines-Young
Author URI: http://matth.eu/
*/

/*  Copyright 2011 Human Made Limited  (email : hello@humanmade.co.uk)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

require_once( __DIR__ . '/class-wp-srcset.php' );

$retina = WP_Srcset::get_instance();

register_activation_hook( __FILE__ , array( $retina, 'plugin_activation_check' ) );
