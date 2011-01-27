<?php

/*
|--------------------------------------------------------------------------
| Combine / Minify / Less
|--------------------------------------------------------------------------
|
| Flags whether files should be combined or minified, and css parsed with less.
|
*/

$config['assets']['env'] 		= 'production'; // Environment (if it's set to 'dev', no processing will be done)
$config['assets']['minify_js'] 	= true;
$config['assets']['minify_css'] = true;
$config['assets']['less_css'] 	= true;


/*
|--------------------------------------------------------------------------
| Default paths and directories
|--------------------------------------------------------------------------
|
| Default directories containing the assets
|
*/

$config['assets']['assets_dir'] = 'assets';
$config['assets']['js_dir'] 	= 'js';
$config['assets']['css_dir'] 	= 'css';
$config['assets']['cache_dir'] 	= 'cache';


