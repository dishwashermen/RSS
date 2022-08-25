<?php

function GetBaseData($data) {
	
	$BaseData = $DBQ -> prep('SELECT `Id`, `Auth_1`' . ($data == 2 ? ', `Auth_2`' : '') . ' FROM `scheme_authorization`') -> fetchAll(PDO :: FETCH_ASSOC);
	
	$in_data = array();
				
	$in_data_index = 0;
	
	foreach ($data as $lkey => $lval) {
		
		$ldata = explode(';', $lval); 
		
		foreach ($ldata as $ldata_key => $ldata_value) {
		
			if ($lkey != 0) {
					
				if ($lkey > 1 && $ldata_key == 0) {
					
					if (! in_array($ldata_value, $in_data[$ldata_key]['fieldData'][$in_data_index])) {
				
						$in_data_index ++;
						
						array_push($in_data[$ldata_key]['fieldData'], array($ldata_value));
						
						if (count($ldata) > 1) for ($i = 1; $i < count($ldata); $i ++) array_push($in_data[$ldata_key + $i]['fieldData'], array());
				
					}
					
				} else array_push($in_data[$ldata_key]['fieldData'][$in_data_index], $ldata_value);

			} else array_push($in_data, array('fieldName' => $ldata_value, 'fieldData' => array(array())));
			
		} 
			
	}
	
	return $in_data;
	
}

function NewUser($id, $data, $provisional = false, $hash = false) {
	
	global $DB;
	
	global $DBQ;
	
	global $_SERVER;
	
	$EmptyId = $DB -> prep('SELECT (`users`.`id` + 1) as `empty_id` FROM `users` WHERE (SELECT 1 FROM `users` as `st` WHERE `st`.`id` = (`users`.`id` + 1)) IS NULL ORDER BY `users`.`id` LIMIT 1') -> fetch(PDO :: FETCH_NUM)[0];
	
	$query = 'INSERT INTO `users` (`id`, `prid`, `data1`' . ($hash ? ', `hash`' : '') . ', `UserAgent`, `Status`) VALUES (' . $EmptyId . ', :prId, :data1' . ($hash ? ', :hash' : '') . ', :UserAgent, 1)';
	
	$array = array('prId' => $id, 'data1' => $data, 'UserAgent' => $_SERVER['HTTP_USER_AGENT']);

	if ($hash) $array = array_merge($array, array('hash' => $hash));
	
	$newId = $DB -> prep($query, $array);
	
	$DBQ -> prep('CREATE TABLE u' . $EmptyId . ' (QName VARCHAR(14) NOT NULL UNIQUE PRIMARY KEY, QResponse TEXT NOT NULL, QSI SMALLINT(5) NULL, Journal SMALLINT(5) NULL, TimeStamp TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB');
	
	if ($provisional) {
		
		$prov = explode('*', $provisional);
		
		foreach ($prov as $provval) {
			
			$provdata = explode('=', $provval);
			
			$newProv = $DBQ -> prep('INSERT INTO `u' . $EmptyId . '` (`QName`, `QResponse`, `QSI`) VALUES ("' . $provdata[0] . '", "' . $provdata[1] . '", 0)');
			
		}
		
	}

	return $EmptyId;
	
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