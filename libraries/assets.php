<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');	
	
/**
 * Assets Library
 *
 * @author 		Boris Strahija <boris@creolab.hr>
 * @copyright 	Copyright (c) 2012, Boris Strahija, http://creolab.hr
 * @version 	1.0.0
 */

define('ASSETS_VERSION', '1.0.0');


class Assets {
	
	protected static $_ci;
	protected static $_less;
	
	
	// Paths and folders
	public static $assets_dir;
	public static $base_path;
	public static $base_url;
	
	public static $js_dir;
	public static $js_path;
	public static $js_url;
	
	public static $css_dir;
	public static $css_path;
	public static $css_url;
	
	public static $img_dir;
	public static $img_path;
	public static $img_url;
	
	public static $cache_dir;
	public static $cache_path;
	public static $cache_url;


	// Prefixes and groups
	public static $prefix_css;
	public static $prefix_js;
	public static $prefix_timestamp;
	public static $group;
	
	
	// Files that should be processed
	private static $_js;
	private static $_css;
	private static $assets;
	
	
	// Config
	public static $combine_css          = true;  // Combine CSS files
	public static $combine_js           = true;  // Combine JS files
	public static $minify               = false; // Minify all
	public static $minify_js            = true;
	public static $minify_css           = true;
	public static $auto_clear_cache     = false; // Automaticly clear all cache before creating new cache files
	public static $auto_clear_css_cache = false; // Or clear just cached CSS files
	public static $auto_clear_js_cache  = false; // Or just cached JS files
	public static $html5                = true;  // Use HTML5 tags
	public static $enable_coffeescript  = true;  // Enable CoffeeScript parser

	// Flags
	public static $auto_cleared_css_cache = false;
	public static $auto_cleared_js_cache  = false;
	
	
	/* ------------------------------------------------------------------------------------------ */
	
	/**
	 * Library initialization
	 * @param  array $cfg
	 */
	public static function init($cfg = null)
	{
		if ( ! self::$_ci)
		{
			self::$_ci =& get_instance();

			// Load LessPHP
			if (defined('SPARKPATH')) include(reduce_double_slashes(SPARKPATH.'assets/'.ASSETS_VERSION.'/libraries/lessc.php'));
			else                      include(reduce_double_slashes(APPPATH.'/third_party/assets/lessc.php'));
			
			// Load JSMin
			if (defined('SPARKPATH')) include(reduce_double_slashes(SPARKPATH.'assets/'.ASSETS_VERSION.'/libraries/jsmin.php'));
			else                      include(reduce_double_slashes(APPPATH.'/third_party/assets/jsmin.php'));
			
			// Load CSSMin
			if (defined('SPARKPATH')) include(reduce_double_slashes(SPARKPATH.'assets/'.ASSETS_VERSION.'/libraries/cssmin.php'));
			else                      include(reduce_double_slashes(APPPATH.'/third_party/assets/cssmin.php'));
			
			// Add config to library
			if ($cfg)
			{
				self::configure(array_merge($cfg), config_item('assets'));
			}
			else
			{
				self::configure(config_item('assets'));
			}
			
			// Load CoffeeScript
			if (self::$enable_coffeescript)
			{
				if (defined('SPARKPATH')) include(reduce_double_slashes(SPARKPATH.'assets/'.ASSETS_VERSION.'/libraries/coffeescript/coffeescript.php'));
				else                      include(reduce_double_slashes(APPPATH.'/third_party/assets/coffeescript/coffeescript.php'));
			}
			
			// Initialize LessPHP
			self::$_less = new lessc();
			self::$_less->importDir = self::$css_path.'/';
		}
	}
	
	
	/* ------------------------------------------------------------------------------------------ */
	
	/**
	 * Add new CSS file for processing
	 * @param  string $file
	 */
	private static function _add_css($file = null, $group = null)
	{
		if ($file)
		{
			// Multiple files as array are supported
			if (is_array($file))
			{
				foreach ($file as $f)
				{
					self::_add_css($f, $group);
				}
			}
			
			// Single file
			else
			{
				if ($group) self::$_css[$group][] = $file;
				else        self::$_css[]         = $file;
			}
		}
	}
	
	
	/* ------------------------------------------------------------------------------------------ */
	
	/**
	 * Add new JS file for processing
	 * @param  string $file
	 */
	private static function _add_js($file = null, $group = null)
	{
		if ($file)
		{
			// Multiple files as array are supported
			if (is_array($file))
			{
				foreach ($file as $f)
				{
					self::_add_js($f, $group);
				}
			}
			
			// Single file
			else
			{
				$type = pathinfo($file, PATHINFO_EXTENSION);

				if ($type != 'coffee' or ($type == 'coffee' and self::$enable_coffeescript))
				{
					if ($group) self::$_js[$group][] = $file;
					else        self::$_js[]         = $file;
				}
			}
		}
	}
	
	
	
	/* ------------------------------------------------------------------------------------------ */
	/* !/===> Processing files, generating HTML tags */
	/* ------------------------------------------------------------------------------------------ */
	
	
	/**
	 * Get tags
	 * @param  string $type
	 * @return string
	 */
	public static function get($type = 'all')
	{
		$html = '';
		
		if ($type == 'all')
		{
			$html .= self::_get_css();
			$html .= self::_get_js();
		}
		elseif ($type == 'css')
		{
			$html .= self::_get_css();
		}
		elseif ($type == 'js')
		{
			$html .= self::_get_js();
		}
		
		return $html;
	}
	
	
	/* ------------------------------------------------------------------------------------------ */
	
	/**
	 * Get CSS tags
	 * @return string
	 */
	private static function _get_css()
	{
		$html = '';
		
		if (self::$_css)
		{
			// Simply return a list of all css tags
			if ( ! self::$combine_css and ( ! self::$minify and ! self::$minify_css))
			{
				foreach (self::$_css as $css)
				{
					if (self::$group)
					{
						foreach ($css as $group_css)
						{
							// Process LESS
							if (pathinfo($group_css, PATHINFO_EXTENSION) === 'less')
							{
								$files = self::_cache_assets(array(self::$group => array($group_css)), 'css');
								$html .= self::_tag($files[0]);
							}
							else
							{
								$html .= self::_tag(self::$css_url.'/'.$group_css);
							}
						}
					}
					else
					{
						// Process LESS
						if (pathinfo($css, PATHINFO_EXTENSION) === 'less')
						{
							$files = self::_cache_assets(array($css), 'css');
							$html .= self::_tag($files[0]);
						}
						else
						{
							$html .= self::_tag(self::$css_url.'/'.$css);
						}
					}
				}
			
			}
			else
			{
				// Try to cache assets and get html tag
				$files = self::_cache_assets(self::$_css, 'css');

				// Add to html
				foreach ($files as $file)
				{
					$html .= self::_tag($file);
				}
			}
		}
		
		return $html;
	}
	
	
	/* ------------------------------------------------------------------------------------------ */
	
	/**
	 * Get JS tags
	 * @return string
	 */
	private static function _get_js()
	{
		$html = '';
		
		if (self::$_js)
		{
			// Simply return a list of all css tags
			if ( ! self::$combine_js and ( ! self::$minify and ! self::$minify_js))
			{
				foreach (self::$_js as $js)
				{
					if (self::$group)
					{
						foreach ($js as $group_js)
						{
							// Process CoffeeScript
							if (pathinfo($group_js, PATHINFO_EXTENSION) === 'coffee')
							{
								$files = self::_cache_assets(array(self::$group => array($group_js)), 'js');
								$html .= self::_tag($files[0]);
							}
							else
							{
								$html .= self::_tag(self::$js_url.'/'.$group_js);
							}
						}
					}
					else
					{
						// Process CoffeeScript
						if (pathinfo($js, PATHINFO_EXTENSION) === 'coffee')
						{
							$files = self::_cache_assets(array($js), 'js');
							$html .= self::_tag($files[0]);
						}
						else
						{
							$html .= self::_tag(self::$js_url.'/'.$js);
						}
					}
				}
			}
			else
			{
				// Try to cache assets and get html tag
				$files = self::_cache_assets(self::$_js, 'js');
				
				// Add to html
				foreach ($files as $file)
				{
					$html .= self::_tag($file);
				}
			}
		}
		
		return $html;
	}
	
	
	/* ------------------------------------------------------------------------------------------ */
	
	/**
	 * Caches the assets if needed and returns a list files/paths
	 * @param  array  $assets
	 * @param  string $type   [description]
	 * @return array
	 */
	private static function _cache_assets($assets = null, $type = null)
	{
		$files = array(); // Will contain all the processed files

		if ($assets and $type)
		{
			$last_modified = 0;
			$path          = ($type == 'css') ? self::$css_path : self::$js_path ;
			
			if (($type === 'css' and self::$combine_css) or ($type === 'js' and self::$combine_js))
			{
				// Check if it's a group (associative array)
				if (self::$group) $assets = $assets[self::$group];

				// Find last modified file
				foreach ($assets as $asset)
				{
					$last_modified 	= max($last_modified, filemtime(realpath($path.'/'.$asset)));
				}
				
				// Build the filename and path
				if     (self::$prefix_css and $type == 'css')       $file_name = self::$prefix_css.'.'.((self::$prefix_timestamp) ? date('YmdHis', $last_modified).'.' : '').$type;
				elseif (self::$prefix_js  and $type == 'js')        $file_name = self::$prefix_js .'.'.((self::$prefix_timestamp) ? date('YmdHis', $last_modified).'.' : '').$type;
				elseif (self::$group and ! self::$prefix_timestamp) $file_name = $type;
				else                                                $file_name = date('YmdHis', $last_modified).'.'.$type;
				$file_path = reduce_double_slashes(self::$cache_path.'/'.((self::$group) ? self::$group.'.' : '').$file_name);
				
				// Now check if the file exists in the cache directory
				if ( ! file_exists($file_path))
				{
					$data = '';
					
					// Get file contents
					foreach ($assets as $asset)
					{
						// Get file contents, for CSS file we use the LESS compiler
						if ($type == 'css')
						{
							$less     = new lessc(self::$css_path.'/'.$asset);
							$contents = $less->parse();
						}
						else
						{
							if (pathinfo($asset, PATHINFO_EXTENSION) === 'coffee')
							{
								// Try to compile CoffeeScript
								try {
									$contents = read_file(reduce_double_slashes($path.'/'.$asset));
									$contents = CoffeeScript\compile($contents);
								}
								catch (Exception $e)
								{
									$contents = '';
								}
							}
							else
							{
								$contents = read_file(reduce_double_slashes($path.'/'.$asset));
							}
						}

						$pathinfo = pathinfo($asset);
						if ($pathinfo['dirname'] != '.') 	$base_url = self::$css_url.'/'.$pathinfo['dirname'];
						else 								$base_url = self::$css_url;
						
						// Process asset
						$data .= self::_process($contents, $type, 'minify', $base_url);
					}
					
					// Minify
					if ($type == 'css')
					{
						$data = self::_process($data, $type, 'minify');
					}
					
					// Auto clear cache directory?
					if ($type == 'css' and (self::$auto_clear_cache or self::$auto_clear_css_cache) and ! self::$auto_cleared_css_cache)
					{
						self::clear_css_cache();
						self::$auto_cleared_css_cache = true;
					}
					
					if ($type == 'js' and (self::$auto_clear_cache or self::$auto_clear_js_cache) and ! self::$auto_cleared_js_cache)
					{
						self::clear_js_cache();
						self::$auto_cleared_js_cache = true;
					}
					
					// And save the file
					write_file($file_path, $data);
				}
				
				// Add to files
				$files[] = reduce_double_slashes(self::$cache_url.'/'.((self::$group) ? self::$group.'.' : '').$file_name);
			}
			
			// No combining
			else
			{
				// Check if it's a group (associative array)
				if (self::$group) $assets = $assets[self::$group];
				
				foreach ($assets as $asset)
				{
					$last_modified 	= filemtime(realpath($path.'/'.$asset));
					
					// Now check if the file exists in the cache directory
					$file 		= pathinfo($asset);
					if     (self::$prefix_css and $type == 'css') $file_name 	= self::$prefix_css.'.'.((self::$prefix_timestamp) ? date('YmdHis', $last_modified).'.' : '').$file['filename'].'.'.$type;
					elseif (self::$prefix_js  and $type == 'js')  $file_name 	= self::$prefix_js .'.'.((self::$prefix_timestamp) ? date('YmdHis', $last_modified).'.' : '').$file['filename'].'.'.$type;
					else                                          $file_name 	= date('YmdHis', $last_modified).'.'.$file['filename'].'.'.$type;
					$file_path 	= reduce_double_slashes(self::$cache_path.'/'.((self::$group) ? self::$group.'.' : '').$file_name);
					
					if ( ! file_exists($file_path))
					{
						// Get file contents
						if ($type == 'css')
						{
							$less = new lessc(self::$css_path.'/'.$asset);
							$data = $less->parse();
						}
						else
						{
							if (pathinfo($asset, PATHINFO_EXTENSION) === 'coffee')
							{
								// Try to compile CoffeeScript
								try {
									$data = read_file(reduce_double_slashes($path.'/'.$asset));
									$data = CoffeeScript\compile($data);
								}
								catch (Exception $e)
								{
									$data = '';
								}
							}
							else
							{
								$data = read_file(reduce_double_slashes($path.'/'.$asset));
							}
						}

						// Process
						$data = self::_process($data, $type, 'all', site_url(self::$css_url));
						
						// Auto clear cache directory?
						if ($type == 'css' and (self::$auto_clear_cache or self::$auto_clear_css_cache))
						{
							self::clear_css_cache($asset);
						}
						
						if ($type == 'js' and (self::$auto_clear_cache or self::$auto_clear_js_cache))
						{
							self::clear_js_cache($asset);
						}
						
						// And save the file
						write_file($file_path, $data);
					}
					
					// Add to files
					$files[] = reduce_double_slashes(self::$cache_url.'/'.((self::$group) ? self::$group.'.' : '').$file_name);
				}
			}
		}
		
		return $files;
	}
	
	
	/* ------------------------------------------------------------------------------------------ */
	
	/**
	 * Process files
	 * @param  string $data     contents of file
	 * @param  string $type     css or js
	 * @param  string $do       what to do (all, minify)
	 * @param  string $base_url URL to assets directory
	 * @return string           returns the processed file data
	 */
	private static function _process($data = null, $type = null, $do = 'all', $base_url = null)
	{
		if ( ! $base_url) $base_url = self::$base_url;
		
		if ($type == 'css')
		{
			if ((self::$minify or self::$minify_css) and ($do == 'all' or $do == 'minify'))
			{
				$data = CSSMin::minify($data, array(
					'currentDir'          => str_replace(site_url(), '', $base_url).'/',
				));
			}
		}
		else
		{
			if ((self::$minify or self::$minify_js) and ($do == 'all' or $do == 'minify'))
			{
				$data = JSMin::minify($data);
			}
		}
		
		return $data;
	}
	
	
	/* ------------------------------------------------------------------------------------------ */
	
	/**
	 * Generates assets tags
	 * @param  string $file
	 * @param  string $type
	 * @return string
	 */
	private static function _tag($file = null, $type = null)
	{
		// Try to figure out a type if none passed
		if ( ! $type)
		{
			$type = substr(strrchr($file,'.'), 1);
		}
		
		// Now return CSS html tag
		if ($file and ($type == 'css' or $type == 'less'))
		{
			// Replace less sufix
			$file = str_replace('.less', '.css', $file);

			if (self::$html5) {
				return '<link rel="stylesheet" href="'.$file.'">'.PHP_EOL;
			}
			else
			{
				return '<link rel="stylesheet" type="text/css" href="'.$file.'" />'.PHP_EOL;
			}
		}
		
		// And the JS html tag
		elseif ($file and ($type == 'js' or $type == 'coffee'))
		{
			// Replace coffee sufix
			$file = str_replace('.coffee', '.js', $file);

			if (self::$html5)
			{
				return '<script src="'.$file.'"></script>'.PHP_EOL;
			}
			else
			{
				return '<script src="'.$file.'" type="text/javascript" charset="utf-8"></script>'.PHP_EOL;
			}
		}
		
		return null;
	}
	
	
	
	/* ------------------------------------------------------------------------------------------ */
	/* !/===> Displaying assets */
	/* ------------------------------------------------------------------------------------------ */
	
	
	/**
	 * Simply displays the generated tags
	 * @param  string $type which assets should be displayed
	 * @param  mixed  $css  CSS files
	 * @param  mixed  $js   JS files
	 * @param  array  $cfg
	 * @return string
	 */
	public static function all($type = 'all', $css = null, $js = null, $group = null, $cfg = null)
	{
		self::$group = $group;
		self::init();

		// Configuration
		if ($cfg) self::configure($cfg);

		// Overwrite CSS files
		if ($css)
		{
			self::$_css = array();
			self::_add_css($css, $group);
		}
		
		// Overwrite JS files
		if ($js)
		{
			self::$_js = array();
			self::_add_js($js, $group);
		}
		
		// Display all the tags
		echo self::get($type);
	}
	
	
	/* ------------------------------------------------------------------------------------------ */
	
	/**
	 * Display CSS tags
	 * @param  array  $assets
	 * @param  array  $cfg
	 * @return string
	 */
	public static function css($assets = null, $cfg = null)
	{
		self::all('css', $assets, null, null, $cfg);
	}

	
	/* ------------------------------------------------------------------------------------------ */
	
	/**
	 * Display a group of CSS tags
	 * @param  string $group
	 * @param  array  $assets
	 * @param  array  $cfg
	 * @return string
	 */
	public static function css_group($group = null, $assets = null, $cfg = null)
	{
		self::all('css', $assets, null, $group, $cfg);
	}
	
	
	/* ------------------------------------------------------------------------------------------ */
	
	/**
	 * Display JS tags
	 * @param  array  $assets
	 * @param  array  $cfg
	 * @return string
	 */
	public static function js($assets = null, $cfg = null)
	{
		self::all('js', null, $assets, null, $cfg);
	}

	
	/* ------------------------------------------------------------------------------------------ */
	
	/**
	 * Display a group of JS tags
	 * @param  string $group
	 * @param  array  $assets
	 * @param  array  $cfg
	 * @return string
	 */
	public static function js_group($group = null, $assets = null, $cfg = null)
	{
		self::all('js', null, $assets, $group, $cfg);
	}
	
	
	
	/* ------------------------------------------------------------------------------------------ */
	/* !/===> Deleting files */
	/* ------------------------------------------------------------------------------------------ */
	
	/**
	 * Delete cached files
	 * @param  string $type
	 * @param  string $asset_file
	 */
	public static function clear_cache($type = null, $asset_file = null)
	{
		self::init();

		$files = directory_map(self::$cache_path, 1);
		
		if ($files)
		{
			foreach ($files as $file)
			{
				if ( ! is_array($file))
				{
					$file_path = reduce_double_slashes(self::$cache_path.'/'.$file);
					$file_info = pathinfo($file_path);
					
					// Clear single file cache
					if ($asset_file)
					{
						$dev_file_name = substr($file, 15); // Get the real filename, without the timestamp prefix
						
						// Compare file name and remove if necesary
						if ($dev_file_name == $asset_file)
						{
							unlink($file_path);
							//echo 'Deleted asset: '.$file."<br>\n";
						}
					}
					
					// Or all files
					else
					{
						if (is_file($file_path) and $file_info)
						{
							// Delete the CSS files
							if ($file_info['extension'] == 'css' and ( ! $type or $type == 'css'))
							{
								unlink($file_path);
								//echo 'Deleted CSS: '.$file."<br>\n";
							}
							
							// Delete the JS files
							if ($file_info['extension'] == 'js' and ( ! $type or $type == 'js'))
							{
								unlink($file_path);
								//echo 'Deleted JS: '.$file."<br>\n";
							}
						}
					}
				}
			}
		}
	}
	
	
	/* ------------------------------------------------------------------------------------------ */
	
	/**
	 * Delete cached CSS files
	 * @param  string $asset_file
	 */
	public static function clear_css_cache($asset_file = null)
	{
		return self::clear_cache('css', $asset_file);
	}
	
	
	/* ------------------------------------------------------------------------------------------ */
	
	/**
	 * Delete cached JS files
	 * @param  string $asset_file
	 */
	public static function clear_js_cache($asset_file = null)
	{
		return self::clear_cache('js', $asset_file);
	}
	
	
	
	/* ------------------------------------------------------------------------------------------ */
	/* !/===> URL / Image helpers */
	/* ------------------------------------------------------------------------------------------ */


	/**
	 * Return url to asset
	 * @param  string $path
	 */
	public static function url($path = null)
	{
		return reduce_double_slashes(self::$base_url.'/'.$path);
	}

	
	/* ------------------------------------------------------------------------------------------ */
	
	/**
	 * Return url to image, or event an entire tag
	 * @param  string $path
	 */
	public static function img($path = null, $tag = false, $properties = null)
	{
		$img_path = reduce_double_slashes(self::$img_url.'/'.$path);

		// Properties
		if ($properties) $properties['src'] = $img_path;

		// Tag?
		if ($tag) return img($properties);
		else      return $img_path;
	}

	
	
	/* ------------------------------------------------------------------------------------------ */
	/* !/===> Configuration */
	/* ------------------------------------------------------------------------------------------ */
	
	
	/**
	 * Library configuration
	 * @param  array  $cfg
	 */
	public static function configure($cfg = null)
	{
		$cfg = array_merge(config_item('assets'), $cfg);
		
		if ($cfg and is_array($cfg))
		{
			foreach ($cfg as $key=>$val)
			{
				self::$$key = $val;
				//echo 'CONFIG: ', $key, ' :: ', $val, '<br>';
			}
		}

		// Prepare all the paths and URI's
		self::_paths();
	}

	
	/* ------------------------------------------------------------------------------------------ */
	
	/**
	 * Set a different assets path
	 * @param string $path
	 */
	public static function set_path($path = null)
	{
		self::init();

		if ($path) self::$assets_dir = $path;

		self::_paths();
	}

	
	/* ------------------------------------------------------------------------------------------ */
	
	/**
	 * Set a prefix for cached files (instead of the timestamp)
	 * @param string $prefix
	 */
	public static function set_prefix($prefix = null, $type = null)
	{
		self::init();

		if ($prefix)
		{
			if ($type == 'css')
			{
				self::$prefix_css = $prefix;
			}
			elseif ($type == 'js')
			{
				self::$prefix_js  = $prefix;
			}
			else
			{
				self::$prefix_css = $prefix;
				self::$prefix_js  = $prefix;
			}
		}
	}
	
	
	/* ------------------------------------------------------------------------------------------ */
	
	/**
	 * Setup paths
	 * @return [type] [description]
	 */
	private static function _paths()
	{
		// Set the assets base path
		self::$base_path = reduce_double_slashes(realpath(self::$assets_dir));
		
		// Now set the assets base URL
		self::$base_url = reduce_double_slashes(config_item('base_url').'/'.self::$assets_dir);
		
		// And finally the paths and URL's to the css and js assets
		self::$js_path    = reduce_double_slashes(self::$base_path .'/'.self::$js_dir);
		self::$js_url     = reduce_double_slashes(self::$base_url  .'/'.self::$js_dir);
		self::$css_path   = reduce_double_slashes(self::$base_path .'/'.self::$css_dir);
		self::$css_url    = reduce_double_slashes(self::$base_url  .'/'.self::$css_dir);
		self::$img_path   = reduce_double_slashes(self::$base_path .'/'.self::$img_dir);
		self::$img_url    = reduce_double_slashes(self::$base_url  .'/'.self::$img_dir);
		self::$cache_path = reduce_double_slashes(self::$base_path .'/'.self::$cache_dir);
		self::$cache_url  = reduce_double_slashes(self::$base_url  .'/'.self::$cache_dir);
		
		// Check if all directories exist
		if ( ! is_dir(self::$js_path))
		{
			if ( ! @mkdir(self::$js_path, 0755))    exit('Error with JS directory.');
		}
		
		if ( ! is_dir(self::$css_path))
		{
			if ( ! @mkdir(self::$css_path, 0755))   exit('Error with CSS directory.');
		}
		
		if ( ! is_dir(self::$cache_path))
		{
			if ( ! @mkdir(self::$cache_path, 0777)) exit('Error with CACHE directory.');
		}
		
		// Try to make the cache direcory writable
		if (is_dir(self::$cache_path) and ! is_really_writable(self::$cache_path))
		{
			@chmod(self::$cache_path, 0777);
		}
		
		// If it's still not writable throw error
		if ( ! is_dir(self::$cache_path) or ! is_really_writable(self::$cache_path))
		{
			exit('Error with CACHE directory.');
		}
	}
	
	
	/* ------------------------------------------------------------------------------------------ */
	
}


/* End of file assets.php */