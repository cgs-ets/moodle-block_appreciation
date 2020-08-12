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
     * Initializes the component.
     */
    function init(instanceid) {
        Log.debug('block_appreciation/content: initializing');

        var rootel = $('[data-region="block_appreciation-instance-' + instanceid + '"]').first();

        if (!rootel.length) {
            Log.error('block_appreciation/control: wrapping region not found!');
            return;
        }

        var content = new Content(rootel, instanceid);
        content.main();
    }

    /**
     * The constructor
     *
     * @constructor
     * @param {jQuery} rootel
     */
    function Content(rootel, instanceid) {
        var self = this;
        self.rootel = rootel;
        self.instanceid = instanceid;
    }

    /**
     * Run the Audience Selector.
     *
     */
    Content.prototype.main = function () {
        var self = this;

        // Handle approve click.
        self.rootel.on('click', '.post .action-approve', function(e) {
            e.preventDefault();
            var button = $(this);
            self.approve(button);
        });

        // Handle delete click.
        self.rootel.on('click', '.post .action-delete', function(e) {
            e.preventDefault();
            var button = $(this);
            self.delete(button);
        });
    };

    /**
     * Approve a post.
     *
     * @method approve
     */
    Content.prototype.approve = function (button) {
        var self = this;

        var post = button.closest('.post');
        var id = post.data('id');
        post.addClass('submitting');
      
        Ajax.call([{
            methodname: 'block_appreciation_approve_post',
            args: { id: id },
            done: function(response) {
                post.removeClass('submitting');
                post.removeClass('unapproved');
            },
            fail: function(reason) {
                post.removeClass('submitting');
                Log.error('block_appreciation/content: failed to approve the post');
                Log.debug(reason);
            }
        }]);
    };


    /**
     * Delete a post.
     *
     * @method delete
     */
    Content.prototype.delete = function (button) {
        var self = this;

        var post = button.closest('.post');
        var id = post.data('id');
        post.addClass('submitting');
      
        Ajax.call([{
            methodname: 'block_appreciation_delete_post',
            args: { id: id },
            done: function(response) {
                post.removeClass('submitting');
                post.addClass('removing');
                post.fadeOut(1000, function() {
                    post.remove();
                });
            },
            fail: function(reason) {
                post.removeClass('submitting');
                Log.error('block_appreciation/content: failed to delete the post');
                Log.debug(reason);
            }
        }]);
    };


    return {
        init: init
    };
});