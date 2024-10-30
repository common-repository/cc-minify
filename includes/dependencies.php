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

use Clearcode\Minify;
use MatthiasMullie\Minify\CSS;
use MatthiasMullie\Minify\JS;
use Exception;

if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! class_exists( __NAMESPACE__ . '\Dependencies' ) ) {
	class Dependencies extends Filterer {
		public function __construct() {
			Files::mkdir( ABSPATH . self::get_dir() );

			parent::__construct();
		}

		public function dependency_loader_tag( $tag, $handle, $src ) {
			if ( ! $ver = self::get_ver( $handle ) ) return $tag;
			if ( ! $groups = self::get_groups() ) return $tag;
			if ( array_keys( $groups, $handle, true ) ) return $tag;
			return Minify::get_template( strtolower( self::get_class() ), array( 'handle' => $handle, 'src' => $src ) ) .
			       Minify::get_template( 'stamp', array( 'time' => date( 'Y-m-d H:i:s', $ver ) ) ) . "\n";
		}

		public function print_dependencies_array( $to_do ) {
			if ( ! self::get_dependencies() ) return $to_do;

			$diff = array_diff( $to_do, $filtered = Minify::apply_filters( self::get_class(), $to_do ) );
			$dependencies = array();
			foreach ( $filtered as $handle ) {
				if ( self::is_done( $handle ) ) continue;
				if ( self::get_group( $handle ) !== static::$group ) continue;
				if ( ! $media = static::get_media( $handle ) ) continue;
				if ( ! $src = self::get_src( $handle )  ) continue;

				if ( false !== $content = Files::get_content( self::get_href( $src, null, $handle ) ) ) {
					$dependencies[static::$group][$media][$handle] = $content;
					self::set_done( $handle );
				}
				else $handles[] = $handle;
			}

			if ( isset ( $dependencies[static::$group] ) )
				foreach( $dependencies[static::$group] as $media => $handles ) {
					if ( ! self::get_file( $media ) ) {
						$file = self::get_file( $media, false );
						if ( false === self::minify( $handles, ABSPATH . $file ) ) return $to_do;
					}
					self::add_dependency( $media );
				}

			$to_do = isset( $dependencies[static::$group] ) ? array_merge( $diff, array_keys( $dependencies[static::$group] ) ) : $diff;
			static::$group = 'footer';
			return $to_do;
		}

		static public function get_url( $site = null ) {
			$dependency = strtolower( self::get_class() );
			return trailingslashit( Minify::apply_filters( ucfirst( $dependency ) . '\Url', $dependency, $dependency, $site ) );
		}

		static public function get_dir( $site = null ) {
			$dir = 'wp-content/cache/';
			if ( is_multisite() ) {
				$site = get_site( $site );
				$dir .= $site['site_id'] . '/' . $site['blog_id'];
			}

			$dependency = strtolower( self::get_class() );
			return trailingslashit( Minify::apply_filters( ucfirst( $dependency ) . '\Dir', $dir . $dependency, $dependency, $site ) );
		}

		static public function get_media( $handle ) {
			// TODO implement in child class
			return false;
		}

		static public function add_dependency( $handle ) {
			if ( ! $dependencies = self::get_dependencies() ) return false;
			if ( ! $file = self::get_file( $handle ) ) return false;
			$dependencies->add( $handle, get_site_url( null, self::get_rewrite( $handle ) ), null, self::get_ver( $handle ), $handle );
			$dependencies->set_group( $handle, null, 'footer' == static::$group ? 1 : 0 );
			$dependencies->enqueue( array( $handle ) );
		}

		static public function get_ver( $handle ) {
			if ( ! $file = self::get_file( $handle ) ) return false;
			return Files::filemtime( ABSPATH . $file );
		}

		static public function is_done( $handle ) {
			if ( ! $done = self::get_done() ) return false;
			return in_array( $handle, $done, true );
		}

		static public function set_done( $handle ) {
			if ( ! $dependencies = self::get_dependencies() ) return false;
			if ( false === self::get_done() ) return false;
			$dependencies->done[] = $handle;
			return true;
		}

		static public function get_done() {
			if ( ! $dependencies = self::get_dependencies() ) return false;
			return isset( $dependencies->done ) ? $dependencies->done : false;
		}

		static public function get_groups() {
			if ( ! $dependencies = self::get_dependencies() ) return false;
			return isset( $dependencies->groups ) ? $dependencies->groups : false;
		}

		static public function get_group( $handle ) {
			if ( ! $groups = self::get_groups() ) return false;
			if ( ! isset( $groups[$handle] ) ) return 'footer';
			if ( 'footer' === static::$group ) return 'footer'; // hack
			return $groups[$handle] ? 'footer' : 'head';
		}

		static public function get_dependency( $handle ) {
			if ( ! $dependencies = self::get_dependencies() ) return false;
			return isset( $dependencies->registered[$handle] ) ? $dependencies->registered[$handle] : false;
		}

		static public function get_src( $handle ) {
			if ( ! $dependencies = self::get_dependencies() ) return false;
			if ( ! $dependency = $dependencies->registered[$handle] ) return false;
			return ! empty( $dependency->src ) ? $dependency->src : false;
		}

		/**
		 * Generates an enqueued style's fully-qualified URL.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @param string $src The source of the enqueued style.
		 * @param string $ver The version of the enqueued style.
		 * @param string $handle The style's registered handle.
		 * @return string Style's fully-qualified URL.
		 */
		static public function get_href( $src, $ver, $handle ) {
			if ( ! $dependencies = self::get_dependencies() ) return false;

			if ( ! is_bool( $src ) && ! preg_match( '|^(https?:)?//|', $src ) && ! ( $dependencies->content_url && 0 === strpos( $src, $dependencies->content_url ) ) )
				$src = $dependencies->base_url . $src;

			if ( ! empty( $ver ) ) $src = add_query_arg( 'ver', $ver, $src );

			/** This filter is documented in wp-includes/class.wp-scripts.php */
			return esc_url( apply_filters( strtolower( self::get_class() ) . '_loader_src', $src, $handle ) );
		}

		static public function get_file( $handle, $file_exists = true ) {
			$file = self::get_dir() . $handle . '.' . static::$ext;
			if ( $file_exists ) return Files::file_exists( ABSPATH . $file ) ? $file : false;
			return $file;
		}

		static public function get_rewrite( $handle, $file_exists = true ) {
			$file = self::get_file( $handle );
			if ( $file_exists && ! $file ) return false;
			return self::get_setting( 'rewrite' ) ? self::get_url() . $handle . '.' . static::$ext : $file;
		}

		static public function get_settings() {
			$settings = strtolower( self::get_class() );
			return Settings::instance()->$settings;
		}

		static public function get_setting( $setting ) {
			if ( ! $settings = self::get_settings() ) return false;
			return ! empty( $settings[$setting] ) ? $settings[$setting] : false;
		}

		static public function minify( $contents, $file = null ) {
			try {
				$class = 'MatthiasMullie\\Minify\\' . strtoupper( static::$ext );
				$minify = new $class();
				if ( is_array( $contents ) ) foreach( $contents as $content ) $minify->add( $content );
				else $minify->add( $contents );
				return $minify->minify( $file );
			} catch ( Exception $exception ) {
				Minify::error_log( $exception->getMessage(), __CLASS__, __FUNCTION__ );
				return false;
			}
		}

		static public function get_class() {
			return str_replace( __NAMESPACE__ . '\\', '', get_called_class() );
		}

		// TODO add is_cacheable function with posts excluding filter & additional metabox for that purpose
		static public function get_dependencies() {
			if ( is_admin() ) return false;
			if ( in_array( $GLOBALS['pagenow'], array( 'wp-login.php', 'wp-register.php' ) ) ) return false;
			if ( ! self::get_setting( 'status' ) ) return false;

			$dir = ABSPATH . self::get_dir();
			if ( ! Files::file_exists( $dir ) ||
			     ! Files::is_readable( $dir ) ||
			     ! Files::is_writable( $dir ) ) return false;

			$function = 'wp_' . strtolower( self::get_class() );
			if ( ! $dependencies = $function() ) return false;
			return $dependencies;
		}
	}
}
