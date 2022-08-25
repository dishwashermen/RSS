<?php

if ($_SERVER['REQUEST_METHOD'] === 'GET' && count($_GET) >= 3 && isset($_GET['p']) && is_numeric($_GET['p'])) {

	require_once 'Functions.php';

	require_once 'DBSettings.php';
	
	$DB = new DBWORKER($dbu['host'], $dbu['db'], 'utf8', $dbu['user'], $dbu['pass']);
	
	$DBQ = new DBWORKER($dbu['host'], 'koksarea_hot' . $_GET['p'], 'utf8', $dbu['user'], $dbu['pass']);

	if (! isset($_GET['jf'])) $maxindex = $DBQ -> prep('SELECT max(`id`) from `qscheme` WHERE `Journal` IS NULL') -> fetch(PDO :: FETCH_NUM)[0];
	
	else $maxindex = + $_GET['jf'] - 1;

	$R = GetR(1, $maxindex);
	
	$ROUT = $R['Result'];
	
	if (isset($_GET['jf'])) {
		
		for ($ijc = 1; $ijc <= $_GET['jc']; $ijc ++) {
		
			$ROUT = array_merge($ROUT, GetR($_GET['jf'], $_GET['jl'], $ijc)); 
			
		}
			
	}

	$RowName = array('Дата' => 'string'); 	// Название вопроса
	
	$RowCol = array(''); 					// Столбец

	$RowRow = array('');					// Строка
	
	$RowNum = array('');					// Номер вопроса
	
	$Offset = 1;

	if (isset($_GET['f'])) {
		
		$RegF = explode('*', $_GET['f']);
		
		foreach ($RegF as $RFV) {

			$RowName[$RFV] = 'string';
			
			array_push($RowCol, '');
			
			array_push($RowRow, '');
			
			array_push($RowNum, '');
			
			$Offset ++;
			
		}
		
	}

	if (isset($_GET['a'])) {
		
		$RegA = explode('*', $_GET['a']);
		
		foreach ($RegA as $RAV) {
			
			$RowName[$RAV] = 'string';
			
			array_push($RowCol, '');
			
			array_push($RowRow, '');
			
			array_push($RowNum, '');
			
			$Offset ++;
			
		}
		
	}
	
	$QN = Array();

	foreach($ROUT as $Q) {
		
		$ICOL = 0;
		
		foreach($Q as $QK => $QV) {

			array_push($QN, $QK);
		
			array_push($RowNum, $QK);

			foreach ($QV as $QVK => $QVV) {
			
				if ($QVK == 'Name') $RowName[$QVV . ' (' . (count($Q) - $ICOL) . ')'] = 'string';
			
				if ($QVK == 'Col') array_push($RowCol, $QVV);

				if ($QVK == 'Row') array_push($RowRow, $QVV);
	
			}
			
			$ICOL ++;

		}

	}

	$DocRows = Array($RowCol, $RowRow, $RowNum);
		
	$Filter = '';

	foreach ($_GET as $key => $val) if (! Preg_match('/^p$|^t$|^n$|^f$|^a$|^jf$|^jl$|^jc$/', $key)) $Filter .= ' AND `' . $key . '` IN ('. $val . ')';

	$Users = $DB -> prep('SELECT `id`, `data1`, `data2`, `TimeStamp` FROM `users` WHERE `prid` = :prid' . $Filter, array('prid' => $_GET['p'])) -> fetchAll(PDO :: FETCH_ASSOC);
	
	foreach ($Users as $UV) {
		
		$UserData = $DBQ -> prep('SELECT `QName`, `QResponse` FROM `u' . $UV['id'] . '` ORDER BY `Journal`, `QSI`') -> fetchAll(PDO :: FETCH_ASSOC);

		$UD = Array();
		
		foreach ($UserData as $UDV) $UD[$UDV['QName']] = $UDV['QResponse'];
		
		$RowUserData = array($UV['TimeStamp']);
		
		if (isset($_GET['f']) && isset($RegF[0])) array_push($RowUserData, $UV['data1']);
		
		if (isset($_GET['f']) && isset($RegF[1])) array_push($RowUserData, $UV['data2']);

		if (isset($_GET['a'])) foreach ($RegA as $RAV) array_push($RowUserData, (isset($UD[$RAV]) ? $UD[$RAV] : ''));
		
		foreach ($QN as $QNV) array_push($RowUserData, (isset($UD[$QNV]) ? $UD[$QNV] : ''));

		array_push($DocRows, $RowUserData);
		
	}
	
	require('XLSX/xlsxwriter.class.php');
	
	$CurrentDate = date("d.m.Y - H.i");
	
	$Total = '(' . (count($DocRows) - 3) . ')';

	$fname = 'REPORT (' . $CurrentDate . ') ' . preg_replace('/,\s+|,/', ' ', $_GET['n']) . '.xlsx';
	
	$SheetName = $_GET['n'] . ' ' . $Total;
			
	$writer = new XLSXWriter();
	
	$writer -> setAuthor('Hotresearch');
	
	$NameStyle = array('fill' => '#555', 'color' => '#fff', 'border' => 'left,right,top,bottom', 'border_color' => '#fff');
	
	$TableStyle = array('fill' => '#c6e0b4', 'border' => 'bottom');
	
	$writer -> writeSheetHeader($SheetName, $RowName, $NameStyle);
	
	foreach ($DocRows as $i => $row) {
		
		if ($i < 3) $writer -> writeSheetRow($SheetName, $rowdata = $row, $TableStyle);
		
		else $writer -> writeSheetRow($SheetName, $rowdata = $row);
		
	}
	
	$styles1 = array( 'border'=>'left,right,top,bottom');
	
	foreach ($R['SIndex'] as $Interval) {
		
		$writer -> markMergedCell($SheetName, $start_row = 0, $start_col = $Interval[0] + $Offset, $end_row = 0, $end_col = $Interval[1] + $Offset);
		
		//$writer -> writeSheetRow($SheetName, $rowdata = array(0, $Interval[0] + $Offset, 0, $Interval[1] + $Offset), $styles1);
		
	}

	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	
	header('Content-Disposition: attachment;filename="' . $fname . '"');
	
	header('Cache-CoRowUserDataol: max-age=0');
	
	$writer->writeToStdOut();

}

function GetR($Start, $Finish, $Journal = 0) {
	
	Global $DBQ;
	
	$Result = Array();
	
	$SIndex = Array();
	
	for ($ind = $Start, $ti = 0; $ind <= $Finish; $ind++) {
		
		$Q = Array();
		
		$a = $DBQ -> prep('SELECT * FROM `qscheme` WHERE `qscheme`.`id` = :index', array('index' =>  $ind)) -> fetch(PDO :: FETCH_ASSOC);
		
		$Num = $a['Num'] ? preg_replace('/\./', '_', $a['Num']) : false;
		
		$InputType = $a['InputType'] ? $a['InputType'] : false;

		if ($Num && $InputType) {
			
			$R = Array();
			
			$ColsData = $a['ColsData'] ? explode('*', $a['ColsData']) : false;

			$RowsData = $a['RowsData'] ? explode('*', $a['RowsData']) : false;
			
			$InputType = $a['InputType'];
				
			if ($Journal > 0) $Num = $Journal . '-' . $Num;
			
			if ($a['OutMark']) { // Строки - столбцы
			
				if ($RowsData) { // Если есть строки
				
					if (($InputType != 'radio' && $InputType != 'checkbox') || count($ColsData) > 2) $SelectIndex = Array($ti);
				
					foreach ($RowsData as $RN => $RV) {
						
						$RV = preg_replace('/\[.+\]|\{.+\}/', '', $RV);
							
						if ($ColsData) { // Если есть столбцы

							$ColsString = Array();

							foreach ($ColsData as $CN => $CV) {
								
								if ($CN > 0) { // Нулевой столбец пропускается
								
									$CV = preg_replace('/\[.+\]|\{.+\}/', '', $CV);
								
									if ($InputType == 'radio' || $InputType == 'checkbox') {  // Номер строки учитыватся, номер столбца не учитывается, но пишется
									
										$ColsString[] = $CN . ' ' . $CV;
									
									} else { // Номер строки учитыватся, номер столбца учитывается
										
										$R[$Num . '_' . ($RN + 1) . '_' . $CN] = array('Name' => $a['Name'], 'Col' => $CV, 'Row' => $RV);
										
										$ti ++;
										
									}
								
								}
							
							}
							
							if ($InputType == 'radio' || $InputType == 'checkbox') {
								
								$R[$Num . '_' . ($RN + 1)] = array('Name' => $a['Name'], 'Col' => implode(',', $ColsString), 'Row' => $RV);
								
								$ti ++;
								
							}
	
						} else { // Если нет столбцов
							
							
							
						}

					}
					
					if (($InputType != 'radio' && $InputType != 'checkbox') || count($ColsData) > 2) array_push($SelectIndex, $ti - 1);
				
				} else { // Если нет строк
		
					
					
				}
			
			} else { // Столбцы - строки
				
				if ($ColsData) { // Если есть столбцы
				
					if (($InputType != 'radio' && $InputType != 'checkbox') || count($ColsData) > 2) $SelectIndex = Array($ti);
				
					foreach ($ColsData as $CN => $CV) {
						
						if ($CN > 0) { // Нулевой столбец пропускается
						
							$CV = preg_replace('/\[.+\]|\{.+\}/', '', $CV);
							
							if ($RowsData) { // Если есть строки
							
								$RowsString = Array();

								foreach ($RowsData as $RN => $RV) {
									
									$RV = preg_replace('/\[.+\]|\{.+\}/', '', $RV);
								
									if ($InputType == 'radio' || $InputType == 'checkbox') {  // Номер столбца учитыватся, номер строки не учитывается, но пишется
									
										$RowsString[] = ($RN + 1) . ' ' . $RV;
									
									} else { // Номер столбца учитыватся, номер строки учитывается
										
										$R[$Num . '_' . $CN . '_' . ($RN + 1)] = array('Name' => $a['Name'], 'Col' => $CV, 'Row' => $RV);
										
										$ti ++;
										
									}
								
								}
								
								if ($InputType == 'radio' || $InputType == 'checkbox') {
									
									$R[$Num . (count($ColsData) > 2 ? '_' . $CN : '')] = array('Name' => $a['Name'], 'Col' => $CV, 'Row' => implode(',', $RowsString));
									
									$ti ++;
									
								}
								
							} else { // Если нет строк
								
								
								
							}
							
						}
						
					}
					
					if (($InputType != 'radio' && $InputType != 'checkbox') || count($ColsData) > 2) array_push($SelectIndex, $ti - 1);
				
				} else { // Если нет столбцов
					
					if ($RowsData) { // Если есть строки
						
						$RowsString = Array();
						
						if ($InputType != 'radio' && $InputType != 'checkbox') $SelectIndex = Array($ti);
						
						foreach ($RowsData as $RN => $RV) {
							
							$RV = preg_replace('/\[.+\]|\{.+\}/', '', $RV);
						
							if ($InputType == 'radio' || $InputType == 'checkbox') { // Номер строки не учитывается, но пишестя
								
								$RowsString[] = ($RN + 1) . ' ' . $RV;
									
							} else { // Номер строки учитывается

								$R[$Num . '_' . ($RN + 1)] = array('Name' => $a['Name'], 'Col' => '', 'Row' => $RV);
								
								$ti ++;
									
							}
							
						}
						
						if ($InputType == 'radio' || $InputType == 'checkbox') {
							
							$R[$Num] = array('Name' => $a['Name'], 'Col' => '', 'Row' => implode(',', $RowsString));
							
							$ti ++;
							
						} else array_push($SelectIndex, $ti - 1);

					} else { // Если нет строк
					
						$RowsString = Array();
					
						if ($a['InputData']) {

							$RowsStringData = explode('*', $a['InputData']);
							
							foreach ($RowsStringData as $RSN => $RSV) $RowsString[] = ($RSN + 1) . ' ' . preg_replace('/\[.+\]|\{.+\}/', '', $RSV);
							
						}
						
						$R[$Num] = array('Name' => $a['Name'], 'Col' => '', 'Row' => (count($RowsString) ? implode(',', $RowsString) : ''));
						
						$ti ++;
						
					}
					
				}
	
			}
			
			$Q = array_merge($Q, $R);
			
			if (isset($SelectIndex)) {
				
				array_push($SIndex, $SelectIndex);
				
				unset($SelectIndex);
				
			}
				
		}
		
		array_push($Result, $Q);

	}
	
	return Array('Result' => $Result, 'SIndex' => $SIndex);
	
}

?>