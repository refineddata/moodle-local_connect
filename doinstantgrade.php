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
 * Do instant grading
 *
 * @package    local_connect
 * @copyright  2016 onwards Refined Data Solutions Inc {@link http://www.refineddata.com}
 * @author     Elvis Li <elvis.li@refineddata.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../config.php');
require_once($CFG->dirroot.'/course/lib.php');
global $CFG, $DB;
require_login();
$courseid = optional_param('courseid', 0, PARAM_INT);
$section = optional_param('section', 0, PARAM_INT);

$fromurl  = isset( $SESSION->fromdiscussion ) ? $SESSION->fromdiscussion : '';
if ( !$fromurl && isset( $_SERVER['HTTP_REFERER'] ) ) {
    $fromurl = $_SERVER['HTTP_REFERER'];
}
if ( !$courseid && $fromurl ) {
    if ( preg_match( '/course\/view.php\?id=(\d+)/', $fromurl, $match ) ) {
        if ( isset( $match[1] ) && $match[1] && is_numeric( $match[1] ) ) {
            $courseid = $match[1];
        }
    }
}

if ( !$courseid ) {
    redirect( "$CFG->wwwroot", '', 0 );
}

$context = context_system::instance();

$USER->usercourseconnectswithgrade = '';
$USER->usercourseconnects = '';

if ( $courseid && isset( $CFG->connect_instant_regrade ) AND $CFG->connect_instant_regrade ) {
    if ( file_exists( $CFG->dirroot . '/mod/connectquiz/lib.php' ) ) {
        if ( $connectquizs = $DB->get_records( 'connectquiz', array( 'course' => $courseid ) ) ) {
            require_once( $CFG->dirroot . '/mod/connectquiz/lib.php' );
            foreach ($connectquizs as $connectquiz) {
                connectquiz_regrade_fullquiz( $connectquiz, true, $USER->id );
            }
            rebuild_course_cache( $courseid );
            global $SESSION;
            unset( $SESSION->gradescorecache );
        }
    }
    // do recordings as well
    if ( file_exists( $CFG->dirroot . '/mod/rtrecording/lib.php' ) ) {
        if ( $rtrecs = $DB->get_records( 'rtrecording', array( 'course' => $courseid ) ) ) {
            require_once( $CFG->dirroot . '/mod/rtrecording/lib.php' );
            foreach ($rtrecs as $rtrec) {
                $entries = $DB->get_records( 'rtrecording_entries', array( 'rtrecording_id' => $rtrec->id, 'userid' => $USER->id ) );
                foreach ($entries as $entry) {
                    rtrecording_do_grade_entry( $entry );
                }
            }
        }
    }
}
if ( isset( $CFG->show_instant_regrade_message ) && $CFG->show_instant_regrade_message &&
        isset( $USER->usercourseconnectswithgrade ) && isset( $USER->usercourseconnects ) &&
        $USER->usercourseconnects != $USER->usercourseconnectswithgrade ) {
    redirect( "$CFG->wwwroot/course/view.php?id=$courseid#section-$section", get_string( 'connect_grades_notyet', 'mod_connectmeeting' ), 25 );
} else {
    redirect( "$CFG->wwwroot/course/view.php?id=$courseid#section-$section", '', 0 );
}
