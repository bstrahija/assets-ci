<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Assets Helper
 * The function names are pretty self-explanatory
 *
 * @author 		Boris Strahija <boris@creolab.hr>
 * @copyright 	Copyright (c) 2012, Boris Strahija, http://creolab.hr
 * @version 	1.5.0
 */


function assets_css($assets = null, $attributes = null)
{
	Assets::css($assets, $attributes);
}


function assets_css_group($group = null, $assets = null, $attributes = null)
{
	Assets::css_group($group, $assets, $attributes);
}


function assets_js($assets = null)
{
	Assets::js($assets);
}


function assets_js_group($group = null, $assets = null)
{
	Assets::js_group($group, $assets);
}


function assets_url($path = null)
{
	return Assets::url($path);
}


function assets_img($path = null, $tag = false, $properties = null)
{
	return Assets::img($path, $tag, $properties);
}


function assets_cdn($assets = null)
{
	Assets::cdn($assets);
}


function assets_conditional($condition = null, $string = null)
{
	Assets::conditional($condition, $string);
}


function clear_assets_cache($type = null)
{
	Assets::clear_cache($type);
}


/* End of file assets_helper.php */