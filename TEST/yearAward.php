<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("ОТЛАДКА БОНУСА");
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();
use Bitrix\Highloadblock\HighloadBlockTable as HLBT;
CModule::IncludeModule('highloadblock');
$highblock_id = 6;
$hl_block = HLBT::getById($highblock_id)->fetch();
$entity = HLBT::compileEntity($hl_block);
$entity_data_class = $entity->getDataClass();
global $USER;
$ID = $USER->GetID();
echo '<link rel="stylesheet" href="/rating/css/form.css">';
echo '<link rel="stylesheet" href="/rating/css/nav.css">';
echo '<script src="/rating/js/form.js"></script>';

?>

<?
$date = getdate();
$month = $date['mon'];//ТЕКУЩИЙ МЕСЯЦ
$months = array( 1 => 'Январь' , 'Февраль' , 'Март' , 'Апрель' , 'Май' , 'Июнь' , 'Июль' , 'Август' , 'Сентябрь' , 'Октябрь' , 'Ноябрь' , 'Декабрь' );
$year=2023;

$summ=0;
$awardsumm=0;
$allawardsumm=0;
$empawardsumm=0;
$freesumm=0;
for($m=1; $m<=$month; $m++){
	$awardsumm=0;
	$allawardsumm=0;
	$lastday =  cal_days_in_month(CAL_GREGORIAN, $m, $year);//последний день месяца
	$d1 = '1.'.$m.'.'.$year.' 00:00:00';//Дата1 для селекта в функциях
	$d2 = $lastday.'.'.$m.'.'.$year.' 00:00:00';//Дата2 для селекта в функциях

	$res = CCrmDeal::GetList(
        Array('DATE_CREATE' => 'DESC'), 
		Array("STAGE_ID" => array('C14:WON', 'C1:WON'), 'CLOSED'=> 'Y',
			">=CLOSEDATE" => $d1,
   				"<=CLOSEDATE" => $d2)
    );
	while($arDeal=$res->GetNext()){
		$awardsumm+=$arDeal['OPPORTUNITY'];
 	}
	$awardsumm=$awardsumm/100;
	$summ+=$awardsumm;
	$filter = Array
		(
		"ACTIVE"              => "Y",
		"GROUPS_ID"           => Array(26),
			"DATE_REGISTER_2" => $d1
		);
	$emps=0;
echo "МЕСЯЦ: ".$months[$m];
echo "<br>";
echo "<br>";
echo "Процент от сделок месяца: ".$awardsumm;
echo "<br>";
	$rsUsers = CUser::GetList(($by="work_department"), ($order="asc"), $filter);
	while($arUser = $rsUsers->GetNext()){
		if(isEmp($arUser['ID'])){
			$emps++;
		}
	}
echo "Кол-во сотрудников: ".$emps;
echo "<br>";
echo "Бонус при максимальной оценке: ".round($awardsumm/$emps,2);
echo "<br>";
echo "<br>";


	$rsUsers = CUser::GetList(($by="work_department"), ($order="asc"), $filter);
	while($arUser = $rsUsers->GetNext()){
		if(isEmp($arUser['ID'])){
			$rs_data = $entity_data_class::getList(array(
				'select' => array('*'),
				'filter'=>array('UF_MONTH'=>$m,'UF_YEAR'=>$year,'UF_EMPLOYEE'=>$arUser["ID"])
				));
			$count=0;
			$value=0;
			while($el = $rs_data->fetch()){
				$value+=$el['UF_RATE'];
				$count++;
			}
			if($count==0){$value=1; $count=1;}
			$rate=round($value/$count/10,1);
			echo "ФИО: ".$arUser["NAME"].$arUser["LAST_NAME"];
			echo "<br>";
			echo "ДАТА: ".$arUser["DATE_REGISTER"];
			echo "<br>";
			echo "ОЦЕНКА: ".$rate;
			echo "<br>";
			echo "БОНУС: ".round($awardsumm/$emps*$rate,2);
			echo "<br><br>";
			$allawardsumm+=round($awardsumm/$emps*$rate,2);
			if($arUser["ID"]==$USER->GetID()){
				$empawardsumm+=round($awardsumm/$emps*$rate,2);
			}
		}
	}
	$freesumm+=round($awardsumm - $allawardsumm, 2);
	echo "свободная Сумма: ".$freesumm;
	echo "<br>";
	echo "Итого: ".$summ;
	echo "<br>";
	echo "--------------------------------- ";
	echo "<br>";
}

?>
<?
	function isEmp($id){//Функция возвращает 1 если сотрудник не является стажером или аутсорсером
	$rsUser = CUser::GetByID($id);
	$arUser = $rsUser->Fetch();
	if($arUser['UF_TYPE_OF_WORK']==6622 || $arUser['UF_TYPE_OF_WORK']==6623){
		return true;
	}
	return false;
}
?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>