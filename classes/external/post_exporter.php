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
 * Provides {@link block_appreciation\external\post_exporter} class.
 *
 * @package   block_appreciation
 * @copyright 2020 Michael Vangelovski
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_appreciation\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/blocks/appreciation/locallib.php');
use core\external\persistent_exporter;
use renderer_base;
use \block_appreciation\persistents\post;


/**
 * Exporter of a single post
 */
class post_exporter extends persistent_exporter {

    /**
    * Returns the specific class the persistent should be an instance of.
    *
    * @return string
    */
    protected static function define_class() {
        return post::class; 
    }

     /**
     * Returns a list of objects that are related.
     *
     * We need the context to be used when formatting the message field.
     *
     * @return array
     */
    protected static function define_related() {
        return [
            'context' => 'context',
            'isapprover' => 'bool',
        ];
    }

    /**
	 * Return the list of additional properties.
	 * @return array
	 */
	protected static function define_other_properties() {
	    return [
	        'creatorphoto' => [
	        	'type' => PARAM_RAW,
	        ],
	        'creatorphototokenised' => [
	        	'type' => PARAM_RAW,
	        ],
	        'creatorfullname' => [
	        	'type' => PARAM_RAW,
	        ],
	        'creatorurl' => [
	        	'type' => PARAM_RAW,
	        ],
	        'recipientphoto' => [
	        	'type' => PARAM_RAW,
	        ],
	        'recipientfullname' => [
	        	'type' => PARAM_RAW,
	        ],
	        'recipienturl' => [
	        	'type' => PARAM_RAW,
	        ],
	        'readabletime' => [
	        	'type' => PARAM_RAW,
	        ],
	        'iscreator' => [
	        	'type' => PARAM_BOOL,
	        ],
	        'isrecipient' => [
	        	'type' => PARAM_BOOL,
	        ],
	        'iscreatororrecipient' => [
	        	'type' => PARAM_BOOL,
	        ],
            "isapproved" => [
                'type' => PARAM_BOOL,
                'default' => 0,
            ],
	        'messagetokenized' => [
	        	'type' => PARAM_RAW,
	        ],
	        'messageplain' => [
	        	'type' => PARAM_RAW,
	        ],
	        'viewurl' => [
	        	'type' => PARAM_RAW,
	        ],
	        'numlikes' => [
	        	'type' => PARAM_INT,
	        ],
            "doilike" => [
                'type' => PARAM_BOOL,
                'default' => 0,
            ],
	    ];
	}

	/**
	 * Get the additional values to inject while exporting.
	 *
	 * @param renderer_base $output The renderer.
	 * @return array Keys are the property names, values are their values.
	 */
	protected function get_other_values(renderer_base $output) {
		global $USER, $DB, $OUTPUT, $PAGE;

		// Check if user is the creator of this post.
		$iscreator = 0;
		if ($this->data->creator == $USER->username || $this->related['isapprover']) {
			$iscreator = 1;
		}


	    // Is the user the recipient of this post.
	    $isrecipient = false;
	    if ($this->data->recipient == $USER->username) {
	    	$isrecipient = true;
	    }

	    // Is creator or recipient, because mustache does not allow logic.
	    $iscreatororrecipient = ($isrecipient || $iscreator);

	    // Does current user like this post?
	    $doilike = false;
	    if (!$iscreatororrecipient) {
	    	$doilike = $DB->record_exists('block_appreciation_likes', array('postid' => $this->data->id, 'username' => $USER->username));
	    }

	   	// Number of likes for this post. Only relevant to recipient.
	   	$numlikes = 0;
	   	if ($iscreatororrecipient) {
	    	$numlikes = $DB->count_records('block_appreciation_likes', array('postid' => $this->data->id));
	    }

		// Author meta.
        $creator = $DB->get_record('user', array('username'=>$this->data->creator));
        profile_load_data($creator);
        $creatorphoto = new \moodle_url('/user/pix.php/'.$creator->id.'/f2.jpg');
        $creatorfullname = fullname($creator);
        $creatorurl = new \moodle_url('/user/profile.php', array('id' => $creator->id));
        $creatorphototokenised = $OUTPUT->user_picture($creator, array('size' => 35, 'includetoken' => true));

        // Recipient meta.
        $recipient = $DB->get_record('user', array('username'=>$this->data->recipient));
        profile_load_data($recipient);
        $recipientphoto = new \moodle_url('/user/pix.php/'.$recipient->id.'/f2.jpg');
        $recipientfullname = fullname($recipient);
        $recipienturl = new \moodle_url('/user/profile.php', array('id' => $recipient->id));

        // Readable time.
      	$readabletime = date('j M Y, g:ia', $this->data->timecreated);

      	// Approval status.
	   	$isapproved = false;
	    if ($this->data->approved == 1) {
	    	$isapproved = true;
	    }

	    $messagetokenized = file_rewrite_pluginfile_urls($this->data->message, 'pluginfile.php', $this->related['context']->id,
	        		'block_appreciation', 'post', $this->data->id, ['includetoken' => true]);
	    $messageplain = trim(html_to_text(format_text_email($messagetokenized, FORMAT_PLAIN)));

	    $viewurl = new \moodle_url('/blocks/appreciation/view.php', array('id' => $this->data->id));
    	$viewurl = $viewurl->out();

	    return [
	        'creatorphoto' => $creatorphoto,
	        'creatorphototokenised' => $creatorphototokenised,
	        'creatorfullname' => $creatorfullname,
	        'creatorurl' => $creatorurl->out(false),
	        'recipientphoto' => $recipientphoto,
	        'recipientfullname' => $recipientfullname,
	        'recipienturl' => $recipienturl->out(false),
	        'readabletime' => $readabletime,
	        'iscreator' => $iscreator,
	        'isapproved' => $isapproved,
	        'isrecipient' => $isrecipient,
	        'iscreatororrecipient' => $iscreatororrecipient,
	        'messagetokenized' => $messagetokenized,
	        'messageplain' => $messageplain,
	        'viewurl' => $viewurl,
	        'numlikes' => $numlikes,
	        'doilike' => $doilike,
	    ];
	}

	
    /**
     * Get the formatting parameters for the message.
     *
     * @return array
     */
    protected function get_format_parameters_for_message() {
        return [
        	'context' => $this->related['context'],
            'component' => 'block_appreciation',
            'filearea' => 'post',
            'itemid' => $this->data->id,
            'options' => \block_appreciation\forms\form_post::editor_options(),
        ];
    }

}