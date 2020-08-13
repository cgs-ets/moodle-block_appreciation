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
 * Provides {@link block_appreciation\external\list_exporter} class.
 *
 * @package   block_appreciation
 * @copyright 2020 Michael Vangelovski
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_appreciation\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/blocks/appreciation/locallib.php');
use renderer_base;
use core\external\exporter;

/**
 * Exports the list of announcements
 */
class list_exporter extends exporter {

    /**
    * Return the list of additional properties.
    *
    * Calculated values or properties generated on the fly based on standard properties and related data.
    *
    * @return array
    */
    protected static function define_other_properties() {
        return [
            'posts' => [
                'type' => post_exporter::read_properties_definition(),
                'multiple' => true,
                'optional' => false,
            ],
            'possiblemore' => [
                'type' => PARAM_BOOL,
                'multiple' => false,
                'optional' => false,
            ],
            'nextpagelink'=> [
                'type' => PARAM_RAW,
                'multiple' => false,
                'optional' => false,
            ],
        ];
    }

    /**
    * Returns a list of objects that are related.
    *
    * Data needed to generate "other" properties.
    *
    * @return array
    */
    protected static function define_related() {
        return [
            'context' => 'context',
            'instanceid' => 'int',
            'courseid' => 'int',
            'posts' => 'block_appreciation\persistents\post[]',
            'page' => 'int',
            'isapprover' => 'bool',
        ];
    }

    /**
     * Get the additional values to inject while exporting.
     *
     * @param renderer_base $output The renderer.
     * @return array Keys are the property names, values are their values.
     */
    protected function get_other_values(renderer_base $output) {
        global $PAGE;

        $posts = [];
        // Export each post in the list
        foreach ($this->related['posts'] as $post) {
            $postexporter = new post_exporter($post, [
                'context' => $this->related['context'],
                'isapprover' => $this->related['isapprover'],
            ]);
            $posts[] = $postexporter->export($output);
        }

        $possiblemore = ( count($posts) >= 25 );

        // Pagination.
        // To minimise load time, we do not attempt to figure out how many posts.
        $totalcount = 999999999;
        $perpage = APPRECIATION_PERPAGE;
        $baseurl = new \moodle_url('/blocks/appreciation/list.php', array(
            'instanceid' => $this->related['instanceid'],
            'courseid' => $this->related['courseid'],
        ));
        $pagingbar = new \paging_bar($totalcount, $this->related['page'], $perpage, $baseurl, 'page');
        $pagingbar->prepare($output, $PAGE, '');
        $nextpagelink = $pagingbar->nextlink;

        return [
            'posts' => $posts,
            'possiblemore' => $possiblemore,
            'nextpagelink' => $nextpagelink,
        ];
    }


}