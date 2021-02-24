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

require_once($CFG->dirroot . '/local/och5p_core/och5p_core.php');

defined('MOODLE_INTERNAL') || die;

function local_och5p_core_extend_themes() {

    $returnurl = new \moodle_url('/admin/settings.php?section=local_och5p_core_settings');

    $to_be_extended_themes_str = get_config('local_och5p_core', 'extended_themes');
    $to_be_extended_themes = !empty($to_be_extended_themes_str) ? explode(',', $to_be_extended_themes_str) : array();

    $available_themes = \core_component::get_plugin_list('theme');

    $other_themes = array_diff(array_keys($available_themes), $to_be_extended_themes);

    if (count($to_be_extended_themes)) {
        $extended_themes = och5p_core_extend_themes($to_be_extended_themes);
        if ($diffs = array_diff($to_be_extended_themes, $extended_themes)) {
            $theme_names = array();
            foreach ($diffs as $diff) {
                $theme_names[] = ucfirst(str_replace('_', ' ', $diff));
            }
            $error_message = sprintf(get_string('extended_error', 'local_och5p_core'), implode(', ', $theme_names));
            redirect($returnurl,$error_message , 0,
                \core\output\notification::NOTIFY_ERROR);
        }
    }

    if (count($other_themes)) {
        $unextended_themes = och5p_core_unenxtend_themes($other_themes);
    }

}

