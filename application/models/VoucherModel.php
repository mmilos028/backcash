<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'ErrorMailHelper.php';

class VoucherModel{
	
	public function __construct(){}

    /**
     * @param $session_id
     * @return mixed
     * @throws Zend_Exception
     */
	public static function getCurrencies($session_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL SITE_LOGIN.CURRENCY_LIST_NEW_AFFILIATE(:session_id_in, :p_currency_out)');
			$stmt->bindParam(':session_id_in', $session_id);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(":p_currency_out", $cursor);
			$stmt->execute(null, false);
			$dbAdapter->commit();
			$cursor->execute();
			$cursor->free();
			$dbAdapter->closeConnection();
			return array("cursor"=>$cursor);
		}catch(Zend_Db_Cursor_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$cursor = array(0);
			return array("status"=>NOK, "cursor"=>$cursor);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			ErrorMailHelper::writeError($message, $message);
			throw new Zend_Exception($message);
		}	
	}

    /**
     * @param $session_id
     * @param $currency_id
     * @return mixed
     * @throws Zend_Exception
     */
	public static function getAffiliateForCurrency($session_id,$currency_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL PREPAID_CARDS.GET_AFFILIATE_FOR_CURRENCY(:session_id_in, :currency_id_in, :p_affiliate_list_out)');
			$stmt->bindParam(':session_id_in', $session_id);
			$stmt->bindParam(':currency_id_in', $currency_id);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(":p_affiliate_list_out", $cursor);
			$stmt->execute(null, false);
			$dbAdapter->commit();
			$cursor->execute();
			$cursor->free();
			$dbAdapter->closeConnection();
			return array("cursor"=>$cursor);
		}catch(Zend_Db_Cursor_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$cursor = array(0);
			return array("status"=>NOK, "cursor"=>$cursor);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			ErrorMailHelper::writeError($message, $message);
			throw new Zend_Exception($message);
		}
	}

    /**
     * @param $session_id
     * @param $currency_id
     * @param $affiliate_id
     * @return mixed
     * @throws Zend_Exception
     */
	public static function getAffiliateForCurrencyNew($session_id, $currency_id, $affiliate_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL PREPAID_CARDS.GET_AFFILIATE_FOR_CURRENCY_NEW(:session_id_in, :currency_id_in, :affiliate_id_in, :p_affiliate_list_out)');
			$stmt->bindParam(':session_id_in', $session_id);
			$stmt->bindParam(':currency_id_in', $currency_id);
			$stmt->bindParam(':affiliate_id_in', $affiliate_id);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(":p_affiliate_list_out", $cursor);
			$stmt->execute(null, false);
			$dbAdapter->commit();
			$cursor->execute();
			$cursor->free();
			$dbAdapter->closeConnection();
			return array("cursor"=>$cursor);
		}catch(Zend_Db_Cursor_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$cursor = array(0);
			return array("status"=>NOK, "cursor"=>$cursor);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			ErrorMailHelper::writeError($message, $message);
			throw new Zend_Exception($message);
		}
	}

    /**
     * @param $session_id
     * @return mixed
     * @throws Zend_Exception
     */
	public static function getSubjectFromSessionId($session_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL CORE.M$CHECK_ADMIN_SESSION(:session_id_in, :p_affiliate_id_out)');
			$stmt->bindParam(':session_id_in', $session_id);
			$affiliate_id = null;
			$stmt->bindParam(':p_affiliate_id_out', $affiliate_id, SQLT_CHR, 255);
			$stmt->execute(null, false);
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("affiliate_id"=>$affiliate_id);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			ErrorMailHelper::writeError($message, $message);
			throw new Zend_Exception($message);
		}
	}

    /**
     * @param $session_id
     * @param $no_cards
     * @param $amount
     * @param $promo
     * @param $currency
     * @param $affiliate_id
     * @param $expire_date
     * @param $no_of_days
     * @param $activate
     * @param $member_card
     * @param $user_name
     * @param $pass
     * @param $refill_allowed
     * @return mixed
     * @throws Zend_Exception
     */
	public static function createPrepaidCards($session_id,$no_cards,$amount,$promo,$currency,$affiliate_id,$expire_date,$no_of_days,$activate,$member_card,$user_name,$pass,$refill_allowed){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try {
			$stmt = $dbAdapter->prepare('CALL PREPAID_CARDS.CREATE_PREPAID_CARDS(:session_id_in,:no_cards_in,:amount_in,:promo_in,:currency_in,:affiliate_id_in,'. "to_date(:expire_date_in, 'DD.MM.YYYY')" .',:no_of_days_in,:activate_in,:member_card_in,:user_name_in,:pass_in,:refill_allowed_in,:p_out,:p_serial_number_start_out,:p_serial_number_end_out)');
			$stmt->bindParam(':session_id_in', $session_id);
			$stmt->bindParam(':no_cards_in', $no_cards);
			$stmt->bindParam(':amount_in', $amount);
			$stmt->bindParam(':promo_in', $promo);
			$stmt->bindParam(':currency_in', $currency);
			$stmt->bindParam(':affiliate_id_in', $affiliate_id);
			$stmt->bindParam(':expire_date_in', $expire_date);
			$stmt->bindParam(':no_of_days_in', $no_of_days);
			$stmt->bindParam(':activate_in', $activate);
			$stmt->bindParam(':member_card_in', $member_card);
			$stmt->bindParam(':user_name_in', $user_name);
			$stmt->bindParam(':pass_in', $pass);
			$stmt->bindParam(':refill_allowed_in', $refill_allowed);
			$status = null;
			$serial_number_end = 0;
			$serial_number_start = 0;
			$stmt->bindParam(':p_out', $status);
			$stmt->bindParam(':p_serial_number_start_out', $serial_number_start, SQLT_INT);
			$stmt->bindParam(':p_serial_number_end_out', $serial_number_end, SQLT_INT);
			//$stmt->bindParam(':p_serial_number_start_out', $serial_number_start,  SQLT_CHR, 255);
			//$stmt->bindParam(':p_serial_number_end_out', $serial_number_end,  SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("status"=>$status,'serial_number_start'=>$serial_number_start,'serial_number_end'=>$serial_number_end);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			ErrorMailHelper::writeError($message, $message);
			throw new Zend_Exception($message);
		}
	}

    /**
     * @param $session_id
     * @param $from_serial
     * @param $to_serial
     * @return mixed
     * @throws Zend_Exception
     */
	public static function getPrepaidCards($session_id,$from_serial,$to_serial){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL PREPAID_CARDS.LIST_OF_PREPAID_CARDS(:session_id_in, :from_serial_in, :to_serial_in, :c_list_cards)');
			$stmt->bindParam(':session_id_in', $session_id);
			$stmt->bindParam(':from_serial_in', $from_serial);
			$stmt->bindParam(':to_serial_in', $to_serial);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':c_list_cards', $cursor);
			$stmt->execute();
			$dbAdapter->commit();
			$cursor->execute();
			$cursor->free();
			$dbAdapter->closeConnection();
			return array("status"=>OK, "cursor"=>$cursor);
		}catch(Zend_Db_Cursor_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$cursor = array(0);
			return array("status"=>NOK, "cursor"=>$cursor);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			ErrorMailHelper::writeError($message, $message);
			throw new Zend_Exception($message);
		}
	}

    /**
     * @param $session_id
     * @param $serial_from
     * @param $serial_to
     * @param $promo
     * @param $currency
     * @param $affiliate_id
     * @param $expire_date
     * @param $no_of_days
     * @param $status
     * @return mixed
     * @throws Zend_Exception
     */
	public static function updatePrepaidCards($session_id,$serial_from,$serial_to,$promo,$currency,$affiliate_id,$expire_date,$no_of_days,$status){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try {
			//$stmt = $dbAdapter->prepare('CALL PREPAID_CARDS.EDIT_PREPAID_CARDS(:session_id_in, :serial_from_in, :serial_to_in, :affiliate_id_in, :currency_in, :expire_date_in, :promo_in, :status_in, :no_of_days_in, :edit_status_out, :messages_out)');
			$stmt = $dbAdapter->prepare('CALL PREPAID_CARDS.EDIT_PREPAID_CARDS(:session_id_in, :serial_from_in, :serial_to_in, :affiliate_id_in, :currency_in,' . "to_date(:expire_date_in, 'DD.MM.YYYY')" . ', :promo_in, :status_in, :no_of_days_in, :edit_status_out, :messages_out)');
			$stmt->bindParam(':session_id_in', $session_id);
			$stmt->bindParam(':serial_from_in', $serial_from);
			$stmt->bindParam(':serial_to_in', $serial_to);
			$stmt->bindParam(':affiliate_id_in', $affiliate_id);
			$stmt->bindParam(':currency_in', $currency);
			$stmt->bindParam(':expire_date_in', $expire_date);
			$stmt->bindParam(':promo_in', $promo);
			$stmt->bindParam(':status_in', $status);
			$stmt->bindParam(':no_of_days_in', $no_of_days);
			$status_out = "";
			$messages = "";
			$stmt->bindParam(':edit_status_out', $status_out, SQLT_CHR, 255);
			$stmt->bindParam(':messages_out', $messages, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("status"=>$status_out,'error_messages'=>$messages);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			ErrorMailHelper::writeError($message, $message);
			throw new Zend_Exception($message);
		}
	}

    /**
     * @param $session_id
     * @param $page_number
     * @param $rows_per_page
     * @return mixed
     * @throws Zend_Exception
     */
	public static function listPrepaidCards($session_id,$page_number,$rows_per_page){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL PREPAID_CARDS.LIST_PREPAID_CARDS_PER_PAGE(:p_session_id_in,:p_page_number,:p_rows_per_page,:p_total_row_count,:p_prepaid_card_list_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_page_number', $page_number);
			$stmt->bindParam(':p_rows_per_page', $rows_per_page);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$count = null;
			$stmt->bindParam(':p_total_row_count', $count, SQLT_INT);
			$stmt->bindCursor(':p_prepaid_card_list_out', $cursor);
			$stmt->execute();
			$dbAdapter->commit();
			$cursor->execute();
			$cursor->free();
			$dbAdapter->closeConnection();
			return array("status"=>OK, "count"=>$count, "cursor"=>$cursor);
		}catch(Zend_Db_Cursor_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$cursor = array(0);
			return array("status"=>NOK, "cursor"=>$cursor);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			ErrorMailHelper::writeError($message, $message);
			throw new Zend_Exception($message);
		}
	}

    /**
     * @param $session_id
     * @param $page_number
     * @param $rows_per_page
     * @param $serial_number
     * @param $affiliate_owner
     * @param $affiliate_creator
     * @param $used_by_player_id
     * @param $player_id_bound
     * @param $activation_date
     * @param $amount
     * @param $prepaid_code
     * @param $currency
     * @param $refill_type
     * @param $status
     * @param $creation_date
     * @param $used_date
     * @param $username
     * @param $refill_allowed
     * @param $expire_before
     * @param $expire_after
     * @return mixed
     * @throws Zend_Exception
     */
	public static function searchPrepaidCards($session_id,$page_number,$rows_per_page,$serial_number,$affiliate_owner,$affiliate_creator,$used_by_player_id,$player_id_bound,$activation_date,$amount,$prepaid_code,$currency,$refill_type,$status,$creation_date,$used_date,$username,$refill_allowed,$expire_before,$expire_after){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
        $dbAdapter->beginTransaction();
        try{
            //$stmt = $dbAdapter->prepare('CALL PREPAID_CARDS.SEARCH_PREPAID_CARDS(:p_session_id_in,:p_page_number,:p_rows_per_page,:p_serial_number,:p_affiliate_owner,:p_affiliate_creator,:p_used_by_player_id,:p_player_id_bound,:p_activation_date,:p_amount,:p_prepaid_code,:p_currency,:p_refill_type,:p_status,:p_creation_date,:p_used_date,:p_username,:p_refill_allowed,:p_expiry_date_before,:p_expiry_date_after,:p_total_row_count,:p_prepaid_card_list_out)');
            $stmt = $dbAdapter->prepare('CALL PREPAID_CARDS.SEARCH_PREPAID_CARDS(:p_session_id_in,:p_page_number,:p_rows_per_page,:p_serial_number,:p_affiliate_owner,:p_affiliate_creator,:p_used_by_player_id,:p_player_id_bound,:p_activation_date,:p_amount,:p_prepaid_code,:p_currency,:p_refill_type,:p_status,:p_creation_date,:p_used_date,:p_username,:p_refill_allowed,'. "to_date(:p_expiry_date_before, 'DD.MM.YYYY')" .','. "to_date(:p_expiry_date_after, 'DD.MM.YYYY')" .',:p_total_row_count,:p_prepaid_card_list_out)');
            $stmt->bindParam(':p_session_id_in', $session_id);
            $stmt->bindParam(':p_page_number', $page_number);
            $stmt->bindParam(':p_rows_per_page', $rows_per_page);
            $stmt->bindParam(':p_serial_number', $serial_number);
            $stmt->bindParam(':p_affiliate_owner', $affiliate_owner);
            $stmt->bindParam(':p_affiliate_creator', $affiliate_creator);
            $stmt->bindParam(':p_used_by_player_id', $used_by_player_id);
            $stmt->bindParam(':p_player_id_bound', $player_id_bound);
            $stmt->bindParam(':p_activation_date', $activation_date);
            $stmt->bindParam(':p_amount', $amount);
            $stmt->bindParam(':p_prepaid_code', $prepaid_code);
            $stmt->bindParam(':p_currency', $currency);
            $stmt->bindParam(':p_refill_type', $refill_type);
            $stmt->bindParam(':p_status', $status);
            $stmt->bindParam(':p_creation_date', $creation_date);
            $stmt->bindParam(':p_used_date', $used_date);
            $stmt->bindParam(':p_username', $username);
            $stmt->bindParam(':p_refill_allowed', $refill_allowed);
            $stmt->bindParam(':p_expiry_date_before', $expire_before);
            $stmt->bindParam(':p_expiry_date_after', $expire_after);
            $cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
            $count = null;
            $stmt->bindParam(':p_total_row_count', $count, SQLT_INT);
            $stmt->bindCursor(':p_prepaid_card_list_out', $cursor);
            $stmt->execute();
            $dbAdapter->commit();
            $cursor->execute();
            $cursor->free();
            $dbAdapter->closeConnection();
            return array("status"=>OK, "count"=>$count, "cursor"=>$cursor);
        }catch(Zend_Db_Cursor_Exception $ex){
            $dbAdapter->rollBack();
            $dbAdapter->closeConnection();
            $cursor = array(0);
            return array("status"=>NOK, "cursor"=>$cursor);
        }catch(Zend_Exception $ex){
            $dbAdapter->rollBack();
            $dbAdapter->closeConnection();
            $message = CursorToArrayHelper::getExceptionTraceAsString($ex);
            ErrorMailHelper::writeError($message, $message);
            throw new Zend_Exception($message);
        }
	}

    /**
     * @return mixed
     * @throws Zend_Exception
     */
	public static function getAffiliateCreator(){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL PREPAID_CARDS.LIST_OF_AFFILIATE_CREATORS(:p_affiliate_creator_out)');
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':p_affiliate_creator_out', $cursor);
			$stmt->execute();
			$dbAdapter->commit();
			$cursor->execute();
			$cursor->free();
			$dbAdapter->closeConnection();
			return array("status"=>OK, "cursor"=>$cursor);
		}catch(Zend_Db_Cursor_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$cursor = array(0);
			return array("status"=>NOK, "cursor"=>$cursor);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			ErrorMailHelper::writeError($message, $message);
			throw new Zend_Exception($message);
		}
	}

    /**
     * @return mixed
     * @throws Zend_Exception
     */
	public static function getAffiliateOwner(){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL PREPAID_CARDS.LIST_OF_AFFILIATE_OWNERS(:p_affiliate_owner_out)');
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':p_affiliate_owner_out', $cursor);
			$stmt->execute();
			$dbAdapter->commit();
			$cursor->execute();
			$cursor->free();
			$dbAdapter->closeConnection();
			return array("status"=>OK, "cursor"=>$cursor);
		}catch(Zend_Db_Cursor_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$cursor = array(0);
			return array("status"=>NOK, "cursor"=>$cursor);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			ErrorMailHelper::writeError($message, $message);
			throw new Zend_Exception($message);
		}
	}

    /**
     * @return mixed
     * @throws Zend_Exception
     */
	public static function getUsedByPlayer(){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL PREPAID_CARDS.LIST_OF_PLAYERS_USED_BY(:p_used_by_player_out)');
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':p_used_by_player_out', $cursor);
			$stmt->execute();
			$dbAdapter->commit();
			$cursor->execute();
			$cursor->free();
			$dbAdapter->closeConnection();
			return array("status"=>OK, "cursor"=>$cursor);
		}catch(Zend_Db_Cursor_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$cursor = array(0);
			return array("status"=>NOK, "cursor"=>$cursor);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			ErrorMailHelper::writeError($message, $message);
			throw new Zend_Exception($message);
		}
	}
}