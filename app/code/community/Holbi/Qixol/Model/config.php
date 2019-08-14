<?php

  error_reporting(E_ALL);
  ini_set('display_errors','On'); 
  define("DIR_FS_CATALOG",dirname(__FILE__).((substr(dirname(__FILE__), -1)<>"/")? "/": ""));
  define('TEMPORARY_DIRECTORY_FOR_FILES',  Mage::getBaseDir()."var/");
  define('LOG_PATH', TEMPORARY_DIRECTORY_FOR_FILES . 'logs_qixol/');     
  define('TMP_PATH',  TEMPORARY_DIRECTORY_FOR_FILES . 'tmp/');
  define('LOG_FILE',  LOG_PATH.'product_export_'.date("_Y_m").'.log');