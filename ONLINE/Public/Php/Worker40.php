<?php

function GetBaseData($data) {
	
	global $DBQ;
	
	$BaseData = $DBQ -> prep('SELECT `Auth_1`' . ($data == 2 ? ', `Auth_2`' : '') . ' FROM `scheme_authorization`') -> fetchAll(PDO :: FETCH_NUM);
	
	$Result = array('FieldName' => array(), 'FieldData' => array());

	foreach ($BaseData as $Key => $Val) if ($Key > 0) {

		if (! array_key_exists($Val[0], $Result['FieldData'])) $Result['FieldData'][$Val[0]] = array();
	
		if ($data == 2) array_push($Result['FieldData'][$Val[0]], $Val[1]);

	} else {
				
		$Result['FieldName'][0] = $Val[0];
				
		if ($data == 2) $Result['FieldName'][1] = $Val[1];
				
	}

	return $Result;
	
}

function NewUser($AuthData) {
	
	global $_POST;
	
	global $DBQ;
	
	global $_SERVER;
	
	$Hash = isset($_POST['AuthHash']) ? hash('sha256', $_POST['AuthHash'] . 'mrs.Maysel') : false;

	$Data = array('LoginData' => $AuthData, 'UserAgent' => $_SERVER['HTTP_USER_AGENT']);

	if ($Hash) $Data['LoginHash'] = $Hash;
	
	$NewId = $DBQ -> prep('INSERT INTO `scheme_users` (`LoginData`' . ($Hash ? ', `LoginHash`' : '') . ', `UserAgent`, `Status`) VALUES (:LoginData' . ($Hash ? ', :LoginHash' : '') . ', :UserAgent, 1)', $Data);
	
	$DBQ -> prep('CREATE TABLE u' . $NewId . ' (QName VARCHAR(14) NOT NULL UNIQUE PRIMARY KEY, QResponse TEXT NOT NULL, QSI SMALLINT(5) NULL, Journal SMALLINT(5) NULL, TimeStamp TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB');

	return $NewId;
	
}



function RuleData($StateIndex, $Uid) {
	
	global $DBQ;
	
	global $_POST;

	$Rules = $DBQ -> prep('SELECT `rscheme`.* FROM `rscheme` WHERE `rscheme`.`Event` = "VISUAL" AND `rscheme`.`StateIndex` =' . $StateIndex) -> fetchAll(PDO :: FETCH_ASSOC);
		
	if (count($Rules)) { // строки визуальных правил
	
		$Result = array();
	
		$ResponseData = array();

		foreach($Rules as $Rule) { // Rule - строка визуальных правил
		
			$Trigger = str_replace('.', '_', $Rule['TriggerVar']);
			
			foreach($_POST as $PostKey => $PostValue) if (preg_match('/' . $Trigger . '$|' . $Trigger . '_.+/', $PostKey)) $ResponseData[] = array('QName' => $PostKey, 'QResponse' => $PostValue, 'QSI' => $_POST['StateIndex']);
				
			if (count($ResponseData) == 0) $ResponseData = $DBQ -> prep('SELECT `QName`, `QResponse`, `QSI` FROM `u' . $Uid . '` WHERE `QName` = "' . $Trigger . '" OR `QName` LIKE "' . $Trigger . '!_%" ESCAPE "!"') -> fetchAll(PDO :: FETCH_ASSOC);

			if (! preg_match('/ROW|COL/', $Rule['Selection'])) {
			
				$QData = $DBQ -> prep('SELECT `ColsContent`, `RowsContent`, `InputType`, `OutMark` FROM `qscheme` WHERE `id` = ' . $ResponseData[0]['QSI']) -> fetch(PDO :: FETCH_ASSOC);
				
				if ($QData['InputType'] == 'radio' || $QData['InputType'] == 'checkbox') {
				
					$TableData = $QData['OutMark'] ? explode('|', $QData['ColsContent']) : explode('|', $QData['RowsContent']);
	
					$ResponseData[0]['QResponse'] = $TableData[$ResponseData[0]['QResponse'] - 1];
				
				}
			
			}

			if (isset($ResponseData)) {

				$Result[$Trigger] = array('Action' => $Rule['Action'], 'Target' => $Rule['Target'], 'Equation' => $Rule['Equation'], 'Operation' => $Rule['Operation'], 'Selection' => $Rule['Selection'], 'ResponseData' => $ResponseData);
				
				$ResponseData = array();
			
			}

		}
		
		return count($Result) ? $Result : false;

	} else return false;

}

function GetQ($index) {
	
	global $DBQ; 

	$a = $DBQ -> prep('SELECT * FROM `qscheme` WHERE `qscheme`.`id` = :index', array('index' =>  $index)) -> fetch(PDO :: FETCH_ASSOC);
	
	if (isset($a['ColsContent']) && preg_match('/^\[content\](.+)$/', $a['ColsContent'], $FN)) {
					
		$FileName = '../../ContentData/' . $FN[1];
		
		if (file_exists($FileName)) {
		
			$lines = file_get_contents($FileName);
			
			$a['ColsContent'] = $lines;
	
		}
		
	}

	return $a;
	
}

?>