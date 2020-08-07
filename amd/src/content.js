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
 * @package   block_appreciation
 * @copyright 2020 Michael Vangelovski
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery', 'core/log', 'core/ajax'], function($, Log, Ajax) {
    "use strict";

    /**
     * Click handler to toggle auditing mode.
     * @param {Object} button The jquery object.
     * @param int the user id.
     */
    function toggleAuditingMode(button, userid) {
        button.addClass('loading');
        // Save toggle state as a preference.
        var value = button.hasClass('auditing-is-off') ? 1 : 0;
        Log.debug('block_appreciation: Setting ' + value);
        var preferences = [{
            'name': 'block_appreciation_auditingmode',
            'value': value,
            'userid': userid
        }];
        Ajax.call([{
            methodname: 'core_user_set_user_preferences',
            args: { preferences: preferences },
            done: function(response) {
                location.reload();
            }
        }]);
    }

    return {
        init: function(userid) {

            if (!userid) {
                Log.error('block_appreciation: userid not provided!');
                return;
            }

            var rootel = $('.block_appreciation').first();
            if (!rootel.length) {
                Log.error('block_appreciation: root element not found!');
                return;
            }

            // Handle turn off audit mode button.
            rootel.on('click', '.auditing-icon', function(e) {
                e.preventDefault();
                var button = $(this);
                toggleAuditingMode(button, userid);
            });
        }
    };
});