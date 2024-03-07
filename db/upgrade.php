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
 * DB upgrade script.
 *
 * @package    tool_emailtemplate
 * @author     Benjamin Walker <benjaminwalker@catalyst-au.net>
 * @copyright  2024, Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrade
 * @param int $oldversion
 */
function xmldb_tool_emailtemplate_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2024022900) {

        // Define table tool_emailtemplate_tracking to be created.
        $table = new xmldb_table('tool_emailtemplate_tracking');

        // Adding fields to table tool_emailtemplate_tracking.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('version', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('lastloaded', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table tool_emailtemplate_tracking.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('userid', XMLDB_KEY_FOREIGN, ['userid'], 'user', ['id']);
        $table->add_key('userid_unique', XMLDB_KEY_UNIQUE, ['userid']);

        // Conditionally launch create table for tool_emailtemplate_tracking.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Emailtemplate savepoint reached.
        upgrade_plugin_savepoint(true, 2024022900, 'tool', 'emailtemplate');
    }

    return true;
}
