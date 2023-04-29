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
 * Behat steps definitions for och5pcore.
 *
 * @package    local_och5pcore
 * @copyright  2023 Farbod Zamani Boroujeni, ELAN e.V.
 * @author     Farbod Zamani Boroujeni <zamani@elan-ev.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

use tool_opencast\seriesmapping;
use core_h5p\factory;

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

/**
 * Behat steps definitions for och5pcore.
 *
 * @package    local_och5pcore
 * @copyright  2023 Farbod Zamani Boroujeni, ELAN e.V.
 * @author     Farbod Zamani Boroujeni <zamani@elan-ev.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_local_och5pcore extends behat_base {

    /**
     * adds a breakpoints
     * stops the execution until you hit enter in the console
     * @Then /^breakpoint in och5pcore/
     */
    public function breakpoint_in_och5pcore() {
        fwrite(STDOUT, "\033[s    \033[93m[Breakpoint] Press \033[1;93m[RETURN]\033[0;93m to continue...\033[0m");
        while (fgets(STDIN, 1024) == '') {
            continue;
        }
        fwrite(STDOUT, "\033[u");
        return;
    }

    /**
     * Upload a testvideo.
     * @Given /^I setup the opencast video block for the course with och5pcore$/
     */
    public function i_setup_the_opencast_video_block_for_the_course_with_och5pcore() {
        $courses = core_course_category::search_courses(array('search' => 'Course 1'));

        // When we are using stable.opencast.org, the series Blender Foundation Productions with id: ID-blender-foundation,
        // is by default avaialble. Therefore, and as for make things simpler, we use this series in our course.
        $mapping = new seriesmapping();
        $mapping->set('courseid', reset($courses)->id);
        $mapping->set('series', 'ID-blender-foundation');
        $mapping->set('isdefault', '1');
        $mapping->set('ocinstanceid', 1);
        $mapping->create();
    }

    /**
     * Get latest Interactive Video content type for h5p
     * @Given /^I get the latest h5p content types$/
     */
    public function i_get_the_latest_h5p_content_types() {
        $factory = new factory();
        $core = $factory->get_core();
        $library = [
            'machineName' => "H5P.InteractiveVideo",
            'majorVersion' => 1,
            'minorVersion' => 26,
            'patchVersion' => 6,
            'example' => "https://h5p.org/interactive-video",
            'tutorial' => "https://h5p.org/tutorial-interactive-video"
        ];
        $res = $core->fetch_content_type($library);
    }

    /**
     * Scrolling to an element in och5pcore
     * @Given /^I scroll to "(?P<element_selector_string>(?:[^"]|\\")*)" in och5pcore$/
     * @param string $elementselector Element we look for
     */
    public function i_scroll_to_in_och5pcore($elementselector) {
        $function = <<<JS
(function(){document.querySelector("$elementselector").scrollIntoView();})()
JS;
        try {
            $this->getSession()->executeScript($function);
        } catch (\moodle_exception $e ) {
            throw new \moodle_exception('behat_error_unabletofind_element', 'local_och5pcore');
        }
    }
}
