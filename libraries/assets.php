<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');	
	
/**
 * Assets Library
 *
 * @author 		Boris Strahija <boris@creolab.hr>
 * @copyright 	Copyright (c) 2012, Boris Strahija, http://creolab.hr
 * @version 	1.5.1
 */

define('ASSETS_VERSION', '1.5.1');


class Assets {
	
	protected static $_ci;
	protected static $_less;
	protected static $_cache_info;
	protected static $_cache_info_file  = 'info.cache';
	protected static $_enable_benchmark = false;


	// All the assets go in here
	private static $_assets = array('js' => array(), 'css' => array());

	
	// Prefixes and groups
	public static $group;
	public static $default_group = array(
		'css' => '__def_css__',
		'js'  => '__def_js__',
	);


	// Paths and folders
	public static $assets_dir, $base_path,  $base_url;
	public static $js_dir,     $js_path,    $js_url;
	public static $css_dir,    $css_path,   $css_url;
	public static $img_dir,    $img_path,   $img_url;
	public static $cache_dir,  $cache_path, $cache_url;


	// Config
	public static $minify               = false; // Minify all
	public static $minify_js            = true;
	public static $minify_css           = true;
	public static $auto_clear_cache     = false; // Automaticly clear all cache before creating new cache files
	public static $auto_clear_css_cache = false; // Or clear just cached CSS files
	public static $auto_clear_js_cache  = false; // Or just cached JS files
	public static $html5                = true;  // Use HTML5 tags
	public static $enable_less          = true;  // Enable LESS CSS parser
	public static $enable_coffeescript  = true;  // Enable CoffeeScript parser
	public static $freeze               = false; // Disable all processing once the assets are cached (for production)

	// CssMin config
	public static $cssmin_plugins       = array();
	public static $cssmin_filters       = array();
	
	// Flags
	public static  $auto_cleared_css_cache = false;
	public static  $auto_cleared_js_cache  = false;
	private static $_cssmin_loaded         = false;
	private static $_jsmin_loaded          = false;
	private static $_less_loaded           = false;
	private static $_coffeescript_loaded   = false;

	
	/* ------------------------------------------------------------------------------------------ */
	
	/**
	 * Display CSS tags
	 * @param  array  $files
	 * @return string
	 */
	public static function css($files = null, $attributes = null)
	{
		self::init();

		// Start benchmark
		if (self::$_enable_benchmark) self::$_ci->benchmark->mark("Assets::css()_start");

		self::$group = null;
		// Add to process container
		self::_add_assets($files, null, 'css', $attributes);

		// And process it
		if (self::$_cache_info and self::$freeze) {}
		else                                      { self::_process('css'); }

		// End benchmark
		if (self::$_enable_benchmark) self::$_ci->benchmark->mark("Assets::css()_end");

		// Tags
		return self::_generate_tags('css');
	}

	
	/* ------------------------------------------------------------------------------------------ */
	
	/**
	 * Display a group of CSS tags
	 * @param  string $group
	 * @param  array  $files
	 * @return string
	 */
	public static function css_group($group = null, $files = null, $attributes = null)
	{
		self::$group = $group;

		self::init();

		// Start benchmark
		if (self::$_enable_benchmark) self::$_ci->benchmark->mark("Assets::css_group(".$group.")_start");

		// Add to process container
		self::_add_assets($files, $group, 'css', $attributes);

		// And process it
		if (self::$_cache_info and self::$freeze) {}
		else                                      { self::_process('css', $group); }

		// End benchmark
		if (self::$_enable_benchmark) self::$_ci->benchmark->mark("Assets::css_group(".$group.")_end");

		// Tags
		return self::_generate_tags('css');
	}

	
	/* ------------------------------------------------------------------------------------------ */
	
	/**
	 * Display JS tags
	 * @param  array  $files
	 * @return string
	 */
	public static function js($files = null)
	{
		self::$group = null;

		self::init();

		// Start benchmark
		if (self::$_enable_benchmark) self::$_ci->benchmark->mark("Assets::js()_start");

		// Add to process container
		self::_add_assets($files, null, 'js');

		// And process it
		if (self::$_cache_info and self::$freeze) {}
		else                                      { self::_process('js'); }

		// End benchmark
		if (self::$_enable_benchmark) self::$_ci->benchmark->mark("Assets::js()_end");

		// Tags
		return self::_generate_tags('js');
	}

	
	/* ------------------------------------------------------------------------------------------ */
	
	/**
	 * Display a group of JS tags
	 * @param  string $group
	 * @param  array  $files
	 * @return string
	 */
	public static function js_group($group = null, $files = null)
	{
		self::$group = $group;

		self::init();

		// Start benchmark
		if (self::$_enable_benchmark) self::$_ci->benchmark->mark("Assets::js_group(".$group.")_start");

		// Add to process container
		self::_add_assets($files, $group, 'js');

		// And process it
		if (self::$_cache_info and self::$freeze) {}
		else                                      { self::_process('js', $group); }

		// End benchmark
		if (self::$_enable_benchmark) self::$_ci->benchmark->mark("Assets::js_group(".$group.")_end");

		// Tags
		return self::_generate_tags('js');
	}

	
	/* ------------------------------------------------------------------------------------------ */
	
	/**
	 * Simply return html tags with CDN paths
	 * @param  array $assets
	 * @return string
	 */
	public static function cdn($assets = null, $echo = true)
	{
		if ($assets)
		{
			// Start benchmark
			if (self::$_enable_benchmark) self::$_ci->benchmark->mark("Assets::cdn()_start");

			self::init();

			$html = '';

			foreach ($assets as $asset)
			{
				// Get asset from config
				if (is_array($asset))
				{
					$cdn_asset = self::$_ci->config->item($asset[0], 'assets_cdn');
					if ($cdn_asset) $version = $asset[1];
				}
				else
				{
					$cdn_asset = self::$_ci->config->item($asset, 'assets_cdn');
					if ($cdn_asset) $version = $cdn_asset['default_version'];
				}

				// Check if asset ok
				if (isset($cdn_asset) and isset($version) and $cdn_asset and $version)
				{
					$cdn_path = str_replace('%version%', $version, $cdn_asset['path']);
					$html .= self::tag($cdn_path, 'js', false);
				}
			}

			// End benchmark
			if (self::$_enable_benchmark) self::$_ci->benchmark->mark("Assets::cdn()_end");

			if ($echo) echo $html;
			else       return $html;
		}
	}

	
	/* ------------------------------------------------------------------------------------------ */
	
	/**
	 * Place string in IE conditional comments
	 * @param  string $condition
	 * @param  string $string
	 */
	public static function conditional($condition = null, $string = null)
	{
		if ($condition and $string)
		{
			// Start benchmark
			if (self::$_enable_benchmark) self::$_ci->benchmark->mark("Assets::conditional()_start");

			echo '<!--[if '.$condition.']>'."\n";

			if (is_array($string))
			{
				foreach ($string as $str)
				{
					echo $str;
				}
			}
			else
			{
				echo $string;	
			}
			
			echo '<![endif]-->';
			
			// End benchmark
			if (self::$_enable_benchmark) self::$_ci->benchmark->mark("Assets::conditional()_end");
		}
	}



	
	/* ------------------------------------------------------------------------------------------ */
	
	/**
	 * Add assets to list for processing
	 * @param array  $assets
	 * @param string $group
	 * @param string $type
	 */
	private static function _add_assets($assets = null, $group = null, $type = null, $attributes = null)
	{
		// Start benchmark
		if (self::$_enable_benchmark) self::$_ci->benchmark->mark("Assets::add-assets()_start");

		if ( ! $group) $group = self::$default_group[$type];

		// Set last modified time to 0
		self::$_assets[$type][$group]['last_modified']       = 0;
		self::$_assets[$type][$group]['last_modified_human'] = "0000-00-00 00:00:00";

		// Attributes
		self::$_assets[$type][$group]['attributes'] = $attributes;

		// Prepare some vars
		self::$_assets[$type][$group]['combined']  = '';
		self::$_assets[$type][$group]['output']    = '';

		// List of active files in group
		if (isset(self::$_cache_info->{$type}->{$group}->file_list)) self::$_assets[$type][$group]['file_list'] = self::$_cache_info->{$type}->{$group}->file_list;
		else                                                         self::$_assets[$type][$group]['file_list'] = array();
		
		// Add assets to list
		foreach ($assets as $asset)
		{
			// Add file
			$tmp_asset = array('file' => $asset);

			// Determine path
			if     ($type === 'css') $file_path = reduce_double_slashes(self::$css_path.'/'.$asset);
			elseif ($type === 'js')  $file_path = reduce_double_slashes(self::$js_path.'/'.$asset);
			$tmp_asset['path'] = $file_path;

			// Modified time
			$tmp_asset['modified'] = filemtime(realpath($file_path));
			if ($tmp_asset['modified'] > self::$_assets[$type][$group]['last_modified'])
			{
				self::$_assets[$type][$group]['last_modified']       = $tmp_asset['modified'];
				self::$_assets[$type][$group]['last_modified_human'] = date('Y-m-d H:i:s', (int) $tmp_asset['modified']);
			}

			// Add to container
			self::$_assets[$type][$group]['src'][]       = $tmp_asset;
			self::$_assets[$type][$group]['file_list']   = (array) self::$_assets[$type][$group]['file_list'];
			self::$_assets[$type][$group]['file_list'][] = $tmp_asset['file'];
		}

		self::$_assets[$type][$group]['file_list'] = array_unique(self::$_assets[$type][$group]['file_list']);
		
		// End benchmark
		if (self::$_enable_benchmark) self::$_ci->benchmark->mark("Assets::add-assets()_end");
	}

	
	/* ------------------------------------------------------------------------------------------ */
	
	/**
	 * Process assets in group
	 * @param  string $type
	 * @param  string $group
	 */
	private static function _process($type = null, $group = null)
	{
		if ( ! $group) $group = self::$default_group[$type];

		// Start benchmark
		if (self::$_enable_benchmark) self::$_ci->benchmark->mark("Assets::process(".$type.", ".$group.")_start");

		// Last modified date
		if ( ! isset(self::$_cache_info->{$type}->{$group})) $last_modified = 0;
		else                                                 $last_modified = self::last_modified($type, $group);

		// Create a cache filename
		if ($group !== self::$default_group[$type]) $file_prefix = $group.'.';
		else                                        $file_prefix = '';
		$file_name = self::$_assets[$type][$group]['cache_file_name'] = $file_prefix.$last_modified.".".$type;

		// And check if we should process it
		$file_exists = file_exists(self::$cache_path.'/'.$file_name);

		if ( ! $file_exists or ! $last_modified)
		{
			// Get list of assets
			$assets = self::$_assets[$type][$group];

			// Loop through all original assets
			foreach ($assets['src'] as $key=>$asset)
			{
				// Get file contents
				$contents = read_file($asset['path']);

				// Get file info
				$asset['info'] = pathinfo($asset['file']);

				// Process imports in CSS files
				if ($type === 'css')
				{
					$import_result = self::_process_imports($contents, null, $asset['file']);
					$contents      = $import_result['contents'];

					// Update last modified time
					if ($import_result['last_modified']  > self::$_assets[$type][$group]['last_modified'])
					{
						self::$_assets[$type][$group]['last_modified']       = $import_result['last_modified'];
						self::$_assets[$type][$group]['last_modified_human'] = date('Y-m-d H:i:s', $import_result['last_modified']);
					}

					// Update imported files
					self::$_assets[$type][$group]['file_list'] = array_unique(array_merge(self::$_assets[$type][$group]['file_list'], $import_result['file_list']));
				}
				elseif ($type === 'js')
				{
					// CoffeeScript parser
					if (self::$enable_coffeescript and $asset['info']['extension'] === 'coffee')
					{
						if ( ! self::$_coffeescript_loaded) self::_init_coffeescript();

						CoffeeScript\Init::load();
						$contents = CoffeeScript\Compiler::compile($contents);
					}
					elseif ( ! self::$enable_coffeescript and $asset['info']['extension'] === 'coffee')
					{
						$contents = '';
					}

					// Minify JS
					if (self::$minify_js)
					{
						self::_init_jsmin();
						$contents = trim(JSMin::minify($contents));
					}
				}

				// Or add to combine var (if we're combining)
				self::$_assets[$type][$group]['combined'] .= "\n".$contents;
			}

			// New file name
			$file_name = self::$_assets[$type][$group]['cache_file_name'] = $file_prefix.self::$_assets[$type][$group]['last_modified'].".".$type;

			// Now minify/less if we choose so
			if ($type === 'css')
			{
				$output = self::$_assets[$type][$group]['combined'];

				// Less
				if (self::$enable_less and ! self::$freeze)
				{
					self::_init_less();
					$output = self::$_less->parse($output);
				}

				// Minify CSS
				if (self::$minify_css and ! self::$freeze)
				{
					self::_init_cssmin();

					$output = trim(CSSMin::minify($output, self::$cssmin_filters, self::$cssmin_plugins));
				}

				// Add to output
				self::$_assets[$type][$group]['output'] = $output;
				unset($output);
			}
			elseif ($type === 'js')
			{
				$output = self::$_assets[$type][$group]['combined'];

				// Minify JS
				if (self::$minify_js)
				{
					self::_init_jsmin();
					self::$_assets[$type][$group]['output'] = trim(JSMin::minify($output));
				}

				// Add to output
				self::$_assets[$type][$group]['output'] = $output;
				unset($output);
			}

			// Once it's processed remove vars we dont need
			unset(self::$_assets[$type][$group]['combined']);

			// And finnaly we create the actual cached files
			self::_cache_assets($type);
		}

		// Update cache info
		self::_update_cache_info();

		// End benchmark
		if (self::$_enable_benchmark) self::$_ci->benchmark->mark("Assets::process(".$type.", ".$group.")_end");
	}

	
	/* ------------------------------------------------------------------------------------------ */
	
	/**
	 * Process all CSS @imports and parse URL's
	 * @param  string $contents
	 * @param  string $import_dir
	 * @return array
	 */
	public static function _process_imports($contents = '', $import_dir = null, $asset_location = null)
	{
		// Start benchmark
		if (self::$_enable_benchmark) self::$_ci->benchmark->mark("Assets::process_imports()_start");

		$last_modified = 0;

		// Process URL's
		$contents = self::_process_urls($contents, $asset_location);
		
		// Find @import calls
		$imports_exist = preg_match_all("/@import\s+(.*);/", $contents, $imports);

		// List of import files
		$import_file_list = array();

		// If @import exist add them
		if ($imports_exist and is_array($imports) and isset($imports[1]) and is_array($imports[1]))
		{
			foreach ($imports[1] as $import_key=>$import)
			{
				$import_file = trim($import, " \"");

				// Is a directory set
				if ($import_dir) $import_file = $import_dir.'/'.$import_file;

				// Add to list
				$import_file_list[] = $import_file;

				// Path info
				$import_info     = pathinfo($import_file);
				$import_info_dir = ($import_info['dirname'] and $import_info['dirname'] !== ".") ? $import_info['dirname'] : null;
				
				// Path to file
				$import_file_path = reduce_double_slashes(self::$css_path.'/'.$import_file);

				// Get modified date
				$import_modified = filemtime(realpath($import_file_path));
				if ($import_modified > $last_modified) $last_modified = $import_modified;

				// Get contents
				$import_contents = read_file($import_file_path);

				// Nested imports?
				$import_result = self::_process_imports($import_contents, $import_info_dir, $import_file);
				if ($import_result)
				{
					$import_contents = $import_result['contents'];

					// Add to list
					if ($import_result['file_list']) $import_file_list = array_unique(array_merge($import_file_list, $import_result['file_list']));

					// Check modified time
					if ($import_result['last_modified'] > $last_modified) $last_modified = $import_result['last_modified'];
				}

				// And add to main contents
				$contents = str_replace($imports[0][$import_key], $import_contents, $contents);
			}
		}

		// End benchmark
		if (self::$_enable_benchmark) self::$_ci->benchmark->mark("Assets::process_imports()_end");

		return array(
			'last_modified' => $last_modified,
			'contents'      => $contents,
			'file_list'     => $import_file_list,
		);
	}

	
	/* ------------------------------------------------------------------------------------------ */
	
	/**
	 * Find all URL's and process em accordingly
	 * @param  string $contents
	 * @param  string $asset_location
	 * @return string
	 */
	private static function _process_urls($contents = null, $asset_location = null)
	{
		// Parse URL's
		preg_match_all("/url\((\"|'|)?((.*\.(png|gif|jpg))(\"|'|))\)/Ui", $contents, $matches);

		if (isset($matches[2]) and $matches[2])
		{
			// Unique URL's
			if (is_array($matches[2])) $matches[2] = array_unique($matches[2]);

			// Set location for assets
			if ($asset_location) $asset_location = dirname($asset_location).'/';
			else                 $asset_location = '';

			foreach ($matches[2] as $match)
			{
				$href     = trim($match, " '\"");
				$new_href = '';
				$base     = self::$css_url.'/'.$asset_location;

				if ( ! $href) $new_href = $base;

				// href="http://..." ==> href isn't relative
				$rel_parsed = parse_url($href);
				if (array_key_exists('scheme', $rel_parsed)) $new_href = $href;

				// Add an extra character so that, if it ends in a /, we don't lose the last piece.
				$base_parsed = parse_url("$base ");
				// if it's just server.com and no path, then put a / there.
				if (!array_key_exists('path', $base_parsed)) $base_parsed = parse_url("$base/ ");

				if ( ! isset($rel_parsed['host']) or (isset($rel_parsed['host']) and isset($base_parsed['host']) and ($rel_parsed['host'] == $base_parsed['host'])))
				{
					// href="/ ==> throw away current path.
					if ($href{0} === "/") $path = $href;
					else                  $path = dirname($base_parsed['path']) . "/$href";

					// bla/./bloo ==> bla/bloo
					$path = preg_replace('~/\./~', '/', $path);

					// resolve /../
					// loop through all the parts, popping whenever there's a .., pushing otherwise.
					$parts = array();
					foreach (explode('/', preg_replace('~/+~', '/', $path)) as $part)
					{
						if ($part === "..")  array_pop($parts);
						elseif ($part != "") $parts[] = $part;
					}

					$new_href = ((array_key_exists('scheme', $base_parsed)) ? $base_parsed['scheme'] . '://' . $base_parsed['host'] : "") . "/" . implode("/", $parts);
					if (substr($path, 0, 2) == '//' and substr($new_href, 0, 2) != '//' and substr($new_href, 0, 1) == '/') $new_href = '/' . $new_href;
				}

				$contents = str_replace($href, $new_href, $contents);
			}
		}

		return $contents;
	}

	
	/* ------------------------------------------------------------------------------------------ */
	
	/**
	 * Find last modified time in list of files
	 * @param  string $type  - css or js (can be null)
	 * @param  string $group - name of group so we can fetch the file list
	 * @return integer
	 */
	public static function last_modified($type = null, $group = null)
	{
		$last_modified = 0;

		// Start benchmark
		if (self::$_enable_benchmark) self::$_ci->benchmark->mark("Assets::last_modified(".$type.", ".$group.")_start");

		// Get file path
		if ($type === 'css')    $path = self::$css_path;
		elseif ($type === 'js') $path = self::$js_path;

		// Get files
		$files = array();
		if ($type and ! $group or ($type and ! isset(self::$_cache_info->{$type}->{$group})))
		{
			$files = directory_map($path, 1);
		}
		elseif ($type and $group and isset(self::$_cache_info->{$type}->{$group}->file_list))
		{
			$files = self::$_cache_info->{$type}->{$group}->file_list;
		}

		foreach ($files as $file)
		{
			if ( ! is_array($file))
			{
				$file_modified = (int) @filemtime($path."/".$file);
				if ($file_modified > $last_modified) $last_modified = $file_modified;
			}
			else
			{
				$subfiles = directory_map($path.'/'.$file, 1);

				foreach ($subfiles as $subfile)
				{
					if ( ! is_array($subfile))
					{
						$subfile_modified = filemtime($path."/".$file);
						if ($subfile_modified > $last_modified) $last_modified = $subfile_modified;
					}
				}
			}
		}

		// End benchmark
		if (self::$_enable_benchmark) self::$_ci->benchmark->mark("Assets::last_modified(".$type.", ".$group.")_end");

		return $last_modified;
	}

	
	/* ------------------------------------------------------------------------------------------ */
	
	/**
	 * Cache all assets of this type
	 * @param  string $type
	 */
	private static function _cache_assets($type = null)
	{
		// Start benchmark
		if (self::$_enable_benchmark) self::$_ci->benchmark->mark("Assets::cache_assets()_start");

		// Auto clear cache directory?
		if ($type == 'css' and (self::$auto_clear_cache or self::$auto_clear_css_cache) and ! self::$auto_cleared_css_cache)
		{
			self::clear_css_cache(self::$group, false);
			self::$auto_cleared_css_cache = true;
		}
		
		if ($type == 'js' and (self::$auto_clear_cache or self::$auto_clear_js_cache) and ! self::$auto_cleared_js_cache)
		{
			self::clear_js_cache(self::$group, false);
			self::$auto_cleared_js_cache = true;
		}

		// Loop through groups
		foreach (self::$_assets[$type] as $key=>$assets_group)
		{
			$file_path = self::$cache_path."/".$assets_group['cache_file_name'];
			
			write_file($file_path, $assets_group['output']);

			// Remove contents after caching
			unset(self::$_assets[$type][$key]['output']);
		}

		// End benchmark
		if (self::$_enable_benchmark) self::$_ci->benchmark->mark("Assets::cache_assets()_end");
	}

	
	/* ------------------------------------------------------------------------------------------ */
	
	/**
	 * Get/parse contents of cache info file
	 * @return object
	 */
	private static function _get_cache_info()
	{
		// Start benchmark
		if (self::$_enable_benchmark) self::$_ci->benchmark->mark("Assets::_get_cache_info()_start");

		self::$_cache_info = @json_decode(read_file(self::$cache_path.'/'.self::$_cache_info_file));
		
		// End benchmark
		if (self::$_enable_benchmark) self::$_ci->benchmark->mark("Assets::_get_cache_info()_end");
	}

	
	/* ------------------------------------------------------------------------------------------ */
	
	/**
	 * Displays cahce info
	 * @return string
	 */
	public static function echo_cache_info()
	{
		echo '<pre>'; print_r(self::$_cache_info); echo '</pre>';
	}

	
	/* ------------------------------------------------------------------------------------------ */
	
	/**
	 * Update cache info file
	 */
	public static function _update_cache_info()
	{
		// Start benchmark
		if (self::$_enable_benchmark) self::$_ci->benchmark->mark("Assets::_update_cache_info()_start");

		// We need to update the info file
		foreach (self::$_assets as $key_type=>$assets_type)
		{
			foreach ($assets_type as $key_group=>$assets_group)
			{
				// Remove output
				unset(self::$_assets[$key_type][$key_group]['output']);

				// Create empty placeholders if needed
				if (self::$_cache_info === null)                             self::$_cache_info = new stdClass;
				if ( ! isset(self::$_cache_info->{$key_type}))               self::$_cache_info->{$key_type} = new stdClass;
				if ( ! isset(self::$_cache_info->{$key_type}->{$key_group})) self::$_cache_info->{$key_type}->{$key_group} = new stdClass;

				// Add group to info
				self::$_cache_info->{$key_type}->{$key_group}->cache_file_name = $assets_group['cache_file_name'];
				self::$_cache_info->{$key_type}->{$key_group}->last_modified   = $assets_group['last_modified'];
				self::$_cache_info->{$key_type}->{$key_group}->file_list       = $assets_group['file_list'];
			}
		}

		write_file(self::$cache_path.'/'.self::$_cache_info_file, json_encode(self::$_cache_info));

		// End benchmark
		if (self::$_enable_benchmark) self::$_ci->benchmark->mark("Assets::_update_cache_info()_end");
	}

	
	/* ------------------------------------------------------------------------------------------ */
	
	/**
	 * Generate and output HTML tags
	 * @param  string  $type
	 * @param  boolean $echo
	 * @return string
	 */
	private static function _generate_tags($type = null, $echo = true)
	{
		if ( ! self::$group) $group = self::$default_group[$type];
		else                 $group = self::$group;

		if (isset(self::$_assets[$type][$group]))
		{
			// Get list of assets
			$assets_group = self::$_assets[$type][$group];

			// Default attributes
			$attributes = '';
			if ($type === 'css')
			{
				// Default attributes
				if ( ! isset($assets_group['attributes']['rel']))     $assets_group['attributes']['rel'] = 'stylesheet';
			}
			elseif ($type === 'js')
			{
				// Default attributes
				if ( ! isset($assets_group['attributes']['charset']) and ! self::$html5) $assets_group['attributes']['charset'] = 'utf-8';
			}

			// Custom attributes
			if (isset($assets_group['attributes']) and $assets_group['attributes'])
			{
				foreach ($assets_group['attributes'] as $att=>$val)
				{
					$attributes .= ' '.$att.'="'.$val.'"';
				}
			}

			// File name
			if ( ! isset($assets_group['cache_file_name'])) $assets_group['cache_file_name'] = self::$_cache_info->{$type}->{$group}->cache_file_name;

			// File and tag
			$file = self::$cache_url.'/'.$assets_group['cache_file_name'];
			$tag  = self::tag($file, $type, false, $attributes);

			if ($echo) echo   $tag;
			else       return $tag;
		}
	}

	
	/* ------------------------------------------------------------------------------------------ */
	
	/**
	 * Display a HTML tag
	 * @param  string  $file
	 * @param  string  $type
	 * @param  boolean $echo
	 * @return string
	 */
	public static function tag($file = null, $type = null, $echo = true, $attributes = '')
	{
		if ($type === 'css')
		{
			if (self::$html5) $tag = '<link href="'.$file.'"'.$attributes.'>'.PHP_EOL;
			else              $tag = '<link type="text/css" href="'.$file.'"'.$attributes.' />'.PHP_EOL;
		}
		elseif ($type === 'js')
		{
			if (self::$html5) $tag = '<script src="'.$file.'"'.$attributes.'></script>'.PHP_EOL;
			else              $tag = '<script src="'.$file.'" type="text/javascript"'.$attributes.'></script>'.PHP_EOL;
		}

		if ($echo) echo   $tag;
		else       return $tag;
	}

	
	/* ------------------------------------------------------------------------------------------ */
	
	/**
	 * Delete cached files
	 * @param  string $type
	 * @param  string $group
	 */
	public static function clear_cache($type = null, $group = null, $init = true)
	{
		if ($init) self::init();

		// Get all cached files
		$files = directory_map(self::$cache_path, 1);

		if ($files)
		{
			foreach ($files as $file)
			{
				if ($group)
				{

				}
				else
				{
					$file_path = reduce_double_slashes(self::$cache_path.'/'.$file);
					$file_info = pathinfo($file_path);

					if ($type === 'css')
					{
						if (isset($file_info['extension']) and strtolower($file_info['extension']) === 'css') unlink($file_path);
					}
					elseif ($type === 'js')
					{
						if (isset($file_info['extension']) and strtolower($file_info['extension']) === 'js') unlink($file_path);
					}
					else
					{
						if (isset($file_info['extension']) and (strtolower($file_info['extension']) === 'css' or strtolower($file_info['extension']) === 'js')) unlink($file_path);
						if (isset($file_info['extension']) and strtolower($file_info['extension']) === 'cache') unlink($file_path);
					}
				}
			}
		}

		self::_get_cache_info();
	}


	/* ------------------------------------------------------------------------------------------ */
	
	/**
	 * Delete cached CSS files
	 * @param  string $asset_file
	 */
	public static function clear_css_cache($group = null, $init = true)
	{
		return self::clear_cache('css', $group, $init);
	}
	
	
	/* ------------------------------------------------------------------------------------------ */
	
	/**
	 * Delete cached JS files
	 * @param  string $asset_file
	 */
	public static function clear_js_cache($group = null, $init = true)
	{
		return self::clear_cache('js', $group, $init);
	}

	
	/* ------------------------------------------------------------------------------------------ */
	
	public static function clear_assets()
	{
		self::$_assets = array('js' => array(), 'css' => array());
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
		return self::$base_url.'/'.$path;
	}

	
	/* ------------------------------------------------------------------------------------------ */
	
	/**
	 * Return url to image, or event an entire tag
	 * @param  string $path
	 */
	public static function img($path = null, $tag = false, $properties = null)
	{
		self::init();

		$img_path = self::$img_url.'/'.$path;

		// Properties
		if ($properties) $properties['src'] = $img_path;

		// Tag?
		if ($tag)
		{
			$img = img($properties, false);

			// Remove site_url if base_url is set as auto protocol
			if (stripos(self::$base_url, '//') === 0)
			{
				$img = str_replace('src="'.base_url(), 'src="', $img);
			}

			return $img;
		}
		else
		{
			return $img_path;
		}
	}

	
	
	/* ------------------------------------------------------------------------------------------ */
	/* !/===> Initialization and configuration */
	/* ------------------------------------------------------------------------------------------ */
	
	
	/**
	 * Library initialization
	 * @param  array $cfg
	 */
	public static function init($cfg = null)
	{
		// Clean previous assets if needed
		self::clear_assets();

		if ( ! self::$_ci)
		{
			self::$_ci =& get_instance();

			// Start benchmark
			if (self::$_enable_benchmark) self::$_ci->benchmark->mark("Assets::init()_start");

			// Add config to library
			if ($cfg)
			{
				self::configure(array_merge($cfg), config_item('assets'));
			}
			else
			{
				self::configure(config_item('assets'));
			}

			// Get cache info
			self::_get_cache_info();
		}

		// End benchmark
		if (self::$_enable_benchmark) self::$_ci->benchmark->mark("Assets::init()_end");
	}

	
	/* ------------------------------------------------------------------------------------------ */
	
	private static function _init_cssmin()
	{
		if (self::$minify_css and ! self::$freeze and ! self::$_cssmin_loaded)
		{
			// Start benchmark
			if (self::$_enable_benchmark) self::$_ci->benchmark->mark("Assets::init_cssmin()_start");

			// Load
			if (defined('SPARKPATH')) include(reduce_double_slashes(SPARKPATH.'assets/'.ASSETS_VERSION.'/libraries/cssmin.php'));
			else                      include(reduce_double_slashes(APPPATH.'/third_party/assets/cssmin.php'));
			self::$_cssmin_loaded = true;

			// Set current dir for css min
			if ( ! isset(self::$cssmin_filters['currentDir'])) self::$cssmin_filters['currentDir'] = str_replace(site_url(), '', self::$base_url).'/';

			// End benchmark
			if (self::$_enable_benchmark) self::$_ci->benchmark->mark("Assets::init_cssmin()_end");
		}
	}

	
	/* ------------------------------------------------------------------------------------------ */
	
	private static function _init_jsmin()
	{
		if (self::$minify_js and ! self::$freeze and ! self::$_jsmin_loaded)
		{
			// Start benchmark
			if (self::$_enable_benchmark) self::$_ci->benchmark->mark("Assets::init_jsmin()_start");

			// Load
			if (defined('SPARKPATH')) include(reduce_double_slashes(SPARKPATH.'assets/'.ASSETS_VERSION.'/libraries/jsmin.php'));
			else                      include(reduce_double_slashes(APPPATH.'/third_party/assets/jsmin.php'));
			self::$_jsmin_loaded = true;

			// End benchmark
			if (self::$_enable_benchmark) self::$_ci->benchmark->mark("Assets::init_jsmin()_end");
		}
	}

	
	/* ------------------------------------------------------------------------------------------ */
	
	private static function _init_less()
	{
		if ( ! self::$freeze and ! self::$_less_loaded)
		{
			// Start benchmark
			if (self::$_enable_benchmark) self::$_ci->benchmark->mark("Assets::init_less()_start");

			// Load LessPHP
			if (defined('SPARKPATH')) include(reduce_double_slashes(SPARKPATH.'assets/'.ASSETS_VERSION.'/libraries/lessc.php'));
			else                      include(reduce_double_slashes(APPPATH.'/third_party/assets/lessc.php'));
			
			// Initialize
			self::$_less = new lessc();
			self::$_less->importDir = self::$css_path.'/';
			self::$_less_loaded = true;

			// End benchmark
			if (self::$_enable_benchmark) self::$_ci->benchmark->mark("Assets::init_less()_end");
		}
	}

	
	/* ------------------------------------------------------------------------------------------ */
	
	private static function _init_coffeescript()
	{
		if ( ! self::$freeze and ! self::$_coffeescript_loaded)
		{
			// Start benchmark
			if (self::$_enable_benchmark) self::$_ci->benchmark->mark("Assets::init_coffeescript()_start");

			// Load classes
			if (defined('SPARKPATH')) include(reduce_double_slashes(SPARKPATH.'assets/'.ASSETS_VERSION.'/libraries/coffeescript/Init.php'));
			else                      include(reduce_double_slashes(APPPATH.'/third_party/assets/coffeescript/coffeescript.php'));

			// Initialize
			CoffeeScript\Init::load();
			self::$_coffeescript_loaded = true;

			// End benchmark
			if (self::$_enable_benchmark) self::$_ci->benchmark->mark("Assets::init_coffeescript()_end");
		}
	}

	
	/* ------------------------------------------------------------------------------------------ */
	
	/**
	 * Library configuration
	 * @param  array  $cfg
	 */
	public static function configure($cfg = null)
	{
		// Start benchmark
		if (self::$_enable_benchmark) self::$_ci->benchmark->mark("Assets::configure()_start");

		$cfg = array_merge(config_item('assets'), $cfg);

		if ($cfg and is_array($cfg))
		{
			foreach ($cfg as $key=>$val)
			{
				self::$$key = $val;
				//echo 'CONFIG: ', $key, ' :: ', $val, '<br>';
			}
		}

		// CssMin configuration
		self::$cssmin_filters = self::$_ci->config->item('assets_cssmin_filters');
		self::$cssmin_plugins = self::$_ci->config->item('assets_cssmin_plugins');

		// Prepare all the paths and URI's
		self::_paths();

		// End benchmark
		if (self::$_enable_benchmark) self::$_ci->benchmark->mark("Assets::configure()_end");
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
	 * Setup paths
	 */
	private static function _paths()
	{
		// Start benchmark
		if (self::$_enable_benchmark) self::$_ci->benchmark->mark("Assets::paths()_start");

		// Set the assets base path
		self::$base_path = reduce_double_slashes(realpath(self::$assets_dir));
		
		// Now set the assets base URL
		if ( ! self::$base_url) self::$base_url = reduce_double_slashes(config_item('base_url').'/'.self::$assets_dir);
		else                    self::$base_url = self::$base_url.self::$assets_dir;

		// Auto protocol
		if (stripos(self::$base_url, '//') === 0) $slash = '/';
		else                                      $slash = '';

		// And finally the paths and URL's to the css and js assets
		self::$js_path    = reduce_double_slashes(self::$base_path .'/'.self::$js_dir);
		self::$js_url     = $slash.reduce_double_slashes(self::$base_url  .'/'.self::$js_dir);
		self::$css_path   = reduce_double_slashes(self::$base_path .'/'.self::$css_dir);
		self::$css_url    = $slash.reduce_double_slashes(self::$base_url  .'/'.self::$css_dir);
		self::$img_path   = reduce_double_slashes(self::$base_path .'/'.self::$img_dir);
		self::$img_url    = $slash.reduce_double_slashes(self::$base_url  .'/'.self::$img_dir);
		self::$cache_path = reduce_double_slashes(self::$base_path .'/'.self::$cache_dir);
		self::$cache_url  = $slash.reduce_double_slashes(self::$base_url  .'/'.self::$cache_dir);

		if ( ! self::$freeze)
		{
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

		// End benchmark
		if (self::$_enable_benchmark) self::$_ci->benchmark->mark("Assets::paths()_end");
	}
	
	/* ------------------------------------------------------------------------------------------ */
	
}


/* End of file assets.php */