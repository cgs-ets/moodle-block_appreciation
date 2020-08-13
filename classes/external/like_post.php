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
 * Provides {@link block_appreciation\external\like_post} trait.
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
 * Trait implementing the external function block_appreciation_like_post.
 */
trait like_post {

    /**
     * Describes the structure of parameters for the function.
     *
     * @return external_function_parameters
     */
    public static function like_post_parameters() {
        return new external_function_parameters([
            'id' => new external_value(PARAM_INT, 'The post id'),
        ]);
    }

    /**
     * like_post a post
     *
     * @param int $id The post id
     */
    public static function like_post($id) {
        global $USER;

        $context = \context_user::instance($USER->id);
        self::validate_context($context);

        self::validate_parameters(self::like_post_parameters(), compact('id'));

        post::like($id);
    }

    /**
     * Describes the structure of the function return value.
     *
     * @return external_single_structure
     */
    public static function like_post_returns() {
        return null;
    }

}