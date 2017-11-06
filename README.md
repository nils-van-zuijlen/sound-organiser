SoundOrganiser
==============

[![Dependency Status](https://gemnasium.com/badges/github.com/nils-van-zuijlen/sound-organiser.svg)](https://gemnasium.com/github.com/nils-van-zuijlen/sound-organiser)

A __Symfony__ project created on June 6, 2016, 11:44 am.

It was not a git repository at that time.

Will be at [this location on the WWW](http://nils.xif.fr/soundorganiser)

SoundOrganiser est une solution pour la diffusion de musique/sons/bruitages lors de spectacles.

## Installation

### Downloading

You can download it from GitHub or clone this repository.

### Dependiencies

After downloading, you must install the dependiencies, I recommend using Composer.
You have to update the configuration in app/config/parameters.yml
Then you'll have to run:
```
php bin/console assets:install
php bin/console dump:emoticons
php bin/console doctrine:schema:update
```

### Server Configuration

* your server **must** be able to run [Symfony](http://symfony.com) projects (use web/config.php file to check it)
* the `php.ini` `upload_max_filesize` must be at `11M`
* `var` folder must be with editable and readable by your server user
* `src/Xif/FileBundle/Uploads` folder must be with editable and readable by your server user too
* your server must be able to read .htaccess files and to do [URL Rewriting](http://httpd.apache.org/docs/2.0/misc/rewriteguide.html)
