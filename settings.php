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
 * Settings
 *
 * @package    tool_emailtemplate
 * @author     Brendan Heywood <brendanheywood@catalyst-au.net>
 * @copyright  2022, Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use tool_emailtemplate\admin_setting_customprofilefield;

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {

    $settings = new admin_settingpage('manageemailtemplate', new lang_string('pluginfile', 'tool_emailtemplate'));
    $ADMIN->add('tools', $settings);

    // This needs to be a configtextarea and not a confightmleditor because
    // atto & html tidy will mangle the mustache tags.

    $data = (new \tool_emailtemplate\footer($USER))->get_data();
    $data = '<pre>' . json_encode($data, JSON_PRETTY_PRINT) . '</pre>';

    $settings->add(new admin_setting_configtextarea(
        'tool_emailtemplate/template',
        get_string('configtemplate', 'tool_emailtemplate'),
        get_string('configtemplate_help', 'tool_emailtemplate') . $data,
        '',
        PARAM_RAW,
        60,
        30
    ));

    $settings->add(new admin_setting_configstoredfile(
        'tool_emailtemplate/images',
        get_string('images', 'tool_emailtemplate'),
        get_string('imagesdesc', 'tool_emailtemplate'),
        'images',
        0,
        ['maxfiles' => 8, 'accepted_types' => ['web_image']]
    ));

    $settings->add(new admin_setting_configcheckbox(
        'tool_emailtemplate/tracking',
        get_string('tracking', 'tool_emailtemplate'),
        get_string('trackingdesc', 'tool_emailtemplate'),
        0
    ));

    $settings = null;
}
