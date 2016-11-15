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
 * Connect Library for Handing SCO Adobe Connect Information
 *
 * @package    local_connect
 * @copyright  2016 onwards Refined Data Solutions Inc {@link http://www.refineddata.com}
 * @author     Elvis Li <elvis.li@refineddata.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('CONNECT_INTERNAL') || die();

/**
 * Get SCO Adobe Connect Information
 *
 * @param integer $connectid connect activity id
 * @param integer $gettimes
 * @param string $connect_meeting_type adobe connect sco type
 * @return object sco information
 */
function connect_get_sco($connectid, $gettimes = 0, $connect_meeting_type = false) {
    $connect = _connect_get_instance();

    $params = array(
    	'external_connect_id' => $connectid,
   		'gettimes'            => $gettimes
    );

    if( !empty($connect_meeting_type) ){
        $params['type'] = $connect_meeting_type;
    }
    
    $cache = $gettimes ? false : true;
    $sco = $connect->connect_call('getactivity', $params, $cache);

    return $sco;
}

/**
 * Check vantage point license
 *
 * @return boolean true if vantage point license is active.
 */
function connect_check_vp_license_active(){
    global $CFG;

    if( isset( $CFG->last_vp_license_check ) && $CFG->last_vp_license_check + ( 60*60*12 ) > time() ){
        if( isset( $CFG->vp_license_check ) ) return $CFG->vp_license_check;
    }

    $connect = _connect_get_instance();
    $result = $connect->connect_call('check-vp-license-active');

    set_config( 'vp_license_check', $result );
    set_config( 'last_vp_license_check', time() );

    return $result;
}

/**
 * Get Adobe connect principal id of the current user
 *
 * @return integer principal id
 */
function connect_get_current_user_pid(){
    $connect = _connect_get_instance();
    return $connect->connect_call('get-current-user-pid', array(), true);
}

/**
 * Get Adobe connect SCO Information
 *
 * @param string $url url
 * @param integer $gettimes
 * @param string $doasuser perform as normal user
 * @return object sco information
 */
function connect_get_sco_by_url($url, $gettimes = 0, $doasuser = 0) {
    $connect = _connect_get_instance();

    $params = array(
    	'ac_url'   => $url,
    	'gettimes' => $gettimes,
        'doasuser' => $doasuser
    );
    $cache = $gettimes ? false : true;
    $sco = $connect->connect_call('getactivitybyurl', $params, $cache);

    return $sco;
}

/**
 * Get Adobe connect quiz question information
 *
 * @param integer $sco_id sco id
 * @return object quiz question information
 */
function connect_get_quiz_question_distribution( $sco_id ){
	$connect = _connect_get_instance();
	
	$params = array('sco_id' => $sco_id);
	$result = $connect->connect_call('quiz-question-distribution', $params, true);
	
	return $result;
}

/**
 * Get Adobe connect sco information by name
 *
 * @param string $name sco name
 * @param integer $scoid sco id
 * @return object sco information
 */
function connect_get_sco_by_name ( $name, $scoid = null ) {
    
    $connect = _connect_get_instance();

    $params = array('name' => $name);
    $result = $connect->connect_call('getactivitybyname', $params, true);
    return $result;
}

/**
 * Update Adobe connect sco information
 *
 * @param integer $connectid connect activity id
 * @param string $name connect activity name
 * @param string $desc connect activity description
 * @param integer $date_begin start date
 * @param integer $date_end end date
 * @param string $connect_meeting_type adobe connect sco type
 * @return boolean true if success
 */
function connect_update_sco($connectid, $name = '', $desc = '', $date_begin = 0, $date_end = 0, $connect_meeting_type = false) {
    $connect = _connect_get_instance();

    $params = array('external_connect_id' => $connectid);
    if ($name) $params['name'] = $name;
    if ($desc) $params['description'] = $desc;
    if ($date_begin) $params['date_begin'] = $date_begin;
    if ($date_end) $params['date_end'] = $date_end;

    if( !empty($connect_meeting_type) ){
        $params['type'] = $connect_meeting_type;
    }

    return $connect->connect_call('updatemeeting', array($params), false, true);
}

/**
 * Add or update Adobe connect sco when a connect activity is added.
 *
 * @param integer $connectid connect activity id
 * @param string $url url
 * @param string $type adobe connect sco type
 * @param integer $courseid course id.
 * @return string
 */
function connect_use_sco($connectid, $url, $type, $courseid = 0){
	$connect = _connect_get_instance();
	
	$params = array(
		'external_connect_id' => $connectid,
		'url'                 => $url,
        'type'                => $type
	);
	if ($courseid) $params['external_course_id'] = $courseid;

	return $connect->connect_call('usesco', array($params));
}

/**
 * Get Adobe connect sco shortcuts.
 *
 * @return array sco shortcuts
 */
function connect_get_sco_shortcuts() {
    global $USER;

    $connect = _connect_get_instance();
    $params = array(
        'external_user_id' => $USER->id
    );
    $shortcuts = $connect->connect_call('get-sco-shortcuts', $params, true);

    return json_decode($shortcuts);
}

function connect_get_sco_contents($scoid) {
    global $USER;

    $connect = _connect_get_instance();
    $params = array(
        'external_user_id' => $USER->id,
        'sco_id' => $scoid
    );
    $contents = $connect->connect_call('get-sco-contents', $params, true);

    return $contents;
}

/**
 * Get last recording for a specific meeting
 *
 * @param string $url url
 * @param integer $start start time
 * @param integer $end end time
 * @return string url of the recording
 */
function connect_get_recording($url, $start, $end) {
    $connect = _connect_get_instance();
	
	$params = array(
		'url'   => $url,
		'start' => $start,
		'end'   => $end
	);

	return $connect->connect_call('getlastrecordingurl', $params, true);
}

/**
 * Get recordings for a list of meeting
 *
 * @param array $url urls
 * @param integer $firstdate first date
 * @return array recordings
 */
function connect_get_recordings($urls, $firstdate) {
    $connect = _connect_get_instance();
    
    if( is_array( $urls ) ){
    	$urls = join(',',$urls);	
    }
	
	$params = array(
		'urls'   => $urls,
		'firstdate' => $firstdate
	);

	$recs = $connect->connect_call('get-recordings', $params, true);
	
    return $recs;
}

/**
 * Get Templates
 *
 * @return array templates
 */
function connect_get_templates() {
    $connect = _connect_get_instance();
    
    $templates = $connect->connect_call('gettemplates', array(), true);
    $templates = (array) $templates;
    
    if( $templates && is_array( $templates ) ) return $templates;
	
    return array();
}

/**
 * Get Telephony Options
 *
 * @param integer $login user id
 * @return array telephony options
 */
function connect_telephony_profiles($login) {
    $connect = _connect_get_instance();
    $params = array(        
        'external_user_id' => $login
    );    
    $profiles =  $connect->connect_call('get-telephony-profiles', $params, true); 
    if( is_object( $profiles ) ) $profiles = (array) $profiles;

    if ( !$profiles || !is_array($profiles) ) {
        return false;
    }
    
    return $profiles;
}

/**
 * Get SCO Scores of the user
 *
 * @param integer $connect_id connect activity id
 * @param integer $userid user id
 * @param string $connect_meeting_type connect sco type
 * @return array sco scores of the user
 */
function connect_sco_scores($connect_id, $userid, $connect_meeting_type = false ) {
    $connect = _connect_get_instance();
    $params = array(
        'external_connect_id' => $connect_id,
        'external_user_id' => $userid
    );

    if( !empty($connect_meeting_type) ){
        $params['type'] = $connect_meeting_type;
    }

    $scores =  $connect->connect_call('get-sco-scores', $params);  
    return $scores;
}

/**
 * Create Meeting
 *
 * @param integer $connect_id connect activity id
 * @param string $type connect sco type
 * @param object $meeting connect meeting
 * @return boolean (Always true)
 */
function connect_create_meeting($connectid, $type, $meeting) {
    global $USER;


    $connect = _connect_get_instance();

    $params = array();
    $params['type'] =  $type;
    $params['external_connect_id'] = $connectid;

    $host = isset($meeting->host) ? $meeting->host : $USER->id;
    $params['name'] = isset($meeting->name) ? $meeting->name : 'Meeting room for HOST:' . $host . ' created on ' . DATE('c', time());
    $params['date_begin'] = isset($meeting->start) ? $meeting->start : time() - 3600;
    $params['date_end'] = isset($meeting->end) ? $meeting->end : time();

    if (isset($meeting->description)) $params['description'] = $meeting->description;
    $params['lang'] = substr(current_language(), 0, 2);
    if (isset($meeting->template)) $params['template'] = $meeting->template;
    if (isset($meeting->url)) $params['url'] = $meeting->url;
    if (isset($meeting->folder)) $params['folder'] = $meeting->folder;

    if (isset($meeting->telephony) && $meeting->telephony && $meeting->telephony != 'none') $params['telephony'] = $meeting->telephony;
    $params['access'] = isset($meeting->access) && $meeting->access ? $meeting->access : 'protected';

    if (isset($meeting->view) && $meeting->view) $params['view'] = $meeting->view;
    if (isset($meeting->presenter) && $meeting->presenter) $params['presenter'] = $meeting->presenter;
    if (isset($meeting->host) && $meeting->host) $params['host'] = $meeting->host;

    $connect->connect_call('createmeeting', array($params), false, true);

    return true;
}

/**
 * Get completed meeting count
 *
 * @param integer $userid user id
 * @return integer count of completed meetings
 */
function connect_completed($userid) {
	$connect = _connect_get_instance();
	
	$params = array(
			'external_user_id' => $userid
	);
	$count = $connect->connect_call('get-completed-training-count', $params);
	
	$count = $count ? $count : 0;
	
	return $count;
}

/**
 * Get sco url of adobe connect
 *
 * @param integer $url url
 * @param string $type sco type
 * @param boolean $edit edit or not
 * @param boolean $archive archive or not
 * @param boolean $guest guest or not
 * @param integer $editismoodleid connect activity id
 * @return string sco url of adobe connect
 */
function connect_get_launch_url($url, $type = 'meeting', $edit = false, $archive = false, $guest = false, $editismoodleid = '') {
    $connect = _connect_get_instance();

    if ($type <> 'meeting') $type = 'content';
    
    $params = array(
        'url'     => $url,
        'type'    => $type,
        'edit'    => $edit,
        'archive' => $archive,
    	'guest'   => $guest,
        'editisexternalid' => $editismoodleid
    );
    $url = $connect->connect_call('get-launch-url', $params);
    if ( json_decode($url) == '' ) {
        $result = new stdClass();
        if ($url == false){
            $url = get_string('unauthorizedadobeconnect', 'local_refinedservices');
        }
        $result->error = $url;
        return $result;
    }
    return json_decode($url);
}

/**
 * Get Transactions
 *
 * @param integer $start start time
 * @param integer $end end time
 * @return array transactions
 */
function connect_get_transactions($start, $end) {
    $connect = _connect_get_instance();
    $params = array(
        'start' => $start,
        'end' => $end
    );    
    $transactions =  $connect->connect_call('get-transactions', $params);  
    return $transactions;
}

/**
 * Check if it is vantage point recording
 *
 * @param string $url url
 * @return boolean returns true if it is a vantage point recording
 */
function connect_check_if_vp_recording($url) {
    $connect = _connect_get_instance();

    $params = array(
        'url' => $url
    );  
    
    $result = $connect->connect_call('check-if-vp-recording', $params);

    return $result;
}

/**
 * Update refined service connect_meetings
 *
 * @param array $external_connect_ids connect activity ids
 * @param string $connect_meeting_type adobe connect sco type
 * @return boolean returns true (success)
 */
function connect_update_connect_meetings($external_connect_ids, $connect_meeting_type){
    $connect = _connect_get_instance();
    $params = array(
        'external_connect_ids' => $external_connect_ids,
        'type' => $connect_meeting_type
    );
    return $connect->connect_call('update-connect-meetings', $params);  
}
