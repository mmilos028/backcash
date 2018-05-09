<?php

class SkillActionFormatterHelper{
    public static function format($action){
        $result = "";

        if(!is_null($action)){
            $action = rtrim(substr($action,21), ";");
            $action_array = explode(":",$action);

            foreach($action_array as $key=>$val){
                if($val == 0){
                    $result = "0";
                    break;
                } elseif( $val == 10){
                    $result = "A";
                    break;
                }
            }

            if($result ==""){
                $result = $action;
            }
        }

        return $result;
    }
}


