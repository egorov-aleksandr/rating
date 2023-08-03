<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Отладка Лицензий");
?>

<?
$month = $_GET['month'];
$year = $_GET['year'];
echo "месяц: ".$month;
echo "<br>";
echo "год: ".$year;
echo "<br><br>";
$lastday =  cal_days_in_month(CAL_GREGORIAN, $month, $year);//последний день месяца
$date1 = '1.'.$month.'.'.$year.' 00:00:00';//Дата1 для селекта в функциях, предыдущий от выбранного месяц
$date2 = $lastday.'.'.$month.'.'.$year.' 00:00:00';//Дата2 для селекта в функциях, предыдущий от выбранного месяц

$res = CCrmDeal::GetList(
        Array('DATE_CREATE' => 'DESC'), 
		Array("STAGE_ID" => array('C14:WON', 'C1:WON','WON'), 'CLOSED'=> 'Y',
			">=CLOSEDATE" => $date1,
   			"<=CLOSEDATE" => $date2,
			">UF_CRM_1565116630"=>0)
    );
	$D=0;
	$licenses=0;
	while($arDeal=$res->GetNext()){

		$D++;
		$time=substr($arDeal["CLOSEDATE"], 0, strpos($arDeal["CLOSEDATE"], ' '));
		echo "ID: ".$arDeal["ID"];
		echo "<br>";
		echo $time." - ".'<a target="_blank" href="https://b24.opti.ooo/crm/deal/details/'.$arDeal['ID'].'/">'.$arDeal['TITLE'].'</a>';
		echo "<br>";
		echo "Сумма: ".$arDeal['UF_CRM_1565116630'];
		echo "<br>";
		$licenses+=$arDeal['UF_CRM_1565116630'];
 	}
	echo "<br><br>";
	echo "Сумма:".$licenses;
?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>