<?php



class ReverseModeHelper {

    private static $username;
    private static $super_role;
    private static $role;
    private static $reverse_type_user;
    private static $is_reverse_type_user_authenticated;
    private static $is_reverse_type_user;
    private static $is_under_reverse_affiliate;

    //private static $instance;

    /*public function __construct(){
        $this->username = $_SESSION['auth_space']['session']['username'];
        $this->super_role = $_SESSION['auth_space']['session']['subject_super_type_name'];
        $this->role = $_SESSION['auth_space']['session']['subject_type_name'];
        $this->reverse_type_user = $_SESSION['auth_space']['session']['reverse_type_user'];
        $this->is_reverse_type_user_authenticated = $_SESSION['auth_space']['session']['is_reverse_type_user_authenticated'];
        $this->is_reverse_type_user = $_SESSION['auth_space']['session']['is_reverse_type_user'];
    }*/

    static function __init()
      {
        self::$username = $_SESSION['auth_space']['session']['username'];
        self::$super_role = $_SESSION['auth_space']['session']['subject_super_type_name'];
        self::$role = $_SESSION['auth_space']['session']['subject_type_name'];
        self::$reverse_type_user = $_SESSION['auth_space']['session']['reverse_type_user'];
        self::$is_reverse_type_user_authenticated = $_SESSION['auth_space']['session']['is_reverse_type_user_authenticated'];
        self::$is_reverse_type_user = $_SESSION['auth_space']['session']['is_reverse_type_user'];
        self::$is_under_reverse_affiliate = $_SESSION['auth_space']['session']['is_under_reverse_affiliate'];
      }

    /*public static function getInstance()
    {
        if ( is_null( self::$instance ) )
        {
            self::$instance = new self();
        }
        return self::$instance;
    }*/

    //show column score in reports for reverse mode
    public static function showColumnForReverseMode(){
        self::__init();
        /*if(in_array(self::$role, array(ROLA_AFFILIATE_REVERSE, ROLA_LOCATION_REVERSE, ROLA_OPERATER_REVERSE))){
            return false;
        }*/

        if(self::$reverse_type_user == "REVERSE LOCATION\CASHIER" && in_array(self::$role, array(ROLA_AD_CASHIER, ROLA_AD_CASHIER_SUBLEVEL, ROLA_AD_SHIFT_CASHIER_W, ROLA_AD_SHIFT_CASHIER_S, ROLA_AD_CASHIER_PAYOUT)))
        {
            return false;
        }

        if(self::isUnderReverseAffiliate() && in_array(self::$role, array(ROLA_AFF_AFFILIATE, ROLA_AFF_LOCATION, ROLA_AFF_OPERATER))){
            return true;
        }

        if(self::isReverseTypeUser()){
            //return true;
            return false;
        }

        if(in_array(self::$username, array("Customers", "ReMember", "SenatoR", "SenatorR"))
        ){
            return true;
        }

        return false;
    }

    //hide cash in columns for reverse role
    public static function showOutColumnsForReverseMode(){
        self::__init();
        if(in_array(self::$role, array(ROLA_AFFILIATE_REVERSE, ROLA_LOCATION_REVERSE, ROLA_OPERATER_REVERSE))){
            return false;
        }

        return true;

        /*if(self::$reverse_type_user == "REVERSE LOCATION\CASHIER" && in_array(self::$role, array(ROLA_AD_CASHIER, ROLA_AD_CASHIER_SUBLEVEL, ROLA_AD_SHIFT_CASHIER_W, ROLA_AD_SHIFT_CASHIER_S, ROLA_AD_CASHIER_PAYOUT)))
        {
            return true;
        }

        if(self::isUnderReverseAffiliate() && in_array(self::$role, array(ROLA_AFF_AFFILIATE, ROLA_AFF_LOCATION, ROLA_AFF_OPERATER))){
            return false;
        }

        if(self::isReverseTypeUser()){
            //return true;
            return true;
        }

        if(in_array(self::$username, array("Customers", "ReMember", "SenatoR", "SenatorR"))
        ){
            return false;
        }

        return true;
        */
    }

    //show high score menu item on main application menu
    public static function showHighScoreMenuItem(){
        self::__init();

        if(self::isUnderReverseAffiliate() && in_array(self::$role, array(ROLA_AFF_AFFILIATE, ROLA_AFF_LOCATION, ROLA_AFF_OPERATER))){
            return true;
        }

        if(in_array(self::$role, array(ROLA_AFFILIATE_REVERSE, ROLA_LOCATION_REVERSE, ROLA_OPERATER_REVERSE))){
            return true;
        }else{
            return false;
        }
    }

    //show reverse reports menu item
    public static function showReverseReportsMenuItem(){
        self::__init();

        if(self::isUnderReverseAffiliate() && in_array(self::$role, array(ROLA_AFF_AFFILIATE, ROLA_AFF_LOCATION, ROLA_AFF_OPERATER))){
            return false;
        }

        if(in_array(self::$role, array(ROLA_AFFILIATE_REVERSE, ROLA_LOCATION_REVERSE, ROLA_OPERATER_REVERSE))){
            return true;
        }else{
            return false;
        }
    }

    //show high score menu item on main application menu
    public static function showCashReportTotal(){
        self::__init();

        if(self::isUnderReverseAffiliate() && in_array(self::$role, array(ROLA_AFF_AFFILIATE, ROLA_AFF_LOCATION, ROLA_AFF_OPERATER))){
            return true;
        }

        if(in_array(self::$role, array(ROLA_AFFILIATE_REVERSE, ROLA_LOCATION_REVERSE, ROLA_OPERATER_REVERSE))){
            return true;
        }else{
            return false;
        }
    }

    public static function showListHighScoreForAffiliateMenuItem(){
        self::__init();

        if(in_array(self::$role, array(ROLA_LOCATION_REVERSE))){
            return true;
        }

        if(self::isUnderReverseAffiliate() && in_array(self::$role, array(ROLA_AFF_AFFILIATE, ROLA_AFF_OPERATER))){
            return true;
        }

        return false;
    }

    //show reset income statistic option
    public static function showResetIncomeStatistic(){
        self::__init();

        if(self::isUnderReverseAffiliate() && in_array(self::$role, array(ROLA_AFF_AFFILIATE, ROLA_AFF_LOCATION, ROLA_AFF_OPERATER))){
            return false;
        }

        if(in_array(self::$role, array(ROLA_AFFILIATE_REVERSE, ROLA_LOCATION_REVERSE, ROLA_OPERATER_REVERSE))){
            return true;
        }else{
            return false;
        }
    }

    //show high score status menu item, for clearing high score
    public static function showHighScoreStatusMenuItem(){
        self::__init();
        //check if it is under Location
        require_once MODELS_DIR . DS . 'ReverseReportsModel.php';
        $modelReverseReports = new ReverseReportsModel();
        $session_id = $_SESSION['auth_space']['session']['session_out'];
        $result = $modelReverseReports->showClearHighScoreForLocation($session_id);
        $show_status = ($result['status'] == OK && $result['show_reverse'] == "1") ? true : false;

        //if it is rola Ad / Reverse Administrator
        if($show_status && in_array(self::$role, array(ROLA_LOCATION_REVERSE, ROLA_AFF_LOCATION))){
            return true;
        }else{
            return false;
        }
    }

    //returns limit for start date on reports and if this limit should be applied
    public static function limitStartDateForReports(){
        self::__init();
        if(in_array(self::$role, array(ROLA_AFFILIATE_REVERSE, ROLA_LOCATION_REVERSE, ROLA_OPERATER_REVERSE))){
            require_once MODELS_DIR . DS . 'ReverseReportsModel.php';
            $modelReverseReports = new ReverseReportsModel();
            $session_id = $_SESSION['auth_space']['session']['session_out'];
            $result = $modelReverseReports->getResetUserDatetime($session_id);
            if($result['status'] != OK){
                return array("status"=>NOK);
            }
            if($result["last_reset_date"] == ""){
                return array("status"=>NOK);
            }

            return array("status"=>OK, "start_date"=>$result["last_reset_date"], "start_time"=>$result["last_reset_time"]);
        }
        return array("status"=>NOK);
    }

    //hides main menu items for conditions bellow
    public static function hideMainMenuItem(){
        self::__init();
        if(self::isUnderReverseAffiliate() && in_array(self::$role, array(ROLA_AFF_AFFILIATE, ROLA_AFF_LOCATION, ROLA_AFF_OPERATER))){
            return false;
        }

        if(in_array(self::$role, array(ROLA_AFFILIATE_REVERSE, ROLA_LOCATION_REVERSE, ROLA_OPERATER_REVERSE))){
            return true;
        }
        if(self::$reverse_type_user == "REVERSE LOCATION\CASHIER" && in_array(self::$role, array(ROLA_AD_CASHIER, ROLA_AD_CASHIER_SUBLEVEL, ROLA_AD_SHIFT_CASHIER_W, ROLA_AD_SHIFT_CASHIER_S, ROLA_AD_CASHIER_PAYOUT)))
        {
            return true;
        }

        return false;
    }

    //shows menu items for reverse roles
    public static function showMenuItemForAdReverseRoles(){
        if(in_array(self::$role, array(ROLA_AFFILIATE_REVERSE, ROLA_LOCATION_REVERSE, ROLA_OPERATER_REVERSE))){
            return false;
        }
        return true;
    }

    //show clear high score button
    public static function showClearHighScoreOption(){
        self::__init();
        if(self::isUnderReverseAffiliate() && in_array(self::$role, array(ROLA_AFF_AFFILIATE, ROLA_AFF_LOCATION, ROLA_AFF_OPERATER))){
            return true;
        }
        if(in_array(self::$role, array(ROLA_AFFILIATE_REVERSE, ROLA_LOCATION_REVERSE, ROLA_OPERATER_REVERSE))){
            return true;
        }
        if(self::$reverse_type_user == "REVERSE LOCATION\CASHIER" && in_array(self::$role, array(ROLA_AD_CASHIER, ROLA_AD_CASHIER_SUBLEVEL, ROLA_AD_SHIFT_CASHIER_W, ROLA_AD_SHIFT_CASHIER_S, ROLA_AD_CASHIER_PAYOUT)))
        {
            return true;
        }

        return false;

    }

    //show summary reports in details pages (my account, affiliate, player, terminal)
    public static function showSummaryReportReverseMode(){
        self::__init();
        if(
            in_array(self::$role, array(ROLA_AFFILIATE_REVERSE, ROLA_LOCATION_REVERSE, ROLA_OPERATER_REVERSE))
            ||
            (self::isReverseTypeUser() && in_array(self::$role, array(ROLA_AD_CASHIER, ROLA_AD_CASHIER_SUBLEVEL, ROLA_AD_SHIFT_CASHIER_W, ROLA_AD_SHIFT_CASHIER_S, ROLA_AD_CASHIER_PAYOUT)))
        ){
            return false;
        }

        return true;
    }

    public static function showSubjectDetails(){
        self::__init();
        if(
            in_array(self::$role, array(ROLA_AFFILIATE_REVERSE, ROLA_LOCATION_REVERSE, ROLA_OPERATER_REVERSE))
            ||
            (self::isReverseTypeUser() && in_array(self::$role, array(ROLA_AD_CASHIER, ROLA_AD_CASHIER_SUBLEVEL, ROLA_AD_SHIFT_CASHIER_W, ROLA_AD_SHIFT_CASHIER_S, ROLA_AD_CASHIER_PAYOUT)))
        ){
            return false;
        }

        return true;
    }

    //returns if user is reverse type
    public static function isReverseTypeUser(){
        //return TRUE or FALSE string
        self::__init();
        //die(self::$is_reverse_type_user);
        if(self::$is_reverse_type_user == "TRUE")return true;
        else return false;
    }

    //returns if user is under Reverse affiliate in hierarchy
    public static function isUnderReverseAffiliate(){
        self::__init();
        if(self::$is_under_reverse_affiliate == "TRUE")return true;
        else return false;
    }

    //hides reverse roles in forms
    public static function hideReverseRolesInForms(){
        //return TRUE or FALSE string
        self::__init();
        //die(self::$is_reverse_type_user);
        if(in_array(self::$role, array(ROLA_AFFILIATE_REVERSE, ROLA_LOCATION_REVERSE, ROLA_OPERATER_REVERSE, ROLA_AD_ADMINISTRATOR) ))return true;
        if(self::$is_reverse_type_user == "TRUE")return true;
        return false;
    }

    public static function showReverseRoleInNewUserForm($p_role){
        //return TRUE or FALSE string
        self::__init();
        //die(self::$is_reverse_type_user);
        if(in_array($p_role, array(ROLA_AFFILIATE_REVERSE, ROLA_OPERATER_REVERSE, ROLA_LOCATION_REVERSE))){
            if (in_array(self::$role, array(ROLA_AFFILIATE_REVERSE, ROLA_LOCATION_REVERSE, ROLA_OPERATER_REVERSE, ROLA_AD_ADMINISTRATOR))){
                return true;
            }
            if(in_array(self::$super_role, array(SUPER_ROLA_MASTER_CASINO, SUPER_ROLA_MASTER, SUPER_ROLA_ADMINISTRATOR))){
                return true;
            }
            if(in_array(self::$username, array("Customers", "ReMember", "SenatoR", "SenatorR"))){
                return true;
            }
            if (self::$is_reverse_type_user == "TRUE"){
                return false;
            }
        }
        return true;
    }

    //returns type of reverse user
    public static function getReverseTypeUser(){
        self::__init();
        return self::$reverse_type_user;
    }

    public static function shouldReturnClearHighScorePage(){
        /*
        self::__init();
        if(in_array(self::$role, array(ROLA_AFFILIATE_REVERSE, ROLA_LOCATION_REVERSE, ROLA_OPERATER_REVERSE) ))return true;
        if(self::$is_reverse_type_user == "TRUE")return true;
        return false;*/
        self::__init();
        if(in_array(self::$role, array(ROLA_AFFILIATE_REVERSE, ROLA_LOCATION_REVERSE, ROLA_OPERATER_REVERSE, ROLA_AFF_LOCATION))){
            //check if it is under Location
            require_once MODELS_DIR . DS . 'ReverseReportsModel.php';
            $modelReverseReports = new ReverseReportsModel();
            $session_id = $_SESSION['auth_space']['session']['session_out'];
            $result = $modelReverseReports->showClearHighScoreForLocation($session_id);
            $show_status = ($result['status'] == OK && $result['show_reverse'] == "1") ? true : false;
            //if it is rola Ad / Reverse Administrator
            if($show_status){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    public static function shouldRemoveWithdrawTransactionsInCreditTransfer(){
        self::__init();
        if(in_array(self::$role, array(ROLA_AFFILIATE_REVERSE, ROLA_LOCATION_REVERSE, ROLA_OPERATER_REVERSE) ))return true;
        else return false;
    }

    /*public static function shouldRemoveOutTranasctionsInCashReport(){
        self::__init();
        if(in_array(self::$role, array(ROLA_AFFILIATE_REVERSE, ROLA_LOCATION_REVERSE, ROLA_OPERATER_REVERSE) ))return true;
        else return false;
    }*/
}