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
 * 
 * @package    local_och5p_core
 * @copyright  2021 Farbod Zamani Boroujeni, ELAN e.V.
 * @author     Farbod Zamani Boroujeni <zamani@elan-ev.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use tool_opencast\local\api;
use block_opencast\local\apibridge;
use core_h5p\factory as H5PFactory;

define('AJAX_SCRIPT', true);
require(__DIR__ . '/../../config.php');

require_login();
if (!confirm_sesskey()) {
    $H5PFactory = new H5PFactory();
    $core = $H5PFactory->get_core();
    $core::ajaxError(get_string('invalidsesskey', 'error'));
    header('HTTP/1.1 403 Forbidden');
    return;
}

$action = required_param('action', PARAM_TEXT);
$contextid = optional_param('contextid', 0, PARAM_INT);
$courseid = optional_param('courseid', 0, PARAM_INT);

list($context, $course, $cm) = get_context_info_array($contextid);

if (!$context && $course) {
    $context = \context_course::instance($course->id);
} else if ($context && !$course && $courseid) { //Global content bank
    $context = \context_course::instance($courseid);
    $course = \get_course($courseid);
}

$coursecontext = $context ? $context : null;

if (is_null($coursecontext) || !has_capability('block/opencast:viewunpublishedvideos', $coursecontext)) {
    print json_encode(['error' => 'No Views Capabilities granted']);
    die;
}

header('Cache-Control: no-cache');
header('Content-Type: application/json; charset=utf-8');

//Validate token.
try {
    $H5PFactory = new H5PFactory();
    $editor = $H5PFactory->get_editor();
    if (!$editor->ajaxInterface->validateEditorToken(required_param('token', PARAM_RAW))) {
        print json_encode(['error' => 'ERROR']);
        die;
    }
} catch ( Exception $e ) {
    print json_encode(['error' => $e->getMessage()]);
    die;
}


switch ($action) {
    case 'courseVideos':
        $apibridge = apibridge::get_instance();

        try {
            $videos = $apibridge->get_course_videos($course->id);
            $data = array(
                "result" => prepareCourseVideos($videos)
            );
        } catch (\moodle_exception $e) {
            print json_encode(['error' => $e->getmessage()]);
            die;
        }

        break;
    case 'videoQualities':
        $data = array(
            "result" => getVideoQualities()
        );
        break;
    case 'courseList':
        $data = array(
            "result" => getCourseList()
        );
        break;    
    default:
        $data = array(
            "error" => "No data"
        );
        break;
}
print json_encode($data);


function prepareCourseVideos($videos) {
    $res_vidoes = array();
    $res_vidoes[] = "<option value=''>-</option>";
    foreach ($videos->videos as $video) {
        $res_vidoes[] = "<option value='{$video->identifier}'>{$video->title}</option>";
    }
    return $res_vidoes;
}

function getVideoQualities() {
    $identifier = required_param('identifier', PARAM_TEXT);
    $api = new api();
    $url = '/search/episode.json?id=' . $identifier;
    $search_result = json_decode($api->oc_get($url), true);
    if ($api->get_http_code() != 200) {
        $result->error = $api->get_http_code();
        header('HTTP/1.1 403 Forbidden');
        die;
    }
    $tracks = $search_result['search-results']['result']['mediapackage']['media']['track'];

    if (!$tracks) {
        print json_encode(['error' => 'Empty tracks']);
        die;
    }

    $video_tracks = array_filter($tracks, function($track) {
        return strpos($track['mimetype'], 'video') !== FALSE;
    });

    $sorted_videos = array();
    foreach ($video_tracks as $video_track ) {

        //accept only videos otherwise reject!
        if (strpos($video_track['mimetype'], 'video') === FALSE) {
            continue;
        }

       $quality = '';

        if (isset($video_track['tags'])) {
            foreach ($video_track['tags']['tag'] as $tag) {
                if (strpos($tag, 'quality') !== FALSE && empty($quality)) {
                    $quality = str_replace('-quality', '', $tag);
                }
            }
        } else if (isset($video_track['video']) && isset($video_track['video']['resolution'])) {
            $quality = $video_track['video']['resolution'];
        } 

        $sorted_videos["{$video_track['type']} ({$video_track['mimetype']})"][$quality] = ["id" => $video_track['id'], "url" => $video_track['url']];
    }

    $res_options = array();
    $res_options[] = "<option value=''>-</option>";
    foreach ($sorted_videos as $flav_type => $qualities) {
        $r_obj = array();
        $r_obj['type']      = ((strpos($flav_type, 'presenter/delivery') !== FALSE) ? get_string('flavor:presenter', 'local_och5p_core') : get_string('flavor:presentation', 'local_och5p_core'));
        preg_match('#\((.*?)\)#', $flav_type, $match);
        $r_obj['mime']      = str_replace('video/', '', $match[1]);

        $option_text = "{$r_obj['type']} ({$r_obj['mime']})";

        $option_value = array();
        $qualities_arr = array();
        foreach ($qualities as $quality => $video) {
            $q_obj_str = '{"quality": "' . $quality . '", "url": "' . $video['url'] . '", "mime": "' . $match[1] . '", "id": "' . $video['id'] . '", "identifier": "' . $identifier . '"}';
            $qualities_arr[] = $q_obj_str;
            $option_value[] = $video['id'];
        }
        
        $res_options[] = "<option data-info='{\"qualities\" : [" . implode(', ', $qualities_arr) . "]}' value='" . implode('&&', $option_value) . "'> $option_text </option>";
    }

    return $res_options;
}

function getCourseList() {
    global $USER;

    if (!is_siteadmin($USER)) {
        return '';
    }

    $courses = enrol_get_my_courses();
    $res_options = array();
    $res_options[] = "<option value=''>-</option>";
    foreach ($courses as $course) {
        $context = \context_course::instance($course->id);
        if (!is_null($context) && has_capability('block/opencast:viewunpublishedvideos', $context)) {
            $res_options[] = "<option value='" . $course->id . "'> $course->shortname </option>";
        }
    }
    return $res_options;
}