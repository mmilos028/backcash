<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'NumberHelper.php';
require_once HELPERS_DIR . DS . 'mobile_detect' . DS . 'Mobile_Detect.php';
class MobileMenuController extends Zend_Controller_Action{
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
            $res =  SessionModel::validateSession($this->session_id);
			$res = $res["status"];
			if($res == NO)$this->forward('terminate', 'auth');
		}catch(Zend_Exception $ex){
			throw new Zend_Exception(CursorToArrayHelper::getExceptionTraceAsString($ex));
		}
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
	}

    public function affiliatesAction(){

    }

    public function playersAction(){

    }

    public function terminalPlayersAction(){

    }

    public function languagesAction(){

    }

    public function cashierReportsAction(){

    }

    public function reportsAction(){

    }

}
