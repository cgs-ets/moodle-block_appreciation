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
use \block_appreciation\forms\form_post;
use \block_appreciation\persistents\post;

$instanceid = required_param('instanceid', PARAM_INT);
$courseid   = required_param('courseid', PARAM_INT);

// Determine course and context.
$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$coursecontext = context_course::instance($courseid);

// Get specific block config and context.
$blockinstance = $DB->get_record('block_instances', array('id' => $instanceid), '*', MUST_EXIST);
$blockconfig = unserialize(base64_decode($blockinstance->configdata));
$blockcontext = context_block::instance($instanceid);

// Set up page parameters.
$PAGE->set_course($course);
$PAGE->set_url(
    '/blocks/appreciation/post.php',
    array(
        'instanceid' => $instanceid,
        'courseid' => $courseid,
    )
);
$PAGE->set_context($coursecontext);
$title = get_string('title', 'block_appreciation');
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->navbar->add($title, new moodle_url('/blocks/appreciation/list.php', array('instanceid' => $instanceid, 'courseid' => $courseid)));

// Check user is logged in and capable of posting.
require_login($course, false);
require_capability('block/appreciation:post', $blockcontext);

$redirectdefault = new \moodle_url('/blocks/appreciation/list.php', array(
    'instanceid' => $instanceid,
    'courseid' => $courseid,
));

// Initialise a default post.
$post = new stdClass();
$post->message = '';

// Load the form.
$formpost = new form_post(
    'post.php', // Action.
    array('post' => $post), // Custom data.
    'post', // Method
    '', // Target frame for form submission.
    array('data-form' => 'appreciation-post') // Additional form attributes.
);

// Redirect if cancel was clicked.
if ($formpost->is_cancelled()) {
    redirect($redirectdefault->out());
}

// This is what actually sets the data in the form.
$formpost->set_data(array(
    'instanceid' => $instanceid,
    'courseid' => $courseid,
    'general' => get_string('postform:postthankyou', 'block_appreciation'),
));

// Form submitted.
if ($formdata = $formpost->get_data()) {

    // Create object in the database.
    $data = new stdClass();
    $data->instanceid = $instanceid;
    $data->creator = $USER->username;
    $recipient = json_decode($formdata->recipient);
    $data->recipient = $recipient->username;
    $data->message = '';
    $data->messageformat = $formdata->message['format'];
    $post = new post(0, $data);
    $post->create();

    // Store message files to a permanent file area and save message text.
    $message = file_save_draft_area_files(
        $formdata->message['itemid'], 
        $coursecontext->id, 
        'block_appreciation', 
        'post', 
        $post->get('id'), 
        form_post::editor_options(), 
        $formdata->message['text']
    );
    $post->set('message', $message);
    $post->update();

    // Redirect.
    $message = get_string("postform:postaddedsuccess", "block_appreciation");
    redirect(
        $redirectdefault->out(),
        '<p>'.$message.'</p>',
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

// Add css.
//$PAGE->requires->css('/blocks/appreciation/styles.css');
$PAGE->requires->css(new moodle_url($CFG->wwwroot . '/blocks/appreciation/styles.css', array('nocache' => rand())));

echo $OUTPUT->header();

$formpost->display();

//echo $OUTPUT->render_from_template('block_appreciation/loadingoverlay', array('class' => 'lann-post-overlay'));

// Add scripts.
$PAGE->requires->js_call_amd('block_appreciation/post', 'init', array('instanceid' => $instanceid));

echo $OUTPUT->footer();
