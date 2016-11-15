<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
/**
 * Core Library for User Manager within Adobe Connect
 *
 * @package    local_connect
 * @copyright  2016 onwards Refined Data Solutions Inc {@link http://www.refineddata.com}
 * @author     Elvis Li <elvis.li@refineddata.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('CONNECT_INTERNAL') || die();

/**
 * Update Adobe Connect user
 *
 * @param object $user Moodle user
 * @param boolean $forceupdate force update or not
 * @param boolean $justreturnparams return parameters only or not
 * @return integer (Always 1) or array parameters
 */
function connect_update_user($user, $forceupdate = false, $justreturnparams = false) {
    global $CFG, $DB;
    if (empty($CFG->connect_service_username)) return false;
    require_once($CFG->dirroot.'/user/profile/lib.php');

    if( file_exists( "{$CFG->dirroot}/local/core/lib.php" ) ){
        require_once("{$CFG->dirroot}/local/core/lib.php");
        local_core_get_user_state_postcode( $user );
    }

    $email = validate_email($user->email) ? $user->email : '';
    
    $connect = _connect_get_instance();
    
    $params = array( 
    		'external_user_id' => $user->id,
    		'email'            => $email,
    		'username'         => $user->username,
    		'first_name'       => $user->firstname,
    		'last_name'        => $user->lastname,
            'auth'             => $user->auth
    );
    
    if ( $user->auth == 'connect' && !empty($user->ackey)) {
        $params['password'] = connect_decrypt( $user->ackey );
    }
    
        
    if( isset( $user->rawpass ) && $user->rawpass ) $params['password'] = $user->rawpass;
    
    if( isset( $user->aclogin ) && $user->aclogin ){// if aclogin is set, this ensures it will be used regardless of settings
    	$params['auth'] = 'connect';
    	$params['username'] = $user->aclogin;
    }
    
    // Now Update Other Fields
    if (isset($user->department) )  $params['department'] = $user->department;
    if (isset($user->institution) ) $params['company']    = $user->institution;
    if (isset($user->address) )     $params['address']    = $user->address;
    if (isset($user->city) )        $params['city']       = $user->city;
    if (isset($user->state) )       $params['state']      = $user->state;
    if (isset($user->postcode) )    $params['postal']     = $user->postcode;
    if (isset($user->country) )     $params['country']    = $user->country;
    if (isset($user->phone1) )      $params['phone1']     = $user->phone1;
    if (isset($user->phone2) )      $params['phone2']     = $user->phone2;

    // Now Update Preferences
    $params['lang'] = substr($user->lang, 0, 2);
    
    if($user->timezone == 99 || !isset($user->timezone)){
       	$tz = date_default_timezone_get();
    }else{
    	$tz = get_user_timezone($user->timezone);
    }
    if(is_numeric($tz) && ($tz == intval($tz))){
    	$tz = $tz . '.0';
    }
    $dbman = $DB->get_manager();
    if ( ! $dbman->table_exists( 'connect_timezones' ) || ! $tz = $DB->get_field( 'connect_timezones', 'connect_timezone', array( 'name' => $tz ) ) ) {
        $tz = '35';
    }
    $params['timezone'] = $tz;
    
    if( is_siteadmin( $user->id ) ){
    	$params['isadmin'] = 1;	
    }
    
    $params['group_name'] = CONNECT_LMS_GROUP;
    
    if( $justreturnparams ){
    	return $params;
    }else{
        $connect->connect_call( 'updateconnectuser', array($params) );
    }

    return 1;
}

/**
 * Remove Adobe Connect user
 *
 * @param integer $userid user id
 * @return boolean returns true if the user is removed from Adobe connect
 */
function connect_remove_user( $userid ) {
    $connect = _connect_get_instance();
    
    $params = array( 
    		'external_user_id' => $userid
    );
    $connect->connect_call( 'removeuser', array($params) );
}

/**
 * Update admin access of Adobe connect for the user
 *
 * @param integer $userid user id
 * @param string $action add or remove
 * @return boolean returns true if the admin access is updated successfully in Adobe connect
 */
function connect_udpate_admin_group_access( $userid, $action = 'add' ) {
	$connect = _connect_get_instance();

	$params = array(
			'external_user_id' => $userid,
			'action'           => $action
	);
	$connect->connect_call( 'updateadmingroupaccess', array($params) );
}

/**
 * Update presenter access of Adobe connect for the user
 *
 * @param integer $userid user id
 * @param string $action add or remove
 * @param string $type access type
 * @return integer (Always 1)
 */
function connect_presenter_group_access( $userid, $action = 'add', $type = 'presenter' ){
    $connect = _connect_get_instance();

    $params = array(
            'external_user_id' => $userid,
            'action'           => $action
    );

    if( $type == 'presenter' ){
        $connect->connect_call( 'update-presenter-group-access', array($params) );
    }else{
        $connect->connect_call( 'update-host-group-access', array($params) );
    }

    return 1;
}

/**
 * Update host group access of Adobe connect for the user
 *
 * @param integer $userid user id
 * @param string $action add or remove
 * @return boolean returns true if the host group access is updated successfully in Adobe connect
 */
function connect_host_group_access( $userid, $action = 'add' ){
    return connect_presenter_group_access( $userid, $action, 'host' );
}

/**
 * Get transcripts
 *
 * @param integer $userid user id
 * @return array transcripts
 */
function connect_get_transcript($userid) {
    $connect = _connect_get_instance();
    $params = array(
        'external_user_id' => $userid
    );
    $trans = $connect->connect_call( 'get-transcript',  $params  );
    return $trans;
}

/**
 * Update transcript
 *
 * @param integer $userid user id
 * @param integer $connectid connect activity id
 * @param string $type sco type
 * @param integer $score score
 * @param string $status status
 * @return boolean returns true if the transcript is updated successfully in Adobe connect
 */
function connect_update_transcript( $userid, $connectid, $type, $score = 0, $status = 'not-attempted' ){
    $connect = _connect_get_instance();
    $result = false;
    $params = array(
        'external_user_id' => $userid,
        'external_connect_id' => $connectid,
        'score' => $score,
        'status' => $status
    );

        $params['type'] = $type;

    $result = $connect->connect_call( 'update-transcript', $params );
    return $result;
}
