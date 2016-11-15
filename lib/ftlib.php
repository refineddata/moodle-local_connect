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
 * Library of functions and constants for module meeting
 *
 * @package    local_connect
 * @copyright  2016 onwards Refined Data Solutions Inc {@link http://www.refineddata.com}
 * @author     Elvis Li <elvis.li@refineddata.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


/**
 * The simplified version of ft_exec
 * Goes out to FastTrack based on the global url/user/pass and gets what's requested of in that command.
 *
 * @param string $cmd - directory within fastrack to be reported on (endpoint).
 * @return string results of the api call.
 **/
function ft_exec( $cmd ) {
    global $CFG;

    if ( empty( $CFG->ft_protocol ) OR empty( $CFG->ft_server ) OR empty( $CFG->ft_admin_user ) OR empty( $CFG->ft_admin_pass ) OR empty( $cmd ) ) return false;

    $return = simplexml_load_file( $CFG->ft_protocol . $CFG->ft_admin_user . ':' . $CFG->ft_admin_pass . '@' . $CFG->ft_server . $cmd );

    if ( $return === false ) return false;
    return $return;
}

/**
 * Fast track - get scores
 * Goes through each session for that url, and compiles an array of user meeting times.
 *
 * @param string $url - meeting room Adobe custom url
 * @return array array of times (min), keyed by Adobe login.
 **/
function ft_get_scores( $url ) {
    global $CFG, $DB;
    
    if ( ! $session_recs = ft_exec( '/rest/manager/sessions' ) ) return false;
   
    // Get sessions for that url    
    $url = '/' . str_replace( '/', '', $url ) . '/';
    $sessions = array();
    foreach( $session_recs->Session as $session_rec ) {
        if ( strpos( $session_rec->meetingurl, $url ) !== false ) $sessions[] = $session_rec;
    }
    
    // Combine sessions for total time per user
    $scores = array();
    foreach( $sessions as $session ) {
        if ( isset( $session->id ) ) {
            if ( $vars = ft_exec( '/rest/manager/sessions/' . $session->id . '/reports' ) ) {
                foreach( $vars as $var ) {
                    if ( isset( $var->url ) AND isset( $var->type ) AND $var->type == 'attendees' AND isset( $var->xml ) AND $var->xml == 'true' ) {
                        if ( $attendees = ft_exec( '/manager/reports/sessions/' . $var->url . '~xml' ) ) {
                           foreach( $attendees->attendee as $attendee ) {
                                if ( isset( $attendee->email ) ) {
                                    $email     = (string) $attendee->email;
                                    $grade     = 0;
                                    if ( isset( $attendee->ft_passed ) AND $attendee->ft_passed == 'true' ) $grade = 100;
                                    if ( !isset( $scores[$email] ) OR $scores[$email] < $grade ) $scores[$email] = $grade;
                                 }
                            }
                        }
                    }
                }
            }
        }
    }

    return $scores;
}
