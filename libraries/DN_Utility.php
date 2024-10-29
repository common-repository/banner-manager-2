<?php

class DN_Utility
{
  /* never allowed, string replacement */
  private $never_allowed_str = array(
    'document.cookie'	=> '[removed]',
    'document.write'	=> '[removed]',
    '.parentNode'		=> '[removed]',
    '.innerHTML'		=> '[removed]',
    'window.location'	=> '[removed]',
    '-moz-binding'		=> '[removed]',
    '<!--'				=> '&lt;!--',
    '-->'				=> '--&gt;',
    '<![CDATA['			=> '&lt;![CDATA['
  );

  /* never allowed, regex replacement */
  private $never_allowed_regex = array(
    "javascript\s*:"			=> '[removed]',
    "expression\s*(\(|&\#40;)"	=> '[removed]', // CSS and IE
    "vbscript\s*:"				=> '[removed]', // IE, surprise!
    "Redirect\s+302"			=> '[removed]'
  );
  
  /**
   * Cut the text and add suffix.
   *
   * @param string $text Plain text
   * @param int $length Number length of text to be cutted
   * @Param string $suffix optional Suffix text to add
   * @return string
   */
  public function cut_text($text, $length, $suffix = '...')
  {
    if (strlen($text) > $length)
    {
      return substr($text, 0, $length).$suffix;
    }

    return $text;
  }
  
  public function time_ago($date, $granularity = 2)
  {
    $date = strtotime($date);
    $difference = time() - $date;
    $periods = array(
      'decade' => 315360000,
      'year' => 31536000,
      'month' => 2628000,
      'week' => 604800, 
      'day' => 86400,
      'hour' => 3600,
      'min' => 60,
      'sec' => 1
    );
                     
    foreach ($periods as $key => $value)
    {
      if ($difference >= $value)
      {
        $time = floor($difference/$value);
        $difference %= $value;
        $retval .= ($retval ? ' ' : '').$time.' ';
        $retval .= (($time > 1) ? __($key.'s', 'fi_ulf') : __($key, 'fi_ulf'));
        $granularity--;
      }
      
      if ($granularity == '0')
      {
        break;
      }
    }
    
    return $retval.' '.__('ago', 'fi_ulf');      
  }
  
  /**
  * array_walk callback method for htmlentities()
  *
  * @static
  * @access private
  * @param string $string (required): the string to update
  * @param mixed $key (ignored): the array key of the string (not needed but passed automatically by array_walk)
  */
  public function _htmlentities(&$string, $key)
  {
    $string = htmlentities($string, ENT_COMPAT, 'UTF-8');
  }

  /**
  * array_walk callback method for trim()
  *
  * @static
  * @access private
  * @param string $string (required): the string to update
  * @param mixed $key (ignored): the array key of the string (not needed but passed automatically by array_walk)
  */
  public function _trim(&$string, $key)
  {
    $string = trim($string);
  }

  /**
  * array_walk callback method for stripslashes()
  *
  * @static
  * @access private
  * @param string $string (required): the string to update
  * @param mixed $key (ignored): the array key of the string (not needed but passed automatically by array_walk)
  */
  public function _stripslashes(&$string, $key)
  {
    $string = stripslashes($string);
  }
	
	public function escape_data($data)
	{
		return strip_tags(addslashes($data));
	}  
	
	public function extractArgument($params, $name)
	{
		$ix = -1;
		$iy = -1;
		if (strlen($params) != 0)
		{
			$args = strtolower($params);
			$arg = strtolower($name).'=';
			$ix = strpos($args, $arg);
			if ($ix > 0)
			{
				$ix = $ix + strlen($arg);
				$iy = strpos(substr($args, $ix, strlen($args)), '&');
				if (!$iy)
				{
					$iy = strlen($args);
				}
			}
		}
		return $argument = ($ix > 0) ? substr($params, $ix, $iy) : '';
	}

  public function xss_clean($str, $is_image = FALSE)
  {
    /*
    * Is the string an array?
    *
    */
    if (is_array($str))
    {
      while (list($key) = each($str))
      {
        $str[$key] = $this->xss_clean($str[$key]);
      }
    
      return $str;
    }
    
    /*
    * Remove Invisible Characters
    */
    $str = $this->remove_invisible_characters($str);
    
    /*
    * Protect GET variables in URLs
    */
    
    // 901119URL5918AMP18930PROTECT8198
    
    $str = preg_replace('|\&([a-z\_0-9]+)\=([a-z\_0-9]+)|i', $this->xss_hash()."\\1=\\2", $str);
    
    /*
    * Validate standard character entities
    *
    * Add a semicolon if missing.  We do this to enable
    * the conversion of entities to ASCII later.
    *
    */
    $str = preg_replace('#(&\#?[0-9a-z]{2,})([\x00-\x20])*;?#i', "\\1;\\2", $str);
    
    /*
    * Validate UTF16 two byte encoding (x00) 
    *
    * Just as above, adds a semicolon if missing.
    *
    */
    $str = preg_replace('#(&\#x?)([0-9A-F]+);?#i',"\\1\\2;",$str);
    
    /*
    * Un-Protect GET variables in URLs
    */
    $str = str_replace($this->xss_hash(), '&', $str);
    
    /*
    * URL Decode
    *
    * Just in case stuff like this is submitted:
    *
    * <a href="http://%77%77%77%2E%67%6F%6F%67%6C%65%2E%63%6F%6D">Google</a>
    *
    * Note: Use rawurldecode() so it does not remove plus signs
    *
    */
    $str = rawurldecode($str);
    
    /*
    * Convert character entities to ASCII 
    *
    * This permits our tests below to work reliably.
    * We only convert entities that are within tags since
    * these are the ones that will pose security problems.
    *
    */
    
    $str = preg_replace_callback("/[a-z]+=([\'\"]).*?\\1/si", array($this, 'convert_attribute'), $str);
    
    $str = preg_replace_callback("/<\w+.*?(?=>|<|$)/si", array($this, 'html_entity_decode_callback'), $str);
    
    /*
    * Remove Invisible Characters Again!
    */
    $str = $this->remove_invisible_characters($str);
    
    /*
    * Convert all tabs to spaces
    *
    * This prevents strings like this: ja	vascript
    * NOTE: we deal with spaces between characters later.
    * NOTE: preg_replace was found to be amazingly slow here on large blocks of data,
    * so we use str_replace.
    *
    */
    
    if (strpos($str, "\t") !== FALSE)
    {
      $str = str_replace("\t", ' ', $str);
    }
    
    /*
    * Capture converted string for later comparison
    */
    $converted_string = $str;
    
    /*
    * Not Allowed Under Any Conditions
    */
    
    foreach ($this->never_allowed_str as $key => $val)
    {
      $str = str_replace($key, $val, $str);   
    }
    
    foreach ($this->never_allowed_regex as $key => $val)
    {
      $str = preg_replace("#".$key."#i", $val, $str);   
    }
    
    /*
    * Makes PHP tags safe
    *
    *  Note: XML tags are inadvertently replaced too:
    *
    *	<?xml
    *
    * But it doesn't seem to pose a problem.
    *
    */
    if ($is_image === TRUE)
    {
      // Images have a tendency to have the PHP short opening and closing tags every so often
      // so we skip those and only do the long opening tags.
      $str = preg_replace('/<\?(php)/i', "&lt;?\\1", $str);
    }
    else
    {
      $str = str_replace(array('<?', '?'.'>'),  array('&lt;?', '?&gt;'), $str);
    }
    
    /*
    * Compact any exploded words
    *
    * This corrects words like:  j a v a s c r i p t
    * These words are compacted back to their correct state.
    *
    */
    $words = array('javascript', 'expression', 'vbscript', 'script', 'applet', 'alert', 'document', 'write', 'cookie', 'window');
    foreach ($words as $word)
    {
      $temp = '';
    
      for ($i = 0, $wordlen = strlen($word); $i < $wordlen; $i++)
      {
        $temp .= substr($word, $i, 1)."\s*";
      }
    
      // We only want to do this when it is followed by a non-word character
      // That way valid stuff like "dealer to" does not become "dealerto"
      $str = preg_replace_callback('#('.substr($temp, 0, -3).')(\W)#is', array($this, 'compact_exploded_words'), $str);
    }
    
    /*
    * Remove disallowed Javascript in links or img tags
    * We used to do some version comparisons and use of stripos for PHP5, but it is dog slow compared
    * to these simplified non-capturing preg_match(), especially if the pattern exists in the string
    */
    do
    {
      $original = $str;
    
      if (preg_match("/<a/i", $str))
      {
        $str = preg_replace_callback("#<a\s+([^>]*?)(>|$)#si", array($this, 'js_link_removal'), $str);
      }
    
      if (preg_match("/<img/i", $str))
      {
        $str = preg_replace_callback("#<img\s+([^>]*?)(\s?/?>|$)#si", array($this, 'js_img_removal'), $str);
      }
    
      if (preg_match("/script/i", $str) OR preg_match("/xss/i", $str))
      {
        $str = preg_replace("#<(/*)(script|xss)(.*?)\>#si", '[removed]', $str);
      }
    }
    while($original != $str);
    
    unset($original);
    
    /*
    * Remove JavaScript Event Handlers
    *
    * Note: This code is a little blunt.  It removes
    * the event handler and anything up to the closing >,
    * but it's unlikely to be a problem.
    *
    */
    $event_handlers = array('[^a-z_\-]on\w*','xmlns');
    
    if ($is_image === TRUE)
    {
      /*
      * Adobe Photoshop puts XML metadata into JFIF images, including namespacing, 
      * so we have to allow this for images. -Paul
      */
      unset($event_handlers[array_search('xmlns', $event_handlers)]);
    }
    
    $str = preg_replace("#<([^><]+?)(".implode('|', $event_handlers).")(\s*=\s*[^><]*)([><]*)#i", "<\\1\\4", $str);
    
    /*
    * Sanitize naughty HTML elements
    *
    * If a tag containing any of the words in the list
    * below is found, the tag gets converted to entities.
    *
    * So this: <blink>
    * Becomes: &lt;blink&gt;
    *
    */
    $naughty = 'alert|applet|audio|basefont|base|behavior|bgsound|blink|body|embed|expression|form|frameset|frame|head|html|ilayer|iframe|input|isindex|layer|link|meta|object|plaintext|style|script|textarea|title|video|xml|xss';
    $str = preg_replace_callback('#<(/*\s*)('.$naughty.')([^><]*)([><]*)#is', array($this, 'sanitize_naughty_html'), $str);
    
    /*
    * Sanitize naughty scripting elements
    *
    * Similar to above, only instead of looking for
    * tags it looks for PHP and JavaScript commands
    * that are disallowed.  Rather than removing the
    * code, it simply converts the parenthesis to entities
    * rendering the code un-executable.
    *
    * For example:	eval('some code')
    * Becomes:		eval&#40;'some code'&#41;
    *
    */
    $str = preg_replace('#(alert|cmd|passthru|eval|exec|expression|system|fopen|fsockopen|file|file_get_contents|readfile|unlink)(\s*)\((.*?)\)#si', "\\1\\2&#40;\\3&#41;", $str);
    
    /*
    * Final clean up
    *
    * This adds a bit of extra precaution in case
    * something got through the above filters
    *
    */
    foreach ($this->never_allowed_str as $key => $val)
    {
      $str = str_replace($key, $val, $str);   
    }
    
    foreach ($this->never_allowed_regex as $key => $val)
    {
      $str = preg_replace("#".$key."#i", $val, $str);
    }
    
    /*
    *  Images are Handled in a Special Way
    *  - Essentially, we want to know that after all of the character conversion is done whether
    *  any unwanted, likely XSS, code was found.  If not, we return TRUE, as the image is clean.
    *  However, if the string post-conversion does not matched the string post-removal of XSS,
    *  then it fails, as there was unwanted XSS code found and removed/changed during processing.
    */
    
    if ($is_image === TRUE)
    {
      if ($str == $converted_string)
      {
        return TRUE;
      }
      else
      {
        return FALSE;
      }
    }
    
    return $str;
  }

  /**
  * Random Hash for protecting URLs
  *
  * @access	public
  * @return	string
  */
  function xss_hash()
  {
    if (phpversion() >= 4.2)
      mt_srand();
    else
      mt_srand(hexdec(substr(md5(microtime()), -8)) & 0x7fffffff);

    return (md5(time() + mt_rand(0, 1999999999)));
  }

  /**
  * Remove Invisible Characters
  *
  * This prevents sandwiching null characters
  * between ascii characters, like Java\0script.
  *
  * @access	public
  * @param	string
  * @return	string
  */
  public function remove_invisible_characters($str)
  {
    // every control character except newline (dec 10), carriage return (dec 13), and horizontal tab (dec 09),
    $non_displayables = array(
      '/%0[0-8bcef]/',			// url encoded 00-08, 11, 12, 14, 15
      '/%1[0-9a-f]/',				// url encoded 16-31
      '/[\x00-\x08]/',			// 00-08
      '/\x0b/', '/\x0c/',			// 11, 12
      '/[\x0e-\x1f]/'				// 14-31
    );

    do
    {
      $cleaned = $str;
      $str = preg_replace($non_displayables, '', $str);
    }
    while ($cleaned != $str);

    return $str;
  }

  /**
  * Attribute Conversion
  *
  * Used as a callback for XSS Clean
  *
  * @access	public
  * @param	array
  * @return	string
  */
  public function convert_attribute($match)
  {
    return str_replace(array('>', '<', '\\'), array('&gt;', '&lt;', '\\\\'), $match[0]);
  }

  /**
  * HTML Entity Decode Callback
  *
  * Used as a callback for XSS Clean
  *
  * @access	public
  * @param	array
  * @return	string
  */
  public function html_entity_decode_callback($match)
  {
    return $this->html_entity_decode($match[0], 'UTF-8');
  }

  /**
  * Compact Exploded Words
  *
  * Callback function for xss_clean() to remove whitespace from
  * things like j a v a s c r i p t
  *
  * @access	public
  * @param	type
  * @return	type
  */
  public function compact_exploded_words($matches)
  {
    return preg_replace('/\s+/s', '', $matches[1]).$matches[2];
  }

  /**
  * JS Link Removal
  *
  * Callback function for xss_clean() to sanitize links
  * This limits the PCRE backtracks, making it more performance friendly
  * and prevents PREG_BACKTRACK_LIMIT_ERROR from being triggered in
  * PHP 5.2+ on link-heavy strings
  *
  * @access	private
  * @param	array
  * @return	string
  */
  public function js_link_removal($match)
  {
    $attributes = $this->filter_attributes(str_replace(array('<', '>'), '', $match[1]));
    return str_replace($match[1], preg_replace("#href=.*?(alert\(|alert&\#40;|javascript\:|charset\=|window\.|document\.|\.cookie|<script|<xss|base64\s*,)#si", "", $attributes), $match[0]);
  }

  /**
  * JS Image Removal
  *
  * Callback function for xss_clean() to sanitize image tags
  * This limits the PCRE backtracks, making it more performance friendly
  * and prevents PREG_BACKTRACK_LIMIT_ERROR from being triggered in
  * PHP 5.2+ on image tag heavy strings
  *
  * @access	private
  * @param	array
  * @return	string
  */
  public function js_img_removal($match)
  {
    $attributes = $this->filter_attributes(str_replace(array('<', '>'), '', $match[1]));
    return str_replace($match[1], preg_replace("#src=.*?(alert\(|alert&\#40;|javascript\:|charset\=|window\.|document\.|\.cookie|<script|<xss|base64\s*,)#si", "", $attributes), $match[0]);
  }

  /**
  * Sanitize Naughty HTML
  *
  * Callback function for xss_clean() to remove naughty HTML elements
  *
  * @access	private
  * @param	array
  * @return	string
  */
  public function sanitize_naughty_html($matches)
  {
    // encode opening brace
    $str = '&lt;'.$matches[1].$matches[2].$matches[3];

    // encode captured opening or closing brace to prevent recursive vectors
    $str .= str_replace(array('>', '<'), array('&gt;', '&lt;'), $matches[4]);

    return $str;
  }

  /**
  * HTML Entities Decode
  *
  * This function is a replacement for html_entity_decode()
  *
  * In some versions of PHP the native function does not work
  * when UTF-8 is the specified character set, so this gives us
  * a work-around.  More info here:
  * http://bugs.php.net/bug.php?id=25670
  *
  * @access	private
  * @param	string
  * @param	string
  * @return	string
  */
  /* -------------------------------------------------
  /*  Replacement for html_entity_decode()
  /* -------------------------------------------------*/

  /*
  NOTE: html_entity_decode() has a bug in some PHP versions when UTF-8 is the
  character set, and the PHP developers said they were not back porting the
  fix to versions other than PHP 5.x.
  */
  public function html_entity_decode($str, $charset='UTF-8')
  {
    if (stristr($str, '&') === FALSE) return $str;

    // The reason we are not using html_entity_decode() by itself is because
    // while it is not technically correct to leave out the semicolon
    // at the end of an entity most browsers will still interpret the entity
    // correctly.  html_entity_decode() does not convert entities without
    // semicolons, so we are left with our own little solution here. Bummer.

    if (function_exists('html_entity_decode') && (strtolower($charset) != 'utf-8' OR version_compare(phpversion(), '5.0.0', '>=')))
    {
      $str = html_entity_decode($str, ENT_COMPAT, $charset);
      $str = preg_replace('~&#x(0*[0-9a-f]{2,5})~ei', 'chr(hexdec("\\1"))', $str);
      return preg_replace('~&#([0-9]{2,4})~e', 'chr(\\1)', $str);
    }

    // Numeric Entities
    $str = preg_replace('~&#x(0*[0-9a-f]{2,5});{0,1}~ei', 'chr(hexdec("\\1"))', $str);
    $str = preg_replace('~&#([0-9]{2,4});{0,1}~e', 'chr(\\1)', $str);

    // Literal Entities - Slightly slow so we do another check
    if (stristr($str, '&') === FALSE)
    {
      $str = strtr($str, array_flip(get_html_translation_table(HTML_ENTITIES)));
    }

    return $str;
  }

  /**
  * Filter Attributes
  *
  * Filters tag attributes for consistency and safety
  *
  * @access	public
  * @param	string
  * @return	string
  */
  public function filter_attributes($str)
  {
    $out = '';

    if (preg_match_all('#\s*[a-z\-]+\s*=\s*(\042|\047)([^\\1]*?)\\1#is', $str, $matches))
    {
      foreach ($matches[0] as $match)
      {
        $out .= preg_replace("#/\*.*?\*/#s", '', $match);
      }
    }

    return $out;
  }
  
  public function fsockopen_err($errnum, $error = '')
  {
    if (!_is_integer($errors[0]))
    {
      return $error;
    }
    
    $fsockopen_errors = array(
      0 => 'Success',
      1 => 'Operation not permitted',
      2 => 'No such file or directory',
      3 => 'No such process',
      4 => 'Interrupted system call - DNS lookup failure',
      5 => 'Input/output error - Connection refused or timed out',
      6 => 'No such device or address',
      7 => 'Argument list too long',
      8 => 'Exec format error',
      9 => 'Bad file descriptor',
      10 => 'No child processes',
      11 => 'Resource temporarily unavailable',
      12 => 'Cannot allocate memory',
      13 => 'Permission denied',
      14 => 'Bad address',
      15 => 'Block device required',
      16 => 'Device or resource busy',
      17 => 'File exists',
      18 => 'Invalid cross-device link',
      19 => 'No such device',
      20 => 'Not a directory',
      21 => 'Is a directory',
      22 => 'Invalid argument',
      23 => 'Too many open files in system',
      24 => 'Too many open files',
      25 => 'Inappropriate ioctl for device',
      26 => 'Text file busy',
      27 => 'File too large',
      28 => 'No space left on device',
      29 => 'Illegal seek',
      30 => 'Read-only file system',
      31 => 'Too many links',
      32 => 'Broken pipe',
      33 => 'Numerical argument out of domain',
      34 => 'Numerical result out of range',
      35 => 'Resource deadlock avoided',
      36 => 'File name too long',
      37 => 'No locks available',
      38 => 'Function not implemented',
      39 => 'Directory not empty',
      40 => 'Too many levels of symbolic links',
      41 => 'Unknown error 41',
      42 => 'No message of desired type',
      43 => 'Identifier removed',
      44 => 'Channel number out of range',
      45 => 'Level 2 not synchronized',
      46 => 'Level 3 halted',
      47 => 'Level 3 reset',
      48 => 'Link number out of range',
      49 => 'Protocol driver not attached',
      50 => 'No CSI structure available',
      51 => 'Level 2 halted',
      52 => 'Invalid exchange',
      53 => 'Invalid request descriptor',
      54 => 'Exchange full',
      55 => 'No anode',
      56 => 'Invalid request code',
      57 => 'Invalid slot',
      58 => 'Unknown error 58',
      59 => 'Bad font file format',
      60 => 'Device not a stream',
      61 => 'No data available',
      62 => 'Timer expired',
      63 => 'Out of streams resources',
      64 => 'Machine is not on the network',
      65 => 'Package not installed',
      66 => 'Object is remote',
      67 => 'Link has been severed',
      68 => 'Advertise error',
      69 => 'Srmount error',
      70 => 'Communication error on send',
      71 => 'Protocol error',
      72 => 'Multihop attempted',
      73 => 'RFS specific error',
      74 => 'Bad message',
      75 => 'Value too large for defined data type',
      76 => 'Name not unique on network',
      77 => 'File descriptor in bad state',
      78 => 'Remote address changed',
      79 => 'Can not access a needed shared library',
      80 => 'Accessing a corrupted shared library',
      81 => '.lib section in a.out corrupted',
      82 => 'Attempting to link in too many shared libraries',
      83 => 'Cannot exec a shared library directly',
      84 => 'Invalid or incomplete multibyte or wide character',
      85 => 'Interrupted system call should be restarted',
      86 => 'Streams pipe error',
      87 => 'Too many users',
      88 => 'Socket operation on non-socket',
      89 => 'Destination address required',
      90 => 'Message too long',
      91 => 'Protocol wrong type for socket',
      92 => 'Protocol not available',
      93 => 'Protocol not supported',
      94 => 'Socket type not supported',
      95 => 'Operation not supported',
      96 => 'Protocol family not supported',
      97 => 'Address family not supported by protocol',
      98 => 'Address already in use',
      99 => 'Cannot assign requested address',
      100 => 'Network is down',
      101 => 'Network is unreachable',
      102 => 'Network dropped connection on reset',
      103 => 'Software caused connection abort',
      104 => 'Connection reset by peer',
      105 => 'No buffer space available',
      106 => 'Transport endpoint is already connected',
      107 => 'Transport endpoint is not connected',
      108 => 'Cannot send after transport endpoint shutdown',
      109 => 'Too many references: cannot splice',
      110 => 'Connection timed out',
      111 => 'Connection refused',
      112 => 'Host is down',
      113 => 'No route to host',
      114 => 'Operation already in progress',
      115 => 'Operation now in progress',
      116 => 'Stale NFS file handle',
      117 => 'Structure needs cleaning',
      118 => 'Not a XENIX named type file',
      119 => 'No XENIX semaphores available',
      120 => 'Is a named type file',
      121 => 'Remote I/O error',
      122 => 'Disk quota exceeded',
      123 => 'No medium found',
      124 => 'Wrong medium type',
      125 => 'Operation canceled'
    );
    
    return (isset($fsockopen_errors[$errnum])) ? $fsockopen_errors[$errnum] : $errnum;
  }
}
