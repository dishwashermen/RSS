<?php

$rule = (array) json_decode($_POST['AutoFill']);
	
$RuleStructure = $DBQ -> prep('SELECT * FROM `[r]structure` WHERE `[r]structure`.`RId` = ' . $rule['Id']) -> fetchAll(PDO :: FETCH_ASSOC);

$COMMON_RESULT = false;

foreach ($RuleStructure as $RSK => $RSV) {
	
	$TriggerVar = str_replace('.', '_', $RSV['TriggerVar']);
	
	$Target = $RSV['Target'];
	
	$Operation = $RSV['Operation'];
	
	$Action = $RSV['Action'];
	
	$Selection = $RSV['Selection'];

	if (! isset($_POST[$TriggerVar])) {
		
		$TRIGGER_request = $DBQ -> prep('SELECT `QResponse` FROM `u' . $_POST['UserId'] . '` WHERE `QName` = :qname', array('qname' => $TriggerVar)) -> fetch(PDO :: FETCH_ASSOC);
		
		$TRIGGER_VALUE = $TRIGGER_request ? $TRIGGER_request['QResponse'] : null;

	} else $TRIGGER_VALUE = $_POST[$TriggerVar];
	
	switch ($Operation) {
		
		case 'EQUAL': $INTERIM_RESULT = $TRIGGER_VALUE == $Target ? true : false;
		
		break;
		
	}
	
	if ($INTERIM_RESULT) {
		
		$a = $DBQ -> prep('INSERT INTO `u' . $_POST['UserId'] . '` (`QName`, `QResponse`, `QSI`) VALUES ("' . $Action . '", "' . $TRIGGER_VALUE . '", "' . $Selection . '") ON DUPLICATE KEY UPDATE QResponse = VALUES(QResponse), QSI = VALUES(QSI), TimeStamp = CURRENT_TIMESTAMP');
		
	}
	
}

?>