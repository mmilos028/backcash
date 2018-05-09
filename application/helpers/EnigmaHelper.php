<?php
//class that does encrypting of data
class EnigmaHelper {

	private static $bicrypt_key = "1d109fdebae5e3a0cd8b547e763051cb";
	private static $monocrypt_key = "2O6742cM4U6849QEc7p1Fw0C8564xC3s";
	private static $url_bicrypt_key = "df507f6524f839f23800d5";

    public static function cryptAdd($string){
        //return $string;
        $newstr = "";
        $arrData = str_split($string, 1);
        foreach($arrData as $str){
            $newstr .= chr(ord($str) +1);
        }
        return $newstr;
    }

    public static function decryptAdd($string){
        //return $string;
        $newstr = "";
        $arrData = str_split($string, 1);
        foreach($arrData as $str){
            $newstr .= chr(ord($str) -1);
        }
        return $newstr;
    }


	public static function encrypt($string) {
		$enc_key = pack('H*', self::$bicrypt_key);
		$size = mcrypt_get_iv_size(MCRYPT_CAST_256, MCRYPT_MODE_CBC);
		$iv = mcrypt_create_iv($size, MCRYPT_DEV_URANDOM);
		$cipherstring = mcrypt_encrypt(MCRYPT_CAST_256, $enc_key, $string, MCRYPT_MODE_CBC, $iv);
		$cipherstring = $iv . '__$#Av_' . $cipherstring;
		$cipherstring_base64 = base64_encode($cipherstring);
		return $cipherstring_base64;
	}

	public static function decrypt($string) {
		$enc_key = pack('H*', self::$bicrypt_key);
		$cipherstring = base64_decode($string);
		$cipherarray = explode('__$#Av_', $cipherstring);
		$iv_dec = $cipherarray[0];
		$cipherstring = $cipherarray[1];
		$string = mcrypt_decrypt(MCRYPT_CAST_256, $enc_key, $cipherstring, MCRYPT_MODE_CBC, $iv_dec);
		return $string;
	}
	
	public static function crypt($string, $key=null) {
		if(!$key) {
			$key = self::$monocrypt_key;
		}
		return crypt($string, "$6$".$key."$");
	}
	
	public static function encryptURL($url){
		$result = '';
		if($url!='') {
			$enc_key = pack('H*', self::$url_bicrypt_key);
			$size = mcrypt_get_iv_size(MCRYPT_CAST_128, MCRYPT_MODE_CBC);
			$iv = mcrypt_create_iv($size, MCRYPT_DEV_URANDOM);
			$cipherstring = mcrypt_encrypt(MCRYPT_CAST_128, $enc_key, $url, MCRYPT_MODE_CBC, $iv);
			$cipherstring = $iv . '__$#Av_' . $cipherstring;
			$cipherstring_base64 = base64_encode($cipherstring);
			$result = trim(strtr($cipherstring_base64, '+/=', '-_,'));
		}
		return $result;
	}
	
	public static function decryptURL($url) {
		$url = trim($url);
		$url = substr($url,1);
		// die($url);
		if($url) {
			$enc_key = pack('H*', self::$url_bicrypt_key);
			//$cipherstring = base64_decode($url);
			$cipherstring = base64_decode(strtr($url, '-_,', '+/='));
			//print_r($cipherstring);die;
			$size = mcrypt_get_iv_size(MCRYPT_CAST_128, MCRYPT_MODE_CBC);
			//$iv = mcrypt_create_iv($size, MCRYPT_DEV_URANDOM);
			$cipherarray = explode('__$#Av_', $cipherstring);
			//if( $size == mcrypt_get_iv_size($cipherarray[0],MCRYPT_MODE_CBC )){
				$iv_dec = $cipherarray[0];
				$cipherstring = $cipherarray[1];
				$url = mcrypt_decrypt(MCRYPT_CAST_128, $enc_key, $cipherstring, MCRYPT_MODE_CBC, $iv_dec);
				return '/' . trim($url);
			//}
			//else return '';
		}
		return trim($url);
	}
}
    