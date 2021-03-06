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
 * Provides {@link block_appreciation\external\get_recipient_users} trait.
 *
 * @package   block_appreciation
 * @category  external
 * @copyright 2020 Michael Vangelovski <michael.vangelovski@hotmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

namespace block_appreciation\external;

defined('MOODLE_INTERNAL') || die();

use context_user;
use external_function_parameters;
use external_value;
use invalid_parameter_exception;
use external_multiple_structure;
use external_single_structure;

require_once($CFG->libdir.'/externallib.php');

/**
 * Trait implementing the external function block_appreciation_get_recipient_users.
 */
trait get_recipient_users {

    /**
     * Describes the structure of parameters for the function.
     *
     * @return external_function_parameters
     */
    public static function get_recipient_users_parameters() {
        return new external_function_parameters([
            'instanceid' => new external_value(PARAM_INT, 'The block instance id'),
            'query' => new external_value(PARAM_RAW, 'The search query')
        ]);
    }

    /**
     * Gets a list of announcement users
     *
     * @param int $query The search query
     */
    public static function get_recipient_users($instanceid, $query) {
        global $USER;

        self::validate_parameters(self::get_recipient_users_parameters(), compact('instanceid', 'query'));
        
        $results = self::search_recipient_users($instanceid, $query);

        $users = array();
        foreach ($results as $user) {
            // Do not add current user as an option.
            if ($user->username == $USER->username) {
                continue;
            }
            $userphoto = new \moodle_url('/user/pix.php/'.$user->id.'/f2.jpg');
            $userurl = new \moodle_url('/user/profile.php', array('id' => $user->id));
            $users[] = array(
                'username' => $user->username,
                'fullname' => fullname($user),
                'photourl' => $userphoto->out(false),
            );
        }
        return $users;
    }

    /**
     * Describes the structure of the function return value.
     *
     * @return external_single_structure
     */
    public static function get_recipient_users_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'username' => new external_value(PARAM_RAW, 'The user\'s username'),
                    'fullname' => new external_value(PARAM_RAW, 'The user\'s full name'),
                    'photourl' => new external_value(PARAM_RAW, 'The user\'s photo src'),
                )
            )
        );
    }

    /*
    * Search for staff by full name.
    *
    * @param string $query. The search query.
    * @return array of user objects.
    */
    private static function search_recipient_users($instanceid, $query) {
        global $DB, $USER;

        // Get the courseid based on the block instanceid.
        $sql = "SELECT c.instanceid
                FROM {context} c, {block_instances} b
                WHERE b.id = ?
                AND c.id = b.parentcontextid";
        $params = array($instanceid);
        $courseid = $DB->get_field_sql($sql, $params);
        if (empty($courseid)) {
            return [];
        }

        // Ensure that the user is enrolled in the course.
        $context = \context_course::instance($courseid);
        if (!is_enrolled($context, $USER->id, '', true)) {
            return [];
        }

        // Search for users within course.
        $sql = "SELECT u.*
                FROM {user} u, {user_enrolments} ue, {enrol} e
                WHERE e.courseid = ?
                AND ue.enrolid = e.id
                AND u.id = ue.userid
                AND (
                    LOWER(u.firstname) LIKE ? OR 
                    LOWER(u.lastname) LIKE ? OR
                    ? LIKE CONCAT('%',LOWER(u.firstname),'%') OR
                    ? LIKE CONCAT('%',LOWER(u.lastname),'%')
                )";
        $params = array(
            $courseid,
            '%'.$DB->sql_like_escape(strtolower($query)).'%',
            '%'.$DB->sql_like_escape(strtolower($query)).'%',
            strtolower($query),
            strtolower($query),
        );

        return $DB->get_records_sql($sql, $params, 0, 15);
    }
}