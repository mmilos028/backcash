<?php
/* This is interface witch actually does user authentification in Oracle DB */
require_once('Zend/Auth/Adapter/Interface.php');
require_once MODELS_DIR . DS . 'SubjectTypesModel.php';
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'ErrorMailHelper.php';
class OracleAuthInterface implements Zend_Auth_Adapter_Interface{
    /**
     * @var Zend_Db_Adapter_Oracle
     */
	private $dbAdapter;
	private $username;
	private $password;
	private $session_id;
	private $session_duration;
	private $returnObject;
	private $first_name;
	private $last_name;

    /**
     * @param $username
     * @return $this
     */
	public function setUsername($username){ $this->username = $username; return $this; }

    /**
     * @param $password
     * @return $this
     */
	public function setPassword($password){ $this->password = $password; return $this; }

    /**
     * @return stdClass
     */
	public function getResultRowObject(){
		$returnObject = new stdClass();
	 	$returnObject->username = $this->username;
	 	$returnObject->session_id = $this->session_id;
	 	$returnObject->first_name = $this->first_name;
	 	$returnObject->last_name = $this->last_name;
	 return $returnObject;
	}
	//test if it is ip address
    /**
     * @param $ip_address
     * @return bool
     */
	private function testPrivateIP($ip_address){
		//if there are ip addresses with , separated as CSV string
		$ip_addresses = explode(",", $ip_address);
		$ip_address = $ip_addresses[0];
		$ip_start = ip2long("192.168.0.0");
		$ip_end = ip2long("192.168.255.255");
		$ip_test = ip2long($ip_address);
		if($ip_test > $ip_start && $ip_test < $ip_end)return true; //if is in private ip address range
		$ip_start = ip2long("10.0.0.0");
		$ip_end = ip2long("10.255.255.255");
		$ip_test = ip2long($ip_address);
		if($ip_test > $ip_start && $ip_test < $ip_end)return true; //if is in private ip address range
		$ip_start = ip2long("172.16.0.0");
		$ip_end = ip2long("172.31.255.255");
		$ip_test = ip2long($ip_address);
		if($ip_test > $ip_start && $ip_test < $ip_end)return true; //if is in private ip address range
		return false; // //if it is not in private ip address range
	}
	//list currencies from user that is currently logging in
    /**
     * @param $session_id
     * @return array
     * @throws Zend_Exception
     */
	private function getCurrencyForSubject($session_id){
		require_once MODELS_DIR . DS . 'CurrencyModel.php';
		$result = CurrencyModel::getCurrencyForSubjects($session_id);
        $cursor = $result["cursor"];
		return array("cursor"=>$cursor);
	}
	//return real client ip address
    /**
     * @return mixed
     */
	private function real_ip_address(){
		if(!empty($_SERVER['HTTP_CLIENT_IP']))$ip=$_SERVER['HTTP_CLIENT_IP'];
			elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))$ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
				else $ip=$_SERVER['REMOTE_ADDR'];
		//if there are ip addresses with , separated as CSV string
		$ip_addresses = explode(",", $ip);
		$ip = $ip_addresses[0];
		return $ip;
	}
	//authentificate client on database
    /**
     * @return Zend_Auth_Result
     * @throws Zend_Exception
     */
	public function authenticate(){
		$this->dbAdapter = Zend_Registry::get('db_auth');
		$translate = Zend_Registry::get("translate");
		$config = Zend_Registry::get("config");
		$this->dbAdapter->beginTransaction();
		$result = -100000;		
		$sesija = array();
		$succ = false; //session opening is not successfull
		try{
            $stmt = $this->dbAdapter->prepare("alter session set nls_date_format = 'dd-Mon-yyyy hh24:mi:ss'");
            $stmt->execute();
			$stmt = $this->dbAdapter->prepare('CALL MANAGMENT_CORE.M$LOGIN_USER(:p_username_in, :p_password_in, :p_ip_address_in, :p_country_name_in, :p_city_in, :p_session_type_name_in, :p_origin_in, :p_session_out, :p_currency_out, :p_multi_currency_out, :p_auto_credit_increment_out, :p_auto_credit_increment_y_out, :p_subject_type_id_out, :p_subject_type_name_out, :p_subject_super_type_id_out, :p_subject_super_type_name_out, :p_session_type_id_out, :p_session_type_name_out, :p_first_name_out, :p_last_name_out, :p_last_time_collect_out, :p_online_casino_out)');
			$stmt->bindParam(":p_username_in", $this->username);
			$stmt->bindParam(":p_password_in", $this->password);
			$ip_address = $this->real_ip_address();
			$stmt->bindParam(":p_ip_address_in", $ip_address);
			$country = "";
			$stmt->bindParam(":p_country_name_in", $country);
			$city = "";
			$stmt->bindParam(":p_city_in", $city);
			$session_type_name_in = "";
			$session_type_name_in = SubjectTypesModel::getSubjectType("MANAGMENT_TYPES.NAME_IN_BACK_OFFICE");
            $session_type_name_in = $session_type_name_in["value"];
			$stmt->bindParam(":p_session_type_name_in", $session_type_name_in, SQLT_CHR, 255);
			$origin_site = $config->origin_site;
			$stmt->bindParam(":p_origin_in", $origin_site);
			$stmt->bindParam(":p_session_out", $result, SQLT_CHR, 255);
			$currency = "";
			$stmt->bindParam(":p_currency_out", $currency, SQLT_CHR, 255);
			$multi_currency = "";
			//logged user is multicurrency
			$stmt->bindParam(":p_multi_currency_out", $multi_currency, SQLT_CHR, 255);
			$auto_credit_increment = "";
			//enabled autoincrement credits amount
			$stmt->bindParam(":p_auto_credit_increment_out", $auto_credit_increment, SQLT_CHR, 255);
			$auto_credit_increment_y = NO;
			//if Y then autocredits is enabled if N then autocredits is disabled
			$stmt->bindParam(":p_auto_credit_increment_y_out", $auto_credit_increment_y, SQLT_CHR, 5);
			$subject_type_id = 0;  
			//number of affiliate that is logging in
			$stmt->bindParam(":p_subject_type_id_out", $subject_type_id, SQLT_CHR, 255);
			$subject_type_name = "";
			//affiliates name that is logging in
			$stmt->bindParam(":p_subject_type_name_out", $subject_type_name, SQLT_CHR, 255);
			//number of parent affiliate that logged affiliate belongs to
			$subject_super_type_id = 0;
			$stmt->bindParam(":p_subject_super_type_id_out", $subject_super_type_id, SQLT_CHR, 255);
			$subject_super_type_name= "";
			//name of parent affiliate
			$stmt->bindParam(':p_subject_super_type_name_out', $subject_super_type_name, SQLT_CHR, 255);
			//session type number for logged in user
			$session_type_id = 0;
			$stmt->bindParam(":p_session_type_id_out", $session_type_id, SQLT_CHR, 255);
			//session type name from logged in user
			$session_type_name = "";
			$stmt->bindParam(":p_session_type_name_out", $session_type_name, SQLT_CHR, 255);
			$first_name = "";
			$stmt->bindParam(":p_first_name_out", $first_name, SQLT_CHR, 255);
			$last_name = "";
			$stmt->bindParam(":p_last_name_out", $last_name, SQLT_CHR, 255);
			$last_time_collect = "";			
			$stmt->bindParam(":p_last_time_collect_out", $last_time_collect, SQLT_CHR, 255);
			$is_online_casino = "";
			$stmt->bindParam(":p_online_casino_out", $is_online_casino, SQLT_CHR, 255);
			$stmt->execute();
			$this->dbAdapter->commit();
			$this->dbAdapter->closeConnection();
			//DEBUG HERE
			//$message = "MANAGMENT_CORE.M$LOGIN_USER(:p_username_in = {$this->username}, :p_password_in = {$this->password}, :p_ip_address_in = {$ip_address}, :p_country_name_in = '', :p_city_in = '', :p_session_type_name_in = {$session_type_name_in}, :p_origin_in = {$origin_site}, :p_session_out = {$result}, :p_currency_out = {$currency}, :p_multi_currency_out = {$multi_currency}, :p_auto_credit_increment_out = {$auto_credit_increment}, :p_auto_credit_increment_y_out = {$auto_credit_increment_y}, :p_subject_type_id_out = {$subject_type_id}, :p_subject_type_name_out = {$subject_type_name}, :p_subject_super_type_id_out = {$subject_super_type_id}, :p_subject_super_type_name_out = {$subject_super_type_name}, :p_session_type_id_out = {$session_type_id}, :p_session_type_name_out = {$session_type_name}, :p_first_name_out = {$first_name}, :p_last_name_out = {$last_name}, :p_last_time_collect_out = {$last_time_collect}, :p_online_casino_out = {$is_online_casino})";
			//ErrorMailHelper::writeInfo($message, $message);
			//
			$authResult = array();
			if($result == -4){
				$succ = false;
				$authResult['code'] = Zend_Auth_Result::FAILURE_IDENTITY_AMBIGUOUS;
				$authResult['identity'] = "";
				$authResult['messages'][] = $translate->_("Already logged in backoffice. Login failed.");
				return new Zend_Auth_Result($authResult['code'], $authResult['identity'], $authResult['messages']);
			}
		}catch(Zend_Exception $ex){
			$succ = false;
			$authResult['code'] = Zend_Auth_Result::FAILURE;
			$authResult['identity'] = "";
			$authResult['messages'][] = "No database connection available";
			$this->dbAdapter->rollBack();
			$this->dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			ErrorMailHelper::writeError($message, $message);
			return new Zend_Auth_Result($authResult['code'], $authResult['identity'], $authResult['messages']);
		}
		$sesija['origin_site'] = $origin_site;
		$sesija['session_out'] = $result;
		$sesija['currency'] = $currency;
		$sesija['multi_currency'] = $multi_currency;
		$sesija['auto_credit_increment'] = $auto_credit_increment;
		$sesija['auto_credit_increment_y'] = $auto_credit_increment_y;
		$sesija['subject_type_id'] = $subject_type_id;
		$sesija['subject_type_name'] = $subject_type_name;
		$sesija['subject_super_type_id'] = $subject_super_type_id;
		$sesija['subject_super_type_name'] = $subject_super_type_name;
		$sesija['session_type_id'] = $session_type_id;
		$sesija['session_type_name'] = $session_type_name;
		$sesija['username'] = $this->username;
		$sesija['first_name'] = $first_name;
		$sesija['last_name'] = $last_name;
		$sesija['last_time_collect'] = $last_time_collect;
		$sesija['is_online_casino'] = $is_online_casino;
        $currencies = array();
		if($result >= 1){
			try{
				$currencies = $this->getCurrencyForSubject($result);
                $currencies = $currencies["cursor"];
				$succ = true;
			}catch(Zend_Exception $ex){
				//IT HAPPENS IF REPORTS PACKAGE IS NOT COMPILED RETURNS TO USER MESSAGE Login failed No user with username password.
				$succ = false;
				$authResult['code'] = Zend_Auth_Result::FAILURE;
				$authResult['identity'] = "";
				$authResult['messages'][] = $translate->_("No currency list available for this user. Please try again.");
				$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
				ErrorMailHelper::writeError($message, $message);
			}
			if($succ == true)$sesija['currencies'] = $currencies;
		}else $succ = false;
		$session_auth = new Zend_Session_Namespace("auth_space");
		$session_auth->session = $sesija;
		$authResult = array(
           'code' => Zend_Auth_Result::FAILURE,
           'identity' => $this->username,
           'messages' => array()
		);
		if($result == -1){
			$succ = false;
			$authResult['code'] = Zend_Auth_Result::FAILURE;
			$authResult['identity'] = "";
			$authResult['messages'][] = $translate->_("Login failed");
			return new Zend_Auth_Result($authResult['code'], $authResult['identity'], $authResult['messages']);
		}
		if($result == -2){
			$succ = false;
			$authResult['code'] = Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND;
			$authResult['identity'] = "";
			$authResult['messages'][] = $translate->_("Login failed. No user with username/password.");
			return new Zend_Auth_Result($authResult['code'], $authResult['identity'], $authResult['messages']);
		}
		if($result == -3){
			$succ = false;
			$authResult['code'] = Zend_Auth_Result::FAILURE_UNCATEGORIZED;
			$authResult['identity'] = "";
			$authResult['messages'][]= $translate->_("Login failed. Unknown error.");
			return new Zend_Auth_Result($authResult['code'], $authResult['identity'], $authResult['messages']);
		}
		if($result == -4){
			$succ = false;
			$authResult['code'] = Zend_Auth_Result::FAILURE_IDENTITY_AMBIGUOUS;
			$authResult['identity'] = "";
			$authResult['messages'][] = $translate->_("Already logged in backoffice. Login failed.");
			return new Zend_Auth_Result($authResult['code'], $authResult['identity'], $authResult['messages']);
		}
		if($result == -5){
			$message = "{$this->username} is in Panic mode.";
			ErrorMailHelper::writeInfoLog($message, $message);
		}
		if($result == -100000){
			$succ = false;
			$authResult['code'] = Zend_Auth_Result::FAILURE;
			$authResult['identity'] = "";
			$authResult['messages'][] = $translate->_("Login failed. Database problem exists."); //ova je nova
			return new Zend_Auth_Result($authResult['code'], $authResult['identity'], $authResult['messages']);
		}
		if($succ == false){
			$authResult['code'] = Zend_Auth_Result::FAILURE;
			$authResult['identity'] = "";
			$authResult['messages'][] = $translate->_("Login failed");
			return new Zend_Auth_Result($authResult['code'], $authResult['identity'], $authResult['messages']);
		}
		if($result >= 1 && $succ == true){
			$authResult['code'] = Zend_Auth_Result::SUCCESS;
			$authResult['identity'] = "";
			$authResult['messages'][] = $translate->_("Login succesful.");
			$this->session_id = $result;
		}
		return new Zend_Auth_Result($authResult['code'], $authResult['identity'], $authResult['messages']);
	}
	//returns session id value
    /**
     * @return mixed
     */
	public function getSessionId(){
		return $this->session_id;
	}
}
?>