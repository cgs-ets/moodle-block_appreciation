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

        /** Related tables. */
    const TABLE_LIKES = 'block_appreciation_likes';

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
            "messageformat" => [
                'type' => PARAM_INT,
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
    public static function get_by_filter($instanceid, $filter = '', $filterval = '', $page = 0, $perpage = 0) {
        global $DB, $USER;

        $posts = array();

        switch ($filter) {
            case 'unapproved':
                $posts = static::get_for_approval($instanceid, $page);
                break;
            case 'byme':
                $posts = static::get_by_me($instanceid, $page);
                break;
            case 'forme':
                $posts = static::get_for_me($instanceid, $page);
                break;
            case 'foruser':
                $posts = static::get_for_user($instanceid, $filterval, $page);
                break;
            case 'thisweek':
                $posts = static::get_for_this_week($instanceid, $page);
                break;
            default:
                $posts = static::get_all($instanceid, $page);
        }

        return $posts;
    }

    

    /**
     * Get appreciation posts.
     *
     * @param string $instanceid. The block instance id.
     * @param int $page.
     * @return array.
     */
    public static function get_all($instanceid, $page = 0, $perpage = 0) {
        global $DB, $USER;

        // Get block config.
        $blockinstance = $DB->get_record('block_instances', array('id' => $instanceid), '*', MUST_EXIST);
        $blockconfig = unserialize(base64_decode($blockinstance->configdata));

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

        // If not approver.
        if (!isset($blockconfig) || !isset($blockconfig->approver) || $USER->username != $blockconfig->approver) {
            $sql .= " AND (approved = 1 OR creator = ?) ";
            $params[] = $USER->username;
        }

        $sql .= " ORDER BY timecreated DESC";
        
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

        // Get block config.
        $blockinstance = $DB->get_record('block_instances', array('id' => $instanceid), '*', MUST_EXIST);
        $blockconfig = unserialize(base64_decode($blockinstance->configdata));

        // Only approver can get this list.
        if (!isset($blockconfig) || !isset($blockconfig->approver) || $blockconfig->approver != $USER->username) {
            return;
        }

        $perpage = APPRECIATION_PERPAGE;
        $from = $perpage*$page;

        $sql = "SELECT *
                   FROM {block_appreciation_posts}
                  WHERE instanceid = ? 
                    AND deleted = 0
                    AND approved = 0
               ORDER BY timecreated DESC";

        $params = array($instanceid);
        $posts = array();
        $recordset = $DB->get_recordset_sql($sql, $params, $from, $perpage);
        foreach ($recordset as $record) {
            $posts[] = new static(0, $record);
        }
        $recordset->close();
     
        return $posts;

    }


    /**
     * Get appreciation posts made by me.
     *
     * @param string $instanceid. The block instance id.
     * @param int $page.
     * @return array.
     */
    public static function get_by_me($instanceid, $page = 0) {
        global $DB, $USER;

        $perpage = APPRECIATION_PERPAGE;
        $from = $perpage*$page;

        $sql = "SELECT *
                   FROM {block_appreciation_posts}
                  WHERE instanceid = ? 
                    AND deleted = 0
                    AND creator = ?
               ORDER BY timecreated DESC";

        $params = array($instanceid, $USER->username);
        $posts = array();
        $recordset = $DB->get_recordset_sql($sql, $params, $from, $perpage);
        foreach ($recordset as $record) {
            $posts[] = new static(0, $record);
        }
        $recordset->close();
     
        return $posts;

    }

    /**
     * Get appreciation posts made by me.
     *
     * @param string $instanceid. The block instance id.
     * @param int $page.
     * @return array.
     */
    public static function get_for_me($instanceid, $page = 0) {
        global $DB, $USER;

        $perpage = APPRECIATION_PERPAGE;
        $from = $perpage*$page;

        $sql = "SELECT *
                   FROM {block_appreciation_posts}
                  WHERE instanceid = ? 
                    AND deleted = 0
                    AND approved = 1
                    AND recipient = ?
               ORDER BY timecreated DESC";

        $params = array($instanceid, $USER->username);
        $posts = array();
        $recordset = $DB->get_recordset_sql($sql, $params, $from, $perpage);
        foreach ($recordset as $record) {
            $posts[] = new static(0, $record);
        }
        $recordset->close();
     
        return $posts;

    }

    /**
     * Get appreciation posts made by me.
     *
     * @param string $instanceid. The block instance id.
     * @param string $username.
     * @param int $page.
     * @return array.
     */
    public static function get_for_user($instanceid, $username, $page = 0) {
        global $DB;

        $perpage = APPRECIATION_PERPAGE;
        $from = $perpage*$page;

        $sql = "SELECT *
                   FROM {block_appreciation_posts}
                  WHERE instanceid = ? 
                    AND deleted = 0
                    AND approved = 1
                    AND recipient = ?
               ORDER BY timecreated DESC";

        $params = array($instanceid, $username);
        $posts = array();
        $recordset = $DB->get_recordset_sql($sql, $params, $from, $perpage);
        foreach ($recordset as $record) {
            $posts[] = new static(0, $record);
        }
        $recordset->close();
     
        return $posts;

    }

    /**
     * Get appreciation posts made this week.
     *
     * @param string $instanceid. The block instance id.
     * @param int $page.
     * @return array.
     */
    public static function get_for_this_week($instanceid, $page = 0) {
        global $DB, $USER;

        // Get block config.
        $blockinstance = $DB->get_record('block_instances', array('id' => $instanceid), '*', MUST_EXIST);
        $blockconfig = unserialize(base64_decode($blockinstance->configdata));

        // Determine the start of this week.
        $start = 0;
        if (empty($blockconfig->weekstartday)) {
            // Default is Sunday 00:00;
            $start = strtotime('last sunday midnight');
            if(date('l') == 'Sunday') {
                $start = strtotime('today midnight');
            }
        } else {
            $days = array(1 => 'Sunday', 2 => 'Monday', 3 => 'Tuesday', 4 => 'Wednesday', 5 => 'Thursday', 6 => 'Friday', 7 => 'Saturday');
            $start = strtotime('last ' . $days[$blockconfig->weekstartday] . ' ' . $blockconfig->weekstarttime);
            // If today is the start of the week, determine whether to look forward or back.
            if(date('l') == $days[$blockconfig->weekstartday]) {
                $start = strtotime('today ' . $days[$blockconfig->weekstartday] . ' ' . $blockconfig->weekstarttime);
                // If we have not reached the end of the week yet.
                if (time() < $start) {
                    $start = strtotime('last ' . $days[$blockconfig->weekstartday] . ' ' . $blockconfig->weekstarttime);
                }
            }
        }

        $perpage = APPRECIATION_PERPAGE;
        $from = $perpage*$page;

        $sql = "SELECT *
                   FROM {block_appreciation_posts}
                  WHERE instanceid = ? 
                    AND deleted = 0
                    AND approved = 1
                    AND timecreated >= ?
               ORDER BY timecreated DESC";

        $params = array($instanceid, $start);
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
            post::send_post_notification($post);
        }
    }

    protected static function send_post_notification($post) {
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
        $fullmessagehtml                = $OUTPUT->render_from_template('block_appreciation/notification_html', array(
            'post' => $data, 
            'subject' =>  $postsubject,
            'a' => $a,
        ));
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


    public static function like($postid) {
        global $DB, $USER;

        // Ensure the post exists.
        $post = new static($postid);
        if (empty($post)) {
            return;
        }

        // Check if like already exists.
        $doilike = $DB->record_exists('block_appreciation_likes', array('postid' => $postid, 'username' => $USER->username));
        if ($doilike) {
            return;
        }

        // Like the post.
        $time = time();
        $data = new \stdClass();
        $data->postid = $postid;
        $data->username = $USER->username;
        $data->timecreated = $time;
        $data->timemodified = $time;
        $id = $DB->insert_record('block_appreciation_likes', $data);

        static::send_like_notification($post, $id);

        return $id;

    }

    public static function unlike($postid) {
        global $DB, $USER;

        // Ensure the post exists.
        $post = new static($postid);
        if (empty($post)) {
            return;
        }

        // Unlike the post.
        $DB->delete_records('block_appreciation_likes', array('postid' => $postid, 'username' => $USER->username));

    }
    
    /**
    * Gets the users that liked the post.
    *
    * @param int $postid.
    * @return array.
    */
    public static function get_like_users($postid) {
        global $DB;
        // Fetch posts_users records
        $sql = "SELECT *
                  FROM {" . static::TABLE_LIKES . "}
                 WHERE postid = ?";
        $params = array($postid);
        $postusers = $DB->get_records_sql($sql, $params);
        // Convert to user records
        $users = array();
        foreach ($postusers as $postuser) {
            $users[] = $DB->get_record('user', array('username'=>$postuser->username));
        }
        return $users;
    }

    protected static function send_like_notification($post, $likeid) {
        global $DB, $PAGE, $OUTPUT;

        // Get the course data.
        $courseid = get_courseid_by_block_instance($post->get('instanceid'));
        $coursecontext = context_course::instance($courseid);

        // Export the post.
        $postexporter = new \block_appreciation\external\post_exporter($post, [
            'context' => $coursecontext,
            'isapprover' => false,
        ]);
        $output = $PAGE->get_renderer('core');
        $data = $postexporter->export($output);

        // Get the like data.
        $like = $DB->get_record('block_appreciation_likes', array('id'=>$likeid));
        if (empty($like)) {
            return;
        }

        // Not all of these variables are used in the default string but are made available to support custom subjects.
        $likeuser = \core_user::get_user_by_username($like->username);
        $site = get_site();
        $a = (object) [
            'likefullname' => format_string(fullname($likeuser)),
            'sitefullname' => format_string($site->fullname),
            'siteshortname' => format_string($site->shortname),
        ];
        $postsubject = html_to_text(get_string('notification_like:subject', 'block_appreciation', $a), 0);

        $recipient = \core_user::get_user_by_username($data->recipient);
        $recipient = minimise_recipient_record($recipient);
        $userfrom = \core_user::get_noreply_user();
        $viewurl = new \moodle_url('/blocks/appreciation/view.php', array('post' => $data->id));
        $userfrom->customheaders = [
            // Headers to make emails easier to track.
            'List-Id: ' . generate_email_messageid($likeid .'_blockappreciation'),
            'List-Help: ' . $viewurl->out(),
            'Message-ID: ' . generate_email_messageid(hash('sha256', 'blockappreciation_' . $like->username . '_' . $likeid)),

            // Headers to help prevent auto-responders.
            'Precedence: Bulk',
            'X-Auto-Response-Suppress: All',
            'Auto-Submitted: auto-generated',
        ];

        $eventdata = new \core\message\message();
        $eventdata->courseid            = $courseid;
        $eventdata->component           = 'block_appreciation';
        $eventdata->name                = 'likes';
        $eventdata->userfrom            = $userfrom;
        $eventdata->userto              = $recipient;
        $eventdata->subject             = $postsubject;
        $eventdata->fullmessage         = $postsubject;
        $eventdata->fullmessageformat   = FORMAT_PLAIN;
        $fullmessagehtml                = $OUTPUT->render_from_template('block_appreciation/notification_like_html', array(
            'post' => $data, 
            'subject' =>  $postsubject,
            'a' => $a,
        ));
        $eventdata->fullmessagehtml     = $fullmessagehtml;
        $eventdata->notification        = 1;
        $eventdata->smallmessage        = $postsubject;

        $contexturl = new \moodle_url('/blocks/appreciation/view.php', ['id' => $data->id]);
        $eventdata->contexturl = $contexturl->out();
        $eventdata->contexturlname = get_string('pluginname', 'block_appreciation');

        $recipientphoto = new \moodle_url('/user/pix.php/'.$recipient->id.'/f2.jpg');
        $eventdata->customdata = [
            'postid' => $data->id,
            'notificationiconurl' => $recipientphoto->out(false),
        ];

        $post->set('notified', 1);
        $post->update();

        // Send a like to the recipient.
        message_send($eventdata);

        // Send a like to the creator.
        $creator = \core_user::get_user_by_username($data->creator);
        $creator = minimise_recipient_record($creator);
        $eventdata->userto = $creator;

        $creatorphoto = new \moodle_url('/user/pix.php/'.$creator->id.'/f2.jpg');
        $eventdata->customdata = [
            'notificationiconurl' => $creatorphoto->out(false),
        ];
        message_send($eventdata);


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
