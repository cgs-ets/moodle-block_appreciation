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
 * Provides the block_appreciation/post module
 *
 * @package   block_appreciation
 * @category  output
 * @copyright 2020 Michael Vangelovski
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * @module block_appreciation/post
 */
define(['jquery', 'block_appreciation/recipientselector', 'core/log', 'core/templates', 'core/form-autocomplete'], 
    function($, RecipientSelector, Log, Templates, AutoComplete) {    
    'use strict';

    /**
     * Initializes the post component.
     */
    function init(instanceid) {
        Log.debug('block_appreciation/post: initializing');

        var rootel = $('#page-blocks-appreciation-post');

        if (!rootel.length) {
            Log.error('block_appreciation/post: #page-blocks-appreciation-post not found!');
            return;
        }

        var post = new Post(rootel, instanceid);
        post.main();
    }

    /**
     * The constructor
     *
     * @constructor
     * @param {jQuery} rootel
     */
    function Post(rootel, instanceid) {
        var self = this;
        self.rootel = rootel;
        self.instanceid = instanceid;
    }

    /**
     * Run the Audience Selector.
     *
     */
   Post.prototype.main = function () {
        var self = this;

        // Initialise the recipient selector.
        RecipientSelector.init(self.instanceid);

    };

    return {
        init: init
    };
});