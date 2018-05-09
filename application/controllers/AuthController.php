<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
class AuthController extends Zend_Controller_Action{
    /**
     * @var int
     */
	private $session_id = 0;

	public function init() {
		$this->_helper->layout->setLayout('layout_login');
	}
	//after trying to login user if remembered on application session
	public function postDispatch(){
		$auth = Zend_Auth::getInstance();
		if($auth->hasIdentity()){
            if(strlen($_SESSION['auth_space']['session']['default_page']) != 0) {
                $this->redirect($_SESSION['auth_space']['session']['default_page']);
            }else{
                $locale = Zend_Registry::get('lang');
                //$locale = 'en';
                //if($locale == '')$locale = 'en';
                $_SESSION['auth_space']['session']['default_page'] = $_SESSION['auth_space']['session']['default_page'] = $locale . '/mobile-menu';
            }
		}
	}
	//automaticaly first target login screen
	public function indexAction(){
		$this->forward('login','auth');
	}
	//login to backoffice all of action is here
	public function loginAction(){
		require_once FORMS_DIR . DS . 'auth' . DS . 'AuthForm.php';
		require_once HELPERS_DIR . DS . 'OracleAuthInterface.php';
		$form = new AuthForm();
		$possible_logins = Zend_Registry::get('possible_logins');
		$sess = new Zend_Session_Namespace();
		$translate = Zend_Registry::get('translate');
		$locale = Zend_Registry::get('lang');
		$config = Zend_Registry::get('config');
		if(!isset($sess->login_count)){
			$sess = new Zend_Session_Namespace();
			$sess->login_count = 1;
		}else {
            $sess->login_count += 1;
        }
		//if auth login form is submitted more than 3 times
		if(!($sess->login_count > $possible_logins + 1)) {
            $form->removeElement('captcha');
        }
		if($config->use_captcha == "false"){
			$form->removeElement('captcha');
		}
		if($this->_request->isPost()){
			$formData = $this->_request->getPost();
			if($form->isValid($formData)) {
				$username = $form->getValue('username');
				$password = $form->getValue('password');
                $locale = $form->getValue('language');
				$db = Zend_Registry::get('db_auth');
				$authAdapter = new OracleAuthInterface($db);
				$authAdapter->setUsername($username);
				$hashed_password = md5(md5($password));
				$authAdapter->setPassword($hashed_password);
				$auth = Zend_Auth::getInstance();
				require_once MODELS_DIR . DS . 'SessionModel.php';
				SessionModel::resetPackages();
				//check if parent affiliate is banned
				require_once MODELS_DIR . DS . 'AffiliatesModel.php';
				$banned_status = AffiliatesModel::checkParentAffiliateBannedStatus($username);
				$result = null;
				if($banned_status["banned_status"] == YES){
					$authResult = array();
					$authResult['code'] = Zend_Auth_Result::FAILURE;
					$authResult['identity'] = '';
					$authResult['messages'][] = $translate->_('Login failed. Parent affiliate is banned.');
					$result = new Zend_Auth_Result($authResult['code'], $authResult['identity'], $authResult['messages']);
				}
				//check if username is forbidden
				if($username == FICTIVE_BO_USERNAME || $username == "ITFA" || $username == "GGL_Admin_WS"){
					$authResult = array();
					$authResult['code'] = Zend_Auth_Result::FAILURE;
					$authResult['identity'] = '';
					$authResult['messages'][] = $translate->_('Login failed. No user with username/password.');
					$result = new Zend_Auth_Result($authResult['code'], $authResult['identity'], $authResult['messages']);
				}
				try{
					if(is_null($result))
						$result = $auth->authenticate($authAdapter);
				}catch(Zend_Exception $ex){
					$this->view->message = CursorToArrayHelper::getExceptionTraceAsString($ex);
				}
				if(is_null($result)){
					$this->view->message = $translate->_('Login failed. No user with username/password.');
					$this->redirect($locale . '/auth/logout');
				}else{
					//if authorization is success then allow user to login
					if($result->isValid()){ 
						$sess->login_count = 0;
						$data = $authAdapter->getResultRowObject();
						$auth->getStorage()->write($data);
						//set panic status in session variable						
						$authInfo = $auth->getIdentity();
						if(isset($authInfo)) {
                            $this->session_id = $authInfo->session_id;
                        }

                        require_once MODELS_DIR . DS . 'SessionModel.php';
                        $result_reverse_type_user = SessionModel::checkReverseTypeUser($this->session_id);
                        if($result_reverse_type_user['result'] == 'REVERSE LOCATION\CASHIER' || $result_reverse_type_user['result'] == 'ROOT\REVERSE'){
                            $_SESSION['auth_space']['session']['is_under_reverse_affiliate'] = "TRUE";
                        }else{
                            $_SESSION['auth_space']['session']['is_under_reverse_affiliate'] = "FALSE";
                        }

                        $rola = $_SESSION['auth_space']['session']['subject_type_name'];
                        if(in_array($rola, array(ROLA_AFFILIATE_REVERSE, ROLA_LOCATION_REVERSE, ROLA_OPERATER_REVERSE))){
                            $_SESSION['auth_space']['session']['is_reverse_type_user'] = "TRUE";
                        }else{
                            $_SESSION['auth_space']['session']['is_reverse_type_user'] = "FALSE";
                        }

                        if($rola == ROLA_AFFILIATE_REVERSE){
                            $_SESSION['auth_space']['session']['reverse_type_user'] = 'ROOT\REVERSE';
                        }
                        if($rola == ROLA_LOCATION_REVERSE || $rola == ROLA_OPERATER_REVERSE){
                            $_SESSION['auth_space']['session']['reverse_type_user'] = 'REVERSE LOCATION\CASHIER';
                        }

                        $_SESSION['auth_space']['session']['is_reverse_type_user_authenticated'] = "FALSE";

						require_once MODELS_DIR . DS . 'MyAccountModel.php';
				      	$arrData = MyAccountModel::getUserInformation($this->session_id);
				      	$userInfo = $arrData["details"]->current();
				      	$affiliate_id = $userInfo['id'];
				      	require_once MODELS_DIR . DS . 'AffiliatesModel.php';
				      	$affDetails = AffiliatesModel::getAffiliateDetailsLastLogin($this->session_id, $affiliate_id);
						$_SESSION['auth_space']['session']['affiliate_id'] = $affiliate_id;
				      	$_SESSION['auth_space']['session']['last_login_date'] = $affDetails["details"]['start_time'];
				      	$_SESSION['auth_space']['session']['last_login_ip_country'] = $affDetails["details"]['ip_address'];
                        $_SESSION['auth_space']['session']['currency'] = $affDetails["details"]['currency'];
				      	//Milan::kraj
						$_SESSION['auth_space']['session']['panic_status'] = OFF; 						
						//if is cashier then redirect to page for transfer credits to its players
						$rola = $_SESSION['auth_space']['session']['subject_type_name'];
						require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
						//affiliate tree popup window is not active
						$_SESSION['auth_space']['session']['popup_window'] = 'inactive';
						//set if user can change start date
						if($rola != ROLA_AD_COLLECTOR){
							$_SESSION['auth_space']['session']['change_startdate'] = true;
						}else{
							//if user is collector then start date is last time collect
							if($_SESSION['auth_space']['session']['last_time_collect'] == ''){
								//if collector had no collect cash he can change startdate and see from first in current month
								$_SESSION['auth_space']['session']['change_startdate'] = true;
							}else{
								$_SESSION['auth_space']['session']['change_startdate'] = false;
							}
						}
                        //get affiliate_id for user and default language
                        require_once MODELS_DIR . DS . 'MyAccountModel.php';
                        $arrData = MyAccountModel::getUserInformation($this->session_id);
                        $userInfo = $arrData["details"]->current();
                        $affiliate_id = $userInfo['id'];
                        /*
                        if(strlen($userInfo['bo_default_language']) == 5){
                            //from database receive locale like en_GB
                            $default_locale = $userInfo['bo_default_language'];
                            //extract default language from locale (en)
                            $res = explode('_', $default_locale);
                            $locale = $res[0];
                        }*/
						if ($rola == ROLA_AD_CASHIER){
							require_once MODELS_DIR . DS . 'SessionModel.php';
							$userPanicStatus = SessionModel::userPanic($affiliate_id);
							//if panic status is under this affiliate for casino put it on session
							$_SESSION['auth_space']['session']['panic_status'] = $userPanicStatus["status"];
                            $_SESSION['auth_space']['session']['default_page'] = $locale . '/mobile-menu';
							$this->redirect($locale . '/mobile-menu');
						//if it is not cashier normal active game session report screen
						}
						else if ($rola == ROLA_AD_CASHIER_PAYOUT || $rola == ROLA_AD_CASHIER_SUBLEVEL){
							require_once MODELS_DIR . DS . 'SessionModel.php';
							$userPanicStatus = SessionModel::userPanic($affiliate_id);
							//if panic status is under this affiliate for casino put it on session
							$_SESSION['auth_space']['session']['panic_status'] = $userPanicStatus["status"];
                            $_SESSION['auth_space']['session']['default_page'] = $locale . '/mobile-menu';
							$this->redirect($locale . '/mobile-menu');
						//if it is not cashier normal active game session report screen
						}
						else if ($rola == ROLA_AD_SHIFT_CASHIER_S || $rola == ROLA_AD_SHIFT_CASHIER_W){
							require_once MODELS_DIR . DS . 'SessionModel.php';
							$userPanicStatus = SessionModel::userPanic($affiliate_id);
							//if panic status is under this affiliate for casino put it on session
							$_SESSION['auth_space']['session']['panic_status'] = $userPanicStatus["status"];
							//
							//if users role is rola ad shift cashier S
							require_once MODELS_DIR . DS . 'CashiersCollectorsModel.php';
							$session_id = $_SESSION['auth_space']['session']['session_out'];
							$arrCheckShift = CashiersCollectorsModel::checkOpenShifts($session_id);
							$shift_status = $arrCheckShift['shift_status'];
							$shift_start_time = $arrCheckShift['shift_start_time'];
							//if shift is opened show players for credit transfer report
							if($shift_status == YES){
                                $_SESSION['auth_space']['session']['default_page'] = $locale . '/mobile-menu';
								$this->redirect($locale . '/mobile-menu');
							}
							else{ //if shift is not opened show start shift screen
                                $_SESSION['auth_space']['session']['default_page'] = $locale . '/cashiers-collectors/start-shift';
								$this->redirect($locale . '/cashiers-collectors/start-shift');
							}
						}
						else{
							if($rola == ROLA_AFF_LOCATION || $rola == ROLA_AFF_OPERATER){
                                //$this->redirect($locale . '/auth/logout');
                                $_SESSION['auth_space']['session']['default_page'] = $locale . '/mobile-menu';
                                $this->redirect($locale . '/mobile-menu');
							}
							else if($rola == ROLA_AD_COLLECTOR){
                                $this->redirect($locale . '/auth/logout');
							}
							else{
                                $_SESSION['auth_space']['session']['default_page'] = $locale . '/mobile-menu';
                                $this->redirect($locale . '/mobile-menu');
							}
						}
					}
					else {
						foreach($result->getMessages() as $mes) {
                            $this->view->message .= $mes . '<br />';
                        }
					}
				}
			}else $form->populate($formData);
		}
		$this->view->form = $form;
	}
	//logout user but don't break users session
	public function terminateAction(){
		Zend_Auth::getInstance()->clearIdentity();
		Zend_Session::regenerateId();
        $this->redirect('en/auth/login');
	}
	//logout user with forwarding page
	public function logoutAction(){
		require_once MODELS_DIR . DS . 'SessionModel.php';
		$auth = Zend_Auth::getInstance();
		$authInfo = $auth->getIdentity();
		if(!isset($this->session_id) || $this->session_id == 0)
			$res = NO;
		else{			
			SessionModel::resetPackages();
			$res = SessionModel::validateSession($this->session_id);
            $res = $res["status"];
		}
		if($res == NO){
			Zend_Session::namespaceUnset('report_operation');
			Zend_Auth::getInstance()->clearIdentity();
            $this->redirect('en/auth/terminate');
		}
		if(isset($authInfo)){
			$session_id = $authInfo->session_id;
			SessionModel::closeSession($session_id);
		}
		Zend_Session::namespaceUnset('report_operation');
		Zend_Auth::getInstance()->clearIdentity();
		session_unset();
		Zend_Session::regenerateId();
		session_destroy();
		$this->redirect('en/auth/logout');
	}	
}