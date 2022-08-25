<?php

$Rules = $DBQ -> prep('SELECT * FROM `rscheme` WHERE `rscheme`.`Event` = "DIRECT" AND `rscheme`.`StateIndex` = :StateIndex', array('StateIndex' => $_POST['StateIndex'])) -> fetchAll(PDO :: FETCH_ASSOC);
	
//$RuleStructure = $DBQ -> prep('SELECT * FROM `[r]structure` WHERE `[r]structure`.`RId` = ' . $rule['Id']) -> fetchAll(PDO :: FETCH_ASSOC);

$COMMON_RESULT = false;

foreach ($Rules as $RSK => $RSV) {
	
	$OVERALL_RESULT = false;

	$INTERIM_RESULT = false;
	
	$TRIGGER_VALUE = false;
	
	$TriggerVar = explode(',', str_replace('.', '_', $RSV['TriggerVar']));
	
	$Target = explode(',', $RSV['Target']);
	
	$Operation = explode(',', $RSV['Operation']);
	
	foreach ($TriggerVar as $TVK => $TVV) {
		
		if (Preg_match('/\$([^&]+)&([^,]+)/', $Target[$TVK], $TargetMatch)) {
			
			$TargetPool = explode(';', str_replace('.', '_', $TargetMatch[1]));
			
			$rq = '`QName` LIKE "';
			
			$PostData = array();
			
			foreach ($TargetPool as $TPK => $TPV) {
				
				if (isset($_POST[$TPV])) $PostData[] = array('QName' => $TPV, 'QResponse' => $_POST[$TPV], 'QSI' => $_POST['StateIndex']);
					
				else $rq .= $TPV . ($TPK < count($TargetPool) - 1 ? '" OR `QName` LIKE "' : '"');
				
			} 
			
			if (count($PostData) == 0) $UQData = $DBQ -> prep('SELECT `QName`, `QResponse`, `QSI` FROM `u' . $_POST['UserId'] . '` WHERE ' . $rq) -> fetchAll(PDO :: FETCH_ASSOC);
				
			else $UQData = $PostData;
			
			$SingleData = array();
			
			foreach ($UQData as $UQK => $UQV) {
						
				if ($UQK > 0) {
				
					$SingleData['QName'] = $SingleData['QName'] . ',' . $UQData[$UQK]['QName'];
					
					$SingleData['QResponse'] = $SingleData['QResponse'] . ',' . $UQData[$UQK]['QResponse'];
				
				} else $SingleData = $UQData[$UQK];
				
			}

			switch ($TargetMatch[2]) {
				
				case 'VOL': $Target[$TVK] = count(explode(',', $SingleData['QResponse']));
				
				break;
				
			}
			
		}
		
		if (! Preg_match('/(\w+)\$?(\$\w+)/', $TVV, $matches)) {
		
			if (! isset($_POST[$TVV])) {
			
				$TRIGGER_request = $DBQ -> prep('SELECT `QResponse` FROM `u' . $_POST['UserId'] . '` WHERE `QName` = :qname', array('qname' => $TVV)) -> fetch(PDO :: FETCH_ASSOC);
				
				$TRIGGER_VALUE = $TRIGGER_request ? $TRIGGER_request['QResponse'] : null;

			} else $TRIGGER_VALUE = $_POST[$TVV];
			
			$TRIGGER_VALUE = preg_replace('/-.*/', '', $TRIGGER_VALUE);
		
		} else {
			
			$trgv = $matches[1];
			
			switch ($matches[2]) {
				
				case '$E':
				
					foreach ($_POST as $pkey => $pval) if (! Preg_match('/ProjectId|UserId|ShiftRules|FillRules|StateIndex|HistoryState|Total|Difference/', $pkey, $matches)) if (strpos($pkey, $trgv) !== false && strpos($pkey, $trgv) == 0 && $pval != '') $TRIGGER_VALUE = $pval;
						
					if (! $TRIGGER_VALUE) $TRIGGER_VALUE = $DBQ -> prep('SELECT `QResponse` FROM `u' . $_POST['UserId'] . '` WHERE `QName` LIKE "' . $trgv . '%"') -> fetch(PDO :: FETCH_ASSOC)['QResponse'];
					
				break;
				
			}
			
		}

		$OperationName = isset($Operation[$TVK]) ? $Operation[$TVK] : $Operation[0];
		
		if ($OperationName[0] != '!') {
			
			if ($TRIGGER_VALUE == '') $TRIGGER_VALUE = 0;
			
		} else $OperationName = substr($OperationName, 1);
		
		switch ($OperationName) {
			
			case 'EQUAL': $INTERIM_RESULT = $TRIGGER_VALUE != '' && $TRIGGER_VALUE == $Target[$TVK] ? true : false;
			
			break;
			
			case 'NOT EQUAL': $INTERIM_RESULT = $TRIGGER_VALUE != '' && $TRIGGER_VALUE != $Target[$TVK] ? true : false;
			
			break;
			
			case 'CONTAINS': $INTERIM_RESULT = $TRIGGER_VALUE != '' && in_array($Target[$TVK], explode(',', $TRIGGER_VALUE)) ? true: false;	

			break;
			
			case 'NOT CONTAINS': $INTERIM_RESULT = $TRIGGER_VALUE != '' && ! in_array($Target[$TVK], explode(',', $TRIGGER_VALUE)) ? true: false;	

			break;
			
			case 'LESS': $INTERIM_RESULT = $TRIGGER_VALUE != '' && $TRIGGER_VALUE < $Target[$TVK] ? true : false;
			
			break;
			
			case 'NOT LESS': $INTERIM_RESULT = $TRIGGER_VALUE != '' && $TRIGGER_VALUE >= $Target[$TVK] ? true : false;
			
			break;
			
			case 'MORE': $INTERIM_RESULT = $TRIGGER_VALUE != '' && $TRIGGER_VALUE > $Target[$TVK] ? true : false;
			
			break;
			
			case 'NOT MORE': $INTERIM_RESULT = $TRIGGER_VALUE != '' && $TRIGGER_VALUE <= $Target[$TVK] ? true : false;
			
			break;
			
			case 'VOL': $INTERIM_RESULT = $TRIGGER_VALUE != '' && $Target[$TVK] == count(explode(',', $TRIGGER_VALUE)) ? true: false;
			
			break;
			
			case 'EXIST': $INTERIM_RESULT = $TRIGGER_VALUE != '' ? true: false;
			
			break;
			
			case 'NOT EXIST': $INTERIM_RESULT = $TRIGGER_VALUE == '' ? true: false;
			
			break;
			
			case 'BETWEEN':
			
				$Parts = explode('-', $Target[$TVK]);
				
				$INTERIM_RESULT = $TRIGGER_VALUE != '' && $TRIGGER_VALUE >= $Parts[0] && $TRIGGER_VALUE <= $Parts[1] ? true : false;
			
			break;
			
		}
		
		$OVERALL_RESULT = $INTERIM_RESULT ? true : false;
		
		$INTERIM_RESULT = false;
		
		if ($RSV['Equation'] == 'AND') {
			
			if (! $OVERALL_RESULT) break;
			
		} else if ($OVERALL_RESULT) break;
		
	}
	
	$COMMON_RESULT = $OVERALL_RESULT ? true : false;
	
	$OVERALL_RESULT = false;
	
	if ($Rules[0]['Criterion'] == 'AND') {
			
		if (! $COMMON_RESULT) break;
		
	} else if ($COMMON_RESULT) break;
	
}

$nextIndex = $COMMON_RESULT ? ($Rules[0]['Criterion'] == 'FORK' ? (explode(',', $Rules[0]['Transit'])[$RSK] == 'FINISH' ? $_POST['Total'] : explode(',', $Rules[0]['Transit'])[$RSK]) : ($Rules[0]['Transit'] == 'FINISH' ? $_POST['Total'] : $Rules[0]['Transit'])) : ($Rules[0]['AltTransit'] != '' ? ($Rules[0]['AltTransit'] == 'FINISH' ? $_POST['Total'] : $Rules[0]['AltTransit']) : $_POST['StateIndex'] + 1);

$Aborted = ($COMMON_RESULT && (($Rules[0]['Criterion'] == 'FORK' && explode(',', $Rules[0]['Transit'])[$RSK] == 'FINISH') || $Rules[0]['Transit'] == 'FINISH')) || (! $COMMON_RESULT && $Rules[0]['AltTransit'] == 'FINISH') ? true : false;

?>