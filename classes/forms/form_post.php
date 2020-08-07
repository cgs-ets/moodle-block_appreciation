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
 * Form definition for posting.
 * *
 * @package   block_appreciation
 * @copyright 2020 Michael Vangelovski
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_appreciation\forms;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/repository/lib.php');

class form_post extends \moodleform {

    /**
     * Returns the options array to use in announcement text editor
     *
     * @return array
     */
    public static function editor_options() {
        global $CFG;

        return array(
            'maxfiles' => 10,
            'maxbytes' => $CFG->maxbytes,
            'trusttext'=> true,
            'noclean' => true,
            'return_types'=> FILE_INTERNAL | FILE_EXTERNAL,
            'subdirs' => 0
        );
    }

    /**
     * Form definition
     *
     * @return void
     */
    function definition() {
        global $CFG, $OUTPUT, $USER, $DB;

        $mform =& $this->_form;

        $post = $this->_customdata['post'];

        $mform->addElement('header', 'general', '');

        // Recipient. A hidden field with a custom JS driven user select field.
        // The recipient field is a text field hidden by css rather than a hidden field so that we can attach validation to it. 
        $mform->addElement('text', 'recipient', 'Recipient JSON');
        $mform->setType('recipient', PARAM_RAW);
        $mform->addRule('recipient', get_string('required'), 'required', null, 'client');
        $recipientselectorhtml = $OUTPUT->render_from_template('block_appreciation/recipient_selector', array()); 
        $mform->addElement('html', $recipientselectorhtml);

        // Message.
        $type = 'editor';
        $name = 'message';
        $title = get_string('postform:message', 'block_appreciation');
        $mform->addElement($type, $name, $title, null, self::editor_options());
        $mform->setType($name, PARAM_RAW);
        $mform->addRule($name, get_string('required'), 'required', null, 'client');

        // Buttons.
        $this->add_action_buttons(true, get_string('savechanges'));

        // Hidden fields
        $mform->addElement('hidden', 'instanceid');
        $mform->setType('instanceid', PARAM_INT);
        $mform->addElement('hidden', 'courseid');
        $mform->setType('courseid', PARAM_INT);
    }

    /**
     * Form validation
     *
     * @param array $data data from the form.
     * @param array $files files uploaded.
     * @return array of errors.
     */
    function validation($data, $files) {
        $errors = parent::validation($data, $files);
        if (empty($data['message']['text'])) {
            $errors['message'] = get_string('postform:erroremptymessage', 'block_appreciation');
        }
        return $errors;
    }

}