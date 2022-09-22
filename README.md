
[![ci](https://github.com/catalyst/moodle-tool_emailtemplate/workflows/ci/badge.svg)](https://github.com/catalyst/moodle-tool_emailtemplate/actions?query=workflow%3Aci)

# A html email footer generating plugin

## What is this plugin?

This is a very simple plugin that generates a chunk of html for each
user which they can use as their email footer. 

The email footer takes values from the user profile fields and can
include custom profile fields like social links.

The html is generated using a mustache template which can be customized.

## Configuring the plugin

The easiest way to get started as an administrator is to:

1) populate your own user profile with any data that you might want exposed
   in your email template
2) Visit the admin settings /admin/settings.php?section=manageemailtemplate and you
   will see a json data structure of all the data available to be used in your mustache template
3) Fill in the email mustache template and previewing it on your own profile page (see below). 
   For mustache syntax see: http://mustache.github.io/mustache.5.html
4) For best results consult with one of the many HTML email resources online around the best
   practice for authoring html in emails which can be very unintuitive
5) Test your email in real email clients and rinse and repeat as needed


## View the footer html as a normal user

Each user will get a new link on their profile to the new page:

/admin/tool/emailtemplate/index.php

Each user then gets shown a preview of what their email footer will
look like and the chunk of HTML they then need to configure in their
various email clients.
