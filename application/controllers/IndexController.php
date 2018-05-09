<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'mobile_detect' . DS . 'Mobile_Detect.php';
class IndexController extends Zend_Controller_Action{
    /**
     * @var int
     */
	private $session_id = 0;
    /**
     * @var object
     */
    private $translate = null;
	
	public function init() {

		$helperMobileDetect = new Mobile_Detect();
        if($helperMobileDetect->isTablet()){
            //if detected mobile or tablet
            $this->_helper->layout->setLayout('layout_tablet');
        }else if($helperMobileDetect->isMobile()){
            $this->_helper->layout->setLayout('layout_mobile');
        }
        else{
            //if detected desktop
            $this->_helper->layout->setLayout('layout_desktop');
        }

		$this->initView ();
		$this->view->baseUrl = $this->_request->getBaseUrl ();
		$this->translate = Zend_Registry::get('translate');

        $this->writeUserSessionDataToFirebug();
	}
	
	public function preDispatch(){
		$auth = Zend_Auth::getInstance();
		if(!$auth->hasIdentity()) {
            $this->forward('login', 'auth');
        }
		else {
			$authInfo = $auth->getIdentity();
			if(isset($authInfo)) {
                $this->session_id = $authInfo->session_id;
            }
		}
		require_once MODELS_DIR . DS . 'SessionModel.php';
		if($this->session_id != 0){
			$res = SessionModel::validateSession($this->session_id);
            $res = $res["status"];
			if($res == NO) {
                $this->forward('terminate', 'auth');
            }
		}
	}

    private function writeUserSessionDataToFirebug(){
        $config = Zend_Registry::get('config');
        if($config->writeSessionDataToFirebug == "true") {
            $message = print_r($_SESSION['auth_space']['session'], true);
            require_once HELPERS_DIR . DS . 'ErrorMailHelper.php';
            ErrorMailHelper::writeToFirebugInfo($message);
        }
    }
	
	public function indexAction(){
		$this->forward('login','auth');
	}
}