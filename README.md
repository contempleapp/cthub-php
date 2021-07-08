# cthub-php
PHP hub for Contemple
[www.contemple.app](http://www.contemple.app)

CTHub works together with the contemple app to enable
Theme installation and content Publishing on any Webserver 
with PHP installed.

Without a hub, contemple can still be used to generate a local static website without any Webserver

## Hub Installation:

- Copy your theme.zip file into the cthub-php folder
- Modify cthub.php file to your server filesystem
- Modify install.xml to handle your Theme wich can be installed from your webserver
- upload the cthub-php folder to your webserver

[www.contemple.app/docs/hub-tutorial.html](http://www.contemple.app/docs/hub-tutorial.html)

## Hub Usage:

After the installtion is done, anybody can use the contemple app to connect to your website, install your website theme and publish new content on your website
- Start the contemple app
- Enter the path to cthub-php folder and cthub.php script: eg: https://mywebsite.com/cthub-php/
- Contemple should find and install the Theme found on the server

## Update Contemple Hub on existing webserver

If you already installed an older version on your webserver,
just upload the *hub.php* file from the latest version to your webserver


## Version 1.2 Update Notes

- Version 1.2 introduces a new API for interacting with the hub
- Version 1.2 also fixes some security issues with File Uploads, all users should upgrade to the latest version of cthub-php
- Version 1.2 is fully compatible with all versions of the Contemple app


