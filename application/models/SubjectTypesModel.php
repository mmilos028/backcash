<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'ErrorMailHelper.php';

class SubjectTypesModel{

	public function __construct(){
	}

	//find subject id from unique name - username
    /**
     * @param $subject_name
     * @throws Zend_Exception
     */
	public static function findSubjectID($subject_name){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL INTEGRATION.M$FIND_SUBJECT_ID(:p_subject_name_in, :p_subject_id_out)');
			$stmt->bindParam(':p_subject_name_in', $subject_name);
			$subject_id = 0;
			$stmt->bindParam(':p_subject_id_out', $subject_id, SQLT_INT);			
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("subject_id"=>$subject_id);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			ErrorMailHelper::writeError($message, $message);
			throw new Zend_Exception($message);
		}
	}	
	
	//returns tree structure of affiliates on backoffice
    /**
     * @param $session_id
     * @param string $subject_type_name
     * @param null $subject_type_name2
     * @throws Zend_Exception
     */
	public static function getSubjectsTree($session_id, $subject_type_name = "Affiliate", $subject_type_name2 = null){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL REPORTS.M$GET_SUBJECTS_TREE(:p_session_id_in, :p_subject_type_name, :p_player_type_name, :p_list_game_stat)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_subject_type_name', $subject_type_name);
			$stmt->bindParam(':p_player_type_name', $subject_type_name2);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(":p_list_game_stat", $cursor);
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
	
	//returns subject type, from managment types returns name-type of subject
    /**
     * @param $subject_name
     * @return mixed
     * @throws Zend_Exception
     */
	public static function getSubjectType($subject_name){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		try{
			$stmt = $dbAdapter->prepare('BEGIN :p := DYNVAR.VAL(:var_in); END;');
			$stmt->bindParam(':var_in', $subject_name);
			$value_out = "";
			$stmt->bindParam(':p', $value_out, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("value"=>$value_out);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			ErrorMailHelper::writeError($message, $message);
			throw new Zend_Exception($message);
		}
	}
	
	//returns list of users with role on system
    /**
     * @param $session_id
     * @param $role_id
     * @param int $page_number
     * @param int $hits_per_page
     * @param int $order_by
     * @param string $sort_order
     * @throws Zend_Exception
     */
	public static function usersWithRole($session_id, $role_id, $page_number = 1, $hits_per_page = 25, $order_by = 1, $sort_order = 'asc'){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$LIST_USER_ROLE(:p_session_id_in, :p_role_id_in, :p_page_number_in, :p_hits_per_page_in, :p_order_by_in, :p_sort_order_in, :list_users)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_role_id_in', $role_id);
			$stmt->bindParam(':p_page_number_in', $page_number);
			$stmt->bindParam(':p_hits_per_page_in', $hits_per_page);
			$stmt->bindParam(':p_order_by_in', $order_by);
			$stmt->bindParam(':p_sort_order_in', $sort_order);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':list_users', $cursor);
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
	
	//performes role name update on system
    /**
     * @param $session_id
     * @param $p_id_old_in
     * @param $new_name
     * @throws Zend_Exception
     */
	public static function updateSubjectType($session_id, $p_id_old_in, $new_name){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		$perrors = "";
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$MANAGE_SUBJECT_TYPE(:p_session_id_in, :p_action_in, :p_name_new_in, :p_id_old_in, :p_super_role_id_in, :p_name_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$action = UPDATE;
			$stmt->bindParam(':p_action_in', $action);
			$stmt->bindParam(':p_name_new_in', $new_name);
			$super_role_id_in = 0;
			$stmt->bindParam(':p_super_role_id_in', $super_role_id_in);
			$stmt->bindParam(':p_id_old_in', $p_id_old_in);
			$stmt->bindParam(':p_name_out', $perrors, SQLT_CHR, 255);
			$stmt->execute(null, false);
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("errors"=>$perrors);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			ErrorMailHelper::writeError($message, $message);
			throw new Zend_Exception($message);
		}		
	}
	
	//inserts new role into system
    /**
     * @param $session_id
     * @param $name
     * @param $parent_role_id
     * @throws Zend_Exception
     */
	public static function insertSubjectType($session_id, $name, $parent_role_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		$perrors = "";
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$MANAGE_SUBJECT_TYPE(:p_session_id_in, :p_action_in, :p_name_new_in, :p_id_old_in, :p_super_role_id_in, :p_name_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$action = INSERT;
			$stmt->bindParam(':p_action_in', $action);
			$stmt->bindParam(':p_name_new_in', $name);
			$old = 0;
			$stmt->bindParam('p_id_old_in', $old);
			$stmt->bindParam('p_super_role_id_in', $parent_role_id);
			$stmt->bindParam(':p_name_out', $perrors, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("errors"=>$perrors);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			ErrorMailHelper::writeError($message, $message);
			throw new Zend_Exception($message);
		}		
	}
	
	//removes role from system
    /**
     * @param $session_id
     * @param $p_id_old_in
     * @throws Zend_Exception
     */
	public static function deleteSubjectType($session_id, $p_id_old_in){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		$perrors = "";
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$MANAGE_SUBJECT_TYPE(:p_session_id_in, :p_action_in, :p_name_in, :p_id_old_in, :p_super_role_id_in, :p_name_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$action = DELETE;
			$stmt->bindParam(':p_action_in', $action);
			$name = "nista";
			$stmt->bindParam(':p_name_in', $name);
			$stmt->bindParam(':p_id_old_in', $p_id_old_in);
			$super_role_id = 0;
			$stmt->bindParam(':p_super_role_id_in', $super_role_id);
			$stmt->bindParam(':p_name_out', $perrors, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("errors"=>$perrors);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			ErrorMailHelper::writeError($message, $message);
			throw new Zend_Exception($message);
		}		
	}
	
	//lists all roles except player role in backoffice with pagination
    /**
     * @param $session_id
     * @param int $id
     * @param int $pageNo
     * @param int $perPage
     * @param int $column
     * @param string $sort_order
     * @throws Zend_Exception
     */
	public static function getSubjectTypesNoPlayer($session_id, $id = 0, $pageNo = 1, $perPage = 25, $column = 1, $sort_order = 'asc'){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$LIST_ROLES(:p_session_id_in, :p_session_type_name, :p_id_in, :p_page_number_in, :p_hits_per_page_in, :p_order_by_in, :p_sort_order_in, :p_roles_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			/*$config = Zend_Registry::get('config');
			$backoffice_type = $config->backoffice_type;
			if($backoffice_type == BACKOFFICE_TYPE_TECHNICAL){
				$subject_type = $this->getSubjectType(NAME_IN_FRONT_END_ABB);
			}else{ //maybe technical
				$subject_type = null;
			}*/
            $subject_type = null;
			$stmt->bindParam(':p_session_type_name', $subject_type);
			$stmt->bindParam(':p_id_in', $id);
			$stmt->bindParam(':p_page_number_in', $pageNo);
			$stmt->bindParam(':p_hits_per_page_in', $perPage);
			$stmt->bindParam(':p_order_by_in', $column);
			$stmt->bindParam(':p_sort_order_in', $sort_order);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':p_roles_out', $cursor);
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
	
	//lists all roles except player role in backoffice
    /**
     * @param $session_id
     * @throws Zend_Exception
     */
	public static function getAllSubjectTypesNoPlayer($session_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$LIST_ROLES_NO_PLAYER(:p_session_id_in, :p_roles_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':p_roles_out', $cursor);
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
            //throw new Zend_Exception(CursorToArrayHelper::getExceptionTraceAsString($ex));
		}
	}
	
	//lists all superroles.
    /**
     * @param $session_id
     * @throws Zend_Exception
     */
	public static function getAllSuperroles($session_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$LIST_SUPER_ROLES(:p_session_id_in, :p_super_roles_out)');
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindCursor(':p_super_roles_out', $cursor);
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
	
	//returns superrole details from role id
    /**
     * @param $subject_type_id_in
     * @throws Zend_Exception
     */
	public static function findSuperrole($subject_type_id_in){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$FIND_SUPER_ROLE(:p_subject_type_id_in, :p_super_type_id_out, :p_super_type_name_out, :p_priority_out)');
			$stmt->bindParam(':p_subject_type_id_in', $subject_type_id_in);
			$super_type_id_out = "";
			$stmt->bindParam(':p_super_type_id_out', $super_type_id_out, SQLT_CHR, 255);
			$super_type_name_out = "";
			$stmt->bindParam(':p_super_type_name_out', $super_type_name_out, SQLT_CHR, 255);
			$priority_out = "";
			$stmt->bindParam(':p_priority_out', $priority_out, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("super_role_name"=>$super_type_name_out);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			ErrorMailHelper::writeError($message, $message);
			throw new Zend_Exception($message);
		}
	}
}