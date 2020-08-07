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
 * Provides the {@link block_appreciation\persistents\thankyou} class.
 *
 * @package   block_appreciation
 * @copyright 2020 Michael Vangelovski
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_appreciation\persistents;

defined('MOODLE_INTERNAL') || die();

use \core\persistent;
use \core_user;
use \context_user;
use \context_course;

/**
 * Persistent model representing a single announcement post.
 */
class thankyou extends persistent {

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
    * Saves the record to the database.
    * 
    * @param stdClass $data. 
    * @return int|bool $id. ID of announcement or false if failed to create.
    */
    /*public static function save_from_data($data) {
        global $DB, $USER;

        $edit = false;
        if ($id > 0) {
            // Make sure the record actually exists.
            if (!static::record_exists($id)) {
                return false;
            }
            $edit = true;
        }

        // Before creating anything, validate the audiences.
        $tags = json_decode($data->audiencesjson);
        if (!static::is_audiences_valid($tags)) {
            return false;
        }

        // Load or create new instance, depending on $id.
        $announcement = new static($id);

        if ($edit) {
            // Editing an announcement.
            // Should the announcement be resent in the next digest.
            if ($data->remail) {
                $announcement->set('mailed', 0);
            }
            // If the announcement is edited by the creator then update the impersonate field.
            // If the impersonated user edits the announcement, do not change the impersonate field.
            if ($announcement->get('authorusername') == $USER->username) {
                $announcement->set('impersonate', $data->impersonate);
            }
        } else {
            // New announcement, set author to current user.
            $announcement->set('authorusername', $USER->username);
            // Set the impersonated user. When editing a different set of rules apply for this field.
            $announcement->set('impersonate', $data->impersonate);
        }

        // An author can't impersonate themselves.
        if ($announcement->get('authorusername') == $data->impersonate) {
            $announcement->set('impersonate', '');
        }

        // Check that the author can actually impersonate the selected user.
        if ($data->impersonate) {
            $impersonator = core_user::get_user_by_username($announcement->get('authorusername'));
            if (!can_impersonate_user($impersonator, $data->impersonate)) {
                $announcement->set('impersonate', '');
            }
        }

        // Set/update the data.
        $announcement->set('timeedited', time());
        $announcement->set('subject', $data->subject);
        $announcement->set('message', '');
        $announcement->set('messageformat', $data->messageformat);
        $announcement->set('messagetrust', $data->messagetrust);
        $announcement->set('timestart', $data->timestart);
        $announcement->set('timeend', $data->timeend);
        $announcement->set('audiencesjson', $data->audiencesjson);
        $announcement->set('forcesend', $data->forcesend);
        $announcement->set('attachment', 0);
        $announcement->set('notified', 0);
        // No moderation set initially. Moderation requirements processed below.
        $announcement->set('modrequired', ANN_MOD_REQUIRED_NO);
        $announcement->set('modstatus', ANN_MOD_STATUS_PENDING);
        // Set savecomplete flag to false until all audiences and users are saved so 
        // that the plugin does not attempt to mail the plugin until everything is saved.
        $announcement->set('savecomplete', 0);

        // Update the persistent with the added details.
        $announcement->save();
        $id = $announcement->get('id');

        // Store message files to a permanent file area.
        $context = \context_system::instance();
        $message = file_save_draft_area_files(
            $data->itemid, 
            $context->id, 
            'block_appreciation', 
            'announcement', 
            $id, 
            form_post::editor_options(null), 
            $data->message
        );
        $announcement->set('message', $message);

        // Store attachments to a permanent file area.
        $info = file_get_draft_area_info($data->attachments);
        $attachment = ($info['filecount']>0) ? '1' : '';
        $announcement->set('attachment', $attachment);
        file_save_draft_area_files(
            $data->attachments, 
            $context->id, 
            'block_appreciation', 
            'attachment', 
            $id, 
            form_post::attachment_options()
        );
        $announcement->update();

        // Determine whether announcement needs moderation.
        moderation::setup_moderation($id, $tags);

        // Save the audiences.
        static::save_audiences($id, $tags);

        // Finally, set savecomplete to true, indicating that all aspects of the 
        // announcement have been fully saved.
        // Update single field rather than using the persistent as the announcement
        // data could have been altered for moderation requirements.
        $DB->set_field('ann_posts', 'savecomplete', 1, array('id' => $id));

        return $id;
    }*/



    public static function soft_delete($id) {
        global $DB, $USER;

        /*// Load the announcement.
        $announcement = new static($id);

        // Soft delete the announcement.
        $announcement->set('deleted', 1);
        $announcement->update();

        // Hard delete the audiences.
        $DB->delete_records(static::TABLE_POSTS_USERS_AUDIENCES, array('postid' => $id));
        $DB->delete_records(static::TABLE_POSTS_USERS, array('postid' => $id));
        $DB->delete_records(static::TABLE_POSTS_AUDIENCES_CONDITIONS, array('postid' => $id));
        $DB->delete_records(static::TABLE_POSTS_AUDIENCES, array('postid' => $id));

        //Hard delete notifications.
        $customdata = '{"postid":"' . $id . '"%';
        $sql = "DELETE FROM {notifications}
                 WHERE component = 'block_appreciation'
                   AND (eventtype = 'notifications' OR eventtype = 'moderationmail')
                   AND customdata LIKE '" . $customdata . "'";
        $DB->execute($sql);
        
        return $id;*/
    }


}
