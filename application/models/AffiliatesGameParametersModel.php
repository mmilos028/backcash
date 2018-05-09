<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'ErrorMailHelper.php';

class AffiliatesGameParametersModel{
	public function __construct(){
	}

    //copies game parameters for affiliate_id
    /**
     * @param $prototype_name
     * @param $aff_id
     * @return mixed
     * @throws Zend_Exception
     */
	public static function copyGamesParameters($prototype_name, $aff_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$COPY_GAMES_PARAMETERS(:p_prototype_name_in, :p_aff_id_in, :p_prototype_id_in)');
			$stmt->bindParam(':p_prototype_name_in', $prototype_name);
			$stmt->bindParam(':p_aff_id_in', $aff_id);
			$prototype_id = null;
			$stmt->bindParam(':p_prototype_id_in', $prototype_id);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return 0;
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$code = $ex->getCode();
			if($code == 20343){
				return $code;
			}else{
				$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
				$helperErrorMail = new ErrorMailHelper();
				$helperErrorMail->writeError($message, $message);
				throw new Zend_Exception($message);
			}
		}
	}

    //deletes game parameter(s)
    /**
     * @param $session_id
     * @param $game_id
     * @param $selectedParams
     * @param $row_id
     * @param null $subject_id
     * @throws Zend_Exception
     */
	public static function deleteGamesParams($session_id, $game_id, $selectedParams, $row_id, $subject_id = null ){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			for($i=0;$i<count($selectedParams);$i++){
				$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$MANAGE_GAME_PARAMETERS(:p_session_id_in, :p_action_in, :p_game_id_in, :p_parameter_id_in, :p_parameter_id_old_in, :p_parameter_name_new_in, :p_value_in, :p_step_in, :p_subject_id_in)');
				$stmt->bindParam(':p_session_id_in', $session_id);
				$action = DELETE;
				$stmt->bindParam(':p_action_in', $action);
				$stmt->bindParam(':p_game_id_in', $game_id);
				$stmt->bindParam(':p_parameter_id_in', $selectedParams[$i]);
				$stmt->bindParam(':p_parameter_id_old_in', $selectedParams[$i]);
				$parameter_name_new = null;
				$stmt->bindParam(':p_parameter_name_new_in', $parameter_name_new);
				$param_value = null;
				$stmt->bindParam(':p_value_in', $param_value);
				$param_step = null;
				$stmt->bindParam(':p_step_in', $param_step);
				$stmt->bindParam(':p_subject_id_in', $subject_id);
				$stmt->execute();
				//echo "\n MANAGMENT_CORE.M_MANAGE_GAME_PARAMETERS(:p_session_id_in = {$session_id}, :p_action_in = DELETE, :p_game_id_in = {$game_id}, :p_parameter_id_in = {$selectedParams[$i]}, :p_parameter_id_old_in = {$selectedParams[$i]}, :p_parameter_name_new_in = null, :p_value_in = null, :p_step_in = null, :p_subject_id_in = {$subject_id})";
			}
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

    //resets chosen - checked games for affiliate
    /**
     * @param $session_id
     * @param $affiliate_id
     * @param $selectedGames
     * @throws Zend_Exception
     */
	public static function resetGamesForAffiliate($session_id, $affiliate_id, $selectedGames){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			for($i=0;$i<count($selectedGames);$i++){
				$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$RESET_GAME_AFFILIATE(:p_session_id_in, :p_subjects_id_in, :p_game_id_in)');
				$stmt->bindParam(':p_session_id_in', $session_id);
				$stmt->bindParam(':p_subjects_id_in', $affiliate_id);
				$stmt->bindParam(':p_game_id_in', $selectedGames[$i]);
				$stmt->execute();
			}
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

    //copy game parameters for selected game and affiliate from selected affiliate
    /**
     * @param $game_id
     * @param $affiliate_from
     * @param $affiliate_to
     * @return mixed
     * @throws Zend_Exception
     */
	public static function copyGameParametersFromAffiliate($game_id, $affiliate_from, $affiliate_to){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$COPY_GAME_PARAMETERS(:p_game_id_in, :p_aff_id_from_in, :p_aff_id_to_in)');
			$stmt->bindParam(':p_game_id_in', $game_id);
			$stmt->bindParam(':p_aff_id_from_in', $affiliate_from);
			$stmt->bindParam(':p_aff_id_to_in', $affiliate_to);
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

    /**
     * @param $session_id
     * @param $subject_id
     * @param $game_id
     * @return mixed
     * @throws Zend_Exception
     */
    public static function addGameToAffiliate($session_id, $subject_id, $game_id){
    /* @var $dbAdapter Zend_Db_Adapter_Oracle */
	$dbAdapter = Zend_Registry::get('db_auth');
	$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$MANAGE_EXCLUSIVE_AFF(:p_session_id_in, :p_action_in, :p_game_id_in, :p_subject_id_in)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$action = INSERT;
			$stmt->bindParam(':p_action_in', $action);
			$stmt->bindParam(':p_subject_id_in', $subject_id);
			$stmt->bindParam(':p_game_id_in', $game_id);
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

    //get affiliates for game parameters copying
    /**
     * @param $session_id
     * @param $game_id
     * @return mixed
     * @throws Zend_Exception
     */
	public static function listAffiliatesForGameParametersCopy($session_id, $game_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$LIST_GAMES_AFF_FOR_COPY(:p_session_id_in, :p_game_id_in, :list_aff_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_game_id_in', $game_id);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':list_aff_out', $cursor);
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
     * @param $subjects_id
     * @return mixed
     * @throws Zend_Exception
     */
    public static function getGamesForAffiliate($session_id, $subjects_id){
    /* @var $dbAdapter Zend_Db_Adapter_Oracle */
	$dbAdapter = Zend_Registry::get('db_auth');
	$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$LIST_AFF_GAME(:p_session_id_in, :p_subjects_id_in, :p_id_in, :p_games_list_out)');
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_subjects_id_in', $subjects_id);
			$p_id = 0;
			$stmt->bindParam(':p_id_in', $p_id);
			$stmt->bindCursor(':p_games_list_out', $cursor);
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

    //lists game parameters for chosen affiliate
    /**
     * @param $session_id
     * @param $affiliate_id
     * @param $game_id
     * @return mixed
     * @throws Zend_Exception
     */
	public static function listGamesParamsForAffiliates($session_id, $affiliate_id, $game_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$LIST_GAMES_PARAMETERS(:p_session_id_in, :p_game_id_in, :p_id_in, :p_subject_id_in, :list_games_parameters)');
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_game_id_in', $game_id);
			$p_id_in = 0;
			$stmt->bindParam(':p_id_in', $p_id_in);
			$stmt->bindParam(':p_subject_id_in', $affiliate_id);
			$stmt->bindCursor(':list_games_parameters', $cursor);
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

    //Milan::lists game parameters for chosen affiliate with report of all possible paramters for that game
    /**
     * @param $session_id
     * @param $game_id
     * @param null $affiliate_id
     * @return mixed
     * @throws Zend_Exception
     */
	public static function listGamesParamsNEW($session_id, $game_id, $affiliate_id=null){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$LIST_ALL_GAMES_PARAMETERS(:p_session_id_in, :p_game_id_in, :p_id_in, :p_subject_id_in, :list_games_parameters)');
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_game_id_in', $game_id);
			$p_id_in = 0;
			$stmt->bindParam(':p_id_in', $p_id_in);
			$stmt->bindParam(':p_subject_id_in', $affiliate_id);
			$stmt->bindCursor(':list_games_parameters', $cursor);
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
     * @param $game_id
     * @return mixed
     * @throws Zend_Exception
     */
    public static function listParams($session_id, $game_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$LIST_PARAMETERS(:p_session_id_in, :p_game_id_in, :list_parameter_out)');
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_game_id_in', $game_id);
			$stmt->bindCursor(':list_parameter_out', $cursor);
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

    //lists affiliates for copying game settings for affiliate's games
    /**
     * @return mixed
     * @throws Zend_Exception
     */
	public static function listGamesPrototypes(){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$LIST_GAMES_PROTOTYPES(:p_list_prototypes_out)');
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':p_list_prototypes_out', $cursor);
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

    //lists games from chosen affiliate
    /**
     * @param $session_id
     * @param $subject_id
     * @return mixed
     * @throws Zend_Exception
     */
	public static function listGamesForAffiliates($session_id, $subject_id){
    /* @var $dbAdapter Zend_Db_Adapter_Oracle */
	$dbAdapter = Zend_Registry::get('db_auth');
	$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$LIST_GAMES_FOR_AFFILIATE(:p_session_id_in, :p_subjects_id_in, :p_games_list_out)');
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_subjects_id_in', $subject_id);
			$stmt->bindCursor(':p_games_list_out', $cursor);
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

    //inserts game parameter for chosen game for chosen affiliate
    /**
     * @param $session_id
     * @param $param_id
     * @param $param_value
     * @param $param_step
     * @param $game_id
     * @param $affiliate_id
     * @return mixed
     * @throws Zend_Exception
     */
	public static function addGamesParamsForAffiliates($session_id, $param_id, $param_value, $param_step, $game_id, $affiliate_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$MANAGE_GAME_PARAMETERS(:p_session_id_in, :p_action_in, :p_game_id_in, :p_parameter_id_in, :p_parameter_id_old_in, :p_parameter_name_new_in, :p_value_in, :p_step_in, :p_subject_id_in)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$action = INSERT;
			$stmt->bindParam(':p_action_in', $action);
			$stmt->bindParam(':p_game_id_in', $game_id);
			$stmt->bindParam(':p_parameter_id_in', $param_id);
			$parameter_id_old = 0;
			$stmt->bindParam(':p_parameter_id_old_in', $parameter_id_old);
			$parameter_name_new = "";
			$stmt->bindParam(':p_parameter_name_new_in', $parameter_name_new, SQLT_CHR, 255);
			$stmt->bindParam(':p_value_in', $param_value);
			$stmt->bindParam(':p_step_in', $param_step);
			$stmt->bindParam(':p_subject_id_in', $affiliate_id);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("status"=>true);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$helperErrorMail = new ErrorMailHelper();
			$helperErrorMail->writeError($message, $message);
			throw $ex;
		}
	}

    //updates selected game parameters
    /**
     * @param $gameParameters
     * @throws Zend_Exception
     */
	public static function updateGameParameters($gameParameters){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			for($i = 0; $i < count($gameParameters); $i++){
				$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$UPDATE_GAME_PARAMETERS(:p_row_id_in, :p_value_in, :p_step_in)');
				$stmt->bindParam(':p_row_id_in', $gameParameters[$i]['row_id']);
				$stmt->bindParam(':p_value_in', $gameParameters[$i]['value']);
				$stmt->bindParam(':p_step_in', $gameParameters[$i]['step']);
				$stmt->execute();
			}
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