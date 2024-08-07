<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('MAIN_FILE_LOADED', true);
define('TEMP_FILE_PATH', $_SERVER["DOCUMENT_ROOT"] . '/../../private/gobag/masterdata_releases/');
define('TEMP_FILE_PATH_LNG', $_SERVER["DOCUMENT_ROOT"] . '/../../private/gobag/translation_releases/');

require_once 'lib/exception/exceptions.php';

require_once 'lib/functional/pdo.php';
require_once 'lib/functional/response.php';
require_once 'lib/functional/transfer_user_data.php';
require_once 'lib/functional/service_builder.php';
require_once 'lib/request/basic_request.php';
require_once 'lib/response/basic_response.php';
require_once 'lib/response/typicaloperation_response.php';
require_once 'lib/dto/basic_dto.php';
require_once 'lib/repository/basic_repository.php';
require_once 'lib/service/basic_service.php';



spl_autoload_register(function ($class_name) {
    $classType = substr($class_name, -7);
    
    if ($classType == 'Service')
    {
        include './lib/service/'.strtolower(str_replace('Service', '_service', $class_name)) . '.php';
    }
    
    if ($classType == 'Request')
    {
        include './lib/request/'.strtolower(str_replace('Request', '_request', $class_name)) . '.php';
    }
});

function GUID()
{
    if (function_exists('com_create_guid') === true)
    {
        return trim(com_create_guid(), '{}');
    }

    return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
}

function GUID64(){
  return sprintf('%04X%04X%04X%04X%04X%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535)).
    sprintf('%04X%04X%04X%04X%04X%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
  
}

function getRealIpAddr()
{
    if (!empty($_SERVER['HTTP_CLIENT_IP']))   //check ip from share internet
    {
      $ip=$_SERVER['HTTP_CLIENT_IP'];
    }
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   //to check ip is pass from proxy
    {
      $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    else
    {
      $ip=$_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}
