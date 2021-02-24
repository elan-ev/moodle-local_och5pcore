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

defined('MOODLE_INTERNAL') || die();

function och5p_core_extend_themes($themes) {
    global $CFG;
    $och5p_core_class_content = file_get_contents($CFG->dirroot . '/local/och5p_core/libs/rederers_content.txt');
    $och5p_core_cofig_content = file_get_contents($CFG->dirroot . '/local/och5p_core/libs/config_content.txt');
    $och5p_core_license_content = file_get_contents($CFG->dirroot . '/local/och5p_core/libs/license_content.txt');

    if (!$och5p_core_class_content && !$och5p_core_cofig_content && !$och5p_core_license_content) {
        return false;
    }

    $extened_themes = array();
    foreach ($themes as $theme_name) {
        $dir = \core_component::get_plugin_directory('theme', $theme_name);
        $och5p_core_class_content = str_replace('local_och5p_core_h5p_renderer', "theme_{$theme_name}_core_h5p_renderer", $och5p_core_class_content);

        if (file_exists("$dir/renderers.php")) {
            $renderer_content = file_get_contents("$dir/renderers.php");
            if (strpos($renderer_content, "theme_{$theme_name}_core_h5p_renderer") === FALSE) {
                $new_renderer = str_replace('?>', '', $renderer_content) . "\r\n" . $och5p_core_class_content;

                if ($new_renderer && strpos($new_renderer, $och5p_core_class_content) !== FALSE && strpos($new_renderer, $renderer_content) !== FALSE) {
                    if (file_put_contents("$dir/renderers.php", $new_renderer) !== FALSE) {
                        $extened_themes[] = $theme_name;
                    }
                }
            } else {
                $extened_themes[] = $theme_name;
            }
        } else {
            $new_renderer = $och5p_core_license_content . "\r\n" . $och5p_core_class_content;
            if (file_put_contents("$dir/renderers.php", $new_renderer) !== FALSE) {
                $extened_themes[] = $theme_name;
            }
        }

        $theme_config_content = file_get_contents("$dir/config.php");
        if (strpos($theme_config_content, 'theme_overridden_renderer_factory') === FALSE) {
            $new_config = str_replace('?>', '', $theme_config_content) . "\r\n" . $och5p_core_cofig_content;
            file_put_contents("$dir/config.php", $new_config);
        }

        //return to default
        $och5p_core_class_content = str_replace("theme_{$theme_name}_core_h5p_renderer", 'local_och5p_core_h5p_renderer', $och5p_core_class_content);
    }

    return $extened_themes;
}

function och5p_core_unenxtend_themes($themes) {
    global $CFG;
    $och5p_core_class_content = file_get_contents($CFG->dirroot . '/local/och5p_core/libs/rederers_content.txt');
    $och5p_core_cofig_content = file_get_contents($CFG->dirroot . '/local/och5p_core/libs/config_content.txt');

    if (!$och5p_core_class_content && !$och5p_core_cofig_content) {
        return false;
    }

    $unextended_themes = array();
    foreach ($themes as $theme_name) {
        $dir = \core_component::get_plugin_directory('theme', $theme_name);
        $och5p_core_class_content = str_replace('local_och5p_core_h5p_renderer', "theme_{$theme_name}_core_h5p_renderer", $och5p_core_class_content);
        if (file_exists("$dir/renderers.php")) {
            $renderer_content = file_get_contents("$dir/renderers.php");
            if (strpos($renderer_content, $och5p_core_class_content) !== FALSE) {
                $renderer_content = str_replace($och5p_core_class_content, '', $renderer_content);
                if(file_put_contents("$dir/renderers.php", $renderer_content) !== FALSE) {
                    $unextended_themes[] = $theme_name;
                }
            } 
        }
        if (file_exists("$dir/config.php")) {
            $config_contents = file_get_contents("$dir/config.php");
            if (strpos($config_contents, $och5p_core_cofig_content) !== FALSE) {
                $config_content = str_replace($och5p_core_cofig_content, '', $config_contents);
                file_put_contents("$dir/config.php", $config_content);
            }
        }
        //return to default
        $och5p_core_class_content = str_replace("theme_{$theme_name}_core_h5p_renderer", 'local_och5p_core_h5p_renderer', $och5p_core_class_content);
    }

    return $unextended_themes;
}
