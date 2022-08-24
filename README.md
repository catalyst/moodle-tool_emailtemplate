
[![ci](https://github.com/catalyst/moodle-tool_emailtemplate/workflows/ci/badge.svg)](https://github.com/catalyst/moodle-tool_emailtemplate/actions?query=workflow%3Aci)

# A html email footer generating plugin

## What is this plugin?

This is a very simple plugin that generates a chunk of html for each
user which they can use as their email footer. 

The email footer takes values from the user profile fields and can
include custom profile fields like social links.

The html is generated using a mustache template which can be customized.

## View the footer html

Each user will get a new link on their profile to the new page:

/admin/tool/emailtemplate/index.php

Each user then gets shown a preview of what their email footer will
look like and the chunk of HTML they then need to configure in their
various email clients.
