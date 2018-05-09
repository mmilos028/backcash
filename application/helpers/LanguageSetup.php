<?php
/* Language translation support setup for backoffice */
class LanguageSetup extends Zend_Controller_Action_Helper_Abstract{
	protected $_languages;
	protected $_directory;
	public function __construct($languages, $directory){
		$this->_dir = $directory;
		$this->_languages = $languages;
	}
	public function init(){
		$lang = $this->getRequest()->getParam('lang');
		if(!in_array($lang, array_keys($this->_languages)))$lang = 'en';
		$localeString = $this->_languages[$lang];
		$file = $this->_dir . '/'. $localeString . '.php';
		if(file_exists($file))include $file;
		else include $this->_dir . '/en_GB.php';
		if(!isset($translationStrings))throw new Exception('Missing $translationStrings');
		/*
		$cache = Zend_Cache::factory(
			'Core', 'File',
			array(
				'caching' => true,
				'lifetime' => null,
				'automatic_serialization'=> true,
				'automatic_cleaning_factor' => 0, //10
				'cache_id_prefix' => 'Translate'
				), 
			array(
				//'hashed_directory_level' => 0, 
				'cache_dir' => TEMP_DIR
			)
		);
		Zend_Translate::setCache($cache);
		*/
		$translate = new Zend_Translate('array', $translationStrings, $lang);
		//$translate->setCache($cache);
		$this->_actionController->_localeString = $localeString;
		$this->_actionController->_translate = $translate;
		$viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
		$viewRenderer->view->localeString = $localeString;
		Zend_Registry::set("translate", $translate);
		Zend_Registry::set("lang", $lang);
		Zend_Registry::set("localeString", $localeString);
		$viewRenderer->view->translate = $translate;
	}
}