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
 * Debugger.
 *
 * @package   block_appreciation
 * @copyright 2020 Michael Vangelovski <michael.vangelovski@hotmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

// Include required files and classes.
require_once(dirname(__FILE__) . '/../../config.php');
require_once('locallib.php');
use block_appreciation\persistents\post;

// Set context.
$context = context_system::instance();

// Set up page parameters.
$PAGE->set_context($context);
$pageurl = new moodle_url('/block/appreciation/debugger.php', array());
$PAGE->set_url($pageurl);
$title = get_string('pluginname', 'block_appreciation');
$PAGE->set_heading($title);
$PAGE->set_title($SITE->fullname . ': ' . $title);
$PAGE->navbar->add($title);
// Add css
$PAGE->requires->css(new moodle_url($CFG->wwwroot . '/block/appreciation/styles.css', array('nocache' => rand().rand())));

// Check user is logged in.
require_login();
require_capability('moodle/site:config', $context, $USER->id); 

// Build page output
$output = '';
//$output .= $OUTPUT->header();





echo "<pre>";
$api = new block_appreciation\external\api;
$out = $api->approve_post(4);
var_export($out);
exit;







// Final outputs
$output .= $OUTPUT->footer();
echo $output;