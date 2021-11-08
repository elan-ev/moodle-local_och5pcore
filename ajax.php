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
 * @package    local_och5pcore
 * @copyright  2021 Farbod Zamani Boroujeni, ELAN e.V.
 * @author     Farbod Zamani Boroujeni <zamani@elan-ev.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_och5pcore\local\video_manager;
use local_och5pcore\local\opencast_manager;
use core_h5p\factory;
use moodle_exception;
use context_course;

define('AJAX_SCRIPT', true);
require(__DIR__ . '/../../config.php');

require_login();

$action = required_param('action', PARAM_TEXT);

if ($action != 'ltiParams' && !confirm_sesskey()) {
    print json_encode(['error' => get_string('invalidsesskey', 'error')]);
    die;
}

$contextid = optional_param('contextid', 0, PARAM_INT);
$courseid = optional_param('courseid', 0, PARAM_INT);

list($context, $course, $cm) = get_context_info_array($contextid);

if (!$context && $course) {
    $context = context_course::instance($course->id);
} else if ($context && !$course && $courseid) { //Global content bank
    $context = context_course::instance($courseid);
    $course = \get_course($courseid);
}

$coursecontext = $context ? $context : null;

if (is_null($coursecontext) || !has_capability('block/opencast:viewunpublishedvideos', $coursecontext)) {
    print json_encode(['error' => get_string('no_view_error', 'local_och5pcore')]);
    die;
}

header('Cache-Control: no-cache');
header('Content-Type: application/json; charset=utf-8');

//Validate token.
try {
    $H5PFactory = new factory();
    $editor = $H5PFactory->get_editor();
    if ($action != 'ltiParams' && !$editor->ajaxInterface->validateEditorToken(required_param('token', PARAM_RAW))) {
        print json_encode(['error' => get_string('invalidtoken_error', 'local_och5pcore')]);
        die;
    }
} catch (moodle_exception $e ) {
    print json_encode(['error' => $e->getMessage()]);
    die;
}

$data = array();

switch ($action) {
    case 'courseVideos':
        try {
            $data['result'] = video_manager::prepare_course_videos($course->id);
        } catch (moodle_exception $e) {
            $data['error'] = $e->getMessage();
        }
        break;
    case 'videoQualities':
        try {
            $identifier = required_param('identifier', PARAM_TEXT);
            $data['result'] = video_manager::get_video_flavors_with_qualities($identifier);
        } catch (moodle_exception $e) {
            $data['error'] = $e->getMessage();
        }
        break;
    case 'courseList':
        try {
            $data['result'] = video_manager::get_course_lists();
        } catch (moodle_exception $e) {
            $data['error'] = $e->getMessage();
        }
        break;
    case 'ltiParams':
        try {
            $data['result'] = opencast_manager::get_lti_params($course->id);
        } catch (moodle_exception $e) {
            $data['error'] = $e->getMessage();
        }
        break;   
    default:
        $data['error'] = get_string('no_action_error', 'local_och5pcore');
        break;
}
print json_encode($data);
