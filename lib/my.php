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
 * Connect Library for Handing My Adobe Connect Information
 *
 * This library was implemented as stand-alone product but is used here as an extension to Moodle
 * All code was written by and remains the property of Refined Data Solutions Inc. Not for resale
 * Copyright 2011
 *
 * @package    local_connect
 * @copyright  2016 onwards Refined Data Solutions Inc {@link http://www.refineddata.com}
 * @author     Elvis Li <elvis.li@refineddata.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('CONNECT_INTERNAL') || die();

/**
 * Get my connect meetings
 *
 * @param integer $days - number of days.
 * @param boolean $cache - true or false
 * @return array connect meetings
 */
function connect_mymeetings( $days, $cache=true ) {
    $connect = _connect_get_instance();
    $params = array();
    if (!empty($days)) {
        $params['filter-lt-date-begin'] = date('Y-m-d', strtotime( date('Y-m-d').' + '.$days.' days' ));
    }    
    $meetings = $connect->connect_call('get-my-meetings', $params, true);

    return $meetings;
}

function connect_allmymeetings() {
    $connect = _connect_get_instance();
    $params = array();
    $meetings = $connect->connect_call('get-all-my-meetings', $params, true);

    return $meetings;
}

