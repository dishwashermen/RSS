<?php 

$Vid = $DBQ -> prep('SELECT `Id` FROM `vscheme` WHERE `vscheme`.`Uid` = :Uid', array('Uid' => $_POST['UserId'])) -> fetch(PDO :: FETCH_ASSOC);

$ColumnName = array();
	
$UpdateArray = array();
	
$ValueData = array();
	
$ValueArray = array();

if ($ViewContent) {
	
	$ColumnData = array();
	
	$Index = 0;
	
	foreach ($ViewContent as $VK => $VV) {
		
		$Index ++;

		$Column = $DBQ -> prep('SHOW COLUMNS FROM `vscheme` LIKE "' . $VK . '"') -> fetch(PDO :: FETCH_NUM);
		
		if ($Column === false) array_push($ColumnData, 'ADD COLUMN `' . $VK . '` VARCHAR(255) NULL');
		
		array_push($UpdateArray, '`' . $VK . '` = :d' . $Index);
		
		array_push($ColumnName, $VK);
		
		array_push($ValueData, ':d' . $Index);
		
		$ValueArray[':d' . $Index] = $VV;
		
	}
	
	if (count($ColumnData)) $DBQ -> prep('ALTER TABLE `vscheme` ' . implode(',', $ColumnData));
	
}

if ($Vid) {
	
	$Data = array_merge(array('Uid' => $_POST['UserId'], 'Status' => $UserStatus, 'StateIndex' => $_POST['StateIndex']), $ValueArray);
	
	if (count($LimitedHIT)) $Data['LimitedHIT'] = implode(',', $LimitedHIT);
	
	$a = $DBQ -> prep('UPDATE `vscheme` SET `Status` = :Status, `StateIndex` = :StateIndex' . (count($LimitedHIT) ? ', `Limited` = :LimitedHIT' : '') . (count($UpdateArray) ? ', ' . implode(', ', $UpdateArray) : '') . ' WHERE `Uid` = :Uid', $Data);
	
} else {
	
	$UserData = $DB -> prep('SELECT `data1` FROM `users` WHERE `users`.`id` = :id', array('id' => $_POST['UserId'])) -> fetch(PDO :: FETCH_ASSOC);
	
	$a = $DBQ -> prep('INSERT INTO `vscheme` (`Uid`, `Status`, `StateIndex`, `UserAgent`, `LoginData`' . (count($ColumnName) ? ', `' . implode('`, `', $ColumnName) . '`' : '') . ') VALUES (:Uid, :Status, :StateIndex, :UserAgent, :LoginData' . (count($ValueData) ? ', ' . implode(', ', $ValueData) : '') . ')', array_merge(array('Uid' => $_POST['UserId'], 'Status' => $UserStatus, 'StateIndex' => $_POST['StateIndex'], 'UserAgent' => $_SERVER['HTTP_USER_AGENT'], 'LoginData' => $UserData['data1']), $ValueArray));
	
}


?>