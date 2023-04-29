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
 * Plugin function library.
 *
 * @package    local_och5pcore
 * @copyright  2021 Farbod Zamani Boroujeni, ELAN e.V.
 * @author     Farbod Zamani Boroujeni <zamani@elan-ev.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_och5pcore\local\theme_manager;

/**
 * Extends and unextends the themes based on the theme selcetion in admi n setting.
 */
function local_och5pcore_extend_themes() {

    $returnurl = new moodle_url('/admin/settings.php?section=local_och5pcore_settings');

    $config = get_config('local_och5pcore', 'extended_themes');
    $selectedthemes = !empty($config) ? explode(',', $config) : array();

    $installedthemes = core_component::get_plugin_list('theme');

    $unselectedthemes = array_diff(array_keys($installedthemes), $selectedthemes);

    if (count($selectedthemes)) {
        $extendedthemes = theme_manager::extend_themes($selectedthemes);

        if ($diffs = array_diff($selectedthemes, $extendedthemes)) {
            $themenames = array();
            foreach ($diffs as $diff) {
                $themenames[] = ucfirst(str_replace('_', ' ', $diff));
            }
            $errormessage = sprintf(get_string('extended_error', 'local_och5pcore'), implode(', ', $themenames));
            redirect($returnurl, $errormessage , 0,
                \core\output\notification::NOTIFY_ERROR);
        }
    }

    if (count($unselectedthemes)) {
        $failedunextended = theme_manager::remove_themes_extension($unselectedthemes);

        if (count($failedunextended) > 0) {
            $errormessage = sprintf(get_string('unextended_error', 'local_och5pcore'), implode(', ', $failedunextended));
            redirect($returnurl, $errormessage , 0,
                \core\output\notification::NOTIFY_ERROR);
        }
    }
}
