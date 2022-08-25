<?php 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	
	require_once 'Functions.php';
	
	if (count($_POST) == 1 && ((isset($_POST['Hash']) && (strlen($_POST['Hash']) == 32 || strlen($_POST['Hash']) == 64)) || (isset($_POST['A']) && strlen($_POST['A']) <= 10))) {
		
		require_once 'DbSettings.php';
		
		require_once 'Worker.php';
		
		$DB = new DBWORKER($dbu['host'], $dbu['db'], 'utf8', $dbu['user'], $dbu['pass']);
		
		if (isset($_POST['Hash'])) $Project = $DB -> prep('SELECT `projects`.* FROM `projects` WHERE `projects`.`Hash` = :Hash AND `projects`.`mode` = "ON"', array('Hash' => $_POST['Hash'])) -> fetch(PDO :: FETCH_ASSOC);
		
		else if (isset($_POST['A'])) $Project = $DB -> prep('SELECT `projects`.* FROM `projects` WHERE `projects`.`Alias` = :Alias AND `projects`.`mode` = "ON"', array('Alias' => $_POST['A'])) -> fetch(PDO :: FETCH_ASSOC);
		
		if ($Project) {
			
			$DBP = new DBWORKER($dbu['host'], 'koksarea_hot' . $Project['id'], 'utf8', $dbu['user'], $dbu['pass']);
			
			$RulesData = $DBP -> prep('SELECT `rscheme`.`Id`, `rscheme`.`StateIndex`, `rscheme`.`Event` FROM `rscheme` WHERE `Disabled` IS NULL AND `rscheme`.`Event` != ""') -> fetchAll(PDO :: FETCH_ASSOC);
			
			$Rules = [];
			
			if ($RulesData) foreach ($RulesData as $RV) if (! isset($Rules[$RV['Event']]) || ! in_array($RV['StateIndex'], $Rules[$RV['Event']])) $Rules[$RV['Event']][] = $RV['StateIndex'];
			
			$FileName = '../../FileData/data_' . $Project['id'];
				
			$FileData = null;
			
			if (preg_match('/file/', $Project['AuthType']) && file_exists($FileName)) {
				
				$Lines = file($FileName, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

				$FileData = GetFileData($Lines);
			
			}
			
			$Limit = [];
			
			if ($Project['Limiting'] == 'ON') {
				
				$LimitData = $DBP -> prep('SELECT `lscheme`.`StateIndex` FROM `lscheme` GROUP BY `lscheme`.`StateIndex`') -> fetchAll(PDO :: FETCH_NUM);
				
				$LD = new RecursiveIteratorIterator(new RecursiveArrayIterator($LimitData));
				
				foreach($LD as $LDV) array_push($Limit, $LDV);
				
			} else $Limit = null;
			
			echo json_encode(array('Action' => 'Welcome', 'FileData' => $FileData, 'Project' => $Project, 'Rules' => (count($Rules) ? $Rules : false), 'Limit' => $Limit));
			
		} else echo json_encode(array('Action' => 'Reject')); 
		
	}
	
}

?>