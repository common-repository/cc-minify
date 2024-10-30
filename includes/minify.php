<?php

/*
	Copyright (C) 2016 by Clearcode <http://clearcode.cc>
	and associates (see AUTHORS.txt file).

	This file is part of CC-Minify.

	CC-Minify is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	CC-Minify is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with CC-Minify; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

namespace Clearcode;

use Clearcode\Minify\Plugin;
use Clearcode\Minify\Settings;
use Clearcode\Minify\Styles;
use Clearcode\Minify\Scripts;

if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! class_exists( __NAMESPACE__ . '\Minify' ) ) {
	class Minify extends Plugin {
		public function __construct() {
			Settings::instance();
			Styles::instance();
			Scripts::instance();

			parent::__construct();
		}

		public function activation() {}

		public function deactivation() {}

		static public function error_log( $error = '', $class = '', $function = '' ) {
			$message = Minify::get( 'Name' ) . ' ';
			if ( $error )    $message .= Minify::__( 'Error' )    . ': ' . $error . ' ';
			if ( $class )    $message .= Minify::__( 'Class' )    . ': ' . $class . ' ';
			if ( $function ) $message .= Minify::__( 'Function' ) . ': ' . $function;

			error_log( $message );
		}

		static public function get_template( $template, $vars = array() ) {
			return parent::get_template( self::get( 'dir' ) . '/templates/' . $template . '.php', $vars );
		}

		public function plugin_action_links( $links ) {
			array_unshift( $links, self::get_template( 'link', array(
				'url'  => get_admin_url( null, Settings::URL ),
				'link' => self::__( 'Settings' )
			) ) );

			return $links;
		}
	}
}
