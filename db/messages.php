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
 * Defines message providers (types of messages being sent)
 *
 * @package   block_appreciation
 * @category  external
 * @copyright 2020 Michael Vangelovski
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


$messageproviders = array (
    // Ordinary single notifications
    'notifications' => array(
        'defaults' => array(
            'popup' => MESSAGE_PERMITTED,
            'email' => MESSAGE_PERMITTED,
            'airnotifier' => MESSAGE_PERMITTED,
        ),
    ),
    // Notification for a like
    'likes' => array(
        'defaults' => array(
            'popup' => MESSAGE_PERMITTED,
            'email' => MESSAGE_PERMITTED,
            'airnotifier' => MESSAGE_PERMITTED,
        ),
    ),
);
