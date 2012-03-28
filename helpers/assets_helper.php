<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Assets Helper
 * The function are pretty self-explanatory
 *
 * @author 		Boris Strahija <boris@creolab.hr>
 * @copyright 	Copyright (c) 2012, Boris Strahija, http://creolab.hr
 * @version 	1.0.0
 */


function assets_css($assets = null)
{
	Assets::css($assets);
}


function assets_js($assets = null)
{
	Assets::js($assets);
}


function assets_url($path = null)
{
	return Assets::url($path);
}


function assets_img($path = null, $tag = false, $properties = null)
{
	return Assets::img($path, $tag, $properties);
}


function clear_assets_cache($type = null)
{
	Assets::clear_cache($type);
}


/* End of file assets_helper.php */