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
 * Opencast Manager class contains all related functions to handle opencast related functionalities.
 *
 * @package    local_och5pcore
 * @copyright  2021 Farbod Zamani Boroujeni, ELAN e.V.
 * @author     Farbod Zamani Boroujeni <zamani@elan-ev.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_och5pcore\local;

use tool_opencast\local\api;
use block_opencast\local\apibridge;
use tool_opencast\local\settings_api;
use oauth_helper;
use moodle_exception;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/lti/locallib.php');
require_once($CFG->dirroot . '/lib/oauthlib.php');

/**
 * Opencast Manager class contains all related functions to handle opencast related functionalities.
 *
 * @package    local_och5pcore
 * @copyright  2021 Farbod Zamani Boroujeni, ELAN e.V.
 * @author     Farbod Zamani Boroujeni <zamani@elan-ev.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class opencast_manager
{
    /**
     * Get Opencast Instances.
     *
     * @return array opencast instances.
     */
    public static function get_ocinstances() {
        // Get all opencast instances.
        $ocinstances = settings_api::get_ocinstances();
        // Return empty array, if nothing's found.
        if (empty($ocinstances)) {
            return [];
        }

        $ocinstancenames = [];
        // Loop through each opencast instance to prepare a consumable array format.
        foreach ($ocinstances as $ocinstanceconfig) {
            $name = (!empty($ocinstanceconfig->name)) ?
                $ocinstanceconfig->name :
                get_string('setting_opencast_instance_default_name_option', 'local_och5pcore');
            $default = ($ocinstanceconfig->isdefault) ?
                ' (' . get_string('setting_opencast_instance_default_indicator', 'local_och5pcore') . ')' :
                '';
            $ocinstancenames[$ocinstanceconfig->id] = "$name (ID:{$ocinstanceconfig->id})$default";
        }

        return $ocinstancenames;
    }

    /**
     * Get Default Opencast Instance.
     *
     * @return int default opencast instance id.
     */
    public static function get_default_ocinstance() {
        // Get the default instance object.
        $ocdefaultinstance = settings_api::get_default_ocinstance();
        // Return the id of the instance, if empty only return 1 refering to the first instance.
        return (empty($ocdefaultinstance)) ? 1 : $ocdefaultinstance->id;
    }
    /**
     * Get the ocinstance config for search endpoint. It double checks if the id still exists.
     * In case the id is not available anymore, it uses the default opencsat instance id.
     *
     * @return int ocinstanceid the id of configured opencsat intance.
     */
    public static function get_configured_search_opencast_instance() {
        // Initialized the search instance with default id.
        $searchocinstanceid = self::get_default_ocinstance();
        // Get config.
        $configedocinstanceid = get_config('local_och5pcore', 'searchocinstance');
        // Get the list.
        $ocinstances = self::get_ocinstances();
        // Loop though to check if the config instance exists.
        foreach ($ocinstances as $id => $name) {
            if ($id == $configedocinstanceid) {
                $searchocinstanceid = $configedocinstanceid;
            }
        }

        return intval($searchocinstanceid);
    }

    /**
     * Get videos avaialble in the course.
     *
     * @param int $courseid the id of the course.
     *
     * @return array the list of opencast course videos.
     */
    public static function get_course_videos($courseid) {
        // Get an instance of apibridge.
        $apibridge = apibridge::get_instance();

        // Initialize the course videos array.
        $coursevideos = null;

        // Get series for the course.
        $courseseries = $apibridge->get_course_series($courseid);
        // Initialize the series videos array.
        $seriesvideos = [];
        $haserror = 0;

        foreach ($courseseries as $series) {
            // Get videos of each series.
            $videos = $apibridge->get_series_videos($series->series);

            // Merge videos into $seriesvideo, when there is something.
            if ($videos->error == 0 && !empty($videos->videos)) {

                // In order to process the video later on, we need to accept those video that has engage publication.
                $engagepublishedvideos = array_filter($videos->videos, function($video) {
                    return in_array('engage-player', $video->publication_status);
                });
                $seriesvideos = array_merge($seriesvideos, $engagepublishedvideos);
            }

            if ($videos->error != 0) {
                $haserror = 1;
            }
        }

        // Check if there is any video to initialize the $coursevideos relatively.
        $coursevideos->videos = !$haserror ? $seriesvideos : [];
        $coursevideos->error = $haserror;

        return $coursevideos;
    }

    /**
     * Get videos avaialble in the course.
     *
     * @param string $identifier the opencast event (video) identifier.
     *
     * @return array the list of consumable opencast events tracks.
     */
    public static function get_episode_tracks($identifier) {
        // Get the opencast instance for the search endpoint.
        $searchocinstanceid = self::get_configured_search_opencast_instance();

        // Get api instance from tool_opencast.
        $api = api::get_instance($searchocinstanceid);

        // Prepare the endpoint url.
        $url = '/search/episode.json?id=' . $identifier;

        // Make the get request.
        $searchresult = json_decode($api->oc_get($url), true);

        // If something went wrong, we return moodle_exception.
        if ($api->get_http_code() != 200) {
            throw new moodle_exception('search_episode_error', 'local_och5pcore');
        }

        // Extract the tracks from mediapackage.
        $tracks = (isset($searchresult['search-results']['result']) ?
            $searchresult['search-results']['result']['mediapackage']['media']['track'] :
            null);

        // If tracks does not exists, we return moodle_exception.
        if (!$tracks) {
            throw new moodle_exception('no_tracks_error', 'local_och5pcore');
        }

        $videotracks = [];
        // If there is video key inside the tracks array, that means it is a single track.
        if (array_key_exists('video', $tracks)) {
            if (strpos($tracks['mimetype'], 'video') !== false) {
                $videotracks[] = $tracks;
            }
        } else {
            // Otherwise, there are more than one track.
            // Extract videos from tracks.
            $videotracks = array_filter($tracks, function($track) {
                return strpos($track['mimetype'], 'video') !== false;
            });
        }


        // Initialise the sorted videos array.
        $sortedvideos = array();

        foreach ($videotracks as $videotrack) {

            // Double check if the track is 100% video track.
            if (strpos($videotrack['mimetype'], 'video') === false) {
                continue;
            }

            $quality = '';

            if (isset($videotrack['tags'])) {
                foreach ($videotrack['tags']['tag'] as $tag) {
                    if (strpos($tag, 'quality') !== false && empty($quality)) {
                        $quality = str_replace('-quality', '', $tag);
                    }
                }
            } else if (isset($videotrack['video']) && isset($videotrack['video']['resolution'])) {
                $quality = $videotrack['video']['resolution'];
            }

            $sortedvideos["{$videotrack['type']} ({$videotrack['mimetype']})"][$quality] =
                ["id" => $videotrack['id'], "url" => $videotrack['url']];
        }

        return $sortedvideos;
    }

    /**
     * Gets LTI parameters to perform the LTI authentication.
     *
     * @param int $courseid id of the course.
     * @return array lti parameters.
     */
    public static function get_lti_params($courseid) {
        global $CFG, $USER;

        // Get the course object.
        $course = get_course($courseid);

        // Get configured consumerkey and consumersecret.
        $consumerkey = get_config('local_och5pcore', 'lticonsumerkey');
        $consumersecret = get_config('local_och5pcore', 'lticonsumersecret');

        // Get the opencast instance for the search endpoint.
        $searchocinstanceid = self::get_configured_search_opencast_instance();

        // Get the endpoint url of the search instance.
        $endpoint = get_config('tool_opencast', 'apiurl_' . $searchocinstanceid);

        // The default api url, gets no instance id in its config setting in tool_opencast.
        if (empty($endpoint) && $searchocinstanceid == self::get_default_ocinstance()) {
            $endpoint = get_config('tool_opencast', 'apiurl');
        }

        // Check if all requirements are correctly configured.
        if (empty($consumerkey) || empty($consumersecret) || empty($endpoint)) {
            throw new moodle_exception('no_lti_config_error', 'local_och5pcore');
        }

        // Validate the url and add lti endpoint to make the call.
        if (strpos($endpoint, 'http') !== 0) {
            $endpoint = 'http://' . $endpoint;
        }
        $endpoint .= '/lti';

        $helper = new oauth_helper(array('oauth_consumer_key'    => $consumerkey,
                                        'oauth_consumer_secret' => $consumersecret));

        // Set all necessary parameters.
        $params = array();
        $params['oauth_version'] = '1.0';
        $params['oauth_nonce'] = $helper->get_nonce();
        $params['oauth_timestamp'] = $helper->get_timestamp();
        $params['oauth_consumer_key'] = $consumerkey;

        $params['context_id'] = $course->id;
        $params['context_label'] = trim($course->shortname);
        $params['context_title'] = trim($course->fullname);
        $params['resource_link_id'] = 'o' . random_int(1000, 9999) . '-' . random_int(1000, 9999);
        $params['resource_link_title'] = 'Opencast';
        $params['context_type'] = ($course->format == 'site') ? 'Group' : 'CourseSection';
        $params['launch_presentation_locale'] = current_language();
        $params['ext_lms'] = 'moodle-2';
        $params['tool_consumer_info_product_family_code'] = 'moodle';
        $params['tool_consumer_info_version'] = strval($CFG->version);
        $params['oauth_callback'] = 'about:blank';
        $params['lti_version'] = 'LTI-1p0';
        $params['lti_message_type'] = 'basic-lti-launch-request';
        $urlparts = parse_url($CFG->wwwroot);
        $params['tool_consumer_instance_guid'] = $urlparts['host'];
        $params['custom_tool'] = '/ltitools';

        // User data.
        $params['user_id'] = $USER->id;
        $params['lis_person_name_given'] = $USER->firstname;
        $params['lis_person_name_family'] = $USER->lastname;
        $params['lis_person_name_full'] = $USER->firstname . ' ' . $USER->lastname;
        $params['ext_user_username'] = $USER->username;
        $params['lis_person_contact_email_primary'] = $USER->email;
        $params['roles'] = lti_get_ims_role($USER, null, $course->id, false);

        if (!empty($CFG->mod_lti_institution_name)) {
            $params['tool_consumer_instance_name'] = trim(html_to_text($CFG->mod_lti_institution_name, 0));
        } else {
            $params['tool_consumer_instance_name'] = get_site()->shortname;
        }

        $params['launch_presentation_document_target'] = 'iframe';
        $params['oauth_signature_method'] = 'HMAC-SHA1';
        $params['oauth_signature'] = $helper->sign("POST", $endpoint, $params, $consumersecret . '&');

        // Additional params.
        $params['endpoint'] = $endpoint;
        return $params;
    }
}
