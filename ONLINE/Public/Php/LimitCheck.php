<?php

$LimitData = $DBQ -> prep('SELECT * FROM `lscheme` WHERE `lscheme`.`StateIndex` = :StateIndex AND `lscheme`.`Disabled` IS NULL', array('StateIndex' => $_POST['StateIndex'])) -> fetchAll(PDO :: FETCH_ASSOC);

foreach ($LimitData as $LD) {
	
	$QNData = explode(',', $LD['QN']);
	
	$TargetData = explode(',', $LD['Target']);
	
	$HIT = true;
	
	for ($i = 0; $i < count($QNData); $i ++) {
		
		if ($_POST[str_replace('.', '_', $QNData[$i])] != $TargetData[$i]) {
			
			$HIT = false;
			
			break;
			
		}
		
	}
		
	if ($HIT && $LD['Limiting'] == $LD['Contains']) {
		
		$QData = $DBQ -> prep('SELECT * FROM `qscheme` WHERE `qscheme`.`id` = :StateIndex', array('StateIndex' => $_POST['StateIndex'])) -> fetch(PDO :: FETCH_ASSOC);
		
		$QInputTypeData = explode('|', $QData['InputType']);
		
		$VarData = [];
		
		for ($i = 0; $i < count($QNData); $i ++) {
			
			$QNIndexData = explode('.', $QNData[$i]);
			
			$InpitType = count($QInputTypeData) > 1 ? $QInputTypeData[$QNIndexData[1] - 1] : $QInputTypeData[0];
			
			switch ($InpitType) {
				
				case 'select':
				
					$InputData = explode('|', $QData['InputData']);
					
					$NameData = count($InputData) > 1 ? explode('*', $InputData[$QNIndexData[1] - 1]) : explode('*', $InputData[0]);
				
				break;
				
				default:
				
					if ($QData['InputData'] != null) {
						
						$InputData = explode('|', $QData['InputData']);
						
						$NameData = count($InputData) > 1 ? explode('*', $InputData[$QNIndexData[1] - 1]) : explode('*', $InputData[0]);
						
					} else $NameData = explode('|', $QData['RowsContent']);

				break;
				
			}
			
			array_push($VarData, $NameData[$TargetData[$i] - 1]);
			
		}
		
		if (isset($LD['Note'])) {
			
			foreach($VarData as $VDV) $LD['Note'] = str_replace_once('$', $VDV, $LD['Note']);
			
			$LimitText = $LD['Note'];
			
		} else $LimitText = 'Квота ' . implode('/', $VarData) . ' выполнена';
		
		$Limited = true;
		
		$nextIndex = $_POST['Total'];
		
		break;
		
	}
	
}

?>