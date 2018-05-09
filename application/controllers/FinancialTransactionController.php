<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'NumberHelper.php';
require_once HELPERS_DIR . DS . 'mobile_detect' . DS . 'Mobile_Detect.php';
class FinancialTransactionController extends Zend_Controller_Action
{
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

    //calls for controller initialization when first time called
    //initialization of layout, pagination, sorting and limit per page
    public function init()
    {

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

        $this->initView();
        $this->view->baseUrl = $this->_request->getBaseUrl();
        $this->translate = Zend_Registry::get("translate");
        $auth = Zend_Auth::getInstance();
        if (!$auth->hasIdentity()) {
            $this->forward('login', 'auth');
        }else {
            $authInfo = $auth->getIdentity();
            if (isset($authInfo)) {
                $this->session_id = $authInfo->session_id;
            }
        }
        //setup number of pages items per report from database or set default 200
        require_once MODELS_DIR . DS . 'BoSetupModel.php';
        $defaultPerPage = BoSetupModel::numberOfItemsPerPage($this->session_id);
        $this->defaultPerPage = $defaultPerPage["lines_for_page"];
        if (!isset($this->defaultPerPage)) {
            $this->defaultPerPage = 200;
        }
        if (!isset($this->session_space)) {
            $this->session_space = new Zend_Session_Namespace('report_operation');
            require_once MODELS_DIR . DS . 'DateTimeModel.php';
            if (!isset($this->session_space->startdate) && !isset($this->session_space->enddate)) {
                $rola = $_SESSION['auth_space']['session']['subject_type_name']; //rola
                //if role is ROLA_AD_COLLECTOR constant then startdate not updateable and is from collectors last collect date
                if ($rola != ROLA_AD_COLLECTOR) {
                    $startdate = DateTimeModel::firstDayInMonth();
                    $_SESSION['auth_space']['session']['change_startdate'] = true;
                } else {
                    //if user is collector then start date is last time collect
                    if ($_SESSION['auth_space']['session']['last_time_collect'] == '') {
                        //if collector had no collect cash he can change startdate and see from first in current month
                        $_SESSION['auth_space']['session']['change_startdate'] = true;
                        $startdate = DateTimeModel::firstDayInMonth();
                    } else {
                        $startdate = date('d-M-Y', strtotime($_SESSION['auth_space']['session']['last_time_collect']));
                        $_SESSION['auth_space']['session']['change_startdate'] = false;
                    }
                }
                $date2 = new Zend_Date();
                $now_in_month = $date2->now();
                $enddate = $now_in_month->toString('dd-MM-yyyy');
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
        if ($origin_url == "") {
            $message = "User with username {$username} and role {$rola} and super-role {$superrola} and backoffice session id = {$session_id} tried to visit: <br /> Date and time: {$date_now} <br /> From manually entered URL address in browser <br /> To page {$dest_url} <br /> FinancialTransactionController";
        } else {
            $message = "User with username {$username} and role {$rola} and super-role {$superrola} and backoffice session id = {$session_id} tried to visit: <br /> Date and time: {$date_now} <br /> From page {$origin_url} <br /> To page {$dest_url} <br /> FinancialTransactionController";
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
    private function setRolePermissions()
    {
        $rola = $_SESSION['auth_space']['session']['subject_type_name']; //rola
        //if rola is Ad / Collector redirect to previous page
        if ($rola == ROLA_AD_COLLECTOR) {
            $this->logVisitedPageError();
            $this->forward("logout", "auth");
        }
        //if rola is ShiftCashierS or ShiftCashierW and shift is not opened - equals N redirect to previous page
        $shift_status = NO;
        if ($rola == ROLA_AD_SHIFT_CASHIER_S || $rola == ROLA_AD_SHIFT_CASHIER_W) {
            require_once MODELS_DIR . DS . 'CashiersCollectorsModel.php';
            $shift_status = CashiersCollectorsModel::checkOpenShifts($this->session_id);
        }
        if (($rola == ROLA_AD_SHIFT_CASHIER_S || $rola == ROLA_AD_SHIFT_CASHIER_W) && $shift_status["shift_status"] == NO) {
            $this->logVisitedPageError();
            $this->forward("logout", "auth");
        }
    }

    //is called before every action and performes session validation and current session of logged in user
    public function preDispatch()
    {
        $auth = Zend_Auth::getInstance();
        if (!$auth->hasIdentity()) {
            $this->forward("logout", "auth");
        }else {
            $authInfo = $auth->getIdentity();
            if (isset($authInfo)) {
                $this->session_id = $authInfo->session_id;
            }
        }
        $this->writeFirebugInfo();
        require_once MODELS_DIR . DS . 'SessionModel.php';
        $res = SessionModel::validateSession($this->session_id);
        if ($res['status'] == NO) {
            $this->forward("terminate", "auth");
        }
        //check if logged in user with role has permissions to access
        //transfer credit menu or redirect back to incoming address
        $this->setRolePermissions();
        //display number of game and backoffice sessions on application main menu
        if (!$this->isXmlHttpRequest()) {
            $activeSessionsArr = SessionModel::listNumberActivePlayerSession($this->session_id);
            Zend_Layout::getMvcInstance()->assign('no_game_sessions', $activeSessionsArr["no_game_sessions"]);
            Zend_Layout::getMvcInstance()->assign('no_bo_sessions', $activeSessionsArr["no_bo_sessions"]);
        }
    }

    private function isXmlHttpRequest()
    {
        return ($this->getHeader('X_REQUESTED_WITH') == 'XMLHttpRequest');
    }

    private function getHeader($header)
    {
        $temp = 'HTTP_' . strtoupper(str_replace('-', '_', $header));
        if (!empty($_SERVER[$temp]))
            return $_SERVER[$temp];
        return null;
    }

    //default action called if no action is named in URL address
    public function indexAction()
    {
        //check forbidden roles first
        $rola = $_SESSION['auth_space']['session']['subject_type_name']; //rola
        if (in_array($rola, array(ROLA_AD_CASHIER_PAYOUT, ROLA_AD_THAICASHIER))) {
            $url = $_SERVER['HTTP_REFERER'];
            $this->redirect($url);
        }
        $this->forward("players-for-credit-transfer", "financial-transaction");
    }

    //NEW SCREENS LOADING

    //enable credit transfer before he chooses to go to credit transfer form
    public function enableCreditTransferAction()
    {
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->getHelper('layout')->disableLayout();
        try {
            require_once MODELS_DIR . DS . 'TransferCreditModel.php';
            TransferCreditModel::enableCreditTransfer($this->session_id);
            $flag = "1";
        } catch (Zend_Exception $ex) {
            $flag = "0";
        }
        echo $flag;
    }

    //disable credit transfer when users tries to post form
    public function disableCreditTransferAction()
    {
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->getHelper('layout')->disableLayout();
        try {
            require_once MODELS_DIR . DS . 'TransferCreditModel.php';
            $status = TransferCreditModel::disableCreditTransfer($this->session_id);
            $status = $status["status"];
            echo $status;
        } catch (Zend_Exception $ex) {
            echo "0";
        }
    }

    //lists players and terminal when cashier login into backoffice
    public function depositListAction()
    {
        $rola = $_SESSION['auth_space']['session']['subject_type_name']; //rola
        $shift_status = NO; //shift for ShiftCashierS has not started
        if ($rola == ROLA_AD_SHIFT_CASHIER_S || $rola == ROLA_AD_SHIFT_CASHIER_W) {
            require_once MODELS_DIR . DS . 'CashiersCollectorsModel.php';
            $session_id = $_SESSION['auth_space']['session']['session_out'];
            $arrCheckShift = CashiersCollectorsModel::checkOpenShifts($session_id);
            $shift_status = $arrCheckShift['shift_status'];
        }
        $flag_player_credit_transfer = true;
        if( in_array($rola, array(ROLA_AD_SHIFT_CASHIER_S, ROLA_AD_SHIFT_CASHIER_W)) && $shift_status == YES){
            $flag_player_credit_transfer = true;
        }
        else{
            if( in_array($rola, array(ROLA_AD_SHIFT_CASHIER_S, ROLA_AD_SHIFT_CASHIER_W))  && $shift_status == NO){
                $flag_player_credit_transfer = false;
            }
        }
        //check shift opened for shift cashiers
		if($flag_player_credit_transfer == false){
			$this->logVisitedPageError();
			$url = $_SERVER['HTTP_REFERER'];
			$this->redirect($url);
		}

        //check if payout cashier then not allow deposits
        if(in_array($rola, array(ROLA_AD_CASHIER_PAYOUT, ROLA_AD_THAICASHIER)))
        {
            $this->logVisitedPageError();
            $url = $_SERVER['HTTP_REFERER'];
            $this->redirect($url);
        }
        $pageNo = 1;
        $perPage = 1000000;
        $total_pages = 1;
        $arrData = array();

        require_once MODELS_DIR . DS . 'TransferCreditModel.php';
        if ($pageNo == 1) {
            $arrData = TransferCreditModel::listDirectPlayersTerminalsForCreditTransfer($this->session_id, 0, 0, $pageNo, $perPage);
            $total_items = $arrData["info"][0]["cnt"];
            $total_pages = ceil($total_items / $perPage);
        } else {
            if ($pageNo >= $total_pages) {
                $arrData = TransferCreditModel::listDirectPlayersTerminalsForCreditTransfer($this->session_id, 0, 0, $pageNo, $perPage);
                $total_items = $arrData["info"][0]["cnt"];
                $total_pages = ceil($total_items / $perPage);
            }
        }
        if (count($arrData["table"]) == 0) {
            $pageNo = 1;
            $arrData = TransferCreditModel::listDirectPlayersTerminalsForCreditTransfer($this->session_id, 0, 0, $pageNo, $perPage);
            $total_items = $arrData["info"][0]["cnt"];
            $total_pages = ceil($total_items / $perPage);
        }
        $report = array();
        foreach($arrData["table"] as $data){
            $report[] = array(
                "player_id"=> $data['player_id'],
                "player_name"=> $data['player_name'],
                "player_credits"=> $data['player_credits'],
                "affiliate_id"=> $data['cashier_id'],
                "currency"=> $data['affiliate_currency'],
                "rola"=> $data['rola']
            );
        }

        CursorToArrayHelper::aasort($report, 'rola', 'DESC');

        $this->view->paginator = $report;
    }

    //add credits from cashier to pc - players
    public function addCreditsToPlayerFromCashierAction()
    {
        $player_id = $this->_getParam('player_id', 0);
        if ($player_id == 0 || is_numeric($player_id) == false || !isset($player_id)) {
            $locale = Zend_Registry::get("lang");
            $this->redirect($locale . "/financial-transaction/deposit-list");
        }
        require_once MODELS_DIR . DS . 'TransferCreditModel.php';
        //ako nije postovana forma
        if (!$this->getRequest()->isPost()) {
            $status = TransferCreditModel::disableCreditTransfer($this->session_id);
            if ($status['status'] != 1) {
                $locale = Zend_Registry::get("lang");
                $this->redirect($locale . "/financial-transaction/deposit-list");
            }
        }
        require_once FORMS_DIR . DS . 'financial_transaction' . DS . 'for_touchscreen' . DS . 'TransferCreditFromCashierToPlayerTerminalForm.php';
        $form = new TransferCreditFromCashierToPlayerTerminalForm();
        $form->SUBMIT->setValue($this->translate->_("Add"));

        $arrData = TransferCreditModel::listDirectPlayersTerminalsForCreditTransfer($this->session_id, 5, $player_id, 1, 1000);
        $data = $arrData["table"][0];
        $affiliate_id = $data['cashier_id'];
        $affiliate_username = $data['cashier_name'];
        $currency = $data['affiliate_currency'];
        $player_username = $data['player_name'];
        $affiliate_credit_status = $data['cashier_balance'];
        $player_credits = $data['player_credits'];
        $autoincrement_amount = $data['cashier_auto_incr'];
        if($autoincrement_amount != ""){
            $enabled_auto_increment_y = YES;
        }else{
            $enabled_auto_increment_y = NO;
        }

        $form->DIRECT_PLAYER_NAME->setValue($player_username);
        $form->PLAYER_CREDIT_STATUS->setValue(NumberHelper::format_double($player_credits));
        $form->PLAYER_CREDIT_STATUS_HIDDEN->setValue($player_credits);
        $form->AFF_NAME->setValue($affiliate_username);
        $form->PLAYER_CURRENCY->setValue($currency);
        $form->AFF_CREDIT_STATUS->setValue(NumberHelper::format_double($affiliate_credit_status));
        $form->AFF_CREDIT_STATUS_HIDDEN->setValue($affiliate_credit_status);
        $form->ENABLED->setValue($enabled_auto_increment_y);
        $form->AFF_ID->setValue($affiliate_id);
        $form->POSSIBLE_AMOUNT->setValue($autoincrement_amount);
        if ($enabled_auto_increment_y == YES) {
            //if affiliate is autoincrement enabled
            $form->removeElement('AFF_CREDIT_STATUS');// then remove affiliate balance
        }
        $this->view->form = $form;
        //post form and try credit transfer to player
        if ($this->getRequest()->isPost()) {
            if ($this->getRequest()->getParam('CANCEL', false)) {
                $locale = Zend_Registry::get("lang");
                $this->redirect($locale . "/financial-transaction/deposit-list");
            }
            $formData = $this->getRequest()->getPost();
            if ($form->isValidPartial($formData)) {
                $aff_id = $form->getValue('AFF_ID');
                $amount = $form->getValue('TRANSFER_AMOUNT_HIDDEN');
                $credit_status = $form->getValue('AFF_CREDIT_STATUS_HIDDEN');
                $possible_amount = $form->getValue('POSSIBLE_AMOUNT');
                $enabled = $form->getValue('ENABLED');
                $currency = $_SESSION['auth_space']['session']['currency'];
                if ($amount <= 0) { //if value is 0 or negative
                    $this->view->message = $this->translate->_("AmountNegativeValueNotAllowed");
                }
                else { //if value is 0 and in correct format
                    if ($amount <= $credit_status) {
                        //if amount transfer is less than affiliate credit status amount
                        //try credit transfer to player
                        $possible_amount = 0;
                        $res = TransferCreditModel::transferAffiliateToPlayer($this->session_id, $aff_id, $player_id, $amount, $credit_status, $possible_amount, $enabled, $currency);
                        if (strlen($res) != 0) {
                            //if there is message from database then print it to transfer credit form
                            $this->view->message = $res;
                        } else {
                            $locale = Zend_Registry::get("lang");
                            $this->redirect($locale . "/financial-transaction/add-credits-to-player-from-cashier-completed/player_id/" . $player_id);
                        }
                    } else {
                        //credit transfer amount is larger than affiliate has on account
                        if ($enabled == YES && $amount <= $possible_amount) {
                            //if affiliate is enabled to transfer larger amounts than its account limit
                            //try credit transfer
                            $res = TransferCreditModel::transferAffiliateToPlayer($this->session_id, $aff_id, $player_id, $amount, $credit_status, $possible_amount, $enabled, $currency);
                            if (strlen($res) != 0) {
                                $this->view->message = $res;
                            } else {
                                $locale = Zend_Registry::get("lang");
                                $this->redirect($locale . "/financial-transaction/add-credits-to-player-from-cashier-completed/player_id/" . $player_id);
                            }
                        } else {
                            //affiliate not allowed to transfer larger amounts than account limit
                            //fill form with initial data and write eror messages transfer not allowed

                            $arrData = TransferCreditModel::listDirectPlayersTerminalsForCreditTransfer($this->session_id, 5, $player_id, 1, 1000);
                            $data = $arrData["table"][0];
                            $affiliate_id = $data['cashier_id'];
                            $affiliate_username = $data['cashier_name'];
                            $currency = $data['affiliate_currency'];
                            $player_username = $data['player_name'];
                            $affiliate_credit_status = $data['cashier_balance'];
                            $player_credits = $data['player_credits'];
                            $autoincrement_amount = $data['cashier_auto_incr'];
                            if($autoincrement_amount != ""){
                                $enabled_auto_increment_y = YES;
                            }else{
                                $enabled_auto_increment_y = NO;
                            }

                            $form->DIRECT_PLAYER_NAME->setValue($player_username);
                            $form->PLAYER_CREDIT_STATUS->setValue(NumberHelper::format_double($player_credits));
                            $form->PLAYER_CREDIT_STATUS_HIDDEN->setValue($player_credits);
                            $form->AFF_NAME->setValue($affiliate_username);
                            $form->PLAYER_CURRENCY->setValue($currency);
                            if ($enabled_auto_increment_y != YES) {
                                $form->AFF_CREDIT_STATUS->setValue(NumberHelper::format_double($affiliate_credit_status));
                            }
                            $form->AFF_CREDIT_STATUS_HIDDEN->setValue($affiliate_credit_status);
                            $form->AFF_ID->setValue($affiliate_id);
                            $form->ENABLED->setValue($enabled_auto_increment_y);
                            $this->view->message = $this->translate->_("LargerAmountEntered"); //error message transfer not possible
                        }
                    }
                }
            } else {
                $player_id = $this->_getParam('player_id', 0);
                if ($player_id == 0 || is_numeric($player_id) == false || !isset($player_id)) {
                    $locale = Zend_Registry::get("lang");
                    $this->redirect($locale . "/financial-transaction/deposit-list");
                }
                //show initial form data

                $arrData = TransferCreditModel::listDirectPlayersTerminalsForCreditTransfer($this->session_id, 5, $player_id, 1, 1000);
                $data = $arrData["table"][0];
                $affiliate_id = $data['cashier_id'];
                $affiliate_username = $data['cashier_name'];
                $currency = $data['affiliate_currency'];
                $player_username = $data['player_name'];
                $affiliate_credit_status = $data['cashier_balance'];
                $player_credits = $data['player_credits'];
                $autoincrement_amount = $data['cashier_auto_incr'];
                if($autoincrement_amount != ""){
                    $enabled_auto_increment_y = YES;
                }else{
                    $enabled_auto_increment_y = NO;
                }

                $form->DIRECT_PLAYER_NAME->setValue($player_username);
                $form->PLAYER_CREDIT_STATUS->setValue(NumberHelper::format_double($player_credits));
                $form->PLAYER_CREDIT_STATUS_HIDDEN->setValue($player_credits);
                $form->AFF_NAME->setValue($affiliate_username);
                if ($enabled_auto_increment_y != YES) {
                    $form->AFF_CREDIT_STATUS->setValue(NumberHelper::format_double($affiliate_credit_status));
                }
                $form->AFF_CREDIT_STATUS_HIDDEN->setValue($affiliate_credit_status);
                $form->PLAYER_CURRENCY->setValue($currency);

                $form->ENABLED->setValue($enabled_auto_increment_y);
                $form->AFF_ID->setValue($affiliate_id);
            }
        }
    }

    //add credits to player from cashier completed
    public function addCreditsToPlayerFromCashierCompletedAction()
    {
        $player_id = $this->_getParam('player_id', 0);
        if ($player_id == 0 || is_numeric($player_id) == false || !isset($player_id)) {
            $locale = Zend_Registry::get("lang");
            $this->redirect($locale . "/financial-transaction/deposit-list");
        }
        require_once MODELS_DIR . DS . 'TransferCreditModel.php';
        require_once FORMS_DIR . DS . 'financial_transaction' . DS . 'for_touchscreen' . DS . 'TransferCreditFromCashierToPlayerTerminalCompletedForm.php';
        $form = new TransferCreditFromCashierToPlayerTerminalCompletedForm();
        $form->SUBMIT->setLabel($this->translate->_("Done"));
        //fill in form initial data

        $arrData = TransferCreditModel::listDirectPlayersTerminalsForCreditTransfer($this->session_id, 5, $player_id, 1, 1000);
        $data = $arrData["table"][0];
        $affiliate_id = $data['cashier_id'];
        $affiliate_username = $data['cashier_name'];
        $currency = $data['affiliate_currency'];
        $player_username = $data['player_name'];
        $affiliate_credit_status = $data['cashier_balance'];
        $player_credits = $data['player_credits'];
        $autoincrement_amount = $data['cashier_auto_incr'];
        if($autoincrement_amount != ""){
            $enabled_auto_increment_y = YES;
        }else{
            $enabled_auto_increment_y = NO;
        }

        $form->DIRECT_PLAYER_NAME->setValue($player_username);
        $form->PLAYER_CREDIT_STATUS->setValue(NumberHelper::format_double($player_credits));
        $form->AFF_NAME->setValue($affiliate_username);
        //find affiliate credit status
        $form->AFF_CREDIT_STATUS->setValue(NumberHelper::format_double($affiliate_credit_status));
        if ($enabled_auto_increment_y == YES) {
            //if affiliate is autoincrement enabled
            $form->removeElement('AFF_CREDIT_STATUS');// then remove affiliate balance
        }
        $this->view->form = $form;
        if ($this->getRequest()->isPost()) {
            if ($this->getRequest()->getParam('SUBMIT', false)) {
                $locale = Zend_Registry::get("lang");
                $this->redirect($locale . "/financial-transaction/deposit-list");
            }
        }
    }

    //add credits from cashier to terminal
    public function addCreditsToTerminalFromCashierAction()
    {
        $player_id = $this->_getParam('player_id', 0);
        if ($player_id == 0 || is_numeric($player_id) == false || !isset($player_id)) {
            $locale = Zend_Registry::get("lang");
            $this->redirect($locale . "/financial-transaction/deposit-list");
        }
        require_once MODELS_DIR . DS . 'TransferCreditModel.php';
        //ako nije postovana forma
        if (!$this->getRequest()->isPost()) {
            $status = TransferCreditModel::disableCreditTransfer($this->session_id);
            if ($status['status'] != 1) {
                $locale = Zend_Registry::get("lang");
                $this->redirect($locale . "/financial-transaction/deposit-list");
            }
        }
        require_once FORMS_DIR . DS . 'financial_transaction' . DS . 'for_touchscreen' . DS . 'TransferCreditFromCashierToPlayerTerminalForm.php';
        $form = new TransferCreditFromCashierToPlayerTerminalForm();
        $form->SUBMIT->setValue($this->translate->_("Add"));

        //fill in form with player informations
        $arrData = TransferCreditModel::listDirectPlayersTerminalsForCreditTransfer($this->session_id, 4, $player_id, 1, 1000);
        $data = $arrData["table"][0];
        $affiliate_id = $data['cashier_id'];
        $affiliate_username = $data['cashier_name'];
        $currency = $data['affiliate_currency'];
        $player_username = $data['player_name'];
        $affiliate_credit_status = $data['cashier_balance'];
        $player_credits = $data['player_credits'];
        $autoincrement_amount = $data['cashier_auto_incr'];
        if($autoincrement_amount != ""){
            $enabled_auto_increment_y = YES;
        }else{
            $enabled_auto_increment_y = NO;
        }

        $form->AFF_NAME->setValue($affiliate_username);
        $form->PLAYER_CURRENCY->setValue($currency);
        $form->DIRECT_PLAYER_NAME->setValue($player_username);
        $form->AFF_CREDIT_STATUS->setValue(NumberHelper::format_double($affiliate_credit_status));
        $form->AFF_CREDIT_STATUS_HIDDEN->setValue($affiliate_credit_status);
        $form->PLAYER_CREDIT_STATUS->setValue(NumberHelper::format_double($player_credits));
        $form->PLAYER_CREDIT_STATUS_HIDDEN->setValue($player_credits);
        $form->ENABLED->setValue($enabled_auto_increment_y);
        $form->AFF_ID->setValue($affiliate_id);
        $form->POSSIBLE_AMOUNT->setValue($autoincrement_amount);

        if ($enabled_auto_increment_y == YES) {
            //if affiliate is autoincrement enabled
            $form->removeElement('AFF_CREDIT_STATUS');// then remove affiliate balance
        }
        $this->view->form = $form;
        //post form and try credit transfer
        if ($this->getRequest()->isPost()) {
            if ($this->getRequest()->getParam('CANCEL', false)) {
                $locale = Zend_Registry::get("lang");
                $this->redirect($locale . "/financial-transaction/deposit-list");
            }
            $formData = $this->getRequest()->getPost();
            if ($form->isValidPartial($formData)) {
                //if form is posted successfully
                $aff_id = $form->getValue('AFF_ID'); //if data is correct try transfer
                $amount = $form->getValue('TRANSFER_AMOUNT_HIDDEN');
                $credit_status = $form->getValue('AFF_CREDIT_STATUS_HIDDEN');
                $player_credit_status = $form->getValue('TERMINAL_CREDIT_STATUS_HIDDEN');
                $possible_amount = $form->getValue('POSSIBLE_AMOUNT');
                $enabled = $form->getValue('ENABLED');
                $currency = $_SESSION['auth_space']['session']['currency'];
                if ($amount <= 0) //value of transfer is 0 or negative value
                    $this->view->message = $this->translate->_("AmountNegativeValueNotAllowed"); //error message
                else {
                    if ($amount <= $credit_status) {
                        //amount is smaller or equal to credit status of affiliate
                        $possible_amount = 0; //is yes than transfer credits to terminal - transfer credit in line bellow
                        TransferCreditModel::transferAffiliateToPlayer($this->session_id, $aff_id, $player_id, $amount, $credit_status, $possible_amount, $enabled, $currency);
                        $locale = Zend_Registry::get("lang");
                        $this->redirect($locale . "/financial-transaction/add-credits-to-terminal-from-cashier-completed/player_id/" . $player_id);
                    } else { //entered value is larger of amount than affiliate has
                        if ($enabled == YES && $amount <= $possible_amount) {
                            //if enabled autoincrement
                            //try credit transfer above limit on affiliate account, only if there is enabled autoincrement
                            TransferCreditModel::transferAffiliateToPlayer($this->session_id, $aff_id, $player_id, $amount, $credit_status, $possible_amount, $enabled, $currency);
                            $locale = Zend_Registry::get("lang");
                            $this->redirect($locale . "/financial-transaction/add-credits-to-terminal-from-cashier-completed/player_id/" . $player_id);
                        } else { //transfer amount larger than affiliate credit status is not possible
                            //cannot transfer credits load form with initial data

                            $arrData = TransferCreditModel::listDirectPlayersTerminalsForCreditTransfer($this->session_id, 4, $player_id, 1, 1000);
                            $data = $arrData["table"][0];
                            $affiliate_id = $data['cashier_id'];
                            $affiliate_username = $data['cashier_name'];
                            $currency = $data['affiliate_currency'];
                            $player_username = $data['player_name'];
                            $affiliate_credit_status = $data['cashier_balance'];
                            $player_credits = $data['player_credits'];
                            $autoincrement_amount = $data['cashier_auto_incr'];
                            if($autoincrement_amount != ""){
                                $enabled_auto_increment_y = YES;
                            }else{
                                $enabled_auto_increment_y = NO;
                            }

                            $form->AFF_NAME->setValue($affiliate_username);
                            $form->PLAYER_CURRENCY->setValue($currency);
                            $form->DIRECT_PLAYER_NAME->setValue($player_username);
                            if ($enabled_auto_increment_y != YES) {
                                $form->AFF_CREDIT_STATUS->setValue(NumberHelper::convert_double($affiliate_credit_status));
                            }
                            $form->AFF_CREDIT_STATUS_HIDDEN->setValue($affiliate_credit_status);
                            $form->PLAYER_CREDIT_STATUS->setValue(NumberHelper::format_double($player_credits));
                            $form->PLAYER_CREDIT_STATUS_HIDDEN->setValue($player_credits);
                            $form->ENABLED->setValue($enabled_auto_increment_y);
                            $form->AFF_ID->setValue($affiliate_id);
                            $form->POSSIBLE_AMOUNT->setValue($autoincrement_amount);
                            $this->view->message = $this->translate->_("LargerAmountEntered");
                        }
                    }
                }
            } else { //form is not posted successfully, data not in correct format or no data available
                $player_id = $this->_getParam('player_id', 0);
                if ($player_id == 0 || is_numeric($player_id) == false || !isset($player_id)) {
                    $locale = Zend_Registry::get("lang");
                    $this->redirect($locale . "/financial-transaction/deposit-list");
                }
                //fill form with initial data

                $arrData = TransferCreditModel::listDirectPlayersTerminalsForCreditTransfer($this->session_id, 4, $player_id, 1, 1000);
                $data = $arrData["table"][0];
                $affiliate_id = $data['cashier_id'];
                $affiliate_username = $data['cashier_name'];
                $currency = $data['affiliate_currency'];
                $player_username = $data['player_name'];
                $affiliate_credit_status = $data['cashier_balance'];
                $player_credits = $data['player_credits'];
                $autoincrement_amount = $data['cashier_auto_incr'];
                if($autoincrement_amount != ""){
                    $enabled_auto_increment_y = YES;
                }else{
                    $enabled_auto_increment_y = NO;
                }

                $form->AFF_NAME->setValue($affiliate_username);
                $form->PLAYER_CURRENCY->setValue($currency);
                $form->DIRECT_PLAYER_NAME->setValue($player_username);
                if ($enabled_auto_increment_y != YES) {
                    $form->AFF_CREDIT_STATUS->setValue(NumberHelper::convert_double($affiliate_credit_status));
                }
                $form->AFF_CREDIT_STATUS_HIDDEN->setValue($affiliate_credit_status);
                $form->PLAYER_CREDIT_STATUS->setValue(NumberHelper::format_double($player_credits));
                $form->PLAYER_CREDIT_STATUS_HIDDEN->setValue($player_credits);
                $form->ENABLED->setValue($enabled_auto_increment_y);
                $form->AFF_ID->setValue($affiliate_id);
                $form->POSSIBLE_AMOUNT->setValue($autoincrement_amount);
            }
        }
    }

    //add credits from cashier to terminal completed form
    public function addCreditsToTerminalFromCashierCompletedAction()
    {
        $player_id = $this->_getParam('player_id', 0);
        if ($player_id == 0 || is_numeric($player_id) == false || !isset($player_id)) {
            $locale = Zend_Registry::get("lang");
            $this->redirect($locale . "/financial-transaction/deposit-list");
        }
        require_once MODELS_DIR . DS . 'TransferCreditModel.php';
        require_once FORMS_DIR . DS . 'financial_transaction' . DS . 'for_touchscreen' . DS . 'TransferCreditFromCashierToPlayerTerminalCompletedForm.php';
        $form = new TransferCreditFromCashierToPlayerTerminalCompletedForm();
        $form->SUBMIT->setLabel($this->translate->_("Done"));

        $arrData = TransferCreditModel::listDirectPlayersTerminalsForCreditTransfer($this->session_id, 4, $player_id, 1, 1000);
        $data = $arrData["table"][0];
        $affiliate_id = $data['cashier_id'];
        $affiliate_username = $data['cashier_name'];
        $currency = $data['affiliate_currency'];
        $player_username = $data['player_name'];
        $affiliate_credit_status = $data['cashier_balance'];
        $player_credits = $data['player_credits'];
        $autoincrement_amount = $data['cashier_auto_incr'];
        if($autoincrement_amount != ""){
            $enabled_auto_increment_y = YES;
        }else{
            $enabled_auto_increment_y = NO;
        }

        $form->DIRECT_PLAYER_NAME->setValue($player_username);
        $form->PLAYER_CREDIT_STATUS->setValue(NumberHelper::format_double($player_credits));
        $form->AFF_NAME->setValue($affiliate_username);
        if ($enabled_auto_increment_y != YES) {
            $form->AFF_CREDIT_STATUS->setValue(NumberHelper::format_double($affiliate_credit_status));
        }else{
            //if affiliate is autoincrement enabled
            $form->removeElement('AFF_CREDIT_STATUS');// then remove affiliate balance
        }
        $this->view->form = $form;
        if ($this->getRequest()->isPost()) {
            if ($this->getRequest()->getParam('SUBMIT', false)) {
                $locale = Zend_Registry::get("lang");
                $this->redirect($locale . "/financial-transaction/deposit-list");
            }
        }
    }

    //lists players and terminal when cashier login into backoffice
    public function withdrawListAction()
    {
        $rola = $_SESSION['auth_space']['session']['subject_type_name']; //rola
        $shift_status = NO; //shift for ShiftCashierS has not started
        if ($rola == ROLA_AD_SHIFT_CASHIER_S || $rola == ROLA_AD_SHIFT_CASHIER_W) {
            require_once MODELS_DIR . DS . 'CashiersCollectorsModel.php';
            $session_id = $_SESSION['auth_space']['session']['session_out'];
            $arrCheckShift = CashiersCollectorsModel::checkOpenShifts($session_id);
            $shift_status = $arrCheckShift['shift_status'];
        }
        $flag_player_credit_transfer = true;
        if( in_array($rola, array(ROLA_AD_SHIFT_CASHIER_S, ROLA_AD_SHIFT_CASHIER_W)) && $shift_status == YES){
            $flag_player_credit_transfer = true;
        }
        else{
            if( in_array($rola, array(ROLA_AD_SHIFT_CASHIER_S, ROLA_AD_SHIFT_CASHIER_W))  && $shift_status == NO){
                $flag_player_credit_transfer = false;
            }
        }
        //check shift opened for shift cashiers
        if($flag_player_credit_transfer == false){
			$this->logVisitedPageError();
			$url = $_SERVER['HTTP_REFERER'];
			$this->redirect($url);
		}

        $total_pages = 1;
        $pageNo = 1;
        $perPage = 1000000;
        $arrData = array();

        require_once MODELS_DIR . DS . 'TransferCreditModel.php';
        if ($pageNo == 1) {
            $arrData = TransferCreditModel::listDirectPlayersTerminalsForCreditTransfer($this->session_id, 0, 0, $pageNo, $perPage);
            $total_items = $arrData["info"][0]["cnt"];
            $total_pages = ceil($total_items / $perPage);
        } else {
            if ($pageNo >= $total_pages) {
                $arrData = TransferCreditModel::listDirectPlayersTerminalsForCreditTransfer($this->session_id, 0, 0, $pageNo, $perPage);
                $total_items = $arrData["info"][0]["cnt"];
                $total_pages = ceil($total_items / $perPage);
            }
        }
        if (count($arrData["table"]) == 0) {
            $pageNo = 1;
            $arrData = TransferCreditModel::listDirectPlayersTerminalsForCreditTransfer($this->session_id, 0, 0, $pageNo, $perPage);
            $total_items = $arrData["info"][0]["cnt"];
            $total_pages = ceil($total_items / $perPage);
        }
        $report = array();
        foreach($arrData["table"] as $data){
            $report[] = array(
                "player_id"=> $data['player_id'],
                "player_name"=> $data['player_name'],
                "player_credits"=> $data['player_credits'],
                "affiliate_id"=> $data['cashier_id'],
                "currency"=> $data['affiliate_currency'],
                "rola"=> $data['rola']
            );
        }

        CursorToArrayHelper::aasort($report, 'rola', 'DESC');

        $this->view->paginator = $report;
    }

    //terminal payout from cashier form
    public function terminalPayoutToCashierAction()
    {
        $player_id = $this->_getParam('player_id', 0);
        if ($player_id == 0 || is_numeric($player_id) == false || !isset($player_id)) {
            $locale = Zend_Registry::get("lang");
            $this->redirect($locale . "/financial-transaction/withdraw-list");
        }
        require_once MODELS_DIR . DS . 'TransferCreditModel.php';
        if (!$this->getRequest()->isPost()) {
            $status = TransferCreditModel::disableCreditTransfer($this->session_id);
            if ($status['status'] != 1) {
                $locale = Zend_Registry::get("lang");
                $this->redirect($locale . "/financial-transaction/withdraw-list");
            }
        }
        require_once FORMS_DIR . DS . 'financial_transaction' . DS . 'for_touchscreen' . DS . 'TransferCreditFromPlayerToCashierForm.php';
        $form = new TransferCreditFromPlayerToCashierForm();
        $form->SUBMIT->setValue($this->translate->_("Payout"));

        //fill in terminal player details and show form with this data

        $arrData = TransferCreditModel::listDirectPlayersTerminalsForCreditTransfer($this->session_id, 4, $player_id, 1, 1000);
        $data = $arrData["table"][0];
        $affiliate_id = $data['cashier_id'];
        $affiliate_username = $data['cashier_name'];
        $currency = $data['affiliate_currency'];
        $player_username = $data['player_name'];
        $affiliate_credit_status = $data['cashier_balance'];
        $player_credits = $data['player_credits'];
        $autoincrement_amount = $data['cashier_auto_incr'];
        if($autoincrement_amount != ""){
            $enabled_auto_increment_y = YES;
        }else{
            $enabled_auto_increment_y = NO;
        }

        $form->DIRECT_PLAYER_NAME->setValue($player_username);
        $form->PLAYER_CREDIT_STATUS->setValue(NumberHelper::format_double($player_credits));
        $form->PLAYER_CREDIT_STATUS_HIDDEN->setValue($player_credits);
        $form->AFF_NAME->setValue($affiliate_username);
        $form->PLAYER_CURRENCY->setValue($currency);
        $form->AFF_CREDIT_STATUS->setValue(NumberHelper::format_double($affiliate_credit_status));
        if ($player_credits == 0) { //player credit status is equal to zero and transfer is not possible remove submit button
            $form->removeElement("SUBMIT");
            $form->removeElement("SUBMIT_PAYOUT_ALL");
            $form->removeElement("CLEAR");
        }
        //$form->TRANSFER_AMOUNT->setValue(NumberHelper::format_double($player_credits));
        ////$form->TRANSFER_AMOUNT_HIDDEN->setValue(NumberHelper::convert_double($player_credits));

        $form->AFF_CREDIT_STATUS_HIDDEN->setValue($affiliate_credit_status);

        $form->ENABLED->setValue($enabled_auto_increment_y);
        $form->AFF_ID->setValue($affiliate_id);
        if ($enabled_auto_increment_y == YES) {
            //if affiliate is autoincrement enabled
            $form->removeElement('AFF_CREDIT_STATUS');// then remove affiliate balance
        }
        $this->view->form = $form;
        if ($this->getRequest()->isPost()) {
            //post form and validate form data
            if ($this->getRequest()->getParam('CANCEL', false)) {
                //press cancel button to quit credit transfer
                $locale = Zend_Registry::get("lang");//list terminal player for credit payout
                $this->redirect($locale . "/financial-transaction/withdraw-list");
            }
            $formData = $this->getRequest()->getPost();
            if ($form->isValidPartial($formData)) {
                //form is posted successfully
                $aff_id = $form->getValue('AFF_ID');
                $amount = $form->getValue('TRANSFER_AMOUNT_HIDDEN');
                $currency = $_SESSION['auth_space']['session']['currency'];
                if ($amount > $player_credits) //entered value is larger than amount on players account
                    $this->view->message = $this->translate->_("LargerAmountTerminalPayoutEntered");
                else { //entered value is smaller or equal than amount on players account
                    if ($amount <= 0) //value is equal or less than zero - negative, this is not allowed
                        $this->view->message = $this->translate->_("AmountNegativeValueNotAllowed");
                    else {
                        $err = 0; //no error occured so far flag
                        try {
                            //DOES PLAYER PAYOUT HERE
                            TransferCreditModel::playerPayout($this->session_id, $aff_id, $player_id, $amount, $currency);
                        } catch (Zend_Exception $ex) {
                            $err = 1; //there was error flag
                            $this->view->message = CursorToArrayHelper::getExceptionTraceAsString($ex);
                        }
                        if ($err == 0) {
                            //no error occured, credit transfer was success
                            $locale = Zend_Registry::get("lang");
                            $this->redirect($locale . "/financial-transaction/terminal-payout-to-cashier-completed/player_id/" . $player_id);
                        }
                    }
                }
            } else { //form not posted show details
                $player_id = $this->_getParam('player_id', 0);
                if ($player_id == 0 || is_numeric($player_id) == false || !isset($player_id)) {
                    $locale = Zend_Registry::get("lang");
                    $this->redirect($locale . "/transfer-credit/withdraw-list");
                }

                $arrData = TransferCreditModel::listDirectPlayersTerminalsForCreditTransfer($this->session_id, 4, $player_id, 1, 1000);
                $data = $arrData["table"][0];
                $affiliate_id = $data['cashier_id'];
                $affiliate_username = $data['cashier_name'];
                $currency = $data['affiliate_currency'];
                $player_username = $data['player_name'];
                $affiliate_credit_status = $data['cashier_balance'];
                $player_credits = $data['player_credits'];
                $autoincrement_amount = $data['cashier_auto_incr'];
                if($autoincrement_amount != ""){
                    $enabled_auto_increment_y = YES;
                }else{
                    $enabled_auto_increment_y = NO;
                }

                $form->DIRECT_PLAYER_NAME->setValue($player_username);
                $form->PLAYER_CREDIT_STATUS->setValue(NumberHelper::format_double($player_credits));
                $form->PLAYER_CREDIT_STATUS_HIDDEN->setValue($player_credits);
                $form->AFF_NAME->setValue($affiliate_username);
                if ($enabled_auto_increment_y != YES) {
                    $form->AFF_CREDIT_STATUS->setValue(NumberHelper::convert_double($affiliate_credit_status));
                }
                $form->PLAYER_CURRENCY->setValue($currency);
                $form->ENABLED->setValue($enabled_auto_increment_y);
                $form->AFF_ID->setValue($affiliate_id);
            }
        }
    }

    //terminal add credits from cashier completed form
    public function terminalPayoutToCashierCompletedAction()
    {
        $player_id = $this->_getParam('player_id', 0);
        if ($player_id == 0 || is_numeric($player_id) == false || !isset($player_id)) {
            $locale = Zend_Registry::get("lang");
            $this->redirect($locale . "/financial-transaction/withdraw-list");
        }
        require_once FORMS_DIR . DS . 'financial_transaction' . DS . 'for_touchscreen' . DS . 'TransferCreditFromPlayerToCashierCompletedForm.php';
        $form = new TransferCreditFromPlayerToCashierCompletedForm();
        $form->SUBMIT->setLabel($this->translate->_("Done"));
        require_once MODELS_DIR . DS . 'TransferCreditModel.php';
        //fill in form with initial data

        $arrData = TransferCreditModel::listDirectPlayersTerminalsForCreditTransfer($this->session_id, 4, $player_id, 1, 1000);
        $data = $arrData["table"][0];
        $affiliate_id = $data['cashier_id'];
        $affiliate_username = $data['cashier_name'];
        $currency = $data['affiliate_currency'];
        $player_username = $data['player_name'];
        $affiliate_credit_status = $data['cashier_balance'];
        $player_credits = $data['player_credits'];
        $autoincrement_amount = $data['cashier_auto_incr'];
        if($autoincrement_amount != ""){
            $enabled_auto_increment_y = YES;
        }else{
            $enabled_auto_increment_y = NO;
        }

        $form->DIRECT_PLAYER_NAME->setValue($player_username);
        $form->PLAYER_CREDIT_STATUS->setValue(NumberHelper::format_double($player_credits));
        $form->AFF_NAME->setValue($affiliate_username);
        $form->AFF_CREDIT_STATUS->setValue(NumberHelper::format_double($affiliate_credit_status));
        if ($enabled_auto_increment_y == YES) {
            //if affiliate is autoincrement enabled
            $form->removeElement('AFF_CREDIT_STATUS');// then remove affiliate balance
        }
        $this->view->form = $form;
        if ($this->getRequest()->isPost()) {
            if ($this->getRequest()->getParam('SUBMIT', false)) {
                $locale = Zend_Registry::get("lang");
                $this->redirect($locale . "/financial-transaction/withdraw-list");
            }
        }
    }

    //payout player terminals to cashier
    public function playerPayoutToCashierAction()
    {
        $player_id = $this->_getParam('player_id', 0);
        if ($player_id == 0 || is_numeric($player_id) == false || !isset($player_id)) {
            $locale = Zend_Registry::get("lang");
            $this->redirect($locale . "/financial-transaction/withdraw-list");
        }
        require_once MODELS_DIR . DS . 'TransferCreditModel.php';
        //ako nije postovana forma
        if (!$this->getRequest()->isPost()) {
            $status = TransferCreditModel::disableCreditTransfer($this->session_id);
            if ($status['status'] != 1) {
                $locale = Zend_Registry::get("lang");
                $this->redirect($locale . "/financial-transaction/withdraw-list");
            }
        }
        require_once FORMS_DIR . DS . 'financial_transaction' . DS . 'for_touchscreen' . DS . 'TransferCreditFromPlayerToCashierForm.php';
        $form = new TransferCreditFromPlayerToCashierForm();
        $form->SUBMIT->setValue($this->translate->_("Payout"));

        //fills in form with player information and affiliate that does credit transfer

        $arrData = TransferCreditModel::listDirectPlayersTerminalsForCreditTransfer($this->session_id, 5, $player_id, 1, 1000);
        $data = $arrData["table"][0];
        $affiliate_id = $data['cashier_id'];
        $affiliate_username = $data['cashier_name'];
        $currency = $data['affiliate_currency'];
        $player_username = $data['player_name'];
        $affiliate_credit_status = $data['cashier_balance'];
        $player_credits = $data['player_credits'];
        $autoincrement_amount = $data['cashier_auto_incr'];
        if($autoincrement_amount != ""){
            $enabled_auto_increment_y = YES;
        }else{
            $enabled_auto_increment_y = NO;
        }

        $form->DIRECT_PLAYER_NAME->setValue($player_username);
        $form->PLAYER_CREDIT_STATUS->setValue(NumberHelper::format_double($player_credits));
        $form->PLAYER_CREDIT_STATUS_HIDDEN->setValue($player_credits);
        $form->AFF_NAME->setValue($affiliate_username);
        $form->PLAYER_CURRENCY->setValue($currency);
        $form->AFF_CREDIT_STATUS->setValue(NumberHelper::format_double($affiliate_credit_status));
        if ($player_credits == 0){
            $form->removeElement("SUBMIT");
            $form->removeElement("SUBMIT_PAYOUT_ALL");
            $form->removeElement("CLEAR");
        }
        $form->TRANSFER_AMOUNT_HIDDEN->setValue(NumberHelper::convert_double($player_credits));
        $form->TRANSFER_AMOUNT->setValue(NumberHelper::format_double($player_credits));
        $form->AFF_CREDIT_STATUS_HIDDEN->setValue($affiliate_credit_status);
        $form->ENABLED->setValue($enabled_auto_increment_y);
        $form->AFF_ID->setValue($affiliate_id);
        if ($enabled_auto_increment_y == YES) {
            //if affiliate is autoincrement enabled
            $form->removeElement('AFF_CREDIT_STATUS');// then remove affiliate balance
        }
        $this->view->form = $form;
        //form is posted and form is validated
        if ($this->getRequest()->isPost()) {
            if ($this->getRequest()->getParam('CANCEL', false)) {
                //quit if cancel button is pressed
                $locale = Zend_Registry::get("lang");
                $this->redirect($locale . "/financial-transaction/withdraw-list");
            }
            $formData = $this->getRequest()->getPost();
            if ($form->isValidPartial($formData)) {
                //if form is submitted successfully
                $aff_id = $form->getValue('AFF_ID');
                $amount = $form->getValue('TRANSFER_AMOUNT_HIDDEN');
                $currency = $_SESSION['auth_space']['session']['currency'];
                if ($amount > $player_credits) {
                    //if entered value is larger than player has on its account
                    $this->view->message = $this->translate->_("LargerAmountPlayerPayoutEntered"); //show error message for larger credit status transfer
                } else { //if entered value is smaller or equal than player has on its account
                    if ($amount <= 0) //value is negative or equal 0, this is not allowed
                        $this->view->message = $this->translate->_("AmountNegativeValueNotAllowed"); //show error message not allowed 0 or negative value
                    else { //entered value is in range and is integer value and positive, continue credit transfer
                        //DOES PLAYER PAYOUT CREDIT AMOUNT
                        TransferCreditModel::playerPayout($this->session_id, $aff_id, $player_id, $amount, $currency);
                        $locale = Zend_Registry::get("lang");
                        $this->redirect($locale . "/financial-transaction/player-payout-to-cashier-completed/player_id/" . $player_id);
                    }
                }
            } else { //form is not submited successfully
                $player_id = $this->_getParam('player_id', 0);
                if ($player_id == 0 || is_numeric($player_id) == false || !isset($player_id)) {
                    $locale = Zend_Registry::get("lang");
                    $this->redirect($locale . "/financial-transaction/withdraw-list");
                }

                $arrData = TransferCreditModel::listDirectPlayersTerminalsForCreditTransfer($this->session_id, 5, $player_id, 1, 1000);
                $data = $arrData["table"][0];
                $affiliate_id = $data['cashier_id'];
                $affiliate_username = $data['cashier_name'];
                $currency = $data['affiliate_currency'];
                $player_username = $data['player_name'];
                $affiliate_credit_status = $data['cashier_balance'];
                $player_credits = $data['player_credits'];
                $autoincrement_amount = $data['cashier_auto_incr'];
                if($autoincrement_amount != ""){
                    $enabled_auto_increment_y = YES;
                }else{
                    $enabled_auto_increment_y = NO;
                }

                $form->DIRECT_PLAYER_NAME->setValue($player_username);
                $form->PLAYER_CREDIT_STATUS->setValue(NumberHelper::format_double($player_credits));
                $form->PLAYER_CREDIT_STATUS_HIDDEN->setValue($player_credits);
                $form->AFF_NAME->setValue($affiliate_username);
                if ($enabled_auto_increment_y != YES) {
                    $form->AFF_CREDIT_STATUS->setValue(NumberHelper::format_double($affiliate_credit_status));
                }
                $form->PLAYER_CURRENCY->setValue($currency);
                $form->ENABLED->setValue($enabled_auto_increment_y);
                $form->AFF_ID->setValue($affiliate_id);
            }
        }
    }

    //player payout to cashier completed form
    public function playerPayoutToCashierCompletedAction()
    {
        require_once MODELS_DIR . DS . 'TransferCreditModel.php';
        require_once FORMS_DIR . DS . 'financial_transaction' . DS . 'for_touchscreen' . DS . 'TransferCreditFromPlayerToCashierCompletedForm.php';
        $form = new TransferCreditFromPlayerToCashierCompletedForm();
        $form->SUBMIT->setLabel($this->translate->_("Done"));
        $modelTransferCredit = new TransferCreditModel();
        $player_id = $this->_getParam('player_id', 0);
        if ($player_id == 0 || is_numeric($player_id) == false || !isset($player_id)) {
            $locale = Zend_Registry::get("lang");
            $this->redirect($locale . "/financial-transaction/withdraw-list");
        }
        //fill form with initial data

        $arrData = TransferCreditModel::listDirectPlayersTerminalsForCreditTransfer($this->session_id, 5, $player_id, 1, 1000);
        $data = $arrData["table"][0];
        $affiliate_id = $data['cashier_id'];
        $affiliate_username = $data['cashier_name'];
        $currency = $data['affiliate_currency'];
        $player_username = $data['player_name'];
        $affiliate_credit_status = $data['cashier_balance'];
        $player_credits = $data['player_credits'];
        $autoincrement_amount = $data['cashier_auto_incr'];
        if($autoincrement_amount != ""){
            $enabled_auto_increment_y = YES;
        }else{
            $enabled_auto_increment_y = NO;
        }

        $form->DIRECT_PLAYER_NAME->setValue($player_username);
        $form->PLAYER_CREDIT_STATUS->setValue(NumberHelper::format_double($player_credits));
        $form->AFF_NAME->setValue($affiliate_username);
        $form->AFF_CREDIT_STATUS->setValue(NumberHelper::format_double($affiliate_credit_status));
        if ($enabled_auto_increment_y == YES) {
            //if affiliate is autoincrement enabled
            $form->removeElement('AFF_CREDIT_STATUS');// then remove affiliate balance
        }
        $this->view->form = $form;
        if ($this->getRequest()->isPost()) {
            if ($this->getRequest()->getParam('SUBMIT', false)) {
                $locale = Zend_Registry::get("lang");
                $this->redirect($locale . "/financial-transaction/withdraw-list");
            }
        }
    }

}

