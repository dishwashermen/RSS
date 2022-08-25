<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    require_once 'Functions.php';
    
    if (preg_array_key_exists('/ProjectId|UserId|StateIndex/', $_POST) && is_numeric($_POST['ProjectId']) && is_numeric($_POST['UserId'])) {

    	require_once 'DbSettings.php';
		
		require_once 'Worker40.php';
		
		$DB = new DBWORKER($dbu['host'], $dbu['db'], 'utf8', $dbu['user'], $dbu['pass']);
    	
    	$DBQ = new DBWORKER($dbu['host'], 'koksarea_hot' . $_POST['ProjectId'], 'utf8', $dbu['user'], $dbu['pass']);
		
		logger('Write');

		$QueryString = '';
		
		$PostValues = array();

		$HistoryData = array();
		
		$LimitedHIT = array();

		$ViewContent = isset($_POST['ViewContent']) ? (array) json_decode($_POST['ViewContent']) : false;																						   
		$Limited = false;
		
		$Aborted = false;
		
		foreach ($_POST as $key => $val) if (! Preg_match('/ProjectId|UserId|DirectRule|AuthType|Autofill|StateIndex|HistoryState|Total|JournalIndex|JournalState|ProjectLimiting|Limit|ViewContent/', $key)) {

			$QueryString .= '("' . $key . '",:' . $key . ',"' . $_POST['StateIndex'] . '"' . (isset($_POST['JournalIndex']) ? ',"' . $_POST['JournalIndex'] . '"' : '') . '),';
			
			$PostValues[$key] = $val;
			
			$KR = str_replace('_', '.', $key);

		}
		
		if ($QueryString != '') {
			
			if (isset($_POST['JournalIndex'])) $a = $DBQ -> prep('INSERT INTO `u' . $_POST['UserId'] . '` (`QName`, `QResponse`, `QSI`, `Journal`) VALUES ' . substr($QueryString, 0, -1) . ' ON DUPLICATE KEY UPDATE QResponse = VALUES(QResponse), QSI = VALUES(QSI), TimeStamp = CURRENT_TIMESTAMP', $PostValues);
			
			else $a = $DBQ -> prep('INSERT INTO `u' . $_POST['UserId'] . '` (`QName`, `QResponse`, `QSI`) VALUES ' . substr($QueryString, 0, -1) . ' ON DUPLICATE KEY UPDATE QResponse = VALUES(QResponse), QSI = VALUES(QSI), TimeStamp = CURRENT_TIMESTAMP', $PostValues);
			
		}
		
		if (! isset($_POST['JournalState']) || $_POST['JournalState'] == 'END') {
			
			// Проверка квот ----------------------------------------------------------------------------------------------------
			
			if (isset($_POST['Limit'])) require_once 'LimitCheck.php';
	
			// ------------------------------------------------------------------------------------------------------------------
			
			// Правила перехода -------------------------------------------------------------------------------------------------
			
			if (! $Limited) {

				if (isset($_POST['DirectRule'])) require_once 'RuleProcessing.php';
			
				else $nextIndex = $_POST['StateIndex'] + 1;
				
			}
			
			// ------------------------------------------------------------------------------------------------------------------
			
			// Статус (2 - продолжить, 3 - завершить, 4 - завершить досрочно, 5 - Завершить по квоте) ---------------------------
			
			$UserStatus = $Limited ? 5 : ($Aborted ? 4 : ($nextIndex != $_POST['Total'] ? 2 : 3));
			
			// ------------------------------------------------------------------------------------------------------------------
			
			// Постпроцессинг при завершении -----------------------------------------------------------------------------------
			
			if ($UserStatus > 2) require_once 'AfterEndProcessing.php';
			
			// ------------------------------------------------------------------------------------------------------------------
			
			// Запись данных для анализа ----------------------------------------------------------------------------------------
			
			require_once 'ViewProcessing.php';
			
			// ------------------------------------------------------------------------------------------------------------------

			if (isset($_POST['AutoFill'])) require_once 'AutoFill.php';

			if (isset($_POST['HistoryState'])) {
				
				$HistoryState = (array) json_decode($_POST['HistoryState']);
				 
				if ($nextIndex - $_POST['StateIndex'] > 1) { // Если между NextIndex и StateIndex интервал более 1, удалить всё в этом интервале и в выводе
				
					$CleanArray = array();
					
					for ($i = $_POST['StateIndex'] + 1; $i < $nextIndex; $i ++) {
						
						$CleanIndex = array_search($i, $HistoryState);
						
						if ($CleanIndex) {
							
							array_splice($HistoryState, $CleanIndex, 1);
							
							$CleanArray[] = $i;
							
						}
						
					}
					
					if (count($CleanArray)) $DeleteInterval = $DBQ -> prep('DELETE FROM `u' . $_POST['UserId'] . '` WHERE `QSI` = ' . implode(' OR `QSI` = ', $CleanArray));

				}

				if (! in_array($_POST['StateIndex'], $HistoryState)) { // Если StateIndex не содержится в $HistoryState
					
					if (count($HistoryState) > 0 && $HistoryState[count($HistoryState) - 1] > $_POST['StateIndex']) { // Если StateIndex не больше последнего в $HistoryState
						
						foreach($HistoryState as $HSK => $HSV) if ($HSV > $_POST['StateIndex']) break; // Индекс следующего за StateIndex в $HistoryState
						
						array_splice($HistoryState, $HSK, 0, $_POST['StateIndex']); // Вставить StateIndex на это место. Остальные сдвигаются
						
					} 
					
				}

				if (! in_array($_POST['StateIndex'], $HistoryState)) $HistoryState[] = $_POST['StateIndex']; // Если StateIndex не содержится в HistoryState и он более всех остальных, добавить его (для новых StateIndex)
				
				if (isset($_POST['JournalIndex'])) $HD = $DBQ -> prep('SELECT `QName`, `QResponse` FROM `u' . $_POST['UserId'] . '` WHERE `QSI` = :qsi AND `Journal` = :Journal', array('qsi' => $nextIndex, 'Journal' => $_POST['JournalIndex'])) -> fetchAll(PDO :: FETCH_ASSOC);
				
				else $HD = $DBQ -> prep('SELECT `QName`, `QResponse` FROM `u' . $_POST['UserId'] . '` WHERE `QSI` = :qsi', array('qsi' => $nextIndex)) -> fetchAll(PDO :: FETCH_ASSOC);
					
				if ($HD) foreach ($HD as $data) $HistoryData[$data['QName']] = $data['QResponse'];
				
				$UserUpdate = $DB -> prep('UPDATE `users` SET `StateIndex` = ' . $nextIndex . ', `HistoryState` = :HistoryState' . ($UserStatus ? ', `Status` = ' . $UserStatus : '') . ' WHERE `id` = :id', array('id' => $_POST['UserId'], 'HistoryState' => json_encode($HistoryState)));	

			} else $UserUpdate = $DB -> prep('UPDATE `users` SET `StateIndex` = ' . $nextIndex . ($UserStatus ? ', `Status` = ' . $UserStatus : '') . ' WHERE `id` = :id', array('id' => $_POST['UserId']));
			
			if (isset($_POST['JournalState']) && $_POST['JournalState'] == 'END') $a = $DBQ -> prep('UPDATE `u' . $_POST['UserId'] . '` SET `QName` = CONCAT("' . $_POST['JournalIndex'] . '-", `QName`) WHERE `Journal` = "' . $_POST['JournalIndex'] . '"');

			echo json_encode(array('Q' => GetQ($nextIndex), 'Action' => 'Resume', 'RuleData' => RuleData($nextIndex, $_POST['UserId']), 'StateIndex' => (string) $nextIndex, 'HistoryState' => json_encode($HistoryState), 'HistoryData' => $HistoryData, 'Uid' => $_POST['UserId'], 'Reload' => true, 'LimitText' => ($Limited ? $LimitText : '')));
	
		} else {
			
			$a = $DBQ -> prep('UPDATE `u' . $_POST['UserId'] . '` SET `QName` = CONCAT("' . $_POST['JournalIndex'] . '-", `QName`) WHERE `Journal` = "' . $_POST['JournalIndex'] . '"');
			
			$NewJournalIndex = + $_POST['JournalIndex'] + 1;

			if (isset($_POST['HistoryState'])) {
			
				$HistoryState = (array) json_decode($_POST['HistoryState']);
				
				$NewHistoryState = array();
				
				foreach ($HistoryState as $HSV) {
					
					if ($HSV == $_POST['JournalState']) break;
					
					array_push($NewHistoryState, $HSV);
	
				}
				
				$UserUpdate = $DB -> prep('UPDATE `users` SET `StateIndex` = :StateIndex, `JournalIndex` = :JournalIndex, `HistoryState` = :HistoryState WHERE `id` = :id', array('id' => $_POST['UserId'], 'HistoryState' => json_encode($NewHistoryState), 'StateIndex' => $_POST['JournalState'], 'JournalIndex' => $NewJournalIndex));
				
			} else $UserUpdate = $DB -> prep('UPDATE `users` SET `StateIndex` = :StateIndex, `JournalIndex` = :JournalIndex WHERE `id` = :id', array('id' => $_POST['UserId'], 'StateIndex' => $_POST['JournalState'], 'JournalIndex' => $NewJournalIndex));
			
			echo json_encode(array('Q' => GetQ($_POST['JournalState']), 'Action' => 'Resume', 'RuleData' => RuleData($_POST['JournalState'], $_POST['UserId']), 'StateIndex' => $_POST['JournalState'], 'HistoryState' => json_encode($NewHistoryState), 'HistoryData' => $HistoryData, 'Uid' => $_POST['UserId'], 'Reload' => true, 'JournalIndex' => $NewJournalIndex));
			
		}
	
    }
	
}

?>