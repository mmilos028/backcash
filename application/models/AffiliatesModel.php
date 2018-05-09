<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'ErrorMailHelper.php';

class AffiliatesModel{
	public function __construct(){
	}

    /**
     * @param $session_id
     * @param null $username
     * @return mixed
     * @throws Zend_Exception
     */
    public static function listAffiliatesWithParent($session_id, $username = null){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL REPORTS_BO.list_affiliates_with_parent(:p_session_id_in, :p_name_in, :cur_result)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_name_in', $username);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':cur_result', $cursor);
			$stmt->execute(null, false);
			$dbAdapter->commit();
			$cursor->execute();
			$cursor->free();
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

    /**
     * @param $session_id
     * @param null $sub_roles
     * @return mixed
     * @throws Zend_Exception
     */
    public static function getAllRoles($session_id, $sub_roles = null){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$LIST_ALL_ROLES(:p_session_id_in, :p_subroles_in, :p_sub_roles_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			//p_sub_roles to return all roles is null or equals 'Ad / Collector' | 'Ad / Cashier'
			$stmt->bindParam(':p_subroles_in', $sub_roles);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':p_sub_roles_out', $cursor);
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

    //returns list of roles below logged in affiliate on affiliate forms - new affiliate, update affiliate, etc.
    /**
     * @param $session_id
     * @return mixed
     * @throws Zend_Exception
     */
	public static function getAffiliatesBelowCurrent($session_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$LIST_AFFILIATES_ROLES(:p_session_id_in, :p_sub_roles_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':p_sub_roles_out', $cursor);
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

	//performes search on affiliates
    /**
     * @param $session_id
     * @param int $pageNo
     * @param int $perPage
     * @param int $column
     * @param string $order
     * @param null $username
     * @param null $first_name
     * @param null $last_name
     * @param null $city
     * @param null $country
     * @param null $parent_aff
     * @param null $currency
     * @param string $show_banned
     * @return mixed
     * @throws Zend_Exception
     */
	public static function search($session_id, $pageNo = 1, $perPage = 25, $column = 1, $order = 'asc', $username = null, $first_name = null, $last_name = null, $city = null, $country = null, $parent_aff = null, $currency = null, $show_banned = YES){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		if($currency == '' || is_null($currency))$currency = 'ALL';
		try{
			$stmt = $dbAdapter->prepare('CALL REPORTS.M$SEARCH_USERS(:p_session_id_in, :p_user_name_in, :p_first_name_in, :p_last_name_in, :p_city_in, :p_country_in, :p_parent_aff_in, :p_currency_in, :p_banned_in, :p_subject_type_name, :p_page_number_in, :p_hits_per_page_in, :p_order_by_in, :p_sort_order_in, :list_users_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_user_name_in', $username);
			$stmt->bindParam(':p_first_name_in', $first_name);
			$stmt->bindParam(':p_last_name_in', $last_name);
			$stmt->bindParam(':p_city_in', $city);
			$stmt->bindParam(':p_country_in', $country);
			$stmt->bindParam(':p_parent_aff_in', $parent_aff);
			$stmt->bindParam(':p_currency_in', $currency);
			$stmt->bindParam(':p_banned_in', $show_banned);
			require_once MODELS_DIR . DS . 'SubjectTypesModel.php';
			$subject_type = SubjectTypesModel::getSubjectType('MANAGMENT_TYPES.NAME_IN_AFFILIATES');
            $subject_type = $subject_type['value'];
			$stmt->bindParam(':p_subject_type_name', $subject_type);
			$stmt->bindParam(':p_page_number_in', $pageNo);
			$stmt->bindParam(':p_hits_per_page_in', $perPage);
			$stmt->bindParam(':p_order_by_in', $column);
			$stmt->bindParam(':p_sort_order_in', $order);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':list_users_out', $cursor);
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

	//returns affiliate details
    /**
     * @param $session_id
     * @param $affiliate_id
     * @return mixed
     * @throws Zend_Exception
     */
	public static function getAffiliateDetails($session_id, $affiliate_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$SUBJECT_DETAIL(:p_session_id_in, :p_subject_id_in, :p_subject_detail_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_subject_id_in', $affiliate_id);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':p_subject_detail_out', $cursor);
			$stmt->execute(null, false);
			$dbAdapter->commit();
			$cursor->execute();
			$cursor->free();
			$dbAdapter->closeConnection();
			return array("details"=>$cursor->current());
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			ErrorMailHelper::writeError($message, $message);
			throw new Zend_Exception($message);
		}
	}
	
	//Milan::returns last login
    /**
     * @param $session_id
     * @param $affiliate_id
     * @return mixed
     * @throws Zend_Exception
     */
	public static function getAffiliateDetailsLastLogin($session_id, $affiliate_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$SUBJECT_DETAIL_LAST_LOGIN(:p_session_id_in, :p_subject_id_in, :p_subject_detail_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_subject_id_in', $affiliate_id);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':p_subject_detail_out', $cursor);
			$stmt->execute(null, false);
			$dbAdapter->commit();
			$cursor->execute();
			$cursor->free();
			$dbAdapter->closeConnection();
			return array("details"=>$cursor->current());
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			ErrorMailHelper::writeError($message, $message);
			throw new Zend_Exception($message);
		}
	}

	//returns list of system affiliates or affiliate details if p_id is not 0
    /**
     * @param $session_id
     * @param string $game_pay_back_y_n
     * @param int $p_id
     * @param int $page_number
     * @param int $hits_per_page
     * @param string $banned
     * @return mixed
     * @throws Zend_Exception
     */
	public static function getAffiliates($session_id, $game_pay_back_y_n = NO, $p_id = 0, $page_number = 1, $hits_per_page = 50, $banned = ALL)
    {
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
        $dbAdapter->beginTransaction();
        try {
            $stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$LIST_AFFILIATES_PLAYERS(:p_session_id_in, :p_id_in, :p_subject_type, :p_player_type, :p_baned_y_n, :p_page_number_in, :p_hits_per_page_in, :p_game_pay_back_y_n, :p_affiliates_out)');
            $stmt->bindParam(':p_session_id_in', $session_id);
            $cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
            $stmt->bindParam(':p_id_in', $p_id);
            require_once MODELS_DIR . DS . 'SubjectTypesModel.php';
            $subject_type = SubjectTypesModel::getSubjectType('MANAGMENT_TYPES.NAME_IN_AFFILIATES');
            $subject_type = $subject_type['value'];
            $stmt->bindParam(':p_subject_type', $subject_type);
            $player_type = 0;
            $stmt->bindParam(':p_player_type', $player_type);
            $stmt->bindParam(':p_baned_y_n', $banned);
            $stmt->bindParam(':p_page_number_in', $page_number);
            $stmt->bindParam(':p_hits_per_page_in', $hits_per_page);
            $stmt->bindParam(':p_game_pay_back_y_n', $game_pay_back_y_n);
            $stmt->bindCursor(':p_affiliates_out', $cursor);
            $stmt->execute(null, false);
            $dbAdapter->commit();
            $cursor->execute();
            $cursor->free();
            $dbAdapter->closeConnection();
            $help = new CursorToArrayHelper($cursor);
            $table = $help->getTableRows();
            $info = $help->getPageRow();
            return array("table" => $table, "info" => $info);
        }catch (Zend_Exception $ex) {
            $dbAdapter->rollBack();
            $dbAdapter->closeConnection();
            $message = CursorToArrayHelper::getExceptionTraceAsString($ex);
            ErrorMailHelper::writeError($message, $message);
            throw new Zend_Exception($message);
        }
    }

    //list direct affiliate for affiliate list
    /**
     * @param $session_id
     * @param int $p_id
     * @param int $page_number
     * @param int $hits_per_page
     * @param string $banned
     * @return mixed
     * @throws Zend_Exception
     */
	public static function listDirectAffiliates($session_id, $p_id = 0, $page_number = 1, $hits_per_page = 50, $banned = ALL){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$LIST_DIRECT_AFFILIATES(:p_session_id_in, :p_id_in, :p_subject_type, :p_player_type, :p_baned_y_n, :p_page_number_in, :p_hits_per_page_in, :p_affiliates_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindParam(':p_id_in', $p_id);
			require_once MODELS_DIR . DS . 'SubjectTypesModel.php';
			$subject_type = SubjectTypesModel::getSubjectType('MANAGMENT_TYPES.NAME_IN_AFFILIATES');
            $subject_type = $subject_type['value'];
			$stmt->bindParam(':p_subject_type', $subject_type);
			$player_type = 0;
			$stmt->bindParam(':p_player_type', $player_type);
			$stmt->bindParam(':p_baned_y_n', $banned);
			$stmt->bindParam(':p_page_number_in', $page_number);
			$stmt->bindParam(':p_hits_per_page_in', $hits_per_page);
			$stmt->bindCursor(':p_affiliates_out', $cursor);
			$stmt->execute(null, false);
			$dbAdapter->commit();
			$cursor->execute();
			$cursor->free();
			$dbAdapter->closeConnection();
			$help = new CursorToArrayHelper($cursor);
			$table = $help->getTableRows();
			$info = $help->getPageRow();
			return array("table" => $table, "info" => $info);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			ErrorMailHelper::writeError($message, $message);
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
			ErrorMailHelper::writeError($message, $message);
			throw new Zend_Exception($message);
		}
	}

    //check if parent affiliate is banned
	//if returns Y don't login user to backoffice otherwise returns N and allows user to login
    /**
     * @param $username
     * @return mixed
     * @throws Zend_Exception
     */
	public static function checkParentAffiliateBannedStatus($username){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$CHECK_AFF_FOR_AFF(:p_user_name_in, :y_n_out)');
			$stmt->bindParam(':p_user_name_in', $username);
			$banned_status = YES;
			$stmt->bindParam(':y_n_out', $banned_status, SQLT_CHR, 10);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("banned_status"=>$banned_status);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			ErrorMailHelper::writeError($message, $message);
			throw new Zend_Exception($message);
		}
	}

    /**
     * @param $affiliate_id
     * @param $pin_code
     * @return array
     * @throws Zend_Exception
     */
    public static function setAffiliatePinCode($affiliate_id, $pin_code){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
        $dbAdapter->beginTransaction();
        try{
            $stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.DEFINE_AFFILIATE_PIN_CODE(:p_affiliate_id_in, :p_pin_code, :p_status)');
            $stmt->bindParam(':p_affiliate_id_in', $affiliate_id);
            $stmt->bindParam(':p_pin_code', $pin_code);
            $status = "";
            //Error. Pin code is not send!!!
            //Updated pin for affiliate: (ime aff-a)
            $stmt->bindParam(':p_status', $status, SQLT_CHR, 255);
            $stmt->execute(null, false);
            $dbAdapter->commit();
            $dbAdapter->closeConnection();
            return array("status"=>OK, "message"=>$status);
        }catch(Zend_Exception $ex){
            $dbAdapter->rollBack();
            $dbAdapter->closeConnection();
            $message = CursorToArrayHelper::getExceptionTraceAsString($ex);
            ErrorMailHelper::writeError($message, $message);
            return array("status"=>NOK, "message"=>$message);
        }
    }

    /**
     * @param $affiliate_id
     * @param $language
     * @return mixed
     * @throws Zend_Exception
     */
    public static function defineAffiliateBackofficeLanguage($affiliate_id, $language){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
        $dbAdapter->beginTransaction();
        try{
            $stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.DEFINE_AFFILIATE_BO_LANG(:p_affiliate_id, :p_bo_language, :p_status)');
            $stmt->bindParam(':p_affiliate_id', $affiliate_id);
            $stmt->bindParam(':p_bo_language', $language);
            $status = "";
            $stmt->bindParam(':p_status', $status, SQLT_CHR, 255);
            $stmt->execute();
            $dbAdapter->commit();
            $dbAdapter->closeConnection();
            return array("status"=>OK, "affiliate_id", "language"=>$language, "message"=>$status);
        }catch(Zend_Exception $ex){
            $dbAdapter->rollBack();
            $dbAdapter->closeConnection();
            $message = CursorToArrayHelper::getExceptionTraceAsString($ex);
            ErrorMailHelper::writeError($message, $message);
            throw new Zend_Exception($message);
            //return array("status"=>NOK, "message"=>NOK_EXCEPTION, "error_message"=>$message);
        }
    }

    //list affiliates and show panic status
    /**
     * @param $session_id
     * @param int $page_number
     * @param int $hits_per_page
     * @return mixed
     * @throws Zend_Exception
     */
	public static function getPanicList($session_id, $page_number = 1, $hits_per_page = 50){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$UNDER_PANIC_LIST(:p_session_id_in, :p_page_number_in, :p_hits_per_page_in, :p_order_by_in, :p_sort_order_in, :LIST_AFF_OUT)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_page_number_in', $page_number);
			$stmt->bindParam(':p_hits_per_page_in', $hits_per_page);
            $orderBy = 1;
			$stmt->bindParam(':p_order_by_in', $orderBy);
            $sortOrder = "asc";
			$stmt->bindParam(':p_sort_order_in', $sortOrder);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':LIST_AFF_OUT', $cursor);
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


    /**
     * @param $affiliate_name
     * @param $datum_code
     * @return array
     * @throws Zend_Exception
     */
    public static function setAffiliateDatumCode($affiliate_name, $datum_code){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
        $dbAdapter->beginTransaction();
        try{
            $stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.SET_DOB_AFF_CHECK(:p_aff_name, :p_aff_dob, :p_result)');
            $stmt->bindParam(':p_aff_name', $affiliate_name);
            $stmt->bindParam(':p_aff_dob', $datum_code);
            $status = "";
            $stmt->bindParam(':p_result', $status, SQLT_CHR, 255);
            $stmt->execute(null, false);
            $dbAdapter->commit();
            $dbAdapter->closeConnection();
            return array("status"=>OK, "message"=>$status);
        }catch(Zend_Exception $ex){
            $dbAdapter->rollBack();
            $dbAdapter->closeConnection();
            $message = CursorToArrayHelper::getExceptionTraceAsString($ex);
            ErrorMailHelper::writeError($message, $message);
            return array("status"=>NOK, "message"=>$message);
        }
    }

}