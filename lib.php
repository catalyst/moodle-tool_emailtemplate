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
 * Version
 *
 * @package    tool_emailtemplate
 * @author     Brendan Heywood <brendanheywood@catalyst-au.net>
 * @copyright  2022, Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


/**
 * Add nodes to myprofile page.
 *
 * @param \core_user\output\myprofile\tree $tree Tree object
 * @param stdClass $user user object
 * @param bool $iscurrentuser
 * @param stdClass $course Course object
 * @return bool
 */
function tool_emailtemplate_myprofile_navigation(core_user\output\myprofile\tree $tree, $user, $iscurrentuser, $course) {
    if (has_capability('tool/emailtemplate:view', context_system::instance())) {
        $url = new moodle_url('/admin/tool/emailtemplate/index.php', ['userid' => $user->id]);
        $node = new core_user\output\myprofile\node('miscellaneous', 'emailtemplate',
            get_string('pluginname', 'tool_emailtemplate'), null, $url);
        $tree->add_node($node);
    }
}

/**
 * Serves email image
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param context $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @return bool|null false if file not found, does not return anything if found - just send the file
 */
function tool_emailtemplate_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload) {
    global $CFG;

    require_once($CFG->libdir . '/filelib.php');

    // Email images must be public so no login of capability checks.

    if ($filearea === 'images') {

        $fullpath = '/' . $context->id . '/tool_emailtemplate/images/0/' . $args[1];

        $fs = get_file_storage();
        if (!$file = $fs->get_file_by_hash(sha1($fullpath)) || $file->is_directory()) {
            return false;
        }

        // Cache them for 1 day.
        send_stored_file($file, DAYSECS, 0, false, [
            'cacheability' => 'public',
        ]);
    }
}
