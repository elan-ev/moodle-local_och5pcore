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
defined('MOODLE_INTERNAL') || die;

global $ADMIN, $CFG;

if ($hassiteconfig) {

    require_once($CFG->dirroot.'/local/och5p_core/lib.php');

    $settings = new admin_settingpage( 'local_och5p_core_settings', get_string('title_settings', 'local_och5p_core'));

    $available_themes = array();

    foreach (\core_component::get_plugin_list('theme') as $name => $dir) {
        $available_themes[$name] = ucfirst(str_replace('_', ' ', $name));
    }

    $setting = new admin_setting_configmultiselect(
        'local_och5p_core/extended_themes',
        get_string('setting_extended_themes', 'local_och5p_core'),
        get_string('setting_extended_themes_desc', 'local_och5p_core'), 
        array(), 
        $available_themes
    );

    $setting->set_updatedcallback('local_och5p_core_extend_themes');
    $settings->add($setting);

    $ADMIN->add( 'localplugins', $settings );
}