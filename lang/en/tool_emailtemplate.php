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
 * Language strings
 *
 * @package    tool_emailtemplate
 * @author     Brendan Heywood <brendanheywood@catalyst-au.net>
 * @copyright  2022, Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Email footer template';
$string['pluginfile'] = 'Email footer template';

$string['configtemplate'] = 'Templates';
$string['configtemplate_help'] = 'A mustache template for the email template';
$string['copytoclipboard'] = 'Copy to clipboard';
$string['images'] = 'Images';
$string['imagesdesc'] = 'These images can be used in the email template. If the image filename is example.jpg then in the template use {{images.example}} without the extension. You can replace images in place and have the footers dynamcially replaced, but do NOT swap it\'s extension. If you have ever used an image then you should keep it indefinetly to no break old footers still being seen.';
$string['usage'] = 'To use this template first make sure that all of your user profile fields are filled in that this template might use such as mobile phone and social links. Then reload this page to see the latest version, then cut and paste the html below into your email client.';
