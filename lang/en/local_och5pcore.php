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
 * Plugin strings are defined here.
 *
 * @package    local_och5pcore
 * @copyright  2021 Farbod Zamani Boroujeni, ELAN e.V.
 * @author     Farbod Zamani Boroujeni <zamani@elan-ev.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

$string['pluginname'] = 'H5P Opencast Extension (Core)';
$string['setting_extended_themes_header'] = 'Themes';
$string['setting_extended_themes'] = 'Available themes to extend';
$string['setting_extended_themes_desc'] = 'Select the themes that should be extended to show Opencast Videos in H5P Interactive videos. Hold down the Ctrl key to select multiple themes. Unselecting a theme will remove the previous extension.';
$string['setting_lti_header'] = 'LTI Configuration';
$string['setting_lti_header_desc'] = 'When "Securing Static Files" in Opencast configuration is enabled, it is necessary to use LTI authentication.';
$string['setting_lti_consumerkey'] = 'LTI Consumer key';
$string['setting_lti_consumerkey_desc'] = 'LTI Consumer key for the opencast.';
$string['setting_lti_consumersecret'] = 'LTI Consumer Secret';
$string['setting_lti_consumersecret_desc'] = 'LTI Consumer Secret for the opencast.';
$string['extended_error'] = 'Unable to extend theme(s): %s';
$string['unextended_error'] = 'Unable to unextend theme(s): %s';
$string['flavor:presenter'] = 'Presenter';
$string['flavor:presentation'] = 'Presentation';
$string['video_course_error'] = 'An error occured while obtaining Opencast course videos.';
$string['search_episode_error'] = 'Unable to get video data from opencast.';
$string['no_tracks_error'] = 'Invalid video data.';
$string['no_admin_user_error'] = 'Only admins can access this feature.';
$string['no_action_error'] = 'Undefined action.';
$string['no_view_error'] = 'The opencast view capability is not granted.';
$string['no_lti_config_error'] = 'Unable to perform Opencast LTI authentication in H5P';
$string['invalidtoken_error'] = 'Invalid token - token not found';
$string['privacy:metadata'] = 'The H5P Opencast Extension (Core) only works as an integration of Opencast into H5P and store no user data.';
$string['label_video_file'] = 'Select a video file';
$string['label_video_flavor'] = 'Select the video\'s flavor and quality';
$string['label_course'] = 'Select a course';
$string['header_text'] = 'Opencast Videos';
$string['behat_error_unabletofind_element'] = 'Unable to locate element in the page';

