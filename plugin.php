<?php
/*
Plugin Name: I Wanna Apply
Plugin URI: http://github.com/tommcfarlin/i-wanna-apply/
Description: A simple plugin for allowing site members to apply for certain events. And display applicants' names for statistics.
Version: 1.3
Author: Zhiyao Wang
Author URI: http://lnkd.in/WPgiQf
Author Email: wzy@umich.edu
License:

  Copyright 2013 Zhiyao Wang (wzy@umich.edu)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as 
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
  
*/

class IWannaApply {
	 
	/*--------------------------------------------*
	 * Constructor
	 *--------------------------------------------*/
	
	/**
	 * Initializes the plugin by setting localization, filters, and administration functions.
	 */
	function __construct() {
	
		load_plugin_textdomain( 'i-wanna-apply', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );
	
		// Register site styles and scripts
		add_action( 'wp_enqueue_scripts', array( &$this, 'register_plugin_styles' ) );
		add_action( 'wp_enqueue_scripts', array( &$this, 'register_plugin_scripts' ) );
		
		// Include the Ajax library on the front end
		add_action( 'wp_head', array( &$this, 'add_ajax_library' ) );
		
		// Setup the event handler for marking this post as read for the current user
		add_action( 'wp_ajax_mark_as_read', array( &$this, 'mark_as_read' ) );
		
		// Setup the filter for rendering the checkbox
		add_filter( 'the_content', array( &$this, 'add_checkbox' ) );

	} // end constructor

	/*--------------------------------------------*
	 * Action Functions
	 *--------------------------------------------*/

	/**
	 * Adds the WordPress Ajax Library to the frontend.
	 */
	public function add_ajax_library() {
		
		$html = '<script type="text/javascript">';
			$html .= 'var ajaxurl = "' . admin_url( 'admin-ajax.php' ) . '"';
		$html .= '</script>';
		
		echo $html;	
		
	} // end add_ajax_library

	/**
	 * Registers and enqueues plugin-specific styles.
	 */
	public function register_plugin_styles() {
	
		wp_register_style( 'i-wanna-apply', plugins_url( 'i-wanna-apply/css/plugin.css' ) );
		wp_enqueue_style( 'i-wanna-apply' );
	
	} // end register_plugin_styles
	
	/**
	 * Registers and enqueues plugin-specific scripts.
	 */
	public function register_plugin_scripts() {
	
		wp_register_script( 'i-wanna-apply', plugins_url( 'i-wanna-apply/js/plugin.js' ), array( 'jquery' ) );
		wp_enqueue_script( 'i-wanna-apply' );
	
	} // end register_plugin_scripts
	
	/**
	 * Uses the current user ID and the incoming post ID to mark this post as read
	 * for the current user.
	 *
	 * We store this post's ID in the associated user's meta so that we can hide it
	 * from displaying in the list later.
	 */
	public function mark_as_read() {
		
		// First, we need to make sure the post ID parameter has been set and that's it's a numeric value
		if( isset( $_POST['post_id'] ) && is_numeric( $_POST['post_id'] ) ) {

			// If we fail to update the user meta, respond with -1; otherwise, respond with 1.
	      // $users = get_post_meta( $_POST['post_id'], 'ive-applied', true );
	      // $users .= '  ';
	      $users = wp_get_current_user()->user_login;
			echo false == add_post_meta( $_POST['post_id'], 'ive-applied',  $users ) ? "-1" : "1";
			
		} // end if
		
		die();
		
	} // end mark_as_read
	
	/*--------------------------------------------*
	 * Filter Functions
	 *--------------------------------------------*/
	 
	 /**
	  * Adds a checkbox to the end of a post in single view that allows users who are logged in
	  * to mark their post as read.
	  * 
	  * @param	$content	The post content
	  * @return				The post content with or without the added checkbox
	  */
	 public function add_checkbox( $content ) {
		 
		 // We only want to modify the content if the user is logged in
		 if( is_single() ) {

			 // If the user is logged in...
			 if( is_user_logged_in() ) {
				 $key_values = get_post_custom_values('ive-applied' ,get_the_ID());
          // Build the element that will be used to mark this post as read
					 $html = '<div id="i-wanna-apply">';
					 	$html .= '<strong>';
					 		$html .= __( "已报名：", 'i-wanna-apply' );
					 		if (!empty($key_values)){
					 		foreach ($key_values as $value) {
					 			$names .= '  |  ' . $value;
					 			if ( wp_get_current_user()->user_login == $value)
					 			{
					 				$have_applied = 1;
					 			}
					 		}
					 		$html .= __( $names, 'i-wanna-apply' );
					 	$html .= '</strong>';
					 		}
					 		if($have_applied != 1){
		            			$html .= '<br>';
							 	$html .= '<button class="i-wanna-apply ow-btn ow-btn-success">';
							 		$html .= __( "我要报名！", 'i-wanna-apply' );
							 	$html .= '</button>';
					 		}
		           $html .= '</div><!-- /#i-wanna-apply -->';
	 
				 
				 
				 // Append it to the content
				 $content .= $html;
				 
			 } // end if
			 
		 } // end if
		 
		 return $content;
		 
	 } // end add_checkbox
  
} // end class

new IWannaApply();
?>