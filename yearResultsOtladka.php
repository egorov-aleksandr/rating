<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Title");
?>
<?
switch($_GET['TYPE']){
	case "yeardeals":
		yeardeals();
		break;
	case "monthdeals":
		monthdeals();
		break;
	case "projectdeals":
		projectdeals();
		break;
	case "actsdeals":
		actsdeals();
		break;
	case "licenses":
		licenses();
		break;
}

function yeardeals(){
//ДАТЫ
$date = getdate();
$tomonth = $date['mon'];//ТЕКУЩИЙ МЕСЯЦ
$toyear=$date['year'];

$fotmonth=$tomonth-1;
$fotyear=$toyear;
if($fotmonth==0){
$fotmonth=12;
$fotyear--;
}

$lastday =  cal_days_in_month(CAL_GREGORIAN, $tomonth, $toyear);//последний день месяца
$monthdate1 = '1.'.$tomonth.'.'.$toyear.' 00:00:00';//Дата1 для селекта в функциях, предыдущий от выбранного месяц
$monthdate2 = $lastday.'.'.$tomonth.'.'.$toyear.' 00:00:00';//Дата2 для селекта в функциях, предыдущий от выбранного месяц

$lastday =  cal_days_in_month(CAL_GREGORIAN, $fotmonth, $fotyear);//последний день месяца
$fotdate1 = '1.'.$fotmonth.'.'.$fotyear.' 00:00:00';//Дата1 для селекта в функциях, предыдущий от выбранного месяц
$fotdate2 = $lastday.'.'.$fotmonth.'.'.$fotyear.' 00:00:00';//Дата2 для селекта в функциях, предыдущий от выбранного месяц

$yeardate1 = '1.'."1".'.'.$toyear.' 00:00:00';//Дата1 для селекта в функциях, предыдущий от выбранного месяц
$yeardate2 = '31'.'.'.'12'.'.'.$toyear.' 00:00:00';//Дата2 для селекта в функциях, предыдущий от выбранного месяц

	$yeardealssum=0;
	$yearcountdeals=0;
	$res = CCrmDeal::GetList(
        Array('DATE_CREATE' => 'DESC'), 
		Array("STAGE_ID" => array('C14:WON', 'C1:WON','WON'), 'CLOSED'=> 'Y',
			">=CLOSEDATE" => $yeardate1,
   				"<=CLOSEDATE" => $yeardate2)
    );
	echo "ВЫРУЧКА ГОД";
	echo "<br><br>";
	while($arDeal=$res->GetNext()){
		$yeardealssum+=$arDeal['OPPORTUNITY'];
		$yearcountdeals++;
		echo '<a href="https://b24.opti.ooo/crm/deal/details/'.$arDeal["ID"].'/">'.$arDeal['TITLE'].'</a>';
		echo "<br>";
		echo "Стадмя: ".$arDeal["STAGE_ID"];
		echo "<br><br>";
 	}	
	echo "Колво: ".$yearcountdeals;
echo "<br>";
	echo "Сумма: ".$yeardealssum;
}


function monthdeals(){
//ДАТЫ
$date = getdate();
$tomonth = $date['mon'];//ТЕКУЩИЙ МЕСЯЦ
$toyear=$date['year'];

$fotmonth=$tomonth-1;
$fotyear=$toyear;
if($fotmonth==0){
$fotmonth=12;
$fotyear--;
}

$lastday =  cal_days_in_month(CAL_GREGORIAN, $tomonth, $toyear);//последний день месяца
$monthdate1 = '1.'.$tomonth.'.'.$toyear.' 00:00:00';//Дата1 для селекта в функциях, предыдущий от выбранного месяц
$monthdate2 = $lastday.'.'.$tomonth.'.'.$toyear.' 00:00:00';//Дата2 для селекта в функциях, предыдущий от выбранного месяц

$lastday =  cal_days_in_month(CAL_GREGORIAN, $fotmonth, $fotyear);//последний день месяца
$fotdate1 = '1.'.$fotmonth.'.'.$fotyear.' 00:00:00';//Дата1 для селекта в функциях, предыдущий от выбранного месяц
$fotdate2 = $lastday.'.'.$fotmonth.'.'.$fotyear.' 00:00:00';//Дата2 для селекта в функциях, предыдущий от выбранного месяц

$yeardate1 = '1.'."1".'.'.$toyear.' 00:00:00';//Дата1 для селекта в функциях, предыдущий от выбранного месяц
$yeardate2 = '31'.'.'.'12'.'.'.$toyear.' 00:00:00';//Дата2 для селекта в функциях, предыдущий от выбранного месяц

	$yeardealssum=0;
	$yearcountdeals=0;
	$res = CCrmDeal::GetList(
        Array('DATE_CREATE' => 'DESC'), 
		Array("STAGE_ID" => array('C14:WON', 'C1:WON','WON'), 'CLOSED'=> 'Y',
			">=CLOSEDATE" => $monthdate1,
   				"<=CLOSEDATE" => $monthdate2)
    );
	echo "ВЫРУЧКА МЕСЯЦ";
	echo "<br><br>";
	while($arDeal=$res->GetNext()){
		$yeardealssum+=$arDeal['OPPORTUNITY'];
		$yearcountdeals++;
		echo '<a href="https://b24.opti.ooo/crm/deal/details/'.$arDeal["ID"].'/">'.$arDeal['TITLE'].'</a>';
		echo "<br>";
		echo "Стадия: ".$arDeal["STAGE_ID"];
		echo "<br><br>";
 	}	
	echo "Колво: ".$yearcountdeals;
	echo "<br>";
	echo "Сумма: ".$yeardealssum;
}


function projectdeals(){
//ДАТЫ
$date = getdate();
$tomonth = $date['mon'];//ТЕКУЩИЙ МЕСЯЦ
$toyear=$date['year'];

$fotmonth=$tomonth-1;
$fotyear=$toyear;
if($fotmonth==0){
$fotmonth=12;
$fotyear--;
}

$lastday =  cal_days_in_month(CAL_GREGORIAN, $tomonth, $toyear);//последний день месяца
$monthdate1 = '1.'.$tomonth.'.'.$toyear.' 00:00:00';//Дата1 для селекта в функциях, предыдущий от выбранного месяц
$monthdate2 = $lastday.'.'.$tomonth.'.'.$toyear.' 00:00:00';//Дата2 для селекта в функциях, предыдущий от выбранного месяц

$lastday =  cal_days_in_month(CAL_GREGORIAN, $fotmonth, $fotyear);//последний день месяца
$fotdate1 = '1.'.$fotmonth.'.'.$fotyear.' 00:00:00';//Дата1 для селекта в функциях, предыдущий от выбранного месяц
$fotdate2 = $lastday.'.'.$fotmonth.'.'.$fotyear.' 00:00:00';//Дата2 для селекта в функциях, предыдущий от выбранного месяц

$yeardate1 = '1.'."1".'.'.$toyear.' 00:00:00';//Дата1 для селекта в функциях, предыдущий от выбранного месяц
$yeardate2 = '31'.'.'.'12'.'.'.$toyear.' 00:00:00';//Дата2 для селекта в функциях, предыдущий от выбранного месяц

	$yeardealssum=0;
	$yearcountdeals=0;
	$res = CCrmDeal::GetList(
        Array('DATE_CREATE' => 'DESC'), 
		Array("STAGE_ID" => array('3'))
    );
	echo "ПРОЕКТЫ В РАБОТЕ";
	echo "<br><br>";
	while($arDeal=$res->GetNext()){
		$yeardealssum+=$arDeal['OPPORTUNITY'];
		$yearcountdeals++;
		echo '<a href="https://b24.opti.ooo/crm/deal/details/'.$arDeal["ID"].'/">'.$arDeal['TITLE'].'</a>';
		echo "<br>";
		echo "Стадия: ".$arDeal["STAGE_ID"];
		echo "<br><br>";
 	}	
	echo "Колво: ".$yearcountdeals;
	echo "<br>";
	echo "Сумма: ".$yeardealssum;
}

function actsdeals(){
//ДАТЫ
$date = getdate();
$tomonth = $date['mon'];//ТЕКУЩИЙ МЕСЯЦ
$toyear=$date['year'];

$fotmonth=$tomonth-1;
$fotyear=$toyear;
if($fotmonth==0){
$fotmonth=12;
$fotyear--;
}

$lastday =  cal_days_in_month(CAL_GREGORIAN, $tomonth, $toyear);//последний день месяца
$monthdate1 = '1.'.$tomonth.'.'.$toyear.' 00:00:00';//Дата1 для селекта в функциях, предыдущий от выбранного месяц
$monthdate2 = $lastday.'.'.$tomonth.'.'.$toyear.' 00:00:00';//Дата2 для селекта в функциях, предыдущий от выбранного месяц

$lastday =  cal_days_in_month(CAL_GREGORIAN, $fotmonth, $fotyear);//последний день месяца
$fotdate1 = '1.'.$fotmonth.'.'.$fotyear.' 00:00:00';//Дата1 для селекта в функциях, предыдущий от выбранного месяц
$fotdate2 = $lastday.'.'.$fotmonth.'.'.$fotyear.' 00:00:00';//Дата2 для селекта в функциях, предыдущий от выбранного месяц

$yeardate1 = '1.'."1".'.'.$toyear.' 00:00:00';//Дата1 для селекта в функциях, предыдущий от выбранного месяц
$yeardate2 = '31'.'.'.'12'.'.'.$toyear.' 00:00:00';//Дата2 для селекта в функциях, предыдущий от выбранного месяц

	$yeardealssum=0;
	$yearcountdeals=0;
	$res = CCrmDeal::GetList(
        Array('DATE_CREATE' => 'DESC'), 
		Array("STAGE_ID" => array('9'))
    );
	echo "АКТЫ";
	echo "<br><br>";
	while($arDeal=$res->GetNext()){
		$yeardealssum+=$arDeal['OPPORTUNITY'];
		$yearcountdeals++;
		echo '<a href="https://b24.opti.ooo/crm/deal/details/'.$arDeal["ID"].'/">'.$arDeal['TITLE'].'</a>';
		echo "<br>";
		echo "Стадия: ".$arDeal["STAGE_ID"];
		echo "<br><br>";
 	}	
	echo "Колво: ".$yearcountdeals;
echo "<br>";
	echo "Сумма: ".$yeardealssum;
}

function licenses(){
//ДАТЫ
$date = getdate();
$tomonth = $date['mon'];//ТЕКУЩИЙ МЕСЯЦ
$toyear=$date['year'];

$fotmonth=$tomonth-1;
$fotyear=$toyear;
if($fotmonth==0){
$fotmonth=12;
$fotyear--;
}

$lastday =  cal_days_in_month(CAL_GREGORIAN, $tomonth, $toyear);//последний день месяца
$monthdate1 = '1.'.$tomonth.'.'.$toyear.' 00:00:00';//Дата1 для селекта в функциях, предыдущий от выбранного месяц
$monthdate2 = $lastday.'.'.$tomonth.'.'.$toyear.' 00:00:00';//Дата2 для селекта в функциях, предыдущий от выбранного месяц

$lastday =  cal_days_in_month(CAL_GREGORIAN, $fotmonth, $fotyear);//последний день месяца
$fotdate1 = '1.'.$fotmonth.'.'.$fotyear.' 00:00:00';//Дата1 для селекта в функциях, предыдущий от выбранного месяц
$fotdate2 = $lastday.'.'.$fotmonth.'.'.$fotyear.' 00:00:00';//Дата2 для селекта в функциях, предыдущий от выбранного месяц

$yeardate1 = '1.'."1".'.'.$toyear.' 00:00:00';//Дата1 для селекта в функциях, предыдущий от выбранного месяц
$yeardate2 = '31'.'.'.'12'.'.'.$toyear.' 00:00:00';//Дата2 для селекта в функциях, предыдущий от выбранного месяц

	$yeardealssum=0;
	$yearcountdeals=0;
	$res = CCrmDeal::GetList(
        Array('DATE_CREATE' => 'DESC'), 
		Array("STAGE_ID" => array('C14:WON', 'C1:WON','WON'), 'CLOSED'=> 'Y',
			">=CLOSEDATE" => $yeardate1,
   				"<=CLOSEDATE" => $yeardate2,
				">UF_CRM_1565116630"=>0)
    );
	echo "ЛИЦЕНЗИИ";
	echo "<br><br>";
	while($arDeal=$res->GetNext()){
		$yeardealssum+=$arDeal['UF_CRM_1565116630'];
		$yearcountdeals++;
		echo '<a href="https://b24.opti.ooo/crm/deal/details/'.$arDeal["ID"].'/">'.$arDeal['TITLE'].'</a>';
		echo "<br>";
		echo "Стадия: ".$arDeal["STAGE_ID"];
		echo "<br><br>";
 	}	
	echo "Колво: ".$yearcountdeals;
echo "<br>";
	echo "Сумма: ".$yeardealssum;
}
?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>