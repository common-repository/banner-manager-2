<?php

class DN_Upload
{
  public $errors;
  public $orig_name;
  
  public $upload_path;
  public $max_size;
	public $max_width;
	public $max_height;
  public $max_filename;
  public $is_image;
  public $remove_spaces;
  public $overwrite;
  public $use_def_wp_upload;
  public $create_dir_if_not_exists;
  public $custom_name;
  public $allowed_types;
  
  
  public $field;
  public $file_temp;
  public $file_name;
  public $file_size;
  public $file_type;
  public $file_ext;
  
	public $mimes = array();
  
  public $image_width;
  public $image_height;
  public $image_type;
  public $image_size_str;

  public function initialize($args){
    $default_args = array(
      'field' => 'user_field',
      'upload_path' => '/',
      'max_size' => 0,
      'max_width' => 0,
      'max_height' => 0,
      'max_filename' => 0, 
      'is_image' => TRUE,
      'remove_spaces' => TRUE, 
      'overwrite' => TRUE,
      'use_def_wp_upload' => TRUE,
      'create_dir_if_not_exists' => TRUE,
      'custom_name' => 'encrypt',
      'allowed_types' => 'jpg|gif|png'
    );
    $args = array_merge($default_args, $args);
    $this->assign_args($args);
		// Is the upload path valid?
		if ( ! $this->validate_upload_path())
		{
			// errors will already be set by validate_upload_path() so just return FALSE
			return FALSE;
		}
  }

  public function upload()
  {
    $field = $this->field;
		// Is $_FILES[$field] set? If not, no reason to continue.
		if (!isset($_FILES[$field]))
		{
			$this->errors = 'upload_no_file_selected';
      return FALSE;
		}
    // Was the file able to be uploaded? If not, determine the reason why.
		if (!is_uploaded_file($_FILES[$field]['tmp_name']))
		{
      $error = ( ! isset($_FILES[$field]['error'])) ? 4 : $_FILES[$field]['error'];
			switch($error)
			{
				case 1:	// UPLOAD_ERR_INI_SIZE
					$this->errors = 'upload_file_exceeds_limit';
					break;
				case 2: // UPLOAD_ERR_FORM_SIZE
					$this->errors = 'upload_file_exceeds_form_limit';
					break;
				case 3: // UPLOAD_ERR_PARTIAL
				   $this->errors = 'upload_file_partial';
					break;
				case 4: // UPLOAD_ERR_NO_FILE
				   $this->errors = 'upload_no_file_selected';
					break;
				case 6: // UPLOAD_ERR_NO_TMP_DIR
					$this->errors = 'upload_no_temp_directory';
					break;
				case 7: // UPLOAD_ERR_CANT_WRITE
					$this->errors = 'upload_unable_to_write_file';
					break;
				case 8: // UPLOAD_ERR_EXTENSION
					$this->errors = 'upload_stopped_by_extension';
					break;
				default :   
          $this->errors = 'upload_no_file_selected';
					break;
      }
      return FALSE;
    }
		$this->file_temp = $_FILES[$field]['tmp_name'];		
		$this->file_name = $this->_prep_filename($_FILES[$field]['name']);
		$this->file_size = $_FILES[$field]['size'];		
		$this->file_type = preg_replace("/^(.+?);.*$/", "\\1", $_FILES[$field]['type']);
		$this->file_type = strtolower($this->file_type);
		$this->file_ext	 = $this->get_extension($_FILES[$field]['name']);
		// Is the file type allowed to be uploaded?
		if ( ! $this->is_allowed_filetype())
		{
			$this->errors = 'upload_invalid_filetype';
			return FALSE;
		}
		// Is the file size within the allowed maximum?
		if ( ! $this->is_allowed_filesize())
		{
			$this->errors = 'upload_invalid_filesize';
			return FALSE;
		}
		// Are the image dimensions within the allowed size?
		// Note: This can fail if the server has an open_basdir restriction.
		if ( ! $this->is_allowed_dimensions())
		{
			$this->errors = 'upload_invalid_dimensions';
			return FALSE;
		}
		// Sanitize the file name for security
		$this->file_name = $this->clean_file_name($this->file_name);
		// Truncate the file name if it's too long
		if ($this->max_filename > 0)
		{
			$this->file_name = $this->limit_filename_length($this->file_name, $this->max_filename);
		}
		// Remove white spaces in the name
		if ($this->remove_spaces == TRUE)
		{
			$this->file_name = preg_replace("/\s+/", "_", $this->file_name);
		}
		/*
		 * Validate the file name
		 * This function appends an number onto the end of
		 * the file if one with the same name already exists.
		 * If it returns false there was a problem.
		 */
		$this->orig_name = $this->file_name;

    $this->file_name = $this->set_filename($this->upload_path, $this->file_name);
    if ($this->file_name === FALSE)
    {
      return FALSE;
    }
		/*
		 * Move the file to the final destination
		 * To deal with different server configurations
		 * we'll attempt to use copy() first.  If that fails
		 * we'll use move_uploaded_file().  One of the two should
		 * reliably work in most environments
		 */
    if( ! is_dir($this->upload_path) ){
      mkdir($this->upload_path , 0777, TRUE);
    }
    
		if ( ! @copy($this->file_temp, $this->upload_path.$this->file_name))
		{
			if ( ! @move_uploaded_file($this->file_temp, $this->upload_path.$this->file_name))
			{
				 $this->errors = 'upload_destination_error';
				 return FALSE;
			}
		}
		$this->set_image_properties($this->upload_path.$this->file_name);
    
		return array(
				'image_width' => $this->image_width,
				'image_height' => $this->image_height,
				'image_type' => $this->image_type,
				'image_size_str' => $this->image_size_str,
        'path_filename' => $this->upload_path.$this->file_name,
        'path' => $this->upload_path,
        'filename' => $this->file_name
      );
  }
  
  private function assign_args($args){
    $this->field = $args['field'];
    $this->upload_path = $args['upload_path'];
    $this->max_size = $args['max_size'];
    $this->max_width = $args['max_width'];
    $this->max_height = $args['max_height'];
    $this->max_filename = $args['max_filename'];
    $this->is_image = $args['is_image'];
    $this->remove_spaces = $args['remove_spaces'];
    $this->overwrite = $args['overwrite'];
    $this->use_def_wp_upload = $args['use_def_wp_upload'];
    $this->create_dir_if_not_exists = $args['create_dir_if_not_exists'];
    $this->custom_name = $args['custom_name'];
    $this->allowed_types = $args['allowed_types'];
  }
  
	private function is_allowed_filetype()
	{
    $this->set_allowed_types($this->allowed_types);

		if (count($this->allowed_types) == 0 OR ! is_array($this->allowed_types))
		{

			$this->errors = 'upload_no_file_types';
			return FALSE;
		}

		$image_types = array('gif', 'jpg', 'jpeg', 'png', 'jpe');

		foreach ($this->allowed_types as $val)
		{
			$mime = $this->mimes_types(strtolower($val));
      
			// Images get some additional checks
			if (in_array($val, $image_types))
			{
				if (getimagesize($this->file_temp) === FALSE)
				{
					return FALSE;
				}
			}

			if (is_array($mime))
			{
				if (in_array($this->file_type, $mime, TRUE))
				{
					return TRUE;
				}
			}
			else
			{
				if ($mime == $this->file_type)
				{
					return TRUE;
				}	
			}		
		}
    
		return FALSE;
	}
  
	private function is_allowed_filesize()
	{
		if ($this->max_size != 0  AND  $this->file_size > $this->max_size)
		{
			return FALSE;
		}
		else
		{
			return TRUE;
		}
	}
  
	private function is_allowed_dimensions()
	{
		if ( ! $this->is_image())
		{
			return TRUE;
		}

		if (function_exists('getimagesize'))
		{
			$D = @getimagesize($this->file_temp);

			if ($this->max_width > 0 AND $D['0'] > $this->max_width)
			{
				return FALSE;
			}

			if ($this->max_height > 0 AND $D['1'] > $this->max_height)
			{
				return FALSE;
			}

			return TRUE;
		}

		return TRUE;
	}
  
	private function is_image()
	{
		// IE will sometimes return odd mime-types during upload, so here we just standardize all
		// jpegs or pngs to the same file type.

		$png_mimes  = array('image/x-png');
		$jpeg_mimes = array('image/jpg', 'image/jpe', 'image/jpeg', 'image/pjpeg');
		
		if (in_array($this->file_type, $png_mimes))
		{
			$this->file_type = 'image/png';
		}
		
		if (in_array($this->file_type, $jpeg_mimes))
		{
			$this->file_type = 'image/jpeg';
		}

		$img_mimes = array(
							'image/gif',
							'image/jpeg',
							'image/png',
						   );

		return (in_array($this->file_type, $img_mimes, TRUE)) ? TRUE : FALSE;
	}
  
	private function get_extension($filename)
	{
		$x = explode('.', $filename);
		return '.'.strtolower(end($x));
	}
  
	function set_filename($path, $filename)
	{
		if ($this->custom_name == 'encrypt')
		{		
			mt_srand();
			$filename = md5(uniqid(mt_rand())).$this->file_ext;	
		}
    else
    {
      $filename = $this->custom_name.$this->file_ext;	
    }
	
		if ( ! file_exists($path.$filename))
		{
			return $filename;
		}
    elseif( file_exists($path.$filename) AND $this->overwrite == FALSE)
    {
      $filename = str_replace($this->file_ext, '', $filename);
      
      $new_filename = '';
      for ($i = 1; $i < 100; $i++)
      {			
        if ( ! file_exists($path.$filename.$i.$this->file_ext))
        {
          $new_filename = $filename.$i.$this->file_ext;
          break;
        }
      }
      if ($new_filename == '')
      {
        $this->errors = 'upload_bad_filename';
        return FALSE;
      }
      else
      {
        return $new_filename;
      }
    }elseif(file_exists($path.$filename) AND $this->overwrite == TRUE){
      return $filename;
    }
	}
  
	function set_allowed_types($types)
	{
		$this->allowed_types = explode('|', $types);
	}
  
	private function _prep_filename($filename)
	{
		if (strpos($filename, '.') === FALSE)
		{
			return $filename;
		}

		$parts		= explode('.', $filename);
		$ext		= array_pop($parts);
		$filename	= array_shift($parts);

		foreach ($parts as $part)
		{
			if ($this->mimes_types(strtolower($part)) === FALSE)
			{
				$filename .= '.'.$part.'_';
			}
			else
			{
				$filename .= '.'.$part;
			}
		}

		// file name override, since the exact name is provided, no need to
		// run it through a $this->mimes check.
		if ($this->file_name != '')
		{
			$filename = $this->file_name;
		}

		$filename .= '.'.$ext;
		
		return $filename;
	}
  
  public function get_def_wp_upload_path(){
      $def_wp_upload_array = wp_upload_dir();
      $def_wp_upload = $def_wp_upload_array['basedir'];
      return $def_wp_upload;
  }
  
	private function validate_upload_path()
	{
		if ($this->upload_path == '')
		{
			$this->errors = 'upload_no_filepath';
			return FALSE;
		}
		if (function_exists('realpath') AND @realpath($this->upload_path) !== FALSE)
		{
			$this->upload_path = str_replace("\\", "/", realpath($this->upload_path));
		}
    if($this->use_def_wp_upload)
    {
      $this->upload_path = $this->get_def_wp_upload_path() . $this->upload_path;
    }
    if($this->create_dir_if_not_exists)
    {
      $deep_path = explode('/', $this->upload_path);
      $n_path = 0;
      while($n_path < count($deep_path)){
        if(!file_exists($deep_path[$n_path]))
        {
          @mkdir($deep_path[$n_path]."/");
        }
        $n_path++;
      }
    }
		if ( ! @is_dir($this->upload_path))
		{
			$this->errors = 'upload_no_filepath';
			return FALSE;
		}
    $this->upload_path = preg_replace("/(.+?)\/*$/", "\\1/",  $this->upload_path);
		
		return TRUE;
	}
  
	private function limit_filename_length($filename, $length)
	{
		if (strlen($filename) < $length)
		{
			return $filename;
		}
	
		$ext = '';
		if (strpos($filename, '.') !== FALSE)
		{
			$parts		= explode('.', $filename);
			$ext		= '.'.array_pop($parts);
			$filename	= implode('.', $parts);
		}
	
		return substr($filename, 0, ($length - strlen($ext))).$ext;
	}
  
	private function clean_file_name($filename)
	{
		$bad = array(
						"<!--",
						"-->",
						"'",
						"<",
						">",
						'"',
						'&',
						'$',
						'=',
						';',
						'?',
						'/',
						"%20",
						"%22",
						"%3c",		// <
						"%253c", 	// <
						"%3e", 		// >
						"%0e", 		// >
						"%28", 		// (
						"%29", 		// )
						"%2528", 	// (
						"%26", 		// &
						"%24", 		// $
						"%3f", 		// ?
						"%3b", 		// ;
						"%3d"		// =
					);
					
		$filename = str_replace($bad, '', $filename);

		return stripslashes($filename);
	}
  
	private function mimes_types($mime)
	{
		global $mimes;
    
		if (count($this->mimes) == 0)
		{
			if (@require_once(DN_CONFIG_PATH.'/DN_Mimes.php'))
			{
				$this->mimes = $mimes;
				unset($mimes);
			}
		}
	
		return ( ! isset($this->mimes[$mime])) ? FALSE : $this->mimes[$mime];
	}
  
	private function set_image_properties($path = '')
	{
		if ( ! $this->is_image())
		{
			return;
		}

		if (function_exists('getimagesize'))
		{
			if (FALSE !== ($D = @getimagesize($path)))
			{	
				$types = array(1 => 'gif', 2 => 'jpeg', 3 => 'png');

				$this->image_width		= $D['0'];
				$this->image_height		= $D['1'];
				$this->image_type		= ( ! isset($types[$D['2']])) ? 'unknown' : $types[$D['2']];
				$this->image_size_str	= $D['3'];  // string containing height and width
			}
		}
	}
}
?>
