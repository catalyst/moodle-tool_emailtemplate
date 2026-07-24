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
 * Form to manage the template.
 *
 * @package    tool_emailtemplate
 * @author     Benjamin Walker <benjaminwalker@catalyst-au.net>
 * @copyright  2024, Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_emailtemplate\form;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");

/**
 * Manage template form.
 */
class template extends \moodleform {
    /** @var array config settings attached to the form */
    protected const FORM_CONFIG = [
        'template',
        'global_vars',
    ];

    /** @var array config */
    protected $config;

    /**
     * Form definition
     *
     * @return void
     */
    public function definition(): void {
        global $CFG, $DB, $USER;

        $mform = $this->_form;

        // Template.
        // This needs to be a configtextarea and not a confightmleditor because
        // atto & html tidy will mangle the mustache tags.
        $mform->addElement('textarea', 'template', get_string('configtemplate', 'tool_emailtemplate'), [
            'cols' => 60,
            'rows' => 30,
        ]);
        $mform->setType('template', PARAM_RAW);

        $data = (new \tool_emailtemplate\footer($USER))->get_data();
        $data = '<pre>' . json_encode($data, JSON_PRETTY_PRINT) . '</pre>';
        $mform->addElement('static', 'template_help', '', get_string('configtemplate_help', 'tool_emailtemplate') . $data);

        // Global vars.
        $mform->addElement('textarea', 'global_vars', get_string('global_vars', 'tool_emailtemplate'), [
            'cols' => 60,
            'rows' => 8,
        ]);
        $mform->setType('global_vars', PARAM_RAW);
        $mform->addElement('static', 'global_vars_help', '', get_string('global_vars_desc', 'tool_emailtemplate'));

        // Images.
        $mform->addElement(
            'filemanager',
            'images',
            get_string('images', 'tool_emailtemplate'),
            null,
            $this->get_filemanager_options()
        );
        $mform->addElement('static', 'images_help', '', get_string('imagesdesc', 'tool_emailtemplate'));

        $this->add_action_buttons(false);
    }

    /**
     * Returns the filemanager options for this form.
     *
     * @return array filemanager options.
     */
    public static function get_filemanager_options(): array {
        return [
            'accepted_types' => ['web_image'],
            'maxbytes' => 0,
            'subdirs' => 0,
            'maxfiles' => 8,
        ];
    }

    /**
     * Loads the current values of the form config.
     * @return void
     */
    public function load_form_config(): void {
        $config = [];
        foreach (self::FORM_CONFIG as $name) {
            $config[$name] = get_config('tool_emailtemplate', $name);
        }

        // Also load the images id.
        $draftitemid = file_get_submitted_draft_itemid('images');
        file_prepare_draft_area(
            $draftitemid,
            \context_system::instance()->id,
            'tool_emailtemplate',
            'images',
            0,
            self::get_filemanager_options()
        );
        $config['images'] = $draftitemid;

        // Save empty config values but don't set them. Needed for comparisons.
        $this->config = $config;
        $this->set_data(array_filter($config));
    }

    /**
     * Saves the new form data to config.
     * @param \stdClass $data submitted data
     * @return void
     */
    public function save_form_config(\stdClass $data): void {
        foreach ($data as $key => $value) {
            if (in_array($key, self::FORM_CONFIG) && $value !== $this->config[$key]) {
                set_config($key, $value, 'tool_emailtemplate');
                add_to_config_log($key, (string) $this->config[$key], $value, 'tool_emailtemplate');
            }
        }

        // Save draft images.
        if (isset($data->images)) {
            file_save_draft_area_files(
                $data->images,
                \context_system::instance()->id,
                'tool_emailtemplate',
                'images',
                0,
                self::get_filemanager_options()
            );
        }
    }
}
