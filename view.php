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
 * A block that allows users to post a message of thanks to other users in a course.
 *
 * @package   block_appreciation
 * @copyright 2020 Michael Vangelovski
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
// Include required files and classes.
require_once('../../config.php');
require_once($CFG->dirroot . '/blocks/appreciation/locallib.php');
use \block_appreciation\persistents\post;

$postid = optional_param('id', 0, PARAM_INT);

// Get the block instance id of the post.
$post = new post($postid);
if (empty($post)) {
    \core\notification::error(get_string('list:postnotfound', 'block_appreciation'));
    echo $OUTPUT->header();
    echo $OUTPUT->footer();
}

// Get the block instance config for the post.
$instanceid = $post->get('instanceid');
$blockinstance = $DB->get_record('block_instances', array('id' => $instanceid), '*', MUST_EXIST);
$blockconfig = unserialize(base64_decode($blockinstance->configdata));
if (empty($blockinstance)) {
    \core\notification::error(get_string('list:blocknotfound', 'block_appreciation'));
    echo $OUTPUT->header();
    echo $OUTPUT->footer();
}
$blockcontext = context_block::instance($instanceid);

// Get the courseid based on the block instanceid.
$courseid = get_courseid_by_block_instance($instanceid);
if (empty($courseid)) {
    \core\notification::error(get_string('list:coursenotfound', 'block_appreciation'));
    echo $OUTPUT->header();
    echo $OUTPUT->footer();
}

// Load the course record and context.
$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$coursecontext = context_course::instance($courseid);

// Set up page parameters.
$PAGE->set_course($course);
$PAGE->requires->css('/blocks/appreciation/styles.css');
$pageurl = new moodle_url('/blocks/appreciation/view.php', array(
    'id' => $postid,
));
$PAGE->set_url($pageurl);
$PAGE->set_context($coursecontext);
$title = get_string('title', 'block_appreciation');
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->navbar->add($title);

// Check user is logged in and capable of viewing.
require_login($course, false);
require_capability('block/appreciation:view', $blockcontext);

// Get the list url.
$listurl = new \moodle_url('/blocks/appreciation/list.php', array(
    'instanceid' => $instanceid,
    'courseid' => $COURSE->id
));

// Get the add new URL.
$addnewurl = new moodle_url('/blocks/appreciation/post.php', array(
    'instanceid' => $instanceid,
    'courseid' => $courseid,
));


// Add css.
$PAGE->requires->css(new moodle_url($CFG->wwwroot . '/blocks/appreciation/styles.css', array('nocache' => rand().rand())));


// Build the output.
echo $OUTPUT->header();

// Export the announcements list.
$approver = isset($blockconfig->approver) ? $blockconfig->approver : 0;
$isapprover = ($USER->username == $approver);
$relateds = [
    'context' => $coursecontext,
    'instanceid' => $instanceid,
    'courseid' => $courseid,
	'posts' => array($post),
    'page' => 0,
    'isapprover' => $isapprover,
];

$list = new block_appreciation\external\list_exporter(null, $relateds);
$data = array(
    'instanceid' => $instanceid,
    'list' => $list->export($OUTPUT),
    'listurl' => $listurl->out(false),
    'addnewurl' => $addnewurl->out(false),
    'canpost' => has_capability('block/appreciation:post', $blockcontext),
);

// Render the appreciation list.
echo $OUTPUT->render_from_template('block_appreciation/view', $data);

// Add scripts.
$PAGE->requires->js_call_amd('block_appreciation/content', 'init', array('instanceid' => $instanceid));

echo $OUTPUT->footer();
