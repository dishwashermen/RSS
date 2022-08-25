<?php 

require_once 'Functions.php';

$strh = '<Сообщение xmlns="https://bit-erp.ru/adapter/Spravochnik.NomenklaturaERP/version2" xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
';

$str = '<СтрокаСообщения>
		<АлкогольнаяПродукция>true</АлкогольнаяПродукция>
		<БазоваяУпаковка>
			<Наименование>бут</Наименование>
			<Идентификатор>3e688689-0be3-11ec-aaa1-005056041a72</Идентификатор>
		</БазоваяУпаковка>
		<бг_ГодУрожая>2013</бг_ГодУрожая>
		<бг_ЕК_СУМ>
			<Код>1000004</Код>
			<Идентификатор>ba87087d-6983-11e4-97fe-005056ac000a</Идентификатор>
		</бг_ЕК_СУМ>
		<бг_ТемператураХранения>+5 +20 не выше 85%</бг_ТемператураХранения>
		<ВесЗнаменатель>1</ВесЗнаменатель>
		<ВесИспользовать>true</ВесИспользовать>
		<ВесЧислитель>1.15</ВесЧислитель>
		<ВидНоменклатуры>
			<Наименование>Алкогольная продукция</Наименование>
			<Идентификатор>5a7228d5-f6c7-11eb-aaa0-005056041a72</Идентификатор>
		</ВидНоменклатуры>
		<ДлинаЗнаменатель>1</ДлинаЗнаменатель>
		<ДлинаИспользовать>true</ДлинаИспользовать>
		<ДлинаЧислитель>0.075</ДлинаЧислитель>
		<ЕдиницаИзмерения>
			<Код>868 </Код>
			<Наименование>бут</Наименование>
			<Идентификатор>ad249ab5-f06f-11eb-aaa0-005056041a72</Идентификатор>
		</ЕдиницаИзмерения>
		<ИспользоватьСерии>false</ИспользоватьСерии>
		<ИспользоватьУпаковки>true</ИспользоватьУпаковки>
		<Код>10121453   </Код>
		<Крепость>13.5</Крепость>
		<МаркированнаяСпиртосодержащаяПродукция>true</МаркированнаяСпиртосодержащаяПродукция>
		<Наименование>Вино защ. наим .места проис..красное сухое выдержанное "Фаустино V Резерва" 13,5 ОБ% ГУ 2013 0.75л.</Наименование>
		<НаименованиеПолное>Фаустино V Резерва Риоха DOC 2013 кр сух 0,75 Испания</НаименованиеПолное>
		<ОбъемДАЛ>0.075</ОбъемДАЛ>
		<ПометкаУдаления>false</ПометкаУдаления>
		<Производитель>
			<Наименование>Faustino</Наименование>
			<Идентификатор>5c0c5354-5d94-11e7-b0d7-005056ac0aed</Идентификатор>
		</Производитель>
		<СтавкаНДС>
			<Ставка>20</Ставка>
			<Идентификатор>8325a8a2-be3c-11eb-aa9c-005056041a72</Идентификатор>
		</СтавкаНДС>
		<ТоварнаяКатегория>
			<Идентификатор>42b7fc11-5d93-11e7-b0d7-005056ac0aed</Идентификатор>
		</ТоварнаяКатегория>
		<УчетПоХарактеристикам>false</УчетПоХарактеристикам>
		<ЭтоГруппа>false</ЭтоГруппа>
		<ШтрихкодыНоменклатуры>
			<Строка>
				<Штрихкод>8410441412065</Штрихкод>
				<Упаковка>
					<Идентификатор>3e688689-0be3-11ec-aaa1-005056041a72</Идентификатор>
				</Упаковка>
			</Строка>
			<Строка>
				<Штрихкод>8410441417060</Штрихкод>
				<Упаковка>
					<Идентификатор>3e68868a-0be3-11ec-aaa1-005056041a72</Идентификатор>
				</Упаковка>
			</Строка>
		</ШтрихкодыНоменклатуры>
		<Упаковки>
			<Строка>
				<Упаковка>
					<Наименование>бут</Наименование>
					<Идентификатор>3e688689-0be3-11ec-aaa1-005056041a72</Идентификатор>
				</Упаковка>
				<ЕдиницаИзмерения>
					<Код>868 </Код>
					<Наименование>бут</Наименование>
					<Идентификатор>ad249ab5-f06f-11eb-aaa0-005056041a72</Идентификатор>
				</ЕдиницаИзмерения>
				<Числитель>1</Числитель>
				<Знаменатель>1</Знаменатель>
				<Вес>1.15</Вес>
			</Строка>
			<Строка>
				<ИдентификаторЕК>bdbc3430-5745-11e8-bc19-005056ac0aed</ИдентификаторЕК>
				<Упаковка>
					<Наименование>кор (6 бут)</Наименование>
					<Идентификатор>3e68868a-0be3-11ec-aaa1-005056041a72</Идентификатор>
				</Упаковка>
				<ЕдиницаИзмерения>
					<Код>8751</Код>
					<Наименование>кор</Наименование>
					<Идентификатор>d3c9eecc-f050-11eb-aaa0-005056041a72</Идентификатор>
				</ЕдиницаИзмерения>
				<Числитель>6</Числитель>
				<Знаменатель>1</Знаменатель>
				<Вес>7</Вес>
			</Строка>
			<Строка>
				<ИдентификаторЕК>bdbc3430-5745-11e8-bc19-005056ac0aed</ИдентификаторЕК>
				<Упаковка>
					<Наименование>Паллета (570 бут)</Наименование>
					<Идентификатор>3e68868b-0be3-11ec-aaa1-005056041a72</Идентификатор>
				</Упаковка>
				<ЕдиницаИзмерения>
					<Код>3001</Код>
					<Наименование>Паллета</Наименование>
					<Идентификатор>f7742584-eebe-11eb-aaa0-005056041a72</Идентификатор>
				</ЕдиницаИзмерения>
				<Числитель>570</Числитель>
				<Знаменатель>1</Знаменатель>
			</Строка>
		</Упаковки>
		';
		
$strf = '<СвойстваСообщения>
		<СобытиеСообщения>Выгружено</СобытиеСообщения>
		<ДатаСобытия>2021-09-20T14:17:07</ДатаСобытия>
		<ИдентификаторСообщения>11468bbb-a9fd-4382-9b30-72c78aedf5dc</ИдентификаторСообщения>
		<ИдентификаторСообщенияИсточника>11468bbb-a9fd-4382-9b30-72c78aedf5dc</ИдентификаторСообщенияИсточника>
		<ИмяБазы>template_erp_beluga</ИмяБазы>
		<ПолноеИмяБазы>Srvr="devbelugas8:1541";Ref="dev_vguzel_erp_beluga";</ПолноеИмяБазы>
		<ИмяБазыИсточника>template_erp_beluga</ИмяБазыИсточника>
		<ПолноеИмяБазыИсточника>Srvr="devbelugas8:1541";Ref="dev_vguzel_erp_beluga";</ПолноеИмяБазыИсточника>
		<КлючМаршрутизации>xml.Справочник.Номенклатура</КлючМаршрутизации>
	</СвойстваСообщения>
</Сообщение>';
		
$strw = '';

$file = isset($_GET['FileName']) ? $_GET['FileName'] . '.txt' : 'Файл.txt';

if (is_file($file)) unlink($file);

file_put_contents($file, $strh, FILE_APPEND | LOCK_EX);

for ($i = 0; $i < $_GET['Col']; $i ++) {
	
	$strw .= $str . '<Идентификатор>' . generator(8, true) . '-' . generator(4, true) . '-' . generator(4, true) . '-' . generator(4, true) . '-' . generator(12, true) . '</Идентификатор>
	</СтрокаСообщения>
	';
	
	file_put_contents($file, $strw, FILE_APPEND | LOCK_EX);
	
	$strw = '';
	
}

file_put_contents($file, $strf, FILE_APPEND | LOCK_EX);

echo '</br>Well done!';
	
?>