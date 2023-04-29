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
 * Video Manager class contains all related functions to extract and manage opencast course videos.
 *
 * @package    local_och5pcore
 * @copyright  2021 Farbod Zamani Boroujeni, ELAN e.V.
 * @author     Farbod Zamani Boroujeni <zamani@elan-ev.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_och5pcore\local;

use local_och5pcore\local\opencast_manager;
use moodle_exception;

/**
 * Video Manager class contains all related functions to extract and manage opencast course videos.
 *
 * @package    local_och5pcore
 * @copyright  2021 Farbod Zamani Boroujeni, ELAN e.V.
 * @author     Farbod Zamani Boroujeni <zamani@elan-ev.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class video_manager {
    /**
     * Get opencast course videos and prepare it to show in dropdown with option tag.
     *
     * @param int $courseid course id
     * @return array option list of opencast course videos
     * @throws moodle_exception
     */
    public static function prepare_course_videos($courseid) {
        // Get the course videos.
        $coursevideos = opencast_manager::get_course_videos($courseid);

        // Throw error if error occures.
        if ($coursevideos->error != 0) {
            throw new moodle_exception('video_course_error', 'local_och5pcore');
        }

        // Initialise options array with an empty option.
        $options = array('<option value="">-</option>');

        // Loop through videos if there is any.
        foreach ($coursevideos->videos as $video) {
            $options[] = "<option value='{$video->identifier}'>{$video->title}</option>";
        }

        // Finally, we return the array of options.
        return $options;
    }

    /**
     * Extracts video's qualities from opencast video metadata catalog.
     *
     * @param string $identifier opencast video identifier
     * @return array option list of opencast course videos
     * @throws moodle_exception
     */
    public static function get_video_flavors_with_qualities($identifier) {
        // Get the sorted video list.
        $sortedvideos = opencast_manager::get_episode_tracks($identifier);

        // Initialise options array with an empty option tag.
        $options = array('<option value="">-</option>');

        foreach ($sortedvideos as $flavor => $qualities) {
            // Extract type and mime from the item.
            $obj = array();
            $obj['type'] = ((strpos($flavor, 'presenter/delivery') !== false) ?
                get_string('flavor:presenter', 'local_och5pcore') :
                get_string('flavor:presentation', 'local_och5pcore'));
            preg_match('#\((.*?)\)#', $flavor, $match);
            $obj['mime']      = str_replace('video/', '', $match[1]);

            // Initialise option text.
            $optiontext = "{$obj['type']} ({$obj['mime']})";

            // Extract and place the qualities.
            $optionvalue = array();
            $qualitiesarray = array();
            foreach ($qualities as $quality => $video) {
                $qualitydatastring = '{"quality": "' . $quality . '", "url": "' . $video['url'] .
                    '", "mime": "' . $match[1] . '", "id": "' . $video['id'] .
                    '", "identifier": "' . $identifier . '"}';
                $qualitiesarray[] = $qualitydatastring;
                $optionvalue[] = $video['id'];
            }

            // Insert all data into an option tag and put into the array.
            $options[] = "<option data-info='{\"qualities\" : [" .
                implode(', ', $qualitiesarray) . "]}' value='" .
                implode('&&', $optionvalue) .
                "'> $optiontext </option>";
        }

        // Finally, we return the options array.
        return $options;
    }

    /**
     * Provides a list of enrolled courses for the user as option tags.
     * It is used to provide extra feature for admins to access their courses from global content bank.
     * @return array option list of user enrolled courses.
     * @throws moodle_exception
     */
    public static function get_course_lists() {
        global $USER;

        // If the user is not admin.
        if (!is_siteadmin($USER)) {
            throw new moodle_exception('no_admin_user_error', 'local_och5pcore');
        }

        // Get the enrolled courses.
        $courses = enrol_get_my_courses();
        // Initialise options array with an empty option tag.
        $options = array('<option value="">-</option>');
        foreach ($courses as $course) {
            $context = \context_course::instance($course->id);
            if (!is_null($context) && has_capability('block/opencast:viewunpublishedvideos', $context)) {
                $options[] = "<option value='{$course->id}'>{$course->shortname}</option>";
            }
        }

        // Finally, we return the options array.
        return $options;
    }

    /**
     * Get all the label texts needed to display to the user.
     *
     * @return array
     */
    public static function get_ui_strings() {
        $texts = [
            'label_course' => get_string('label_course', 'local_och5pcore'),
            'label_video_file' => get_string('label_video_file', 'local_och5pcore'),
            'label_video_flavor' => get_string('label_video_flavor', 'local_och5pcore'),
            'header_text' => get_string('header_text', 'local_och5pcore'),
        ];
        return $texts;
    }
}
