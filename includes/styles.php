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
if ( ! class_exists( __NAMESPACE__ . '\Styles' ) ) {
	class Styles extends Dependencies {
		static protected $group = 'head';
		static protected $ext = 'css';

		public function filter_style_loader_tag_999( $tag, $handle, $src ) {
			return self::dependency_loader_tag( $tag, $handle, $src );
		}

		public function action_print_styles_array_999( $to_do ) {
			return self::print_dependencies_array( $to_do );
		}

		public function self_styles( $to_do ) {
			return array_diff( $to_do, array( 'admin-bar', 'dashicons', 'cc-minify' ) );
		}

		static public function get_media( $handle ) {
			if ( ! $dependency = self::get_dependency( $handle ) ) return false;
			return ! empty( $dependency->args ) ? esc_attr( $dependency->args ) : 'all';
		}
	}
}
