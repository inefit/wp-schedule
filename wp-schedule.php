<?php
/*
Plugin Name: WP Schedule
Plugin URI: http://github.com/mfahri
Description: Schedule appointments 
Version: 1.0
Author: Fahri Arrasyid
Author URI: http://github.com/mfahri
*/


include( plugin_dir_path( __FILE__ ) . 'includes/functions.php');
include( plugin_dir_path( __FILE__ ) . 'includes/users.php');
include( plugin_dir_path( __FILE__ ) . 'includes/manage.php');
include( plugin_dir_path( __FILE__ ) . 'includes/calendar.php');
include( plugin_dir_path( __FILE__ ) . 'includes/shortcode.php');
//Initialize when plugin actived  
register_activation_hook(__FILE__,'dite_schedule_install');
function dite_schedule_install(){
	require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
	global $wpdb;
	$table = $wpdb->prefix . 'dite_appointment';
	if($wpdb->get_var( "SHOW TABLES LIKE '$table'" ) != $table){
		$sql = 'CREATE TABLE IF NOT EXISTS '.$table.' (
			  `post_id` bigint(20) NOT NULL AUTO_INCREMENT
			  `post_title` varchar(255) NOT NULL,
			  `post_content` text NOT NULL,
			  `start_time` datetime NOT NULL,
			  `end_time` datetime NOT NULL,
			  `whole_day` int(1) NOT NULL,
			  `status` varchar(12) NOT NULL,
			  `color` int(2) NOT NULL,
			  PRIMARY KEY (`post_id`)
		)';
		dbDelta($sql);
	}
	
	$table = $wpdb->prefix . 'dite_appointment_meta';
	if($wpdb->get_var( "SHOW TABLES LIKE '$table'" ) != $table){
		$sql = 'CREATE TABLE IF NOT EXISTS '.$table.' (
			  `meta_id` bigint(20) NOT NULL AUTO_INCREMENT,
			  `post_id` bigint(20) NOT NULL,
			  `meta_key` varchar(255) NOT NULL,
			  `meta_value` text NOT NULL,
			  PRIMARY KEY (`meta_id`) )';
		dbDelta($sql);
	}
}

/**
  * @function 	: client_projects_head
  *	@desc 		: This function will include in <head></head> tag
  */
function dite_schedule_head() {
	wp_deregister_script('jquery-ui-datepicker');
	wp_enqueue_script('jquery');
	wp_enqueue_script('jquery-ui-core');
	wp_enqueue_script('jquery-ui-datepicker' , dite_schedule_pluginurl('js') . '/jquery.datepicker.js');
	wp_enqueue_style('dailog.css', dite_schedule_pluginurl('css') . '/dailog.css');
	wp_enqueue_style('calendar.css', dite_schedule_pluginurl('css') . '/calendar.css');
	wp_enqueue_style('dp.css', dite_schedule_pluginurl('css') . '/dp.css');
	wp_enqueue_style('alert.css', dite_schedule_pluginurl('css') . '/alert.css');
	wp_enqueue_style('main.css', dite_schedule_pluginurl('css') . '/main.css');
	
	wp_enqueue_script('Common.js',  dite_schedule_pluginurl('js') . '/Common.js');
	wp_enqueue_script('datepicker_lang_US.js',  dite_schedule_pluginurl('js') . '/datepicker_lang_US.js');
	wp_enqueue_script('jquery.alert.js',  dite_schedule_pluginurl('js') . '/jquery.alert.js');
	wp_enqueue_script('jquery.ifrmdailog.js',  dite_schedule_pluginurl('js') . '/jquery.ifrmdailog.js');
	wp_enqueue_script('wdCalendar_lang_US.js',  dite_schedule_pluginurl('js') . '/wdCalendar_lang_US.js');
	wp_enqueue_script('jquery.calendar.js',  dite_schedule_pluginurl('js') . '/jquery.calendar.js');
}

add_action('admin_head', 'dite_schedule_head');

/**
  * @function 	: dite_schedule_menu
  *	@desc 		: This function will create menu in admin wordpress
  */
add_action("admin_menu", "dite_schedule_menu");
function dite_schedule_menu() {
     add_menu_page( 'Dite Schedule', 'Dite Schedule', 'add_users', 'dite-schedule', 'dite_schedule_init', '',0 );
	 add_submenu_page( "dite-schedule", "Manage Calendar", "Manage Calendar", 0, "dite-schedule", "dite_schedule_init" );
	 add_submenu_page( "dite-schedule", "Manage Schedule", "Manage Schedule", 0, "dite-schedule-manage", "dite_schedule_appointment" );
	 add_submenu_page( "dite-schedule", "Manage User", "Manage User", 0, "dite-schedule-manage-user", "dite_schedule_manage_user" );
}

function dite_schedule_appointment() {
	global $dite_appointment;
	$doaction = ! empty( $_REQUEST['action'] ) ? $_REQUEST['action'] : '';
	
	if($_POST) $dite_appointment->post($doaction);

	// Display the single screen
	if ( 'edit' == $doaction && ! empty( $_GET['id'] ) ){
		add_meta_box( 'submitdiv',           _x( 'Status', 'activity admin edit screen' ), 'dite_schedule_edit_user_meta', get_current_screen()->id, 'side', 'core' );
		$dite_appointment->edit();
	}
	else if ( 'add' == $doaction ){
		add_meta_box( 'submitdiv',           _x( 'Status', 'activity admin edit screen' ), 'dite_schedule_edit_user_meta', get_current_screen()->id, 'side', 'core' );
		$dite_appointment->edit();
	}
	else{
		$dite_appointment->manage();
	}
}

add_action('wp_ajax_appointment_form', 'dite_schedule_appointment_form');
function dite_schedule_appointment_form() {
	global $wpdb;
	include( plugin_dir_path( __FILE__ ) . 'view/appointment_form.php');
	die(); // this is required to return a proper result
}

add_action('wp_ajax_dite_action_shortcode', 'dite_schedule_dite_action_shortcode');
function dite_schedule_dite_action_shortcode() {
	global $wpdb;
	include( plugin_dir_path( __FILE__ ) . 'view/shortcode_form.php');
	die(); // this is required to return a proper result
}

add_action('wp_ajax_dite_show_calendar', 'dite_schedule_show_calendar');
add_action('wp_ajax_dite_schedule_freetime', 'dite_schedule_dite_schedule_freetime');
function dite_schedule_show_calendar() {
	global $wpdb;
	header('Content-type:text/javascript;charset=UTF-8');
	if($_POST){
		if($_REQUEST['method'] == 'add'){
			$start_date = dite_schedule_php2mysqltime(dite_schedule_js2phptime($_POST['CalendarStartTime']));
			$end_date = dite_schedule_php2mysqltime(dite_schedule_js2phptime($_POST['CalendarEndTime']));
			$sql = "select * from ".$wpdb->prefix."dite_appointment where 
					(`start_time` between '".$start_date."' and '".$end_date."') OR 
					(`end_time` between '".$start_date."' and '".$end_date."') OR
					(`start_time` >= '".$start_date."' AND `end_time` <='".$start_date."' ) OR
					(`start_time` >= '".$end_date."' AND `end_time` <='".$end_date."' )";
			$results = $wpdb->get_results($sql);
			if(count($results) < 1){
				$data = array(
						'start_time'=> $start_date,
						'end_time' => $end_date,
						'post_title' => 'Free Time',
						'status' => 'free',
						'color' => 3
					);
				$new_id = $wpdb->insert( $wpdb->prefix.'dite_appointment', $data );
				echo '{"IsSuccess":true,"Msg":"add success","Data":'.$new_id.'}';
			}
			else{
				echo '{"IsSuccess":false,"Msg":"Conflict date","Data":0}';
			}
		}
		else if($_REQUEST['method'] == 'list'){
			$ret = dite_schedule_list_calendar($_POST["showdate"], $_POST["viewtype"]);
			echo json_encode($ret); 
		}
		else if( $_REQUEST['method'] == 'remove' ){
			try{
				$sql = "DELETE FROM ".$wpdb->prefix."dite_appointment WHERE post_id='".$_POST['calendarId']."'";
				$wpdb->query( $wpdb->prepare( $sql ));
				$ret['IsSuccess'] = true;
				$ret['Msg'] = 'Schedule Removed';
			}
			catch(Exception $e){
				$ret['IsSuccess'] = false;
				$ret['Msg'] = 'Schedule not found';
			}
			echo json_encode($ret); 
			die();
		}
	}
	die(); // this is required to return a proper result
}

function dite_schedule_manage_user(){
	global $dite_user;
	// Decide whether to load the index or edit screen
	$doaction = ! empty( $_REQUEST['action'] ) ? $_REQUEST['action'] : '';
	
	if($_POST) $dite_user->post_user($doaction);

	// Display the single Dev edit screen
	if ( 'edit' == $doaction && ! empty( $_GET['id'] ) ){
		add_meta_box( 'submitdiv', _x( 'Status', 'activity admin edit screen' ), 'dite_schedule_edit_user_meta', get_current_screen()->id, 'side', 'core' );
		$dite_user->edit_user();
	}
	else if ( 'add' == $doaction ){
		add_meta_box( 'submitdiv', _x( 'Status', 'activity admin edit screen' ), 'dite_schedule_edit_user_meta', get_current_screen()->id, 'side', 'core' );
		$dite_user->edit_user();
	}
	else{
		$dite_user->manage_user();
	}
}

function dite_schedule_edit_user_meta(){
	global $dite_user;
	$dite_user->meta_edit();
}

function dite_template_include($template) {
	$tokens = explode('/', $_SERVER['REQUEST_URI']);
	$count = count($tokens);
	if($tokens[$count - 1] == ''){
		$url = $tokens[$count - 2];
	}
	else{
		$url = $tokens[$count - 1];	
	}
	if ($url == 'dite-schedule') {
		return dirname(__FILE__) . '/single.php'; 
	} else { return $template; } 
}
add_filter('template_include', 'dite_template_include');
?>