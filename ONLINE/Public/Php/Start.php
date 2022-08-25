<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    require_once 'Functions.php';
    
    if (count($_POST) == 6 && preg_array_key_exists('/AuthType|AuthCol|AuthDef|AuthData|ProjectId|Provisional/', $_POST)) {
		
		require_once 'DbSettings.php';
		
		require_once 'Worker.php';

		$DB = new DBWORKER($dbu['host'], $dbu['db'], 'utf8', $dbu['user'], $dbu['pass']);
		
		$DBQ = new DBWORKER($dbu['host'], 'koksarea_hot' . $_POST['ProjectId'], 'utf8', $dbu['user'], $dbu['pass']);

		$AuthData = (array) json_decode($_POST['AuthData']);
		
		if ($_POST['AuthCol'] == 2) $ud = $DB -> prep('SELECT `users`.`id`, `users`.`prid`, `users`.`data1`, `users`.`data2`, `users`.`hash`, `users`.`StateIndex`, `users`.`HistoryState`, `users`.`Status`, `users`.`JournalIndex` FROM `users` WHERE `users`.`prid` = :ProjectId AND `users`.`data1` = :data1 AND `users`.`data2` = :data2', array('ProjectId' => $_POST['ProjectId'], 'data1' => $AuthData['data'][0], 'data2' => $AuthData['data'][1])) -> fetch(PDO :: FETCH_ASSOC);
			
		else $ud = $DB -> prep('SELECT `users`.`id`, `users`.`prid`, `users`.`data1`, `users`.`data2`, `users`.`hash`, `users`.`StateIndex`, `users`.`HistoryState`, `users`.`Status`, `users`.`JournalIndex` FROM `users` WHERE `users`.`prid` = :ProjectId AND `users`.`data1` = :data1', array('ProjectId' => $_POST['ProjectId'], 'data1' => $AuthData['data'][0])) -> fetch(PDO :: FETCH_ASSOC);
		
		$hash = hash('sha256', $AuthData['hash'] . 'mrs.Maysel');
		
		if ($ud) { // --------------------------------------------------------- current user -----------------------------------------------------------------------------------------------------
		
			if ($_POST['AuthDef'] == 'protect') {
				
				if ($ud['hash'] == $hash) { // -------------------------------- protect success --------------------------------------------------------------------------------------------------
					
					logger('Login', $ud['id'], false);
					
					echo json_encode(array('Q' => GetQ($ud['StateIndex']), 'Action' => 'Resume', 'RuleData' => RuleData($ud['StateIndex'], $ud['id']), 'Uid' => $ud['id'], 'StateIndex' => $ud['StateIndex'], 'HistoryState' => $ud['HistoryState'], 'Reload' => false, 'UserStatus' => $ud['Status'], 'JournalIndex' => $ud['JournalIndex']));
					
				} else {
					
					logger('Denied', $ud['id']);
					
					echo json_encode(array('Action' => 'Denied'));

				}					// ------ denied -----------------------------------------------------------------------------------------------------------
				
			} else {
				
				logger('Login', $ud['id'], false);
				
				echo json_encode(array('Q' => GetQ($ud['StateIndex']), 'Action' => 'Resume', 'RuleData' => RuleData($ud['StateIndex'], $ud['id']), 'Uid' => $ud['id'], 'StateIndex' => $ud['StateIndex'], 'HistoryState' => $ud['HistoryState'], 'Reload' => false, 'UserStatus' => $ud['Status'], 'JournalIndex' => $ud['JournalIndex']));

			}				// -------- unprotect success ------------------------------------------------------------------------------------------------
		
		} else { // ----------------------------------------------------------- new user ---------------------------------------------------------------------------------------------------------
			
			if (count($AuthData['data'])) {
			
				if ($_POST['AuthDef'] =='protect') $uid = NewUser($_POST['ProjectId'], $AuthData['data'], $_POST['Provisional'], $hash); // ------- new protect
					
				else $uid = NewUser($_POST['ProjectId'], $AuthData['data'], $_POST['Provisional']); // -------------------------------------------- new unprotect
				
				logger('Create user', $uid);
				
				echo json_encode(array('Q' => GetQ(1), 'Action' => 'Resume', 'Uid' => $uid, 'StateIndex' => 1, 'JournalIndex' => 1, 'Reload' => false));
			
			} else echo json_encode(array('Action' => 'Denied'));
			
		}

	}
	
}

?>