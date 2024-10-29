<?php

class DN_Temp
{
  private $__tmp;
  
	public function DN_Temp()
	{
	  $this->__tmp = get_option( '_fi_temp', array() );
	  if (!is_array($this->__tmp))
	  {
	    $this->__tmp = array();
	  }
  }
	
	public function set($type, $pointer, $key, $value)
	{
	  $id_data = array();
    if (isset($this->__tmp[$type]))
    {
      $id_data = $this->__tmp[$type];
    }
		
		if (is_numeric($key))
		{
		  $key = 'key_'.$key;
		}
		
		$new_data = array(
			$type => array(
				$pointer => array(
					$key => $value
				)
			)
		);
		
		if (is_array($id_data) && count($id_data) > 0)
		{
			if (isset($id_data[$pointer]))
			{
				$id_data[$pointer] = array_merge($id_data[$pointer], array(
					$key => $value
				));
				
				$new_data = array(
					$type => $id_data
				);
			}
			else
			{
				$new_data = array(
					$type => array_merge($id_data, array(
						$pointer => array(
							$key => $value
						)
					))
				);
			}
		}
		
		$this->__tmp = array_merge($this->__tmp, $new_data);
		
		update_option( '_fi_temp', $this->__tmp );
	}
	
	public function get($type, $pointer, $clean = TRUE)
	{
		$data = array();
		
		$id_data = array();
    if (isset($this->__tmp[$type]))
    {
      $id_data = $this->__tmp[$type];
    }
    
		if (is_array($id_data) && count($id_data) > 0)
		{
			if (isset($id_data[$pointer]))
			{
				$data = $id_data[$pointer];
				if ($clean)
				{
				  $this->remove_by_pointer($type, $pointer);
				}
			}
		}
		
		return $data;
	}
	
	public function get_by_key($type, $pointer, $key, $clean = TRUE)
	{
	  if (is_numeric($key))
	  {
	    $key = 'key_'.$key;
	  }
	  
		$message = '';
		
		$id_data = $this->get($type, $pointer, $clean);
		if (is_array($id_data) && count($id_data) > 0)
		{
			if (isset($id_data[$key]))
			{
				$message = $id_data[$key];
				if ($clean)
				{
					unset($id_data[$key]);
				}
			}
		}
		
		return $message;
	}
	
	public function remove($type)
	{
	  unset($this->__tmp[$type]);
    
    update_option( '_fi_temp', $this->__tmp );
	}
	
	public function remove_by_pointer($type, $pointer)
	{
	  unset($this->__tmp[$type][$pointer]);
    
    update_option( '_fi_temp', $this->__tmp );
	}
	
	public function has($type, $pointer)
	{
		$id_data = $this->get($type, $pointer, FALSE);
		if (is_array($id_data) && count($id_data) > 0)
		{
			unset($id_data);
			return TRUE;
		}
		
		unset($id_data);
		return FALSE;
	}
}
