<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
class RoleHelper{
	/**
		returns number rounded and formated to 2 decimals with thousand separator
	*/
	public static function translateRole($real_subject_type_name){
		$translate = Zend_Registry::get('translate');
		$subject_type = $real_subject_type_name;
		if($real_subject_type_name == ROLA_AFF_AFFILIATE){
			$subject_type = $translate->_("Affiliates_Role");
		}else if($real_subject_type_name == ROLA_AFF_LOCATION){
			$subject_type = $translate->_("Location_Role");
		}else if($real_subject_type_name == ROLA_AD_CASHIER){
			$subject_type = $translate->_("Cashier_Role");
		}else if($real_subject_type_name == ROLA_AFF_OPERATER){
			$subject_type = $translate->_("Operater_Role");
		}else if($real_subject_type_name == ROLA_AD_ADMINISTRATOR){
			$subject_type = $translate->_("Administrator_Role");
		}else if($real_subject_type_name == ROLA_ADMIN_CUSTOM){
			$subject_type = $translate->_("AdminCustom_Role");
		}else if($real_subject_type_name == ROLA_AD_COLLECTOR){
			$subject_type = $translate->_("Collector_Role");
		}else if($real_subject_type_name == ROLA_AD_SHIFT_CASHIER_S){
			$subject_type = $translate->_("ShiftCashierS_Role");
		}else if($real_subject_type_name == ROLA_PL_PC_PLAYER_INTERNET){
			$subject_type = $translate->_("Player_Role");
		}else if($real_subject_type_name == ROLA_PL_TERMINAL_PLAYER){
			$subject_type = $translate->_("Terminal_Role");
		}else if($real_subject_type_name == ROLA_AD_CASHIER_PAYOUT){
			$subject_type = $translate->_("CashierPayout_Role");
		}else if($real_subject_type_name == ROLA_AD_CASHIER_SUBLEVEL){
			$subject_type = $translate->_("CashierSublevel_Role");
		}else if($real_subject_type_name == ROLA_AD_THAICASHIER){
			$subject_type = $translate->_("CashierThai_Role");
		}else if($real_subject_type_name == ROLA_AD_SHIFT_CASHIER_W){
			$subject_type = $translate->_("ShiftCashierW_Role");
		}
		return $subject_type;
	}	
}