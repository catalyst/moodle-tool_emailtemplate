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

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    if ($ADMIN->fulltree) {

        $settings = new admin_settingpage('manageemailtemplate', new lang_string('pluginfile', 'tool_emailtemplate'));
        $ADMIN->add('tools', $settings);

        // TODO turn the mustache template in code into config here.
        $settings->add(new admin_setting_configtextarea(
            'tool_emailtemplate/template',
            get_string('configtemplate', 'tool_emailtemplate'),
            get_string('configtemplate_help', 'tool_emailtemplate'),
            ''
        ));

        $settings = null;
    }
}