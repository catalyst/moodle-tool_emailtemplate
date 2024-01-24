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
    if (has_capability('tool/emailtemplate:view', context_system::instance()) &&
            !empty(get_config('tool_emailtemplate', 'template'))) {
        $url = new moodle_url('/admin/tool/emailtemplate/index.php', ['userid' => $user->id]);
        $node = new core_user\output\myprofile\node('contact', 'emailtemplate',
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

    // Email images must be public so no login or capability checks.
    if ($filearea === 'images') {

        $fullpath = '/' . $context->id . '/tool_emailtemplate/images/0/' . $args[1];

        $fs = get_file_storage();
        if (!$file = $fs->get_file_by_hash(sha1($fullpath))) {
            return false;
        }
        if ($file->is_directory()) {
            return false;
        }

        tool_emailtemplate_update_timestamp($args[0]);

        // Cache them for 1 day. Most email clients will also proxy and cache
        // the images for a day or so as well.
        send_stored_file($file, DAYSECS, 0, false, [
            'cacheability' => 'public',
        ]);
    }
}

/**
 * Stores the date when the footer image was last updated into a custom field.
 *
 * @param string $info
 * @return void
 */
function tool_emailtemplate_update_timestamp($info) {
    GLOBAL $CFG, $DB;

    require_once($CFG->dirroot . '/user/profile/lib.php');

    $customfield = get_config('tool_emailtemplate', 'lastupdated');
    $datelen = strlen(userdate(time(), get_string('dateformat', 'tool_emailtemplate')));
    if (empty($customfield) || strlen($info) < ($datelen + 1) || !str_contains($info, '-')) {
        return;
    }

    $username = substr($info, 0, -$datelen - 1);
    $date = substr($info, -$datelen);

    // Grab the user from the username. Doesn't handle cases where it's not unique.
    $user = $DB->get_record('user', array('username' => $username));
    if (empty($user)) {
        return;
    }

    profile_load_data($user);

    $profilefieldname = 'profile_field_' . $customfield;
    $currenttimestamp = $user->$profilefieldname;

    try {
        $date = new \DateTime($date);
        $timestamp = $date->getTimestamp();
        // Only save if later than the most recent saved timestamp.
        if (isset($currenttimestamp) && $timestamp > $currenttimestamp) {
            profile_save_custom_fields($user->id, [$customfield => $timestamp]);
        }
    } catch (\Exception $e) {
        // Invalid date formatting, don't save the custom field.
        debugging($e->getMessage());
    }
}
