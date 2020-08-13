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
 * Strings for block_appreciation
 *
 * @package   block_appreciation
 * @copyright 2020 Michael Vangelovski
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
$string['pluginname'] = 'Appreciation';
$string['pluginname_desc'] = 'A block that allows users to post a message of thanks to other users in a course.';
$string['title'] = 'Thank you\'s';
$string['block_appreciation:addinstance'] = 'Add an "Appreciation" block';
$string['block_appreciation:edit'] = 'Edit an "Appreciation" block';
$string['privacy:metadata'] = 'The "Appreciation" block does not store any personal data.';
$string['add'] = 'Add';
$string['config:displaynum'] = 'Display number';
$string['config:displaynumdesc'] = 'Number of most recent posts to display';
$string['config:approver'] = 'Approver';
$string['config:approverdesc'] = 'Username of the person in the course that will be approving posts.';
$string['config:hideblock'] = 'Hide block';
$string['config:hideblockdesc'] = 'Tick this to hide the block contents from the course. The list can still be accessed via direct link.';


$string['postform:postthankyou'] = 'Post a thank you';
$string['postform:postaddedsuccess'] = 'Your message was successfully added.';
$string['postform:message'] = 'Message';
$string['postform:recipient'] = 'Recipient';
$string['postform:recipientplaceholder'] = 'Search by name';
$string['postform:recipientnoselection'] = 'No selection.';
$string['postform:erroremptymessage'] = 'Post message cannot be empty';

$string['addnew'] = 'Add new';
$string['viewunapproved'] = 'View {$a} unapproved';

$string['list:postunapproved'] = 'This post is not visible to users because approval is pending.';
$string['list:noposts'] = "No posts found.";
$string['list:postnotfound'] = 'Post not found. It may have been deleted.';
$string['list:blocknotfound'] = 'Block not found. It may have been deleted.';
$string['list:coursenotfound'] = 'Course not found. It may have been deleted.';
$string['list:viewall'] = 'View all';
$string['list:numlikes'] = 'Liked by {$a} people';
$string['list:likes'] = 'Likes';

$string['notification:subject'] = '{$a->siteshortname}: {$a->creatorfullname} has thanked you.';
$string['messageprovider:notifications'] = 'Appreciation (Thank you) post notifications';

