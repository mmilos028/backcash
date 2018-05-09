<?php
require_once HELPERS_DIR . DS . 'ErrorMailHelper.php';
require_once HELPERS_DIR . DS . 'mobile_detect' . DS . 'Mobile_Detect.php';

class ErrorController extends Zend_Controller_Action{
	public function init() {

		$helperMobileDetect = new Mobile_Detect();
        if($helperMobileDetect->isTablet()){
            //if detected mobile or tablet
            $this->_helper->layout->setLayout('layout_tablet');
        }
        else if($helperMobileDetect->isMobile()){
            $this->_helper->layout->setLayout('layout_mobile');
        }
        else{
            //if detected desktop
            $this->_helper->layout->setLayout('layout_desktop');
        }

		$this->initView ();
		$this->view->baseUrl = $this->_request->getBaseUrl();
	}
	//it is always called before any action is called
	//performes session validation and backoffice logout if session timeout occured
	public function preDispatch(){
		$auth = Zend_Auth::getInstance();
		if(!$auth->hasIdentity()) {
            $this->forward('login', 'auth');
        }else {
			$authInfo = $auth->getIdentity();
			if(isset($authInfo)) {
                $this->session_id = $authInfo->session_id;
            }
		}
	}
	
	public function indexAction(){
		$this->forward('login','auth');
	}
	
	//return http 500 when no db connection available
	public function errorAction(){
		$errors = $this->_getParam('error_handler');
		$this->view->title = 'Error page';
		$messageErr = 'We are sorry for inconvenience. Please try again or contact your backoffice administrator.';
		switch ($errors->type){
			case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
				$this->view->message = $messageErr;
				break;
			case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
				$this->view->message = $messageErr;
				break;
			case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
				$this->view->message = $messageErr;
				break;
			case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_OTHER:
				$this->view->message = $messageErr;
				break;
			default:
				$this->view->message = $messageErr;
				break;
		}
        $message = $errors->exception;
        ErrorMailHelper::writeError($message, $message);
		if ($this->getInvokeArg('displayExceptions') == true){
			$this->view->exception = $messageErr;
		}
	}
}