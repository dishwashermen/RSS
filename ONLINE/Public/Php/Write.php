<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    require_once 'Functions.php';
    
    if (preg_array_key_exists('/ProjectId|UserId|StateIndex/', $_POST) && is_numeric($_POST['ProjectId']) && is_numeric($_POST['UserId'])) {

    	require_once 'DbSettings.php';
		
		require_once 'Worker.php';
		
		$DB = new DBWORKER($dbu['host'], $dbu['db'], 'utf8', $dbu['user'], $dbu['pass']);
    	
    	$DBQ = new DBWORKER($dbu['host'], 'koksarea_hot' . $_POST['ProjectId'], 'utf8', $dbu['user'], $dbu['pass']);
		
		logger('Write');

		$QueryString = '';
		
		$PostValues = array();
		
		$Difference = array();
		
		$HistoryData = array();
		
		$PostDifference = isset($_POST['Difference']) ? (array) json_decode($_POST['Difference']) : false;
		
		$DiffContent = isset($_POST['DiffContent']) ? (array) json_decode($_POST['DiffContent']) : false;
		
		$Limited = false;
		
		$Aborted = false;
		
		foreach ($_POST as $key => $val) if (! Preg_match('/ProjectId|UserId|DirectRule|Autofill|StateIndex|HistoryState|Total|Difference|DiffContent|JournalIndex|JournalState|Limit/', $key, $matches)) {

			$QueryString .= '("' . $key . '",:' . $key . ',"' . $_POST['StateIndex'] . '"' . (isset($_POST['JournalIndex']) ? ',"' . $_POST['JournalIndex'] . '"' : '') . '),';
			
			$PostValues[$key] = $val;
			
			$KR = str_replace('_', '.', $key);
			
			if ($PostDifference && in_array($KR, $PostDifference)) array_push($Difference, ($DiffContent && isset($DiffContent[$KR]) ? $DiffContent[$KR] : $val));

		}
		
		if ($QueryString != '') {
			
			if (isset($_POST['JournalIndex'])) $a = $DBQ -> prep('INSERT INTO `u' . $_POST['UserId'] . '` (`QName`, `QResponse`, `QSI`, `Journal`) VALUES ' . substr($QueryString, 0, -1) . ' ON DUPLICATE KEY UPDATE QResponse = VALUES(QResponse), QSI = VALUES(QSI), TimeStamp = CURRENT_TIMESTAMP', $PostValues);
			
			else $a = $DBQ -> prep('INSERT INTO `u' . $_POST['UserId'] . '` (`QName`, `QResponse`, `QSI`) VALUES ' . substr($QueryString, 0, -1) . ' ON DUPLICATE KEY UPDATE QResponse = VALUES(QResponse), QSI = VALUES(QSI), TimeStamp = CURRENT_TIMESTAMP', $PostValues);
			
		} //else if (! isset($_POST['JournalState'])) customErrorHandler("Empty query\n\nPOST\t" . json_encode($_POST));
		
		if (! isset($_POST['JournalState']) || $_POST['JournalState'] == 'END') {
			
			// ???????????????? ???????? ----------------------------------------------------------------------------------------------------
			
			if (isset($_POST['Limit'])) require_once 'LimitCheck.php';
	
			// ------------------------------------------------------------------------------------------------------------------
			
			// ?????????????? ???????????????? -------------------------------------------------------------------------------------------------
			
			if (! $Limited) {

				if (isset($_POST['DirectRule'])) require_once 'RuleProcessing.php';
			
				else $nextIndex = $_POST['StateIndex'] + 1;
				
			}
			
			// ------------------------------------------------------------------------------------------------------------------
			
			// ???????????? (2 - ????????????????????, 3 - ??????????????????, 4 - ?????????????????? ????????????????, 5 - ?????????????????? ???? ??????????) ---------------------------
			
			$UserStatus = $Limited ? 5 : ($Aborted ? 4 : ($nextIndex != $_POST['Total'] ? 2 : 3));
			
			// ------------------------------------------------------------------------------------------------------------------
			
			// ???????????????????? ???????? ?????? ???????????????????? -----------------------------------------------------------------------------------
			
			if ($UserStatus == 3 && $_POST['ProjectLimiting'] == 'ON') require_once 'LimitUpdate.php';
			
			// ------------------------------------------------------------------------------------------------------------------
			
			if (isset($_POST['AutoFill'])) require_once 'AutoFill.php';

			if (isset($_POST['HistoryState'])) {
				
				$HistoryState = (array) json_decode($_POST['HistoryState']);
				 
				if ($nextIndex - $_POST['StateIndex'] > 1) { // ???????? ?????????? NextIndex ?? StateIndex ???????????????? ?????????? 1, ?????????????? ?????? ?? ???????? ?????????????????? ?? ?? ????????????
				
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

				if (! in_array($_POST['StateIndex'], $HistoryState)) { // ???????? StateIndex ???? ???????????????????? ?? $HistoryState
					
					if (count($HistoryState) > 0 && $HistoryState[count($HistoryState) - 1] > $_POST['StateIndex']) { // ???????? StateIndex ???? ???????????? ???????????????????? ?? $HistoryState
						
						foreach($HistoryState as $HSK => $HSV) if ($HSV > $_POST['StateIndex']) break; // ???????????? ???????????????????? ???? StateIndex ?? $HistoryState
						
						array_splice($HistoryState, $HSK, 0, $_POST['StateIndex']); // ???????????????? StateIndex ???? ?????? ??????????. ?????????????????? ????????????????????
						
					} 
					
				}

				if (! in_array($_POST['StateIndex'], $HistoryState)) $HistoryState[] = $_POST['StateIndex']; // ???????? StateIndex ???? ???????????????????? ?? HistoryState ?? ???? ?????????? ???????? ??????????????????, ???????????????? ?????? (?????? ?????????? StateIndex)
				
				if (isset($_POST['JournalIndex'])) $HD = $DBQ -> prep('SELECT `QName`, `QResponse` FROM `u' . $_POST['UserId'] . '` WHERE `QSI` = :qsi AND `Journal` = :Journal', array('qsi' => $nextIndex, 'Journal' => $_POST['JournalIndex'])) -> fetchAll(PDO :: FETCH_ASSOC);
				
				else $HD = $DBQ -> prep('SELECT `QName`, `QResponse` FROM `u' . $_POST['UserId'] . '` WHERE `QSI` = :qsi', array('qsi' => $nextIndex)) -> fetchAll(PDO :: FETCH_ASSOC);
					
				if ($HD) foreach ($HD as $data) $HistoryData[$data['QName']] = $data['QResponse'];
				
				$UserUpdate = $DB -> prep('UPDATE `users` SET `StateIndex` = ' . $nextIndex . ', `HistoryState` = :HistoryState' . ($UserStatus ? ', `Status` = ' . $UserStatus : '') . (count($Difference) ? ', `Difference` = CONCAT(IFNULL(CONCAT(`Difference`, ","), ""), "' . implode(',', $Difference) . '")' : '') . ' WHERE `id` = :id', array('id' => $_POST['UserId'], 'HistoryState' => json_encode($HistoryState)));	

			} else $UserUpdate = $DB -> prep('UPDATE `users` SET `StateIndex` = ' . $nextIndex . ($UserStatus ? ', `Status` = ' . $UserStatus : '') . (count($Difference) ? ', `Difference` = CONCAT(IFNULL(CONCAT(`Difference`, ", "), ""), "' . implode(', ', $Difference) . '")' : '') . ' WHERE `id` = :id', array('id' => $_POST['UserId']));
			
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
				
				$UserUpdate = $DB -> prep('UPDATE `users` SET `StateIndex` = :StateIndex, `JournalIndex` = :JournalIndex, `HistoryState` = :HistoryState' . (count($Difference) ? ', `Difference` = CONCAT(IFNULL(CONCAT(`Difference`, ", "), ""), "' . implode(', ', $Difference) . '")' : '') . ' WHERE `id` = :id', array('id' => $_POST['UserId'], 'HistoryState' => json_encode($NewHistoryState), 'StateIndex' => $_POST['JournalState'], 'JournalIndex' => $NewJournalIndex));
				
			} else $UserUpdate = $DB -> prep('UPDATE `users` SET `StateIndex` = :StateIndex, `JournalIndex` = :JournalIndex' . (count($Difference) ? ', `Difference` = CONCAT(IFNULL(CONCAT(`Difference`, ", "), ""), "' . implode(', ', $Difference) . '")' : '') . ' WHERE `id` = :id', array('id' => $_POST['UserId'], 'StateIndex' => $_POST['JournalState'], 'JournalIndex' => $NewJournalIndex));
			
			echo json_encode(array('Q' => GetQ($_POST['JournalState']), 'Action' => 'Resume', 'RuleData' => RuleData($_POST['JournalState'], $_POST['UserId']), 'StateIndex' => $_POST['JournalState'], 'HistoryState' => json_encode($NewHistoryState), 'HistoryData' => $HistoryData, 'Uid' => $_POST['UserId'], 'Reload' => true, 'JournalIndex' => $NewJournalIndex));
			
		}
	
    }
	
}

?>