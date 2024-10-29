<?php
/**
 * Description...
 *
 */
 
class DN_Exceptions extends Exception
{}

/**
 * Description...
 *
 */
 
class DN_ExceptionsDesc
{
  const EC_SUCCESS = 0;
  
  /*
   * GENERAL ERRORS
   */
  const EC_UNKNOWN = 1;
  const EC_OBJECT = 2;
  const EC_METHOD = 3;
  const EC_FILE = 4;
  
  /*
   * PARAMETER ERRORS
   */
  const EC_PARAM = 1000;
  
  /*
   * USER PERMISSIONS ERRORS
   */
  const EC_PERMISSION = 2000;
  
  /**
   * DATA STORE ERRORS
   */
  const EC_DATA_UNKNOWN_ERROR = 3000;
  const EC_DATA_DATABASE_ERROR = 3001;
  const EC_DATA_ERROR = 3002;
  
  public static $descriptions = array(
    EC_SUCCESS => 'Success',
    EC_UNKNOWN => 'An unknown error occurred',
    EC_OBJECT => 'Unknown object',
    EC_METHOD => 'Unknown method',
    EC_FILE => 'Unknown file',
    EC_PARAM => 'Invalid parameter',
    EC_PERMISSION => 'Permissions error',
    EC_DATA_UNKNOWN_ERROR => 'Unknown data store API error',
    EC_DATA_DATABASE_ERROR => 'A database error occurred. Please try again',
    EC_DATA_ERROR => 'Unknown Data type'
  );
}
