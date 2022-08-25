<?php 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	
	require_once 'Functions.php';
	
	if (count($_POST) == 1 && ((isset($_POST['Hash']) && (strlen($_POST['Hash']) == 32 || strlen($_POST['Hash']) == 64)) || (isset($_POST['A']) && strlen($_POST['A']) <= 10))) {
		
		require_once 'DbSettings.php';

		$DB = new DBWORKER($dbu['host'], $dbu['db'], 'utf8', $dbu['user'], $dbu['pass']);
		
		if (isset($_POST['Hash'])) $Project = $DB -> prep('SELECT `projects`.* FROM `projects` WHERE `projects`.`Hash` = :Hash AND `projects`.`mode` = "ON"', array('Hash' => $_POST['Hash'])) -> fetch(PDO :: FETCH_ASSOC);
		
		else if (isset($_POST['A'])) $Project = $DB -> prep('SELECT `projects`.* FROM `projects` WHERE `projects`.`Alias` = :Alias AND `projects`.`mode` = "ON"', array('Alias' => $_POST['A'])) -> fetch(PDO :: FETCH_ASSOC);
		
		if ($Project) {
			
			$DBQ = new DBWORKER($dbu['host'], 'koksarea_hot' . $Project['id'], 'utf8', $dbu['user'], $dbu['pass']);
			
			require_once 'Worker40.php';
			
			$RulesData = $DBQ -> prep('SELECT `rscheme`.`Id`, `rscheme`.`StateIndex`, `rscheme`.`Event` FROM `rscheme` WHERE `Disabled` IS NULL AND `rscheme`.`Event` != ""') -> fetchAll(PDO :: FETCH_ASSOC);
			
			$Rules = [];
			
			if ($RulesData) foreach ($RulesData as $RV) if (! isset($Rules[$RV['Event']]) || ! in_array($RV['StateIndex'], $Rules[$RV['Event']])) $Rules[$RV['Event']][] = $RV['StateIndex'];
			
			$BaseData = $Project['AuthType'] == 'base' ? GetbaseData($Project['AuthData']) : null;

			if ($Project['Limiting'] == 'ON') {
				
				$Limit = [];
				
				$LimitData = $DBQ -> prep('SELECT `lscheme`.`StateIndex` FROM `lscheme` GROUP BY `lscheme`.`StateIndex`') -> fetchAll(PDO :: FETCH_NUM);
				
				$LD = new RecursiveIteratorIterator(new RecursiveArrayIterator($LimitData));
				
				foreach($LD as $LDV) array_push($Limit, $LDV);
				
			} else $Limit = null;
			
			echo json_encode(array('Action' => 'Welcome', 'BaseData' => $BaseData, 'Project' => $Project, 'Rules' => (count($Rules) ? $Rules : false), 'Limit' => $Limit));
			
		} else echo json_encode(array('Action' => 'Reject')); 
		
	}
	
}

?>