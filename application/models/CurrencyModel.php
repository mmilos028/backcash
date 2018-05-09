<?php
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'ErrorMailHelper.php';
class CurrencyModel
{
    public function __construct()
    {
    }

    //returns currencies for country
    /**
     * @param $country_id
     * @return mixed
     * @throws Zend_Exception
     */
    public static function getCurrencyForCountry($country_id)
    {
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
        $dbAdapter->beginTransaction();
        try {
            $stmt = $dbAdapter->prepare('CALL CORE.M$FIND_CURRENCY_FOR_SUBJECT(:p_subject_id_in, :p_currency_out)');
            $stmt->bindParam(':p_subject_id_in', $country_id);
            $cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
            $stmt->bindCursor(":p_currency_out", $cursor);
            $stmt->execute(null, false);
            $dbAdapter->commit();
            $cursor->execute();
            $cursor->free();
            $dbAdapter->closeConnection();
            $currency = $cursor->current();
            return $currency['currency'];
        } catch (Zend_Exception $ex) {
            $dbAdapter->rollBack();
            $dbAdapter->closeConnection();
            $message = CursorToArrayHelper::getExceptionTraceAsString($ex);
            ErrorMailHelper::writeError($message, $message);
            throw new Zend_Exception($message);
        }
    }

    //returns currencies
    /**
     * @return mixed
     * @throws Zend_Exception
     */
    public static function getCurrencies()
    {
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
        $dbAdapter->beginTransaction();
        try {
            $stmt = $dbAdapter->prepare('CALL CORE.M$FIND_CURRENCY_FOR_SUBJECT(:p_subject_id_in, :p_currency_out)');
            $country_id = 0;
            $stmt->bindParam(':p_subject_id_in', $country_id);
            $cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
            $stmt->bindCursor(":p_currency_out", $cursor);
            $stmt->execute(null, false);
            $dbAdapter->commit();
            $cursor->execute();
            $cursor->free();
            $dbAdapter->closeConnection();
            return array("cursor"=>$cursor);
        } catch (Zend_Exception $ex) {
            $dbAdapter->rollBack();
            $dbAdapter->closeConnection();
            $message = CursorToArrayHelper::getExceptionTraceAsString($ex);
            ErrorMailHelper::writeError($message, $message);
            throw new Zend_Exception($message);
        }
    }

    //returns countries
    /**
     * @param $session_id
     * @return mixed
     * @throws Zend_Exception
     */
    public static function getCountries($session_id)
    {
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
        $dbAdapter->beginTransaction();
        try {
            $stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$LIST_COUNTRIES(:p_session_id_in, :p_countries_list_out)');
            $stmt->bindParam(':p_session_id_in', $session_id);
            $cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
            $stmt->bindCursor(":p_countries_list_out", $cursor);
            $stmt->execute(null, false);
            $dbAdapter->commit();
            $cursor->execute();
            $cursor->free();
            $dbAdapter->closeConnection();
            return array("cursor"=>$cursor);
        } catch (Zend_Exception $ex) {
            $dbAdapter->rollBack();
            $dbAdapter->closeConnection();
            $message = CursorToArrayHelper::getExceptionTraceAsString($ex);
            ErrorMailHelper::writeError($message, $message);
            throw new Zend_Exception($message);
        }
    }

    //returns currencies for affiliate, player and terminal update
    /**
     * @param $subject_id
     * @return mixed
     * @throws Zend_Exception
     */
    public static function getCurrenciesForSubject($subject_id)
    {
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
        $dbAdapter->beginTransaction();
        try {
            $stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$FIND_CURR_FOR_SUBJECT_PUBL(:p_subject_id_in, :p_currency_out)');
            $stmt->bindParam(':p_subject_id_in', $subject_id);
            $cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
            $stmt->bindCursor(":p_currency_out", $cursor);
            $stmt->execute(null, false);
            $dbAdapter->commit();
            $cursor->execute();
            $cursor->free();
            $dbAdapter->closeConnection();
            return array("cursor"=>$cursor);
        } catch (Zend_Exception $ex) {
            $dbAdapter->rollBack();
            $dbAdapter->closeConnection();
            $message = CursorToArrayHelper::getExceptionTraceAsString($ex);
            ErrorMailHelper::writeError($message, $message);
            throw new Zend_Exception($message);
        }
    }

    //list currencies for logged in affiliate in backoffice
    //also in ReportsModelPartial5, finds list of currencies on backoffice session id
    /**
     * @param $session_id
     * @return mixed
     * @throws Zend_Exception
     */
    public static function getCurrencyForSubjects($session_id)
    {
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
        $dbAdapter->beginTransaction();
        try {
            $stmt = $dbAdapter->prepare('CALL REPORTS.M$LIST_CURRENCY_FOR_SESSIONS(:p_session_id_in, :p_currency_out)');
            $stmt->bindParam(':p_session_id_in', $session_id);
            $cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
            $stmt->bindCursor(':p_currency_out', $cursor);
            $stmt->execute(null, false);
            $dbAdapter->commit();
            $cursor->execute();
            $cursor->free();
            $dbAdapter->closeConnection();
            return array("cursor"=>$cursor);
        } catch (Zend_Exception $ex) {
            $dbAdapter->rollBack();
            $dbAdapter->closeConnection();
            $message = CursorToArrayHelper::getExceptionTraceAsString($ex);
            ErrorMailHelper::writeError($message, $message);
            throw new Zend_Exception($message);
        }
    }
}