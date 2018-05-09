<?php
require_once MODELS_DIR . DS . 'PlayersModel.php';
require_once MODELS_DIR . DS . 'SubjectTypesModel.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'ErrorMailHelper.php';

class TransferCreditModel{
	public function __construct(){
	}

    /**
     * @param $session_id
     * @return array
     * @throws Zend_Exception
     */
	public static function disableCreditTransfer($session_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$DISABLE_CREDIT_TRANSFER(:p_session_id_in, :p_send_one_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$send_one = "0";
			$stmt->bindParam(':p_send_one_out', $send_one, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("status"=>$send_one);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			ErrorMailHelper::writeError($message, $message);
			return array("status"=>0);
		}
	}
	//enable credit transfer to selected player
    /**
     * @param $session_id
     * @return mixed
     * @throws Zend_Exception
     */
	public static function enableCreditTransfer($session_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$ENABLE_CREDIT_TRANSFER(:p_session_id_in)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->execute();
			$dbAdapter->commit();			
			$dbAdapter->closeConnection();
			return array("status"=>true);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			ErrorMailHelper::writeError($message, $message);
			throw new Zend_Exception($message);
		}
	}
	
	//list terminal players with status game open, terminal session	
	//status - active (lobby or game) inactive (not active)
	//state - only if status = active (lobby or game)
    /**
     * @param $session_id
     * @param $order_by
     * @param $sort_order
     * @return mixed
     * @throws Zend_Exception
     */
	public static function listTerminalsWithStatus($session_id, $order_by, $sort_order){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$LIST_DIR_TM_PL_ON_OFF_AC(:p_session_id_in, :p_order_by_in, :p_sort_order_in, :list_direct_pc_players_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_order_by_in', $order_by);
			$stmt->bindParam(':p_sort_order_in', $sort_order);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':list_direct_pc_players_out', $cursor);
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
			ErrorMailHelper::writeError($message, $message);
			throw new Zend_Exception($message);
		}
	}
	
	//list pc players and terminals for credit transfers
    /**
     * @param $session_id
     * @param $page_number
     * @param $hits_per_page
     * @param $order_by
     * @param $sort_order
     * @return mixed
     * @throws Zend_Exception
     */
	public static function listPlayersTerminalsForCreditTransfer($session_id, $page_number, $hits_per_page, $order_by, $sort_order){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();		
		try{
			$stmt = $dbAdapter->prepare('CALL CREDIT_TRANSFER.M$LIST_DIRECT_ALL_PLAYERS(:p_session_id_in, :p_id_in, :p_page_number_in, :p_hits_per_page_in, :p_order_by_in, :p_sort_order_in, :list_direct_pc_players_out)');
			//$stmt = $dbAdapter->prepare('CALL REPORTS_BO.LIST_DIRECT_ALL_PLAYERS(:p_session_id_in, :p_id_in, :p_page_number_in, :p_hits_per_page_in, :p_order_by_in, :p_sort_order_in, :list_direct_pc_players_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$p_id = 0; //for entire list or just one player
			$stmt->bindParam(':p_id_in', $p_id);
			$stmt->bindParam(':p_page_number_in', $page_number);
			$stmt->bindParam(':p_hits_per_page_in', $hits_per_page);
			$stmt->bindParam(':p_order_by_in', $order_by);
			$stmt->bindParam(':p_sort_order_in', $sort_order);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':list_direct_pc_players_out', $cursor);
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
	//list pc player details from parent subaffiliate to load form for credit transfer
    /**
     * @param $session_id
     * @param $affiliate_id
     * @param $player_id
     * @return mixed
     * @throws Zend_Exception
     */
	public static function fillInSubaffiliateToTerminalPlayer($session_id, $affiliate_id, $player_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL CREDIT_TRANSFER.M$LIST_DIRECT_PC_TM_PLYR_AFF(:p_session_id_in, :p_aff_id_in, :p_id_in, :p_player_type_name_in, :p_page_number_in, :p_hits_per_page_in, :p_order_by_in, :p_sort_order_in, :list_direct_pc_players_out)');
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_aff_id_in', $affiliate_id);
			$stmt->bindParam(':p_id_in', $player_id);
			$player_type = SubjectTypesModel::getSubjectType("MANAGMENT_TYPES.NAME_IN_TERMINAL_PLAYER");
            $player_type = $player_type["value"];
			$stmt->bindParam(':p_player_type_name_in', $player_type);
			$page_number = 1;
			$stmt->bindParam(':p_page_number_in', $page_number);
			$hits_per_page = 25;
			$stmt->bindParam(':p_hits_per_page_in', $hits_per_page);
			$order_by = 1;
			$stmt->bindParam(':p_order_by_in', $order_by);
			$sort_order = "asc";
			$stmt->bindParam(':p_sort_order_in', $sort_order);
			$stmt->bindCursor(':list_direct_pc_players_out', $cursor);
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
    /**
     * @param $session_id
     * @param $affiliate_id
     * @param $player_id
     * @return mixed
     * @throws Zend_Exception
     */
	//list pc player details from parent subaffiliate to load form for credit transfer
	public static function fillInSubaffiliateToPlayer($session_id, $affiliate_id, $player_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL CREDIT_TRANSFER.M$LIST_DIRECT_PC_TM_PLYR_AFF(:p_session_id_in, :p_aff_id_in, :p_id_in, :p_player_type_name_in, :p_page_number_in, :p_hits_per_page_in, :p_order_by_in, :p_sort_order_in, :list_direct_pc_players_out)');
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_aff_id_in', $affiliate_id);
			$stmt->bindParam(':p_id_in', $player_id);
			$player_type = SubjectTypesModel::getSubjectType("MANAGMENT_TYPES.NAME_IN_PC_PLAYER");
            $player_type = $player_type["value"];
			$stmt->bindParam(':p_player_type_name_in', $player_type);
			$page_number = 1;
			$stmt->bindParam(':p_page_number_in', $page_number);
			$hits_per_page = 25;
			$stmt->bindParam(':p_hits_per_page_in', $hits_per_page);
			$order_by = 1;
			$stmt->bindParam(':p_order_by_in', $order_by);
			$sort_order = "asc";
			$stmt->bindParam(':p_sort_order_in', $sort_order);
			$stmt->bindCursor(':list_direct_pc_players_out', $cursor);
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
	
	//list terminals for subaffiliate's credit transfers
    /**
     * @param $session_id
     * @param $affiliate_id
     * @param int $page_number
     * @param int $hits_per_page
     * @param int $order_by
     * @param string $sort_order
     * @return mixed
     * @throws Zend_Exception
     */
	public static function getSubaffiliatesTerminalPlayers($session_id, $affiliate_id, $page_number = 1, $hits_per_page = 25, $order_by = 1, $sort_order = 'asc'){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL CREDIT_TRANSFER.M$LIST_DIRECT_PC_TM_PLYR_AFF(:p_session_id_in, :p_aff_id_in, :p_id_in, :p_player_type_name_in, :p_page_number_in, :p_hits_per_page_in, :p_order_by_in, :p_sort_order_in, :list_direct_pc_players_out)');
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_aff_id_in', $affiliate_id);
			$p_id = 0;
			$stmt->bindParam(':p_id_in', $p_id);
			$player_type = SubjectTypesModel::getSubjectType("MANAGMENT_TYPES.NAME_IN_TERMINAL_PLAYER");
            $player_type = $player_type["value"];
			$stmt->bindParam(':p_player_type_name_in', $player_type);
			$stmt->bindParam(':p_page_number_in', $page_number);
			$stmt->bindParam(':p_hits_per_page_in', $hits_per_page);
			$stmt->bindParam(':p_order_by_in', $order_by);
			$stmt->bindParam(':p_sort_order_in', $sort_order);
			$stmt->bindCursor(':list_direct_pc_players_out', $cursor);
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

	//list players for subaffiliate's credit transfers
    /**
     * @param $session_id
     * @param $affiliate_id
     * @param int $page_number
     * @param int $hits_per_page
     * @param int $order_by
     * @param string $sort_order
     * @return mixed
     * @throws Zend_Exception
     */
	public static function getSubaffiliatesPCPlayers($session_id, $affiliate_id, $page_number = 1, $hits_per_page = 1000000, $order_by = 1, $sort_order = 'asc'){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL CREDIT_TRANSFER.M$LIST_DIRECT_PC_TM_PLYR_AFF(:p_session_id_in, :p_aff_id_in, :p_id_in, :p_player_type_name_in, :p_page_number_in, :p_hits_per_page_in, :p_order_by_in, :p_sort_order_in, :list_direct_pc_players_out)');
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_aff_id_in', $affiliate_id);
			$p_id = 0;
			$stmt->bindParam(':p_id_in', $p_id);
			$player_type = SubjectTypesModel::getSubjectType("MANAGMENT_TYPES.NAME_IN_PC_PLAYER");
            $player_type = $player_type["value"];
			$stmt->bindParam(':p_player_type_name_in', $player_type);
			$stmt->bindParam(':p_page_number_in', $page_number);
			$stmt->bindParam(':p_hits_per_page_in', $hits_per_page);
			$stmt->bindParam(':p_order_by_in', $order_by);
			$stmt->bindParam(':p_sort_order_in', $sort_order);
			$stmt->bindCursor(':list_direct_pc_players_out', $cursor);
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
	//subaffiliate payout
    /**
     * @param $session_id
     * @param $aff_id_to
     * @param $aff_id_from
     * @param $amount
     * @param $currency
     * @return mixed
     * @throws Zend_Exception
     */
	public static function subaffiliatePayout($session_id, $aff_id_to, $aff_id_from, $amount, $currency){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
        //DEBUG HERE
		//require_once HELPERS_DIR . DS . 'ErrorMailHelper.php';
		//$errorMailHelper = new ErrorMailHelper();
		//$message = "CREDIT_TRANSFER.M\$TRANS_CREDIT_FROM_SUB_SUBJ(:p_session_id_in = {$session_id}, :p_aff_to_in = {$aff_id_to}, :p_aff_from_in = {$aff_id_from}, :p_amount_in = {$amount}, :p_currency_in = {$currency})";
		//$errorMailHelper->sendMail($message);
		try{
			$stmt = $dbAdapter->prepare('CALL CREDIT_TRANSFER.M$TRANS_CREDIT_FROM_SUB_SUBJ(:p_session_id_in, :p_aff_to_in, :p_aff_from_in, :p_amount_in, :p_currency_in)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			//number of subaffiliate to transfer credits to
			$stmt->bindParam(':p_aff_to_in', $aff_id_to); 
			//number of affiliate that does credit transfer from
			$stmt->bindParam(':p_aff_from_in', $aff_id_from); 
			//transfered amount
			$stmt->bindParam(':p_amount_in', $amount); 
			//currency of transfered amount
			$stmt->bindParam(':p_currency_in', $currency);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			ErrorMailHelper::writeError($message, $message);
			throw new Zend_Exception($message);
		}
	}

	//transfer credits from affiliate to subafffiliate
    /**
     * @param $session_id
     * @param $aff_id_from
     * @param $aff_id_to
     * @param $amount
     * @param $credit_status_aff
     * @param $auto_credit_increment
     * @param $enabled_increment
     * @param $currency
     * @return array
     * @throws Zend_Exception
     */
	public static function transferAffiliateToSubaffiliate($session_id, $aff_id_from, $aff_id_to, $amount, $credit_status_aff, $auto_credit_increment, $enabled_increment, $currency){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		//DEBUG HERE		
		//require_once HELPERS_DIR . DS . 'ErrorMailHelper.php';
		//$errorMailHelper = new ErrorMailHelper();
		//$message = "CREDIT_TRANSFER.M\$TRANSFER_CREDIT_TO_SUB_SUBJ(:p_session_id_in = {$session_id}, :p_aff_from_in = {$aff_id_from}, :p_aff_to_in = {$aff_id_to}, :p_amount_in = {$amount}, :p_credit_status_aff_in = {$credit_status_aff}, :p_auto_credits_increment_in = {$auto_credit_increment}, :p_enabled_auto_increment = {$enabled_increment}, :p_currency_in = {$currency})";
		//$errorMailHelper->sendMail($message);		
		try{
			$stmt = $dbAdapter->prepare('CALL CREDIT_TRANSFER.M$TRANSFER_CREDIT_TO_SUB_SUBJ(:p_session_id_in, :p_aff_from_in, :p_aff_to_in, :p_amount_in, :p_credit_status_aff_in, :p_auto_credits_increment_in, :p_enabled_auto_increment, :p_currency_in)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			//number of affiliate to transfer credits from
			$stmt->bindParam(':p_aff_from_in', $aff_id_from);
			//number of affiliate to transfer credits to
			$stmt->bindParam(':p_aff_to_in', $aff_id_to); 
			//transfer amount to subaffiliate from affiliate
			$stmt->bindParam(':p_amount_in', $amount); 
			//affiliate credit status
			$stmt->bindParam(':p_credit_status_aff_in', $credit_status_aff); 
			//autoincrement value
			$stmt->bindParam(':p_auto_credits_increment_in', $auto_credit_increment); 
			//is autoincrement enabled
			$stmt->bindParam(':p_enabled_auto_increment', $enabled_increment); 
			//currency to transfer credits
			$stmt->bindParam(':p_currency_in', $currency); 
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("status"=>OK);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();			
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			ErrorMailHelper::writeError($message, $message);
			$code = $ex->getCode();
			$translate = Zend_Registry::get('translate');
			if($code == "20224"){
				return array("status"=>NOK_EXCEPTION, "message"=>$translate->_("Autoincrement is set low"));
			}
			else{
				return array("status"=>NOK_EXCEPTION, "message"=>CursorToArrayHelper::getExceptionTraceAsString($ex));
			}
		}
	}

	//return subaffiliate details for credit transfer
    /**
     * @param $session_id
     * @param string $currency_aff_yes
     * @param $currency_in
     * @param int $subaff_id
     * @return mixed
     * @throws Zend_Exception
     */
	public static function fillInAffiliateToSubaffiliate($session_id, $currency_aff_yes = YES, $currency_in, $subaff_id = 0){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL CREDIT_TRANSFER.M$LIST_AFF_FOR_TRANSFER(:p_session_id_in, :p_currency_aff_yes_in, :p_id_in, :p_currency_in, :p_page_number_in, :p_hits_per_page_in, :p_order_by_in, :p_sort_order_in, :p_list_aff_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_currency_aff_yes_in', $currency_aff_yes);
			$stmt->bindParam(':p_id_in', $subaff_id);
			$stmt->bindParam(':p_currency_in', $currency_in);
			$page_number = 1;
			$stmt->bindParam(':p_page_number_in', $page_number);
			$hits_per_page = 25;
			$stmt->bindParam(':p_hits_per_page_in', $hits_per_page);
			$order_by = 1;
			$stmt->bindParam(':p_order_by_in', $order_by);
			$sort_order = 'asc';
			$stmt->bindParam(':p_sort_order_in', $sort_order);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':p_list_aff_out', $cursor);
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

	//list subaffiliates for credit transfers
    /**
     * @param $session_id
     * @param string $currency_aff_yes
     * @param $currency
     * @param int $page_number
     * @param int $hits_per_page
     * @param int $order_by
     * @param string $sort_order
     * @return mixed
     * @throws Zend_Exception
     */
	public static function listAffiliatesForTransfer($session_id, $currency_aff_yes = YES, $currency, $page_number = 1, $hits_per_page = 25, $order_by = 1, $sort_order = 'asc'){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL CREDIT_TRANSFER.M$LIST_AFF_FOR_TRANSFER(:p_session_id_in, :p_currency_aff_yes_in, :p_id_in, :p_currency_in, :p_page_number_in, :p_hits_per_page_in, :p_order_by_in, :p_sort_order_in, :p_list_aff_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_currency_aff_yes_in', $currency_aff_yes);
			$subaff_id = 0;
			$stmt->bindParam(':p_id_in', $subaff_id);
			$stmt->bindParam(':p_currency_in', $currency);
			$stmt->bindParam(':p_page_number_in', $page_number);
			$stmt->bindParam(':p_hits_per_page_in', $hits_per_page);
			$stmt->bindParam(':p_order_by_in', $order_by);
			$stmt->bindParam(':p_sort_order_in', $sort_order);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':p_list_aff_out', $cursor);
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

	//terminal and pc player payout
    /**
     * @param $session_id
     * @param $affiliate_id
     * @param $player_id
     * @param $amount
     * @param $currency
     * @return mixed
     * @throws Zend_Exception
     */
	public static function playerPayout($session_id, $affiliate_id, $player_id, $amount, $currency){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{		
			$stmt = $dbAdapter->prepare('CALL CREDIT_TRANSFER.M$TRANSFER_CREDIT_FROM_DIR_PL(:p_session_id_in, :p_transaction_type_name_in, 
			:p_aff_id_in, :p_player_id_in, :p_amount_in, :p_currency_in,
			:p_apco_transaction_id, :p_credit_card_number_in, :p_credit_card_date_expiried_in,
			:p_credit_card_holder_in, :p_credit_card_country_in, :p_credit_card_type_in,
			:p_start_time, :p_bank_code, :p_ip_address_in, :p_card_bank_issuer_in,
			:p_card_country_ip_in, :p_transaction_id_in, :p_client_email_in, :p_bank_auth_code_in,
			:p_source_in, :p_apco_user_trans_id_out, :p_transaction_id_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$transaction_type = null;
			$stmt->bindParam(':p_transaction_type_name_in', $transaction_type);
			$stmt->bindParam(':p_aff_id_in', $affiliate_id);
			$stmt->bindParam(':p_player_id_in', $player_id);
			$stmt->bindParam(':p_amount_in', $amount);
			$stmt->bindParam(':p_currency_in', $currency);
			$apco_transaction_id = null;
			$stmt->bindParam(':p_apco_transaction_id', $$apco_transaction_id);
			$credit_card_number = null;
			$stmt->bindParam(':p_credit_card_number_in', $credit_card_number);
			$credit_card_expired = null;
			$stmt->bindParam(':p_credit_card_date_expiried_in', $credit_card_expired);
			$credit_card_holder = null;
			$stmt->bindParam(':p_credit_card_holder_in', $credit_card_holder);
			$credit_card_country = null;
			$stmt->bindParam(':p_credit_card_country_in', $credit_card_country);
			$credit_card_type = null;
			$stmt->bindParam(':p_credit_card_type_in', $credit_card_type);
			$start_time = null;
			$stmt->bindParam(':p_start_time', $start_time);
			$bank_code = null;
			$stmt->bindParam(':p_bank_code', $bank_code);
			$ip_address = null;
			$stmt->bindParam(':p_ip_address_in', $ip_address);
			$card_bank_issuer = null;
			$stmt->bindParam(':p_card_bank_issuer_in', $card_bank_issuer);
			$card_country_ip = null;
			$stmt->bindParam(':p_card_country_ip_in', $card_country_ip);
			$transaction_id = null;
			$stmt->bindParam(':p_transaction_id_in', $transaction_id);
			$client_email = null;
			$stmt->bindParam(':p_client_email_in', $client_email);
			$bank_auth_code = null;
			$stmt->bindParam(':p_bank_auth_code_in', $bank_auth_code);
			$source = null;			
			$stmt->bindParam(':p_source_in', $source);
			$apco_user_trans_id_out = "";
			$stmt->bindParam(':p_apco_user_trans_id_out', $apco_user_trans_id_out, SQLT_CHR, 255);
			$transaction_id_out = "";
			$stmt->bindParam(':p_transaction_id_out', $transaction_id_out, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			ErrorMailHelper::writeError($message, $message);
			throw new Zend_Exception($message);
		}
	}

	//list terminals for credit transfers
    /**
     * @param $session_id
     * @param int $page_number
     * @param int $hits_per_page
     * @param int $order_by
     * @param string $sort_order
     * @return mixed
     * @throws Zend_Exception
     */
	public static function getDirectTerminalPlayers($session_id, $page_number = 1, $hits_per_page = 25, $order_by = 1, $sort_order = 'asc'){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{		
			//$stmt = $dbAdapter->prepare('CALL CREDIT_TRANSFER.M$LIST_DIRECT_PC_TM_PLAYERS(:p_session_id_in, :p_id_in, :p_player_type_name_in, :p_page_number_in, :p_hits_per_page_in, :p_order_by_in, :p_sort_order_in, :list_direct_pc_players_out)');
            //$stmt = $dbAdapter->prepare('CALL REPORTS_BO.LIST_DIRECT_PC_TM_PLAYERS(:p_session_id_in, :p_id_in, :p_player_type_name_in, :p_page_number_in, :p_hits_per_page_in, :p_order_by_in, :p_sort_order_in, :list_direct_pc_players_out)');
            //10.03.2016 premestena procedura u ovaj paket
            $stmt = $dbAdapter->prepare('CALL CREDIT_TRANSFER.LIST_DIRECT_PC_TM_PLAYERS(:p_session_id_in, :p_id_in, :p_player_type_name_in, :p_page_number_in, :p_hits_per_page_in, :p_order_by_in, :p_sort_order_in, :list_direct_pc_players_out)');
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindParam(':p_session_id_in', $session_id);
			$p_id = 0;
			$stmt->bindParam(':p_id_in', $p_id);
			$player_type = SubjectTypesModel::getSubjectType("MANAGMENT_TYPES.NAME_IN_TERMINAL_PLAYER");
            $player_type = $player_type["value"];
			$stmt->bindParam(':p_player_type_name_in', $player_type);
			$stmt->bindParam(':p_page_number_in', $page_number);
			$stmt->bindParam(':p_hits_per_page_in', $hits_per_page);
			$stmt->bindParam(':p_order_by_in', $order_by);
			$stmt->bindParam(':p_sort_order_in', $sort_order);
			$stmt->bindCursor(':list_direct_pc_players_out', $cursor);
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

	//list pc players for credit transfers
    /**
     * @param $session_id
     * @param int $page_number
     * @param int $hits_per_page
     * @param int $order_by
     * @param string $sort_order
     * @return mixed
     * @throws Zend_Exception
     */
	public static function getDirectPCPlayers($session_id, $page_number = 1, $hits_per_page = 25, $order_by = 1, $sort_order = 'asc'){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{	
			//$stmt = $dbAdapter->prepare('CALL CREDIT_TRANSFER.M$LIST_DIRECT_PC_TM_PLAYERS(:p_session_id_in, :p_id_in, :p_player_type_name_in, :p_page_number_in, :p_hits_per_page_in, :p_order_by_in, :p_sort_order_in, :list_direct_pc_players_out)');
            $stmt = $dbAdapter->prepare('CALL CREDIT_TRANSFER.LIST_DIRECT_PC_TM_PLAYERS(:p_session_id_in, :p_id_in, :p_player_type_name_in, :p_page_number_in, :p_hits_per_page_in, :p_order_by_in, :p_sort_order_in, :list_direct_pc_players_out)');
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindParam(':p_session_id_in', $session_id);
			$p_id = 0;
			$stmt->bindParam(':p_id_in', $p_id);
			$player_type = SubjectTypesModel::getSubjectType("MANAGMENT_TYPES.NAME_IN_PC_PLAYER");
            $player_type = $player_type["value"];
			$stmt->bindParam(':p_player_type_name_in', $player_type);
			$stmt->bindParam(':p_page_number_in', $page_number);
			$stmt->bindParam(':p_hits_per_page_in', $hits_per_page);
			$stmt->bindParam(':p_order_by_in', $order_by);
			$stmt->bindParam(':p_sort_order_in', $sort_order);
			$stmt->bindCursor(':list_direct_pc_players_out', $cursor);
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

	//list terminal player details from parent affiliate
    /**
     * @param $session_id
     * @param $terminal_player_id
     * @return mixed
     * @throws Zend_Exception
     */
	public static function fillInAffiliateToTerminalPlayer($session_id, $terminal_player_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{		
			//$stmt = $dbAdapter->prepare('CALL CREDIT_TRANSFER.M$LIST_DIRECT_PC_TM_PLAYERS(:p_session_id_in, :p_id_in, :p_player_type_name_in, :p_page_number_in, :p_hits_per_page_in, :p_order_by_in, :p_sort_order_in, :list_direct_pc_players_out)');
            $stmt = $dbAdapter->prepare('CALL CREDIT_TRANSFER.LIST_DIRECT_PC_TM_PLAYERS(:p_session_id_in, :p_id_in, :p_player_type_name_in, :p_page_number_in, :p_hits_per_page_in, :p_order_by_in, :p_sort_order_in, :list_direct_pc_players_out)');
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_id_in', $terminal_player_id);
			$player_type = SubjectTypesModel::getSubjectType("MANAGMENT_TYPES.NAME_IN_TERMINAL_PLAYER");
            $player_type = $player_type["value"];
			$stmt->bindParam(':p_player_type_name_in', $player_type);
			$page_number = 1;
			$stmt->bindParam(':p_page_number_in', $page_number);
			$hits_per_page = 25;
			$stmt->bindParam(':p_hits_per_page_in', $hits_per_page);
			$order_by = 1;
			$stmt->bindParam(':p_order_by_in', $order_by);
			$sort_order = "asc";
			$stmt->bindParam(':p_sort_order_in', $sort_order);
			$stmt->bindCursor(':list_direct_pc_players_out', $cursor);
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

	//list pc player details from parent affiliate
    /**
     * @param $session_id
     * @param $player_id
     * @return mixed
     * @throws Zend_Exception
     */
	public static function fillInAffiliateToPlayer($session_id, $player_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{	
			//$stmt = $dbAdapter->prepare('CALL CREDIT_TRANSFER.M$LIST_DIRECT_PC_TM_PLAYERS(:p_session_id_in, :p_id_in, :p_player_type_name_in, :p_page_number_in, :p_hits_per_page_in, :p_order_by_in, :p_sort_order_in, :list_direct_pc_players_out)');
            $stmt = $dbAdapter->prepare('CALL CREDIT_TRANSFER.LIST_DIRECT_PC_TM_PLAYERS(:p_session_id_in, :p_id_in, :p_player_type_name_in, :p_page_number_in, :p_hits_per_page_in, :p_order_by_in, :p_sort_order_in, :list_direct_pc_players_out)');
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_id_in', $player_id);
			$player_type = SubjectTypesModel::getSubjectType("MANAGMENT_TYPES.NAME_IN_PC_PLAYER");
            $player_type = $player_type["value"];
			$stmt->bindParam(':p_player_type_name_in', $player_type);
			$page_number = 1;
			$stmt->bindParam(':p_page_number_in', $page_number);
			$hits_per_page = 25;
			$stmt->bindParam(':p_hits_per_page_in', $hits_per_page);
			$order_by = 1;
			$stmt->bindParam(':p_order_by_in', $order_by);
			$sort_order = "asc";
			$stmt->bindParam(':p_sort_order_in', $sort_order);
			$stmt->bindCursor(':list_direct_pc_players_out', $cursor);
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

	//transfer credits from affiliate to pc player
    /**
     * @param $session_id
     * @param $aff_id
     * @param $player_id
     * @param $amount
     * @param $credit_status
     * @param $auto_credit_increment
     * @param $enabled_increment
     * @param $currency
     * @return string
     * @return mixed
     * @throws Zend_Exception
     */
	public static function transferAffiliateToPlayer($session_id, $aff_id, $player_id, $amount, $credit_status, $auto_credit_increment, $enabled_increment, $currency){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL CREDIT_TRANSFER.M$TRANSFER_CREDIT_TO_DIR_PL(:p_session_id_in, :p_aff_id_in, :p_player_id_in, :p_amount_in, :p_credit_status_aff_in, :p_auto_credits_increment_in, :p_enabled_auto_increment, :p_currency_in, :p_apco_transaction_id, :p_credit_card_number_in, :p_credit_card_date_expiried_in, :p_credit_card_holder_in, :p_credit_card_country_in, :p_credit_card_type_in, :p_start_time_in, :p_bank_code_in, :p_ip_address_in, :p_CARD_ISSUER_BANK_in, :p_card_country_ip_in, :p_transaction_id_in, :p_client_email_in, :p_BANK_AUTH_CODE_in, :p_source_in, :p_apco_sequence_in, :p_apco_user_trans_id_out, :p_transaction_id_for)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_aff_id_in', $aff_id);
			$stmt->bindParam(':p_player_id_in', $player_id);
			$stmt->bindParam(':p_amount_in', $amount);
			$stmt->bindParam(':p_credit_status_aff_in', $credit_status);
			$stmt->bindParam(':p_auto_credits_increment_in', $auto_credit_increment);
			$stmt->bindParam(':p_enabled_auto_increment', $enabled_increment);
			$stmt->bindParam(':p_currency_in', $currency);
			$apco_transaction_id = null;
			$stmt->bindParam(':p_apco_transaction_id', $apco_transaction_id);
			$credit_card_number = null;
			$stmt->bindParam(':p_credit_card_number_in', $credit_card_number);
			$credit_card_date_expired = null;
			$stmt->bindParam(':p_credit_card_date_expiried_in', $credit_card_date_expired);
			$credit_card_holder = null;
			$stmt->bindParam(':p_credit_card_holder_in', $credit_card_holder);
			$credit_card_country = null;
			$stmt->bindParam(':p_credit_card_country_in', $credit_card_country);
			$credit_card_type = null;
			$stmt->bindParam(':p_credit_card_type_in', $credit_card_type);
			$start_time_in = null;
			$stmt->bindParam(':p_start_time_in', $start_time_in);
			$bank_code = null;
			$stmt->bindParam(':p_bank_code_in', $bank_code);
			$ip_address = null;
			$stmt->bindParam(':p_ip_address_in', $ip_address);
			$card_issuer_bank = null;
			$stmt->bindParam(':p_CARD_ISSUER_BANK_in', $card_issuer_bank);
			$card_country_ip = null;
			$stmt->bindParam(':p_card_country_ip_in', $card_country_ip);
			$transaction_id = null;
			$stmt->bindParam(':p_transaction_id_in', $transaction_id);
			$client_email = null;
			$stmt->bindParam(':p_client_email_in', $client_email);
			$bank_auth_code = null;
			$stmt->bindParam(':p_BANK_AUTH_CODE_in', $bank_auth_code);
			$source = null;
			$stmt->bindParam(':p_source_in', $source);
			$apco_sequence = null;
			$stmt->bindParam(':p_apco_sequence_in', $apco_sequence);
			$apco_transaction_id_out = "";
			$stmt->bindParam(':p_apco_user_trans_id_out', $apco_transaction_id_out, SQLT_CHR, 255);
			$transaction_id_for = null;
			$stmt->bindParam(':p_transaction_id_for', $transaction_id_for, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
            return "";
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			ErrorMailHelper::writeError($message, $message);
			$code = $ex->getCode();
			$translate = Zend_Registry::get('translate');
			if($code == "20200"){
				return $translate->_("Not enough credits. No communication between servers!!!");
			}
			else if($code == "20201"){
				return $translate->_("Limit has been reached!!!");
			}
			else if($code == "20710"){
				//return $this->translate->_("Limit has been reached!!!");				
				$pos1 = strpos($ex->getMessage(), "ORA");
				$pos2 = strpos($ex->getMessage(), "ORA", $pos1 + strlen("ORA"));							
				$message1 = trim(substr($ex->getMessage(), 0, $pos2));				
				$message2 = trim(substr($message1, strpos($message1, "Maximum deposit limit reached!"), strlen($message1)));
				$amount = trim(substr($message2, strlen("Maximum deposit limit reached!"), strlen($message2)));
				return $translate->_("Maximum deposit limit reached!") . " " . sprintf($translate->_('Available credits'), $amount);
			}
			else if($code == "20666"){
				return $translate->_("Unexpected error!");
			}
			else if($code == "20224"){
				return $translate->_("Autoincrement is set low");
			}
			else{
				return CursorToArrayHelper::getExceptionTraceAsString($ex);
			}		
		}
	}

	//find credit status from affiliate and affiliates used currency
    /**
     * @param $session_id
     * @param $currency
     * @return mixed
     * @throws Zend_Exception
     */
	public static function findCreditStatus($session_id, $currency){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{		
			$stmt = $dbAdapter->prepare('CALL CREDIT_TRANSFER.M$FIND_CREDIT_STATUS(:p_session_id_in, :p_currency_in, :p_credits_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_currency_in', $currency);
			$credits = "0.00";
			$stmt->bindParam(':p_credits_out', $credits, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("credits"=>$credits);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			ErrorMailHelper::writeError($message, $message);
			throw new Zend_Exception($message);
		}
	}
	
	//test - list pc players and terminals for credit transfers
    /**
     * @param $session_id
     * @param $page_number
     * @param $hits_per_page
     * @param $order_by
     * @param $sort_order
     * @return mixed
     * @throws Zend_Exception
     */
	public static function listPlayersTerminalsForCreditTransferTEST($session_id, $page_number, $hits_per_page, $order_by, $sort_order){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL REPORTS_BO.LIST_DIRECT_ALL_PLAYERS(:p_session_id_in, :p_id_in, :p_page_number_in, :p_hits_per_page_in, :p_order_by_in, :p_sort_order_in, :list_direct_pc_players_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$p_id = 0; //for entire list or just one player
			$stmt->bindParam(':p_id_in', $p_id);
			$stmt->bindParam(':p_page_number_in', $page_number);
			$stmt->bindParam(':p_hits_per_page_in', $hits_per_page);
			$stmt->bindParam(':p_order_by_in', $order_by);
			$stmt->bindParam(':p_sort_order_in', $sort_order);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':list_direct_pc_players_out', $cursor);
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

    /*
    PROCEDURE list_all_players(
        p_session_id_in            IN csessions.id%TYPE,
       p_player_type_id           IN VARCHAR2,
       p_player_id_in             IN VARCHAR2,
       p_page_number_in           IN NUMBER,
       p_hits_per_page_in         IN NUMBER,
       list_direct_pc_players_out OUT SYS_REFCURSOR)
    */
    /**
     * @param $session_id
     * @param int $player_type_id
     * @param int $player_id
     * @param int $page_number
     * @param int $hits_per_page
     * @return mixed
     * @throws Zend_Exception
     */
    public static function listDirectPlayersTerminalsForCreditTransfer($session_id, $player_type_id = 0, $player_id = 0, $page_number = 1, $hits_per_page = 200){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL CREDIT_TRANSFER.list_all_players(:p_session_id_in, :p_player_type_id, :p_player_id_in, :p_page_number_in, :p_hits_per_page_in, :list_direct_pc_players_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
            $stmt->bindParam(':p_player_type_id', $player_type_id);
			$stmt->bindParam(':p_player_id_in', $player_id);
			$stmt->bindParam(':p_page_number_in', $page_number);
			$stmt->bindParam(':p_hits_per_page_in', $hits_per_page);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':list_direct_pc_players_out', $cursor);
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