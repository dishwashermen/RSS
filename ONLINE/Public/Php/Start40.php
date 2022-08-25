<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    require_once 'Functions.php';
    
    if (count($_POST) == 2 && preg_array_key_exists('/AuthData|ProjectId/', $_POST)) {
		
		require_once 'DbSettings.php';
		
		require_once 'Worker40.php';

		$DB = new DBWORKER($dbu['host'], $dbu['db'], 'utf8', $dbu['user'], $dbu['pass']);
		
		$DBQ = new DBWORKER($dbu['host'], 'koksarea_hot' . $_POST['ProjectId'], 'utf8', $dbu['user'], $dbu['pass']);

		$Ud = $DB -> prep('SELECT * FROM `users` WHERE `users`.`prid` = :ProjectId AND `users`.`data1` = :data1', array('ProjectId' => $_POST['ProjectId'], 'data1' => $_POST['AuthData'])) -> fetch(PDO :: FETCH_ASSOC);
		
		$Hash = isset($_POST['AuthHash']) ? hash('sha256', $AuthData['Hash'] . 'mrs.Maysel') : false;

		if ($Ud) { 
		
		// есть текущий пользователь --------------------------------------------------------------------------------------------
		
			if ($Hash) {
				
				if ($Ud['hash'] == $Hash) {
					
					logger('Login', $Ud['id'], false);
					
					echo json_encode(array('Q' => GetQ($Ud['StateIndex']), 'Action' => 'Resume', 'RuleData' => RuleData($Ud['StateIndex'], $Ud['id']), 'Uid' => $Ud['id'], 'StateIndex' => $Ud['StateIndex'], 'HistoryState' => $Ud['HistoryState'], 'Reload' => false, 'UserStatus' => $Ud['Status'], 'JournalIndex' => $Ud['JournalIndex']));
					
				} else {
					
					logger('Denied', $Ud['id']);
					
					echo json_encode(array('Action' => 'Denied'));

				}
				
			} else {
				
				logger('Login', $Ud['id'], false);
				
				echo json_encode(array('Q' => GetQ($Ud['StateIndex']), 'Action' => 'Resume', 'RuleData' => RuleData($Ud['StateIndex'], $Ud['id']), 'Uid' => $Ud['id'], 'StateIndex' => $Ud['StateIndex'], 'HistoryState' => $Ud['HistoryState'], 'Reload' => false, 'UserStatus' => $Ud['Status'], 'JournalIndex' => $Ud['JournalIndex']));

			}		
		
		} else {
			
		// нет текущего пользователя, создание нового ---------------------------------------------------------------------------

			$Uid = NewUser($_POST['ProjectId'], $_POST['AuthData'], isset($_POST['Provisional']) ? $_POST['Provisional'] : false, $Hash);

			logger('Create user', $Uid);
			
			echo json_encode(array('Q' => GetQ(1), 'Action' => 'Resume', 'Uid' => $Uid, 'StateIndex' => 1, 'JournalIndex' => 1, 'Reload' => false));
	
		}

	}
	
}

?>