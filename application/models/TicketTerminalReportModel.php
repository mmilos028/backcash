<?php
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'ErrorMailHelper.php';
class TicketTerminalReportModel{
    public function __construct($config = array()){
    }

    //list ticket terminals report
    /**
     * @param $session_id
     * @param $recycler_id
     * @param $affiliate_id
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
            ErrorMailHelper::writeError($message, $message);
            throw new Zend_Exception($message);
        }
    }

    // Transaction report on affiliate details page
    /**
     * @param $session_id
     * @param $recycler_id
     * @param $affiliate_id
     * @param $page_number
     * @param $per_page
     * @param $start_date
     * @param $end_date
     * @return mixed
     * @throws Zend_Exception
     */
    public static function listAffiliateTransactionReport($session_id, $recycler_id, $affiliate_id, $page_number, $per_page, $start_date, $end_date){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
        $dbAdapter->beginTransaction();
        try{
            $stmt = $dbAdapter->prepare('CALL aff_reports.tt_transaction_report(:p_session_id_in, :p_recycler_id_in, :p_aff_id_in, :p_start_date_in, :p_end_date_in, :p_page_number_in, :p_hits_per_page_in, :cur_totals, :c_list_tt_transactions_out)');
            $stmt->bindParam(':p_session_id_in', $session_id);
            $stmt->bindParam(':p_recycler_id_in', $recycler_id);
            $stmt->bindParam(':p_aff_id_in', $affiliate_id);
            $stmt->bindParam(':p_start_date_in', $start_date);
            $stmt->bindParam(':p_end_date_in', $end_date);
            $stmt->bindParam(':p_page_number_in', $page_number);
            $stmt->bindParam(':p_hits_per_page_in', $per_page);
            $cursorTotal = new Zend_Db_Cursor_Oracle($dbAdapter);
            $stmt->bindCursor(":cur_totals", $cursorTotal);
            $cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
            $stmt->bindCursor(":c_list_tt_transactions_out", $cursor);
            $stmt->execute(null, false);
            $dbAdapter->commit();
            $cursorTotal->execute();
            $cursor->execute();
            $dbAdapter->closeConnection();
            $help = new CursorToArrayHelper($cursor);
			$table = $help->getTableRows();
			$info = $help->getPageRow();
            return array("status"=>OK, "table"=>$table, "info"=>$info, "cursorTotal"=>$cursorTotal);
        }catch(Zend_Exception $ex){
            $dbAdapter->rollBack();
            $dbAdapter->closeConnection();
            $message = CursorToArrayHelper::getExceptionTraceAsString($ex);
            ErrorMailHelper::writeError($message, $message);
            throw new Zend_Exception($message);
        }
    }

    //list recyclers for affiliate to filter transaction report on affiliate details page
    /**
     * @param $session_id
     * @param $affiliate_id
     * @return mixed
     * @throws Zend_Exception
     */
    public static function listRecyclersPerAffiliate($session_id, $affiliate_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
        $dbAdapter->beginTransaction();
        try{
            $stmt = $dbAdapter->prepare('CALL reports_bo.list_of_recyclers_per_aff(:p_session_id_in, :p_aff_id_in, :c_list_of_recyclers)');
            $stmt->bindParam(':p_session_id_in', $session_id);
            $stmt->bindParam(':p_aff_id_in', $affiliate_id);
            $cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
            $stmt->bindCursor(":c_list_of_recyclers", $cursor);
            $stmt->execute(null, false);
            $dbAdapter->commit();
            $cursor->execute();
            $dbAdapter->closeConnection();
            return array("status"=>OK, "cursor"=>$cursor);
        }catch(Zend_Exception $ex){
            $dbAdapter->rollBack();
            $dbAdapter->closeConnection();
            $message = CursorToArrayHelper::getExceptionTraceAsString($ex);
            ErrorMailHelper::writeError($message, $message);
            throw new Zend_Exception($message);
        }
    }

    /*
    [31.05.2016. 7:10:16] ColdSoft SM Oracle - Slavica Milisavljevic: tt_status_report (
      p_ba_id_in subjects.ID%TYPE,
      p_recycler_balance_out OUT NUMBER,
      p_r_total_in_amount_out OUT NUMBER,
      p_r_total_out_amount_out OUT NUMBER,

      p_c_total_in_amount_out OUT NUMBER,
      p_c_total_out_amount_out OUT NUMBER,

      p_cashbox_balance_out OUT NUMBER,
      p_total_r_out OUT NUMBER,
      p_total_rc_out OUT NUMBER,
      p_currency_out OUT currency.ics%TYPE,
      p_last_discharge_date_time OUT VARCHAR2,
      p_amounts_per_banknote_type out sys_refcursor)
[31.05.2016. 7:10:21] ColdSoft SM Oracle - Slavica Milisavljevic: reports_bo
    */
    //list status report
    /**
     * @param $session_id
     * @param $ba_id
     * @return array
     * @throws Zend_Exception
     */
    public static function listStatusReport($session_id, $ba_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
        $dbAdapter->beginTransaction();
        try{
            $stmt = $dbAdapter->prepare('CALL reports_bo.tt_status_report(:p_ba_id_in,
            :p_recycler_balance_out, :p_r_total_in_amount_out, :p_r_total_out_amount_out,
            :p_c_total_in_amount_out, :p_c_total_out_amount_out,
            :p_cashbox_balance_out, :p_total_r_out, :p_total_rc_out, :p_currency_out, :p_last_discharge_date_time,
            :p_amounts_per_banknote_type)');
            $stmt->bindParam(':p_ba_id_in', $ba_id);
            $recycler_balance_out = "";
            $stmt->bindParam(':p_recycler_balance_out', $recycler_balance_out, SQLT_CHR, 255);
            $r_total_in_amount_out = "";
            $stmt->bindParam(':p_r_total_in_amount_out', $r_total_in_amount_out, SQLT_CHR, 255);
            $r_total_out_amount_out = "";
            $stmt->bindParam(':p_r_total_out_amount_out', $r_total_out_amount_out, SQLT_CHR, 255);
            $c_total_in_amount_out = "";
            $stmt->bindParam(':p_c_total_in_amount_out', $c_total_in_amount_out, SQLT_CHR, 255);
            $c_total_out_amount_out = "";
            $stmt->bindParam(':p_c_total_out_amount_out', $c_total_out_amount_out, SQLT_CHR, 255);
            $cashbox_balance_out = "";
            $stmt->bindParam(':p_cashbox_balance_out', $cashbox_balance_out, SQLT_CHR, 255);
            $total_r_out = "";
            $stmt->bindParam(':p_total_r_out', $total_r_out, SQLT_CHR, 255);
            $total_rc_out = "";
            $stmt->bindParam(':p_total_rc_out', $total_rc_out, SQLT_CHR, 255);
            $currency_out = "";
            $stmt->bindParam(':p_currency_out', $currency_out, SQLT_CHR, 255);
            $last_discharge_date_time = "";
            $stmt->bindParam(':p_last_discharge_date_time', $last_discharge_date_time, SQLT_CHR, 255);
            $cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
            $stmt->bindCursor(":p_amounts_per_banknote_type", $cursor);
            $stmt->execute(null, false);
            $dbAdapter->commit();
            $cursor->execute();
            $dbAdapter->closeConnection();
            return array("status"=>OK,
                "recycler_balance"=>$recycler_balance_out, "recycler_total_in_amount"=>$r_total_in_amount_out, "recycler_total_out_amount"=>$r_total_out_amount_out,
                "cashbox_total_in_amount"=>$c_total_in_amount_out, "cashbox_total_out_amount"=>$c_total_out_amount_out,
                "cashbox_balance"=>$cashbox_balance_out, "total_recycler"=>$total_r_out, "total_recycler_cashbox"=>$total_rc_out, "currency"=>$currency_out,
                "last_discharge_date_time"=>$last_discharge_date_time,
                "cursor"=>$cursor);
        }catch(Zend_Exception $ex){
            $dbAdapter->rollBack();
            $dbAdapter->closeConnection();
            $message = CursorToArrayHelper::getExceptionTraceAsString($ex);
            ErrorMailHelper::writeError($message, $message);
            return array("status"=>NOK);
        }
    }
}