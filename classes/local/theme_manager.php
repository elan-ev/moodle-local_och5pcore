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
 * Theme Manager class contains all related functions to extend and unextend the themes.
 *
 * @package    local_och5pcore
 * @copyright  2021 Farbod Zamani Boroujeni, ELAN e.V.
 * @author     Farbod Zamani Boroujeni <zamani@elan-ev.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_och5pcore\local;

use core_component;

defined('MOODLE_INTERNAL') || die();

/**
 * Theme Manager class contains all related functions to extend and unextend the themes.
 *
 * @package    local_och5pcore
 * @copyright  2021 Farbod Zamani Boroujeni, ELAN e.V.
 * @author     Farbod Zamani Boroujeni <zamani@elan-ev.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class theme_manager
{
    /** @var string extension start flag */
    const START_OCH5PCORE_EXTENSION = '// Added by local_och5pcore plugin';

    /** @var string extension end flag */
    const END_OCH5PCORE_EXTENSION = '// End of local_och5pcore code block.';

    /**
     * Extends themes by appending the codes into related files.
     *
     * @param array $themes list of themes to extend
     * @return array list extended themes
     */
    public static function extend_themes($themes) {
        global $CFG;

        // Initialise the extended themes array list.
        $extendedthemes = array();

        // Gather all required contents.
        $renderercontent = file_get_contents($CFG->dirroot . '/local/och5pcore/lib/extension_contents/renderer_content.txt');
        $configcontent = file_get_contents($CFG->dirroot . '/local/och5pcore/lib/extension_contents/config_content.txt');
        $licensecontent = file_get_contents($CFG->dirroot . '/local/och5pcore/lib/extension_contents/license_content.txt');

        // Check if the contents are not empty.
        if (empty($renderercontent) || empty($configcontent) || empty($licensecontent)) {
            return $extendedthemes;
        }

        // Loop thorugh the themes array to extend.
        foreach ($themes as $themename) {

            // Get the directory of the theme.
            $dir = core_component::get_plugin_directory('theme', $themename);

            // Replace the renderer class name with the theme name in the renderer content.
            $renderercontent = str_replace('local_och5pcore_h5p_renderer',
                "theme_{$themename}_core_h5p_renderer", $renderercontent);

            // Step 1: Extend theme renderers.php file.

            // If the theme has the renderer file.
            if (file_exists("$dir/renderers.php")) {

                // Get the current theme renderer file's content.
                $themerenderer = file_get_contents("$dir/renderers.php");

                // If the och5pcore content does not exists in the theme renderer.
                if (strpos($themerenderer, "theme_{$themename}_core_h5p_renderer") === false) {

                    // Append the customized renderer class into the theme renderer content.
                    $themenewrenderer = str_replace('?>', '', $themerenderer) . "\r\n\r\n" . $renderercontent;

                    // Make sure that new renderer content contains both current and och5pcore contents.
                    if ($themenewrenderer && strpos($themenewrenderer, $renderercontent) !== false &&
                        strpos($themenewrenderer, $themerenderer) !== false) {

                        // Insert the new renderer contents into the theme renderer file.
                        if (file_put_contents("$dir/renderers.php", $themenewrenderer) !== false) {
                            $extendedthemes[] = $themename;
                        }
                    }
                } else {
                    // In case the och5pcore content already exists.
                    $extendedthemes[] = $themename;
                }
            } else {
                // If the theme does not have any renderers file.

                // Append license into renderer content.
                $themenewrenderer = $licensecontent . "\r\n\r\n" . $renderercontent;

                // Insert the new renderer contents into the theme renderer file.
                if (file_put_contents("$dir/renderers.php", $themenewrenderer) !== false) {
                    $extendedthemes[] = $themename;
                }
            }

            // Step 2: Extend theme config.php file.

            // Get the current theme config content.
            $themeconfigcontent = file_get_contents("$dir/config.php");

            // Check if the theme_overridden_renderer_factory config already exists.
            if (strpos($themeconfigcontent, 'theme_overridden_renderer_factory') === false) {
                // Append required config option into theme config.
                $newconfig = str_replace('?>', '', $themeconfigcontent) . "\r\n\r\n" . $configcontent;
                // Insert the new config contents into the theme config file.
                file_put_contents("$dir/config.php", $newconfig);
            }

            // Revert back the class name in renderer content in order to check for the next itteration.
            $renderercontent = str_replace("theme_{$themename}_core_h5p_renderer",
                'local_och5pcore_h5p_renderer', $renderercontent);
        }

        // Finally, return the list of extended themes.
        return $extendedthemes;
    }

    /**
     * Unextends themes by removing the codes from themes related files.
     *
     * @param array $themes list of themes to unextend
     * @return array list of unextended themes
     */
    public static function remove_themes_extension($themes) {
        // Initialise the list of themes failed to remove extension.
        $failedtoremoveextension = array();

        foreach ($themes as $themename) {
            // Get the directory of the theme.
            $dir = core_component::get_plugin_directory('theme', $themename);

            // Step 1: remove extension from theme renderers file.
            // If the theme has the renderer file.
            if (file_exists("$dir/renderers.php")) {
                // Get the theme renderer content.
                $themerenderer = file_get_contents("$dir/renderers.php");

                // If start and end tags in theme renderers content have been identified, then remove the block.
                if (strpos($themerenderer, self::START_OCH5PCORE_EXTENSION) !== false &&
                    strpos($themerenderer, self::END_OCH5PCORE_EXTENSION) !== false) {

                    // Find the position of the start and end flags.
                    $beginpos = strpos($themerenderer, self::START_OCH5PCORE_EXTENSION);
                    $endpos = strpos($themerenderer, self::END_OCH5PCORE_EXTENSION);

                    // Extract the extension block.
                    $och5pextensionblock = substr($themerenderer,
                        $beginpos, ($endpos + strlen(self::END_OCH5PCORE_EXTENSION)) - $beginpos);
                    // Remove the extesion block from the theme renderer content.
                    $themerenderer = rtrim(str_replace($och5pextensionblock, '', $themerenderer));

                    // Insert the new renderer content into the theme renderers file.
                    if (file_put_contents("$dir/renderers.php", $themerenderer) === false) {
                        $failedtoremoveextension[] = ucfirst(str_replace('_', ' ', $themename));
                    }
                }
            }

            // Step 2: remove extension from theme config file.
            // If the theme has the config file.
            if (file_exists("$dir/config.php")) {
                // Get the theme config content.
                $themeconfigcontent = file_get_contents("$dir/config.php");

                // If start and end tags in theme config content have been identified, then remove the block.
                if (strpos($themeconfigcontent, self::START_OCH5PCORE_EXTENSION) !== false &&
                    strpos($themeconfigcontent, self::END_OCH5PCORE_EXTENSION) !== false) {
                    // Find the position of the start and end flags.
                    $beginpos = strpos($themeconfigcontent, self::START_OCH5PCORE_EXTENSION);
                    $endpos = strpos($themeconfigcontent, self::END_OCH5PCORE_EXTENSION);

                    // Extract the extension block.
                    $och5pextensionblock = substr($themeconfigcontent,
                        $beginpos, ($endpos + strlen(self::END_OCH5PCORE_EXTENSION)) - $beginpos);
                    // Remove the extesion block from the theme renderer content.
                    $themeconfigcontent = str_replace($och5pextensionblock, '', $themeconfigcontent);

                    // Insert the new config content into the theme config file.
                    file_put_contents("$dir/config.php", $themeconfigcontent);
                }
            }
        }

        // Finally, return the list of themes that have no more extensions.
        return $failedtoremoveextension;
    }

    /**
     * Cleans up the renderers files from the old version extension codes.
     */
    public static function cleaup_themes_extension() {
        // Get the installed themes.
        $installedthemes = core_component::get_plugin_list('theme');
        // We define the clean-up flags here.
        // Start and End of a flag help to locate the codes better.
        $cleanupflags = [
            ['start' => '//#och5p_core', 'end' => '//#end_och5p_core']
        ];

        foreach ($installedthemes as $themename => $themedir) {

            // If the theme has the renderer file.
            if (file_exists("$themedir/renderers.php")) {
                // Loop through the flags to find the code blocks.
                foreach ($cleanupflags as $flag) {
                    // Reject invalid flags.
                    if (!array_key_exists('start', $flag) || !array_key_exists('end', $flag)) {
                        continue;
                    }

                    // Get the theme renderer content.
                    $themerenderer = file_get_contents("$themedir/renderers.php");

                    // If start and end tags in theme renderers content have been identified, then remove the block.
                    if (strpos($themerenderer, $flag['start']) !== false && strpos($themerenderer, $flag['end']) !== false) {

                        // Find the position of the start and end flags.
                        $beginpos = strpos($themerenderer, $flag['start']);
                        $endpos = strpos($themerenderer, $flag['end']);

                        // Extract the extension block.
                        $blocktocleanup = substr($themerenderer, $beginpos, ($endpos + strlen($flag['end'])) - $beginpos);
                        // Remove the extesion block from the theme renderer content.
                        $themerenderer = rtrim(str_replace($blocktocleanup, '', $themerenderer));

                        // Insert the new renderer content into the theme renderers file.
                        file_put_contents("$themedir/renderers.php", $themerenderer);
                    }
                }
            }
        }
    }
}
