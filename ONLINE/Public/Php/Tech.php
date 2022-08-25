<?php 

require_once 'Functions.php';

require_once 'DbSettings.php';

//$Hash = hash('sha256', md5($_GET['Login']) . 'mrs.Maysel');

//echo $Hash;

die();

$DB = new DBWORKER($dbu['host'], $dbu['db'], 'utf8', $dbu['user'], $dbu['pass']);;

$DBQ = new DBWORKER($dbu['host'], 'koksarea_hot' . $_GET['Project'], 'utf8', $dbu['user'], $dbu['pass']);

$UD = $DB -> prep('SELECT * FROM `users` WHERE `prid` = ' . $_GET['Project']) -> fetchAll(PDO :: FETCH_ASSOC);

$VD = $DBQ -> prep('SELECT * FROM `vscheme`') -> fetchAll(PDO :: FETCH_ASSOC);

foreach ($UD as $user) {
	
	$Check = false;
	
	foreach ($VD as $view) {
		
		if ($user['id'] == $view['Uid']) {
			
			$Check = true;
			
			//$DBQ -> prep('UPDATE `vscheme` SET `UserAgent` = "' . $user['UserAgent'] . '" WHERE `Uid` = ' . $view['Uid']);
			
			break;
			
		}
		
	}
	
	if ($Check == false) echo 'check false ' . $user['id'] . '</br>';
	
}

echo '</br>Well done!';

die();

$max_qid = $DBQ -> prep('SELECT MAX(`id`) FROM `qscheme`') -> fetch(PDO :: FETCH_NUM)[0];

			$After = + $_GET['After'];

			$Col = isset($_GET['Col']) ? + $_GET['Col'] : 1;
		
			$Transit = $DBQ -> prep('SELECT `id`, `Transit`, `AltTransit` FROM `rscheme`') -> fetchAll(PDO :: FETCH_ASSOC);

			$NewTransit = array();

			$QueryString = '';

			$Change = false;

			foreach ($Transit as $TransitString) {
				
				foreach ($TransitString as $TRSK => $TRSV) {
					
					if ($TRSK == 'id') $Change = false;
					
					if ($TRSK != 'id') {
						
						if ($TRSV != '') {
						
							$TRSV_A = explode(',', $TRSV);
						
							$TRSV_B = array_map(function($x) {
								
								Global $After, $Col, $Change;
								
								if (is_numeric($x) && + $x > $After) {
									
									$Change = true;
									
									return $x + $Col;
									
								}
									
							}, $TRSV_A);
						
						}
						
						if ($Change) $TransitString[$TRSK] = $TRSV != '' ? implode(',', $TRSV_B) : 'NULL';
						
					}
					
				}
				
				if ($Change) array_push($NewTransit, $TransitString);
				
			}

			if (count($NewTransit)) {
				
				foreach ($NewTransit as $NTV) $QueryString .= '("' . implode('", "', $NTV) . '"),';
				
				$r = $DBQ -> prep('INSERT INTO `rscheme` (`id`, `Transit`, `AltTransit`) VALUES ' . substr(str_replace('""', 'NULL', $QueryString), 0, -1) . ' ON DUPLICATE KEY UPDATE Transit = VALUES(Transit), AltTransit = VALUES(AltTransit)');
				
			}
			
			for ($i = $max_qid; $i > $After; $i --) $DBQ -> prep('UPDATE `qscheme` SET `id` = ' . ($i + $Col) . ' WHERE `id` = ' . $i);

			for ($j = 1; $j <= $Col; $j ++) $DBQ -> prep('INSERT INTO `qscheme` (`id`) VALUES (' . ($After + $j) . ')');

// $GTIN = array('04650075194959', '04650075194966', '04650075195147', '04650075194980', '04650075194973', '04650075196021', '04650075196014', '04650075190135', '04650075190142', '04650075190159', '04650075195178', '04650075195161', '04650075195185');

// $str = '';

// $file = isset($_GET['FN']) ? $_GET['FN'] . '.csv' : 'Марки.csv';

// if (is_file($file)) unlink($file);

// for ($i = 0; $i < $_GET['SL']; $i ++) {
	
	// $index_gtin = isset($_GET['GTIN']) ? array_search($_GET['GTIN'], $GTIN) : mt_rand(0, 12);
		
	// $current_gtin = isset($_GET['GTIN']) ? $_GET['GTIN'] : $GTIN[$index_gtin];
	
	// $hash_length = $index_gtin ? ($index_gtin < 7 ? 42 : 86) : 42;
	
	// $serial = generator(9);
	
	// $s91 = generator(4);
	
	// $s92 = generator($hash_length);
	
	// if (isset($_GET['type'])) {
		
		// if ($_GET['type'] == 'TABLEMARK') {
	
			// $str .= generator(8, true) . '-' . generator(4, true) . '-' . generator(4, true) . '-' . generator(4, true) . '-' . generator(12, true) . ';';
			
			// $str .= $current_gtin;
			
			// $str .= ';0.0;0.0;0.0;;;';
			
			// $str .= '01' . $current_gtin . '21demo' . $serial . chr(29) . '91' . $s91 . chr(29) . '92' . $s92 . '==';
			
			// $str .= ';01' . $current_gtin . '21demo' . $serial;
			
			// $str .= ';(01)' . $current_gtin . '(21)demo' . $serial . '(91)' . $s91 . '(92)' . $s92 . '==';
			
			// $str .= ';;0;;0' . ($i < ($_GET['SL'] - 1) ? "\n" : '');
	
		// } else if ($_GET['type'] == 'TABLECIS') {
			
			// $str .= generator(8, true) . '-' . generator(4, true) . '-' . generator(4, true) . '-' . generator(4, true) . '-' . generator(12, true) . ';';
			
			// $str .= '01' . $current_gtin . '21demo' . $serial . ';';
			
			// $str .= $current_gtin . ';;;;2021-06-11T13:31:34.270Z;;;;;APPLIED;LOCAL;957efcdf-fb0d-4bbd-b966-5f2742469b14;;';
			
			// $str .= '01' . $current_gtin . '21demo' . $serial . chr(29) . '91' . $s91 . chr(29) . '92' . $s92 . '==';
			
			// $str .= ';;0' . ($i < ($_GET['SL'] - 1) ? "\n" : '');
			
		// }
	
	// } else $str .= '01' . $current_gtin . '21demo' . generator(9) . chr(29) . '91' . generator(4) . chr(29) . '92' . generator($hash_length) . '==' . ($i < ($_GET['SL'] - 1) ? "\n" : '');
	
	// file_put_contents($file, $str, FILE_APPEND | LOCK_EX);
	
	// $str = '';
	
// }

// echo $file . ' - ' . $i;

// -----------------------------------------------------------------------------------------------------------

// Найти первый пустой id ------------------------------------------------------------------------------------

// $EmptyId = $DB -> prep('SELECT (`users`.`id` + 1) as `empty_id` FROM `users` WHERE (SELECT 1 FROM `users` as `st` WHERE `st`.`id` = (`users`.`id` + 1)) IS NULL ORDER BY `users`.`id` LIMIT 1') -> fetch(PDO :: FETCH_NUM)[0];

// echo $EmptyId;

// -----------------------------------------------------------------------------------------------------------

// Обновить Difference в зависимости от ответа ---------------------------------------------------------------

// $users = $DB -> prep('SELECT `id` FROM `users` WHERE `prid` = "' . $_GET['Project'] . '" AND `Status` != "1" AND `StateIndex` > 2 AND `Difference` IS NULL') -> fetchAll(PDO :: FETCH_NUM);

// $str = '(';

// foreach ($users as $uid) {
	
	// $resp = $DBQ -> prep('SELECT `QResponse` FROM `u' . $uid[0] . '` WHERE `QName` = "' . str_replace('.', '_', $_GET['QName']) . '"') -> fetch(PDO :: FETCH_NUM);

	// if ($resp) $a = $DB -> prep('UPDATE `users` SET `Difference` = "' . $resp[0] . '" WHERE `id` = ' . $uid[0]);
	
// }

// -----------------------------------------------------------------------------------------------------------	

echo '</br>Well done!';
	
?>