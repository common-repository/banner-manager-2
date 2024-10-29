<?php

include_once(DN_PATH . '/libraries/DN_Message.php');

class DN_Validation extends DN_Message
{
	private static $encoding = "iso-8859-1";
	private static $email_regular_expression = "^([-!#\$%&'*+./0-9=?A-Z^_`a-z{|}~])+@([-!#\$%&'*+/0-9=?A-Z^_`a-z{|}~]+\\.)+[a-zA-Z]{2,6}\$";
	private static $float_regular_expression = "^-?[0-9]+(\\.[0-9]*)?([Ee][+-]?[0-9]+)?\$";
	private static $decimal_regular_expression = "^-?[0-9]+(\\.[0-9]{0,PLACES})?\$";
	
	private $valid = TRUE;
	
	public function DN_Validation()
	{
		parent::DN_Message();
	}
	
	public function set_encoding($encoding)
	{
		$this->encoding = $encoding;
	}
	
	public function is_valid()
	{
		return $this->valid;
	}
	
	public function encode_javascript_string($s)
	{
		$l=strlen($s=strval($s));
		if($l==0)
			return("''");
		$n=!strcmp(strtolower($this->encoding),"iso-8859-1");
		$e=array(
			"\n"=>"\\n",
			"\r"=>"\\r",
			"\t"=>"\\t"
		);
		for($d=0, $j="", $c=0; $c<$l; $c++)
		{
			$t = $s[$c];
			$a = ord($t);
			if($a<32
			|| $t=='<'
			|| ($a>126
			&& $n))
			{
				if(IsSet($e[$t]))
				{
					if($c==0
					|| $d)
					{
						if($c!=0)
							$j.='+';
						$j.="'".$e[$t];
						$d=0;
					}
					else
						$j.=$e[$t];
				}
				else
				{
					if($c!=0)
					{
						if(!$d)
							$j.="'";
						$j.="+";
					}
					$j.="unescape('";
					for(;$c<$l; $c++)
					{
						$t = $s[$c];
						$a = ord($t);
						if($a<32
						|| $t=='<'
						|| $t=='%'
						|| ($a>126
						&& $n))
						{
							if(IsSet($e[$t]))
								$j.=$e[$t];
							else
								$j.="%".dechex($a);
						}
						else
						{
							if($t=="'"
							|| $t=="\\")
								$j.="\\";
							$j.=$t;
						}
					}
					$j.="')";
					$d=1;
				}
			}
			else
			{
				if($d)
					$j.="+'";
				else
				{
					if($c==0)
						$j.="'";
				}
				if($t=="'"
				|| $t=="\\")
					$j.="\\";
				$j.=$t;
				$d=0;
			}
		}
		if(!$d)
			$j.="'";
		return($j);
	}

	public function encode_html_string($string)
	{
		switch(strtolower($this->encoding))
		{
			case "iso-8859-1":
				return(HtmlEntities($string));
			default:
				return(HtmlSpecialChars($string));
		}
	}

	public function escape_javascript_regular_expressions($expression)
	{
		return($this->encode_javascript_string($expression));
	}
	
	public function email($value)
	{
		if( ! preg_match('/'.str_replace('/', '\\/', $this->email_regular_expression).'/i', $value))
		{
			return $this->valid = FALSE;
		}
		
		return $this->valid = TRUE;
	}
	
	public function creditcard($checkation_type, $value)
	{
		$value = preg_replace('/[- .]/', '', $value);
		$len = strlen($value);
		$check = 0;
		
		if($len < 13)
		{
			$check = 1;
		}
		else
		{
			$first = ord($value[0]) - ord("0");
			$second = ord($value[1]) - ord("0");
			$third = ord($value[2]) - ord("0");
			
			switch($checkation_type)
			{
				case "mastercard":
					if($len != 16 || $first != 5 || $second < 1 || $second > 5)
					{
						$check = 1;
					}
					break;
				case "visa":
					if(($len != 16 && $len != 13) || $first != 4)
					{
						$check = 1;
					}
					break;
				case "amex":
					if($len != 15 || $first != 3 || ($second != 4 && $second != 7))
					{
						$check = 1;
					}
					break;
				case "carteblanche":
				case "dinersclub":
					if($len != 14 || $first != 3 || (($second != 0 || $third < 0 || $third > 5) && $second != 6 && $second != 8))
					{
						$check = 1;
					}
					break;
				case "discover":
					if($len != 16 || (($first != 5 || $second < 1 || $second > 5) && strcmp(substr($value,0,4), "6011")))
					{
						$check = 1;
					}
					break;
				case "enroute":
					if($len != 15 || (substr($value,0,4) != "2014" && substr($value,0,4) != "2149"))
					{
						$check = 1;
					}
					break;
				case "jcb":
					if(($len != 16 || $first != 3) && ($len != 15 || (substr($value,0,4) != "2031" && substr($value,0,4) != "1800")))
					{
						$check = 1;
					}
					break;
				case "unknown":
					break;
				default:
					$check = 1;
					break;
			}
		}
		
		if($check == 0)
		{
			for($odd = "0246813579", $zero = ord("0"), $position = 1; $position <= $len; $position++)
			{
				if(($digit = ord($value[$len - $position]) - $zero) > 9 || $digit < 0)
				{
					$check=1;
					break;
				}
				if( ! ($position % 2))
				{
					$digit = intval($odd[$digit]);
				}
				$check += $digit;
			}
			$check %= 10;
		}
		
		if($check)
		{
			return $this->valid = FALSE;
		}
		
		return $this->valid = TRUE;
	}
	
	public function regular_expression($regular_expresson, $value)
	{
		if( ! preg_match('/'.str_replace('/', '\\/', $regular_expresson).'/', $value))
		{
			return $this->valid = FALSE;
		}
		
		return $this->valid = TRUE;
	}
	
	public function is_not_empty($value)
	{
		if(strlen($value) == 0)
		{
			return $this->valid = FALSE;
		}
		
		return $this->valid = TRUE;
	}
	
	public function minimum_length($length, $value)
	{
		if(strlen($value) < $length)
		{
			return $this->valid = FALSE;
		}
		
		return $this->valid = TRUE;
	}
	
	public function maximum_length($length, $value)
	{
		if(strlen($value) >= $length)
		{
			return $this->valid = FALSE;
		}
		
		return $this->valid = TRUE;
	}
	
	public function is_integer($value)
	{
		if(strcmp($value,strval(intval($value))))
		{
			return $this->valid = FALSE;
		}
		
		return $this->valid = TRUE;
	}
	
	public function is_float($value)
	{
		if( ! preg_match('/'.str_replace('/', '\\/', $this->float_regular_expression) .'/', $value))
		{
			return $this->valid = FALSE;
		}
		
		return $this->valid = TRUE;
	}
	
	public function is_equal_to($compare, $value)
	{
		if(strcmp($compare, $value))
		{
			return $this->valid = FALSE;
		}
		
		return $this->valid = TRUE;
	}
}
