<?php
define('APP_DIR', realpath('./'));
defined('DS') or define('DS', DIRECTORY_SEPARATOR);
define('INCL_DIR', APP_DIR.DS.'protected'.DS.'include');
require(INCL_DIR.DS.'core.php');