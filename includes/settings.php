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

if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! class_exists( __NAMESPACE__ . '\Settings' ) ) {
	class Settings extends Filterer {
		const PAGE = 'minify';
		const URL  = 'options-general.php?page=minify';

		protected $styles  = array( 'status' => true, 'rewrite' => false );
		protected $scripts = array( 'status' => true, 'rewrite' => false );

		protected function __construct() {
			if ( $settings = get_option( Minify::get( 'slug' ) ) ) {
				$this->styles  = $settings['styles'];
				$this->scripts = $settings['scripts'];
			}

			parent::__construct();
		}

		public function __get( $name ) {
			if ( isset( $this->$name ) ) return $this->$name;
			return false;
		}

		public function action_admin_notices() {
			if ( 'settings_page_minify' !== get_current_screen()->id ) return;

			foreach( array( 'styles' => Minify::get( 'namespace' ) . '\Styles', 'scripts' => Minify::get( 'namespace' ) . '\Scripts' ) as $dependency => $class ) {
				$dir = ABSPATH . $class::get_dir();
				$code = Minify::get_template( 'code', array( 'content' => $dir ) );
				if ( ! Files::file_exists( $dir ) )     echo Minify::get_template( 'notice', array( 'content' => sprintf( Minify::__( 'Dir: %s does not exists!' ), $code ) ) );
				elseif ( ! Files::is_readable( $dir ) ) echo Minify::get_template( 'notice', array( 'content' => sprintf( Minify::__( 'Dir: %s is not readable!' ), $code ) ) );
				elseif ( ! Files::is_writable( $dir ) ) echo Minify::get_template( 'notice', array( 'content' => sprintf( Minify::__( 'Dir: %s is not writable!' ), $code ) ) );
			}
			if ( ! got_url_rewrite() ) echo Minify::get_template( 'notice', array( 'content' => Minify::__( 'Server not supports URL rewriting.' ) ) );
		}

		public function action_admin_menu_999() {
			add_options_page(
				Minify::__( 'Minify Settings' ),
				Minify::get_template( 'div', array(
					'class'   => 'dashicons-before dashicons-backup',
					'content' => Minify::__( 'Minify' ) ) ),
				'manage_options',
				self::PAGE,
				array( $this, 'page' )
			);
		}

		public function action_admin_bar_menu_999( $wp_admin_bar ) {
			$wp_admin_bar->add_node( array(
				'id'    => self::PAGE,
				'title' => Minify::get_template( 'span', array( 'class' => 'ab-icon' ) ) . Minify::__( 'Minify' ),
				'href'  => get_admin_url( null, self::URL )
			) );
		}

		public function action_admin_enqueue_scripts() {
			$this->action_wp_enqueue_scripts();
		}

		public function action_wp_enqueue_scripts() {
			if ( ! is_admin_bar_showing() ) return;

			wp_register_style( Minify::get( 'slug' ), Minify::get( 'url' ) . '/assets/css/style.css', array(), Minify::get( 'Version' ) );
			wp_enqueue_style(  Minify::get( 'slug' ) );
		}

		public function page() {
			echo Minify::get_template( 'page', array(
				'option' => Minify::get( 'slug' ),
				'page'   => Minify::get( 'slug' )
			) );

			$dependencies = array();
			foreach( array( 'styles' => Minify::get( 'namespace' ) . '\Styles', 'scripts' => Minify::get( 'namespace' ) . '\Scripts' ) as $dependency => $class )
				if ( $this->{$dependency}['rewrite'] ) $dependencies[$dependency] = array( 'url' => $class::get_url(), 'dir' => $class::get_dir() );

			// wp-admin/includes/network.php get_clean_basedomain()
			$domain = is_multisite() ? preg_replace( '|https?://|', '', get_site_url() ) : '';
			$rules = Minify::get_template( 'rules', array(
				'domain'       => $domain,
				'dependencies' => $dependencies
			) );

			if ( ( $this->styles['rewrite'] || $this->scripts['rewrite'] ) &&
				file_exists( $file = ABSPATH . '.htaccess' ) &&
				str_replace( "\r\n", "\n", $rules ) != implode( "\n", extract_from_markers( $file, $marker = trim( 'Minify ' . $domain ) ) ) )
				echo Minify::get_template( 'htaccess', array(
					'message' => Minify::__( 'Add the following rules at the beginning of this file' ),
					'file'    => $file,
					'marker'  => $marker,
					'rules'   => htmlspecialchars( $rules )
				) );
		}

		public function action_admin_init() {
			register_setting(     Minify::get( 'slug' ), Minify::get( 'slug' ), array( $this, 'sanitize' ) );
			add_settings_section( Minify::get( 'slug' ), Minify::__( 'Minify' ), array( $this, 'section' ), Minify::get( 'slug' ) );

			foreach( array( 'styles' => Minify::__( 'Styles' ), 'scripts' => Minify::__( 'Scripts' ) ) as $field => $title )
				add_settings_field( $field, $title, array( $this, $field ), Minify::get( 'slug' ), Minify::get( 'slug' ) );
		}

		public function section() {
			echo Minify::get_template( 'section', array( 'content' => Minify::__( 'Settings' ) ) );
		}

		// TODO errors
		public function sanitize( $settings ) {
			$sanitized = array();

			foreach( array( 'styles' => Minify::get( 'namespace' ) . '\Styles', 'scripts' => Minify::get( 'namespace' ) . '\Scripts' ) as $dependency => $class ) {
				foreach ( array( 'status', 'rewrite' ) as $setting ) $sanitized[$dependency][$setting] = empty( $settings[$dependency][$setting] ) ? false : true;
				if ( ! empty( $settings[$dependency]['clear'] ) ) Files::unlink( ABSPATH . $class::get_dir() );
			}

			return $sanitized;
		}

		static public function input( $type, $name, $value, $label = '', $checked = '' ) {
			return Minify::get_template( 'input', array(
				'type'    => $type,
				'name'    => $name,
				'value'   => $value,
				'label'   => $label,
				'checked' => $checked
			) );
		}

		public function styles() {
			echo $this->dependencies( 'styles' );
		}

		public function scripts() {
			echo $this->dependencies( 'scripts' );
		}

		protected function dependencies( $dependency ) {
			$class = Minify::get( 'namespace' ) . '\\' . ucfirst( $dependency );
			return self::input( 'checkbox',
				Minify::get( 'slug' ) . "[$dependency][status]",
				true,
				Minify::__( 'Minify' ) . '<br />' .
				Minify::__( 'To exclude' ) . ' ' . $dependency . ' ' .
				Minify::__( 'use filter' ) . ': ' .
				Minify::get_template( 'code', array(
					'content' => $class ) ),
				checked( $this->{$dependency}['status'], true, false )
			) . '<br />' . self::input( 'checkbox',
				Minify::get( 'slug' ) . "[$dependency][rewrite]",
				true,
				Minify::__( 'Rewrite' ) . ': ' .
				Minify::get_template( 'code', array(
					'content' => get_site_url( null, $class::get_dir() ) ) ) . ' ' .
				Minify::__( 'to' ) . ': ' .
				Minify::get_template( 'code', array(
					'content' => get_site_url( null, $class::get_url() ) ) ) . '<br />' .
				Minify::__( 'To change' ) . ' ' .
				Minify::__( 'url' ) . ' ' .
				Minify::__( 'use filter' ) . ': ' .
				Minify::get_template( 'code', array(
					'content' => $class . '\\Url' ) ),
				checked( $this->{$dependency}['rewrite'], true, false )
			) . '<br />' . self::input( 'checkbox',
				Minify::get( 'slug' ) . "[$dependency][clear]",
				true,
				Minify::__( 'Clear' ) . ': ' .
				Minify::get_template( 'code', array(
					'content' => ABSPATH . $class::get_dir() ) ) . '<br />' .
				Minify::__( 'To change' ) . ' ' .
				Minify::__( 'dir' ) . ' ' .
				Minify::__( 'use filter' ) . ': ' .
				Minify::get_template( 'code', array(
					'content' => $class . '\\Dir' ) )
			);
		}
	}
}
