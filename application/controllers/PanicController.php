<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'mobile_detect' . DS . 'Mobile_Detect.php';

class PanicController extends Zend_Controller_Action{
    /**
     * @var int
     */
	private $session_id = 0;
    /**
     * @var object
     */
	private $session_space = null;
    /**
     * @var int
     */
	private $defaultPerPage = 200;
		
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

		$this->initView();
		$this->view->baseUrl = $this->_request->getBaseUrl();
		$this->translate = Zend_Registry::get('translate');
		//setup number of pages items per report from database or set default 200
		require_once MODELS_DIR . DS . 'BoSetupModel.php';
        $defaultPerPage = BoSetupModel::numberOfItemsPerPage($this->session_id);
		$this->defaultPerPage = $defaultPerPage["lines_for_page"];
		if(!isset($this->defaultPerPage)) {
            $this->defaultPerPage = 200;
        }
		if(!isset($this->session_space)){
			$this->session_space = new Zend_Session_Namespace('report_operation');
			if(!isset($this->session_space->limitPerPage)) {
                $this->session_space->limitPerPage = $this->defaultPerPage;
            }
			$this->session_space->columns = 1;
			$this->session_space->order = 'asc';
		}
	}

    private function writeFirebugInfo(){
        $bo_session_id = $_SESSION['auth_space']['session']['session_out'];
        $username = $_SESSION['auth_space']['session']['username'];
        $super_role = $_SESSION['auth_space']['session']['subject_super_type_name'];
        $role = $_SESSION['auth_space']['session']['subject_type_name'];
        $affiliate_id = $_SESSION['auth_space']['session']['affiliate_id'];
        $currency = $_SESSION['auth_space']['session']['currency'];
        $firebug_message = "[BO Session ID: {$bo_session_id}] [Username: {$username}] [SuperRole: {$super_role}] [Role: {$role}] [Affiliate ID: {$affiliate_id}] [Currency: {$currency}]";
        ErrorMailHelper::writeToFirebugInfo($firebug_message);
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
        $this->writeFirebugInfo();
		require_once MODELS_DIR . DS . 'SessionModel.php';
		$res = SessionModel::validateSession($this->session_id);
		if($res['status'] == NO) {
            $this->forward('terminate', 'auth');
        }
		//display number of game and backoffice sessions on application main menu
		if(!$this->isXmlHttpRequest()){
			$activeSessionsArr = SessionModel::listNumberActivePlayerSession($this->session_id);
			Zend_Layout::getMvcInstance()->assign('no_game_sessions', $activeSessionsArr["no_game_sessions"]);
			Zend_Layout::getMvcInstance()->assign('no_bo_sessions', $activeSessionsArr["no_bo_sessions"]);
		}
	}
	
	//detects ajax calls
	private function isXmlHttpRequest(){
		return ($this->getHeader('X_REQUESTED_WITH') == 'XMLHttpRequest');
	}
	
	//returns header from servers response
	private function getHeader($header){
		$temp = 'HTTP_' . strtoupper(str_replace('-', '_', $header));
		if (!empty($_SERVER[$temp]))
			return $_SERVER[$temp];
        return null;
	}
	
	public function indexAction(){
		$this->forward('login','auth');
	}
	
	public function affiliateUnsetPanicAction(){
		$affiliate_id = $this->_getParam('id', null);
		if(!isset($affiliate_id)){
			$locale = Zend_Registry::get('lang');
			$this->redirect($locale . '/affiliate/panic-list');
		}
		require_once MODELS_DIR . DS . 'SessionModel.php';
		SessionModel::unsetPanic($affiliate_id);
		$locale = Zend_Registry::get('lang');
		$this->redirect($locale . '/affiliate/panic-list');
	}
	
	public function affiliateSetPanicAction(){
		$affiliate_id = $this->_getParam('id', null);
		if(!isset($affiliate_id)){
			$locale = Zend_Registry::get('lang');
			$this->redirect($locale . '/affiliate/panic-list');
		}
		require_once MODELS_DIR . DS . 'SessionModel.php';
		SessionModel::setPanic($affiliate_id);
		$locale = Zend_Registry::get('lang');
		$this->redirect($locale . '/affiliate/panic-list');
	}
	
	//unset panic after panic action was initiated
	public function unsetPanicAction(){
		require_once MODELS_DIR . DS . 'MyAccountModel.php';
		$arrData = MyAccountModel::getUserInformation($this->session_id);
		$userInfo = $arrData["details"]->current();
		$affiliate_id = $userInfo['id'];
		require_once MODELS_DIR . DS . 'SessionModel.php';
		SessionModel::unsetPanic($affiliate_id);
		$_SESSION['auth_space']['session']['panic_status'] = OFF;
		$rola = $_SESSION['auth_space']['session']['subject_type_name'];
		//if user is shift cashier then show start shift screen
		if($rola == ROLA_AD_SHIFT_CASHIER_S || $rola == ROLA_AD_SHIFT_CASHIER_W){
			$locale = Zend_Registry::get('lang');
			$this->redirect($locale . '/cashiers-collectors/start-shift');
		}else{
			//if user is someone else then show personal information screen
			$locale = Zend_Registry::get('lang');
			$this->redirect($locale . '/my-account/personal-information');
		}
	}
	
	//javascript event that trigers panic is in /backoffice/application/layouts/scripts/statistics.php
	//panic button on esc key in backoffice
	public function panicAction(){
		$this->_helper->layout->disableLayout();
		$this->view->baseUrl = $this->_request->getBaseUrl();
		$this->_helper->viewRenderer->setNoRender();
		$auth = Zend_Auth::getInstance();
		if($auth->hasIdentity()){
			try{
                $authInfo = $auth->getIdentity();
                if(isset($authInfo)){
                    $this->session_id = $authInfo->session_id;
                }
                require_once MODELS_DIR . DS . 'MyAccountModel.php';
                $arrData = MyAccountModel::getUserInformationDirect($this->session_id);
                $userInfo = $arrData["details"]->current();
                $affiliate_id = $userInfo['id'];
                require_once MODELS_DIR . DS . 'SessionModel.php';
                SessionModel::setPanic($affiliate_id);
                SessionModel::panic($this->session_id);
                $_SESSION['auth_space']['session']['panic_status'] = ON;
                SessionModel::resetPackages();
                $username = $authInfo->username;
                $session_id = $authInfo->session_id;
                SessionModel::closeSession($session_id);
                Zend_Session::namespaceUnset('report_operation');
                Zend_Auth::getInstance()->clearIdentity();
                session_unset();
                session_destroy();
                $config = Zend_Registry::get('config');
                $website = $config->panicSite;
                if($website == '') {
                    $website = $this->url(array('controller' => 'auth', 'action' => 'login'));
                }
                exit($website);
			}catch(Zend_Exception $ex){
                $config = Zend_Registry::get('config');
                $website = $config->panicSite;
				if($website == '') {
                    $website = $this->url(array('controller' => 'auth', 'action' => 'login'));
                }
                exit($website);
			}
		}else{
            $website = "http://www.google.com";
            exit($website);
        }
	}
}