<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'ErrorMailHelper.php';

class AffiliatesSubaffiliateModel{
	public function __construct(){
	}

    //list direct affiliates / administrators for choosen affiliate in affiliate details
    /**
     * @param $session_id
     * @param $affiliate_id
     * @param $subject_type_name
     * @param int $pageNumber
     * @param int $hitsPerPage
     * @param int $columnNo
     * @param string $orderBy
     * @return mixed
     * @throws Zend_Exception
     */
	public static function listDirectAffiliates($session_id, $affiliate_id, $subject_type_name, $pageNumber = 1, $hitsPerPage = 200, $columnNo = 1, $orderBy = 'asc'){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL AFF_REPORTS.M$LIST_AFF_DIRECT_AFF(:p_session_id_in, :p_aff_id_in, :p_subject_type_name_in, :p_page_number_in, :p_hits_per_page_in, :p_order_by_in, :p_sort_order_in, :list_dir_aff)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_aff_id_in', $affiliate_id);
			$stmt->bindParam(':p_subject_type_name_in', $subject_type_name);
			$stmt->bindParam(':p_page_number_in', $pageNumber);
			$stmt->bindParam(':p_hits_per_page_in', $hitsPerPage);
			$stmt->bindParam(':p_order_by_in', $columnNo);
			$stmt->bindParam(':p_sort_order_in', $orderBy);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(":list_dir_aff", $cursor);
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

    //list all affiliates / administrators for choosen affiliate in affiliate details
    /**
     * @param $session_id
     * @param $affiliate_id
     * @param $subject_type_name
     * @param int $pageNumber
     * @param int $hitsPerPage
     * @param int $columnNo
     * @param string $orderBy
     * @return mixed
     * @throws Zend_Exception
     */
	public static function listAllAffiliates($session_id, $affiliate_id, $subject_type_name, $pageNumber = 1, $hitsPerPage = 200, $columnNo = 1, $orderBy = 'asc'){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL AFF_REPORTS.M$LIST_AFF_ALL_AFF(:p_session_id_in, :p_aff_id_in, :p_subject_type_name_in, :p_page_number_in, :p_hits_per_page_in, :p_order_by_in, :p_sort_order_in, :list_dir_aff)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_aff_id_in', $affiliate_id);
			$stmt->bindParam(':p_subject_type_name_in', $subject_type_name);
			$stmt->bindParam(':p_page_number_in', $pageNumber);
			$stmt->bindParam(':p_hits_per_page_in', $hitsPerPage);
			$stmt->bindParam(':p_order_by_in', $columnNo);
			$stmt->bindParam(':p_sort_order_in', $orderBy);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(":list_dir_aff", $cursor);
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
}