<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'ErrorMailHelper.php';
class AdministratorModel{
	public function __construct(){
	}

    /**
     * @param $subject_id
     * @param $access_code
     * @throws Zend_Exception
     */
	public static function setupAccessCode($subject_id, $access_code){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.SETUP_ACCESS_CODE(:p_subject_id_in, :p_access_code_in)');
			$stmt->bindParam(':p_subject_id_in', $subject_id);
			$stmt->bindParam(':p_access_code_in', $access_code);
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
	
	//new user, player, affiliate, terminal player, other... - MANAGE AFFILIATE
    /**
     * @param $session_id
     * @param $action
     * @param $aff_for
     * @param $name
     * @param $password
     * @param $subrole
     * @param $mac_address
     * @param $email
     * @param $country
     * @param $currency
     * @param $banned
     * @param $zip
     * @param $phone
     * @param $address
     * @param $birthday
     * @param $first_name
     * @param $last_name
     * @param $city
     * @param $subject_id
     * @param $multicurrency
     * @param $autoincrement
     * @param $game_payback
     * @param $key_exit
     * @param $enter_password
     * @param null $street_address2
     * @param null $bank_account
     * @param null $bank_country
     * @param null $swift
     * @param null $iban
     * @param null $receive_mail
     * @param null $inactive_time
     * @param null $site_name
     * @param string $origin_site
     * @param null $registred_affiliate
     * @param null $password_surf
     * @param string $new_login_kills_sess
     * @return mixed
     * @throws Exception
     * @throws Zend_Db_Adapter_Oracle_Exception
     * @throws Zend_Db_Statement_Oracle_Exception
     * @throws Zend_Exception
     */
	public static function manageUser($session_id, $action, $aff_for, $name, $password, $subrole, $mac_address, $email, $country, 
	$currency, $banned, $zip, $phone, $address, $birthday, $first_name, $last_name, $city, $subject_id, $multicurrency, $autoincrement,
	$game_payback, $key_exit, $enter_password, $street_address2 = null, $bank_account = null, $bank_country = null, $swift = null, 
	$iban = null, $receive_mail = null, $inactive_time = null, $site_name = null, $origin_site = DEFAULT_ORIGIN_SITE, $registred_affiliate = null, $password_surf = null,
    $new_login_kills_sess = NO){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		$registred_affiliate = NO;
		/*
		require_once HELPERS_DIR . DS . 'ErrorMailHelper.php';
		$errorMailHelper = new ErrorMailHelper();
		$errorMailHelper->sendMail("MANAGMENT_CORE.M_DOLAR_MANAGE_AFFILIATES(p_session_id_in = $session_id, p_action_in = $action, p_aff_for_in = $aff_for, 
			p_name_new_in = $name, p_password_in = $password, p_affiliates_type_in = $subrole, 
			p_mac_address_in = $mac_address, p_email_in = $email, p_country_in = $country, 
			p_currency_in = $currency, p_banned_in = $banned, p_zip_code_in = $zip, 
			p_phone_in = $phone, p_address_in = $address, p_birthday_in = $birthday, 
			p_first_name_in = $first_name, p_last_name_in = $last_name, p_city_in = $city, 
			p_subject_id_in = $subject_id, p_multi_currency_in = $multicurrency, 
			p_auto_credits_increment_in = $autoincrement, p_pay_back_perc = $game_payback, 
			p_key_exit_in = $key_exit, p_enter_pass_in = $enter_password, p_ADDRESS2_in = $street_address2, 
			p_BANK_ACCOUNT_in = $bank_account, p_BANK_COUNTRY_in = $bank_country, 
			p_SWIFT_in = $swift, p_IBAN_in = $iban, p_send_mail_in = $receive_mail, 
			p_inactive_time_in = $inactive_time, p_site_name_in = $site_name, p_registred_aff = $registred_affiliate, p_origin_in = $origin_site,
		    p_new_login_kills_sess = $new_login_kills_sess, p_subject_id_dummy_out = null)");
		//return array("status"=>OK, "subject_id"=>$subject_id_dummy);
		*/
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$MANAGE_AFFILIATES(:p_session_id_in, :p_action_in, :p_aff_for_in, :p_name_new_in, :p_password_in, :p_password_in_surf, :p_affiliates_type_in, :p_mac_address_in, :p_email_in, :p_country_in, :p_currency_in, :p_banned_in, :p_zip_code_in, :p_phone_in, :p_address_in, :p_birthday_in, :p_first_name_in, :p_last_name_in, :p_city_in, :p_subject_id_in, :p_multi_currency_in, :p_auto_credits_increment_in, :p_pay_back_perc, :p_key_exit_in, :p_enter_pass_in, :p_ADDRESS2_in, :p_BANK_ACCOUNT_in, :p_BANK_COUNTRY_in, :p_SWIFT_in, :p_IBAN_in, :p_send_mail_in, :p_inactive_time_in, :p_site_name_in, :p_registred_aff, :p_origin_in, :p_new_login_kills_sess, :p_subject_id_dummy_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_action_in', $action);
			$stmt->bindParam(':p_aff_for_in', $aff_for);
			$stmt->bindParam(':p_name_new_in', $name);
			$stmt->bindParam(':p_password_in', $password);
			$stmt->bindParam(':p_password_in_surf', $password_surf);     
			$stmt->bindParam(':p_affiliates_type_in', $subrole);
			$stmt->bindParam(':p_mac_address_in', $mac_address);
			$stmt->bindParam(':p_email_in', $email);
			$stmt->bindParam(':p_country_in', $country);
			$stmt->bindParam(':p_currency_in', $currency);
			$stmt->bindParam(':p_banned_in', $banned);
			$stmt->bindParam(':p_zip_code_in', $zip);
			$stmt->bindParam(':p_phone_in', $phone);
			$stmt->bindParam(':p_address_in', $address);
			$stmt->bindParam(':p_birthday_in', $birthday);
			$stmt->bindParam(':p_first_name_in', $first_name);
			$stmt->bindParam(':p_last_name_in', $last_name);
			$stmt->bindParam(':p_city_in', $city);
			$stmt->bindParam(':p_subject_id_in', $subject_id);
			$stmt->bindParam(':p_multi_currency_in', $multicurrency);
			$stmt->bindParam(':p_auto_credits_increment_in', $autoincrement);
			$stmt->bindParam(':p_pay_back_perc', $game_payback);
			$stmt->bindParam(':p_key_exit_in', $key_exit);
			$stmt->bindParam(':p_enter_pass_in', $enter_password);
			$stmt->bindParam(':p_ADDRESS2_in', $street_address2);
			$stmt->bindParam(':p_BANK_ACCOUNT_in', $bank_account);
			$stmt->bindParam(':p_BANK_COUNTRY_in', $bank_country);
			$stmt->bindParam(':p_SWIFT_in', $swift);
			$stmt->bindParam(':p_IBAN_in', $iban);
			$stmt->bindParam(':p_send_mail_in', $receive_mail);
			$stmt->bindParam(':p_inactive_time_in', $inactive_time);
			$stmt->bindParam(':p_site_name_in', $site_name);
			$stmt->bindParam(':p_registred_aff', $registred_affiliate);
			$stmt->bindParam(':p_origin_in', $origin_site);
            $stmt->bindParam(':p_new_login_kills_sess', $new_login_kills_sess);
			$subject_id_dummy = null;
			$stmt->bindParam(':p_subject_id_dummy_out', $subject_id_dummy, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("status"=>OK, "subject_id"=>$subject_id_dummy);
		}catch(Zend_Db_Adapter_Oracle_Exception $ex1){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex1);
			ErrorMailHelper::writeError($message, $message);
			throw $ex1;
		}catch(Zend_Db_Statement_Oracle_Exception $ex2){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex2);
			ErrorMailHelper::writeError($message, $message);
			throw $ex2;
		}catch(Zend_Exception $ex3){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex3);
			ErrorMailHelper::writeError($message, $message);
			throw $ex3;
		}
	}
	
	//search all users
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
     * @param null $parent_aff
     * @param null $currency
     * @param string $show_banned
     * @param string $subject_type
     * @return mixed
     * @throws Zend_Exception
     */
	public static function search($session_id, $pageNo = 1, $perPage = 25, $column = 1, $order = 'asc', $username = null, $first_name = null, $last_name = null, $city = null, $parent_aff = null, $currency = null, $show_banned = YES, $subject_type = ALL){
		//$cacheObj = Zend_Registry::get('db_cache');
		//$result = unserialize($cacheObj->load("REPORTS__MSEARCH_ALL_USERS_session_id_{$session_id}_pageNo_{$pageNo}_perPage_{$perPage}_column_{$column}_order_{$order}_username_{$username}_firstname_{$first_name}_lastname_{$last_name}_city_{$city}_parentaff_{$parent_aff}_currency_{$currency}_showbanned_{$show_banned}_subjecttype_{$subject_type}") );
		//if(!isset($result) || $result == null || !$result){
		if(true){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		if($currency == '' || is_null($currency))
			$currency = ALL;
			try{
				$stmt = $dbAdapter->prepare('CALL REPORTS.M$SEARCH_ALL_USERS(:p_session_id_in, :p_user_name_in, :p_first_name_in, :p_last_name_in, :p_city_in, :p_parent_aff_in, :p_currency_in, :p_banned_in, :p_page_number_in, :p_hits_per_page_in, :p_order_by_in, :p_sort_order_in, :p_subject_type_name_in, :list_users_out)');
				$stmt->bindParam(':p_session_id_in', $session_id);
				$stmt->bindParam(':p_user_name_in', $username);
				$stmt->bindParam(':p_first_name_in', $first_name);
				$stmt->bindParam(':p_last_name_in', $last_name);
				$stmt->bindParam(':p_city_in', $city);
				$stmt->bindParam(':p_parent_aff_in', $parent_aff);
				$stmt->bindParam(':p_currency_in', $currency);
				$stmt->bindParam(':p_banned_in', $show_banned);
				$stmt->bindParam(':p_page_number_in', $pageNo);
				$stmt->bindParam(':p_hits_per_page_in', $perPage);
				$stmt->bindParam(':p_order_by_in', $column);
				$stmt->bindParam(':p_sort_order_in', $order);
				$stmt->bindParam(':p_subject_type_name_in', $subject_type);
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
				$result = array("table"=>$table, "info"=>$info);
				//$cacheObj->save(serialize($result), "REPORTS__MSEARCH_ALL_USERS_session_id_{$session_id}_pageNo_{$pageNo}_perPage_{$perPage}_column_{$column}_order_{$order}_username_{$username}_firstname_{$first_name}_lastname_{$last_name}_city_{$city}_parentaff_{$parent_aff}_currency_{$currency}_showbanned_{$show_banned}_subjecttype_{$subject_type}");
				return $result;
			}catch(Zend_Exception $ex){
				$dbAdapter->rollBack();
				$dbAdapter->closeConnection();
				$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
				ErrorMailHelper::writeError($message, $message);
				throw new Zend_Exception($message);
			}		
		}else{
			//return $result;
            return null;
		}
	}
	
	//list backoffice administrators
    /**
     * @param $session_id
     * @param int $page_number
     * @param int $hits_per_page
     * @return mixed
     * @throws Zend_Exception
     */
	public static function listAdministrators($session_id, $page_number = 1, $hits_per_page = 50){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$LIST_ADMINISTRATORS(:p_session_id_in, :p_subtype_name_in, :p_page_number_in, :p_hits_per_page_in, :p_adm_list_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$subtype_name = null;
			$stmt->bindParam(':p_subtype_name_in', $subtype_name);
			$stmt->bindParam(':p_page_number_in', $page_number);
			$stmt->bindParam(':p_hits_per_page_in', $hits_per_page);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(":p_adm_list_out", $cursor);
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
	
	//reset administrators password in backoffice
    /**
     * @param $session_id
     * @param $subject_id
     * @param $password_new
     * @throws Zend_Exception
     */
	public static function resetPassword($session_id, $subject_id, $password_new){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$RESET_PASSWORD(:p_session_id_in, :p_subject_id_in, :p_password_new_in)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_subject_id_in', $subject_id);
			$stmt->bindParam(':p_password_new_in',$password_new);
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
	
	//returns administrators details in backoffice
    /**
     * @param $session_id
     * @param $administrator_id
     * @return mixed
     * @throws Zend_Exception
     */
	public static function getAdministratorDetails($session_id, $administrator_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$SUBJECT_DETAIL(:p_session_id_in, :p_subject_id_in, :p_subject_detail_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_subject_id_in', $administrator_id);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(":p_subject_detail_out", $cursor);
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

    /**
     * @param $session_id
     * @return mixed
     * @throws Zend_Exception
     */
    public static function listAffiliatesWithRoot($session_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
        $dbAdapter->beginTransaction();
        try{
            $stmt = $dbAdapter->prepare('CALL reports_bo.list_aff_w_paybackpotsettings(:p_session_id_in, :cur_result)');
            $stmt->bindParam(':p_session_id_in', $session_id);
            $cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
            $stmt->bindCursor(":cur_result", $cursor);
            $stmt->execute(null, false);
            $dbAdapter->commit();
            $cursor->execute();
            $cursor->free();
            $dbAdapter->closeConnection();
            return array('cursor' => $cursor);
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
     * @param $language
     * @return array
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
            //throw new Zend_Exception($message);
            return array("status"=>NOK, "message"=>NOK_EXCEPTION, "error_message"=>$message);
        }
    }
}