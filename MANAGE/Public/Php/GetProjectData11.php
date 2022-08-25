<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ProjectId']) && is_numeric($_POST['ProjectId'])) {
   
	require_once 'Functions.php';

	require_once 'DBSettings.php';
	
	require_once 'Process.php';
	
	$DB = new DBWORKER($dbu['host'], $dbu['db'], 'utf8', $dbu['user'], $dbu['pass']);
	
	if ($_POST['ProjectId'] == 8) $DBQ = new DBWORKER($dbu['host'], 'koksarea_hottest', 'utf8', $dbu['user'], $dbu['pass']);
	
	else $DBQ = new DBWORKER($dbu['host'], 'koksarea_hot' . $_POST['ProjectId'], 'utf8', $dbu['user'], $dbu['pass']);

	$Users = $DB -> prep('SELECT `users`.`id`, `users`.`data1`, ' . ($_POST['AuthCol'] == '2' ? '`users`.`data2`, ' : '') . ($_POST['AuthDef'] == 'protect' ? '`users`.`hash`, ' : '') . '`users`.`StateIndex`, `users`.`TimeStamp`, `users`.`UserAgent`, `users`.`Status`, `users`.`Difference` FROM `users` WHERE `users`.`prid` = :ProjectId', array('ProjectId' => $_POST['ProjectId'])) -> fetchAll(PDO :: FETCH_ASSOC);
	
	if ($_POST['Status'] > 1) {
		
		$Project = $DB -> prep('SELECT * FROM `projects` WHERE `projects`.`id` = :ProjectId', array('ProjectId' => $_POST['ProjectId'])) -> fetch(PDO :: FETCH_ASSOC);
	
		$QScheme = $DBQ -> prep('SELECT * FROM `qscheme`') -> fetchAll(PDO :: FETCH_ASSOC);
	
		$RScheme = $DBQ -> prep('SELECT * FROM `rscheme`') -> fetchAll(PDO :: FETCH_ASSOC);
	
		$fileName = '../../../ONLINE/FileData/data_' . $_POST['ProjectId'];
					
		$FileData = null;
		
		if ($_POST['AuthType'] == 'file' && file_exists($fileName)) {
						
			$lines = file($fileName, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

			$FileData = GetFileData($lines);
		
		}
	
		echo json_encode(array('Action' => 'Project data', 'ProjectId' => $_POST['ProjectId'], 'Project' => $Project, 'Users' => $Users, 'FileData' => $FileData, 'RScheme' => $RScheme, 'QScheme' => $QScheme));
	
	} else echo json_encode(array('Action' => 'Project data', 'Users' => $Users, 'ProjectId' => $_POST['ProjectId']));
		
}

?>
