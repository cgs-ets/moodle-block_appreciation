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
 * Provides {@link block_appreciation\external\approve_post} trait.
 *
 * @package   block_appreciation
 * @category  external
 * @copyright 2020 Michael Vangelovski <michael.vangelovski@hotmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

namespace block_appreciation\external;

defined('MOODLE_INTERNAL') || die();

use \block_appreciation\persistents\thankyou;
use external_function_parameters;
use external_value;
use invalid_parameter_exception;

require_once($CFG->libdir.'/externallib.php');

/**
 * Trait implementing the external function block_appreciation_approve_post.
 */
trait approve_post {

    /**
     * Describes the structure of parameters for the function.
     *
     * @return external_function_parameters
     */
    public static function approve_post_parameters() {
        return new external_function_parameters([
            'id' => new external_value(PARAM_INT, 'ID of the post')
        ]);
    }

    /**
     * Approve the post
     *
     * @param int $id Id of the post
     */
    public static function approve_post($id) {
        global $USER;

        self::validate_parameters(self::approve_post_parameters(), compact('id'));

        // Get the block instance id of the post.
        $thankyou = new thankyou($id);

        // Get the block instance config for the post.
        $blockinstance = $DB->get_record('block_instances', array('id' => $thankyou->instanceid), '*', MUST_EXIST);
        $blockconfig = unserialize(base64_decode($blockinstance->configdata));
        
        // Check if user is the approver for this block.
        if ($USER->username == $blockconfig->approver) {
            // Approve the post.

        }
    }

    /**
     * Describes the structure of the function return value.
     *
     * @return external_single_structure
     */
    public static function approve_post_returns() {
         return null;
    }
}