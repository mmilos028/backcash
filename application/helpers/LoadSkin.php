<?php
/* Loading themes into BackOffice  */
class Zend_View_Helper_LoadSkin extends Zend_View_Helper_Abstract{
	public function loadSkin($baseUrl, $skin, $type){
		$skinPath = ROOT_DIR  . DS . 'skins' . DS . $skin . DS . 'skin.xml';
		$skinData = new Zend_Config_Xml($skinPath);
		if($type == "loginScreen"){
			$this->view->clearVars();
			$stylesheets = $skinData->loginScreen->stylesheet->toArray();
			if (is_array($stylesheets)) 
				foreach ($stylesheets as $stylesheet) 
					$this->view->headLink()->appendStylesheet($baseUrl . '/skins/' . $skin . '/css/' . $stylesheet);
		}
		if($type == "userWideScreen"){
			$stylesheets = $skinData->userWideScreen->stylesheet->toArray();
			$this->view->clearVars();
			if (is_array($stylesheets))
				foreach ($stylesheets as $stylesheet)
					$this->view->headLink()->appendStylesheet($baseUrl . '/skins/' . $skin . '/css/' . $stylesheet);
		}
	}
}