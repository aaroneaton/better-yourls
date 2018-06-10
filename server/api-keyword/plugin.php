<?php

  /*
    Plugin Name: API Keyword
    Plugin URI: https://github.com/generationtech/yourls-api-keyword
    Description: Define a custom API action 'exist-keyword' 'get-keyword-url'
    Version: 1.0
    Author: Ken McDonald
    Author URI: https://generation.tech
  */

  /**
   * yourls - API Keyword
   * Copyright (C) 2018 by Ken McDonald (ken@generation.tech)
   *
   * This program is free software: you can redistribute it and/or modify it
   * under the terms of the GNU General Public License as published by the
   * Free Software Foundation, either version 3 of the License, or (at your
   * option) any later version.
   *
   * This program is distributed in the hope that it will be useful, but
   * WITHOUT ANY WARRANTY; without even the implied warranty of
   * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General
   * Public License for more details.
   *
   * You should have received a copy of the GNU General Public License along
   * with this program. If not, see <http://www.gnu.org/licenses/>.
   **/

  yourls_add_filter( 'api_action_exist-keyword', 'api_exist_keyword' );
  yourls_add_filter( 'api_action_get-keyword-url', 'api_get_keyword_url' );

  function api_exist_keyword() {
      if ( ! isset( $_REQUEST['keyword'] ) ) {
          return array(
              'statusCode' => 400,
              'status'     => 'fail',
              'simple'     => "Need a 'keyword' parameter",
              'message'    => 'Error: missing keyword param',
          );
      }

      $keyword = $_REQUEST['keyword'];
      $$keyword_exists = yourls_keyword_is_taken( $keyword );

      if ( $$keyword_exists ) {
          return array(
              'statusCode' => 200,
              'simple'     => "$keyword exists",
              'message'    => "$keyword exists",
              'keyword'    => true,
          );
      } else {
          return array(
              'statusCode' => 200,
              'simple'     => "$keyword does not exist",
              'message'    => "$keyword does not exist",
              'keyword'    => false,
          );
      }
  }


  function api_get_keyword_url() {
      if ( ! isset( $_REQUEST['url'] ) ) {
          return array(
              'statusCode' => 400,
              'status'     => 'fail',
              'simple'     => "Need a 'url' parameter",
              'message'    => 'Error: missing url param',
          );
      }

      $url = $_REQUEST['url'];

      if ( $_REQUEST['newest'] == 'true' ) {
        $url_exists = yourls_url_exists_newest($url);
      } else {
        $url_exists = yourls_url_exists($url);
      }

      if ( $url_exists ) {
          return array(
              'statusCode' => 200,
              'simple'     => "Keyword for $url is " . $url_exists->keyword,
              'message'    => "Keyword for $url is " . $url_exists->keyword,
              'keyword'    => $url_exists->keyword,
          );
      } else {
          return array(
              'statusCode' => 200,
              'simple'     => "Keyword for $url is not found",
              'message'    => 'false',
              'keyword'    => false,
          );
      }
  }

  function yourls_url_exists_newest( $url ) {
  	global $ydb;
  	$table = YOURLS_DB_TABLE_URL;
  	$url   = yourls_escape( yourls_sanitize_url( $url) );
  	$url_exists = $ydb->get_row( "SELECT * FROM `$table` WHERE `url` = '".$url."' ORDER BY `timestamp` DESC;" );

  	return yourls_apply_filter( 'url_exists', $url_exists, $url );
  }


?>
