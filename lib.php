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
    global $CFG, $DB;

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

        // Update tracking information if enabled and the file is viewed outside of Moodle.
        if (!empty(get_config('tool_emailtemplate', 'tracking')) && !isloggedin()) {
            tool_emailtemplate_update_tracking($args[0]);
        }

        // Cache them for 1 day. Most email clients will also proxy and cache
        // the images for a day or so as well.
        send_stored_file($file, DAYSECS, 0, false, [
            'cacheability' => 'public',
        ]);

    } else if ($filearea === 'avatar') {
        // Public endpoint serving a user's profile avatar for use in email footers.
        // No login required - this is intentionally public and only exposes avatar images.
        // Resolved at serve time so changes to the user's profile picture are reflected.
        \core\session\manager::write_close();

        $userid = (int) array_shift($args);
        $filename = array_shift($args) ?: 'f1';

        // Only allow the known avatar size identifiers.
        if (!in_array($filename, ['f1', 'f2', 'f3'])) {
            return false;
        }

        $usercontext = \context_user::instance($userid, IGNORE_MISSING);
        if ($usercontext) {
            $fs = get_file_storage();
            $file = $fs->get_file($usercontext->id, 'user', 'icon', 0, '/', $filename . '.png');
            if (!$file) {
                $file = $fs->get_file($usercontext->id, 'user', 'icon', 0, '/', $filename . '.jpg');
            }
            if ($file && !$file->is_directory()) {
                send_stored_file($file, DAYSECS * 7, 0, false, ['cacheability' => 'public']);
            }
        }

        // No local image - fall back to Gravatar if enabled.
        if (!empty($CFG->enablegravatar)) {
            $user = $DB->get_record('user', ['id' => $userid], 'id, email', IGNORE_MISSING);
            if ($user && !empty($user->email)) {
                $sizemap = ['f2' => 35, 'f1' => 100, 'f3' => 512];
                $size = $sizemap[$filename];

                $md5 = md5(strtolower(trim($user->email)));
                $cachedir = $CFG->tempdir . '/tool_emailtemplate';
                $cachefile = $cachedir . '/' . $md5 . '_' . $size;

                if (file_exists($cachefile) && filemtime($cachefile) > time() - DAYSECS) {
                    $imagedata = file_get_contents($cachefile);
                    $mimetype = mime_content_type($cachefile) ?: 'image/jpeg';
                } else {
                    $gravatardefault = !empty($CFG->gravatardefaulturl) ? $CFG->gravatardefaulturl : '404';
                    $gravatarurl = "https://www.gravatar.com/avatar/{$md5}?s={$size}&d=" . urlencode($gravatardefault);

                    $curl = new \curl();
                    $curl->setopt(['CURLOPT_FOLLOWLOCATION' => true]);
                    $imagedata = $curl->get($gravatarurl);

                    if (!$imagedata || $curl->get_errno()) {
                        return false;
                    }

                    $info = $curl->get_info();
                    $mimetype = explode(';', $info['content_type'] ?? 'image/jpeg')[0];

                    make_writable_directory($cachedir);
                    file_put_contents($cachefile, $imagedata);
                }

                header('Content-Type: ' . $mimetype);
                header('Cache-Control: public, max-age=' . DAYSECS);
                header('Content-Length: ' . strlen($imagedata));
                echo $imagedata;
                die;
            }
        }

        return false;
    }
}

/**
 * Stores tracking information about footer image version into the database.
 *
 * @param string $info
 * @return void
 */
function tool_emailtemplate_update_tracking($info) {
    global $DB;

    // Confirm data is formatted correctly and contains the required info.
    $date = date('Y-m-d');
    $datelen = strlen($date);
    if (strlen($info) < ($datelen + 1) || !str_contains($info, '-')) {
        return;
    }

    // Grab the user from the username. Doesn't handle cases where it's not unique.
    $username = substr($info, 0, -$datelen - 1);
    $user = $DB->get_record('user', array('username' => $username));
    if (empty($user)) {
        return;
    }

    // Verify that the remaining part of the string is a valid version id (date).
    $version = strtotime(substr($info, -$datelen));
    if (empty($version) || $version > time()) {
        return;
    }

    // Use date timestamp rather than time so it's only updated once per day max.
    $lastloaded = strtotime($date);

    // Load previous record for user.
    $tracking = $DB->get_record('tool_emailtemplate_tracking', ['userid' => $user->id]);

    // If we have no tracking information, create new record.
    if (empty($tracking)) {
        $trackinginfo = [
            'userid' => $user->id,
            'version' => $version,
            'lastloaded' => $lastloaded,
        ];
        $DB->insert_record('tool_emailtemplate_tracking', $trackinginfo);
        return;
    }

    // Otherwise check if the existing record needs updating.
    $update = false;

    // Version info can come from outdated links, so only update when a higher version is detected.
    if (isset($tracking->version) && $version > $tracking->version) {
        $tracking->version = $version;
        $update = true;
    }

    // Lastloaded is a date timestamp so only needs to be updated once per day.
    if (isset($tracking->lastloaded) && $lastloaded > $tracking->lastloaded) {
        $tracking->lastloaded = $lastloaded;
        $update = true;
    }

    if ($update) {
        $DB->update_record('tool_emailtemplate_tracking', $tracking);
    }
}
