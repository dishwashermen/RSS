<?php 

if ($ViewContent) {
	
	$ColumnName = array();
	
	$UpdateArray = array();
		
	$ValueData = array();
		
	$ValueArray = array();
	
	$ColumnData = array();
	
	$Index = 0;
	
	foreach ($ViewContent as $VK => $VV) {
		
		$Index ++;

		$Column = $DBQ -> prep('SHOW COLUMNS FROM `scheme_users` LIKE "' . $VK . '"') -> fetch(PDO :: FETCH_NUM);
		
		if ($Column === false) array_push($ColumnData, 'ADD COLUMN `' . $VK . '` VARCHAR(255) NULL');
		
		array_push($UpdateArray, '`' . $VK . '` = :d' . $Index);
		
		array_push($ColumnName, $VK);
		
		array_push($ValueData, ':d' . $Index);
		
		$ValueArray[':d' . $Index] = $VV;
		
	}
	
	if (count($ColumnData)) $DBQ -> prep('ALTER TABLE `scheme_users` ' . implode(',', $ColumnData));
	
	$Data = array_merge(array('Uid' => $_POST['UserId']), $ValueArray);

	$a = $DBQ -> prep('UPDATE `scheme_users` SET ' . implode(', ', $UpdateArray) . ' WHERE `Id` = :Uid', $Data);
	
}

?>