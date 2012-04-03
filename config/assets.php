<?php

/*
|--------------------------------------------------------------------------
| Combine / Minify / Less
|--------------------------------------------------------------------------
|
| Flags whether files should be combined and minified
|
*/

$config['assets']['combine_css']          = true;
$config['assets']['combine_js']           = true;
$config['assets']['minify_css']           = true;
$config['assets']['minify_js']            = true;
$config['assets']['enable_coffeescript']  = false;

/*
|--------------------------------------------------------------------------
| Cache
|--------------------------------------------------------------------------
|
| Define if the cache folder should be cleared when generating new cache files
| 
*/

$config['assets']['auto_clear_cache']     = true;
$config['assets']['auto_clear_css_cache'] = false;
$config['assets']['auto_clear_js_cache']  = false;

/*
|--------------------------------------------------------------------------
| Default paths and directories, tags
|--------------------------------------------------------------------------
|
| Default directories containing the assets
| Option to use HTML5 tags
|
*/

$config['assets']['assets_dir'] = 'assets';
$config['assets']['js_dir']     = 'js';
$config['assets']['css_dir']    = 'css';
$config['assets']['cache_dir']  = 'cache';
$config['assets']['img_dir']    = 'img';
$config['assets']['html5']      = true;

/*
|--------------------------------------------------------------------------
| Cache prefix
|--------------------------------------------------------------------------
|
| This prefix is added to cache files instead of the default timestamp
| By default only the timestamp will be added, but if you setup a custom 
| prefix, you can choose to remove the timestamp
|
*/

$config['assets']['prefix_css']       = null;
$config['assets']['prefix_js']        = null;
$config['assets']['prefix_timestamp'] = true;

