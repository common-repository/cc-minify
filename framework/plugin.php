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

if ( ! class_exists( __NAMESPACE__ . '\Plugin' ) ) {
	class Plugin extends Filterer {
		static protected $dir  = null;
		static protected $file = null;

		static public function set( $name, $value ) {
			switch ( strtolower( $name ) ) {
				case 'file':
					if ( is_file( $value ) ) static::$file = $value;
					return true;
				case 'dir':
					if ( is_dir( $value ) ) static::$dir = $value;
					return true;
				default:
					return false;
			}
		}

		static public function get( $name = null ) {
			$data = get_plugin_data( static::$file );

			switch ( strtolower( $name ) ) {
				case 'file':
					return static::$file;
				case 'dir':
					return static::$dir;
				case 'url':
					return plugins_url( '', static::$file );
				case 'slug':
					return sanitize_title( $data['Name'] );
				case 'namespace':
					return __NAMESPACE__;
				case null:
					return $data;
				default:
					if ( ! empty( $data[$name] ) ) {
						return $data[$name];
					}

					return null;
			}
		}

		static public function autoload( $name ) {
			if ( 0 !== strpos( $name, static::get( 'namespace' ) ) ) return;

			$name = substr( $name, strlen( static::get( 'namespace' ) ) );
			$name = strtolower( $name );
			$name = str_replace( '\\', '/', $name );
			$name = str_replace( '_', '-', $name );
			$name = trim( $name, '/' );

			if ( is_file( $file = static::get( 'dir' ) . '/includes/' . $name . '.php' ) ) require_once( $file );
		}

		static public function load( $paths ) {
			if ( ! is_array( $paths ) ) $paths = array( (string)$paths );

			foreach( $paths as $path )
				if ( is_file( $path = locate_template( $path ) ) ) require_once $path;
				elseif ( is_dir( $path ) )
					foreach ( glob( trailingslashit( $path ) . '*.php' ) as $file ) require_once $file;
		}

		protected function __construct() {
			register_activation_hook(   static::get( 'file' ), array( $this, 'activation'   ) );
			register_deactivation_hook( static::get( 'file' ), array( $this, 'deactivation' ) );

			add_action( 'activated_plugin',   array( $this, 'switch_plugin_hook' ), 10, 2 );
			add_action( 'deactivated_plugin', array( $this, 'switch_plugin_hook' ), 10, 2 );

			// Add an action link pointing to the options page.
			add_filter( 'plugin_action_links_' . plugin_basename( static::get( 'file' ) ), array( $this, 'plugin_action_links' ) );

			// Add an action link pointing to the network options page.
			// add_filter( 'network_admin_plugin_action_links_' . static::get( 'file' ), array( $this, 'network_admin_plugin_action_links' ) );

			parent::__construct();
		}

		public function activation() {}

		public function deactivation() {}

		static public function __( $text ) {
			return __( $text, static::get( 'TextDomain' ) );
		}

		static public function apply_filters( $tag, $value ) {
			$args    = func_get_args();
			$args[0] = static::get( 'namespace' ) . '\\' . $args[0];

			return call_user_func_array( 'apply_filters', $args );
		}

		static public function get_template( $template, $vars = array() ) {
			$template = static::apply_filters( 'template', $template, $vars );
			if ( ! is_file( $template ) ) return false;

			$vars = static::apply_filters( 'vars', $vars, $template );
			if ( is_array( $vars ) ) extract( $vars, EXTR_SKIP );

			ob_start();
			include $template;

			return ob_get_clean();
		}

		static public function get_sites( $args = array() ) {
			$args = wp_parse_args( $args, array( 'public' => 1 ) );

			$sites = array();
			$sites = function_exists( 'get_sites' ) ? get_sites( $args ) : wp_get_sites( $args );

			return $sites ? array_map( function( $site ) { return (array)$site; }, $sites ) : false;
		}

		/**
		 * Add settings action link to the plugins page.
		 *
		 * @since    1.0.0
		 */
		public function plugin_action_links( $links ) {
			return $links;
		}

		public function switch_plugin_hook( $plugin, $network_wide = null ) {
			if ( ! $network_wide ) return;

			list( $hook ) = explode( '_', current_filter(), 2 );
			$hook = str_replace( 'activated', 'activate_', $hook );
			$hook .= plugin_basename( static::get( 'file' ) );

			$this->call_user_func_array( 'do_action', array( $hook, false ) );
		}

		protected function call_user_func_array( $function, $args = array() ) {
			if ( is_multisite() ) {
				$sites = self::get_sites();

				foreach ( $sites as $site ) {
					switch_to_blog( $site['blog_id'] );
					call_user_func_array( $function, $args );
				}

				restore_current_blog();
			} else $function( $args );
		}
	}
}
