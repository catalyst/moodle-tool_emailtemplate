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

namespace tool_emailtemplate;

use core_user\external\user_summary_exporter;

/**
 * A footer
 */
class footer {

    /**
     * @var user
     */
    private $user;

    /**
     * Create a footer for a specific user
     * @param user $user the user
     */
    public function __construct($user) {
        $this->user = $user;
    }

    /**
     * Get the data to be used as the mustache context
     * @return array of context data
     */
    public function get_data(): array {
        global $CFG, $DB, $OUTPUT, $SITE;

        require_once($CFG->dirroot . '/user/lib.php');
        require_once($CFG->dirroot . '/user/profile/lib.php');

        $user = $this->user;
        profile_load_data($user);

        $userexporter = new user_summary_exporter($user);
        $profile = $userexporter->export($OUTPUT);
        $data = array_merge((array)$profile, (array)$user);

        // Set some convenient values.
        $data['fullname'] = fullname($user);
        if (isset($data['country'])) {
            $data['countryname'] = get_string($data['country'], 'countries');
        }

        $data['site'] = [
            'fullname'  => $SITE->fullname,
            'shortname' => $SITE->shortname,
            'wwwroot'   => $CFG->wwwroot,
        ];
        if (isset($OUTPUT->get_compact_logo_url)) {
            $data['logocompact'] = $OUTPUT->get_compact_logo_url()->out();
        }

        // Set a more convenient field but only if the profile image is set.
        if (strpos($data['profileimageurl'], '/theme/') === false) {

            // Always set gravatar.
            $data['gravatar'] = $data['profileimageurl'];

            // Only set avatar if url is local.
            if (strpos($data['profileimageurl'], $CFG->wwwroot) !== false) {
                $data['avatar'] = $data['profileimageurl'];
            }

        }

        // Make custom fields easier to reference.
        if (isset($data['customfields'])) {
            foreach ($data['customfields'] as $value) {
                $data['custom_' . $value['shortname']] = $value['value'];
            }
        }
        unset($data['auth']);
        unset($data['access']);
        unset($data['confirmed']);
        unset($data['customfields']);
        unset($data['enrol']);
        unset($data['enrolledcourses']);
        unset($data['firstaccess']);
        unset($data['lastaccess']);
        unset($data['lastcourseaccess']);
        unset($data['mailformat']);
        unset($data['preference']);
        unset($data['preferences']);
        unset($data['suspended']);
        unset($data['sesskey']);
        unset($data['userselectors']);

        // Load all images into template data.
        $fs = get_file_storage();
        $contextid = \context_system::instance()->id;
        $files = $fs->get_area_files($contextid, 'tool_emailtemplate', 'images');

        $data['images'] = [];

        $sql = "SELECT MAX(timemodified) AS timemodified
                  FROM {config_log}
                 WHERE plugin = 'tool_emailtemplate' AND name = 'template'";
        $record = $DB->get_record_sql($sql);
        $lastupdated = $record->timemodified ?? time();

        foreach ($files as $file) {
            $filename = $file->get_filename();
            $shortfilename = pathinfo($filename, PATHINFO_FILENAME);
            if ($filename == '.') {
                continue;
            }

            $info = $user->username . '-' . userdate($lastupdated, get_string('dateformat', 'tool_emailtemplate'));
            $url = \moodle_url::make_pluginfile_url($contextid, 'tool_emailtemplate', 'images', $info, '/', $filename);
            $data['images'][$shortfilename] = $url->out();
        }

        ksort($data);

        return $data;
    }

    /**
     * Gets a raw mustache engine
     *
     * @return \Mustache_Engine
     */
    private function get_mustache() {
        $mustache = new \Mustache_Engine([
            'escape' => 's',
            'pragmas' => [\Mustache_Engine::PRAGMA_BLOCKS],
        ]);
        return $mustache;
    }

    /**
     * Get the personalised email footer
     * @return string html
     */
    public function get_html(): string {
        $mustache = $this->get_mustache();
        $data = $this->get_data();
        $config = get_config('tool_emailtemplate');
        $footer = $mustache->render($config->template, $data);

        // Clean up blank lines.
        $footer = preg_replace('/\s*($|\n)/', '\1', $footer);
        $footer = preg_replace('/  /', ' ', $footer);

        return $footer;
    }

}
