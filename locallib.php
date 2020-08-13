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
 * @package   block_appreciation
 * @copyright 2020 Michael Vangelovski
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/filelib.php');

define('APPRECIATION_PERPAGE', 50); // The number of posts to fetch at a time on the list page.
define('APPRECIATION_DISPLAYNUM', 5); // The number of posts displayed in the block content.

// Get the courseid based on the block instanceid.
function get_courseid_by_block_instance($instanceid) {
	global $DB;

	$sql = "SELECT c.instanceid
        FROM {context} c, {block_instances} b
        WHERE b.id = ?
        AND c.id = b.parentcontextid";
	$params = array($instanceid);
	$courseid = $DB->get_field_sql($sql, $params);
	//$course = get_course($courseid);

	return (int) $courseid;
}

/**
 * Removes properties from user record that are not necessary for sending post notifications.
 *
 */
function minimise_recipient_record($recipient) {
    // Make sure we do not store info there we do not actually
    // need in mail generation code or messaging.
    unset($recipient->institution);
    unset($recipient->department);
    unset($recipient->address);
    unset($recipient->city);
    unset($recipient->url);
    unset($recipient->currentlogin);
    unset($recipient->description);
    unset($recipient->descriptionformat);
    unset($recipient->icq);
    unset($recipient->skype);
    unset($recipient->yahoo);
    unset($recipient->aim);
    unset($recipient->msn);
    unset($recipient->phone1);
    unset($recipient->phone2);
    unset($recipient->country);
    unset($recipient->firstaccess);
    unset($recipient->lastaccess);
    unset($recipient->lastlogin);
    unset($recipient->lastip);

    return $recipient;
}

function get_block_urls($instanceid, $courseid) {
    // Get the list url.
    $listurl = new \moodle_url('/blocks/appreciation/list.php', array(
        'instanceid' => $instanceid,
        'courseid' => $courseid
    ));

    // Get the add new URL.
    $addnewurl = new moodle_url('/blocks/appreciation/post.php', array(
        'instanceid' => $instanceid,
        'courseid' => $courseid,
    ));

    return array($listurl, $addnewurl);
}