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
$PAGE->set_heading($pluginname);
$PAGE->set_title($pluginname);
$PAGE->navigation->extend_for_user($user);
$PAGE->navbar->add(get_string('profile'), new moodle_url('/user/profile.php', array('id' => $user->id)));
$PAGE->navbar->add($pluginname);

// Check for caps.
require_capability('tool/emailtemplate:view', context_system::instance());
echo $OUTPUT->header();
echo $OUTPUT->heading($pluginname);

$footer = new tool_emailtemplate\footer($user);
$html = $footer->get_html();

echo $OUTPUT->render_from_template('tool_emailtemplate/compose', [
    'footer' => $html,
    'from' => fullname($user) . ' <' .$user->email . '>',
]);
$rows = substr_count($html, "\n") + 2;
echo $OUTPUT->notification(get_string('usage', 'tool_emailtemplate'), 'info');

echo html_writer::tag('button',
    $OUTPUT->pix_icon('t/copy', '') . ' ' . get_string('copytoclipboard', 'tool_emailtemplate'),
    ['id' => 'copy', 'class' => 'btn btn-primary']);
echo html_writer::tag('textarea', $html, ['id' => 'email-template', 'rows' => $rows, 'style' => 'width: 100%; font-family:monospace; font-size: 10px']);

echo <<<EOF
<script>
document.getElementById('copy').addEventListener('click', function(e) {
    document.getElementById('email-template').select();
    document.execCommand('copy');
});
</script>
EOF;

echo $OUTPUT->footer();

