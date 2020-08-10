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

require_once($CFG->dirroot . '/blocks/appreciation/locallib.php');
use \block_appreciation\persistents\thankyou;
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
     * Get appreciation posts.
     *
     * @param string $instanceid. The block instance id.
     * @param int $page.
     * @return array.
     */
    public static function get_for_user($instanceid, $isapprover = 0, $page = 0) {
        global $DB, $USER;

        $perpage = APPRECIATION_PERPAGE;
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
