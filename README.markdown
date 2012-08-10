# Simple Assets Library

A simple assets library that has the ability to combine and minify your JavaScript and CSS assets.
Additionally there's a <a href="http://leafo.net/lessphp/">LessCSS</a> compiler and a <a href="https://github.com/alxlit/coffeescript-php">CoffeeScript</a> compiler.

## Third Party Libraries

The libraries <a href="https://github.com/rgrove/jsmin-php/">JSMin</a>, <a href="http://code.google.com/p/cssmin/">CSSMin</a>, <a href="http://leafo.net/lessphp/">LessPHP</a> and <a href="https://github.com/alxlit/coffeescript-php">CoffeeScript-PHP</a> are all created by third parties, but they're included in this package for convenience.

## Requirements

1. PHP 5.3+
2. CodeIgniter 2.1
3. Directory structure for the assets files, with a writeable cache directory

## Documentation

Set all your preferences in the config file (assets directories, options to minify and combine).
Now you can use the helper methods in your views like this:
	
	<?php Assets::css(array('bootstrap.less', 'init.css', 'style.css')); ?>
	<?php Assets::js(array('libs/jquery.js', 'script.js', 'bean.coffee')); ?>

You can load the javascript files from the CDN using:

	<?php Assets::cdn(array('jquery','jquery-validate','jqueryui'));?>

There's also a method for clearing all cached files:
	
	<?php Assets::clear_cache(); ?>

The default configuration assumes your assets directory is in the root of your project. Be sure to set the permissions for the cache directory so it can be writeable.

Note about "freeze" option: This basicly tells the lib not to scan the files and folders for new and changed files, but to pull all the info from the info.cache file. This speeds up the whole process a little bit. Useful for apps with a bigger load in production.

### LESS / CoffeeScript

Files with extensions .less and .coffee will automatically be processed through appropriate libraries.

### Groups

There's also a possibility to define groups of assets. This can be useful when for e.g. you want separate scripts in you page header, and others in the footer. This can be accomplished like this:

    <?php Assets::js_group('head',   array('libs/modernizr.js')); ?>
    <?php Assets::js_group('footer', array('plugins.js', 'script.js')); ?>
    
The same thing will work with CSS files. You can use this to show groups of CSS files for specific pages:

    <?php Assets::css_group('global', array('style.css')); ?>
    <?php Assets::css_group('login',  array('login.css')); ?>

### Importing CSS files (@import)

Including files via *@import* should work just fine, just be sure to use proper paths. Example of a stylesheet would be something like this:

    @import "bootstrap/bootstrap.less";
    @import "libs/fancybox.css";
    
    body { background: #f2f2f2; }
    
    …

Just keep in mind when using *@import* that those files are not scanned for changes and the cache wont be cleared in case you change a file that is included via *@import*.

### Images

A helper is also provided to display images from the directory setup in the config.

    <?php echo Assets::img('logo.png'); ?>

You can also generate the img tag directly using a similar syntax as in the CodeIgniter HTML helper.

    <?php echo Assets::img('logo.png', true, array('title' => 'Logo')); ?>

### Overriding CI base_url
By default Assets uses codeIgniter's `$config['base_url']` config to determine the URL for your assets. However this can be overwritten by defining the following configuration item:
```php
$config['assets']['base_url'] = 'https://example.com';
```
This will allow you to define your assets on a seperate static domain, or specify `https` for assets seperately from your CI application.

## Frameworks / Libraries

The library has been tested with Twitter Bootstrap 2.0.1 and HTML5 Boilerplate 3.0. It wont work with the latest Bootstrap 2.0.2 because of a problem in LessPHP. I hope this will be fixed soon. And if you happen to use the library with a different framework (bootstrap), give me a shout and I'll put it on this list. So here it is:

* Twitter Bootstrap 2.0.1 (2.0.2 not working yet) LESS
* HTML5 Boilerplate 3.0

## Directory structure example

	/application
	/assets
		/cache
		/css
		/images
		/js
	/sparks
	/system
