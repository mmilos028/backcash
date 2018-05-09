<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'NumberHelper.php';
require_once HELPERS_DIR . DS . 'DateTimeHelper.php';
require_once HELPERS_DIR . DS . 'mobile_detect' . DS . 'Mobile_Detect.php';
class CashierReportsController extends Zend_Controller_Action{
    /**
     * @var int
     */
	public $session_id = 0;
    /**
     * @var object
     */
	public $session_space = null;
    /**
     * @var int
     */
	public $defaultPerPage = 200;
	//if true this will limit some reports to newLimit variable they cannot be generated over 200
    /**
     * @var int
     */
	public $newLimit = 100;
    /**
     * @var bool
     */
	public $limitReports200 = false;
    /**
     * @var object
     */
    public $translate = null;

	//initialization of application layout
	//sets inital dates for reports to first in current month and current day in month as startdate and enddate
	//it is called if reports are visited first time in backoffice
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
		if(!$auth->hasIdentity()){
           if($this->isXmlHttpRequest()){
                $this->forward('login', 'auth');
            }else {
                $this->forward('login', 'auth');
            }
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
		if($this->defaultPerPage > 200 && $this->limitReports200 == true) {
            $this->defaultPerPage = 200;
        }
		if(!isset($this->defaultPerPage)) {
            $this->defaultPerPage = 200;
        }
		if(!isset($this->session_space)){
			$this->session_space = new Zend_Session_Namespace('report_operation');
			require_once MODELS_DIR . DS . 'DateTimeModel.php';
			if(!isset($this->session_space->startdate) && !isset($this->session_space->enddate)){
				$rola = $_SESSION['auth_space']['session']['subject_type_name']; //rola
				//if role is ROLA_AD_COLLECTOR constant then startdate not updateable and is from collectors last collect date
				if($rola != ROLA_AD_COLLECTOR){
					$startdate = DateTimeModel::firstDayInMonth();
					$_SESSION['auth_space']['session']['change_startdate'] = true;
				}else{
					//if user is collector then start date is last time collect
					if($_SESSION['auth_space']['session']['last_time_collect'] == ''){
						//if collector had no collect cash he can change startdate and see from first in current month
						$_SESSION['auth_space']['session']['change_startdate'] = true;
						$startdate = DateTimeModel::firstDayInMonth();
					}else{
						$startdate = date('d-M-Y', strtotime($_SESSION['auth_space']['session']['last_time_collect']));
						$_SESSION['auth_space']['session']['change_startdate'] = false;
					}
				}
				$date2 = new Zend_Date();
				$now_in_month = $date2->now();
				$enddate = $now_in_month->toString('dd-MMM-yyyy');
                $months_in_past = DateTimeModel::monthsInPast($this->session_id);
				$this->session_space->months_in_past = $months_in_past["report_date_limit"];
				$this->session_space->startdate = date('d-M-Y', (strtotime($startdate) == false) ? time() : strtotime($startdate));
				$this->session_space->enddate = date('d-M-Y', (strtotime($enddate) == false) ? time() : strtotime($enddate));
				$this->session_space->limitPerPage = $this->defaultPerPage;
				$this->session_space->columns = 1;
				$this->session_space->order = 'asc';
				$this->session_space->currency_for_report = ALL;
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
			$message = "User with username {$username} and role {$rola} and super-role {$superrola} and backoffice session id = {$session_id} tried to visit: <br /> Date and time: {$date_now} <br /> From manually entered URL address in browser <br /> To page {$dest_url} <br /> CashierReportController";
		}else{
			$message = "User with username {$username} and role {$rola} and super-role {$superrola} and backoffice session id = {$session_id} tried to visit: <br /> Date and time: {$date_now} <br /> From page {$origin_url} <br /> To page {$dest_url} <br /> CashierReportController";
		}
		ErrorMailHelper::writeError($message, $message);
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

	//set permissions for roles for entire transfer credit verticale
	private function setRolePermissions(){
		$rola = $_SESSION['auth_space']['session']['subject_type_name']; //rola
        /*if(!in_array($rola, array(ROLA_AD_CASHIER, ROLA_AD_CASHIER_PAYOUT, SUPER_ROLA_MASTER_CASINO)))
        {
            $this->logVisitedPageError();
            $url = $_SERVER['HTTP_REFERER'];
            $this->redirect($url);
        }*/
	}

	//it is always called before any action is called
	//performes session validation and backoffice logout if session timeout occured
	public function preDispatch(){
		$auth = Zend_Auth::getInstance();
		if(!$auth->hasIdentity())$this->forward('login','auth');
		else {
			$authInfo = $auth->getIdentity();
			if(isset($authInfo)) $this->session_id = $authInfo->session_id;
		}
        $this->writeFirebugInfo();
		require_once MODELS_DIR . DS . 'SessionModel.php';
		$res = NO;
		try{
			$res = SessionModel::validateSession($this->session_id);
			if($res['status'] == NO){
                $this->forward('terminate', 'auth');
            }
		}catch(Zend_Exception $ex){
			throw new Zend_Exception(CursorToArrayHelper::getExceptionTraceAsString($ex));
		}
		//check if logged in user with role has permissions to access
		//reports menu or redirect back to incoming address
		$this->setRolePermissions();
		//display number of game and backoffice sessions on application main menu
		if(!$this->isXmlHttpRequest()){
			$activeSessionsArr = SessionModel::listNumberActivePlayerSession($this->session_id);
			Zend_Layout::getMvcInstance()->assign('no_game_sessions', $activeSessionsArr["no_game_sessions"]);
			Zend_Layout::getMvcInstance()->assign('no_bo_sessions', $activeSessionsArr["no_bo_sessions"]);
		}
	}

	//detect ajax calls
	private function isXmlHttpRequest(){
		return ($this->getHeader('X_REQUESTED_WITH') == 'XMLHttpRequest');
	}

	//returns server response
	private function getHeader($header){
		$temp = 'HTTP_' . strtoupper(str_replace('-', '_', $header));
		if (!empty($_SERVER[$temp]))return $_SERVER[$temp];
        return null;
	}

	//set dates to session and filter or correct date format
	public function setDates($startdate, $enddate){
		require_once MODELS_DIR . DS . 'DateTimeModel.php';
		$fdm = DateTimeModel::firstDayInMonth();
		$date2 = new Zend_Date();
		$now_in_month = $date2->now();
		if(!isset($startdate) && !isset($enddate)){
			$rola = $_SESSION['auth_space']['session']['subject_type_name'];
			if($rola == ROLA_AD_COLLECTOR){
				if(!isset($_SESSION['auth_space']['session']['last_time_collect'])){
					//if collector had no collect cash he can change startdate and see from first in current month
					$startdate = date('d-M-Y', (strtotime($fdm) == false) ? time() : strtotime($fdm));
					if(!isset($startdate))
					$startdate = date('d-M-Y', (strtotime($fdm) == false) ? time() : strtotime($fdm));
					else
					$startdate = $this->session_space->startdate;
				}else{
					$startdate = date('d-M-Y', strtotime($_SESSION['auth_space']['session']['last_time_collect']));
				}
				if(!isset($enddate))
				$enddate = $this->session_space->enddate;
				else
				$enddate = $now_in_month->toString('dd-MMM-yyyy');
				$this->session_space->startdate = date('d-M-Y', (strtotime($startdate) == false) ? time() : strtotime($startdate));
				$this->session_space->enddate = date('d-M-Y', (strtotime($enddate) == false) ? time() : strtotime($enddate));
			}else{
				//if role is not Ad / Collector
				if(!(!isset($startdate) && !isset($enddate))){
					//if date is set through form post
					if(!isset($enddate))
					$enddate = $now_in_month->toString("dd-MMM-yyyy");
					$this->session_space->startdate = date('d-M-Y', (strtotime($startdate) == false) ? time() : strtotime($startdate));
					$this->session_space->enddate = date('d-M-Y', (strtotime($enddate) == false) ? time() : strtotime($enddate));
				}
			}
		}else{
			//is posted through form kept on session
			$this->session_space->startdate = date('d-M-Y', (strtotime($startdate) == false) ? time() : strtotime($startdate));
			$this->session_space->enddate = date('d-M-Y', (strtotime($enddate) == false) ? time() : strtotime($enddate));
		}
		$startdate = $this->session_space->startdate;
		$enddate = $this->session_space->enddate;
		return array("start_date"=>$startdate, "end_date"=>$enddate);
	}

	//it is default action if reports are visited
	public function indexAction(){
		$this->forward('logout','auth');
	}

    private function goToReportsMenu(){
        $rola = $_SESSION['auth_space']['session']['subject_type_name'];
        $locale = Zend_Registry::get('lang');
		$redirector = new Zend_Controller_Action_Helper_Redirector();

        if(in_array($rola, array(ROLA_AD_SHIFT_CASHIER_S, ROLA_AD_SHIFT_CASHIER_W))){
            $redirector->gotoUrl($locale . '/mobile-menu/index');
        }else if(in_array($rola, array(ROLA_AD_CASHIER, ROLA_AD_CASHIER_PAYOUT, ROLA_AD_CASHIER_SUBLEVEL))){
            //$redirector->gotoUrl($locale . '/mobile-menu/cashier-reports');
            $redirector->gotoUrl($locale . '/mobile-menu/index');
        }else{
            $redirector->gotoUrl($locale . '/mobile-menu/reports');
        }
    }

    public function cashierHistoryAction(){
        if($this->getRequest()->getParam('PREVIOUS_PAGE', false)){
			$this->goToReportsMenu();
		}
        require_once FORMS_DIR . DS . 'cashier_reports' . DS . 'ListCreditTransfersReportFullForm.php';
		$form = new ListCreditTransfersReportFullForm();
        $form->removeElement('PAGE');
        $form->removeElement('LIMIT');
        $form->removeElement('FILTER_BY_PLAYER');
        $form->removeElement('REPORT_STARTDATE');
        $form->removeElement('REPORT_ENDDATE');
        $form->removeElement('TRANSACTION_TYPE');
        $form->removeElement('GENERATE_REPORT');
		//$start_date = $this->getRequest()->getParam('REPORT_STARTDATE',null);
		//$end_date = $this->getRequest()->getParam('REPORT_ENDDATE',null);
		$transaction_type = $this->getRequest()->getParam('TRANSACTION_TYPE', ALL);
		$filter = $_SESSION['auth_space']['session']['username'];
		//$dateRes = $this->setDates($start_date, $end_date);
		//$start_date = $dateRes['start_date'];
		//$end_date = $dateRes['end_date'];

        $start_date = DateTimeHelper::getDateFormat10(30);
        $end_date = DateTimeHelper::getDateFormat11();

        $new_start_date = $start_date . " " . "00:00:00";
        $new_start_date = substr($new_start_date, 0, 20);
        $new_end_date = $end_date . " " . "23:59:59";
        $new_end_date = substr($new_end_date, 0, 20);

        $pageNo = 1;
        $perPage = 5;
		require_once MODELS_DIR . DS . 'TransactionReportModel.php';
		$total_pages = 1;
		$arrData = array();
		if($pageNo == 1){
			$arrData = TransactionReportModel::getListCreditTransfers($this->session_id, $new_start_date, $new_end_date, $pageNo, $perPage, $transaction_type, $filter);
			$total_items = $arrData["info"][0]['cnt'];
			$total_pages = ceil($total_items / $perPage);
		}else{
			if($pageNo >= $total_pages){
				$arrData = TransactionReportModel::getListCreditTransfers($this->session_id, $new_start_date, $new_end_date, $pageNo, $perPage, $transaction_type, $filter);
				$total_items = $arrData["info"][0]['cnt'];
				$total_pages = ceil($total_items / $perPage);
			}
		}
		if(count($arrData["table"]) == 0){
			$pageNo = 1;
			$arrData = TransactionReportModel::getListCreditTransfers($this->session_id, $new_start_date, $new_end_date, $pageNo, $perPage, $transaction_type, $filter);
			$total_items = $arrData["info"][0]["cnt"];
			$total_pages = ceil($total_items / $perPage);
		}
		$this->view->paginator = $arrData["table"];
		/*$form->REPORT_STARTDATE->setValue($start_date);
		$form->REPORT_ENDDATE->setValue($end_date);
		$form->TRANSACTION_TYPE->setValue($transaction_type);*/
		$this->view->form = $form;
		$this->view->startdate = $start_date;
		$this->view->enddate = $end_date;
    }

	public function myHistoryAction(){
        if($this->getRequest()->getParam('PREVIOUS_PAGE', false)){
			$this->goToReportsMenu();
		}
        if($this->getRequest()->getParam('MIN_REPORT', false)){
			$locale = Zend_Registry::get('lang');
			$redirector = new Zend_Controller_Action_Helper_Redirector();
			$redirector->gotoUrl($locale . '/cashier-reports/my-history-min');
		}
        require_once FORMS_DIR . DS . 'cashier_reports' . DS . 'ListCreditTransfersReportFullForm.php';
		$form = new ListCreditTransfersReportFullForm();
        $form->removeElement('PAGE');
        $form->removeElement('LIMIT');
        $form->removeElement('FILTER_BY_PLAYER');
		$start_date = $this->getRequest()->getParam('REPORT_STARTDATE',null);
		$end_date = $this->getRequest()->getParam('REPORT_ENDDATE',null);
		$transaction_type = $this->getRequest()->getParam('TRANSACTION_TYPE', ALL);
		$filter = $_SESSION['auth_space']['session']['username'];
		$dateRes = $this->setDates($start_date, $end_date);
		$start_date = $dateRes['start_date'];
		$end_date = $dateRes['end_date'];

        $new_start_date = $start_date . " " . "00:00:00";
        $new_start_date = substr($new_start_date, 0, 20);
        $new_end_date = $end_date . " " . "23:59:59";
        $new_end_date = substr($new_end_date, 0, 20);

        $pageNo = 1;
        $perPage = 10;
		require_once MODELS_DIR . DS . 'TransactionReportModel.php';
		$total_pages = 1;
		$arrData = array();
		if($pageNo == 1){
			$arrData = TransactionReportModel::getListCreditTransfers($this->session_id, $new_start_date, $new_end_date, $pageNo, $perPage, $transaction_type, $filter);
			$total_items = $arrData["info"][0]['cnt'];
			$total_pages = ceil($total_items / $perPage);
		}else{
			if($pageNo >= $total_pages){
				$arrData = TransactionReportModel::getListCreditTransfers($this->session_id, $new_start_date, $new_end_date, $pageNo, $perPage, $transaction_type, $filter);
				$total_items = $arrData["info"][0]['cnt'];
				$total_pages = ceil($total_items / $perPage);
			}
		}
		if(count($arrData["table"]) == 0){
			$pageNo = 1;
			$arrData = TransactionReportModel::getListCreditTransfers($this->session_id, $new_start_date, $new_end_date, $pageNo, $perPage, $transaction_type, $filter);
			$total_items = $arrData["info"][0]["cnt"];
			$total_pages = ceil($total_items / $perPage);
		}
		$this->view->paginator = $arrData["table"];
		$form->REPORT_STARTDATE->setValue($start_date);
		$form->REPORT_ENDDATE->setValue($end_date);
		$form->TRANSACTION_TYPE->setValue($transaction_type);
		$this->view->form = $form;
		$this->view->startdate = $start_date;
		$this->view->enddate = $end_date;
    }

    public function myHistoryMinAction(){
        if($this->getRequest()->getParam('PREVIOUS_PAGE', false)){
			$this->goToReportsMenu();
		}
        if($this->getRequest()->getParam('MAX_REPORT', false)){
			$locale = Zend_Registry::get('lang');
			$redirector = new Zend_Controller_Action_Helper_Redirector();
			$redirector->gotoUrl($locale . '/cashier-reports/my-history');
		}
        require_once FORMS_DIR . DS . 'cashier_reports' . DS . 'ListCreditTransfersReportMinForm.php';
		$form = new ListCreditTransfersReportminForm();
        $form->removeElement('PAGE');
        $form->removeElement('LIMIT');
        $form->removeElement('FILTER_BY_PLAYER');
		$start_date = $this->getRequest()->getParam('REPORT_STARTDATE',null);
		$end_date = $this->getRequest()->getParam('REPORT_ENDDATE',null);
		$transaction_type = $this->getRequest()->getParam('TRANSACTION_TYPE', ALL);
		$filter = $_SESSION['auth_space']['session']['username'];
		$dateRes = $this->setDates($start_date, $end_date);
		$start_date = $dateRes['start_date'];
		$end_date = $dateRes['end_date'];

        $new_start_date = $start_date . " " . "00:00:00";
        $new_start_date = substr($new_start_date, 0, 20);
        $new_end_date = $end_date . " " . "23:59:59";
        $new_end_date = substr($new_end_date, 0, 20);

        $pageNo = 1;
        $perPage = 10;
		require_once MODELS_DIR . DS . 'TransactionReportModel.php';
		$total_pages = 1;
		$arrData = array();
		if($pageNo == 1){
			$arrData = TransactionReportModel::getListCreditTransfers($this->session_id, $new_start_date, $new_end_date, $pageNo, $perPage, $transaction_type, $filter);
			$total_items = $arrData["info"][0]['cnt'];
			$total_pages = ceil($total_items / $perPage);
		}else{
			if($pageNo >= $total_pages){
				$arrData = TransactionReportModel::getListCreditTransfers($this->session_id, $new_start_date, $new_end_date, $pageNo, $perPage, $transaction_type, $filter);
				$total_items = $arrData["info"][0]['cnt'];
				$total_pages = ceil($total_items / $perPage);
			}
		}
		if(count($arrData["table"]) == 0){
			$pageNo = 1;
			$arrData = TransactionReportModel::getListCreditTransfers($this->session_id, $new_start_date, $new_end_date, $pageNo, $perPage, $transaction_type, $filter);
			$total_items = $arrData["info"][0]["cnt"];
			$total_pages = ceil($total_items / $perPage);
		}
		$this->view->paginator = $arrData["table"];
		$form->REPORT_STARTDATE->setValue($start_date);
		$form->REPORT_ENDDATE->setValue($end_date);
		$form->TRANSACTION_TYPE->setValue($transaction_type);
		$this->view->form = $form;
		$this->view->startdate = $start_date;
		$this->view->enddate = $end_date;
    }

    public function playerHistoryAction(){
        if($this->getRequest()->getParam('PREVIOUS_PAGE', false)){
			$this->goToReportsMenu();
		}
        if($this->getRequest()->getParam('MIN_REPORT', false)){
			$locale = Zend_Registry::get('lang');
			$redirector = new Zend_Controller_Action_Helper_Redirector();
			$redirector->gotoUrl($locale . '/cashier-reports/player-history-min');
		}
        require_once FORMS_DIR . DS . 'cashier_reports' . DS . 'ListCreditTransfersReportFullForm.php';
		$form = new ListCreditTransfersReportFullForm();
        $form->removeElement('PAGE');
        $form->removeElement('LIMIT');
		$start_date = $this->getRequest()->getParam('REPORT_STARTDATE',null);
		$end_date = $this->getRequest()->getParam('REPORT_ENDDATE',null);
		$transaction_type = $this->getRequest()->getParam('TRANSACTION_TYPE', ALL);
		$filter = $this->getRequest()->getParam('FILTER_BY_PLAYER', ALL);
		$dateRes = $this->setDates($start_date, $end_date);
		$start_date = $dateRes['start_date'];
		$end_date = $dateRes['end_date'];

        $new_start_date = $start_date . " " . "00:00:00";
        $new_start_date = substr($new_start_date, 0, 20);
        $new_end_date = $end_date . " " . "23:59:59";
        $new_end_date = substr($new_end_date, 0, 20);

        $pageNo = 1;
        $perPage = 10;
		require_once MODELS_DIR . DS . 'TransactionReportModel.php';
		$total_pages = 1;
		$arrData = array();
		if($pageNo == 1){
			$arrData = TransactionReportModel::getListCreditTransfers($this->session_id, $new_start_date, $new_end_date, $pageNo, $perPage, $transaction_type, $filter);
			$total_items = $arrData["info"][0]['cnt'];
			$total_pages = ceil($total_items / $perPage);
		}else{
			if($pageNo >= $total_pages){
				$arrData = TransactionReportModel::getListCreditTransfers($this->session_id, $new_start_date, $new_end_date, $pageNo, $perPage, $transaction_type, $filter);
				$total_items = $arrData["info"][0]['cnt'];
				$total_pages = ceil($total_items / $perPage);
			}
		}
		if(count($arrData["table"]) == 0){
			$pageNo = 1;
			$arrData = TransactionReportModel::getListCreditTransfers($this->session_id, $new_start_date, $new_end_date, $pageNo, $perPage, $transaction_type, $filter);
			$total_items = $arrData["info"][0]["cnt"];
			$total_pages = ceil($total_items / $perPage);
		}
		$this->view->paginator = $arrData["table"];
		$form->REPORT_STARTDATE->setValue($start_date);
		$form->REPORT_ENDDATE->setValue($end_date);
		$form->FILTER_BY_PLAYER->setValue($filter);
		$form->TRANSACTION_TYPE->setValue($transaction_type);
		$this->view->form = $form;
		$this->view->startdate = $start_date;
		$this->view->enddate = $end_date;
    }

    public function playerHistoryMinAction(){
        if($this->getRequest()->getParam('PREVIOUS_PAGE', false)){
			$this->goToReportsMenu();
		}
        if($this->getRequest()->getParam('MAX_REPORT', false)){
			$locale = Zend_Registry::get('lang');
			$redirector = new Zend_Controller_Action_Helper_Redirector();
			$redirector->gotoUrl($locale . '/cashier-reports/player-history');
		}
        require_once FORMS_DIR . DS . 'cashier_reports' . DS . 'ListCreditTransfersReportMinForm.php';
		$form = new ListCreditTransfersReportMinForm();
        $form->removeElement('PAGE');
        $form->removeElement('LIMIT');
		$start_date = $this->getRequest()->getParam('REPORT_STARTDATE',null);
		$end_date = $this->getRequest()->getParam('REPORT_ENDDATE',null);
		$transaction_type = $this->getRequest()->getParam('TRANSACTION_TYPE', ALL);
		$filter = $this->getRequest()->getParam('FILTER_BY_PLAYER', ALL);
		$dateRes = $this->setDates($start_date, $end_date);
		$start_date = $dateRes['start_date'];
		$end_date = $dateRes['end_date'];

        $new_start_date = $start_date . " " . "00:00:00";
        $new_start_date = substr($new_start_date, 0, 20);
        $new_end_date = $end_date . " " . "23:59:59";
        $new_end_date = substr($new_end_date, 0, 20);

        $pageNo = 1;
        $perPage = 10;
		require_once MODELS_DIR . DS . 'TransactionReportModel.php';
		$total_pages = 1;
		$arrData = array();
		if($pageNo == 1){
			$arrData = TransactionReportModel::getListCreditTransfers($this->session_id, $new_start_date, $new_end_date, $pageNo, $perPage, $transaction_type, $filter);
			$total_items = $arrData["info"][0]['cnt'];
			$total_pages = ceil($total_items / $perPage);
		}else{
			if($pageNo >= $total_pages){
				$arrData = TransactionReportModel::getListCreditTransfers($this->session_id, $new_start_date, $new_end_date, $pageNo, $perPage, $transaction_type, $filter);
				$total_items = $arrData["info"][0]['cnt'];
				$total_pages = ceil($total_items / $perPage);
			}
		}
		if(count($arrData["table"]) == 0){
			$pageNo = 1;
			$arrData = TransactionReportModel::getListCreditTransfers($this->session_id, $new_start_date, $new_end_date, $pageNo, $perPage, $transaction_type, $filter);
			$total_items = $arrData["info"][0]["cnt"];
			$total_pages = ceil($total_items / $perPage);
		}
		$this->view->paginator = $arrData["table"];
		$form->REPORT_STARTDATE->setValue($start_date);
		$form->REPORT_ENDDATE->setValue($end_date);
		$form->FILTER_BY_PLAYER->setValue($filter);
		$form->TRANSACTION_TYPE->setValue($transaction_type);
		$this->view->form = $form;
		$this->view->startdate = $start_date;
		$this->view->enddate = $end_date;
    }

    //generates cash report when first time loading this page
	public function cashReportAction(){
        $link = '/cashier-reports/cash-report/';
		$this->cash_report($this, $link, 1);
	}

	//cash report upper level choosen
	//generates cash report when going level up on green arrow
	public function cashReportUpLevelAction(){
        $link = '/cashier-reports/cash-report-up-level/';
		$this->cash_report($this, $link, 0);
	}

	//generates cash report through link on report
	//generates cash report on affiliate link click and going down the hierarchy
	public function cashReportLinkAction(){
        $link = '/cashier-reports/cash-report-link/';
		$this->cash_report($this, $link, 1);
	}

    private function cash_report($parentController, $link, $default_direction){
        if($this->getRequest()->getParam('PREVIOUS_PAGE', false)){
			$this->goToReportsMenu();
		}
        if($this->getRequest()->getParam('MIN_REPORT', false)){
			$locale = Zend_Registry::get('lang');
			$redirector = new Zend_Controller_Action_Helper_Redirector();
			$redirector->gotoUrl($locale . '/cashier-reports/cash-report-min');
		}
		require_once FORMS_DIR . DS . 'cashier_reports' . DS . 'ListCashReportFullForm.php';
		//initialize form class - form to list reports
		$form = new ListCashReportFullForm();
		//receive startdate and enddate from form as parameters
		$start_date = $parentController->getRequest()->getParam('REPORT_STARTDATE', null);
		$end_date = $parentController->getRequest()->getParam('REPORT_ENDDATE', null);
		$dateRes = $parentController->setDates($start_date, $end_date);
		$start_date = $dateRes['start_date'];
		$end_date = $dateRes['end_date'];
		//set dates on form to values from session
		$form->REPORT_STARTDATE->setValue($start_date);
		$form->REPORT_ENDDATE->setValue($end_date);
		//receive parameters for sorting report

		$affiliate_id = $parentController->getRequest()->getParam('AFFILIATES', null);
		$direction = $parentController->getRequest()->getParam('LEVEL_DIRECTION', $default_direction); //direction of going through hierarchy 0 is for up level, 1 is for down level
		$currency = $parentController->getRequest()->getParam('CURRENCIES', ALL);
		//if up level received
		if($affiliate_id == '-1'){
			$affiliate_id = $parentController->getRequest()->getParam('AFFILIATE_NUMBER', -1);
			$direction = 0;
		}

        $baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
		$lang = Zend_Registry::get('lang');
		$form->setAction($baseUrl . '/'. $lang . $link . 'AFFILIATES/' . $affiliate_id);

        require_once MODELS_DIR . DS . 'CashReportModel.php';
		try{

			$arrData = CashReportModel::listCashReport($parentController->session_id, $affiliate_id, $currency, $direction, $start_date, $end_date);
		}catch(Zend_Exception $ex){
			throw $ex;
		}
		require_once MODELS_DIR . DS . 'AffiliatesModel.php';
		$affiliate_details = AffiliatesModel::getAffiliateDetails($parentController->session_id, $arrData['aff_id_out']); //get affiliate details
        $affiliate_details = $affiliate_details['details'];
		$parentController->view->affiliate_name = $affiliate_details['user_name'];
		$parentController->view->paginator = $arrData["cursor"];

        $form->removeElement('PAGE');
        $form->removeElement('LIMIT');
		$parentController->view->affiliate_id = $arrData["aff_id_out"];
		$form->AFFILIATES->clearMultiOptions();
		$form->AFFILIATES->addMultiOption('-10', $parentController->translate->_("SelectAffiliate"));
		$is_root = $arrData["is_root"]; //get if it is root - first level in hierarchy is 1
		//if it is not first level affiliate then add up level into affiliates list
		//-1 is signal to go up level and with affiliate number not parentController -1 and with direction 0 (up)
		if($is_root == '0') {
            $form->AFFILIATES->addMultiOption('-1', 'up level');
        }
		//if it is first level affiliate
		if($is_root == '1') {
            $form->AFFILIATES->addMultiOption($arrData["aff_id_out"], $this->translate->_(ROOT_AFFILIATE));
        }
		//if current affiliate name is added then with his number and name and direction is 1 (down)
		else {
            $form->AFFILIATES->addMultiOption($arrData["aff_id_out"], $affiliate_details['user_name']);
        }
		foreach($arrData['cursor'] as $cur) {
            if ($cur['player'] != SUPER_ROLA_PLAYER) {
                $form->AFFILIATES->addMultiOption($cur['id'], $cur['name']);
            }
        }
		//total report for cash report generating
		$form->CURRENCIES->setValue($currency);
		$form->AFFILIATE_NUMBER->setValue($arrData["aff_id_out"]);
		$parentController->view->is_root = $is_root;
		$form->AFFILIATES->setValue($arrData["aff_id_out"]);
		$parentController->view->form = $form;

        $parentController->view->total = CashReportModel::listCashReportTotal($parentController->session_id, $currency, $affiliate_id, $direction, $start_date, $end_date);
        //$parentController->view->totalApt = CashReportModel::listCashReportTotalApt($parentController->session_id, $currency, $affiliate_id, $direction, $start_date, $end_date);

        $parentController->_helper->viewRenderer('cash-report');
	}

    //generates cash report when first time loading this page
	public function cashReportMinAction(){
        $link = '/cashier-reports/cash-report-min/';
		$this->cash_report_min($this, $link, 1);
	}

	//cash report upper level choosen
	//generates cash report when going level up on green arrow
	public function cashReportMinUpLevelAction(){
        $link = '/cashier-reports/cash-report-min-up-level/';
		$this->cash_report_min($this, $link, 0);
	}

	//generates cash report through link on report
	//generates cash report on affiliate link click and going down the hierarchy
	public function cashReportMinLinkAction(){
        $link = '/cashier-reports/cash-report-min-link/';
		$this->cash_report_min($this, $link, 1);
	}

    private function cash_report_min($parentController, $link, $default_direction){
        if($this->getRequest()->getParam('PREVIOUS_PAGE', false)){
			$this->goToReportsMenu();
		}
        if($this->getRequest()->getParam('MAX_REPORT', false)){
			$locale = Zend_Registry::get('lang');
			$redirector = new Zend_Controller_Action_Helper_Redirector();
			$redirector->gotoUrl($locale . '/cashier-reports/cash-report');
		}
		require_once FORMS_DIR . DS . 'cashier_reports' . DS . 'ListCashReportMinForm.php';
		//initialize form class - form to list reports
		$form = new ListCashReportMinForm();
		//receive startdate and enddate from form as parameters
		$start_date = $parentController->getRequest()->getParam('REPORT_STARTDATE', null);
		$end_date = $parentController->getRequest()->getParam('REPORT_ENDDATE', null);
		$dateRes = $parentController->setDates($start_date, $end_date);
		$start_date = $dateRes['start_date'];
		$end_date = $dateRes['end_date'];
		//set dates on form to values from session
		$form->REPORT_STARTDATE->setValue($start_date);
		$form->REPORT_ENDDATE->setValue($end_date);
		//receive parameters for sorting report

		$affiliate_id = $parentController->getRequest()->getParam('AFFILIATES', null);
		$direction = $parentController->getRequest()->getParam('LEVEL_DIRECTION', $default_direction); //direction of going through hierarchy 0 is for up level, 1 is for down level
		$currency = $parentController->getRequest()->getParam('CURRENCIES', ALL);
		//if up level received
		if($affiliate_id == '-1'){
			$affiliate_id = $parentController->getRequest()->getParam('AFFILIATE_NUMBER', -1);
			$direction = 0;
		}

        $baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
		$lang = Zend_Registry::get('lang');
		$form->setAction($baseUrl . '/'. $lang . $link . 'AFFILIATES/' . $affiliate_id);

        require_once MODELS_DIR . DS . 'CashReportModel.php';
		try{

			$arrData = CashReportModel::listCashReport($parentController->session_id, $affiliate_id, $currency, $direction, $start_date, $end_date);
		}catch(Zend_Exception $ex){
			throw $ex;
		}
		require_once MODELS_DIR . DS . 'AffiliatesModel.php';
		$affiliate_details = AffiliatesModel::getAffiliateDetails($parentController->session_id, $arrData['aff_id_out']); //get affiliate details
        $affiliate_details = $affiliate_details['details'];
		$parentController->view->affiliate_name = $affiliate_details['user_name'];
		$parentController->view->paginator = $arrData["cursor"];

        $form->removeElement('PAGE');
        $form->removeElement('LIMIT');
		$parentController->view->affiliate_id = $arrData["aff_id_out"];
		$form->AFFILIATES->clearMultiOptions();
		$form->AFFILIATES->addMultiOption('-10', $parentController->translate->_("SelectAffiliate"));
		$is_root = $arrData["is_root"]; //get if it is root - first level in hierarchy is 1
		//if it is not first level affiliate then add up level into affiliates list
		//-1 is signal to go up level and with affiliate number not parentController -1 and with direction 0 (up)
		if($is_root == '0') {
            $form->AFFILIATES->addMultiOption('-1', 'up level');
        }
		//if it is first level affiliate
		if($is_root == '1') {
            $form->AFFILIATES->addMultiOption($arrData["aff_id_out"], $this->translate->_(ROOT_AFFILIATE));
        }
		//if current affiliate name is added then with his number and name and direction is 1 (down)
		else {
            $form->AFFILIATES->addMultiOption($arrData["aff_id_out"], $affiliate_details['user_name']);
        }
		foreach($arrData['cursor'] as $cur) {
            if ($cur['player'] != SUPER_ROLA_PLAYER) {
                $form->AFFILIATES->addMultiOption($cur['id'], $cur['name']);
            }
        }
		//total report for cash report generating
		$form->CURRENCIES->setValue($currency);
		$form->AFFILIATE_NUMBER->setValue($arrData["aff_id_out"]);
		$parentController->view->is_root = $is_root;
		$form->AFFILIATES->setValue($arrData["aff_id_out"]);
		$parentController->view->form = $form;


        $parentController->view->total = CashReportModel::listCashReportTotal($parentController->session_id, $currency, $affiliate_id, $direction, $start_date, $end_date);
        //$parentController->view->totalApt = CashReportModel::listCashReportTotalApt($parentController->session_id, $currency, $affiliate_id, $direction, $start_date, $end_date);

        $parentController->_helper->viewRenderer('cash-report-min');
	}

    private function cash_report_daily($parentController, $link, $default_direction = 1){
        if($this->getRequest()->getParam('PREVIOUS_PAGE', false)){
			$this->goToReportsMenu();
		}
        if($this->getRequest()->getParam('MIN_REPORT', false)){
			$locale = Zend_Registry::get('lang');
			$redirector = new Zend_Controller_Action_Helper_Redirector();
			$redirector->gotoUrl($locale . '/cashier-reports/cash-report-min-daily');
		}
        $lang = Zend_Registry::get("lang");
        $baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
        $start_date = $parentController->getRequest()->getParam('REPORT_STARTDATE', null);
        $end_date = $parentController->getRequest()->getParam('REPORT_ENDDATE', null);
        $dateRes = $parentController->setDates($start_date, $end_date);
        $start_date = $dateRes['start_date'];
        $end_date = $dateRes['end_date'];
        // pagination isn't implemented
        $affiliate_id = $parentController->getRequest()->getParam('AFFILIATES', null);
        $direction = $parentController->getRequest()->getParam('LEVEL_DIRECTION', $default_direction);
        $currency = $parentController->getRequest()->getParam('CURRENCIES', ALL);

        require_once FORMS_DIR . DS . 'cashier_reports' . DS . 'ListCashReportFullForm.php';
        $form = new ListCashReportFullForm();

        //if up level received, ovo se ne koristi
        if($affiliate_id == '-1'){
            $affiliate_id = $parentController->getRequest()->getParam('AFFILIATE_NUMBER', -1);
            $direction = 0;
        }

        $baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
		$lang = Zend_Registry::get('lang');
		$form->setAction($baseUrl . '/'. $lang . $link . 'AFFILIATES/' . $affiliate_id);

        try{
            require_once MODELS_DIR . DS . 'CashReportModel.php';
            $arrData = CashReportModel::listCashReportDaily($this->session_id, $affiliate_id, $currency, $direction, $start_date, $end_date);
        }catch(Zend_Exception $ex){
            throw new Zend_Exception(CursorToArrayHelper::getExceptionTraceAsString($ex));
        }

        require_once MODELS_DIR . DS . 'AffiliatesModel.php';
        $affiliate_details = AffiliatesModel::getAffiliateDetails($parentController->session_id, $arrData['aff_id_out']);
        $affiliate_details = $affiliate_details['details'];

        $form->setAction($baseUrl . '/'. $lang . '/cashier-reports/cash-report-daily');
        $form->REPORT_STARTDATE->setValue($start_date);
        $form->REPORT_ENDDATE->setValue($end_date);
        $form->removeElement('PAGE');
        $form->removeElement('LIMIT');
        $form->AFFILIATES->clearMultiOptions();
        $form->AFFILIATES->addMultiOption('-10', $parentController->translate->_("SelectAffiliate"));
        $is_root = $arrData['is_root'];
        //if it is not first level affiliate then add up level into affiliates list
        //-1 is signal to go up level and with affiliate number not parentController -1 and with direction 0 (up)
        if($is_root == '0') {
            $form->AFFILIATES->addMultiOption('-1', 'up level');
        }
        if($is_root == '1') {
            $form->AFFILIATES->addMultiOption($arrData['aff_id_out'], $this->translate->_(ROOT_AFFILIATE));
        }
        //if current affiliate name is added then with his number and name and direction is 1 (down)
        else {
            $form->AFFILIATES->addMultiOption($arrData['aff_id_out'], $affiliate_details['user_name']);
        }
        foreach($arrData['cursor'] as $cur) {
            if ($cur['player'] != SUPER_ROLA_PLAYER) {
                $form->AFFILIATES->addMultiOption($cur['id'], $cur['name']);
            }
        }
        $form->CURRENCIES->setValue($currency);
        $form->AFFILIATE_NUMBER->setValue($arrData['aff_id_out']);
        $form->AFFILIATES->setValue($arrData['aff_id_out']);

        $has_integration = $arrData['has_integration'];

        $parentController->view->paginator = $arrData['cursor'];
        $parentController->view->affiliate_id = $arrData['aff_id_out'];
        $parentController->view->form = $form;
        $parentController->view->affiliate_name = $affiliate_details['user_name'];
        $parentController->view->is_root = $is_root;
        $parentController->view->has_integration = $has_integration;

        $parentController->_helper->viewRenderer('cash-report-daily');
    }

     //generates cash report when first time loading this page
	public function cashReportDailyAction(){
        $link = '/cashier-reports/cash-report-daily/';
		$this->cash_report_daily($this, $link, 1);
	}

	//cash report upper level choosen
	//generates cash report when going level up on green arrow
	public function cashReportDailyUpLevelAction(){
        $link = '/cashier-reports/cash-report-daily-up-level/';
		$this->cash_report_daily($this, $link, 0);
	}

	//generates cash report through link on report
	//generates cash report on affiliate link click and going down the hierarchy
	public function cashReportDailyLinkAction(){
        $link = '/cashier-reports/cash-report-daily-link/';
		$this->cash_report_daily($this, $link, 1);
	}

    private function cash_report_min_daily($parentController, $link, $default_direction = 1){
        if($this->getRequest()->getParam('PREVIOUS_PAGE', false)){
			$this->goToReportsMenu();
		}
        if($this->getRequest()->getParam('MAX_REPORT', false)){
			$locale = Zend_Registry::get('lang');
			$redirector = new Zend_Controller_Action_Helper_Redirector();
			$redirector->gotoUrl($locale . '/cashier-reports/cash-report-daily');
		}
        $lang = Zend_Registry::get("lang");
        $baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
        $start_date = $parentController->getRequest()->getParam('REPORT_STARTDATE', null);
        $end_date = $parentController->getRequest()->getParam('REPORT_ENDDATE', null);
        $dateRes = $parentController->setDates($start_date, $end_date);
        $start_date = $dateRes['start_date'];
        $end_date = $dateRes['end_date'];
        // pagination isn't implemented
        $affiliate_id = $parentController->getRequest()->getParam('AFFILIATES', null);
        $direction = $parentController->getRequest()->getParam('LEVEL_DIRECTION', $default_direction);
        $currency = $parentController->getRequest()->getParam('CURRENCIES', ALL);

        require_once FORMS_DIR . DS . 'cashier_reports' . DS . 'ListCashReportMinForm.php';
        $form = new ListCashReportMinForm();

        //if up level received, ovo se ne koristi
        if($affiliate_id == '-1'){
            $affiliate_id = $parentController->getRequest()->getParam('AFFILIATE_NUMBER', -1);
            $direction = 0;
        }

        $baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
		$lang = Zend_Registry::get('lang');
		$form->setAction($baseUrl . '/'. $lang . $link . 'AFFILIATES/' . $affiliate_id);

        try{
            require_once MODELS_DIR . DS . 'CashReportModel.php';
            $arrData = CashReportModel::listCashReportDaily($this->session_id, $affiliate_id, $currency, $direction, $start_date, $end_date);
        }catch(Zend_Exception $ex){
            throw new Zend_Exception(CursorToArrayHelper::getExceptionTraceAsString($ex));
        }

        require_once MODELS_DIR . DS . 'AffiliatesModel.php';
        $affiliate_details = AffiliatesModel::getAffiliateDetails($parentController->session_id, $arrData['aff_id_out']);
        $affiliate_details = $affiliate_details['details'];

        $form->setAction($baseUrl . '/'. $lang . '/cashier-reports/cash-report-min-daily');
        $form->REPORT_STARTDATE->setValue($start_date);
        $form->REPORT_ENDDATE->setValue($end_date);
        $form->removeElement('PAGE');
        $form->removeElement('LIMIT');
        $form->AFFILIATES->clearMultiOptions();
        $form->AFFILIATES->addMultiOption('-10', $parentController->translate->_("SelectAffiliate"));
        $is_root = $arrData['is_root'];
        //if it is not first level affiliate then add up level into affiliates list
        //-1 is signal to go up level and with affiliate number not parentController -1 and with direction 0 (up)
        if($is_root == '0') {
            $form->AFFILIATES->addMultiOption('-1', 'up level');
        }
        if($is_root == '1') {
            $form->AFFILIATES->addMultiOption($arrData['aff_id_out'], $this->translate->_(ROOT_AFFILIATE));
        }
        //if current affiliate name is added then with his number and name and direction is 1 (down)
        else {
            $form->AFFILIATES->addMultiOption($arrData['aff_id_out'], $affiliate_details['user_name']);
        }
        foreach($arrData['cursor'] as $cur) {
            if ($cur['player'] != SUPER_ROLA_PLAYER) {
                $form->AFFILIATES->addMultiOption($cur['id'], $cur['name']);
            }
        }
        $form->CURRENCIES->setValue($currency);
        $form->AFFILIATE_NUMBER->setValue($arrData['aff_id_out']);
        $form->AFFILIATES->setValue($arrData['aff_id_out']);

        $has_integration = $arrData['has_integration'];

        $parentController->view->paginator = $arrData['cursor'];
        $parentController->view->affiliate_id = $arrData['aff_id_out'];
        $parentController->view->form = $form;
        $parentController->view->affiliate_name = $affiliate_details['user_name'];
        $parentController->view->is_root = $is_root;
        $parentController->view->has_integration = $has_integration;

        $parentController->_helper->viewRenderer('cash-report-min-daily');
    }

     //generates cash report when first time loading this page
	public function cashReportMinDailyAction(){
        $link = '/cashier-reports/cash-report-min-daily/';
		$this->cash_report_min_daily($this, $link, 1);
	}

	//cash report upper level choosen
	//generates cash report when going level up on green arrow
	public function cashReportMinDailyUpLevelAction(){
        $link = '/cashier-reports/cash-report-min-daily-up-level/';
		$this->cash_report_min_daily($this, $link, 0);
	}

	//generates cash report through link on report
	//generates cash report on affiliate link click and going down the hierarchy
	public function cashReportMinDailyLinkAction(){
        $link = '/cashier-reports/cash-report-min-daily-link/';
		$this->cash_report_min_daily($this, $link, 1);
	}

    public function credit_report($parentController, $link, $default_direction = 1)
    {
        if($this->getRequest()->getParam('PREVIOUS_PAGE', false)){
			$this->goToReportsMenu();
		}
        if($this->getRequest()->getParam('MIN_REPORT', false)){
			$locale = Zend_Registry::get('lang');
			$redirector = new Zend_Controller_Action_Helper_Redirector();
			$redirector->gotoUrl($locale . '/cashier-reports/credit-report-min');
		}
        require_once FORMS_DIR . DS . 'cashier_reports' . DS . 'ListCreditReportFullForm.php';
        $form = new ListCreditReportFullForm();
        $form->removeElement('PAGE');
        $form->removeElement('LIMIT');
        $form->removeElement('GAME_TYPE');
        $lang = Zend_Registry::get("lang");
        $baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
        $form->setAction($baseUrl . '/'. $lang . '/cashier-reports/credit-report');

        $start_date = $parentController->getRequest()->getParam('REPORT_STARTDATE', null);
        $end_date = $parentController->getRequest()->getParam('REPORT_ENDDATE', null);
        $dateRes = $parentController->setDates($start_date, $end_date);
        $start_date = $dateRes['start_date'];
        $end_date = $dateRes['end_date'];
        $form->REPORT_STARTDATE->setValue($start_date);
        $form->REPORT_ENDDATE->setValue($end_date);
        $perPage = 1000000;
        $pageNo = 1;
        $affiliate_id = $parentController->getRequest()->getParam('AFFILIATES', null);
        $direction = $parentController->getRequest()->getParam('LEVEL_DIRECTION', $default_direction);
        $currency_filter = $parentController->getRequest()->getParam('CURRENCIES', ALL);
        $game_type = $parentController->getRequest()->getParam('GAME_TYPE', ALL);
        //if up level received
        if ($affiliate_id == '-1') {
            $affiliate_id = $parentController->getRequest()->getParam('AFFILIATE_NUMBER', -1);
            $direction = 0;
        }

        $baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
		$lang = Zend_Registry::get('lang');
		$form->setAction($baseUrl . '/'. $lang . $link . 'AFFILIATES/' . $affiliate_id);

        require_once MODELS_DIR . DS . 'CashReportModel.php';
        try {
            $arrData = CashReportModel::listCreditReport($parentController->session_id, $affiliate_id, $currency_filter, $direction, $start_date, $end_date, $pageNo, $perPage, $game_type);
        } catch (Zend_Exception $ex) {
            throw new Zend_Exception(CursorToArrayHelper::getExceptionTraceAsString($ex));
        }
        $parentController->view->paginator = $arrData["table"];
        $total_items = $arrData["info"][0]['cnt'];
        $total_pages = ceil($total_items / $perPage);
        $parentController->view->affiliate_id = $arrData["aff_id_out"];
        require_once MODELS_DIR . DS . 'AffiliatesModel.php';
        $affiliate_details = AffiliatesModel::getAffiliateDetails($parentController->session_id, $arrData["aff_id_out"]);
        $affiliate_details = $affiliate_details['details'];
        $parentController->view->affiliate_name = $affiliate_details['user_name'];
        $form->AFFILIATES->clearMultiOptions();
        $form->AFFILIATES->addMultiOption('-10', $parentController->translate->_("SelectAffiliate"));
        $is_root = $arrData["is_root"];
        //if it is not first level affiliate then add up level into affiliates list
        //-1 is signal to go up level and with affiliate number not parentController -1 and with direction 0 (up)
        if ($is_root == '0') {
            $form->AFFILIATES->addMultiOption('-1', $arrData["info"][0]['super_role_for']);
        }
        if ($is_root == '1') {
            $form->AFFILIATES->addMultiOption($arrData["aff_id_out"], $this->translate->_(ROOT_AFFILIATE));
        } //if current affiliate name is added then with his number and name and direction is 1 (down)
        else {
            $form->AFFILIATES->addMultiOption($arrData["aff_id_out"], $affiliate_details['user_name']);
        }
        foreach ($arrData["table"] as $cur) {
            if ($cur['player'] != SUPER_ROLA_PLAYER) {
                $form->AFFILIATES->addMultiOption($cur['subject_id_for'], $cur['name_from']);
            }
        }
		$form->CURRENCIES->setValue($currency_filter);
		//flag if it is root level
		$parentController->view->is_root = $is_root;
		$form->AFFILIATES->setValue($arrData["aff_id_out"]);
		$form->AFFILIATE_NUMBER->setValue($arrData["aff_id_out"]);
       //$form->GAME_TYPE->setValue($game_type);
		$parentController->view->form = $form;

		$parentController->view->total = CashReportModel::listCreditReportTotal($parentController->session_id, $currency_filter, $affiliate_id, $direction, $start_date, $end_date);
		//$parentController->view->totalApt = CashReportModel::listCreditReportTotalApt($parentController->session_id, $currency_filter, $affiliate_id, $direction, $start_date, $end_date);

        $parentController->_helper->viewRenderer('credit-report');
	}

     //generates cash report when first time loading this page
	public function creditReportAction(){
        $link = '/cashier-reports/credit-report/';
		$this->credit_report($this, $link, 1);
	}

	//cash report upper level choosen
	//generates cash report when going level up on green arrow
	public function creditReportUpLevelAction(){
        $link = '/cashier-reports/credit-report-up-level/';
		$this->credit_report($this, $link, 0);
	}

	//generates cash report through link on report
	//generates cash report on affiliate link click and going down the hierarchy
	public function creditReportLinkAction(){
        $link = '/cashier-reports/credit-report-link/';
		$this->credit_report($this, $link, 1);
	}

    public function credit_report_min($parentController, $link, $default_direction = 1)
    {
        if($this->getRequest()->getParam('PREVIOUS_PAGE', false)){
			$this->goToReportsMenu();
		}
        if($this->getRequest()->getParam('MAX_REPORT', false)){
			$locale = Zend_Registry::get('lang');
			$redirector = new Zend_Controller_Action_Helper_Redirector();
			$redirector->gotoUrl($locale . '/cashier-reports/credit-report');
		}
        require_once FORMS_DIR . DS . 'cashier_reports' . DS . 'ListCreditReportMinForm.php';
        $form = new ListCreditReportMinForm();
        $form->removeElement('PAGE');
        $form->removeElement('LIMIT');
        $form->removeElement('GAME_TYPE');
        $lang = Zend_Registry::get("lang");
        $baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
        $form->setAction($baseUrl . '/'. $lang . '/cashier-reports/credit-report-min');

        $start_date = $parentController->getRequest()->getParam('REPORT_STARTDATE', null);
        $end_date = $parentController->getRequest()->getParam('REPORT_ENDDATE', null);
        $dateRes = $parentController->setDates($start_date, $end_date);
        $start_date = $dateRes['start_date'];
        $end_date = $dateRes['end_date'];
        $form->REPORT_STARTDATE->setValue($start_date);
        $form->REPORT_ENDDATE->setValue($end_date);
        $perPage = 1000000;
        $pageNo = 1;
        $affiliate_id = $parentController->getRequest()->getParam('AFFILIATES', null);
        $direction = $parentController->getRequest()->getParam('LEVEL_DIRECTION', $default_direction);
        $currency_filter = $parentController->getRequest()->getParam('CURRENCIES', ALL);
        $game_type = $parentController->getRequest()->getParam('GAME_TYPE', ALL);
        //if up level received
        if ($affiliate_id == '-1') {
            $affiliate_id = $parentController->getRequest()->getParam('AFFILIATE_NUMBER', -1);
            $direction = 0;
        }

        $baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
		$lang = Zend_Registry::get('lang');
		$form->setAction($baseUrl . '/'. $lang . $link . 'AFFILIATES/' . $affiliate_id);

        require_once MODELS_DIR . DS . 'CashReportModel.php';
        try {
            $arrData = CashReportModel::listCreditReport($parentController->session_id, $affiliate_id, $currency_filter, $direction, $start_date, $end_date, $pageNo, $perPage, $game_type);
        } catch (Zend_Exception $ex) {
            throw new Zend_Exception(CursorToArrayHelper::getExceptionTraceAsString($ex));
        }
        $parentController->view->paginator = $arrData["table"];
        $total_items = $arrData["info"][0]['cnt'];
        $total_pages = ceil($total_items / $perPage);
        $parentController->view->affiliate_id = $arrData["aff_id_out"];
        require_once MODELS_DIR . DS . 'AffiliatesModel.php';
        $affiliate_details = AffiliatesModel::getAffiliateDetails($parentController->session_id, $arrData["aff_id_out"]);
        $affiliate_details = $affiliate_details['details'];
        $parentController->view->affiliate_name = $affiliate_details['user_name'];
        $form->AFFILIATES->clearMultiOptions();
        $form->AFFILIATES->addMultiOption('-10', $parentController->translate->_("SelectAffiliate"));
        $is_root = $arrData["is_root"];
        //if it is not first level affiliate then add up level into affiliates list
        //-1 is signal to go up level and with affiliate number not parentController -1 and with direction 0 (up)
        if ($is_root == '0') {
            $form->AFFILIATES->addMultiOption('-1', $arrData["info"][0]['super_role_for']);
        }
        if ($is_root == '1') {
            $form->AFFILIATES->addMultiOption($arrData["aff_id_out"], $this->translate->_(ROOT_AFFILIATE));
        } //if current affiliate name is added then with his number and name and direction is 1 (down)
        else {
            $form->AFFILIATES->addMultiOption($arrData["aff_id_out"], $affiliate_details['user_name']);
        }
        foreach ($arrData["table"] as $cur) {
            if ($cur['player'] != SUPER_ROLA_PLAYER) {
                $form->AFFILIATES->addMultiOption($cur['subject_id_for'], $cur['name_from']);
            }
        }
		$form->CURRENCIES->setValue($currency_filter);
		//flag if it is root level
		$parentController->view->is_root = $is_root;
		$form->AFFILIATES->setValue($arrData["aff_id_out"]);
		$form->AFFILIATE_NUMBER->setValue($arrData["aff_id_out"]);
        //$form->GAME_TYPE->setValue($game_type);
		$parentController->view->form = $form;

        $parentController->view->total = CashReportModel::listCreditReportTotal($parentController->session_id, $currency_filter, $affiliate_id, $direction, $start_date, $end_date);
		//$parentController->view->totalApt = CashReportModel::listCreditReportTotalApt($parentController->session_id, $currency_filter, $affiliate_id, $direction, $start_date, $end_date);

        $parentController->_helper->viewRenderer('credit-report-min');
	}

     //generates cash report when first time loading this page
	public function creditReportMinAction(){
        $link = '/cashier-reports/credit-report-min/';
		$this->credit_report_min($this, $link, 1);
	}

	//cash report upper level choosen
	//generates cash report when going level up on green arrow
	public function creditReportMinUpLevelAction(){
        $link = '/cashier-reports/credit-report-min-up-level/';
		$this->credit_report_min($this, $link, 0);
	}

	//generates cash report through link on report
	//generates cash report on affiliate link click and going down the hierarchy
	public function creditReportMinLinkAction(){
        $link = '/cashier-reports/credit-report-min-link/';
		$this->credit_report_min($this, $link, 1);
	}

    public function credit_report_daily($parentController, $link, $default_direction = 1)
    {
        if($this->getRequest()->getParam('PREVIOUS_PAGE', false)){
			$this->goToReportsMenu();
		}
        if($this->getRequest()->getParam('MIN_REPORT', false)){
			$locale = Zend_Registry::get('lang');
			$redirector = new Zend_Controller_Action_Helper_Redirector();
			$redirector->gotoUrl($locale . '/cashier-reports/credit-report-min-daily');
		}
        require_once FORMS_DIR . DS . 'cashier_reports' . DS . 'ListCreditReportFullForm.php';
        $form = new ListCreditReportFullForm();
        $form->removeElement('PAGE');
        $form->removeElement('LIMIT');
        $form->removeElement('GAME_TYPE');
        $lang = Zend_Registry::get("lang");
        $baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
        $form->setAction($baseUrl . '/'. $lang . '/cashier-reports/credit-report-daily');

        $start_date = $parentController->getRequest()->getParam('REPORT_STARTDATE', null);
        $end_date = $parentController->getRequest()->getParam('REPORT_ENDDATE', null);
        $dateRes = $parentController->setDates($start_date, $end_date);
        $start_date = $dateRes['start_date'];
        $end_date = $dateRes['end_date'];
        $form->REPORT_STARTDATE->setValue($start_date);
        $form->REPORT_ENDDATE->setValue($end_date);
        $perPage = 1000000;
        $pageNo = 1;
        $affiliate_id = $parentController->getRequest()->getParam('AFFILIATES', null);
        $direction = $parentController->getRequest()->getParam('LEVEL_DIRECTION', $default_direction);
        $currency_filter = $parentController->getRequest()->getParam('CURRENCIES', ALL);
        $game_type = $parentController->getRequest()->getParam('GAME_TYPE', ALL);
        //if up level received
        if ($affiliate_id == '-1') {
            $affiliate_id = $parentController->getRequest()->getParam('AFFILIATE_NUMBER', -1);
            $direction = 0;
        }

        $baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
		$lang = Zend_Registry::get('lang');
		$form->setAction($baseUrl . '/'. $lang . $link . 'AFFILIATES/' . $affiliate_id);

        try {
            require_once MODELS_DIR . DS . 'CashReportModel.php';
            $arrData = CashReportModel::listCreditReportDaily($parentController->session_id, $affiliate_id, $currency_filter, $direction, $start_date, $end_date, $pageNo, $perPage);
        } catch (Zend_Exception $ex) {
            throw new Zend_Exception(CursorToArrayHelper::getExceptionTraceAsString($ex));
        }
        $parentController->view->paginator = $arrData["table"];
        $total_items = $arrData["info"][0]['cnt'];
        $total_pages = ceil($total_items / $perPage);
        $parentController->view->affiliate_id = $arrData["aff_id_out"];
        require_once MODELS_DIR . DS . 'AffiliatesModel.php';
        $affiliate_details = AffiliatesModel::getAffiliateDetails($parentController->session_id, $arrData["aff_id_out"]);
        $affiliate_details = $affiliate_details['details'];
        $parentController->view->affiliate_name = $affiliate_details['user_name'];
        $form->AFFILIATES->clearMultiOptions();
        $form->AFFILIATES->addMultiOption('-10', $parentController->translate->_("SelectAffiliate"));
        $is_root = $arrData["is_root"];
        //if it is not first level affiliate then add up level into affiliates list
        //-1 is signal to go up level and with affiliate number not parentController -1 and with direction 0 (up)
        if ($is_root == '0') {
            $form->AFFILIATES->addMultiOption('-1', $arrData["info"][0]['super_role_for']);
        }
        if ($is_root == '1') {
            $form->AFFILIATES->addMultiOption($arrData["aff_id_out"], $this->translate->_(ROOT_AFFILIATE));
        } //if current affiliate name is added then with his number and name and direction is 1 (down)
        else {
            $form->AFFILIATES->addMultiOption($arrData["aff_id_out"], $affiliate_details['user_name']);
        }
        foreach ($arrData["table"] as $cur) {
            if ($cur['player'] != SUPER_ROLA_PLAYER) {
                $form->AFFILIATES->addMultiOption($cur['subject_id_for'], $cur['name_from']);
            }
        }
		$form->CURRENCIES->setValue($currency_filter);
		//flag if it is root level
		$parentController->view->is_root = $is_root;
		$form->AFFILIATES->setValue($arrData["aff_id_out"]);
		$form->AFFILIATE_NUMBER->setValue($arrData["aff_id_out"]);
		$parentController->view->form = $form;

        $parentController->_helper->viewRenderer('credit-report-daily');
	}

     //generates cash report when first time loading this page
	public function creditReportDailyAction(){
        $link = '/cashier-reports/credit-report-daily/';
		$this->credit_report_daily($this, $link, 1);
	}

	//cash report upper level choosen
	//generates cash report when going level up on green arrow
	public function creditReportDailyUpLevelAction(){
        $link = '/cashier-reports/credit-report-daily-up-level/';
		$this->credit_report_daily($this, $link, 0);
	}

	//generates cash report through link on report
	//generates cash report on affiliate link click and going down the hierarchy
	public function creditReportDailyLinkAction(){
        $link = '/cashier-reports/credit-report-daily-link/';
		$this->credit_report_daily($this, $link, 1);
	}

    public function credit_report_min_daily($parentController, $link, $default_direction = 1)
    {
        if($this->getRequest()->getParam('PREVIOUS_PAGE', false)){
			$this->goToReportsMenu();
		}
        if($this->getRequest()->getParam('MAX_REPORT', false)){
			$locale = Zend_Registry::get('lang');
			$redirector = new Zend_Controller_Action_Helper_Redirector();
			$redirector->gotoUrl($locale . '/cashier-reports/credit-report-daily');
		}
        require_once FORMS_DIR . DS . 'cashier_reports' . DS . 'ListCreditReportMinForm.php';
        $form = new ListCreditReportMinForm();
        $form->removeElement('PAGE');
        $form->removeElement('LIMIT');
        $form->removeElement('GAME_TYPE');
        $lang = Zend_Registry::get("lang");
        $baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
        $form->setAction($baseUrl . '/'. $lang . '/cashier-reports/credit-report-min-daily');

        $start_date = $parentController->getRequest()->getParam('REPORT_STARTDATE', null);
        $end_date = $parentController->getRequest()->getParam('REPORT_ENDDATE', null);
        $dateRes = $parentController->setDates($start_date, $end_date);
        $start_date = $dateRes['start_date'];
        $end_date = $dateRes['end_date'];
        $form->REPORT_STARTDATE->setValue($start_date);
        $form->REPORT_ENDDATE->setValue($end_date);
        $perPage = 1000000;
        $pageNo = 1;
        $affiliate_id = $parentController->getRequest()->getParam('AFFILIATES', null);
        $direction = $parentController->getRequest()->getParam('LEVEL_DIRECTION', $default_direction);
        $currency_filter = $parentController->getRequest()->getParam('CURRENCIES', ALL);
        $game_type = $parentController->getRequest()->getParam('GAME_TYPE', ALL);
        //if up level received
        if ($affiliate_id == '-1') {
            $affiliate_id = $parentController->getRequest()->getParam('AFFILIATE_NUMBER', -1);
            $direction = 0;
        }

        $baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
		$lang = Zend_Registry::get('lang');
		$form->setAction($baseUrl . '/'. $lang . $link . 'AFFILIATES/' . $affiliate_id);

        try {
            require_once MODELS_DIR . DS . 'CashReportModel.php';
            $arrData = CashReportModel::listCreditReportDaily($parentController->session_id, $affiliate_id, $currency_filter, $direction, $start_date, $end_date, $pageNo, $perPage);
        } catch (Zend_Exception $ex) {
            throw new Zend_Exception(CursorToArrayHelper::getExceptionTraceAsString($ex));
        }
        $parentController->view->paginator = $arrData["table"];
        $total_items = $arrData["info"][0]['cnt'];
        $total_pages = ceil($total_items / $perPage);
        $parentController->view->affiliate_id = $arrData["aff_id_out"];
        require_once MODELS_DIR . DS . 'AffiliatesModel.php';
        $affiliate_details = AffiliatesModel::getAffiliateDetails($parentController->session_id, $arrData["aff_id_out"]);
        $affiliate_details = $affiliate_details['details'];
        $parentController->view->affiliate_name = $affiliate_details['user_name'];
        $form->AFFILIATES->clearMultiOptions();
        $form->AFFILIATES->addMultiOption('-10', $parentController->translate->_("SelectAffiliate"));
        $is_root = $arrData["is_root"];
        //if it is not first level affiliate then add up level into affiliates list
        //-1 is signal to go up level and with affiliate number not parentController -1 and with direction 0 (up)
        if ($is_root == '0') {
            $form->AFFILIATES->addMultiOption('-1', $arrData["info"][0]['super_role_for']);
        }
        if ($is_root == '1') {
            $form->AFFILIATES->addMultiOption($arrData["aff_id_out"], $this->translate->_(ROOT_AFFILIATE));
        } //if current affiliate name is added then with his number and name and direction is 1 (down)
        else {
            $form->AFFILIATES->addMultiOption($arrData["aff_id_out"], $affiliate_details['user_name']);
        }
        foreach ($arrData["table"] as $cur) {
            if ($cur['player'] != SUPER_ROLA_PLAYER) {
                $form->AFFILIATES->addMultiOption($cur['subject_id_for'], $cur['name_from']);
            }
        }
		$form->CURRENCIES->setValue($currency_filter);
		//flag if it is root level
		$parentController->view->is_root = $is_root;
		$form->AFFILIATES->setValue($arrData["aff_id_out"]);
		$form->AFFILIATE_NUMBER->setValue($arrData["aff_id_out"]);
		$parentController->view->form = $form;

        $parentController->_helper->viewRenderer('credit-report-min-daily');
	}

     //generates cash report when first time loading this page
	public function creditReportMinDailyAction(){
        $link = '/cashier-reports/credit-report-min-daily/';
		$this->credit_report_min_daily($this, $link, 1);
	}

	//cash report upper level choosen
	//generates cash report when going level up on green arrow
	public function creditReportMinDailyUpLevelAction(){
        $link = '/cashier-reports/credit-report-min-daily-up-level/';
		$this->credit_report_min_daily($this, $link, 0);
	}

	//generates cash report through link on report
	//generates cash report on affiliate link click and going down the hierarchy
	public function creditReportMinDailyLinkAction(){
        $link = '/cashier-reports/credit-report-min-daily-link/';
		$this->credit_report_min_daily($this, $link, 1);
	}
}
