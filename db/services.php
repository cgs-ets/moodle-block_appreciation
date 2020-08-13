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
 * Plugin external functions and services are defined here.
 *
 * @package   block_appreciation
 * @category  external
 * @copyright 2020 Michael Vangelovski
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'block_appreciation_get_recipient_users' => [
        'classname'     => 'block_appreciation\external\api',
        'methodname'    => 'get_recipient_users',
        'classpath'     => '',
        'description'   => 'Get\'s a list of users for the recipient selector',
        'type'          => 'read',
        'loginrequired' => true,
        'ajax'          => true,
    ],
    'block_appreciation_approve_post' => [
        'classname'     => 'block_appreciation\external\api',
        'methodname'    => 'approve_post',
        'classpath'     => '',
        'description'   => 'Approve a post',
        'type'          => 'write',
        'loginrequired' => true,
        'ajax'          => true,
    ],
    'block_appreciation_delete_post' => [
        'classname'     => 'block_appreciation\external\api',
        'methodname'    => 'delete_post',
        'classpath'     => '',
        'description'   => 'Delete a post',
        'type'          => 'write',
        'loginrequired' => true,
        'ajax'          => true,
    ],
    'block_appreciation_like_post' => [
        'classname'     => 'block_appreciation\external\api',
        'methodname'    => 'like_post',
        'classpath'     => '',
        'description'   => 'Like a post',
        'type'          => 'write',
        'loginrequired' => true,
        'ajax'          => true,
    ],
    'block_appreciation_unlike_post' => [
        'classname'     => 'block_appreciation\external\api',
        'methodname'    => 'unlike_post',
        'classpath'     => '',
        'description'   => 'Unlike a post',
        'type'          => 'write',
        'loginrequired' => true,
        'ajax'          => true,
    ],
    'block_appreciation_get_like_users' => [
        'classname'     => 'block_appreciation\external\api',
        'methodname'    => 'get_like_users',
        'classpath'     => '',
        'description'   => 'Get\'s a list of users that have liked a post',
        'type'          => 'read',
        'loginrequired' => true,
        'ajax'          => true,
    ],
];