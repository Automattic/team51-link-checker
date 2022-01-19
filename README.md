# Team51 Link Checker

WordPress plugin to identify issues with links (400, 404, etc.) and surface linked domains like staging or development.

## Current status

Right now, the plugin is able to crawl all internal links of the website where it is installed.
After the crawl process, it lists all the URLs by status code.

### Installation
At the moment, to install this plugin you have to clone this repo and then run `composer install` to download all PHP packages (particularly, spatie/crawler)

After this, you can Compress the whole folder and upload it from the Plugins page of your WordPress site.

### Technical notes
This tool uses `spatie/crawler` library to crawl the website. The chosen version for this library is `6.0.2`, since versions `7.0.0+` require PHP 8.0, which not all Pressable WordPress sites have configured.