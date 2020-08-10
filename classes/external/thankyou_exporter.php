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
 * Provides {@link block_appreciation\external\thankyou_exporter} class.
 *
 * @package   block_appreciation
 * @copyright 2020 Michael Vangelovski
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_appreciation\external;

defined('MOODLE_INTERNAL') || die();

use core\external\persistent_exporter;
use renderer_base;
use \block_appreciation\persistents\thankyou;


/**
 * Exporter of a single thankyou
 */
class thankyou_exporter extends persistent_exporter {

    /**
    * Returns the specific class the persistent should be an instance of.
    *
    * @return string
    */
    protected static function define_class() {
        return thankyou::class; 
    }

    /**
     * Returns a list of objects that are related.
     *
     * @return array
     */
    protected static function define_related() {
        return [
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
            "isapproved" => [
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

		// Check if user is the creator of this thankyou.
		$iscreator = 0;
		if ($this->data->creator == $USER->username || $this->related['isapprover']) {
			$iscreator = 1;
		}

		// Author meta.
        $creator = $DB->get_record('user', array('username'=>$this->data->creator));
        profile_load_data($creator);
        $creatorphoto = new \moodle_url('/user/pix.php/'.$creator->id.'/f2.jpg');
        $creatorfullname = fullname($creator);
        $creatorurl = new \moodle_url('/user/profile.php', array('id' => $creator->id));

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

	    return [
	        'creatorphoto' => $creatorphoto,
	        'creatorfullname' => $creatorfullname,
	        'creatorurl' => $creatorurl->out(false),
	        'recipientphoto' => $recipientphoto,
	        'recipientfullname' => $recipientfullname,
	        'recipienturl' => $recipienturl->out(false),
	        'readabletime' => $readabletime,
	        'iscreator' => $iscreator,
	        'isapproved' => $isapproved,
	    ];
	}

	
    /**
     * Get the formatting parameters for the message.
     *
     * @return array
     */
    protected function get_format_parameters_for_message() {
        return [
            'component' => 'block_appreciation',
            'filearea' => 'thankyou',
            'itemid' => $this->data->id,
            'options' => \block_appreciation\forms\form_post::editor_options(),
        ];
    }

}