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

define(['jquery', 'core/log', 'core/ajax','core/templates', 
        'core/str', 'core/modal_factory', 'core/modal_events' ], 
        function($, Log, Ajax, Templates, Str, ModalFactory, ModalEvents) {    
    'use strict';

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
        self.component = 'block_appreciation';

        self.modals = {
            VIEWUSERS: null,
        };
        self.templates = {
            VIEWUSERS: 'block_appreciation/post_like_users',
        };

        // Get some strings for future use.
        self.strings = {}
        Str.get_strings([
            {key: 'list:likes', component: self.component},
        ]).then(function(s) {
            self.strings.continueintersection = s[0];
        });
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


        self.rootel.on('click touchstart', '.heart', function() {
            var heart = $(this);

            if (heart.hasClass('is_animating')) {
                return;
            }

            if (heart.hasClass('is_selected')) {
                heart.removeClass('is_selected');
                // Delete the like.
                self.unlike(heart);

            } else {
                heart.toggleClass('is_animating');
            }
        });

        self.rootel.on('animationend', '.heart', function() {
            var heart = $(this);
            heart.toggleClass('is_animating');
            if (!heart.hasClass('is_selected')) {
                heart.addClass('is_selected');
                // Insert the like.
                self.like(heart);
            }
        });

        // Handle get like users click.
        self.rootel.on('click', '.post .action-getlikeusers', function(e) {
            e.preventDefault();
            var button = $(this);
            self.getLikeUsers(button);
        });

        // Preload the modals and templates.
        var preloads = [];
        preloads.push(self.loadModal('VIEWUSERS', 'Post like users', '', ModalFactory.types.DEFAULT));
        preloads.push(self.loadTemplate('VIEWUSERS'));
        $.when.apply($, preloads).then(function() {
            self.rootel.addClass('preloads-completed');
        })

        // set up infinite scroll
        if(typeof InfiniteScroll != 'undefined') {
          var infScroll = new InfiniteScroll( '.bapp-list', {
            // options
            path: '.next',
            append: '.post',
            history: false,
            status: '.page-load-status',
          });
        }

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

    /**
     * Like a post.
     *
     * @method approve
     */
    Content.prototype.like = function (heart) {
        var self = this;

        var post = heart.closest('.post');
        var id = post.data('id');
      
        Ajax.call([{
            methodname: 'block_appreciation_like_post',
            args: { id: id },
            done: function(response) {
                Log.debug('block_appreciation/content: Like successful.');
            },
            fail: function(reason) {
                Log.error('block_appreciation/content: Failed to like the post');
                Log.debug(reason);
            }
        }]);
    };


    /**
     * Unlike a post.
     *
     * @method approve
     */
    Content.prototype.unlike = function (heart) {
        var self = this;

        var post = heart.closest('.post');
        var id = post.data('id');
      
        Ajax.call([{
            methodname: 'block_appreciation_unlike_post',
            args: { id: id },
            done: function(response) {
                Log.debug('block_appreciation/content: Unlike successful.');
            },
            fail: function(reason) {
                Log.error('block_appreciation/content: Failed to unlike the post');
                Log.debug(reason);
            }
        }]);
    };

    /**
     * View a list of users that have liked a post.
     *
     * @method
     */
    Content.prototype.getLikeUsers = function (button) {
        var self = this;

        var post = button.closest('.post');
        var id = post.data('id');

        if (self.modals.VIEWUSERS) {
            self.modals.VIEWUSERS.getModal().addClass('modal-xl');
            self.modals.VIEWUSERS.setBody('<div style="font-style:italic;">... Fetching user list ...<div class="loader" style="display:block;"><div class="circle spin"></div></div></div>');
            self.modals.VIEWUSERS.show();
            Ajax.call([{
                methodname: 'block_appreciation_get_like_users',
                args: { id: id },
                done: function(response) {
                    var count = Object.keys(response['userslist']).length;
                    self.modals.VIEWUSERS.setTitle('Likes');
                    Templates.render(self.templates.VIEWUSERS, response)
                        .done(function(html) {
                            self.modals.VIEWUSERS.setBody(html);
                        })
                        .fail(function(reason) {
                            Log.debug(reason);
                            return "Failed to render post like users."
                        });
                },
                fail: function(reason) {
                    Log.error('block_appreciation/list: unable to get post like users.');
                    Log.debug(reason);
                }
            }]);
        }
    };

    /**
     * Helper used to preload a modal
     *
     * @method loadModal
     * @param {string} modalkey The property of the global modals variable
     * @param {string} title The title of the modal
     * @param {string} title The button text of the modal
     * @return {object} jQuery promise
     */
    Content.prototype.loadModal = function (modalkey, title, buttontext, type) {
        var self = this;
        return ModalFactory.create({type: type}).then(function(modal) {
            modal.setTitle(title);
            if (buttontext) {
                modal.setSaveButtonText(buttontext);
            }
            self.modals[modalkey] = modal;
            // Preload backgrop.
            modal.getBackdrop();
            modal.getRoot().addClass('modal-' + modalkey);
        });
    }

    /**
     * Helper used to preload a template
     *
     * @method loadModal
     * @param {string} templatekey The property of the global templates variable
     * @return {object} jQuery promise
     */
    Content.prototype.loadTemplate = function (templatekey) {
        var self = this;
        return Templates.render(self.templates[templatekey], {});
    }


    return {
        init: init
    };
});