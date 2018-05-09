<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'ErrorMailHelper.php';

class AffiliatesTransactionsReportModel{
	public function __construct(){
	}

    //lists credits direct report
    /**
     * @param $session_id
     * @param $affiliate_id
     * @param $startdate
     * @param $enddate
     * @param $pageNo
     * @param $perPage
     * @param $column
     * @param $order
     * @return mixed
     * @throws Zend_Exception
     */
	public function creditsDirectReport($session_id, $affiliate_id, $startdate, $enddate, $pageNo, $perPage, $column = 1, $order = 'asc'){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL AFF_REPORTS.M$LIST_CREDIT_TRANSFERS_DIRECT(:p_session_id_in, :p_aff_id_in,' . "to_date(:p_start_time_in, 'DD-Mon-YYYY')," . "to_date(:p_end_time_in, 'DD-Mon-YYYY')" . ', :p_page_number_in, :p_hits_per_page_in, :p_order_by_in, :p_sort_order_in, :p_list_transactions_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_aff_id_in', $affiliate_id);
			$stmt->bindParam(':p_start_time_in', $startdate);
			$stmt->bindParam(':p_end_time_in', $enddate);
			$stmt->bindParam(':p_page_number_in', $pageNo);
			$stmt->bindParam(':p_hits_per_page_in', $perPage);
			$stmt->bindParam(':p_order_by_in', $column);
			$stmt->bindParam(':p_sort_order_in', $order);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(":p_list_transactions_out", $cursor);
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
            ErrorMailHelper::writeError($message, $message);
			throw new Zend_Exception($message);
		}
	}

    //lists credits all report
    /**
     * @param $session_id
     * @param $affiliate_id
     * @param $startdate
     * @param $enddate
     * @param $pageNo
     * @param $perPage
     * @param $column
     * @param $order
     * @return mixed
     * @throws Zend_Exception
     */
	public static function creditsAllReport($session_id, $affiliate_id, $startdate, $enddate, $pageNo, $perPage, $column = 1, $order = 'asc'){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL AFF_REPORTS.M$LIST_CREDIT_TRANSFERS_ALL(:p_session_id_in, :p_aff_id_in,' . "to_date(:p_start_time_in, 'DD-Mon-YYYY')," . "to_date(:p_end_time_in, 'DD-Mon-YYYY')" . ', :p_page_number_in, :p_hits_per_page_in, :p_order_by_in, :p_sort_order_in, :p_list_transactions_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_aff_id_in', $affiliate_id);
			$stmt->bindParam(':p_start_time_in', $startdate);
			$stmt->bindParam(':p_end_time_in', $enddate);
			$stmt->bindParam(':p_page_number_in', $pageNo);
			$stmt->bindParam(':p_hits_per_page_in', $perPage);
			$stmt->bindParam(':p_order_by_in', $column);
			$stmt->bindParam(':p_sort_order_in', $order);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(":p_list_transactions_out", $cursor);
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
			ErrorMailHelper::writeError($message, $message);
			throw new Zend_Exception($message);
		}
	}

    //lists his credit transactions
    /**
     * @param $session_id
     * @param $affiliate_id
     * @param $startdate
     * @param $enddate
     * @param $pageNo
     * @param $perPage
     * @param $column
     * @param $order
     * @return mixed
     * @throws Zend_Exception
     */
	public static function creditTransactionsOwn($session_id, $affiliate_id, $startdate, $enddate, $pageNo, $perPage, $column = 1, $order = 'asc'){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL AFF_REPORTS.M$LIST_CREDIT_TRANSFERS_OWN(:p_session_id_in, :p_aff_id_in,' . "to_date(:p_start_time_in, 'DD-Mon-YYYY')," . "to_date(:p_end_time_in, 'DD-Mon-YYYY')" . ', :p_page_number_in, :p_hits_per_page_in, :p_order_by_in, :p_sort_order_in, :p_list_transactions_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_aff_id_in', $affiliate_id);
			$stmt->bindParam(':p_start_time_in', $startdate);
			$stmt->bindParam(':p_end_time_in', $enddate);
			$stmt->bindParam(':p_page_number_in', $pageNo);
			$stmt->bindParam(':p_hits_per_page_in', $perPage);
			$stmt->bindParam(':p_order_by_in', $column);
			$stmt->bindParam(':p_sort_order_in', $order);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(":p_list_transactions_out", $cursor);
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
			ErrorMailHelper::writeError($message, $message);
			throw new Zend_Exception($message);
		}
	}

    //list credit transfers of current affiliate
    /**
     * @param $session_id
     * @param $affiliate_id
     * @param $startdate
     * @param $enddate
     * @param $pageNo
     * @param $perPage
     * @param $column
     * @param $order
     * @return mixed
     * @throws Zend_Exception
     */
	public static function creditTransfersToHim($session_id, $affiliate_id, $startdate, $enddate, $pageNo, $perPage, $column = 1, $order = 'asc'){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL AFF_REPORTS.M$LIST_CREDIT_TRANSFERS_TO(:p_session_id_in, :p_aff_id_in,' . "to_date(:p_start_time_in, 'DD-Mon-YYYY')," . "to_date(:p_end_time_in, 'DD-Mon-YYYY')" . ', :p_page_number_in, :p_hits_per_page_in, :p_order_by_in, :p_sort_order_in, :p_list_transactions_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_aff_id_in', $affiliate_id);
			$stmt->bindParam(':p_start_time_in', $startdate);
			$stmt->bindParam(':p_end_time_in', $enddate);
			$stmt->bindParam(':p_page_number_in', $pageNo);
			$stmt->bindParam(':p_hits_per_page_in', $perPage);
			$stmt->bindParam(':p_order_by_in', $column);
			$stmt->bindParam(':p_sort_order_in', $order);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(":p_list_transactions_out", $cursor);
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

    //list credit transfers of current affiliate
    /**
     * @param $session_id
     * @param $affiliate_id
     * @param $startdate
     * @param $enddate
     * @param $pageNo
     * @param $perPage
     * @param int $column
     * @param string $order
     * @return mixed
     * @throws Zend_Exception
     */
	public static function creditTransfersFromHim($session_id, $affiliate_id, $startdate, $enddate, $pageNo, $perPage, $column = 1, $order = 'asc'){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL AFF_REPORTS.M$LIST_CREDIT_TRANSFERS_FROM(:p_session_id_in, :p_aff_id_in,' . "to_date(:p_start_time_in, 'DD-Mon-YYYY')," . "to_date(:p_end_time_in, 'DD-Mon-YYYY')" . ', :p_page_number_in, :p_hits_per_page_in, :p_order_by_in, :p_sort_order_in, :p_list_transactions_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_aff_id_in', $affiliate_id);
			$stmt->bindParam(':p_start_time_in', $startdate);
			$stmt->bindParam(':p_end_time_in', $enddate);
			$stmt->bindParam(':p_page_number_in', $pageNo);
			$stmt->bindParam(':p_hits_per_page_in', $perPage);
			$stmt->bindParam(':p_order_by_in', $column);
			$stmt->bindParam(':p_sort_order_in', $order);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(":p_list_transactions_out", $cursor);
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
			ErrorMailHelper::writeError($message, $message);
			throw new Zend_Exception($message);
		}
	}
}