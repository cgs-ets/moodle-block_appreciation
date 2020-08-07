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
 * Module for recipient autocomplete field.
 *
 * @package   block_appreciation
 * @category  output
 * @copyright 2020 Michael Vangelovski
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * @module block_appreciation/recipientselector
 */
define(['jquery', 'core/log', 'core/ajax', 'core/templates', 'core/str'], function($, Log, Ajax, Templates, Str) {
    'use strict';

    /**
     * Initializes the recipientselector component.
     */
    function init(instanceid) {
        Log.debug('block_appreciation/recipientselector: initializing the recipient-selector component');

        var rootel = $('.recipient-selector').first();

        if (!rootel.length) {
            Log.error('block_appreciation/recipientselector: recipient-selector root element not found!');
            return;
        }

        var recipientselector = new RecipientSelector(rootel, instanceid);
        recipientselector.main();
    }

    /**
     * The recipient selector constructor
     *
     * @constructor
     * @param {jQuery} rootel
     */
    function RecipientSelector(rootel, instanceid) {
        var self = this;
        self.rootel = rootel;
        self.instanceid = instanceid;
        self.component = 'block_appreciation';
        self.strings = {}
        Str.get_strings([
            {key: 'postform:recipientnoselection', component: self.component},
        ]).then(function(s) {
            self.strings.noselectionstr = s[0];
        });
    }

    /**
     * Run the Recipient Selector.
     *
     */
   RecipientSelector.prototype.main = function () {
        var self = this;

        // Render existing selection (if editing announcement).
        self.render();

        // Handle search.
        var keytimer;
        self.rootel.on('keyup', '.recipient-autocomplete', function(e) {
            clearTimeout(keytimer);
            var autocomplete = $(this);
            keytimer = setTimeout(function () {
                self.search(autocomplete);
            }, 500);
        });

        // Handle search result click.
        self.rootel.on('click', '.recipient-result', function(e) {
            e.preventDefault();
            var tag = $(this);
            self.add(tag);
        });

        // Handle tag click.
        self.rootel.on('click', '.recipient-tag', function(e) {
            e.preventDefault();
            var tag = $(this);
            self.remove(tag);
        });

        // Handle entering the autocomplete field.
        self.rootel.on('focus', '.recipient-autocomplete', function(e) {
            self.refocus();
        });

        // Handle leaving the autocomplete field.
        $(document).on('click', function (e) {
            var target = $(e.target);
            if (target.is('.recipient-autocomplete') || target.is('.recipient-result')) {
                return;
            }
            self.unfocus();
        });
    };


    /**
     * Add a selection.
     *
     * @method
     */
    RecipientSelector.prototype.add = function (tag) {
        var self = this;
        self.unfocus();

        var input = $('input[name="recipient"]');

        // Encode to json and add tag to hidden input.
        var obj = {
            username: tag.data('username'),
            photourl: tag.find('img').attr('src'),
            fullname: tag.find('span').text()
        };
        input.val(JSON.stringify(obj));

        self.render();
    };

    /**
     * Remove a selection.
     *
     * @method
     */
    RecipientSelector.prototype.remove = function (tag) {
        var self = this;

        var input = $('input[name="recipient"]');
        input.val('');

        self.render();
    };

    /**
     * Render the selection.
     *
     * @method
     */
    RecipientSelector.prototype.render = function () {
        var self = this;
        var input = $('input[name="recipient"]');

        if (input.val() == '') {
            // Remove tag.
            self.rootel.find('.recipient-selection').html(self.strings.noselectionstr);
            return;
        }

        var json = input.val();
        if(json) {
            var tag = JSON.parse(json);

            console.log(tag);
            // Render the tag from a template.
            Templates.render('block_appreciation/recipient_selector_tag', tag)
                .then(function(html) {
                    self.rootel.find('.recipient-selection').html(html);
                }).fail(function(reason) {
                    Log.error(reason);
                });
        }
    };

    /**
     * Search.
     *
     * @method
     */
    RecipientSelector.prototype.search = function (searchel) {
        var self = this;
        self.hasresults = false;

        if (searchel.val() == '') {
            return;
        }

        Ajax.call([{
            methodname: 'block_appreciation_get_recipient_users',
            args: { 
                instanceid: self.instanceid,
                query: searchel.val() 
            },
            done: function(response) {
                if (response.length) {
                    self.hasresults = true;
                    // Render the results.
                    Templates.render('block_appreciation/recipient_selector_results', { users : response }) 
                        .then(function(html) {
                            var results = self.rootel.find('.recipient-results');
                            results.html(html);
                            results.addClass('active');
                        }).fail(function(reason) {
                            Log.error(reason);
                        });
                } else {
                    self.rootel.find('.recipient-results').removeClass('active');
                }
            },
            fail: function(reason) {
                Log.error('block_appreciation/recipientselector: failed to search.');
                Log.debug(reason);
            }
        }]);
    };

    /**
     * Leave the autocomplete field.
     *
     * @method
     */
    RecipientSelector.prototype.unfocus = function () {
        var self = this;
        self.rootel.find('.recipient-results').removeClass('active');
    };

    /**
     * Leave the autocomplete field.
     *
     * @method
     */
    RecipientSelector.prototype.refocus = function () {
        var self = this;
        if (self.hasresults) {
            self.rootel.find('.recipient-results').addClass('active');
        }
    };

    return {
        init: init
    };
});