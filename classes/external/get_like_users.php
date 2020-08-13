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
 * Provides {@link block_appreciation\external\get_like_users} trait.
 *
 * @package   block_appreciation
 * @category  external
 * @copyright 2020 Michael Vangelovski <michael.vangelovski@hotmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

namespace block_appreciation\external;

defined('MOODLE_INTERNAL') || die();

use \block_appreciation\persistents\post;
use context_user;
use external_function_parameters;
use external_value;
use invalid_parameter_exception;
use external_multiple_structure;
use external_single_structure;

require_once($CFG->libdir.'/externallib.php');

/**
 * Trait implementing the external function block_appreciation_get_like_users.
 */
trait get_like_users {

    /**
     * Describes the structure of parameters for the function.
     *
     * @return external_function_parameters
     */
    public static function get_like_users_parameters() {
        return new external_function_parameters([
            'id' => new external_value(PARAM_INT, 'The post id'),
        ]);
    }

    /**
     * Gets a list of announcement users
     *
     * @param int $query The search query
     */
    public static function get_like_users($id) {
        global $USER;

        self::validate_parameters(self::get_like_users_parameters(), compact('id'));
        
        $likeusers = post::get_like_users($id);

        $users = array('userslist' => array());
        foreach ($likeusers as $user) {
            $userphoto = new \moodle_url('/user/pix.php/'.$user->id.'/f2.jpg');
            $userurl = new \moodle_url('/user/profile.php', array('id' => $user->id));
            $users['userslist'][] = array(
                'username' => $user->username,
                'fullname' => fullname($user),
                'photo' => $userphoto->out(false),
                'url' => $userurl->out(false),
            );
        }
        return $users;
    }

    /**
     * Describes the structure of the function return value.
     *
     * @return external_single_structure
     */
    public static function get_like_users_returns() {
        return new external_single_structure(
            array (
                'userslist' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'username' => new external_value(PARAM_RAW, 'The user\'s username'),
                            'fullname' => new external_value(PARAM_RAW, 'The user\'s full name'),
                            'photo' => new external_value(PARAM_RAW, 'The user\'s photo src'),
                            'url' => new external_value(PARAM_RAW, 'The user\'s profile url'),
                        )
                    )
                )
            )
        );
    }


}