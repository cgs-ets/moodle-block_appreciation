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

defined('MOODLE_INTERNAL') || die();

class block_appreciation extends block_base {

    /**
     * Core function used to initialize the block.
     */
    public function init() {
        $this->title = '';
    }

    /**
    * We have global config/settings data.
    * @return bool
    */
    public function has_config() {
        return true;
    }

    /**
     * Controls whether multiple instances of the block are allowed on a page
     *
     * @return bool
     */
    public function instance_allow_multiple() {
        return false;
    }

    /**
     * Controls whether the block is configurable
     *
     * @return bool
     */
    public function instance_allow_config() {
        return true;
    }


    /**
     * Set where the block should be allowed to be added
     *
     * @return array
     */
    public function applicable_formats() {
        return array('all' => true);
    }


    /**
     * Used to generate the content for the block.
     * @return object
     */
    public function get_content() {
        global $PAGE, $COURSE, $USER, $OUTPUT, $DB;

        // If content has already been generated, don't waste time generating it again.
        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->text = '';
        $this->content->footer = '';

        if (isguestuser() || !isloggedin()) {
            return $this->content;
        }

        // Get the thankyou posts.

        $displaynum = $this->config->displaynum;

        // Fetch the thankyou posts.
        
        //$relateds = [
        //    'context' => $context,
        //    'posts' => $posts,
        //    'page' => 0,
        //];
        //$list = new list_exporter(null, $relateds);

        // View more link.
        $link = new \moodle_url('/blocks/appreciation/view.php', array(
            'instanceid' => $this->instance->id,
            'courseid' => $COURSE->id
        ));

        // Contruct the data for rendering.
        $data = array(
            //'list' => $list->export($OUTPUT),
            'instanceid' => $this->instance->id,
            'viewmoreurl' => $link->out(false),
        );

        // Render the appreciation list.
        //$this->content->text = $OUTPUT->render_from_template('block_appreciation/list', $data);

        $this->content->text = $link->out(false);

        return $this->content;
    }


}
