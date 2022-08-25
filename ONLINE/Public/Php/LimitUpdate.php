<?php

$LimitData = $DBQ -> prep('SELECT * FROM `lscheme`') -> fetchAll(PDO :: FETCH_ASSOC);

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

?>