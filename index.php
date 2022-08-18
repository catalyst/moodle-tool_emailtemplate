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

require_once(dirname(__FILE__) . '/../../../config.php');

require_login();

$userid = optional_param('userid', $USER->id, PARAM_INT);

$pluginname = get_string('pluginname', 'tool_emailtemplate');

$user = \core_user::get_user($userid, '*', MUST_EXIST);

$url = new moodle_url('/admin/tool/emailtemplate/index.php');
$context = context_system::instance();
$context = context_user::instance($user->id);
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_url($url);
$PAGE->navigation->extend_for_user($user);
$PAGE->navbar->add(get_string('profile'), new moodle_url('/user/profile.php', array('id' => $user->id)));
$PAGE->navbar->add($pluginname);

// Check for caps.
require_capability('tool/emailtemplate:view', context_system::instance());
echo $OUTPUT->header();
echo $OUTPUT->heading($pluginname);

$config = get_config('tool_emailtemplate');
$template = $config->template;

profile_load_data($user);
$data = user_get_user_details($user);

unset($data['preferences']);

// Set some convenient values.
$data['fullname'] = fullname($user);
$data['countryname'] = get_string($data['country'], 'countries');
$data['site'] = [
    'logocompact' => $OUTPUT->get_compact_logo_url()->out(),
    'fullname'  => $SITE->fullname,
    'shortname' => $SITE->shortname,
    'wwwroot'   => $CFG->wwwroot,
];

// Set a more convenient field but only if the profile image is set.
if (strpos($data['profileimageurl'], '/theme/') === false) {
    $data['avatar'] = $data['profileimageurl'];
}

// Make custom fields easier to reference.
if (isset($data['customfields']) ) {
    foreach ($data['customfields'] as $key => $value) {
        $data['custom_' . $value['shortname']] = $value['value'];
    }
}
unset($data['customfields']);

$html = $OUTPUT->render_from_template('tool_emailtemplate/email', $data);

// Clean up blank lines.
$html = preg_replace('/\s*($|\n)/', '\1', $html);
$rows = substr_count($html, "\n") + 2;

echo $OUTPUT->render_from_template('tool_emailtemplate/compose', [
    'footer' => $html,
    'from' => fullname($user) . ' <' .$user->email . '>',
]);

echo $OUTPUT->notification(get_string('usage', 'tool_emailtemplate'), 'info');

echo html_writer::tag('textarea', $html, ['rows' => $rows, 'style' => 'width: 100%; font-family:monospace; font-size: 10px']);

echo $OUTPUT->footer();

