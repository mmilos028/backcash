<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'ErrorMailHelper.php';

class AffiliatesJackpotSetupModel{
	public function __construct(){
	}

     //get system pot income value
    /**
     * @return mixed
     * @throws Zend_Exception
     */
	public static function systemPotIncomeValue(){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$LIST_SYSTEM_POT_INCOM(:p_pot_incom_out)');
			$pot_income_out = '';
			$stmt->bindParam(':p_pot_incom_out', $pot_income_out, SQLT_CHR, 10);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("pot_income_out"=>$pot_income_out);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$helperErrorMail = new ErrorMailHelper();
			$helperErrorMail->writeError($message, $message);
			throw new Zend_Exception($message);
		}
	}

    //list pot overview report
    /**
     * @param $session_id
     * @param string $filter
     * @param int $aff_id
     * @param int $page_number
     * @param int $hits_per_page
     * @return mixed
     * @throws Zend_Exception
     */
	public static function listPotOverview($session_id, $filter = '', $aff_id = 0, $page_number = 1, $hits_per_page = 25){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$LIST_AFFILIATES_POT(:p_session_id_in, :p_id_in, :p_subject_type, :p_player_type, :p_filter_in, :p_page_number_in, :p_hits_per_page_in, :p_affiliates_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_id_in', $aff_id);
			require_once MODELS_DIR . DS . 'SubjectTypesModel.php';
			$modelSubjectTypes = new SubjectTypesModel();
			$subject_type = $modelSubjectTypes->getSubjectType('MANAGMENT_TYPES.NAME_IN_AFFILIATES');
			$stmt->bindParam(':p_subject_type', $subject_type);
			$player_type = 0;
			$stmt->bindParam(':p_player_type', $player_type);
            $stmt->bindParam(':p_filter_in', $filter);
			$stmt->bindParam(':p_page_number_in', $page_number);
			$stmt->bindParam(':p_hits_per_page_in', $hits_per_page);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':p_affiliates_out', $cursor);
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

    //list players and terminals for jackpot
    /**
     * @param $session_id
     * @param $affiliate_id
     * @return mixed
     * @throws Zend_Exception
     */
	public static function listPlayersTerminalsForJackpot($session_id, $affiliate_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$LIST_PLAYERS_TERMIN_FOR_JP(:p_session_id_in, :p_aff_id, :p_PLAYERS_list_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_aff_id', $affiliate_id);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':p_PLAYERS_list_out', $cursor);
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


	//lists possible named pots
    /**
     * @param $session_id
     * @param $affiliate_id
     * @return mixed
     * @throws Zend_Exception
     */
	//lists possible named pots
	public static function listPossibleNamedPots($session_id, $affiliate_id, $currency){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$LIST_POSIBLE_NAMED_POTS(:p_session_id_in, :p_aff_id_in, :p_currency_in, :NAMED_POTS)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_aff_id_in', $affiliate_id);
            $stmt->bindParam(':p_currency_in', $currency);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':NAMED_POTS', $cursor);
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

    //get list of terminals players to use in manage jpot for affiliates
    /**
     * @param $session_id
     * @return mixed
     * @throws Zend_Exception
     */
	public static function listTpDomain($session_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.Z$LIST_TP_DOMAIN(:p_session_id_in, :cur_list_tp_domain)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':cur_list_tp_domain', $cursor);
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

    //fills in selected affiliate list for new user form on administration section
    /**
     * @param $session_id
     * @return mixed
     * @throws Zend_Exception
     */
	public static function getAffiliatesForNewUserForm($session_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$SELECT_SUBJECTS(:p_session_id_in, :list_subjects_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':list_subjects_out', $cursor);
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

    //lists named pot affiliates
    /**
     * @param $session_id
     * @param $id
     * @param $page_number
     * @param $per_page
     * @param $order_by
     * @param $sort_order
     * @return mixed
     * @throws Zend_Exception
     */
	public static function listNamedPotAffiliates($session_id, $id, $page_number, $per_page, $order_by = 1, $sort_order = "asc"){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$LIST_NAMED_POT_AFFILIATES(:p_session_id_in, :p_id_in, :p_page_number_in, :p_hits_per_page_in, :p_order_by_in, :p_sort_order_in, :NAMED_POTS_AFF)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_id_in', $id);
			$stmt->bindParam(':p_page_number_in', $page_number);
			$stmt->bindParam(':p_hits_per_page_in', $per_page);
			$stmt->bindParam(':p_order_by_in', $order_by);
			$stmt->bindParam(':p_sort_order_in', $sort_order);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':NAMED_POTS_AFF', $cursor);
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

    //list implementation level affiliates for jackpot specification setup form
    /**
     * @param $session_id
     * @param $named_pot_id
     * @return mixed
     * @throws Zend_Exception
     */
	public static function listImplementationLevelAffiliates($session_id, $named_pot_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$LIST_IMPLEMENTATION_AFF(:p_session_id_in, :p_named_pot_id_in, :implementation_aff)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_named_pot_id_in', $named_pot_id);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':implementation_aff', $cursor);
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

    //update or delete named pot for affiliate
    /**
     * @param $session_id
     * @param $id
     * @param $aff_id
     * @param $aff_implement_id
     * @param $aff_named_pot_id
     * @param $level_bronze_jp_start_value
     * @param $level_silver_jp_start_value
     * @param $level_gold_jp_start_value
     * @param $level_platinum_jp_start_value
     * @param $start_date
     * @param $end_date
     * @param $start_time
     * @param $end_time
     * @param $min_terminals_win
     * @param $min_players_win
     * @param $part_of_game_in_out
     * @param $level_bronze_pct
     * @param $level_silver_pct
     * @param $level_gold_pct
     * @param $level_platinum_pct
     * @param $tp_type
     * @param $player_term_id
     * @param int $win_probability_bronze
     * @param int $win_probability_silver
     * @param int $win_probability_gold
     * @param int $win_probability_platinum
     * @param int $win_whole_pot
     * @param int $bronze_win_price_min_win
     * @param int $silver_win_price_min_win
     * @param int $gold_win_price_min_win
     * @param int $platinum_win_price_min_win
     * @param $min_bet_bronze
     * @param $min_bet_silver
     * @param $min_bet_gold
     * @param $min_bet_platinum
     * @param $action
     * @return int|mixed|string
     * @throws Zend_Exception
     */
	public static function manageNamedPotAffiliates($session_id, $id, $aff_id, $aff_implement_id, $aff_named_pot_id,
	    $level_bronze_jp_start_value, $level_silver_jp_start_value, $level_gold_jp_start_value, $level_platinum_jp_start_value, $start_date, $end_date, $start_time, $end_time,
	    $min_terminals_win, $min_players_win, $part_of_game_in_out, $level_bronze_pct, $level_silver_pct, $level_gold_pct,
	    $level_platinum_pct, $tp_type, $player_term_id,
        $win_probability_bronze = 0, $win_probability_silver = 0, $win_probability_gold = 0, $win_probability_platinum = 0, $win_whole_pot = 0,
        $bronze_win_price_min_win = 0, $silver_win_price_min_win = 0, $gold_win_price_min_win = 0, $platinum_win_price_min_win = 0,
        $min_bet_bronze, $min_bet_silver, $min_bet_gold, $min_bet_platinum,
    $action
    ){
        //DEBUG THIS
        /*
        $message = "CALL MANAGMENT_CORE.M\$MANAGE_NAMED_POT_AFFILIATES(:p_session_id_in = {$session_id}, :p_id_in = {$id}, :p_aff_id_in = {$aff_id}, :p_aff_id_implement_in = {$aff_implement_id},
        :p_aff_named_pot_id_in = {$aff_named_pot_id}, :p_pot_low_level_in = {$level_bronze_jp_start_value},
			 :p_pot_midle_level_in = {$level_silver_jp_start_value}, :p_pot_high_level_in = {$level_gold_jp_start_value} , :p_start_date_in = {$start_date}, :p_end_date_in = {$end_date}, :p_start_time_in = {$start_time}, :p_end_time_in = {$end_time},
			 :p_MIN_NUMBER_TER_POT_in = {$min_terminals_win}, :p_MIN_NUMBER_PL_POT_in = {$min_players_win}, :p_PART_OF_GAME_IN_OUT_in = {$part_of_game_in_out},
			 :p_platinum_level_in = {$level_platinum_jp_start_value}, :p_PLATINUM_LEVEL_PCT_in = {$level_platinum_pct}, :p_GOLD_LEVEL_PCT_in = {$level_gold_pct}, :p_SILVER_LEVEL_PCT_in = {$level_silver_pct}, :p_BRONZE_LEVEL_PCT_in = {$level_bronze_pct},
			 :p_tp_type_in = {$tp_type}, :p_player_term_id_in = {$player_term_id},
			 :p_win_probability_bronze_in = {$win_probability_bronze}, :p_win_probability_silver_in = {$win_probability_silver}, :p_win_probability_gold_in = {$win_probability_gold}, :p_win_probability_platinum_in = {$win_probability_platinum},
			 :p_win_whole_pot_in = {$win_whole_pot},
			 :p_start_bronze_in = {$win_whole_pot}, :p_start_silver_in = {$level_silver_jp_start_value}, :p_start_gold_in = {$level_gold_jp_start_value}, :p_start_platinum_in = {$level_platinum_jp_start_value},
             :p_min_bet_for_bronze_jp_in = {$min_bet_bronze}, :p_min_bet_for_bronze_jp_in = {$min_bet_silver}, :p_min_bet_for_bronze_jp_in = {$min_bet_gold}, :p_min_bet_for_bronze_jp_in = {$min_bet_platinum},
             :p_action_in = {$action})";
        $helperErrorMail = new ErrorMailHelper();
		$helperErrorMail->writeInfo($message, $message);
        */
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$MANAGE_NAMED_POT_AFFILIATES(:p_session_id_in, :p_id_in, :p_aff_id_in, :p_aff_id_implement_in, :p_aff_named_pot_id_in, :p_pot_low_level_in,
			 :p_pot_midle_level_in, :p_pot_high_level_in, :p_start_date_in, :p_end_date_in, :p_start_time_in, :p_end_time_in, :p_MIN_NUMBER_TER_POT_in, :p_MIN_NUMBER_PL_POT_in, :p_PART_OF_GAME_IN_OUT_in,
			 :p_platinum_level_in, :p_PLATINUM_LEVEL_PCT_in, :p_GOLD_LEVEL_PCT_in, :p_SILVER_LEVEL_PCT_in, :p_BRONZE_LEVEL_PCT_in, :p_tp_type_in, :p_player_term_id_in,
			 :p_win_probability_bronze_in, :p_win_probability_silver_in, :p_win_probability_gold_in, :p_win_probability_platinum_in, :p_win_whole_pot_in,
			 :p_start_bronze_in, :p_start_silver_in, :p_start_gold_in, :p_start_platinum_in,
			 :p_min_bet_for_bronze_jp, :p_min_bet_for_silver_jp, :p_min_bet_for_gold_jp, :p_min_bet_for_platinum_jp,
			 :p_action_in)');
            $stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_id_in', $id);
			$stmt->bindParam(':p_aff_id_in', $aff_id);
			$stmt->bindParam(':p_aff_id_implement_in', $aff_implement_id);
			$stmt->bindParam(':p_aff_named_pot_id_in', $aff_named_pot_id);
			$stmt->bindParam(':p_pot_low_level_in', $bronze_win_price_min_win);
			$stmt->bindParam(':p_pot_midle_level_in', $silver_win_price_min_win);
			$stmt->bindParam(':p_pot_high_level_in', $gold_win_price_min_win);
			$stmt->bindParam(':p_start_date_in', $start_date);
			$stmt->bindParam(':p_end_date_in', $end_date);
			$stmt->bindParam(':p_start_time_in', $start_time);
			$stmt->bindParam(':p_end_time_in', $end_time);
			$stmt->bindParam(':p_MIN_NUMBER_TER_POT_in', $min_terminals_win);
			$stmt->bindParam(':p_MIN_NUMBER_PL_POT_in', $min_players_win);
			$stmt->bindParam(':p_PART_OF_GAME_IN_OUT_in', $part_of_game_in_out);
			$stmt->bindParam(':p_platinum_level_in', $platinum_win_price_min_win);
			$stmt->bindParam(':p_PLATINUM_LEVEL_PCT_in', $level_platinum_pct);
			$stmt->bindParam(':p_GOLD_LEVEL_PCT_in', $level_gold_pct);
			$stmt->bindParam(':p_SILVER_LEVEL_PCT_in', $level_silver_pct);
			$stmt->bindParam(':p_BRONZE_LEVEL_PCT_in', $level_bronze_pct);
			$stmt->bindParam(':p_tp_type_in', $tp_type);
			$stmt->bindParam(':p_player_term_id_in', $player_term_id);
            $stmt->bindParam(':p_win_probability_bronze_in', $win_probability_bronze);
            $stmt->bindParam(':p_win_probability_silver_in', $win_probability_silver);
            $stmt->bindParam(':p_win_probability_gold_in', $win_probability_gold);
            $stmt->bindParam(':p_win_probability_platinum_in', $win_probability_platinum);
            $stmt->bindParam(':p_win_whole_pot_in', $win_whole_pot);
            $stmt->bindParam(':p_start_bronze_in', $level_bronze_jp_start_value);
            $stmt->bindParam(':p_start_silver_in', $level_silver_jp_start_value);
            $stmt->bindParam(':p_start_gold_in', $level_gold_jp_start_value);
            $stmt->bindParam(':p_start_platinum_in', $level_platinum_jp_start_value);
            $stmt->bindParam(':p_min_bet_for_bronze_jp', $min_bet_bronze);
            $stmt->bindParam(':p_min_bet_for_silver_jp', $min_bet_silver);
            $stmt->bindParam(':p_min_bet_for_gold_jp', $min_bet_gold);
            $stmt->bindParam(':p_min_bet_for_platinum_jp', $min_bet_platinum);
			$stmt->bindParam(':p_action_in', $action);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return "-11";
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$helperErrorMail = new ErrorMailHelper();
			$helperErrorMail->writeError($message, $message);
			if($ex->getCode() == "20198"){
				return "20198"; //unique constraint
			}else return $ex->getCode();
		}
	}

    //update system pot income
    /**
     * @param $session_id
     * @param $level_in
     * @param $aff_id
     * @param $value
     * @throws Zend_Exception
     */
	public static function updateJackPotBackDoor($session_id, $level_in, $aff_id, $value){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$JACK_POT_BACK_DOOR(:p_session_id_in, :p_level_in, :p_aff_id_in, :p_value_in)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_level_in', $level_in);
			$stmt->bindParam(':p_aff_id_in', $aff_id);
			$stmt->bindParam(':p_value_in', $value);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
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