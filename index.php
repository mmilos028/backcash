<?php
if (version_compare(phpversion(), '5.2.0', '<') === true)
	die('ERROR: Your PHP version is ' . phpversion() . '. Online-Casino BackOffice requires PHP version 5.2.0 or newer.');
define('DS', DIRECTORY_SEPARATOR);
define('PS', PATH_SEPARATOR);
define('ROOT_DIR', dirname(__FILE__));
define('APP_DIR', ROOT_DIR . DS . 'application');
//WINDOWS
define('LIB_DIR', "C:\\xampp_7_0_27\\htdocs\\zend_library_1_12_19\\");
//LINUX
//define('LIB_DIR', "/var/www/html/zend_library");
//define('LIB_DIR',  ROOT_DIR . DS . 'library');
define('TEMP_DIR', ROOT_DIR . DS . 'temp');
define('MODELS_DIR', APP_DIR . DS . 'models');
define('FORMS_DIR', APP_DIR . DS . 'forms');
define('HELPERS_DIR', APP_DIR . DS . 'helpers');
set_include_path(PS . LIB_DIR . PS . get_include_path()); 
require_once 'Zend/Registry.php';
require_once 'Zend/Config/Ini.php';
require_once 'Zend/Application.php';
//section type moze da bude
//live - www.dosetup.com sa live baze
//production - game.sjm.rs sa 192.168.3.6 baze
//test - game.sjm.rs sa 192.168.3.240 bazom kopijom sa live baze
//testing - moj lokalni racunar sa 192.168.3.6 bazom
$section_type = "testing"; //OVDE SE MENJA live | production | test | testing
$config = new Zend_Config_Ini(APP_DIR . DS .'configs' . DS .'application_' . $section_type . '.ini', $section_type);
Zend_Registry::set('config', $config);

$application =
    new Zend_Application($config->getSectionName(), APP_DIR . DS . 'configs' . DS . 'application_' . $section_type . '.ini');
$application
    ->setBootstrap(APP_DIR . DS . "bootstrap.php", "Bootstrap");
$application
    ->bootstrap(array('settings', 'database', 'pagination', 'routes', 'translations', 'locale', 'backofficeLoggers', 'layout', 'view', 'viewRenderer'))
    ->run();