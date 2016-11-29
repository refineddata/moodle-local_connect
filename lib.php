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
 * Library of functions and constants for Local Connect
 *
 * @package    local_connect
 * @copyright  2016 onwards Refined Data Solutions Inc {@link http://www.refineddata.com}
 * @author     Elvis Li <elvis.li@refineddata.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $CFG;

// Used to ensure this file is set up prior to other libraries.
if (!defined('CONNECT_INTERNAL')) {
	define('CONNECT_INTERNAL', true);
}

// Number of seconds before resetting Connect CURL
define( 'CONNECT_INIT_BUFFER',           60 );

// Encryption key
define( 'CONNECT_ENCRYPTION_KEY',        'RefinedDataSolutions' );

// Encryption Algorithm
define( 'CONNECT_ENCRYPTION_ALGORITHM',  'cast-256' );

// Encryption Mode
define( 'CONNECT_ENCRYPTION_MODE',       'ecb' );

// Default Language for Invalid
define( 'CONNECT_DEFAULT_LANGUAGE',      'en' );

// Valid Language Codes
define( 'CONNECT_LANGUAGES',             'en,fr,de,ja,do,es' );

// Default Timezone for Invalid
define( 'CONNECT_DEFAULT_TIMEZONE',      '35' );

// Moodle-id Field Name
define( 'CONNECT_MOODLE_ID_FIELD_NAME',  'Moodle-ID' );

// Dummy Password when Unknown
define( 'CONNECT_DUMMY_PASSWORD',        'Null1234' );

// MoodleUsers Group
define( 'CONNECT_LMS_GROUP',             'MoodleUsers' );

// MoodleUsers Group Description
define( 'CONNECT_LMS_GROUP_DESC',        'Group for All LMS Users' );

// Group Description Prefix
define( 'CONNECT_COURSE_GROUP_PRE',      'LMS Course for ' );

define( 'CONNECT_LEGACYFILES_ACTIVE',    false);


require_once( $CFG->dirroot . '/local/refinedservices/ServiceClient.php' );
require_once( $CFG->dirroot . '/local/connect/lib/access.php' );
require_once( $CFG->dirroot . '/local/connect/lib/ftlib.php' );
require_once( $CFG->dirroot . '/local/connect/lib/my.php' );
require_once( $CFG->dirroot . '/local/connect/lib/sco.php' );
require_once( $CFG->dirroot . '/local/connect/lib/user.php' );


function _connect_get_instance() {
    return new RefinedServices\ServiceClient( 'connect' );
}

// Delete all records from the connect cache
//
function reset_connect_cache() {
	global $CFG, $DB;

	$DB->delete_records( 'connect_cache', array() );
}

//Toggles between system timezone and the timezone passed in
//Typical use connect_tz_set( get_user_timezone() ) then date( ... ) then cpro_tz_set( 'reset' )
function connect_tz_set( $tz='reset' ){
	static $systz;
	static $nonstd = array( "(+ 0 WET) Western European Time" => "Europe/London",
			"(+ 1 CET) Central European Time" => "Europe/Paris",
			"(+ 2 EET) Eastern European Time" => "Europe/Athens",
			"(- 3.5 NST) Newfoundland Standard Time" => "America/St_Johns",
			"(- 4 AST) Atlantic Standard Time" => "Atlantic/Bermuda",
			"(- 5 EST) Eastern Standard Time" => "America/New_York",
			"(- 6 CST) Central Standard Time" => "America/Chicago",
			"(- 7 MST) Mountain Standard Time" => "America/Denver",
			"(- 8 PST) Pacific Standard Time" => "America/Los_Angeles",
			"(- 9 AKST) Alaska Standard Time" => "America/Anchorage",
			"(-11 HST) Hawaii Standard Time" => "Pacific/Honolulu" );

	if ( isset( $nonstd[ $tz ] ) ) $tz = $nonstd[ $tz ];

	if ( function_exists( 'date_default_timezone_get' ) ) {  // Only available for PHP V5.2 or greater
		if ( !isset( $systz ) ) {
			$systz = date_default_timezone_get();
		}
		if ( $tz == 'reset' || abs( $tz ) > 12 ) {
			return date_default_timezone_set( $systz );
		}
		if ( is_float( $tz ) ) {
			if ( ! $tz = timezone_name_from_abbr( "", $tz*3600, 0 ) ) {
				$tz = usertimezone();
			}
		}
		return date_default_timezone_set( $tz );
	}

	if ( !isset( $systz ) ) {
		$systz = getenv( "TZ" );
	}
	if( $tz == 'reset' || abs( $tz ) > 12 ) {
		return putenv( "TZ=$systz" );
	}
	return putenv( "TZ=" . usertimezone() );
}

//encrypt string
//
function connect_encrypt( $data_input ){
	if (!function_exists('mcrypt_module_open')) {
		throw new Exception('Missing PHP Mcrypt library <a href="http://www.php.net/manual/en/mcrypt.installation.php">http://www.php.net/manual/en/mcrypt.installation.php</a>');
	}
	$key = CONNECT_ENCRYPTION_KEY;
	$td  = mcrypt_module_open( CONNECT_ENCRYPTION_ALGORITHM, '', CONNECT_ENCRYPTION_MODE, '');
	$iv  = mcrypt_create_iv( mcrypt_enc_get_iv_size( $td ), MCRYPT_RAND );
	mcrypt_generic_init( $td, $key, $iv );
	$encrypted_data = mcrypt_generic( $td, $data_input );
	mcrypt_generic_deinit( $td );
	mcrypt_module_close( $td );
	$encoded_64 = base64_encode( $encrypted_data );
	return $encoded_64;
}

//decrypt string
//
function connect_decrypt( $encoded_64 ){
	$decoded_64 = base64_decode($encoded_64);
	$key = CONNECT_ENCRYPTION_KEY;
	$td  = mcrypt_module_open( CONNECT_ENCRYPTION_ALGORITHM, '', CONNECT_ENCRYPTION_MODE, '');
	$iv  = mcrypt_create_iv( mcrypt_enc_get_iv_size( $td ), MCRYPT_RAND );
	mcrypt_generic_init( $td, $key, $iv );
	$decrypted_data = rtrim( mdecrypt_generic( $td, $decoded_64 ) );
	mcrypt_generic_deinit( $td );
	mcrypt_module_close( $td );
	return $decrypted_data;
}

if (!function_exists('strptime')) {
	function strptime($str, $fmt)
	{
		$rtn = array(
				'tm_sec'    => 0,
				'tm_min'    => 0,
				'tm_hour'   => 0,
				'tm_mday'   => 0,
				'tm_mon'    => 0,
				'tm_year'   => 0,
				'tm_wday'   => 0,
				'tm_yday'   => 0,
				'unparsed'  => ''
		);

		$rtn['tm_year'] = intval(substr($str,  0, 4)) - 1900;
		$rtn['tm_mon']  = intval(substr($str,  5, 2)) - 1;
		$rtn['tm_mday'] = intval(substr($str,  8, 2));
		$rtn['tm_hour'] = intval(substr($str, 11, 2));
		$rtn['tm_min']  = intval(substr($str, 14, 2));
		$rtn['tm_sec']  = intval(substr($str, 17, 2));

		return $rtn;
	}
}

function connect_fatal( $str ) {
	$str = isset( $str ) ? $str : 'A fatal error has occurred.';

	die( "<div class='box errorbox errorcontent'><b>$str</b></div>" );
}

/**
 * Try on demand migration of file from old course files
 * @param string $filepath old file path
 * @param int $cmid migrated course module if
 * @param int $courseid
 * @param string $component
 * @param string $filearea new file area
 * @param int $itemid migrated file item id
 * @return mixed, false if not found, stored_file instance if migrated to new area
 */
function connectlib_try_file_migration($filepath, $cmid, $courseid, $component, $filearea, $itemid) {
	$fs = get_file_storage();

	if (stripos($filepath, '/backupdata/') === 0 or stripos($filepath, '/moddata/') === 0) {
		// do not steal protected files!
		return false;
	}

	if (!$context = get_context_instance(CONTEXT_MODULE, $cmid)) {
		return false;
	}
	if (!$coursecontext = context_course::instance($courseid)) {
		return false;
	}

	$fullpath = rtrim("/$coursecontext->id/course/legacy/0".$filepath, '/');
	do {
		if (!$file = $fs->get_file_by_hash(sha1($fullpath))) {
			if ($file = $fs->get_file_by_hash(sha1("$fullpath/.")) and $file->is_directory()) {
				if ($file = $fs->get_file_by_hash(sha1("$fullpath/index.htm"))) {
					break;
				}
				if ($file = $fs->get_file_by_hash(sha1("$fullpath/index.html"))) {
					break;
				}
				if ($file = $fs->get_file_by_hash(sha1("$fullpath/Default.htm"))) {
					break;
				}
			}
			return false;
		}
	} while (false);

	// copy and keep the same path, name, etc.
	$file_record = array('contextid'=>$context->id, 'component'=>$component, 'filearea'=>$filearea, 'itemid'=>$itemid);
	try {
		return $fs->create_file_from_storedfile($file_record, $file);
	} catch (Exception $e) {
		// file may exist - highly unlikely, we do not want upgrades to stop here
		return false;
	}
}

function local_connect_grade_based_on_range( $userid, $connectid, $startdaterange, $enddaterange, $regrade, $type ){
    global $DB;
    
    $sql = 'SELECT gg.* FROM {grade_grades} gg, {grade_items} gi, {'.$type.'} c
                WHERE gg.userid = ? AND gg.itemid = gi.id
                AND gi.itemmodule = "' .$type. '" AND gi.iteminstance = c.id
                AND c.id = ?';
    $grade = $DB->get_record_sql( $sql, array( $userid, $connectid ) ); 

    //echo "$userid, $connectid, $startdaterange, $enddaterange, $regrade<br />";

    // if they have no grade or have on in the range, return true so they'll be graded for the first time or regraded
    if( ( !$regrade && !$grade ) || ( $grade && $grade->timecreated > $startdaterange && $grade->timecreated < $enddaterange ) || empty($grade->timecreated)) return true;
    // otherwise, return false, don't grade them
    return false;
}

function local_connect_gradebook_update($connect, $entry, $type) {
    global $CFG, $DB;
    require_once($CFG->libdir . '/gradelib.php');
    
    if (isset($entry->grade) AND $entry->grade AND isset($entry->userid) AND $entry->userid) {
        $grades = new stdClass();
        $grades->id = $entry->userid;
        $grades->userid = $entry->userid;
        $grades->rawgrade = $entry->grade;
        $grades->dategraded = time();
        $grades->datesubmitted = time();
    } else $grades = null;

    $params = array();
    $params['itemname'] = $connect->name;
    $params['gradetype'] = GRADE_TYPE_VALUE;
    $params['grademax'] = 100;
    $params['grademin'] = 0;

    $sts = grade_update('mod/'.$type, $connect->course, 'mod', $type, $connect->id, 0, $grades, $params);
    $sts = $sts == GRADE_UPDATE_OK ? 1:0; // GRADE_UPDATE_OK value is actually zero, so we have to do this to turn it into true value

    // new stuff to ensure it saved the grade correctly
    if( isset( $connect->type ) && $connect->type == 'cquiz' && isset($entry->userid) && $entry->userid){
        $sql = "SELECT gg.id, gg.finalgrade FROM {grade_grades} gg, {grade_items} gi WHERE gi.iteminstance=? AND gi.itemtype='mod' AND gi.itemmodule='connectquiz' 
        AND gi.id = gg.itemid AND gg.userid=? LIMIT 1";
        $grade = $DB->get_record_sql($sql, array( $connect->id, $entry->userid ) );
        if( $grade && $grade->finalgrade == NULL ){// final grade is NULL, booooooo
            $DB->execute("DELETE FROM {grade_grades} WHERE id=?", array($grade->id));
            $sts = grade_update('mod/connectquiz', $connect->course, 'mod', 'connectquiz', $connect->id, 0, $grades, $params);
            // also lets email about it
            $emails = array( '' );

            $from = 'support@refineddata.com';
            $subject = 'User connect quiz grading error on '.$CFG->wwwroot;
            $message = "User: $entry->userid - Course: $connect->course - Activity Name: $connect->name";

            $headers = 'From: '.$from.'' . "\r\n" .
                    'Reply-To: '.$from.'' . "\r\n" .
                    'X-Mailer: PHP/' . phpversion();

            foreach( $emails as $email ){
                $to      = $email;
                mail($to, $subject, $message, $headers);
            }
        }
    }
    // end of new stuff

    if ($sts AND $entry->grade == 100 AND $cm = get_coursemodule_from_instance($type, $connect->id)) {
        // Mark Users Complete
        if ($cmcomp = $DB->get_record('course_modules_completion', array('coursemoduleid' => $cm->id, 'userid' => $entry->userid))) {
            $cmcomp->completionstate = 1;
            $cmcomp->viewed = 1;
            $cmcomp->timemodified = time();
            $DB->update_record('course_modules_completion', $cmcomp);
        } else {
            $cmcomp = new stdClass;
            $cmcomp->coursemoduleid = $cm->id;
            $cmcomp->userid = $entry->userid;
            $cmcomp->completionstate = 1;
            $cmcomp->viewed = 1;
            $cmcomp->timemodified = time();
            $DB->insert_record('course_modules_completion', $cmcomp);
        }
    }
    return $sts;
}

function local_connect_translate_display($connect, $type = 'connect') {
    global $CFG;

    if( $type != 'connect' && $type != 'rtrecording' ){
        $type = 'connect';
    }

    if ($type != 'connect' || $connect->type != 'video') {
        if (empty($connect->url) OR empty($connect->iconsize) OR $connect->iconsize == 'none') return '';
        $flags = '-';

        if (!empty($connect->iconpos) AND $connect->iconpos) $flags .= $connect->iconpos;
        if (!empty($connect->iconsilent) AND $connect->iconsilent) $flags .= 's';
        if (!empty($connect->iconphone) AND $connect->iconphone) $flags .= 'p';
        //if (!empty($connect->iconmouse) AND $connect->iconmouse) $flags .= 'm';
        if (!empty($connect->iconguests) AND $connect->iconguests) $flags .= 'g';
        if (!empty($connect->iconnorec) AND $connect->iconnorec) $flags .= 'a';

        $start = ''; //TODO - get start and end from Restrict Access area
        $end = '';
        $extrahtml = empty($connect->extrahtml) ? '' : $connect->extrahtml;

        $display = '[['.$type.'#' . $connect->url . '#' . $connect->iconsize . $flags . '#' . $start . '#' . $end . '#' . $extrahtml . '#' . $connect->forceicon . '#' . $connect->id . ']]';

        return $display;
    } else {
        // Video
        if (empty($connect->url) OR (isset($connect->textdisp) AND $connect->textdisp)) return '';
        $width = empty($connect->width) ? 640 : $connect->width;
        $height = empty($connect->height) ? 380 : $connect->height;
        $image = empty($connect->image) ? '' : $connect->image;

        return '<center>[[flashvideo#' . $connect->url . '#' . $width . '#' . $height . '#' . $image . ']]</center>';
    }
}

function local_connect_set_forceicon($connect, $type = 'connect') {
    //if( $type != 'connect' && $type != 'rtrecording' ){
        //$type = 'connect';
    //}

    $fmoptions = array(
        // 3 == FILE_EXTERNAL & FILE_INTERNAL
        // These two constant names are defined in repository/lib.php
        'return_types' => 3,
        'accepted_types' => 'images',
        'maxbytes' => 0,
        'maxfiles' => 1
    );

    if( !isset( $connect->coursemodule ) ){
        $cm = get_coursemodule_from_instance($type, $connect->id);
        $connect->coursemodule = $cm->id;
    }

    $context = context_module::instance($connect->coursemodule);
    $connect = file_postupdate_standard_filemanager($connect, 'forceicon', $fmoptions, $context, 'mod_'.$type, 'content', 0);
    return $connect;
}

/**
 * Serves the resource files.
 * @param object $course
 * @param object $cm
 * @param object $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @return bool false if file not found, does not return if found - just send the file
 */
function local_connect_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array(), $type = 'connect') {
    global $CFG, $DB;

    //RT-1367#Custom Icons not displaying
    /*if( $type != 'connect' && $type != 'rtrecording' ){
        $type = 'connect';
    }*/
    //END

    if ($filearea === 'content' and $context->contextlevel === CONTEXT_MODULE) {

        // Remove item id 0
        array_shift($args);

        require_course_login($course, true, $cm);
        //if (!has_capability('mod/connect:view', $context)) {
        //    return false;
        //}

        $relativepath = implode('/', $args);
        $fullpath = "/$context->id/mod_$type/content/0/$relativepath";

        $fs = get_file_storage();
        if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
            $fullpath = "/$context->id/mod_connect/content/0/$relativepath";
            if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
                return false;
            }
        }

        // finally send the file
        send_stored_file($file, 0, 0, true, $options);
    }

    if (preg_match('/_icon$/', $filearea)) {

        // Remove item id 0
        array_shift($args);

        require_course_login($course, true, $cm);
        //if (!has_capability('mod/connect:view', $context)) {
        //    return false;
        //}

        $relativepath = implode('/', $args);
        $fullpath = "/$context->id/mod_$type/$filearea/0/$relativepath";

        $fs = get_file_storage();
        if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
            return false;
        }

        // finally send the file
        send_stored_file($file, 0, 0, true, $options);
    }

    return false;
}
