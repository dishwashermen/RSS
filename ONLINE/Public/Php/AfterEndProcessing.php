<?php
// Запись квот ------------------------------------------------------------------------------------------------------------------

if ($UserStatus == 3 && $_POST['ProjectLimiting'] == 'ON') {
	
	$LimitData = $DBQ -> prep('SELECT * FROM `lscheme` WHERE `lscheme`.`Disabled` IS NULL') -> fetchAll(PDO :: FETCH_ASSOC);

	$LimitIndex = $DBQ -> prep('SELECT `StateIndex` FROM `lscheme` GROUP BY `StateIndex`') -> fetchAll(PDO :: FETCH_NUM);

	$LimitIndexData = array_map('reset', $LimitIndex);

	$WorkData = $DBQ -> prep('SELECT `QName`, `QResponse` FROM `u' . $_POST['UserId'] . '` WHERE `QSI` = ' . implode(' OR `QSI` = ', $LimitIndexData)) -> fetchAll(PDO :: FETCH_ASSOC);

	$UR = array();

	foreach ($WorkData as $WD) $UR[$WD['QName']] = $WD['QResponse'];

	foreach ($LimitData as $LD) {

		$QNData = explode(',', $LD['QN']);
		
		$TargetData = explode(',', $LD['Target']);
		
		$HIT = true;
		
		for ($i = 0; $i < count($QNData); $i ++) {
			
			if ($UR[str_replace('.', '_', $QNData[$i])] != $TargetData[$i]) {
				
				$HIT = false;
				
				break;
				
			}
			
		}
		
		if ($HIT) {
			
			$DBQ -> prep('UPDATE `lscheme` SET `lscheme`.`Contains` = ' . ($LD['Contains'] + 1) . ' WHERE `lscheme`.`Id` = :Id', array('Id' => $LD['Id']));
			
			array_push($LimitedHIT, $LD['Id']);
			
		}
		
	}
	
}

// ------------------------------------------------------------------------------------------------------------------------------

// Запись допполей из базы ------------------------------------------------------------------------------------------------------

if ($_POST['AuthType'] == 'base') {
	
	$UserLogin = $DBQ -> prep('SELECT `LoginData` FROM `vscheme` WHERE `Uid` = :UserId', array('UserId' => $_POST['UserId'])) -> fetch(PDO :: FETCH_ASSOC);
	
	$LoginData = explode(',', $UserLogin['data1']);
	
	$BaseData = $DBQ -> prep('SELECT * FROM `scheme_authorization` WHERE (`Auth_1` = "' . $LoginData[0] . '"' . (count($LoginData) > 1 ? ' AND `Auth_2` = "' . $LoginData[1] . '")': ')') . ' OR `Id` = 1') -> fetchAll(PDO :: FETCH_ASSOC);
	
	if (isset($BaseData[0]['Data_1'])) {
		
		$QueryString = '';
		
		$QueryData = array();
		
		$Data = array();
		
		foreach($BaseData[0] as $Key => $Val) {
			
			if (! Preg_match('/Id|Auth_1|Auth_2/', $Key)) {
				
				$FieldName = $Val;
				
				$FieldValue = $BaseData[1][$Key];
				
				$QueryString = '(:' . $Key . '_name, :' . $Key . '_val, 0)';
				
				array_push($QueryData, $QueryString);
				
				$Data[$Key . '_name'] = $FieldName;
				
				$Data[$Key . '_val'] = $FieldValue;
				
				//$a = $DBQ -> prep('INSERT INTO `u' . $_POST['UserId'] . '` (`QName`, `QResponse`, `QSI`) VALUES ' . implode(',', $QueryData) . ' ON DUPLICATE KEY UPDATE QResponse = VALUES(QResponse), QSI = VALUES(QSI), TimeStamp = CURRENT_TIMESTAMP', $Data);
				
			}
			
		}
		
	}
	
}

?>