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
 * Provides the {@link block_appreciation\persistents\post} class.
 *
 * @package   block_appreciation
 * @copyright 2020 Michael Vangelovski
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_appreciation\persistents;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/blocks/appreciation/locallib.php');
use \core\persistent;
use \core_user;
use \context_user;
use \context_course;

/**
 * Persistent model representing a single post.
 */
class post extends persistent {

    /** Table to store this persistent model instances. */
    const TABLE = 'block_appreciation_posts';

    /**
     * Return the definition of the properties of this model.
     *
     * @return array
     */
    protected static function define_properties() {
        return [
            "instanceid" => [
                'type' => PARAM_INT,
            ],
            "creator" => [
                'type' => PARAM_RAW,
            ],
            "recipient" => [
                'type' => PARAM_RAW,
            ],
            "mailed" => [
                'type' => PARAM_BOOL,
                'default' => 0,
            ],
            "notified" => [
                'type' => PARAM_BOOL,
                'default' => 0,
            ],
            "approved" => [
                'type' => PARAM_BOOL,
                'default' => 0,
            ],
            "message" => [
                'type' => PARAM_RAW,
            ],
            "deleted" => [
                'type' => PARAM_BOOL,
                'default' => 0,
            ],
        ];
    }

    /**
     * Get appreciation posts.
     *
     * @param string $instanceid. The block instance id.
     * @param int $page.
     * @return array.
     */
    public static function get_for_user($instanceid, $isapprover = 0, $page = 0, $perpage = 0) {
        global $DB, $USER;

        if (!$perpage) {
            $perpage = APPRECIATION_PERPAGE;
        }
        $from = $perpage*$page;

        $params = array();
        $sql = "SELECT *
                   FROM {block_appreciation_posts}
                  WHERE instanceid = ? 
                    AND deleted = 0 ";
        $params[] = $instanceid;

        if (!$isapprover) {
            $sql .= " AND (approved = 1 OR creator = ?) ";
            $params[] = $USER->username;
        }

        $sql .= " ORDER BY timemodified DESC";
        
        $posts = array();
        $recordset = $DB->get_recordset_sql($sql, $params, $from, $perpage);
        foreach ($recordset as $record) {
            $posts[] = new static(0, $record);
        }
        $recordset->close();
     
        return $posts;
    }


    /**
     * Get appreciation posts that need approval.
     *
     * @param string $instanceid. The block instance id.
     * @param int $page.
     * @return array.
     */
    public static function get_for_approval($instanceid, $page = 0) {
        global $DB, $USER;

        $perpage = APPRECIATION_PERPAGE;
        $from = $perpage*$page;

        $sql = "SELECT *
                   FROM {block_appreciation_posts}
                  WHERE instanceid = ? 
                    AND deleted = 0
                    AND approved = 0
               ORDER BY timemodified DESC";

        $params = array($instanceid, $USER->username);
        $posts = array();
        $recordset = $DB->get_recordset_sql($sql, $params, $from, $perpage);
        foreach ($recordset as $record) {
            $posts[] = new static(0, $record);
        }
        $recordset->close();
     
        return $posts;

    }

    public static function approve($id) {
        global $DB, $USER;

        // Get the block instance id of the post.
        $post = new static($id);
        if (empty($post)) {
            return;
        }

        // Get the block instance config for the post.
        $blockinstance = $DB->get_record('block_instances', array('id' => $post->get('instanceid')), '*', MUST_EXIST);
        $blockconfig = unserialize(base64_decode($blockinstance->configdata));
        
        // Check if user is the approver for this block.
        $approver = isset($blockconfig->approver) ? $blockconfig->approver : 0;
        if ($USER->username == $approver) {
            // Approve the post.
            $post->set('approved', 1);
            $post->update();

            // Notify recipient.
            post::send_notification($post);
        }
    }

    protected static function send_notification($post) {
        global $DB, $PAGE, $OUTPUT;

        $courseid = get_courseid_by_block_instance($post->get('instanceid'));
        $coursecontext = context_course::instance($courseid);

        $postexporter = new \block_appreciation\external\post_exporter($post, [
                'context' => $coursecontext,
                'isapprover' => false,
        ]);
        $output = $PAGE->get_renderer('core');
        $data = $postexporter->export($output);

        // Not all of these variables are used in the default string but are made available to support custom subjects.
        $site = get_site();
        $a = (object) [
            'creatorfullname' => format_string($data->creatorfullname),
            'sitefullname' => format_string($site->fullname),
            'siteshortname' => format_string($site->shortname),
        ];
        $postsubject = html_to_text(get_string('notification:subject', 'block_appreciation', $a), 0);

        $recipient = \core_user::get_user_by_username($data->recipient);
        $recipient = minimise_recipient_record($recipient);
        $userfrom = \core_user::get_noreply_user();
        $userfrom->customheaders = $post::get_message_headers($data, $recipient, $a);

        $eventdata = new \core\message\message();
        $eventdata->courseid            = $courseid;
        $eventdata->component           = 'block_appreciation';
        $eventdata->name                = 'notifications';
        $eventdata->userfrom            = $userfrom;
        $eventdata->userto              = $recipient;
        $eventdata->subject             = $postsubject;
        $eventdata->fullmessage         = $data->messageplain;
        $eventdata->fullmessageformat   = FORMAT_PLAIN;
        $fullmessagehtml                = $OUTPUT->render_from_template('block_appreciation/notification_html', array('posts' => $data));
        $eventdata->fullmessagehtml     = $fullmessagehtml;
        $eventdata->notification        = 1;
        $eventdata->smallmessage        = $postsubject;

        $contexturl = new \moodle_url('/blocks/appreciation/view.php', ['id' => $data->id]);
        $eventdata->contexturl = $contexturl->out();
        $eventdata->contexturlname = get_string('pluginname', 'block_appreciation');

        $eventdata->customdata = [
            'postid' => $data->id,
            'notificationiconurl' => $data->creatorphoto->out(false),
        ];

        $post->set('notified', 1);
        $post->update();

        return message_send($eventdata);
    }


    public static function soft_delete($id) {
        global $DB, $USER;

        // Get the block instance id of the post.
        $post = new static($id);
        if (empty($post)) {
            return;
        }

        // Check if user is the creator.
        if ($USER->username == $post->get('creator')) {
            // Approve the post.
            $post->set('deleted', 1);
            $post->update();
            return;
        }

        // Get the block instance config for the post.
        $blockinstance = $DB->get_record('block_instances', array('id' => $post->get('instanceid')), '*', MUST_EXIST);
        $blockconfig = unserialize(base64_decode($blockinstance->configdata));
        
        // Check if user is the approver for this block.
        $approver = isset($blockconfig->approver) ? $blockconfig->approver : 0;
        if ($USER->username == $approver) {
            // Approve the post.
            $post->set('deleted', 1);
            $post->update();
        }

        return;
    }


    /**
     * Get the list of message headers.
     *
     * @param   \stdClass   $data. The exported post data.
     * @param   \stdClass   $recipient. User record.
     * @param   \stdClass   $a The list of strings for this post.
     * @return  array
     */
    protected static function get_message_headers($data, $recipient, $a) {
        $viewurl = new \moodle_url('/blocks/appreciation/view.php', array('post' => $data->id));
        $headers = [
            // Headers to make emails easier to track.
            'List-Id: ' . generate_email_messageid($data->id .'_blockappreciation'),
            'List-Help: ' . $viewurl->out(),
            'Message-ID: ' . generate_email_messageid(hash('sha256', 'blockappreciation_' . $data->creator . '_' . $data->recipient . '_' . $data->id)),

            // Headers to help prevent auto-responders.
            'Precedence: Bulk',
            'X-Auto-Response-Suppress: All',
            'Auto-Submitted: auto-generated',
        ];
        return $headers;
    }


}
