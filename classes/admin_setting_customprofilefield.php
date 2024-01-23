<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace tool_emailtemplate;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/adminlib.php');

use admin_setting_configselect;

/**
 * Custom profile field admin setting. Allows selection of a profile customfield from the config.
 *
 * @package   tool_emailtemplate
 * @author    Matthew Hilton (matthewhilton@catalyst-au.net)
 * @copyright Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class admin_setting_customprofilefield extends admin_setting_configselect {
    /**
     * Constructs the customfield config setting.
     *
     * @param string $name
     * @param string $visiblename
     * @param string $description
     * @param string $defaultsetting
     */
    public function __construct(string $name, string $visiblename, string $description, string $defaultsetting) {
        $defaultfield = [
            $defaultsetting => get_string('customprofilefield:default', 'tool_emailtemplate')
        ];

        // Get the names of all the custom course fields.
        $customfields = profile_get_custom_fields();

        $fieldoptions = [];
        $validtypes = ['datetime', 'text'];
        foreach ($customfields as $field) {
            if (!in_array($field->datatype, $validtypes)) {
                continue;
            }
            $fieldoptions[$field->shortname] = get_string('customprofilefield:displayname',
                'tool_emailtemplate', $field);
        }

        $options = $defaultfield + $fieldoptions;
        parent::__construct($name, $visiblename, $description, $defaultsetting, $options);
    }
}
