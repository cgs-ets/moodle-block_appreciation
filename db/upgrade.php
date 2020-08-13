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
 * Post installation and migration code.
 *
 * @package   block_appreciation
 * @copyright 2020 Michael Vangelovski
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_block_appreciation_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2020081300) {

        /**
         * xmldb_field 
         * @param string $name of field
         * @param int $type XMLDB_TYPE_INTEGER, XMLDB_TYPE_NUMBER, XMLDB_TYPE_CHAR, XMLDB_TYPE_TEXT, XMLDB_TYPE_BINARY
         * @param string $precision length for integers and chars, two-comma separated numbers for numbers
         * @param bool $unsigned XMLDB_UNSIGNED or null (or false)
         * @param bool $notnull XMLDB_NOTNULL or null (or false)
         * @param bool $sequence XMLDB_SEQUENCE or null (or false)
         * @param mixed $default meaningful default o null (or false)
         * @param xmldb_object $previous
         */
        $id = new xmldb_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $postid = new xmldb_field('postid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0, null, 'id');
        $username = new xmldb_field('username', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null, null, 'postid');
        $timecreated = new xmldb_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0, null, 'username');
        $timemodified = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0, null, 'timecreated');
        $primarykey = new xmldb_key('primary', XMLDB_KEY_PRIMARY, array('id'), null, null);

        $table = new xmldb_table('block_appreciation_likes');
        $table->addField($id);
        $table->addField($postid);
        $table->addField($username);
        $table->addField($timecreated);
        $table->addField($timemodified);
        $table->addKey($primarykey);

        // Add a new table for audience cc's.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

    }

    return true;

}
