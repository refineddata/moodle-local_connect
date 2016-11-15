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
 * Core Library for Updating Permissions within Adobe Connect
 *
 * This library was implemented as stand-alone product but is used here as an extension to Moodle
 * External Calls:
 *    connect_enrol
 *    connect_exec
 *    connect_encrypt
 *    connect_decrypt
 *    connect_custom_field
 *
 * @package    local_connect
 * @copyright  2016 onwards Refined Data Solutions Inc {@link http://www.refineddata.com}
 * @author     Elvis Li <elvis.li@refineddata.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('CONNECT_INTERNAL') || die();

/**
 * Update group access
 *
 * @param integer $userid user id.
 * @param integer $courseid course id.
 * @param boolean $add add access or remove access.
 * @return boolean (Always true)
 */
function connect_group_access( $userid, $courseid, $add=true ) {
    global $CFG, $DB;
    
    $connect = _connect_get_instance();
    
    $params = array( 
    		'external_course_id' => $courseid,
    		'external_user_id'   => $userid
    );
    $params['action'] = $add ? 'add' : 'remove';		
    
    $connect->connect_call( 'updategroupaccess', array($params) );
    
    return true;
}

/**
 * Remove group
 *
 * @param integer $courseid course id.
 * @return boolean (Always true)
 */
function connect_remove_group( $courseid ) {
    $connect = _connect_get_instance();
    
    $params = array(
    		'external_course_id' => $courseid
    );
    
    $connect->connect_call( 'removegroup', array($params) );

    return true;
}

/**
 * Gives a user or a group access to a adobe connect meeting or content
 *
 * @param integer $connect_id connect id.
 * @param integer $add_id course id or user id.
 * @param string $type user or group.
 * @param string $access access level.
 * @param boolean $noredirect true or false.
 * @param string $connect_meeting_type connect content type.
 * @return void
 */
function connect_add_access( $connect_id, $add_id, $type, $access='view', $noredirect = false, $connect_meeting_type = false ) {
	$connect = _connect_get_instance();
	
	$params = array(
			'external_connect_id' => $connect_id,
			'external_add_id'     => $add_id,
			'accesstype'          => $type,
			'action'              => $access
	);

    if( !empty($connect_meeting_type) ){
        $params['type'] = $connect_meeting_type;
    }
	
	$connect->connect_call( 'updatemeetingaccess', array($params), false, false, $noredirect );
}

/**
 * Update group
 *
 * @param string $name course short name.
 * @param integer $courseid course id.
 * @param boolean $justreturnparams true or false.
 * @return array parameters
 */
function connect_update_group( $name, $courseid, $justreturnparams = false ) {
    global $CFG;
    
    $connect = _connect_get_instance();
    
    $params = array( 
    		'external_course_id' => $courseid,
    		'name'   => substr( $name, 0, 20 ) . ' (' .  $courseid . ')'
    );		
    
    if( $justreturnparams ){
    	return $params;
    }else{
	    $connect->connect_call( 'updategroup', array($params) );
    }
    
    return true;
}

/**
 * Get course role
 *
 * @param string $name course short name.
 * @param integer $id course id.
 * @return string course shortname ( course id )
 */
function _course_role( $name, $id ) {
    return urlencode( substr( $name, 0, 20 ) . ' (' .  $id . ')' );
}
