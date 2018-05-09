<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'mobile_detect' . DS . 'Mobile_Detect.php';
class CashiersCollectorsController extends Zend_Controller_Action{
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
    /**
     * @var object
     */
    private $translate = null;
	//initialize backoffice layout and set dates for reports and sorting in reports
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
		//setup number of pages items per report from database or set default 200
		require_once MODELS_DIR . DS . 'BoSetupModel.php';
        $defaultPerPage = BoSetupModel::numberOfItemsPerPage($this->session_id);
		$this->defaultPerPage = $defaultPerPage["lines_for_page"];
		if(!isset($this->defaultPerPage)) {
            $this->defaultPerPage = 200;
        }
		if(!isset($this->session_space)){
			$this->session_space = new Zend_Session_Namespace('report_operation');
			if(!isset($this->session_space->startdate) && !isset($this->session_space->enddate)){
				require_once MODELS_DIR . DS . 'DateTimeModel.php';
				$date2 = new Zend_Date();
				$now_in_month = $date2->now();
				$startdate = DateTimeModel::firstDayInMonth();
				$enddate = $now_in_month->toString('dd-MMM-yyyy');
                $months_in_past = DateTimeModel::monthsInPast($this->session_id);
                $months_in_past = $months_in_past['report_date_limit'];
				$this->session_space->months_in_past = $months_in_past;
				$this->session_space->startdate = date('d-M-Y', (strtotime($startdate) == false) ? time() : strtotime($startdate));
				$this->session_space->enddate = date('d-M-Y', (strtotime($enddate) == false) ? time() : strtotime($enddate));
				$this->session_space->limitPerPage = $this->defaultPerPage;
				$this->session_space->columns = 1;
				$this->session_space->order = 'asc';
			}
		}
	}
	
	private function logVisitedPageError()
	{
		$superrola = $_SESSION['auth_space']['session']['subject_super_type_name']; //superrola
		$rola = $_SESSION['auth_space']['session']['subject_type_name']; //rola		
		$username = $_SESSION['auth_space']['session']['username']; //username of backoffice user
		$session_id = $_SESSION['auth_space']['session']['session_out']; //backoffice session id number
		require_once HELPERS_DIR . DS . 'DateTimeHelper.php';
		$date_now = DateTimeHelper::getDateFormat8();
		require_once HELPERS_DIR . DS . 'ErrorMailHelper.php';
		$origin_url = $_SERVER['HTTP_REFERER'];
		$dest_url = $_SERVER['REQUEST_URI'];
		if($origin_url == ""){
			$message = "User with username {$username} and role {$rola} and super-role {$superrola} and backoffice session id = {$session_id} tried to visit: <br /> Date and time: {$date_now} <br /> From manually entered URL address in browser <br /> To page {$dest_url} <br /> CashiersCollectorsController";
		}else{
			$message = "User with username {$username} and role {$rola} and super-role {$superrola} and backoffice session id = {$session_id} tried to visit: <br /> Date and time: {$date_now} <br /> From page {$origin_url} <br /> To page {$dest_url} <br /> CashiersCollectorsController";
		}
		//ErrorMailHelper::writeError($message, $message);
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
	
	//set permissions for roles for entire cashiers collectors verticale
	private function setRolePermissions(){
		$rola = $_SESSION['auth_space']['session']['subject_type_name']; //rola
		if($rola == ROLA_AD_CASHIER || $rola == ROLA_AD_THAICASHIER || $rola == ROLA_AD_CASHIER_SUBLEVEL || 
			$rola == ROLA_AD_CASHIER_PAYOUT){
			$this->logVisitedPageError();
			$this->redirect($_SERVER['HTTP_REFERER']);
		}
	}
	
	//check if user has authenticated
	public function preDispatch(){
		$auth = Zend_Auth::getInstance();
		if(!$auth->hasIdentity())
			$this->forward('login', 'auth');
		else{
			$authInfo = $auth->getIdentity();
			if(isset($authInfo))
				$this->session_id = $authInfo->session_id;
		} //validate session if user is active or logout if inactive
        $this->writeFirebugInfo();
		require_once MODELS_DIR . DS . 'SessionModel.php';
		$res = SessionModel::validateSession($this->session_id);
		if($res['status'] == NO) {
            $this->forward('terminate', 'auth');
        }
		//check if logged in user with role has permissions to access
		//cashiers collectors menu or redirect back to incoming address
		$this->setRolePermissions();
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
	
	//returns header from server response
	private function getHeader($header){
		$temp = 'HTTP_' . strtoupper(str_replace('-', '_', $header));
		if (!empty($_SERVER[$temp]))
			return $_SERVER[$temp];
        return null;
	}

	//end shift
	public function endShiftAction(){
		require_once FORMS_DIR . DS . 'cashiers_collectors' . DS . 'EndShiftForm.php';
		$form = new EndShiftForm();
		require_once MODELS_DIR . DS . 'CashiersCollectorsModel.php';
		$arrCheckShift = CashiersCollectorsModel::checkOpenShifts($this->session_id);
		$status_shift = $arrCheckShift['shift_status'];
        $arrData = array();
		if($status_shift == NO){
			//shift is already closed and show message and disable END SHIFT button
			$form->removeElement('SAVE');
			$this->view->message = $this->translate->_('Shift is already closed. Please open shift first.');
		}else{
			//shift is not opened fill in filds on form
			$arrData = CashiersCollectorsModel::calculateCollectAmountEndShift($this->session_id);
			$form->CASH_IN->setValue(round($arrData['cash_in'],2));
			$form->CASH_OUT->setValue(round($arrData['cash_out'],2));
			$form->BALANCE->setValue(round($arrData['amount'],2));
			$form->START_SHIFT_TIME_S->setValue($arrData['start_time_s'],2);
			$form->CASH_IN_S->setValue(round($arrData['cash_in_s'],2));
			$form->CASH_OUT_S->setValue(round($arrData['cash_out_s'],2));
			$form->BALANCE_S->setValue(round($arrData['amount_s'],2));
			//cash in - cash in start
			$form->TOTAL_IN->setValue(round($arrData['cash_in'] - $arrData['cash_in_s'], 2));
			//cash out - cash out start
			$form->TOTAL_OUT->setValue(round($arrData['cash_out'] - $arrData['cash_out_s'],2));
			//total in - total out
			$form->BALANCE_DIFF->setValue(round(($arrData['cash_in'] - $arrData['cash_in_s']) - ($arrData['cash_out'] - $arrData['cash_out_s']),2));
			//duration time
			$form->DURATION_TIME->setValue('');
		}
		$rola = $_SESSION['auth_space']['session']['subject_type_name']; //rola
		$comment = null;
		$amount = null;
		if($rola != ROLA_AD_SHIFT_CASHIER_W){
			$form->removeElement('AMOUNT');
			$form->removeElement('COMMENT');
			$form->removeElement('BALANCE_START');
			$form->removeElement('BALANCE_END');
		}else{
			$form->BALANCE_START->setValue(round($arrCheckShift['start_amount'],2));
			//balance end = balance_start + balance
			$form->BALANCE_END->setValue(round($arrCheckShift['start_amount'] + $form->getValue('BALANCE_DIFF'),2));
			//amount default = balance_end
			$form->AMOUNT->setValue(round($form->getValue('BALANCE_END'),2));
		}	
		$this->view->form = $form;
		//form is posted and shift is opened
		if ($this->getRequest()->isPost() && $status_shift == YES){
			$formData = $this->getRequest()->getPost();
			if ($form->isValidPartial($formData)) {
				//check again after END SHIFT button is pressed if shift is still opened
				$arrCheckShift = CashiersCollectorsModel::checkOpenShifts($this->session_id);
				$status_shift = $arrCheckShift['shift_status'];
				if($status_shift == NO){
					$form->removeElement('SAVE');
					$this->view->message = $this->translate->_('Shift is already closed. Please open shift first.');
				}else{
					//shift is closed - start new shift operation here
					require_once MODELS_DIR . DS . 'MyAccountModel.php';
					$userData = MyAccountModel::getUserInformationDirect($this->session_id);
					$userDataInfo = $userData["details"]->current();
					$shift_collector_id = $userDataInfo['id'];
					$cash_in = $form->getValue('CASH_IN');
					$cash_out = $form->getValue('CASH_OUT');
					$balance = $form->getValue('BALANCE');
					if($rola == ROLA_AD_SHIFT_CASHIER_W){
						$comment = $form->getValue('COMMENT');
						$amount = $form->getValue('AMOUNT');
						if($amount == ''){
							$amount = $form->getValue('BALANCE');
							if(!isset($amount) || $amount == '' || is_null($amount))
								$amount = 0;
							$form->AMOUNT->setValue($amount);
						}
					}
					$currency = $_SESSION['auth_space']['session']['currency'];
					$arrResult = CashiersCollectorsModel::startEndShift($this->session_id, $shift_collector_id, $cash_in, $cash_out, $balance, $currency, 'MANAGMENT_TYPES.NAME_IN_END_SHIFT', $arrData['start_time_s'], $comment, $amount);
					$end_shift_time = $arrResult['start_time'];
					$duration_out = $arrResult['duration_out'];
					$form->START_SHIFT_TIME->setValue($end_shift_time);
					$form->DURATION_TIME->setValue($duration_out);
					$form->removeElement('SAVE');
					$this->view->information = $this->translate->_('Shift is successfully closed.');
				}
			}
		}
	}
	
	//start shift
	public function startShiftAction(){
		require_once FORMS_DIR . DS . 'cashiers_collectors' . DS . 'StartShiftForm.php';
		$form = new StartShiftForm();
		require_once MODELS_DIR . DS . 'CashiersCollectorsModel.php';
		$arrCheckShift = CashiersCollectorsModel::checkOpenShifts($this->session_id);
		$status_shift = $arrCheckShift['shift_status'];
		if($status_shift == YES){
			//shift is opened show message and disable START SHIFT button
			$form->removeElement('SUBMIT');
			$this->view->message = $this->translate->_('Shift is already opened. Please close shift first.');
		}else{
			//shift is not opened fill in filds on form
			$arrData = CashiersCollectorsModel::calculateCollectAmountShift($this->session_id);
			$form->CASH_IN->setValue(round($arrData['cash_in'],2));
			$form->CASH_OUT->setValue(round($arrData['cash_out'],2));
			$form->BALANCE->setValue(round($arrData['amount'],2));
		}
		$rola = $_SESSION['auth_space']['session']['subject_type_name']; //rola
		$comment = null;
		$amount = null;
		if($rola != ROLA_AD_SHIFT_CASHIER_W){
			$form->removeElement('AMOUNT');
			$form->removeElement('COMMENT');
		}else{
			if($status_shift == NO){
				$form->AMOUNT->setValue($arrCheckShift['start_amount']);
			}
		}
		$this->view->form = $form;
		if ($this->getRequest()->getParam('CANCEL', false)) {
			$locale = Zend_Registry::get('lang');
			$this->redirect($locale . '/mobile-menu');
		}		
		if ($this->getRequest()->isPost() && $status_shift == NO) {
            $formData = $this->getRequest()->getPost();
            if ($form->isValidPartial($formData)) {
                //check after START SHIFT button is pressed if shift is still opened
                $status_shift = CashiersCollectorsModel::checkOpenShifts($this->session_id);
                if ($status_shift["shift_status"] == YES) {
                    $form->removeElement('SUBMIT');
                    $this->view->message = $this->translate->_('Shift is already opened. Please close shift first.');
                } else {
                    //shift is closed - start new shift operation here
                    require_once MODELS_DIR . DS . 'MyAccountModel.php';
                    $userData = MyAccountModel::getUserInformationDirect($this->session_id);
                    $userDataInfo = $userData["details"]->current();
                    $shift_collector_id = $userDataInfo['id'];
                    $cash_in = $form->getValue('CASH_IN');
                    $cash_out = $form->getValue('CASH_OUT');
                    $balance = $form->getValue('BALANCE');
                    if ($rola == ROLA_AD_SHIFT_CASHIER_W) {
                        $comment = $form->getValue('COMMENT');
                        $amount = $form->getValue('AMOUNT');
                        if ($amount == '') {
                            $amount = $arrCheckShift['start_amount'];
                            if (!isset($amount) || $amount == '' || is_null($amount))
                                $amount = 0;
                            $form->AMOUNT->setValue($amount);
                        }
                    }
                    $currency = $_SESSION['auth_space']['session']['currency'];
                    $arrResult = CashiersCollectorsModel::startEndShift($this->session_id, $shift_collector_id, $cash_in, $cash_out, $balance, $currency, 'MANAGMENT_TYPES.NAME_IN_START_SHIFT', null, $comment, $amount);
                    $start_shift_time = $arrResult['start_time'];
                    $form->START_SHIFT_TIME->setValue($start_shift_time);
                    $form->removeElement('SUBMIT');
                    $this->view->information = $this->translate->_('Shift is successfully started.');
                    //redirect to players credit transfer - touch screen
                    $locale = Zend_Registry::get('lang');
                    $this->redirect($locale . '/mobile-menu');
                }
            }
        }
	}

}