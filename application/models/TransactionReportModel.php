<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'ErrorMailHelper.php';

class TransactionReportModel{
	public function __construct(){
	}

    //list credit transfer report
    /**
     * @param $session_id
     * @param $start_date
     * @param $end_date
     * @param $page_number
     * @param $hits_per_page
     * @param $transaction_type_name
     * @param $subject_name
     * @return mixed
     * @throws Zend_Exception
     */
	public static function getListCreditTransfers($session_id, $start_date, $end_date, $page_number, $hits_per_page, $transaction_type_name, $subject_name){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$rola = $_SESSION['auth_space']['session']['subject_type_name'];
			if($rola != ROLA_AD_COLLECTOR){
				$stmt = $dbAdapter->prepare('CALL REPORTS.M$LIST_CREDIT_TRANSFERS(:p_session_id_in,' . "to_date(:p_start_time_in, 'DD-Mon-YYYY hh24:mi:ss')," . "to_date(:p_end_time_in, 'DD-Mon-YYYY hh24:mi:ss')" . ', :p_page_number_in, :p_hits_per_page_in, :p_reverse_y_n, :p_transaction_types_name_in, :p_subject_name_in, :p_list_transactions_out)');
			}else{
				if($_SESSION['auth_space']['session']['last_time_collect'] != ""){
					$start_date = $_SESSION['auth_space']['session']['last_time_collect'];
				}
				$stmt = $dbAdapter->prepare('CALL REPORTS.M$LIST_CREDIT_TRANSFERS(:p_session_id_in,' . "to_date(:p_start_time_in, 'DD-Mon-YYYY hh24:mi:ss')," . "to_date(:p_end_time_in, 'DD-Mon-YYYY hh24:mi:ss')" . ', :p_page_number_in, :p_hits_per_page_in, :p_reverse_y_n, :p_transaction_types_name_in, :p_subject_name_in, :p_list_transactions_out)');
			}
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_start_time_in', $start_date);
			$stmt->bindParam(':p_end_time_in', $end_date);
			$stmt->bindParam(':p_page_number_in', $page_number);
			$stmt->bindParam(':p_hits_per_page_in', $hits_per_page);
            require_once HELPERS_DIR . DS . 'ReverseModeHelper.php';
            $is_reverse_user = ReverseModeHelper::shouldRemoveWithdrawTransactionsInCreditTransfer();
            $is_reverse_user = $is_reverse_user ? 1 : -1;
            $stmt->bindParam(':p_reverse_y_n', $is_reverse_user);
			$stmt->bindParam(':p_transaction_types_name_in', $transaction_type_name);
			$stmt->bindParam(':p_subject_name_in', $subject_name);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':p_list_transactions_out', $cursor);
			$stmt->execute(null, false);
			$dbAdapter->commit();
			$cursor->execute();
			$cursor->free();
			$dbAdapter->closeConnection();
			$help = new CursorToArrayHelper($cursor);
			$table = $help->getTableRows();
			$info = $help->getPageRow();
			return array("table"=>$table, "info"=>$info);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$helperErrorMail = new ErrorMailHelper();
			$helperErrorMail->writeError($message, $message);
			throw new Zend_Exception($message);
		}
	}

    //lists subjects for credit transfers filters
    /**
     * @param $session_id
     * @return mixed
     * @throws Zend_Exception
     */
	public static function listSubjects($session_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL REPORTS.M$LIST_SUBJECTS(:p_session_id_in, :p_list_subject_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':p_list_subject_out', $cursor);
			$stmt->execute(null, false);
			$dbAdapter->commit();
			$cursor->execute();
			$cursor->free();
			$dbAdapter->closeConnection();
			return array("cursor"=>$cursor);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$helperErrorMail = new ErrorMailHelper();
			$helperErrorMail->writeError($message, $message);
			throw new Zend_Exception($message);
		}
	}

    //list transaction types
    /**
     * @param $session_id
     * @return mixed
     * @throws Zend_Exception
     */
	public static function listTransactionTypes($session_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL REPORTS.M$LIST_TRANSACTION_TYPES(:p_session_id_in, :list_transaction_types)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':list_transaction_types', $cursor);
			$stmt->execute(null, false);
			$dbAdapter->commit();
			$cursor->execute();
			$cursor->free();
			$dbAdapter->closeConnection();
			return array("cursor"=>$cursor);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$helperErrorMail = new ErrorMailHelper();
			$helperErrorMail->writeError($message, $message);
			throw new Zend_Exception($message);
		}
	}

    //returns tree structure of affiliates on backoffice
    /**
     * @param $session_id
     * @return mixed
     * @throws Zend_Exception
     */
    public static function getTicketTerminalCashierAffiliatesList($session_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
        $dbAdapter->beginTransaction();
        try{
            $stmt = $dbAdapter->prepare('CALL REPORTS_BO.LIST_AFFILIATES_WITH_REC_TRANS(:p_session_id_in, :list_affiliates_out)');
            $stmt->bindParam(':p_session_id_in', $session_id);
            $cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
            $stmt->bindCursor(":list_affiliates_out", $cursor);
            $stmt->execute(null, false);
            $dbAdapter->commit();
            $cursor->execute();
            $cursor->free();
            $dbAdapter->closeConnection();
            return array("cursor"=>$cursor);
        }catch(Zend_Exception $ex){
            $dbAdapter->rollBack();
            $dbAdapter->closeConnection();
            $message = CursorToArrayHelper::getExceptionTraceAsString($ex);
            $helperErrorMail = new ErrorMailHelper();
            $helperErrorMail->writeError($message, $message);
            throw new Zend_Exception($message);
        }
    }

    /**
     * @param $session_id
     * @return mixed
     * @throws Zend_Exception
     */
    public static function listShopDTypes($session_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
        $dbAdapter->beginTransaction();
        try{
            $stmt = $dbAdapter->prepare('CALL REPORTS_BO.LIST_SHOP_DTYPES(:p_session_id_in, :cur_result)');
            $stmt->bindParam(':p_session_id_in', $session_id);
            $cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
            $stmt->bindCursor(":cur_result", $cursor);
            $stmt->execute(null, false);
            $dbAdapter->commit();
            $cursor->execute();
            $dbAdapter->closeConnection();
            return array("cursor"=>$cursor);
        }catch(Zend_Exception $ex){
            $dbAdapter->rollBack();
            $dbAdapter->closeConnection();
            $message = CursorToArrayHelper::getExceptionTraceAsString($ex);
            $helperErrorMail = new ErrorMailHelper();
            $helperErrorMail->writeError($message, $message);
            throw new Zend_Exception($message);
        }
    }

    /**
     * @param $session_id
     * @param $affiliate_id
     * @param $currency
     * @param $shop_type
     * @param $start_date
     * @param $end_date
     * @param int $page_no
     * @param int $per_page
     * @return mixed
     * @throws Zend_Exception
     */
    public static function listAffiliateShopOrders($session_id, $affiliate_id, $currency, $shop_type, $start_date, $end_date, $page_no = 1, $per_page = 1000000){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
        $dbAdapter->beginTransaction();
        try{
            $stmt = $dbAdapter->prepare('CALL REPORTS_BO.LIST_AFF_SHOP_ORDERS(:p_session_id_in, :p_aff_id_in, :p_currency_in,' . "to_date(:p_start_date_in, 'DD-Mon-YYYY')" . ", to_date(:p_end_date_in, 'DD-Mon-YYYY')" . ', :p_page_number_in, :p_hits_per_page_in, :p_shop_dtype_in, :cur_result)');
            $stmt->bindParam(':p_session_id_in', $session_id);
            $stmt->bindParam(':p_aff_id_in', $affiliate_id);
            $stmt->bindParam(':p_currency_in', $currency);
            $stmt->bindParam(':p_start_date_in', $start_date);
            $stmt->bindParam(':p_end_date_in', $end_date);
            $stmt->bindParam(':p_page_number_in', $page_no);
            $stmt->bindParam(':p_hits_per_page_in', $per_page);
            $stmt->bindParam(':p_shop_dtype_in', $shop_type);
            $cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
            $stmt->bindCursor(":cur_result", $cursor);
            $stmt->execute(null, false);
            $dbAdapter->commit();
            $cursor->execute();
            $dbAdapter->closeConnection();
            $help = new CursorToArrayHelper($cursor);
            $table = $help->getTableRows();
            $info = $help->getPageRow();
            return array("table"=>$table, "info"=>$info);
        }catch(Zend_Exception $ex){
            $dbAdapter->rollBack();
            $dbAdapter->closeConnection();
            $message = CursorToArrayHelper::getExceptionTraceAsString($ex);
            $helperErrorMail = new ErrorMailHelper();
            $helperErrorMail->writeError($message, $message);
            throw new Zend_Exception($message);
        }
    }

    /**
     * @param $session_id
     * @param $purchase_number
     * @return mixed
     * @throws Zend_Exception
     */
    public static function listPurchaseContent($session_id, $purchase_number){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
        $dbAdapter->beginTransaction();
        try{
            $stmt = $dbAdapter->prepare('CALL REPORTS_BO.LIST_PURCHASE_CONTENT(:p_session_id_in, :p_purchase_number_in, :cur_result)');
            $stmt->bindParam(':p_session_id_in', $session_id);
            $stmt->bindParam(':p_purchase_number_in', $purchase_number);
            $cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
            $stmt->bindCursor(":cur_result", $cursor);
            $stmt->execute(null, false);
            $dbAdapter->commit();
            $cursor->execute();
            $cursor->free();
            $dbAdapter->closeConnection();
            return array("cursor"=>$cursor);
        }catch(Zend_Exception $ex){
            $dbAdapter->rollBack();
            $dbAdapter->closeConnection();
            $message = CursorToArrayHelper::getExceptionTraceAsString($ex);
            $helperErrorMail = new ErrorMailHelper();
            $helperErrorMail->writeError($message, $message);
            throw new Zend_Exception($message);
        }
    }

    //list ticket terminals report
    /**
     * @param $session_id
     * @param $recycler_id
     * @param $affiliate_id
     * @return mixed
     * @throws Zend_Exception
     */
    public static function listTicketTerminalsReport($session_id, $recycler_id, $affiliate_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
        $dbAdapter->beginTransaction();
        try{
            $stmt = $dbAdapter->prepare('CALL reports_bo.list_ticket_terminals(:p_session_id_in, :p_recycler_id, :p_affiliate_id, :cur_list_ticket_terminals)');
            $stmt->bindParam(':p_session_id_in', $session_id);
            $stmt->bindParam(':p_recycler_id', $recycler_id);
            $stmt->bindParam(':p_affiliate_id', $affiliate_id);
            $cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
            $stmt->bindCursor(":cur_list_ticket_terminals", $cursor);
            $stmt->execute(null, false);
            $dbAdapter->commit();
            $cursor->execute();
            $dbAdapter->closeConnection();
            return array("status"=>OK, "cursor"=>$cursor);
        }catch(Zend_Exception $ex){
            $dbAdapter->rollBack();
            $dbAdapter->closeConnection();
            $message = CursorToArrayHelper::getExceptionTraceAsString($ex);
            $helperErrorMail = new ErrorMailHelper();
            $helperErrorMail->writeError($message, $message);
            throw new Zend_Exception($message);
        }
    }

}