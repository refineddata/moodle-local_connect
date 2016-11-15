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
 * Local Connect event handler function
 *
 * @package    local_connect
 * @category   event
 * @copyright  2016 onwards Refined Data Solutions Inc {@link http://www.refineddata.com}
 * @author     Elvis Li <elvis.li@refineddata.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function core_user_enrolled_event( $ue ) {
    global $DB, $CFG;
    require_once( $CFG->dirroot . '/group/lib.php' );
    if ( ! $enrol = $DB->get_record( 'enrol', array( 'id' => $ue->enrolid ) ) ) {
        return true;
    }
    if ( ! $course = $DB->get_record( 'course', array( 'id' => $enrol->courseid ) ) ) {
        return true;
    }
    if ( isset( $course->defaultgroupid ) AND $course->defaultgroupid AND empty( $enrol->customint5 ) ) {
        groups_add_member( $course->defaultgroupid, $ue->userid );
    }
    return true;
}