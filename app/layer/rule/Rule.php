<?php
require_once getLibFilePath("util.StringHelper");

class Rule {
	protected $DBDriver;
	
	public function setDBDriver($DBDriver) {
		$this->DBDriver = $DBDriver;
	}
	
	protected function addSlashes(&$text) {
		$text = addcslashes($text, "'\\");
		return $text;
	}
	
	protected function setData($sql) {
		$status = $this->DBDriver->setData($sql);
		
		if ($status === true) {
			return true;
		}
		
		//TODO: log error
		echo $status[$sql]."\n";
		return false;
	}
	
	protected function getData($sql) {
		$result = $this->DBDriver->getData($sql);
		
		if (!$result["ERROR"]) {
			return $result["RESULT"];
		}
		
		//TODO: log error
		//echo $status["ERROR"];
		return false;
	}
	
	//get current timestamp YYYY-MM-DD HH:MM:SS
	protected function getCurrentTimestamp(){
		return date('Y-m-d H:i:s');
	}
	
	
	//get comma seperate string from array
	protected function getCSVStringFromArray($data){
		$str = "";
	
		if(!empty($data)){
			foreach($data as $row){
				$str .= "'";
				$str .= $row;
				$str .= "'";
				$str .= ",";
			}
			
			$str = substr($str, 0, -1); //remove last comma
		}
		
		return $str;
	}
	
	//get hash code of the string (should be consistent with JAVA platform) 2011-11-07
	protected static function getTextCode($str){
		$hash = 0;
		
		$n = (int) ((strlen($str) / 2) + 1);
		
		$first_half = substr($str, 0, $n);
		$second_half = substr($str, $n);
		
		$mainId = StringHelper::getHashCodePositive($str);
		$firstId = StringHelper::getHashCodePositive($first_half);
		$secondId = StringHelper::getHashCodePositive($second_half);
		
		$n1 = strlen($firstId);
		if ($n1 > 4) {
			$n1 = 4;
		}
		$firstId = substr($firstId, 0, $n1);
		
		$n2 = strlen($secondId) - 4;
		if ($n2 < 0) {
			$n2 = 0;
		}
		$secondId = substr($secondId, $n2);
		
		$hash_str = $firstId . $secondId . $mainId;
		
		return $hash_str;
	}
	/**
	 * @param Date in mm/dd/yyyy format
	 */
	protected function getDateFromString($str) {
		$date = $str; 
		list($month, $day, $year) = split('[/.-]', $date);
		$result = $year."-".$month."-".$day;
		return $result;
		
	}
}


?>
