<?php
/* Helper class to data conversion from Oracle database ref cursor into php arrays */
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
class CursorToArrayHelper {
	private $cursorData;
	private $firstRow;
	private $arrayData;
	public function __construct($_cursorData){
		$this->cursorData = $_cursorData;
		$this->cursorToIteratorOperation();
	}
	
	//sort array by key element	
	public static function aasort (&$array, $key, $direction = 'ASC') {
		$sorter=array();
		$ret=array();
		reset($array);
		foreach ($array as $ii => $va) {
			$sorter[$ii]=$va[$key];
		}
		if(strtoupper($direction) == 'ASC')
			asort($sorter);
		else{
			arsort($sorter);
		}
		foreach ($sorter as $ii => $va) {
			$ret[$ii]=$array[$ii];
		}
		$array=$ret;
		return $ret;
	}

     public static function sortMultiDimArray($arrData, $key_name){
        $sort_by = array();
        foreach ($arrData as $key => $data) {
            $sort_by[$key] = $data[$key_name];
        }
        //array_multisort($sort_by, SORT_ASC, SORT_STRING, $arrData);
        array_multisort($sort_by, SORT_STRING | SORT_FLAG_CASE, $arrData);
        return $arrData;
    }
	
	//fills in firstRow data about pagination
	public function cursorToIteratorOperation(){
		//load arrayData with data from table except first row which is pagination information
		$i=0;
		$this->firstRow = array();
		$this->arrayData = array();
		foreach($this->cursorData as $row){
			if($i == 0)$this->firstRow[] = $row;
			else $this->arrayData[] = $row;
			$i++;
		}
	}
	//converts cursor from database to array of php elements
	public static function cursorToArray($cursor){
		$arrData = array();
		foreach($cursor as $row)$arrData[] = $row;
		return $arrData;
	}
	//converts cursor to array without zend cursor object
	public static function cursorToCleanArray($arrData){
		$i=0;
        $result = array();
		foreach($arrData as $data){
			foreach((array)$data as $value)
				$result[$i] = $value;
			$i++;
		}
		return $result;
	}
	//list only affiliates in cursor
	public static function cursorToArrayAffiliates($cursor, $filter){
		$arrData = array();
		foreach($cursor as $row){
			if($row['role'] == $filter){
				$arrData[] = $row;
			}
		}
		return $arrData;
	}
	//used to generate terminals tree
	public function playersTree($base_url){
		$this->arrayData = array();
		foreach($this->cursorData as $row){
			if(isset($row) && !is_null($row)){
				$new_row['id'] = $row['id'];
				$new_row['parent_id'] = $row['parent_id'];
				$new_row['aff_id'] = $row['aff_id'];
				$new_row['type'] = $row['rola'];
				$locale = Zend_Registry::get('lang');
				if($row['rola'] == SUPER_ROLA_AFFILIATES){
					$new_row['type'] = "Affiliate";
					$new_row['text'] = "<a href=" . $base_url . '/' . $locale . "/affiliate/details/affiliate/" . $new_row['aff_id'] . ">" . htmlspecialchars($row['text']) . "</a>";
					$url_reset_password = $base_url . '/' . $locale . "/affiliate/reset-password/affiliate/" . $row['aff_id'];
					$imgtxt_reset_password = "<img src='" . $base_url . "/images/icons/user_edit.png' alt='Reset Password' title='Reset Password' />";
					$new_row['actions'] = "&nbsp;&nbsp;<a href='$url_reset_password'>" . $imgtxt_reset_password . "</a>";
					$url_update_aff = $base_url . '/' . $locale . "/affiliate/update/affiliate/" . $row['aff_id'];
					$imgtxt_update_aff = "<img src='" . $base_url . "/images/icons/application_form_edit.png' alt='Update Affiliate' title='Update Affiliate' />";
					$new_row['actions'] .= "&nbsp;&nbsp;&nbsp;<a href='$url_update_aff'>" . $imgtxt_update_aff . "</a>";
					$url_games_for_aff = $base_url . '/' . $locale . "/affiliate/games-for-affiliate/affiliate/" . $row['aff_id'];
					$imgtxt_games_for_aff = "<img src='" . $base_url . "/images/icons/application_view_tile.png' alt='List Games For Affiliate' title='List Games For Affiliate' />";
					$new_row['actions'] .= "&nbsp;&nbsp;&nbsp;<a href='$url_games_for_aff'>" . $imgtxt_games_for_aff . "</a>";
				}
				if($row['rola'] == SUPER_ROLA_PLAYER){
					$new_row['type'] = "Player";
					$new_row['text'] = "<a href=" . $base_url . '/' . $locale . "/players/details/player/" . $new_row['aff_id'] . ">" . htmlspecialchars($row['text']) . "</a>";
					$url_reset_password = $base_url . '/' . $locale . "/players/reset-password/player/" . $row['aff_id'];
					$imgtxt_reset_password = "<img src='" . $base_url . "/images/icons/user_edit.png' alt='Reset Password' title='Reset Password' />";
					$new_row['actions'] = "&nbsp;&nbsp;<a href='$url_reset_password'>" . $imgtxt_reset_password . "</a>";
					$url_update_player = $base_url . '/' . $locale . "/players/update/player/" . $row['aff_id'];
					$imgtxt_update_terminal = "<img src='" . $base_url . "/images/icons/application_form_edit.png' alt='Update Player' title='Update Player' />";
					$new_row['actions'] .= "&nbsp;&nbsp;&nbsp;<a href='$url_update_player'>" . $imgtxt_update_terminal . "</a>";
				}
				if($row['rola'] != SUPER_ROLA_AFFILIATES && $row['rola'] != SUPER_ROLA_PLAYER){
					$new_row['type'] = "";
					$new_row['text'] = "<a href=" . $base_url . '/' . $locale . "/affiliate/details/affiliate/" . $new_row['aff_id'] . ">" . htmlspecialchars($row['text']) . "</a>";
					$url_reset_password = $base_url . '/' . $locale . "/affiliate/reset-password/affiliate/" . $row['aff_id'];
					$imgtxt_reset_password = "<img src='" . $base_url . "/images/icons/user_edit.png' alt='Reset Password' title='Reset Password' />";
					$new_row['actions'] = "&nbsp;&nbsp;<a href='$url_reset_password'>" . $imgtxt_reset_password . "</a>";
					$url_update_aff = $base_url . '/' . $locale . "/affiliate/update/affiliate/" . $row['aff_id'];
					$imgtxt_update_aff = "<img src='" . $base_url . "/images/icons/application_form_edit.png' alt='Update Affiliate' title='Update Affiliate' />";
					$new_row['actions'] .= "&nbsp;&nbsp;&nbsp;<a href='$url_update_aff'>" . $imgtxt_update_aff . "</a>";
					$url_games_for_aff = $base_url . '/' . $locale . "/affiliate/games-for-affiliate/affiliate/" . $row['aff_id'];
					$imgtxt_games_for_aff = "<img src='" . $base_url . "/images/icons/application_view_tile.png' alt='List Games For Affiliate' title='List Games For Affiliate' />";
					$new_row['actions'] .= "&nbsp;&nbsp;&nbsp;<a href='$url_games_for_aff'>" . $imgtxt_games_for_aff . "</a>";
				}
				$this->arrayData[] = $new_row;
			}
		}
		return $this->arrayData;
	}
	//used to generate terminals tree
	public function terminalsTree($base_url){
		$this->arrayData = array();
		foreach($this->cursorData as $row){
			if(isset($row) && !is_null($row)){
				$new_row['id'] = $row['id'];
				$new_row['parent_id'] = $row['parent_id'];
				$new_row['aff_id'] = $row['aff_id'];
				$locale = Zend_Registry::get('lang');
				if($row['rola'] == SUPER_ROLA_AFFILIATES){
					$new_row['type'] = "Affiliate";
					$new_row['text'] = "<a href=" . $base_url . '/' . $locale . "/affiliate/details/affiliate/" . $new_row['aff_id'] . ">" . htmlspecialchars($row['text']) . "</a>";
					$url_reset_password = $base_url . '/' . $locale . "/affiliate/reset-password/affiliate/" . $row['aff_id'];
					$imgtxt_reset_password = "<img src='" . $base_url . "/images/icons/user_edit.png' alt='Reset Password' title='Reset Password' />";
					$new_row['actions'] = "&nbsp;&nbsp;<a href='$url_reset_password'>" . $imgtxt_reset_password . "</a>";
					$url_update_aff = $base_url . '/' . $locale . "/affiliate/update/affiliate/" . $row['aff_id'];
					$imgtxt_update_aff = "<img src='" . $base_url . "/images/icons/application_form_edit.png' alt='Update Affiliate' title='Update Affiliate' />";
					$new_row['actions'] .= "&nbsp;&nbsp;&nbsp;<a href='$url_update_aff'>" . $imgtxt_update_aff . "</a>";
					$url_games_for_aff = $base_url . '/' . $locale . "/affiliate/games-for-affiliate/affiliate/" . $row['aff_id'];
					$imgtxt_games_for_aff = "<img src='" . $base_url . "/images/icons/application_view_tile.png' alt='List Games For Affiliate' title='List Games For Affiliate' />";
					$new_row['actions'] .= "&nbsp;&nbsp;&nbsp;<a href='$url_games_for_aff'>" . $imgtxt_games_for_aff . "</a>";
				}
				if($row['rola'] == SUPER_ROLA_PLAYER){
					$new_row['type'] = "Terminal Player";
					$new_row['text'] = "<a href=" . $base_url . '/' . $locale . "/terminal-players/details/terminal-player/" . $new_row['aff_id'] . ">" . htmlspecialchars($row['text']) . "</a>";
					$url_reset_password = $base_url . '/' . $locale . "/terminal-players/reset-password/terminal-player/" . $row['aff_id'];
					$imgtxt_reset_password = "<img src='" . $base_url . "/images/icons/user_edit.png' alt='Reset Password' title='Reset Password' />";
					$new_row['actions'] = "&nbsp;&nbsp;<a href='$url_reset_password'>" . $imgtxt_reset_password . "</a>";
					$url_update_terminal = $base_url . '/' . $locale . "/terminal-players/update/terminal-player/" . $row['aff_id'];
					$imgtxt_update_terminal = "<img src='" . $base_url . "/images/icons/application_form_edit.png' alt='Update Terminal Player' title='Update Terminal Player' />";
					$new_row['actions'] .= "&nbsp;&nbsp;&nbsp;<a href='$url_update_terminal'>" . $imgtxt_update_terminal . "</a>";					
				}
				if($row['rola'] != SUPER_ROLA_AFFILIATES && $row['rola'] != SUPER_ROLA_PLAYER){
					$new_row['type'] = "";
					$new_row['text'] = "<a href=" . $base_url . '/' . $locale . "/affiliate/details/affiliate/" . $new_row['aff_id'] . ">" . htmlspecialchars($row['text']) . "</a>";
					$url_reset_password = $base_url . '/' . $locale . "/affiliate/reset-password/affiliate/" . $row['aff_id'];
					$imgtxt_reset_password = "<img src='" . $base_url . "/images/icons/user_edit.png' alt='Reset Password' title='Reset Password' />";
					$new_row['actions'] = "&nbsp;&nbsp;<a href='$url_reset_password'>" . $imgtxt_reset_password . "</a>";
					$url_update_aff = $base_url . '/' . $locale . "/affiliate/update/affiliate/" . $row['aff_id'];
					$imgtxt_update_aff = "<img src='" . $base_url . "/images/icons/application_form_edit.png' alt='Update Affiliate' title='Update Affiliate' />";
					$new_row['actions'] .= "&nbsp;&nbsp;&nbsp;<a href='$url_update_aff'>" . $imgtxt_update_aff . "</a>";
					$url_games_for_aff = $base_url . '/' . $locale . "/affiliate/games-for-affiliate/affiliate/" . $row['aff_id'];
					$imgtxt_games_for_aff = "<img src='" . $base_url . "/images/icons/application_view_tile.png' alt='List Games For Affiliate' title='List Games For Affiliate' />";
					$new_row['actions'] .= "&nbsp;&nbsp;&nbsp;<a href='$url_games_for_aff'>" . $imgtxt_games_for_aff . "</a>";
				}
				$this->arrayData[] = $new_row;
			}
		}
		return $this->arrayData;
	}
	
	//used to generate affiliates tree
	public function affiliatesTree($base_url){
		$this->arrayData = array();
		foreach($this->cursorData as $row){
			if(isset($row) && !is_null($row)){
				$new_row['id'] = $row['id'];
				$new_row['parent_id'] = $row['parent_id'];
				$new_row['aff_id'] = $row['aff_id'];
				$locale = Zend_Registry::get('lang');
				$new_row['text'] = "<a href=" . $base_url . '/' . $locale . "/affiliate/details/affiliate/" . $new_row['aff_id'] . ">" . htmlspecialchars($row['text']) . "</a>";
				$url_reset_password = $base_url . '/' . $locale . "/affiliate/reset-password/affiliate/" . $row['aff_id'];
				$imgtxt_reset_password = "<img src='" . $base_url . "/images/icons/user_edit.png' alt='Reset Password' title='Reset Password' />";
				$new_row['actions'] = "&nbsp;&nbsp;<a href='$url_reset_password'>" . $imgtxt_reset_password . "</a>";				
				$url_update_aff = $base_url . '/' . $locale . "/affiliate/update/affiliate/" . $row['aff_id'];
				$imgtxt_update_aff = "<img src='" . $base_url . "/images/icons/application_form_edit.png' alt='Update Affiliate' title='Update Affiliate' />";
				$new_row['actions'] .= "&nbsp;&nbsp;&nbsp;<a href='$url_update_aff'>" . $imgtxt_update_aff . "</a>";
				$url_games_for_aff = $base_url . '/' . $locale . "/affiliate/games-for-affiliate/affiliate/" . $row['aff_id'];
				$imgtxt_games_for_aff = "<img src='" . $base_url . "/images/icons/application_view_tile.png' alt='List Games For Affiliate' title='List Games For Affiliate' />";
				$new_row['actions'] .= "&nbsp;&nbsp;&nbsp;<a href='$url_games_for_aff'>" . $imgtxt_games_for_aff . "</a>";				
				$this->arrayData[] = $new_row;
			}
		}
		return $this->arrayData;
	}
	
	//affiliates tree for game analyser report
	public function affiliatesTreeForGameAnalyser($url){
		$this->arrayData = array();
		foreach($this->cursorData as $row){
			if(isset($row) && !is_null($row)){
				$new_row['id'] = $row['id'];
				$new_row['parent_id'] = $row['parent_id'];
				$new_row['aff_id'] = $row['aff_id'];
				$new_row['text'] = "<a href=" . $url . "/" . $new_row['aff_id'] . ">" . htmlspecialchars($row['text']) . "</a>";
				$this->arrayData[] = $new_row;
			}
		}
		return $this->arrayData;
	}
	
	//affiliates tree for EXT JS component
	public function affiliatesTreeText(){
		$this->arrayData = array();
		foreach($this->cursorData as $row){
			if(isset($row) && !is_null($row)){
				$new_row['id'] = $row['id'];
				$new_row['parent_id'] = $row['parent_id'];
				$new_row['aff_id'] = $row['aff_id'];
				$new_row['text'] = $row['text'];
				$new_row['role'] = $row['role'];
				$new_row['type'] = $row['role'];
				$this->arrayData[] = $new_row;
			}
		}
		return $this->arrayData;
	}
	
	//load all data from table into arrayData for menues
	public function getMenuesArray(){
		$this->arrayData = array();
		foreach($this->cursorData as $row)$this->arrayData[] = $row['name'];
		return $this->arrayData;
	}
	
	//return first row data in table
	public function getPageRow(){ return $this->firstRow; }
	
	//return table data
	public function getTableRows(){return $this->arrayData; }
	
	static function getExceptionTraceAsString($exception) {
		$rtn = "";
		$count = 0;
		foreach ($exception->getTrace() as $frame) {
			$args = "";
			if (isset($frame['args'])) {
				$args = array();
				foreach ($frame['args'] as $arg) {
					if (is_string($arg)) {
						$args[] = "'" . $arg . "'";
					} elseif (is_array($arg)) {
						$args[] = "Array";
					} elseif (is_null($arg)) {
						$args[] = 'NULL';
					} elseif (is_bool($arg)) {
						$args[] = ($arg) ? "true" : "false";
					} elseif (is_object($arg)) {
						$args[] = get_class($arg);
					} elseif (is_resource($arg)) {
						$args[] = get_resource_type($arg);
					} else {
						$args[] = $arg;
					}
				}
				$args = join(", ", $args);
			}
			$rtn .= sprintf( "#%s %s(%s): %s(%s)\n",
			$count,
			$frame['file'],
			$frame['line'],
			$frame['function'],
			$args );
			$count++;
		}
		$rtn = $exception->getMessage() . "<br /><br />" .  $rtn;
		return $rtn;
	}
}