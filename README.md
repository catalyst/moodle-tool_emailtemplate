
[![ci](https://github.com/catalyst/moodle-tool_emailtemplate/workflows/ci/badge.svg)](https://github.com/catalyst/moodle-tool_emailtemplate/actions?query=workflow%3Aci)

* [What is this?](#what-is-this)
* [Branches](#branches)
* [Configuration](#configuration)
* [Using the email template](#using-the-email-template)
* [Support](#support)
* [Credits](#credits)

# A html email footer generating plugin

## What is this?

This is a very simple plugin that generates a chunk of html for each
user which they can use as their email footer. 

The email footer takes values from the user profile fields and can
include custom profile fields like social links.

The html is generated using a mustache template which can be customized.

## Branches

| Moodle version    | Branch             |
| ----------------- | ------------------ |
| Moodle 3.9+       | `MOODLE_39_STABLE` |

## Configuration

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


## Using the email template

Each user will get a new link on their profile to the new page:

/admin/tool/emailtemplate/index.php

Each user then gets shown a preview of what their email footer will
look like and the chunk of HTML they then need to configure in their
various email clients.

## Image support

Images such as a brand can be uploaded and references from the template. These images
will be served from the Moodle domain. The images can also be replaced at any time but
care needs to be take as they will be replaced not just in new emails but in all historical
emails as well. This capability is very useful for things like an image which can be
swapped out centrally that all users get without extra effort.

The link the image would go to needs to be hard coded into the template but this plugin pairs
well with tool_redirects for creating a generic url which can be redirected to somewhere else.
For instance this month you could have an image for a conference and the redirect takes you
to the conference page, and then next month the image is for a course offering and takes you
to the enrolment page.

https://github.com/catalyst/moodle-tool_redirects


## Support

If you have issues please log them in
[GitHub](https://github.com/catalyst/moodle-auth_saml2/issues).

Please note our time is limited, so if you need urgent support or want to
sponsor a new feature then please contact
[Catalyst IT Australia](https://www.catalyst-au.net/contact-us).


## Credits

This plugin was developed by [Catalyst IT Australia](https://www.catalyst-au.net/).

<img alt="Catalyst IT" src="https://cdn.rawgit.com/CatalystIT-AU/moodle-auth_saml2/MOODLE_39_STABLE/pix/catalyst-logo.svg" width="400">
