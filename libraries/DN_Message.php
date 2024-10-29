<?php

include_once(DN_PATH . '/libraries/DN_Temp.php');

class DN_Message extends DN_Temp
{
	public function DN_Message()
	{
		parent::DN_Temp();
	}
	
	public function set_error($pointer, $key, $message)
	{
		$this->set('error', $pointer, $key, $message);
	}
	
	public function set_success($pointer, $key, $message)
	{
		$this->set('success', $pointer, $key, $message);
	}
	
	public function get_error($pointer, $clean = TRUE)
	{
		return $this->get('error', $pointer, $clean);
	}
	
	public function get_success($pointer, $clean = TRUE)
	{
		return $this->get('success', $pointer, $clean);
	}
	
	public function get_by_key_error($pointer, $key, $clean = TRUE)
	{
		return $this->get_by_key('error', $pointer, $key, $clean);
	}
	
	public function get_by_key_success($pointer, $key, $clean = TRUE)
	{
		return $this->get_by_key('success', $pointer, $key, $clean);
	}
	
	public function has_error_message($pointer)
	{
		if ($this->has('error', $pointer))
		{
			return TRUE;
		}
		
		return FALSE;
	}
	
	public function has_success_message($pointer)
	{
		if ($this->has('success', $pointer))
		{
			return TRUE;
		}
		
		return FALSE;
	}
}

