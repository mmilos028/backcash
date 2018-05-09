<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'NumberHelper.php';
require_once HELPERS_DIR . DS . 'mobile_detect' . DS . 'Mobile_Detect.php';
class VoucherCashierController extends Zend_Controller_Action{
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
			if(isset($authInfo)) $this->session_id = $authInfo->session_id;
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
			$message = "User with username {$username} and role {$rola} and super-role {$superrola} and backoffice session id = {$session_id} tried to visit: <br /> Date and time: {$date_now} <br /> From manually entered URL address in browser <br /> To page {$dest_url} <br /> VoucherCashierController";
		}else{
			$message = "User with username {$username} and role {$rola} and super-role {$superrola} and backoffice session id = {$session_id} tried to visit: <br /> Date and time: {$date_now} <br /> From page {$origin_url} <br /> To page {$dest_url} <br /> VoucherCashierController";
		}
		//ErrorMailHelper::writeError($message, $message);
	}

	//set permissions for roles for entire transfer credit verticale
	private function setRolePermissions(){
		$rola = $_SESSION['auth_space']['session']['subject_type_name']; //rola
        if(!in_array($rola, array(ROLA_AD_CASHIER, ROLA_AD_CASHIER_PAYOUT, SUPER_ROLA_MASTER_CASINO)))
        {
            $this->logVisitedPageError();
            $url = $_SERVER['HTTP_REFERER'];
            $this->redirect($url);
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

	//generates list ticket terminals report (LOG REPORT)
	public function voucherPrintForAffiliateAction(){
        $affiliate = $_SESSION['auth_space']['session']['affiliate_id'];
        $currencies = array();
		$options = array();
		$status = $this->getRequest()->getParam('STATUS',null);
		$affiliate_id = $this->getRequest()->getParam('AFFILIATE',null);
		$currency_id = $this->getRequest()->getParam('CURRENCY',null);
		if($currency_id)
			$currency_ics = $this->getRequest()->getParam('CURRENCY_ICS');
		else
			$currency_ics = NULL;
		$expire_date = $this->getRequest()->getParam('EXPIRE_DATE',null);
		$no_of_days = $this->getRequest()->getParam('NO_OF_DAYS',null);
		$promo = $this->getRequest()->getParam('PROMO',null);
		$serial_from = $this->getRequest()->getParam('SERIAL_FROM',null);
		$serial_to = $this->getRequest()->getParam('SERIAL_TO',null);
		$update = $this->getRequest()->getParam('UPDATE',null);
		$status_check = $this->getRequest()->getParam('STATUS_CHECK',null);
		if(isset($status) && isset($status_check) && $status == $status_check)
			$status = NULL;
		if($no_of_days) $no_of_days = (int) $no_of_days;
		if(!$affiliate_id) $affiliate_id = null;
		if(!$currency_ics) $currency_ics = null;
		if(!$expire_date) $expire_date = null;
		if(!$no_of_days) $no_of_days = null;
		require_once MODELS_DIR . DS . 'VoucherModel.php';
		$currencyArr = VoucherModel::getCurrencies($this->session_id);
		foreach($currencyArr["cursor"] as $data){
			$currencies[$data['id']] = $data['ics'];
		}
		$options['currencies'] = $currencies;
		require_once FORMS_DIR . DS . 'voucher_cashier' . DS . 'VoucherMemberEditForm.php';
		$form = new VoucherMemberEditForm($options);
		if(!empty($_POST) && $form->isValid($_POST) && isset($update)){
			$status = VoucherModel::updatePrepaidCards($this->session_id,$serial_from,$serial_to,$promo,$currency_ics,$affiliate_id,$expire_date,$no_of_days,$status);
			//print_r($status);
			$this->view->messages = explode('!!!',$status['error_messages']);
			$this->view->status = $status['status'];
		}
		$this->view->form = $form;
		$this->view->affiliate = $affiliate;
	}

    public function getAffiliateForCurrencyAction(){
        $this->_helper->viewRenderer->setNoRender();
		$this->_helper->getHelper('layout')->disableLayout();
        try {
            require_once MODELS_DIR . DS . 'VoucherModel.php';
            $currency_id = $this->getRequest()->getParam('currency_id', null);
            $aff_from_session_id = VoucherModel::getSubjectFromSessionId($this->session_id);
            $aff_from_session_id = $aff_from_session_id["affiliate_id"];
            $affiliate_id = ($aff_from_session_id) ? (int)$aff_from_session_id : $_SESSION['auth_space']['session']['affiliate_id'];
            $arrData = VoucherModel::getAffiliateForCurrencyNew($this->session_id, $currency_id, $affiliate_id);
            $result = array();
            foreach ($arrData["cursor"] as $data)
                $result[] = array('id' => $data['id'], 'name' => $data['name']);
            echo Zend_Json::encode($result);
        }catch(Zend_Exception $ex){
            $result = array();
            echo Zend_Json::encode($result);
        }
	}

    public function getPrepaidCardsAction(){
		$this->_helper->viewRenderer->setNoRender();
		$this->_helper->getHelper('layout')->disableLayout();
        $from_serial = $this->getRequest()->getParam('serial_number_start',null);
		$to_serial = $this->getRequest()->getParam('serial_number_end',null);
		$card_type = $this->getRequest()->getParam('card_type',null);
		$affiliate = $this->getRequest()->getParam('affiliate',null);
		$no_valid = array();
		if($from_serial > $to_serial || ($to_serial - $from_serial > 4000)) {
			$no_valid[] = array("valid"=>"NO");
			echo Zend_Json::encode($no_valid);
			return;
		}
		require_once MODELS_DIR . DS . 'VoucherModel.php';
		$arrData = VoucherModel::getPrepaidCards($this->session_id,$from_serial,$to_serial);
		$cards_data = CursorToArrayHelper::cursorToCleanArray($arrData['cursor']);
		if(isset($cards_data) && !empty($cards_data)){
			$currency = $cards_data[0]['currency'];
			$expiry_date = $cards_data[0]['expiry_date'];
			$refill_type = $cards_data[0]['refill_type'];
			foreach($cards_data as &$data){
				if($affiliate && $data['affiliate_owner'] != $affiliate){
					$no_valid[] = array("valid"=>"NO");
					echo Zend_Json::encode($no_valid);
					return;
				}
				if(($card_type == 'member' && !$data['username'] && !$data['password']) || (!$card_type && $data['username'] && $data['password'])){
					$no_valid[] = array("valid"=>"NO");
					echo Zend_Json::encode($no_valid);
					return;
				}
				if($data['status']=='E' || $data['status']=='U' || $currency != $data['currency'] || $expiry_date != $data['expiry_date'] || $refill_type != $data['refill_type']){
					$no_valid[] = array("valid"=>"NO");
					echo Zend_Json::encode($no_valid);
					return;
				}else{
					if($data['expiry_date']){
						//$expity_date = new DateTime($data['expiry_date']);
						//$data['expiry_date'] = $expity_date->format('d.m.Y');
						$data['expiry_date'] = Date('d.m.Y',strtotime($data['expiry_date']));
					}
				}
			}
		}
		echo Zend_Json::encode($cards_data);
	}

    public function voucherMemberPrintForAffiliateAction(){
		$affiliate = $_SESSION['auth_space']['session']['affiliate_id'];
		$currencies = array();
		$options = array();
		$status = $this->getRequest()->getParam('STATUS',null);
		$affiliate_id = $this->getRequest()->getParam('AFFILIATE',null);
		$currency_id = $this->getRequest()->getParam('CURRENCY',null);
		if($currency_id)
			$currency_ics = $this->getRequest()->getParam('CURRENCY_ICS');
		else
			$currency_ics = NULL;
		$expire_date = $this->getRequest()->getParam('EXPIRE_DATE',null);
		$no_of_days = $this->getRequest()->getParam('NO_OF_DAYS',null);
		$promo = $this->getRequest()->getParam('PROMO',null);
		$serial_from = $this->getRequest()->getParam('SERIAL_FROM',null);
		$serial_to = $this->getRequest()->getParam('SERIAL_TO',null);
		$update = $this->getRequest()->getParam('UPDATE',null);
		$status_check = $this->getRequest()->getParam('STATUS_CHECK',null);
		if(isset($status) && isset($status_check) && $status == $status_check)
			$status = NULL;
		if($no_of_days) $no_of_days = (int) $no_of_days;
		if(!$affiliate_id) $affiliate_id = null;
		if(!$currency_ics) $currency_ics = null;
		if(!$expire_date) $expire_date = null;
		if(!$no_of_days) $no_of_days = null;
		require_once MODELS_DIR . DS . 'VoucherModel.php';
		$currencyArr = VoucherModel::getCurrencies($this->session_id);
		foreach($currencyArr["cursor"] as $data){
			$currencies[$data['id']] = $data['ics'];
		}
		$options['currencies'] = $currencies;
		require_once FORMS_DIR . DS . 'voucher_cashier' . DS . 'VoucherMemberEditForm.php';
		$form = new VoucherMemberEditForm($options);
		if(!empty($_POST) && $form->isValid($_POST) && isset($update)){
			$status = VoucherModel::updatePrepaidCards($this->session_id,$serial_from,$serial_to,$promo,$currency_ics,$affiliate_id,$expire_date,$no_of_days,$status);
			//print_r($status);
			$this->view->messages = explode('!!!',$status['error_messages']);
			$this->view->status = $status['status'];
		}
		$this->view->form = $form;
		$this->view->affiliate = $affiliate;
	}

    public function voucherCreatePdfAction(){
		$this->_helper->viewRenderer->setNoRender();
		$this->_helper->getHelper('layout')->disableLayout();
		if($this->getRequest()->isPost()){
		require_once('tcpdf/tcpdf.php');
		require_once('tcpdf/barcodes.php');
		$from_serial = $this->getRequest()->getParam('serial_number_start',null);
		$to_serial = $this->getRequest()->getParam('serial_number_end',null);
		require_once MODELS_DIR . DS . 'VoucherModel.php';
		$arrData = VoucherModel::getPrepaidCards($this->session_id,$from_serial,$to_serial);
		$cards_data = CursorToArrayHelper::cursorToCleanArray($arrData['cursor']);
		if(isset($cards_data) && !empty($cards_data)){
			$currency = $cards_data[0]['currency'];
			$expiry_date = $cards_data[0]['expiry_date'];
			$refill_type = $cards_data[0]['refill_type'];			
			$username = $cards_data[0]['username'];			
			foreach($cards_data as $data){
				if(($username && !$data['username']) || (!$username && $data['username']))
					return;
				if($data['status']=='E' || $data['status']=='U' || $currency != $data['currency'] || $expiry_date != $data['expiry_date'] || $refill_type != $data['refill_type'])
					return;
			}
		}
		$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor($_SESSION['auth_space']['session']['username']);
		$pdf->SetTitle($this->translate->_('PPCard'));
		$pdf->SetSubject($this->translate->_('PPCard'));
		$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
		//$pdf->SetFont('freeserif', '', 10);
		$pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
		$style = array(
			'position' => 'S',
			'align' => '',
			'stretch' => false,
			'fitwidth' => true,
			'cellfitalign' => '',
			'border' => false,
			'hpadding' => 'auto',
			'vpadding' => 'auto',
			'fgcolor' => array(0,0,0),
			'bgcolor' => false, //array(255,255,255),
			'text' => false,
			'font' => 'helvetica',
			'fontsize' => 1,
			'stretchtext' => 4);
		$card_counter = 1;
		$j=1;
		$barcode_size = 22;
		$html = "";
		$limit_per_page = 9;
		$new_row=1;
		foreach($cards_data as $data){
			$expire_date = "";
			$refill = (isset($data['refill_allowed']) && $data['refill_allowed']=="Y") ? " - Refill" : "";
			if(isset($data['expiry_date']) && $data['expiry_date']){
				$expire_date = explode(' ', $data['expiry_date']);
				$expire_date = explode('.', $expire_date[0]);
				$expire_date = $expire_date[2] . '/' . $expire_date[1] . '/' . $expire_date[0];
			}
			//$barcode_size++;
			$code_to_show = chunk_split($data['prepaid_code'],4,' ');
			$card_member_addon = "";
			$card_name = "Voucher";
            if($data['refill_type'] == "PROMO MONEY"){
                $card_name = "Promotion Voucher";
            }
			if(isset($data['username']) && $data['username'] && isset($data['password']) && $data['password']){
				$barcode_size = 22;
				$barcode = $pdf->serializeTCPDFtagParameters(array('$$'.$data['prepaid_code'], 'C39', '', '', 0, $barcode_size, 0.3, $style, 'N'));
				//$card_name = "Prepaid Card";
				$limit_per_page = 9;
				$card_member_addon = <<<CARD_MEMEBER_ADDON
				<tr><td colspan="3" style="font-size:5px;"></td></tr>
				<tr>
					<td colspan="3" style="font-size:4mm;font-weight:bold;">&nbsp;New Account</td>
				</tr>
				<tr>
					<td colspan="3" style="font-size:2px;font-weight:bold;">
						<table cellspacing="2"><tr><td style="border-top:1px solid #AAA;"></td></tr></table>
					</td>
				</tr>
				<tr>
					<td style="font-size:3mm;"> &nbsp;Username</td>
					<td style="font-size:3mm;font-weight:bold;"></td>
					<td style="font-size:3mm;">Password</td>
				</tr>
				<tr>
					<td style="font-size:3mm;font-weight:bold;" colspan="2"> &nbsp;{$data['username']}</td>
					<td style="font-size:3mm;font-weight:bold;">{$data['password']}</td>
				</tr>
				<tr>
					<td style="width:100%;font-size:3mm;" colspan="3">&nbsp;&nbsp;Voucher code: <span style="font-weight:bold;">{$code_to_show}</span></td>
				</tr>
				<tr>
					<td style="font-size:4mm;width:57%;">&nbsp;{$card_name}</td>
					<td style="font-size:3mm;width:42%;text-align:left;line-height:5px;" colspan="2">Serial number:</td>		
				</tr>
				<tr>
					<td style="font-size:3mm;">&nbsp;</td>
					<td colspan="2" style="font-size:3mm;text-align:center;font-weight:bold;">{$data['serial_number']}</td>
				</tr>
CARD_MEMEBER_ADDON;
			}
			else{
				$barcode_size = 26;
				$barcode = $pdf->serializeTCPDFtagParameters(array('$$'.$data['prepaid_code'], 'C39', '', '', 0, $barcode_size, 0.3, $style, 'N'));
				$card_member_addon = <<<CARD_MEMEBER_ADDON
				<tr><td colspan="3" style="font-size:18px;"></td></tr>
				<tr>
				    <td style="font-size:5mm;text-align:left;font-weight:bold;" colspan="3">&nbsp;{$card_name}</td>
				</tr>
				<tr><td colspan="3" style="font-size:8px;"></td></tr>
				<tr>
					<td colspan="3" style="font-size:3mm;font-weight:bold;">
						<table cellspacing="2"><tr><td style="border-top:1px solid #AAA;"></td></tr></table>
					</td>
				</tr>
				<tr>
					<td style="width:100%;font-size:3mm;" colspan="3">&nbsp;&nbsp;Voucher code: <span style="font-weight:bold;">{$code_to_show}</span></td>
				</tr>
				<tr><td colspan="3" style="font-size:8px;"></td></tr>
				<tr><td colspan="3" style="font-size:8px;"></td></tr>
				<tr>
					<td style="font-size:3mm; width:42%;">&nbsp;&nbsp;Serial number:</td>
					<td colspan="2" style="font-size:3mm; text-align: left;font-weight:bold;">{$data['serial_number']}</td>
					
				</tr>
CARD_MEMEBER_ADDON;
			}
			$amount = number_format($data['amount'],0,'','.');
			$card = <<<CARD
			<table><tr><td></td></tr></table>
			<table><tr><td></td></tr></table>
			<table style="height:85.60mm;background:#FFF;width:54mm;margin:10mm;padding:0;border:1px dashed #666" cellpadding="1" border="0">
				{$card_member_addon}
				<tr>
					<td style="width:27%;font-size:3mm;height:20px;line-height:10px;text-align:right;">Amount:</td>
					<td style="width:70%;" colspan="2"><span style="font-size:5mm;height:12px;line-height:6px;font-weight:bold;">{$amount} </span><span style="color:#999;font-size:5mm;height:12px;line-height:6px;font-weight:bold;">{$data['currency']}</span></td>					
				</tr>
				<tr>
					<td style="width:100%;font-size:3mm" colspan="3">&nbsp;&nbsp;Expiry date: <span style="font-weight:bold;">{$expire_date}</span></td>
				</tr>
				<tr>
					<td style="width:100%;font-size:1mm" colspan="3"><tcpdf method="write1DBarcode" params="{$barcode}"/></td>
				</tr>
			</table>
CARD;
			if($j==1)
				$html .= "<table style=''>";
			if($new_row)
				$html .= "<tr>";
			$html .= "<td>" . $card . "</td>";
			$new_row = ($j%3 == 0) ? 1 : 0;
			if($j%3 == 0 || $card_counter == count($cards_data))
				$html .= "</tr>";
			if($j==$limit_per_page || $card_counter == count($cards_data)) {
				$html .= "</table>";
				$pdf->AddPage();
				$pdf->writeHTML($html, true, false, false, false, '');
				$html = "";
				$j = 0;
			}
			$j++;
			$card_counter++;
		}
		//echo "<xmp>".$html."</xmp>";die;
		$pdf->lastPage();
		//$pdf_title = "PPCard-" . date("Ymd-His") . ".pdf";
		$pdf_title = "Voucher-{$from_serial}-{$to_serial}.pdf";
		$pdf->Output($pdf_title,'D');
		unset($pdf);
		unset($html);
	}
	else{
		$locale = Zend_Registry::get('lang');
		$redirector = new Zend_Controller_Action_Helper_Redirector();
		$redirector->gotoUrl($locale . '/');
	}
	}

	public function voucherCreatePdfHorizontalLayoutAction(){
		$this->_helper->viewRenderer->setNoRender();
		$this->_helper->getHelper('layout')->disableLayout();
		if($this->getRequest()->isPost()){
		require_once('tcpdf/tcpdf.php');
		require_once('tcpdf/barcodes.php');
		$from_serial = $this->getRequest()->getParam('serial_number_start',null);
		$to_serial = $this->getRequest()->getParam('serial_number_end',null);
		require_once MODELS_DIR . DS . 'VoucherModel.php';
		$arrData = VoucherModel::getPrepaidCards($this->session_id,$from_serial,$to_serial);
		$cards_data = CursorToArrayHelper::cursorToCleanArray($arrData['cursor']);
		if(isset($cards_data) && !empty($cards_data)){
			$currency = $cards_data[0]['currency'];
			$expiry_date = $cards_data[0]['expiry_date'];
			$refill_type = $cards_data[0]['refill_type'];			
			$username = $cards_data[0]['username'];			
			foreach($cards_data as $data){
				if(($username && !$data['username']) || (!$username && $data['username']))
					return;
				if($data['status']=='E' || $data['status']=='U' || $currency != $data['currency'] || $expiry_date != $data['expiry_date'] || $refill_type != $data['refill_type'])
					return;
			}
		}
		$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor($_SESSION['auth_space']['session']['username']);
		$pdf->SetTitle($this->translate->_('PPCard'));
		$pdf->SetSubject($this->translate->_('PPCard'));
		$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
		//$pdf->SetFont('freeserif', '', 10);
		$pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
		$style = array(
			'position' => 'S',
			'align' => '',
			'stretch' => false,
			'fitwidth' => true,
			'cellfitalign' => '',
			'border' => false,
			'hpadding' => 'auto',
			'vpadding' => 'auto',
			'fgcolor' => array(0,0,0),
			'bgcolor' => false, //array(255,255,255),
			'text' => false,
			'font' => 'helvetica',
			'fontsize' => 1,
			'stretchtext' => 4);
		$card_counter = 1;
		$j=1;
		$barcode_size = 16;
		$html = "";
		$limit_per_page = 8;
		foreach($cards_data as $data){
			$expire_date = "";
			$refill = (isset($data['refill_allowed']) && $data['refill_allowed']=="Y") ? " - Refill" : "";
			if(isset($data['expiry_date']) && $data['expiry_date']){
				$expire_date = explode(' ', $data['expiry_date']);
				$expire_date = explode('.', $expire_date[0]);
				$expire_date = $expire_date[2] . '/' . $expire_date[1] . '/' . $expire_date[0];
			}
			//$barcode_size++;
			$code_to_show = chunk_split($data['prepaid_code'],4,' ');
			$card_member_addon = "";
            $card_name = "Voucher";
			if($data['refill_type'] == "PROMO MONEY"){
                $card_name = "Promotion Voucher";
            }
			if(isset($data['username']) && $data['username'] && isset($data['password']) && $data['password']){
				$barcode_size = 12;
				$barcode = $pdf->serializeTCPDFtagParameters(array('$$'.$data['prepaid_code'], 'C39', '', '', 0, $barcode_size, 0.3, $style, 'N'));
				$card_name = "Prepaid Card";
				$limit_per_page = 8;
				$card_member_addon = <<<CARD_MEMEBER_ADDON
				<tr>
					<td colspan="3" style="font-size:4mm;font-weight:bold;">&nbsp;New Account</td>
				</tr>
				<tr>
					<td style="font-size:3mm;font-weight:bold;"> Username</td>
					<td></td>
					<td style="font-size:3mm;font-weight:bold;">Password</td>
				</tr>
				<tr>
					<td style="font-size:3mm;"> {$data['username']}</td>
					<td></td>
					<td style="font-size:3mm;">{$data['password']}</td>
				</tr>
CARD_MEMEBER_ADDON;
			}
			else{
				$barcode_size = 16;
				$barcode = $pdf->serializeTCPDFtagParameters(array('$$'.$data['prepaid_code'], 'C39', '', '', 0, $barcode_size, 0.3, $style, 'N'));
				$card_member_addon = <<<CARD_MEMEBER_ADDON
				<tr>
					<td colspan="3" style="font-size:4mm;font-weight:bold;"></td>
				</tr>
CARD_MEMEBER_ADDON;
			}
			$card = <<<CARD
			<table><tr><td></td></tr></table>
			<table style="height:53.98mm;background:#FFF;width:85.60mm;margin:10mm;padding:0;border:1px dashed #666" cellpadding="1" border="0">
				{$card_member_addon}
				<tr>
					<td style="font-size:4mm;width:57%;font-weight:bold;">&nbsp;{$card_name}{$refill}</td>
					<td style="font-size:3mm;width:40%;text-align:right;font-weight:bold;" colspan="2">Expiry date: {$expire_date}</td>		
				</tr>
				<tr>
					<td style="width:18%;font-size:3mm;height:20px;line-height:10px;text-align:right;">Amount:</td>
					<td style="width:35%;"><span style="font-size:5mm;height:12px;line-height:6px;font-weight:bold;">{$data['amount']} </span><span style="color:#999;font-size:5mm;height:12px;line-height:6px;font-weight:bold;">{$data['currency']}</span></td>
					<td style="width:47%;"></td>
				</tr>
				<tr>
					<td style="font-size:3mm" colspan="3">&nbsp;Serial number: {$data['serial_number']}</td>
				</tr>
				<tr>
					<td style="font-size:1mm" colspan="3"><tcpdf method="write1DBarcode" params="{$barcode}"/></td>
				</tr>
				<tr>
					<td style="font-size:3mm;" colspan="3">&nbsp;Voucher code: <span style="font-weight:bold;">{$code_to_show}</span></td>
				</tr>
			</table>
CARD;
			if($j%9==0 || $j==1)
				$html .= "<table style=''>";
			if($j%2)
				$html .= "<tr>";
			$html .= "<td>" . $card . "</td>";
			if(!($j%2))
				$html .= "</tr>";
			if($j>$limit_per_page-1 || $card_counter==count($cards_data)) {
				$html .= "</table>";
				//echo "<xmp>".$html."</xmp>";
				$pdf->AddPage();
				$pdf->writeHTML($html, true, false, false, false, '');
				$html = "";
				$j = 0;
				//continue;
			}
			$j++;
			$card_counter++;
		}
		//echo "<xmp>".$html."</xmp>";
		$pdf->lastPage();
		//$pdf_title = "PPCard-" . date("Ymd-His") . ".pdf";
		$pdf_title = "Voucher-{$from_serial}-{$to_serial}.pdf";
		$pdf->Output($pdf_title,'D');
		unset($pdf);
		unset($html);
	}
	else{
		$locale = Zend_Registry::get('lang');
		$redirector = new Zend_Controller_Action_Helper_Redirector();
		$redirector->gotoUrl($locale . '/');
	}
	}
    
    public function voucherListForAffiliateAction(){
		$affiliate_id = $_SESSION['auth_space']['session']['affiliate_id'];
		require_once MODELS_DIR . DS . 'VoucherModel.php';
		$page_number = $this->getRequest()->getParam('PAGE_NUMBER',1);
		$rows_per_page = $this->getRequest()->getParam('ROWS_PER_PAGE',100);
		$serial_number = $this->getRequest()->getParam('SERIAL_NUMBER',null);
        if(is_numeric($serial_number) == false){
            $replacement = '';
            $serial_number = str_replace('_',$replacement, $serial_number);
        }
		$amount = $this->getRequest()->getParam('AMOUNT',null);
		$currency = $this->getRequest()->getParam('CURRENCY',null);
		$currency_isc = $this->getRequest()->getParam('CURRENCY_ISC',null);
		$status = $this->getRequest()->getParam('STATUS',null);
		$affiliate_creator = $this->getRequest()->getParam('CREATED_BY',null);
		$creation_date = $this->getRequest()->getParam('CREATED_DATE',null);
		$affiliate_owner = $this->getRequest()->getParam('AFF_OWNER',null);
		$used_by_player_id = $this->getRequest()->getParam('USED_BY',null);
		$used_date = $this->getRequest()->getParam('USED_DATE',null);
		$username = $this->getRequest()->getParam('USERNAME',null);
		$expire_before = $this->getRequest()->getParam('EXPIRE_BEFORE',null);
		$expire_after = $this->getRequest()->getParam('EXPIRE_AFTER',null);
		$refill_type = $this->getRequest()->getParam('PROMO',null);
		$refill_allowed = $this->getRequest()->getParam('REFILL',null);
		$player_id_bound = $this->getRequest()->getParam('PLAYER_ID_BOUND',null);
		$activation_date = $this->getRequest()->getParam('ACTIVATION_DATE',null);
		$prepaid_code = $this->getRequest()->getParam('PREPAID_CODE',null);
		$change = $this->getRequest()->getParam('CHANGE',null);
		if(isset($change) && $change=='per_page') $page_number = 1;
		if($this->getRequest()->isPost() && $change!='per_page' && $change!='page') $page_number = 1;
		$options = array();
		//$expire_after .= " 00:00:00";
		$currencyArr = VoucherModel::getCurrencies($this->session_id);
		$affiliateCreatorCursor = VoucherModel::getAffiliateCreator();
		foreach($affiliateCreatorCursor['cursor'] as $key=>$data) $affiliateCreator[$data['affiliate_creator']] = $data['creator_name'];
		$options['aff_creator'] = $affiliateCreator;
		$affiliateOwnerCursor = VoucherModel::getAffiliateOwner();
		foreach($affiliateOwnerCursor['cursor'] as $data) $affiliateOwner[$data['affiliate_owner']] = $data['owner_name'];
		$options['aff_owner'] = $affiliateOwner;
		$used_by_PlayerCursor = VoucherModel::getUsedByPlayer();
		foreach($used_by_PlayerCursor['cursor'] as $data) $used_by_Player[$data['used_by_player_id']] = $data['player_used_by_name'];
		$options['used_by_player'] = $used_by_Player;
        $currencies = array();
		foreach($currencyArr["cursor"] as $data){
			$currencies[$data['id']] = $data['ics']; 
		}
		$options['currencies'] = $currencies;
		if($affiliate_id){
			$options['affiliate_id'] = $affiliate_id;
			$arrData = VoucherModel::searchPrepaidCards($this->session_id,$page_number,$rows_per_page,$serial_number,$affiliate_id,$affiliate_creator,$used_by_player_id,$player_id_bound,$activation_date,$amount,$prepaid_code,$currency_isc,$refill_type,$status,$creation_date,$used_date,$username,$refill_allowed,$expire_before,$expire_after);
		}else{
			$arrData = VoucherModel::searchPrepaidCards($this->session_id,$page_number,$rows_per_page,$serial_number,$affiliate_owner,$affiliate_creator,$used_by_player_id,$player_id_bound,$activation_date,$amount,$prepaid_code,$currency_isc,$refill_type,$status,$creation_date,$used_date,$username,$refill_allowed,$expire_before,$expire_after);
		}
		$cards_data = CursorToArrayHelper::cursorToCleanArray($arrData['cursor']);
		if($arrData['count'])
			$count = $arrData['count'];
		elseif($arrData['count']===0) 
			$count = 1;
		else
			$count = $this->getRequest()->getParam('COUNT');
		$this->view->cards_data = $cards_data;
		$pages_count = ceil($count/$rows_per_page);
		if($page_number == 0)
			$page_number = 1;
		for($i=1; $i<=$pages_count; $i++)
			$options['page_number'][$i] = $i;
		require_once FORMS_DIR . DS . 'voucher_cashier' . DS . 'VoucherListForm.php';
		$form = new VoucherListForm($options);
		$formData = $this->getRequest()->getPost();
		if ($this->getRequest()->isPost()) {
            $form->populate($formData);
        }
		$this->view->count = $count;
		$this->view->form = $form;
		$this->view->page_number = $page_number;
		$this->view->rows_per_page = $rows_per_page;
		$this->_helper->viewRenderer('voucher-list');
	}

}
