<?php
/**
 * Description...
 *
 */

class DN_Loader
{
	// All these are set automatically. Don't mess with them.
	private $_dn_classes = array();
	private $_dn_loaded_files	= array();
  private $_dn_models = array();
	private $_dn_helpers = array();
	private $_dn_varmap = array();
	private $_dn_cached_vars = array();
		
	/**
	 * Class Loader
	 *
	 * This function lets users load and instantiate classes.
	 *
	 * @access	public
	 * @param	string	the name of the class
	 * @param	string	an optional object name
	 * @return	void
	 */	
	public function library($library = '', $object_name = NULL)
	{
		if ($library == '')
		{
			return FALSE;
		}

		if ( ! is_null($params) AND ! is_array($params))
		{
			$params = NULL;
		}
		
		if (is_array($library))
		{
			foreach ($library as $class)
			{
				$this->load_class(DN_LIBRARY_PATH, $class, $object_name);
			}
		}
		else
		{
			$this->load_class(DN_LIBRARY_PATH, $library, $object_name);
		}
	}
	
	/**
	 * Model Loader
	 *
	 * This function lets users load and instantiate models.
	 *
	 * @access	public
	 * @param	string	the name of the class
	 * @param	string	name for the model
	 * @return	void
	 */	
	public function model($model, $object_name = NULL)
	{
    if ($model == '')
	  {
	  	return FALSE;
	  }
	  
	  if (is_array($model))
	  {
	  	foreach ($model as $class)
	  	{
	  		$this->load_class(DN_MODEL_PATH, $class, $object_name);
	  	}
	  }
	  else
	  {
	  	$this->load_class(DN_MODEL_PATH, $model, $object_name);
	  }
	}
	
	/**
	 * Controller Loader
	 *
	 * This function lets users load and instantiate controller classes.
	 *
	 * @access	public
	 * @param	string	the name of the class
	 * @param	string	an optional object name
	 * @return	void
	 */	
	public function controller($library = '', $object_name = NULL)
	{
	  if ($library == '')
	  {
	  	return FALSE;
	  }
	  
	  if ( ! is_null($params) AND ! is_array($params))
	  {
	  	$params = NULL;
	  }
	  
	  if (is_array($library))
	  {
	  	foreach ($library as $class)
	  	{
	  		$this->load_class(DN_CONTROLLER_PATH, $class, $object_name);
	  	}
	  }
	  else
	  {
	  	$this->load_class(DN_CONTROLLER_PATH, $library, $object_name);
	  }
	}
	
	/**
	 * Load View
	 *
	 * This function is used to load a "view" file.  It has three parameters:
	 *
	 * 1. The name of the "view" file to be included.
	 * 2. An associative array of data to be extracted for use in the view.
	 * 3. TRUE/FALSE - whether to return the data or load it.  In
	 * some cases it's advantageous to be able to return data so that
	 * a developer can process it in some way.
	 *
	 * @access	public
	 * @param	string
	 * @param	array
	 * @param	bool
	 * @return	void
	 */
	function view($view, $vars = array(), $return = FALSE)
	{
		return $this->load(array('_dn_view' => $view, '_dn_vars' => $this->object_to_array($vars), '_dn_return' => $return));
	}
	
	/**
	 * Load File
	 *
	 * This is a generic file loader
	 *
	 * @access	public
	 * @param	string
	 * @param	bool
	 * @return	string
	 */
	function file($path, $return = FALSE)
	{
		return $this->load(array('_dn_path' => $path, '_dn_return' => $return));
	}
	
	/**
   * Loader
   *
   * This function is used to load views and files.
   * Variables are prefixed with _x_ to avoid symbol collision with
   * variables made available to view files
   *
   * @access	private
   * @param	array
   * @return	void
   */
  function load($_dn_data)
  {
  	// Set the default data variables
  	foreach (array('_dn_view', '_dn_vars', '_dn_path', '_dn_return') as $_dn_val)
  	{
  		$$_dn_val = ( ! isset($_dn_data[$_dn_val])) ? FALSE : $_dn_data[$_dn_val];
  	}
  
  	// Set the path to the requested file
  	if ($_dn_path == '')
  	{
  		$_dn_ext = pathinfo($_dn_view, PATHINFO_EXTENSION);
  		$_dn_file = ($_dn_ext == '') ? $_dn_view.'.php' : $_dn_view;
  		$_dn_path = DN_VIEW_PATH.'/'.$_dn_file;
  	}
  	else
  	{
  		$_dn_x = explode('/', $_dn_path);
  		$_dn_file = end($_dn_x);
  	}
  	
  	if ( ! file_exists($_dn_path))
  	{
  	  throw new DN_Exceptions(DN_ExceptionsDesc::$descriptions[EC_FILE] . ': '.$_dn_file, DN_ExceptionsDesc::EC_FILE);
  	}
    
  	// This allows anything loaded using $this->load (views, files, etc.)
  	// to become accessible from within the Controller and Model functions.
  	$_dn_App =& DN_Base::app();
		foreach (get_object_vars($_dn_App) as $_dn_key => $_dn_var)
		{
			if ( ! isset($this->$_dn_key))
			{
				$this->$_dn_key =& $_dn_App->$_dn_key;
			}
		}
    
  	/*
  	 * Extract and cache variables
  	 *
  	 * You can either set variables using the dedicated $this->load_vars()
  	 * function or via the second parameter of this function. We'll merge
  	 * the two types and cache them so that views that are embedded within
  	 * other views can have access to these variables.
  	 */	
  	if (is_array($_dn_vars))
  	{
  		$this->_dn_cached_vars = array_merge($this->_dn_cached_vars, $_dn_vars);
  	}
  	extract($this->_dn_cached_vars);
  	
  	// If the PHP installation does not support short tags we'll
  	// do a little string replacement, changing the short tags
  	// to standard PHP echo statements.
  	
  	if ((bool) @ini_get('short_open_tag') === FALSE)
  	{
  		echo eval('?>'.preg_replace("/;*\s*\?>/", "; ?>", str_replace('<?=', '<?php echo ', file_get_contents($_dn_path))));
  	}
  	else
  	{
  		include($_dn_path); // include() vs include_once() allows for multiple views with the same name
  	}
  }

	/**
	 * Load class
	 *
	 * This function loads the requested class.
	 *
	 * @access	public
	 * @param 	string	the item that is being loaded
	 * @param	string	an optional object name
	 * @return 	void
	 */
	private function load_class($path, $class, $object_name = NULL)
	{
		// Get the class name, and while we're at it trim any slashes.  
		// The directory path can be included as part of the class name, 
		// but we don't want a leading slash
		$class = str_replace('.php', '', trim($class, '/'));
	
		// Was the path included with the class name?
		// We look for a slash to determine this
		$subdir = '';
		if (strpos($class, '/') !== FALSE)
		{
			// explode the path so we can separate the filename from the path
			$fi = explode('/', $class);	
			
			// Reset the $class variable now that we know the actual filename
			$class = end($fi);
			
			// Kill the filename from the array
			unset($fi[count($fi)-1]);
			
			// Glue the path back together, sans filename
			$subdir = implode($fi, '/').'/';
		}

		// We'll test for both lowercase and capitalized versions of the file name
		foreach (array(ucfirst($class), strtolower($class)) as $class)
		{
			$subclass = $path.'/'.$subdir.$class.'.php';
			
			// Is this a class extension request?			
			if (file_exists($subclass))
			{
			  // Safety:  Was the class already loaded by a previous call?
			  if (in_array($subclass, $this->_dn_loaded_files))
			  {
			  	// Before we deem this to be a duplicate request, let's see
			  	// if a custom object name is being supplied.  If so, we'll
			  	// return a new instance of the object
			  	if ( ! is_null($object_name))
			  	{
			  		if ( ! isset(DN_Base::app()->$object_name))
			  		{
			  			return $this->init_class($class, $object_name);			
			  		}
			  	}
			  	
			  	$is_duplicate = TRUE;
			  	return;
			  }
			  
				include_once($subclass);
				$this->_dn_loaded_files[] = $subclass;
	
				return $this->init_class($class, $object_name);
			}
			
			// Lets search for the requested library file and load it.
			$is_duplicate = FALSE;
			for ($i = 1; $i < 3; $i++)
			{
				$filepath = $path.'/'.$subdir.$class.'.php';
				
				// Does the file exist?  No?  Bummer...
				if ( ! file_exists($filepath))
				{
					continue;
				}
				
				// Safety:  Was the class already loaded by a previous call?
				if (in_array($filepath, $this->_dn_loaded_files))
				{
					// Before we deem this to be a duplicate request, let's see
					// if a custom object name is being supplied.  If so, we'll
					// return a new instance of the object
					if ( ! is_null(DN_Base::app()->$object_name))
					{
						return $this->init_class($class, $object_name);
					}
				
					$is_duplicate = TRUE;
					return;
				}
				
				include_once($filepath);
				$this->_dn_loaded_files[] = $filepath;
				return $this->init_class($class, $object_name);
			}
		} // END FOREACH
		
		// If we got this far we were unable to find the requested class.
		// We do not issue errors if the load call failed due to a duplicate request
		if ($is_duplicate == FALSE)
		{
		  throw new DN_Exceptions(DN_ExceptionsDesc::$descriptions[EC_OBJECT] . ': '.$class, DN_ExceptionsDesc::EC_OBJECT);
		}
	}
	
	/**
	* Instantiates a class
	*
	* @access	private
	* @param	string
	* @param	string	an optional object name
	* @return	null
	*/
  private function init_class($class, $object_name = NULL)
	{	
		// Is the class name valid?
		if ( ! class_exists($class))
		{
		  throw new DN_Exceptions(DN_ExceptionsDesc::$descriptions[EC_OBJECT] . ': '.$class, DN_ExceptionsDesc::EC_OBJECT);
		}
			
		// Set the variable name we will assign the class to
		// Was a custom class name supplied?  If so we'll use it
		$class = strtolower($class);
			
		if (is_null($object_name))
		{
			$classvar = ( ! isset($this->_dn_varmap[$class])) ? $class : $this->_dn_varmap[$class];
		}
		else
		{
			$classvar = $object_name;
		}
	  
		// Save the class name and object name
		$this->_dn_classes[$class] = $classvar;
	
		// Instantiate the class
		DN_Base::app()->$classvar = new $class;
	}
	
	/**
	 * Object to Array
	 *
	 * Takes an object as input and converts the class variables to array key/vals
	 *
	 * @access	private
	 * @param	object
	 * @return	array
	 */
	function object_to_array($object)
	{
		return (is_object($object)) ? get_object_vars($object) : $object;
	}
}
