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
    private function get_data(): array {
        global $CFG, $OUTPUT, $SITE;

        $user = $this->user;
        profile_load_data($user);
        $data = user_get_user_details($user);

        unset($data['preferences']);

        // Set some convenient values.
        $data['fullname'] = fullname($user);
        $data['countryname'] = get_string($data['country'], 'countries');
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
            $data['avatar'] = $data['profileimageurl'];
        }

        // Make custom fields easier to reference.
        if (isset($data['customfields'])) {
            foreach ($data['customfields'] as $value) {
                $data['custom_' . $value['shortname']] = $value['value'];
            }
        }
        unset($data['customfields']);

        // Load all images into template data.
        $fs = get_file_storage();
        $contextid = \context_system::instance()->id;
        $files = $fs->get_area_files($contextid, 'tool_emailtemplate', 'images');

        $data['images'] = [];
        foreach ($files as $file) {
            $filename = $file->get_filename();
            $shortfilename = pathinfo($filename, PATHINFO_FILENAME);
            if ($filename == '.') {
                continue;
            }
            $url = \moodle_url::make_pluginfile_url($contextid, 'tool_emailtemplate', 'images', $user->id, '/', $filename);
            $data['images'][$shortfilename] = $url->out();
        }

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
