<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'NumberHelper.php';
require_once HELPERS_DIR . DS . 'RoleHelper.php';
require_once HELPERS_DIR . DS . 'mobile_detect' . DS . 'Mobile_Detect.php';
class PlayersController extends Zend_Controller_Action{
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
    private $translate = null;
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
			if(isset($authInfo)){
                $this->session_id = $authInfo->session_id;
            }
		}
		//setup number of pages items per report from database or set default 200
		require_once MODELS_DIR . DS . 'BoSetupModel.php';
        $defaultPerPage = BoSetupModel::numberOfItemsPerPage($this->session_id);
		$this->defaultPerPage = $defaultPerPage["lines_for_page"];
		if($this->defaultPerPage > 200 && $this->limitReports200 == true)
			$this->defaultPerPage = 200;
		if(!isset($this->defaultPerPage))
			$this->defaultPerPage = 200;
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
			$message = "User with username {$username} and role {$rola} and super-role {$superrola} and backoffice session id = {$session_id} tried to visit: <br /> Date and time: {$date_now} <br /> From manually entered URL address in browser <br /> To page {$dest_url} <br /> AffiliatesController";
		}else{
			$message = "User with username {$username} and role {$rola} and super-role {$superrola} and backoffice session id = {$session_id} tried to visit: <br /> Date and time: {$date_now} <br /> From page {$origin_url} <br /> To page {$dest_url} <br /> AffiliatesController";
		}

		ErrorMailHelper::writeError($message, $message);
	}

	//set permissions for roles for entire transfer credit verticale
	private function setRolePermissions(){
		$rola = $_SESSION['auth_space']['session']['subject_type_name']; //rola
        /*if(!in_array($rola, array(ROLA_AD_CASHIER, ROLA_AD_CASHIER_PAYOUT, SUPER_ROLA_MASTER_CASINO)))
        {
            $this->logVisitedPageError();
            $url = $_SERVER['HTTP_REFERER'];
            $this->redirect($url);
        }
        */
	}

    private function writeFirebugInfo(){
        $bo_session_id = $_SESSION['auth_space']['session']['session_out'];
        $username = $_SESSION['auth_space']['session']['username'];
        $super_role = $_SESSION['auth_space']['session']['subject_super_type_name'];
        $role = $_SESSION['auth_space']['session']['subject_type_name'];
        $affiliate_id = $_SESSION['auth_space']['session']['affiliate_id'];
        $currency = $_SESSION['auth_space']['session']['currency'];
        //$firebug_message = "[BO Session ID: {$bo_session_id}] [Username: {$username}] [SuperRole: {$super_role}] [Role: {$role}] [Affiliate ID: {$affiliate_id}] [Currency: {$currency}]";
        $firebug_message = print_r($_SESSION['auth_space']['session'], true);
        ErrorMailHelper::writeToFirebugInfo($firebug_message);
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
            $res = $res["status"];
			if($res == NO)$this->forward('terminate', 'auth');
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
		unset($modelDateTime);
		$date2 = new Zend_Date();
		$now_in_month = $date2->now();
		if(!isset($startdate) && !isset($enddate)){
			$rola = $_SESSION['auth_space']['session']['subject_type_name'];
			if($rola == ROLA_AD_COLLECTOR){
				if(!isset($_SESSION['auth_space']['session']['last_time_collect'])){
					//if collector had no collect cash he can change startdate and see from first in current month
					$startdate = date('d-M-Y', (strtotime($fdm) == false) ? time() : strtotime($fdm));
					if(!isset($startdate)) {
                        $startdate = date('d-M-Y', (strtotime($fdm) == false) ? time() : strtotime($fdm));
                    }
					else {
                        $startdate = $this->session_space->startdate;
                    }
				}else{
					$startdate = date('d-M-Y', strtotime($_SESSION['auth_space']['session']['last_time_collect']));
				}
				if(!isset($enddate)) {
                    $enddate = $this->session_space->enddate;
                }
				else {
                    $enddate = $now_in_month->toString('dd-MMM-yyyy');
                }
				$this->session_space->startdate = date('d-M-Y', (strtotime($startdate) == false) ? time() : strtotime($startdate));
				$this->session_space->enddate = date('d-M-Y', (strtotime($enddate) == false) ? time() : strtotime($enddate));
			}else{
				//if role is not Ad / Collector
				if(!(!isset($startdate) && !isset($enddate))){
					//if date is set through form post
					if(!isset($enddate)) {
                        $enddate = $now_in_month->toString("dd-MMM-yyyy");
                    }
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
    /**
     * @throws Zend_Exception
     */
	public function listPlayersAction(){
        if($this->getRequest()->getParam('PREVIOUS_PAGE', false)){
			$locale = Zend_Registry::get('lang');
			$redirector = new Zend_Controller_Action_Helper_Redirector();
			$redirector->gotoUrl($locale . '/mobile-menu/players');
		}
        require_once MODELS_DIR . DS . 'PlayersModel.php';
		require_once FORMS_DIR . DS . 'players' . DS . 'ListPlayersForm.php';
		$form = new ListPlayersForm();
		$baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
		$lang = Zend_Registry::get('lang');
		$form->setAction($baseUrl . '/' . $lang . '/players/list-players');
		$filter_by = $this->getRequest()->getParam('FILTER_BY', 'All');
        $filter_by_status = $this->getRequest()->getParam('FILTER_BY_STATUS', NO);
		$pageNo = $this->getRequest()->getParam('PAGE', 1);
		if(is_numeric($pageNo) && $pageNo>1) {
            $pageNo = $this->getRequest()->getParam('PAGE', 1);
        }
		else {
            $pageNo = 1;
        }
		$pageNo = max($pageNo, 1);
		$perPage = $this->getRequest()->getParam('LIMIT', null);
		if(!isset($perPage)) {
            $perPage = $this->defaultPerPage;
        }
		else {
            $this->session_space->limitPerPage = $perPage;
        }
		$perPage = $this->session_space->limitPerPage;
		$total_pages = 1;
		$arrData = array();
		if($pageNo == 1){
			if($filter_by == 'All') {
                $arrData = PlayersModel::getPlayers($this->session_id, $pageNo, $perPage, $filter_by_status);
            }
			if($filter_by == 'Direct') {
                $arrData = PlayersModel::listDirectPlayers($this->session_id, 0, $pageNo, $perPage, $filter_by_status);
            }
			$total_items = $arrData["info"][0]['cnt'];
			$total_pages = ceil($total_items / $perPage);
		}else{
			if($pageNo >= $total_pages){
				if($filter_by == 'All') {
                    $arrData = PlayersModel::getPlayers($this->session_id, $pageNo, $perPage, $filter_by_status);
                }
				if($filter_by == 'Direct') {
                    $arrData = PlayersModel::listDirectPlayers($this->session_id, 0, $pageNo, $perPage, $filter_by_status);
                }
				$total_items = $arrData["info"][0]['cnt'];
				$total_pages = ceil($total_items / $perPage);
			}
		}
		if(count($arrData["table"]) == 0){
			$pageNo = 1;
			if($filter_by == 'All') {
                $arrData = PlayersModel::getPlayers($this->session_id, $pageNo, $perPage, $filter_by_status);
            }
			if($filter_by == 'Direct') {
                $arrData = PlayersModel::listDirectPlayers($this->session_id, 0, $pageNo, $perPage, $filter_by_status);
            }
			$total_items = $arrData["info"][0]['cnt'];
			$total_pages = ceil($total_items / $perPage);
		}
		$this->view->paginator = $arrData["table"];
		if($total_pages > 1){
			for($i=1;$i<=$total_pages; $i++) {
                $form->PAGE->addMultiOption($i, $i);
            }
			$form->PAGE->setValue($pageNo);
		}else {
            $form->removeElement('PAGE');
        }
		$form->LIMIT->setValue($perPage);
		$form->FILTER_BY->setValue($filter_by);
		$form->FILTER_BY_STATUS->setValue($filter_by_status);
		$this->view->form = $form;
	}

    /**
     *  action to search players in database
     */
    public function searchAction(){
        if($this->getRequest()->getParam('PREVIOUS_PAGE', false)){
			$locale = Zend_Registry::get('lang');
			$redirector = new Zend_Controller_Action_Helper_Redirector();
			$redirector->gotoUrl($locale . '/mobile-menu/players');
		}
        require_once FORMS_DIR . DS . 'players' . DS . 'SearchPlayersForm.php';
		$form = new SearchPlayersForm();
		$username = $this->_getParam('USERNAME', null);
		$first_name = $this->_getParam('FIRST_NAME', null);
		$last_name = $this->_getParam('LAST_NAME', null);
		$city = $this->_getParam('CITY', null);
		$country = $this->_getParam('COUNTRY');
		$parent_affiliate = $this->_getParam('PARENT_AFFILIATE');
		$currency = $this->_getParam('CURRENCY', ALL);
		$banned = $this->_getParam('BANNED', NO);

		$pageNo = $this->_getParam('PAGE',1);
		if(is_numeric($pageNo) && $pageNo>1) {
            $pageNo = $this->_getParam('PAGE', 1);
        }
		else {
            $pageNo = 1;
        }
		$pageNo = max($pageNo, 1);
		$perPage = $this->_getParam('LIMIT',null);
		if(!isset($perPage)) {
            $perPage = $this->defaultPerPage;
        }
		else {
            $this->session_space->limitPerPage = $perPage;
        }
		$perPage = $this->session_space->limitPerPage;
		require_once MODELS_DIR . DS . 'PlayersModel.php';
		$total_pages = 1;
		$arrData = array();
		if($pageNo == 1){
			$arrData = PlayersModel::search($this->session_id, $pageNo, $perPage, 1, "asc", $username, $first_name, $last_name, $city, $country, $parent_affiliate, $currency, $banned);
			$total_items = $arrData["info"][0]['cnt'];
			$total_pages = ceil($total_items / $perPage);
		}else{
			if($pageNo >= $total_pages){
				$arrData = PlayersModel::search($this->session_id, $pageNo, $perPage, 1, "asc", $username, $first_name, $last_name, $city, $country, $parent_affiliate, $currency, $banned);
				$total_items = $arrData["info"][0]['cnt'];
				$total_pages = ceil($total_items / $perPage);
			}
		}
		if(count($arrData["table"]) == 0){
			$pageNo = 1;
			$arrData = PlayersModel::search($this->session_id, $pageNo, $perPage, 1, "asc", $username, $first_name, $last_name, $city, $country, $parent_affiliate, $currency, $banned);
			$total_items = $arrData["info"][0]['cnt'];
			$total_pages = ceil($total_items / $perPage);
		}
		$this->view->paginator = $arrData["table"];
		if($total_pages > 1){
			for($i=1;$i<=$total_pages; $i++){
                $form->PAGE->addMultiOption($i, $i);
            }
			$form->PAGE->setValue($pageNo);
		}else {
            $form->removeElement('PAGE');
        }

		$form->LIMIT->setValue($perPage);
		$form->USERNAME->setValue($username);
		$form->FIRST_NAME->setValue($first_name);
		$form->LAST_NAME->setValue($last_name);
		$form->CITY->setValue($city);
		$form->COUNTRY->setValue($country);
		$form->PARENT_AFFILIATE->setValue($parent_affiliate);
		$form->CURRENCY->setValue($currency);
		$form->BANNED->setValue($banned);

		$this->view->form = $form;
    }

    //shows details from player
	public function detailsAction(){
		$player_id = $this->_getParam('player_id',0);
		if($player_id == 0 || is_numeric($player_id) == false || !isset($player_id)){
			$locale = Zend_Registry::get('lang');
			$this->redirect($locale . '/players/list-players');
		}
        require_once MODELS_DIR . DS . 'PlayersModel.php';
		$player = PlayersModel::getPlayerDetails($this->session_id, $player_id);
        $this->view->player_id = $player_id;
        $player['details']['player_id'] = $player_id;
        $this->view->details = $player['details'];


        $start_date = $this->getRequest()->getParam('START_DATE', null);
		$end_date = $this->getRequest()->getParam('END_DATE', null);
        $dateRes = $this->setDates($start_date, $end_date);
        $start_date = $dateRes['start_date'];
        $end_date = $dateRes['end_date'];
        require_once FORMS_DIR . DS . 'affiliates' . DS . 'SummaryReportForm.php';
        $form = new SummaryReportForm();
        $form->START_DATE->setValue($start_date);
        $form->END_DATE->setValue($end_date);
        $this->view->formSummaryReport = $form;

        require_once MODELS_DIR . DS . 'PlayersReportModel.php';
        $player_summary = PlayersReportModel::listPlayerHistoryTotal($player['details']['user_name'], $start_date, $end_date);
        $player_summary = $player_summary['cursor']->current();

        $report =
            array(
                array(
                    "name"=>$this->translate->_("Total Games"),
                    "value"=>NumberHelper::format_integer($player_summary['total_games']),
                ),
                array(
                    "name"=>$this->translate->_("Cash IN"),
                    "value"=>NumberHelper::format_double($player_summary['cash_in']) . " " . $player['details']['currency'],
                ),
                array(
                    "name"=>$this->translate->_("Cash OUT"),
                    "value"=>NumberHelper::format_double($player_summary['cash_out']) . " " . $player['details']['currency'],
                ),
                array(
                    "name"=>$this->translate->_("Game IN"),
                    "value"=>NumberHelper::format_double($player_summary['game_in']) . " " . $player['details']['currency'],
                ),
                array(
                    "name"=>$this->translate->_("Game OUT"),
                    "value"=>NumberHelper::format_double($player_summary['game_out']) . " " . $player['details']['currency'],
                ),
                array(
                    "name"=>$this->translate->_("Netto"),
                    "value"=>NumberHelper::format_double($player_summary['game_win']) . " " . $player['details']['currency'],
                ),
                array(
                    "name"=>$this->translate->_("Payback"),
                    "value"=>NumberHelper::format_double($player_summary['game_payback']) . " %",
                ),
            );
        $this->view->summary_report = $report;
	}

    //ban or unban player
    /**
     * @throws Exception
     * @throws Zend_Db_Adapter_Oracle_Exception
     * @throws Zend_Db_Statement_Oracle_Exception
     * @throws Zend_Exception
     */
     public function playerStatusAction(){
		$player_id = $this->getRequest()->getParam('player_id', 0);
		if($player_id == 0 || is_numeric($player_id) == false || !isset($player_id) || $this->getRequest()->getParam('CANCEL', false)){
			$locale = Zend_Registry::get('lang');
			$redirector = new Zend_Controller_Action_Helper_Redirector();
			$redirector->gotoUrl($locale . '/players/list-players');
		}
        require_once MODELS_DIR . DS . 'PlayersModel.php';
        $playerInfo = PlayersModel::getPlayerDetails($this->session_id, $player_id);
        $playerInfo = $playerInfo["details"];
        require_once FORMS_DIR . DS . 'players' . DS . 'PlayerBanUnbanForm.php';
		$form = new PlayerBanUnbanForm();
		$form->NAME->setAttrib('disabled', true);
		$form->NAME->setValue($playerInfo['user_name']);
		$form->NAME->clearValidators();
		$form->NAME->setRequired(false);
		$this->view->form = $form;
		$this->view->username = $playerInfo['user_name'];
        $this->view->player_id = $player_id;
        $form->STATUS->setValue($playerInfo['banned']);
		if ($this->getRequest()->isPost()) {
			$formData = $this->getRequest()->getPost();
			if ($form->isValidPartial($formData)) {
                require_once MODELS_DIR . DS . 'AdministratorModel.php';
                if($this->getRequest()->getParam('BAN', false)){
                    $ban_status = YES;
                    AdministratorModel::manageUser($this->session_id, UPDATE, null, null, null, null, null, null, null, null, $ban_status, null, null, null, null, null, null, null, $player_id, null, null, null, null, null);
                    $locale = Zend_Registry::get('lang');
			        $redirector = new Zend_Controller_Action_Helper_Redirector();
			        $redirector->gotoUrl("{$locale}/players/player-status/player_id/{$player_id}");
                }
                if($this->getRequest()->getParam('UNBAN', false)) {
                    $ban_status = NO;
                    AdministratorModel::manageUser($this->session_id, UPDATE, null, null, null, null, null, null, null, null, $ban_status, null, null, null, null, null, null, null, $player_id, null, null, null, null, null);
                    $locale = Zend_Registry::get('lang');
			        $redirector = new Zend_Controller_Action_Helper_Redirector();
			        $redirector->gotoUrl("{$locale}/players/player-status/player_id/{$player_id}");
                }
			}else{
				$form->NAME->setValue($playerInfo['user_name']);
				$form->NAME->clearValidators();
				$form->NAME->setRequired(false);
			}
		}
	}

    /**
     * @throws Zend_Exception
     */
    public function resetPasswordAction(){
		$player_id = $this->getRequest()->getParam('player_id', 0);
		if($player_id == 0 || is_numeric($player_id) == false || !isset($player_id) || $this->getRequest()->getParam('CANCEL', false)){
			$locale = Zend_Registry::get('lang');
			$redirector = new Zend_Controller_Action_Helper_Redirector();
			$redirector->gotoUrl($locale . '/players/list-players');
		}
		require_once MODELS_DIR . DS . 'PlayersModel.php';
		$playerInfo = PlayersModel::getPlayerDetails($this->session_id, $player_id);
        $playerInfo = $playerInfo['details'];
        require_once FORMS_DIR . DS . 'players' . DS . 'ResetPasswordForm.php';
		$form = new ResetPasswordForm();
		$form->NAME->setAttrib('disabled',true);
		$form->NAME->setValue($playerInfo['user_name']);
		$form->NAME->clearValidators();
		$form->NAME->setRequired(false);
		$this->view->form = $form;
		$this->view->username = $playerInfo['user_name'];
        $this->view->player_id = $player_id;
		if ($this->getRequest()->isPost()) {
			$formData = $this->getRequest()->getPost();
			if ($form->isValidPartial($formData)) {
				$player_name = $form->getValue('NAME');
				$player_password = $form->getValue('PASSWORD');
				$player_hashed_password = md5(md5($player_password));
				require_once MODELS_DIR . DS . 'AdministratorModel.php';
				AdministratorModel::resetPassword($this->session_id, $player_id, $player_hashed_password);
				$this->view->information = $this->translate->_("Password has been successfully reseted!!!");
			}else{
				$form->NAME->setValue($playerInfo['user_name']);
				$form->NAME->clearValidators();
				$form->NAME->setRequired(false);
			}
		}
	}

    public function unlockPlayerAction(){
		$player_id = $this->_getParam('player_id', 0);
        if($player_id == 0 || is_numeric($player_id) == false || !isset($player_id) || $this->getRequest()->getParam('CANCEL', false)){
			$locale = Zend_Registry::get('lang');
			$redirector = new Zend_Controller_Action_Helper_Redirector();
			$redirector->gotoUrl($locale . '/players/list-players');
		}
		require_once MODELS_DIR . DS . 'PlayersModel.php';
        $playerInfo = PlayersModel::getPlayerDetails($this->session_id, $player_id);
        $playerInfo = $playerInfo["details"];

        require_once FORMS_DIR . DS . 'players' . DS . 'PlayerUnlockForm.php';
		$form = new PlayerUnlockForm();

        $form->NAME->setValue($playerInfo['user_name']);

        $this->view->form = $form;

        $this->view->username = $playerInfo['user_name'];
        $this->view->player_id = $player_id;

        if ($this->getRequest()->isPost()) {
			$formData = $this->getRequest()->getPost();
			if ($form->isValidPartial($formData)) {
                if($this->getRequest()->getParam('UNLOCK', false)){
		            PlayersModel::resetWrongLoginsLeft($player_id);
                    $locale = Zend_Registry::get('lang');
			        $redirector = new Zend_Controller_Action_Helper_Redirector();
			        $redirector->gotoUrl($locale . '/players/list-players');
                }
			}else{
				$form->NAME->setValue($playerInfo['user_name']);
				$form->NAME->clearValidators();
				$form->NAME->setRequired(false);
			}
		}
	}

    //updates information details on player
	public function updateAction(){
		$player_id = $this->_getParam('player_id', 0);
		if($player_id == 0 || is_numeric($player_id) == false || !isset($player_id)){
			$locale = Zend_Registry::get('lang');
			$this->redirect($locale . '/players/list-players');
		}
		require_once FORMS_DIR . DS . 'players' . DS . 'UpdatePlayerForm.php';
		$form = new UpdatePlayerForm();
		$this->view->form = $form;

        $form->NAME->setRequired(false)->clearValidators()->clearFilters();
		$form->EMAIL->setRequired(false)->clearValidators()->clearFilters();
		$form->FIRST_NAME->setRequired(false)->clearValidators()->clearFilters();
		$form->LAST_NAME->setRequired(false)->clearValidators()->clearFilters();
		$form->BIRTHDATE->setRequired(false)->clearValidators()->clearFilters();
		$form->FIRST_NAME->setRequired(false)->clearValidators()->clearFilters();
		$form->ZIP->setRequired(false)->clearValidators()->clearFilters();
		$form->PHONE->setRequired(false)->clearValidators()->clearFilters();
		$form->CITY->setRequired(false)->clearValidators()->clearFilters();
		$form->ADDRESS->setRequired(false)->clearValidators()->clearFilters();
		$form->BANNED->setRequired(false)->clearValidators()->clearFilters();
        $form->COUNTRY->setRequired(false)->clearValidators()->clearFilters();

		if ($this->getRequest()->getParam('CANCEL', false)){
			$locale = Zend_Registry::get('lang');
			$this->redirect($locale . '/players/list-players');
		}
		require_once MODELS_DIR . DS . 'PlayersModel.php';
		$playerInfo = PlayersModel::getPlayerDetails($this->session_id, $player_id);
        $playerInfo = $playerInfo['details'];
		$this->view->username = htmlspecialchars($playerInfo['user_name']);
        $this->view->player_id = $player_id;
		$form->PLAYER_LOCATION->setValue(htmlspecialchars($playerInfo['path']));
		if ($this->getRequest()->isPost()){
			$formData = $this->getRequest()->getPost();
			if ($form->isValidPartial($formData)){
				$player_id = $this->_getParam('player_id', 0);
				if($player_id == 0 || is_numeric($player_id) == false || !isset($player_id)){
					$locale = Zend_Registry::get('lang');
					$this->redirect($locale . '/players/list-players');
				}
				$email = $form->getValue('EMAIL');
				$banned = $form->getValue('BANNED');
				$zip = $form->getValue('ZIP');
				$phone = $form->getValue('PHONE');
				$address = $form->getValue('ADDRESS');
				$birthday = $form->getValue('BIRTHDATE');
				$first_name = $form->getValue('FIRST_NAME');
				$last_name = $form->getValue('LAST_NAME');
				$city = $form->getValue('CITY');
                $country = $form->getValue('COUNTRY');
                require_once MODELS_DIR . DS . 'AdministratorModel.php';
                AdministratorModel::manageUser($this->session_id, UPDATE, null, null, null, null, null, $email, $country, null, $banned, $zip, $phone,
                    $address, $birthday, $first_name, $last_name, $city, $player_id, null, null, null, null, null);
                $this->view->information = $this->translate->_('User has been successfully updated!!!');

                $playerInfo = PlayersModel::getPlayerDetails($this->session_id, $player_id);
                $playerInfo = $playerInfo['details'];
                $form->NAME->setValue(htmlspecialchars($playerInfo['user_name']));
				$form->EMAIL->setValue(htmlspecialchars($playerInfo['email']));
				$form->BANNED->setValue(htmlspecialchars($playerInfo['banned']));
				$form->ZIP->setValue(htmlspecialchars($playerInfo['zip_code']));
				$form->PHONE->setValue(htmlspecialchars($playerInfo['phone']));
				$form->ADDRESS->setValue(htmlspecialchars($playerInfo['address']));
				$form->BIRTHDATE->setValue(htmlspecialchars($playerInfo['birthday']));
				$form->FIRST_NAME->setValue(htmlspecialchars($playerInfo['first_name']));
				$form->LAST_NAME->setValue(htmlspecialchars($playerInfo['last_name']));
				$form->CITY->setValue(htmlspecialchars($playerInfo['city']));
				$form->CURRENCY->setValue(htmlspecialchars($playerInfo['currency']));
                $form->COUNTRY->setValue(htmlspecialchars($playerInfo['country_id']));
				$form->PLAYER_LOCATION->setValue(htmlspecialchars($playerInfo['path']));
			} else {
				$this->view->message = $this->translate->_('InvalidUpdate');
				$player_id = $this->_getParam('player_id', 0);
				if($player_id == 0 || is_numeric($player_id) == false || !isset($player_id)){
					$locale = Zend_Registry::get('lang');
					$this->redirect($locale . '/players/list-players');
				}
                $form->NAME->setValue(htmlspecialchars($playerInfo['user_name']));
				$form->EMAIL->setValue(htmlspecialchars($playerInfo['email']));
				$form->BANNED->setValue(htmlspecialchars($playerInfo['banned']));
				$form->ZIP->setValue(htmlspecialchars($playerInfo['zip_code']));
				$form->PHONE->setValue(htmlspecialchars($playerInfo['phone']));
				$form->ADDRESS->setValue(htmlspecialchars($playerInfo['address']));
				$form->BIRTHDATE->setValue(htmlspecialchars($playerInfo['birthday']));
				$form->FIRST_NAME->setValue(htmlspecialchars($playerInfo['first_name']));
				$form->LAST_NAME->setValue(htmlspecialchars($playerInfo['last_name']));
				$form->CITY->setValue(htmlspecialchars($playerInfo['city']));
				$form->CURRENCY->setValue(htmlspecialchars($playerInfo['currency']));
                $form->COUNTRY->setValue(htmlspecialchars($playerInfo['country_id']));
				$form->PLAYER_LOCATION->setValue(htmlspecialchars($playerInfo['path']));
			}
		} else {
			$player_id = $this->_getParam('player_id', 0);
			if($player_id == 0 || is_numeric($player_id) == false || !isset($player_id)){
				$locale = Zend_Registry::get('lang');
				$this->redirect($locale . '/players/list-players');
			}
			if ($player_id > 0) {
                $form->NAME->setValue(htmlspecialchars($playerInfo['user_name']));
				$form->EMAIL->setValue(htmlspecialchars($playerInfo['email']));
				$form->BANNED->setValue(htmlspecialchars($playerInfo['banned']));
				$form->ZIP->setValue(htmlspecialchars($playerInfo['zip_code']));
				$form->PHONE->setValue(htmlspecialchars($playerInfo['phone']));
				$form->ADDRESS->setValue(htmlspecialchars($playerInfo['address']));
				$form->BIRTHDATE->setValue(htmlspecialchars($playerInfo['birthday']));
				$form->FIRST_NAME->setValue(htmlspecialchars($playerInfo['first_name']));
				$form->LAST_NAME->setValue(htmlspecialchars($playerInfo['last_name']));
				$form->CITY->setValue(htmlspecialchars($playerInfo['city']));
				$form->CURRENCY->setValue(htmlspecialchars($playerInfo['currency']));
                $form->COUNTRY->setValue(htmlspecialchars($playerInfo['country_id']));
				$form->PLAYER_LOCATION->setValue(htmlspecialchars($playerInfo['path']));
			}
		}
	}

    //new player
	public function newPlayerAction(){
		require_once FORMS_DIR . DS . 'players' . DS . 'NewPlayerForm.php';
		$form = new NewPlayerForm();
		$this->view->form = $form;

		$form->BIRTHDATE->setRequired(false)->clearValidators()->clearFilters();
		$form->ZIP->setRequired(false)->clearValidators()->clearFilters();
		$form->PHONE->setRequired(false)->clearValidators()->clearFilters();
		$form->CITY->setRequired(false)->clearValidators()->clearFilters();
		$form->ADDRESS->setRequired(false)->clearValidators()->clearFilters();

		if ($this->getRequest()->getParam('CANCEL', false)){
			$locale = Zend_Registry::get('lang');
			$this->redirect($locale . '/players/list-players');
		}
        $formData = $this->getRequest()->getPost();
		if ($this->getRequest()->isPost()){
			$formData = $this->getRequest()->getPost();
			if ($form->isValidPartial($formData)){
                $name = $form->getValue('NAME');
                $password = $form->getValue('PASSWORD');
	 			$password = md5(md5($password));
                $player_type = $form->getValue('PLAYER_TYPE');
				$email = $form->getValue('EMAIL');
				$banned = $form->getValue('BANNED');
				$zip = $form->getValue('ZIP');
				$phone = $form->getValue('PHONE');
				$address = $form->getValue('ADDRESS');
				$birthday = $form->getValue('BIRTHDATE');
				$first_name = $form->getValue('FIRST_NAME');
				$last_name = $form->getValue('LAST_NAME');
				$city = $form->getValue('CITY');
                $country = $form->getValue('COUNTRY');
                $currency = $form->getValue('CURRENCY');
                require_once MODELS_DIR . DS . 'AdministratorModel.php';
                try{
	 				AdministratorModel::manageUser($this->session_id, INSERT, null, $name, $password, $player_type, null, $email, $country, $currency,
	 				$banned, $zip, $phone, $address, $birthday, $first_name, $last_name, $city, null, null, null, null, null, null);
	 				$this->view->information = $this->translate->_('Player created');
	 			}catch(Zend_Exception $ex){
		 			if($ex->getCode() == '1')
	 					$this->view->message = $this->translate->_('Player exists');
		 			else
		 				$this->view->message = $this->translate->_('Player not created');
	 			}
			}
		} else $form->populate($formData);
	}

}
