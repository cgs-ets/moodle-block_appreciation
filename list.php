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
use \block_appreciation\forms\form_post;
use \block_appreciation\persistents\post;

$instanceid = required_param('instanceid', PARAM_INT);
$courseid   = required_param('courseid', PARAM_INT);
$page = optional_param('page', 0, PARAM_INT);
$filter = optional_param('filter', '', PARAM_TEXT);
$filterval = optional_param('filterval', '', PARAM_RAW);

// Determine course and context.
$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$coursecontext = context_course::instance($courseid);

// Get specific block config and context.
$blockinstance = $DB->get_record('block_instances', array('id' => $instanceid), '*', MUST_EXIST);
$blockconfig = unserialize(base64_decode($blockinstance->configdata));
$blockcontext = context_block::instance($instanceid);

// Set up page parameters.
$PAGE->set_course($course);
$pageurl = new moodle_url('/blocks/appreciation/list.php', array(
    'instanceid' => $instanceid,
    'courseid' => $courseid,
    'page' => $page,
    'filter' => $filter,
    'filterval' => $filterval,
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

// Add css
$PAGE->requires->css(new moodle_url($CFG->wwwroot . '/blocks/appreciation/styles.css', array('nocache' => rand())));
// Add extra js.
$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/blocks/appreciation/js/infinite-scroll.pkgd.min.js'), true );

// Get the urls.
list($listurl, $addnewurl) = get_block_urls($instanceid, $courseid);

//Get the unapproved url
$approver = isset($blockconfig->approver) ? $blockconfig->approver : 0;
$isapprover = ($USER->username == $approver);
$numunapproved = post::count_records(['instanceid' => $instanceid, 'approved' => 0, 'deleted' => 0]);

// Get the thank yous.
$posts = post::get_by_filter($instanceid, $filter, $filterval, $page);

// Build the output.
echo $OUTPUT->header();

// Set up relateds for list exporter.
$relateds = [
    'context' => $coursecontext,
    'instanceid' => $instanceid,
    'courseid' => $courseid,
	'posts' => $posts,
    'page' => $page,
    'isapprover' => $isapprover,
    'baseurl' => $pageurl,
];

// Export the list.
$list = new block_appreciation\external\list_exporter(null, $relateds);

// Set up filter logic for template.
$filterlogic = new \stdClass();
$filterlogic->block = false;
$filterlogic->all = ($filter == '');
$filterlogic->unapproved = ($filter == 'unapproved');
$filterlogic->byme = ($filter == 'byme');
$filterlogic->forme = ($filter == 'forme');
$filterlogic->foruser = ($filter == 'foruser');
$filterlogic->thisweek = ($filter == 'thisweek');
$filterlogic->filter = $filter;
$filterlogic->filterval = $filterval;
$filtera = ($filterlogic->foruser) ? fullname(\core_user::get_user_by_username($filterval)) : $filterval;
$filterlogic->filterstr =  $filter ? get_string('list:'.$filter, 'block_appreciation', $filtera) : '';

// Set up the template data.
$data = array(
    'instanceid' => $instanceid,
	'list' => $list->export($OUTPUT),
    'listurl' => $listurl->out(false),
    'addnewurl' => $addnewurl->out(false),
    'canpost' => has_capability('block/appreciation:post', $blockcontext),
    'isapprover' => $isapprover,
    'numunapproved' => $numunapproved,
    'filter' => $filterlogic,
);

// Render the appreciation list.
echo $OUTPUT->render_from_template('block_appreciation/list', $data);

// Add scripts.
$PAGE->requires->js_call_amd('block_appreciation/content', 'init', array('instanceid' => $instanceid));

echo $OUTPUT->footer();
