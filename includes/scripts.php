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

namespace Clearcode\Minify;

if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! class_exists( __NAMESPACE__ . '\Scripts' ) ) {
	class Scripts extends Dependencies {
		static protected $group = 'head';
		static protected $ext = 'js';

		public function filter_script_loader_tag_999( $tag, $handle, $src ) {
			return self::dependency_loader_tag( $tag, $handle, $src );
		}

		public function action_print_scripts_array_999( $to_do ) {
			return self::print_dependencies_array( $to_do );
		}

		public function self_scripts( $to_do ) {
			return array_diff( $to_do, array( 'admin-bar' ) );
		}

		static public function get_media( $handle ) {
			return self::get_dependency( $handle ) ? self::get_group( $handle ) : false;
		}
	}
}
