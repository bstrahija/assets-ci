<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');	
	
/**
 * Assets Library
 *
 * @author 		Boris Strahija <boris@creolab.hr>
 * @copyright 	Copyright (c) 2010, Boris Strahija, Creo
 * @version 	0.1
 */

require APPPATH."libraries/lessc.php";

class Assets {
	
	protected $ci;
	protected $less;
	
	
	// Paths and folders
	public $assets_dir;
	public $base_path;
	public $base_url;
	
	public $js_dir;
	public $js_path;
	public $js_url;
	
	public $css_dir;
	public $css_path;
	public $css_url;
	
	public $cache_dir;
	public $cache_path;
	public $cache_url;
	
	
	// Files that should be processed
	private $_js;
	private $_css;
	
	
	// Config
	public $combine 	= true;
	public $minify 		= true; // Minify all
	public $minify_js 	= true;
	public $minify_css 	= true;
	public $html5 		= true; // Use HTML5 tags
	public $env 		= 'production';
	
	
	/* ------------------------------------------------------------------------------------------ */
	
	/**
	 *
	 */
	public function __construct($cfg = null)
	{
		$this->ci =& get_instance();
		
		// Load the resources and config
		$this->ci->config->load('assets');
		$this->ci->load->helper(array('file', 'directory', 'string', 'assets'));
		$this->ci->load->library(array('cssmin', 'jsmin'));
		
		// Initialize LessPHP
		$this->less = new lessc();
		
		// Add config to library
		if ($cfg) {
			$this->configure(array_merge($cfg), config_item('assets'));
		}
		else {
			$this->configure(config_item('assets'));
		} // end if
		
	} //end __contruct()
	
	
	/* ------------------------------------------------------------------------------------------ */
	
	/**
	 * Add new CSS file for processing
	 *
	 */
	public function css($file = null)
	{
		if ($file) {
			// Multiple files as array are supported
			if (is_array($file)) {
				foreach ($file as $f) {
					$this->css($f);
				} // end foreach
			}
			
			// Single file
			else {
				$this->_css[] = $file;
				
			} // end if
		} // end if
		
	} // end css()
	
	
	/* ------------------------------------------------------------------------------------------ */
	
	/**
	 * Add new JS file for processing
	 *
	 */
	public function js($file = null)
	{
		if ($file) {
			// Multiple files as array are supported
			if (is_array($file)) {
				foreach ($file as $f) {
					$this->js($f);
				} // end foreach
			}
			
			// Single file
			else {
				$this->_js[] = $file;
				
			} // end if
		} // end if
		
	} // end js()
	
	
	
	/* ------------------------------------------------------------------------------------------ */
	/* !/===> Processing files, generating HTML tags */
	/* ------------------------------------------------------------------------------------------ */
	
	
	/**
	 *
	 */
	public function get($type = 'all')
	{
		$html = '';
		
		if ($type == 'all') {
			$html .= $this->_get_css();
			$html .= $this->_get_js();
		}
		elseif ($type == 'css') {
			$html .= $this->_get_css();
		}
		elseif ($type == 'js') {
			$html .= $this->_get_js();
		} // end if
		
		return $html;
		
	} // end get()
	
	
	/* ------------------------------------------------------------------------------------------ */
	
	/**
	 *
	 */
	private function _get_css()
	{
		$html = '';
		
		if ($this->_css) {
			// Simply return a list of all css tags
			if ($this->env == 'dev' or( ! $this->combine and ! $this->minify)) {
				foreach ($this->_css as $css) {
					$html .= '<link rel="stylesheet" href="'.reduce_double_slashes($this->css_url.'/'.$css).'">'.PHP_EOL;
				} // end foreach
			}
			
			// Process the files
			else {
				$last_modified 	= 0;
				
				// Find last modified file
				foreach ($this->_css as $css) {
					$last_modified 	= max($last_modified, filemtime(realpath($this->css_path.'/'.$css)));
				} // end foreach
				
				// Now check if the file exists in the cache directory
				$file_name = date('YmdHis', $last_modified).'.css';
				$file_path = reduce_double_slashes($this->cache_path.'/'.$file_name);
				if ( ! file_exists($file_path)) {
					$processed 		= '';
					
					// Process files
					foreach ($this->_css as $css) {
						// Get file contents
						$contents = read_file(reduce_double_slashes($this->css_path.'/'.$css));
						
						// Combine
						$processed .= $contents;
						
					} // end foreach
	
					// Less
					if ($this->less_css) 	$processed = $this->less->parse($processed);
					
					// Minify
					if ($this->minify_css) 	$processed = $this->ci->cssmin->minify($processed);
					
					// And save the file
					write_file($file_path, $processed);
					
				} // end if
				
				// HTML tag
				$html .= '<link rel="stylesheet" href="'.reduce_double_slashes($this->cache_url.'/'.$file_name).'">'.PHP_EOL;
				
			} // end if
		} // end if
		
		return $html;
		
	} // end _get_css()
	
	
	/* ------------------------------------------------------------------------------------------ */
	
	/**
	 *
	 */
	private function _get_js()
	{
		$html = '';
		
		if ($this->_js) {
			// Simply return a list of all css tags
			if ($this->env == 'dev' or( ! $this->combine and ! $this->minify)) {
				foreach ($this->_js as $js) {
					$html .= '<script src="'.reduce_double_slashes($this->js_url.'/'.$js).'"></script>'.PHP_EOL;
				} // end foreach
			
			}
			
			// Process the files
			else {
				$last_modified 	= 0;
				
				// Find last modified file
				foreach ($this->_js as $js) {
					$last_modified 	= max($last_modified, filemtime(realpath($this->js_path.'/'.$js)));
				} // end foreach
				
				// Now check if the file exists in the cache directory
				$file_name = date('YmdHis', $last_modified).'.js';
				$file_path = reduce_double_slashes($this->cache_path.'/'.$file_name);
				if ( ! file_exists($file_path)) {
					$processed 		= '';
					
					// Process files
					foreach ($this->_js as $js) {
						// Get file contents
						$contents = read_file(reduce_double_slashes($this->js_path.'/'.$js));
						
						// Combine
						$processed .= $contents;
						
					} // end foreach
	
					// Minify
					if ($this->minify_js) 	$processed = $this->ci->jsmin->minify($processed);
					
					// And save the file
					write_file($file_path, $processed);
					
				} // end if
				
				// HTML tag
				$html .= '<script src="'.reduce_double_slashes($this->cache_url.'/'.$file_name).'"></script>'.PHP_EOL;
				
			} // end if
		} // end if
		
		return $html;
		
	} // end _get_js()
	
	
	
	/* ------------------------------------------------------------------------------------------ */
	/* !/===> Displaying assets */
	/* ------------------------------------------------------------------------------------------ */
	
	
	/**
	 *
	 */
	public function display($type = 'all', $css = null, $js = null, $cfg = null)
	{
		// Configuration
		if ($cfg) $this->configure($cfg);
		
		// Overwrite CSS files
		if ($css) {
			$this->_css = array();
			$this->css($css);
		} // end if
		
		// Overwrite JS files
		if ($js) {
			$this->_js = array();
			$this->js($js);
		} // end if
		
		// Display all the tags
		echo $this->get($type);
		
	} // end display()
	
	
	/* ------------------------------------------------------------------------------------------ */
	
	/**
	 *
	 */
	public function display_css($assets = null, $cfg = null)
	{
		$this->display('css', $assets, null, $cfg);
		
	} // end display_css()
	
	
	/* ------------------------------------------------------------------------------------------ */
	
	/**
	 *
	 */
	public function display_js($assets = null, $cfg = null)
	{
		$this->display('js', null, $assets, $cfg);
		
	} // end display_js()
	
	
	
	/* ------------------------------------------------------------------------------------------ */
	/* !/===> Deleting files */
	/* ------------------------------------------------------------------------------------------ */
	
	/**
	 *
	 */
	public function clear_cache($type = null)
	{
		$files = directory_map($this->cache_path, 1);
		
		if ($files) {
			foreach ($files as $file) {
				if ( ! is_array($file)) {
					$file_path = reduce_double_slashes($this->cache_path.'/'.$file);
					$file_info = pathinfo($file_path);
					
					if (is_file($file_path) and $file_info) {
						// Delete the CSS files
						if ($file_info['extension'] == 'css' and ( ! $type or $type == 'css')) {
							unlink($file_path);
							echo 'Deleted CSS: '.$file;
						} // end if
						
						// Delete the JS files
						if ($file_info['extension'] == 'js' and ( ! $type or $type == 'js')) {
							unlink($file_path);
							echo 'Deleted JS: '.$file;
						} // end if
					} // end if
					
				} // end if
			} // end foreach
		} // end if
		
	} // end empty_cache()
	
	
	/* ------------------------------------------------------------------------------------------ */
	
	/**
	 *
	 */
	public function clear_css_cache()
	{
		return $this->clear_cache('css');
		
	} // end empty_css_cache()
	
	
	/* ------------------------------------------------------------------------------------------ */
	
	/**
	 *
	 */
	public function clear_js_cache()
	{
		return $this->clear_cache('js');
		
	} // end empty_js_cache()
	
	
	
	/* ------------------------------------------------------------------------------------------ */
	/* !/===> Configuration */
	/* ------------------------------------------------------------------------------------------ */
	
	
	/**
	 * Configure the library
	 *
	 */
	public function configure($cfg = null)
	{
		$cfg = array_merge($cfg, config_item('assets'));
		
		if ($cfg and is_array($cfg)) {
			foreach ($cfg as $key=>$val) {
				$this->$key = $val;
				//echo 'CONFIG: ', $key, ' :: ', $val, '<br>';
			} // end foreach
		} // end if
		
		// Prepare all the paths and URI's
		$this->_paths();
		
	} // end configure()
	
	
	/* ------------------------------------------------------------------------------------------ */
	
	/**
	 *
	 */
	private function _paths()
	{
		// Set the assets base path
		$this->base_path = reduce_double_slashes(realpath($this->assets_dir));
		
		// Now set the assets base URL
		$this->base_url = reduce_double_slashes(config_item('base_url').'/'.$this->assets_dir);
		
		// And finally the paths and URL's to the css and js assets
		$this->js_path 		= reduce_double_slashes($this->base_path .'/'.$this->js_dir);
		$this->js_url 		= reduce_double_slashes($this->base_url  .'/'.$this->js_dir);
		$this->css_path 	= reduce_double_slashes($this->base_path .'/'.$this->css_dir);
		$this->css_url 		= reduce_double_slashes($this->base_url  .'/'.$this->css_dir);
		$this->cache_path 	= reduce_double_slashes($this->base_path .'/'.$this->cache_dir);
		$this->cache_url 	= reduce_double_slashes($this->base_url  .'/'.$this->cache_dir);
		
		// Check if all directories exist
		if ( ! is_dir($this->js_path)) {
			if ( ! @mkdir($this->js_path, 0755))    exit('Error with JS directory.');
		} // end if
		if ( ! is_dir($this->css_path)) {
			if ( ! @mkdir($this->css_path, 0755))   exit('Error with CSS directory.');
		} // end if
		if ( ! is_dir($this->cache_path)) {
			if ( ! @mkdir($this->cache_path, 0777)) exit('Error with CACHE directory.');
		} // end if
		
		// Try to make the cache direcory writable
		if (is_dir($this->cache_path) and ! is_really_writable($this->cache_path)) {
			@chmod($this->cache_path, 0777);
		} // end if
		
		// If it's still not writable throw error
		if ( ! is_dir($this->cache_path) or ! is_really_writable($this->cache_path)) {
			exit('Error with CACHE directory.');
		} // end if
		
	} // end _paths()
	
	
	/* ------------------------------------------------------------------------------------------ */
	
} //end Assets


/* End of file assets.php */