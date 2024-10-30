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
if ( ! class_exists( __NAMESPACE__ . '\Files' ) ) {
	class Files extends Filterer {
		// TODO use wp_filesystem API
		public function __construct() {

			parent::__construct();
		}

		static public function filemtime( $file ) {
			return @filemtime( $file );
		}

		static public function file_exists( $file ) {
			return @file_exists( $file );
		}

		static public function is_dir( $file ) {
			return @is_dir( $file );
		}

		static public function is_readable( $file ) {
			return @is_readable( $file );
		}

		static public function is_writable( $file ) {
			return @is_writable( $file );
		}

		static public function mkdir( $file ) {
			return @mkdir( $file, 0755, true );
		}

		static public function unlink( $file ) {
			if ( self::is_dir( $file ) ) array_map( 'unlink', glob( $file . '*.*'  ) );
			elseif( self::file_exists( $file ) ) @unlink( $file );
		}

		static public function put_content( $file, $content ) {
			return (bool)file_put_contents( $file, $content );
		}

		static public function get_content( $src ) {
			$response = wp_remote_get( $src, array( 'sslverify' => false ) );

			if ( is_wp_error( $response ) ) {
				$error = '';
				if ( $errors = $response->get_error_messages() ) $error .= join( '; ', $errors );
				Minify::error_log( $error, __CLASS__, __FUNCTION__ );
				return false;
			} elseif ( 200 != $code = wp_remote_retrieve_response_code( $response ) ) {
				Minify::error_log( Minify::__( 'Wrong response code' ) . ': ' . $code, __CLASS__, __FUNCTION__ );
				return false;
			} elseif ( $content = wp_remote_retrieve_body( $response ) ) {
				return $content;
			} else {
				return false;
			}
		}
	}
}
