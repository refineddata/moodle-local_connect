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
 * Get file tree of Adobe Connect
 *
 * @package    local_connect
 * @copyright  2016 onwards Refined Data Solutions Inc {@link http://www.refineddata.com}
 * @author     Elvis Li <elvis.li@refineddata.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../config.php');

require_login();
require_once( $CFG->dirroot . '/local/connect/lib.php' );
require_once($CFG->dirroot . '/local/connect/lib/sco.php');

$scoid = optional_param('dir', null, PARAM_RAW);
$scoid = preg_replace('/\//', '', $scoid);

$type = 'content';


if (!isset($scoid) OR !$scoid) {
    $shortcuts = connect_get_sco_shortcuts();
    if (!is_array($shortcuts)) {
        die($shortcuts);
    }
    echo "<ul class='jqueryFileTree'>";
    foreach ($shortcuts as $sco) {
        if ($type == 'meeting' AND $sco->type == 'my-meetings') {
            cp_one('directory', 'My-Meetings', $sco->sco_id);
        }
        if ($type == 'meeting' AND $sco->type == 'user-meetings') {
            cp_one('direcoty', 'User-Meetings', $sco->sco_id);
        }
        if ($type == 'meeting' AND $sco->type == 'meetings') {
            cp_one('direcoty', 'Shared-Meetings', $sco->sco_id);
        }
        if ($type == 'content' AND $sco->type == 'my-content') {
            cp_one('direcoty', 'My-content', $sco->sco_id);
        }
        if ($type == 'content' AND $sco->type == 'user-content') {
            cp_one('direcoty', 'User-content', $sco->sco_id);
        }
        if ($type == 'content' AND $sco->type == 'content') {
            cp_one('direcoty', 'Shared-content', $sco->sco_id);
        }
    }
    echo "</ul>";
} else {
    $scos = connect_get_sco_contents($scoid);
    if (!is_array($scos)) {
        die($scos);
    }
    $first = 1;
    echo "<ul class='jqueryFileTree'>";
    foreach ($scos as $sco) {
        $datestart = !empty($sco->date_begin) ? userdate(strtotime($sco->date_begin), '%a %b %d, %Y %l:%M %p',
            $USER->timezone) : '&nbsp;';
        $datemod = !empty($sco->date_modified) ? userdate(strtotime($sco->date_modified), '%a %b %d, %Y %l:%M %p',
            $USER->timezone) : '&nbsp;';
        $dateend = !empty($sco->date_end) ? userdate(strtotime($sco->date_end), '%a %b %d, %Y %l:%M %p',
            $USER->timezone) : '&nbsp;';

        cp_one($sco->type, $sco->name, $sco->sco_id, $sco->icon, $sco->url_path, $datemod, $datestart, $dateend);
    }
    echo "</ul>";
}

/**
 * Display folders and files
 *
 * @param string $type sco type.
 * @param string $name sco name.
 * @param integer $scoid sco id.
 * @param string $icon optional sco icon.
 * @param string $url optional sco url path.
 * @param string $datemod optional sco date modified.
 * @param string $datestart optional sco start date.
 * @param string $dateend optional sco start date.
 * @return none
 */

function cp_one(
    $type,
    $name,
    $scoid,
    $icon = 'folder',
    $url = '',
    $datemod = '&nbsp;',
    $datestart = '&nbsp;',
    $dateend = '&nbsp;'
) {
    global $first;

    if ($type == 'meeting' OR $type == 'content') {
        $ext = 'txt';
        if ($icon == 'meeting') {
            $ext = 'doc';
        } else if ($icon == 'archive' || $icon == 'presentation') {
            $ext = 'mp4';
        } else if ($icon == 'quiz') {
            $ext = 'mp3';
        } else if ($icon == 'slideshow') {
            $ext = 'pdf';
        }
        if ($first) {
            echo "<li><a style='width:100%;'>
                <div style='float: left; width: 26%; overflow: hidden;'>Name</div>
                <div style='float: left; width: 19%; overflow: hidden; padding-left:10px'>Modified</div>
                <div style='float: left; width: 19%; overflow: hidden; padding-left:10px'>Start</div>
                <div style='float: left; width: 19%; overflow: hidden; padding-left:10px'>End</div>
                <div style='float: left; width: 11%; overflow: hidden; padding-left:10px'>Url</div>
            </a></li>";
            echo "<li><a style='width:100%;'>
                <div style='float: left; width: 95%; overflow: hidden;'><hr style='margin: 8px 0;' /></div>
            </a></li>";
            $first = 0;
        }

        echo "<li class='file ext_" . $ext . "'><a href='#' style='width:100%;' rel='" . $url . "'>
                <div style='float: left; width: 26%; overflow: hidden;'>" . $name . "</div>
                <div style='float: left; width: 19%; overflow: hidden; padding-left:10px;'>" . $datemod . "</div>
                <div style='float: left; width: 19%; overflow: hidden; padding-left:10px;'>" . $datestart . "</div>
                <div style='float: left; width: 19%; overflow: hidden;padding-left:10px;'>" . $dateend . "</div>
                <div style='float: left; width: 11%; overflow: hidden; padding-left:10px'>" . $url . "</div>
            </a></li>";
    } else {
        echo "<li class='directory collapsed'><a href='#' style='width:100%;' rel='/" . $scoid . "/'><div style='float: left; width: 100%;'>" . $name . "</div></a></li>";
    }
}

