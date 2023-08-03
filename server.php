<?//РАБОТА С ХАЙЛОАД
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule("tasks");?>
<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();
use Bitrix\Highloadblock\HighloadBlockTable as HLBT;
CModule::IncludeModule('highloadblock');
CModule::IncludeModule('support');
?>

<?//ПОЛУЧАЕМ команду
$str_json = file_get_contents('php://input');
$command = json_decode($str_json)->command;
switch($command){
	case "SAVE":
        send($str_json);
        break;
	case "ADDTASKS":
		addtasks($str_json);
		break;
	case "UPLOAD":
		upload($str_json);
		break;
}

?>

<?//СОХРАНЕНИЕ НАЧИСЛЕНИЙ ОДНОГО ПОЛЬЗОВАТЕЛЯ
function send($str_json){
	$month = json_decode($str_json)->month;
	$year = json_decode($str_json)->year;
	$emp = json_decode($str_json)->employee;
	$oklad = json_decode($str_json)->oklad;
	$tax = json_decode($str_json)->tax;
	$avans = json_decode($str_json)->avans;
	$award = json_decode($str_json)->award;
	$payoff = json_decode($str_json)->payoff;
	$hospital = json_decode($str_json)->hospital;
	$holiday = json_decode($str_json)->holiday;

	$highblock_id = 8;
	$hl_block = HLBT::getById($highblock_id)->fetch();
	$entity = HLBT::compileEntity($hl_block);
	$entity_data_class = $entity->getDataClass();

	
	//ЗАПРОС В ХАЙЛОАД
	$rs_data = $entity_data_class::getList(array(
	   'select' => array('ID','UF_EMPLOYEE', 'UF_MONTH','UF_YEAR'),
		'filter' => array('UF_EMPLOYEE'=>$emp,'UF_MONTH'=>$month,'UF_YEAR'=>$year)
	));
	$data = $rs_data->fetch();
	if($data){
		$idForUpdate = $data["ID"];
		$result = $entity_data_class::update($idForUpdate,array(
			"UF_OKLAD"=>$oklad,
			"UF_TAX"=>$tax,
			"UF_AVANS" => $avans,
			"UF_AWARD" => $award,
			"UF_PAYOFF" => $payoff,
			"UF_HOSPITAL" => $hospital,
			"UF_HOLIDAY" => $holiday
	   ));
		echo json_encode("Данные изменены", JSON_UNESCAPED_UNICODE);
	}else{
		$result = $entity_data_class::add(array(
			"UF_EMPLOYEE"=> $emp,
			"UF_YEAR"=> $year,
			"UF_MONTH"=> $month,
			"UF_OKLAD"=>$oklad,
			"UF_TAX"=>$tax,
			"UF_AVANS" => $avans,
			"UF_AWARD" => $award,
			"UF_PAYOFF" => $payoff,
			"UF_HOSPITAL" => $hospital,
			"UF_HOLIDAY" => $holiday
	   ));
		echo json_encode("Данные добавлены", JSON_UNESCAPED_UNICODE);
	}
}

//ДОБАВЛЕНИЕ ЗАДАЧ
function addtasks($str_json){
	$month = json_decode($str_json)->month;
	$year = json_decode($str_json)->year;
	$months = array( 1 => 'январь' , 'февраль' , 'март' , 'апрель' , 'май' , 'июнь' , 'июль' , 'август' , 'сентябрь' , 'октябрь' , 'ноябрь' , 'декабрь' );
	$titlemonth = $month+1;
	$titleyear = $year;
	if($titleyear==2023){$titleyear=23;}
	if($titleyear==2022){$titleyear=22;}
	if($titlemonth>12){$titlemonth=1;$titleyear++;}
	$highblock_id = 8;//ХАЙЛОАД НАЧИСЛЕНИЙ
	$hl_block = HLBT::getById($highblock_id)->fetch();
	$entity = HLBT::compileEntity($hl_block);
	$entity_data_class = $entity->getDataClass();

	
	//ЗАПРОС В ХАЙЛОАД
	$rs_data = $entity_data_class::getList(array(
	   'select' => array("*"),
		'filter' => array('UF_MONTH'=>$month,'UF_YEAR'=>$year)
	));
	$countTasks =0;
	$countUsers =0;
	$countFailed =0;
	while($el = $rs_data->fetch()){//ЦИКЛ ПО НАЧИСЛЕНИЯМ
		$type=getTypeOfWork($el["UF_EMPLOYEE"]);
		if(!$el['UF_TASK']){
			if($el["UF_HOSPITAL"]>0){
				$arFields = Array(//АВАНС
						"TITLE" => "Больничный за ".$months[$titlemonth]."'".$titleyear,
						"DESCRIPTION" => "",
						"RESPONSIBLE_ID" => 1,//ОТВЕСТВЕННЫЙ
						"CREATED_BY"=>1,//ПОСТАНОВЩИК
						"AUDITORS"=> array($el["UF_EMPLOYEE"]),//НАБЛЮДАТЕЛИ
						"ACCOMPLICES" => array(5462),//СОИСПОЛНИТЕЛИ
						"TAGS"=> array("ЗП","начислено к выплате"),
						"UF_AUTO_922766608571"=>$el["UF_HOSPITAL"],//СУММА
						"UF_AUTO_494712915809"=>"СБЕР",//СПИСАНО СО СЧЕТА
						"GROUP_ID" => 67
					);
					$obTask = new CTasks;
					$ID = $obTask->Add($arFields);
					$success = ($ID>0);
					if(!$success)
					{
						if($e = $APPLICATION->GetException())
							echo "Error: ".$e->GetString();  
					}
					$countTasks++;
			}
			if($el["UF_HOLIDAY"]>0){
				$arFields = Array(//АВАНС
						"TITLE" => "Отпускные за ".$months[$titlemonth]."'".$titleyear,
						"DESCRIPTION" => "",
						"RESPONSIBLE_ID" => 1,//ОТВЕСТВЕННЫЙ
						"CREATED_BY"=>1,//ПОСТАНОВЩИК
						"AUDITORS"=> array($el["UF_EMPLOYEE"]),//НАБЛЮДАТЕЛИ
						"ACCOMPLICES" => array(5462),//СОИСПОЛНИТЕЛИ
						"TAGS"=> array("ЗП","начислено к выплате"),
						"UF_AUTO_922766608571"=>$el["UF_HOLIDAY"],//СУММА
						"UF_AUTO_494712915809"=>"СБЕР",//СПИСАНО СО СЧЕТА
						"GROUP_ID" => 67
					);
					$obTask = new CTasks;
					$ID = $obTask->Add($arFields);
					$success = ($ID>0);
					if(!$success)
					{
						if($e = $APPLICATION->GetException())
							echo "Error: ".$e->GetString();  
					}
					$countTasks++;
			}
			if($type==283 || $type==224){
				if($el["UF_AVANS"]>0){
					$arFields = Array(//АВАНС
						"TITLE" => "Аванс за ".$months[$titlemonth]."'".$titleyear,
						"DESCRIPTION" => "Аванс:".$el["UF_AVANS"],
						"RESPONSIBLE_ID" => 1,//ОТВЕСТВЕННЫЙ
						"CREATED_BY"=>1,//ПОСТАНОВЩИК
						"AUDITORS"=> array($el["UF_EMPLOYEE"]),//НАБЛЮДАТЕЛИ
						"ACCOMPLICES" => array(5462),//СОИСПОЛНИТЕЛИ
						"TAGS"=> array("ЗП","начислено к выплате"),
						"UF_AUTO_922766608571"=>$el["UF_AVANS"],//СУММА
						"UF_AUTO_494712915809"=>"СБЕР",//СПИСАНО СО СЧЕТА
						"GROUP_ID" => 67
					);
					$obTask = new CTasks;
					$ID = $obTask->Add($arFields);
					$success = ($ID>0);
					if(!$success)
					{
						if($e = $APPLICATION->GetException())
							echo "Error: ".$e->GetString();  
					}
					$countTasks++;
				}
				if($el["UF_PAYOFF"]>0){
					$arFields = Array(//РАСЧЁТ ОФИЦ
						"TITLE" => "ЗП _".$months[$titlemonth]."'".$titleyear,
						"DESCRIPTION" => "Расчет офиц:".$el["UF_PAYOFF"]." Расчет неофиц.:".$el["UF_AWARD"],
						"RESPONSIBLE_ID" => 1,//ОТВЕСТВЕННЫЙ
						"CREATED_BY"=>1,//ПОСТАНОВЩИК
						"AUDITORS"=> array($el["UF_EMPLOYEE"]),//НАБЛЮДАТЕЛИ
						"ACCOMPLICES" => array(5462),//СОИСПОЛНИТЕЛИ
						"TAGS"=> array("ЗП","начислено к выплате"),
						"UF_AUTO_922766608571"=>$el["UF_PAYOFF"]+$el["UF_AWARD"],//СУММА
						"UF_AUTO_494712915809"=>"СБЕР",//СПИСАНО СО СЧЕТА
						"GROUP_ID" => 67
					);
					$obTask = new CTasks;
					$ID = $obTask->Add($arFields);
					$success = ($ID>0);
					if(!$success)
					{
						if($e = $APPLICATION->GetException())
							echo "Error: ".$e->GetString();  
					}
					$countTasks++;
				}
				$countUsers++;
			}else{
				if($el["UF_AVANS"]>0){
					$arFields = Array(//АВАНС
						"TITLE" => "Аванс за ".$months[$titlemonth]."'".$titleyear,
						"DESCRIPTION" => "Аванс:".$el["UF_AVANS"],
						"RESPONSIBLE_ID" => 1,//ОТВЕСТВЕННЫЙ
						"CREATED_BY"=>1,//ПОСТАНОВЩИК
						"AUDITORS"=> array($el["UF_EMPLOYEE"]),//НАБЛЮДАТЕЛИ
						"ACCOMPLICES" => array(5462),//СОИСПОЛНИТЕЛИ
						"TAGS"=> array("ЗП","начислено к выплате"),
						"UF_AUTO_922766608571"=>$el["UF_AVANS"],//СУММА
						"UF_AUTO_494712915809"=>"СБЕР",//СПИСАНО СО СЧЕТА
						"GROUP_ID" => 67
					);
					$obTask = new CTasks;
					$ID = $obTask->Add($arFields);
					$success = ($ID>0);
					if(!$success)
					{
						if($e = $APPLICATION->GetException())
							echo "Error: ".$e->GetString();  
					}
					$countTasks++;
				}
				if($el["UF_PAYOFF"]>0){
					$arFields = Array(//РАСЧЁТ ОФИЦ
						"TITLE" => "ЗП _".$months[$titlemonth]."'".$titleyear,
						"DESCRIPTION" => "Расчет офиц:".$el["UF_PAYOFF"],
						"RESPONSIBLE_ID" => 1,//ОТВЕСТВЕННЫЙ
						"CREATED_BY"=>1,//ПОСТАНОВЩИК
						"AUDITORS"=> array($el["UF_EMPLOYEE"]),//НАБЛЮДАТЕЛИ
						"ACCOMPLICES" => array(5462),//СОИСПОЛНИТЕЛИ
						"TAGS"=> array("ЗП","начислено к выплате"),
						"UF_AUTO_922766608571"=>$el["UF_PAYOFF"],//СУММА
						"UF_AUTO_494712915809"=>"СБЕР",//СПИСАНО СО СЧЕТА
						"GROUP_ID" => 67
					);
					$obTask = new CTasks;
					$ID = $obTask->Add($arFields);
					$success = ($ID>0);
					if(!$success)
					{
						if($e = $APPLICATION->GetException())
							echo "Error: ".$e->GetString();  
					}
					$countTasks++;
				}
				if($el["UF_AWARD"]>0){
					$arFields = Array(//РАСЧЁТ НЕОФИЦ
						"TITLE" => "ЗП _".$months[$titlemonth]."'".$titleyear." (неоф)",
						"DESCRIPTION" => "Расчет неофиц.:".$el["UF_AWARD"],
						"RESPONSIBLE_ID" => 1,//ОТВЕСТВЕННЫЙ
						"CREATED_BY"=>1,//ПОСТАНОВЩИК
						"AUDITORS"=> array($el["UF_EMPLOYEE"]),//НАБЛЮДАТЕЛИ
						"ACCOMPLICES" => array(5462),//СОИСПОЛНИТЕЛИ
						"TAGS"=> array("ЗП","начислено к выплате","неоф"),
						"UF_AUTO_922766608571"=>$el["UF_AWARD"],//СУММА
						"UF_AUTO_494712915809"=>"СБЕР",//СПИСАНО СО СЧЕТА
						"GROUP_ID" => 67
					);
					$obTask = new CTasks;
					$ID = $obTask->Add($arFields);
					$success = ($ID>0);
					if(!$success)
					{
						if($e = $APPLICATION->GetException())
							echo "Error: ".$e->GetString();  
					}
					$typeofwork = getTypeOfWork($el["UF_EMPLOYEE"]);
					if($typeofwork == 6625){
						$res = CTasks::GetList(
							Array("TITLE" => "ASC"), 
							Array(
							">=CREATED_DATE" => $date1,
							"<=CREATED_DATE" => $date2,
							"AUDITORS"=> array($el["UF_EMPLOYEE"]),//НАБЛЮДАТЕЛИ
							"GROUP_ID" => 67
							)
						);
						while ($arTask = $res->GetNext()){
							$arFields = Array(
								"PARENT_ID" => $ID,
							);
							$obTask = new CTasks;
							$success = $obTask->Update($arTask['ID'], $arFields);
							if(!$success)
							{
								if($e = $APPLICATION->GetException())
									echo "Error: ".$e->GetString();
							}
						}
					}
					$idForUpdate = $el["ID"];//СТАВИМ ФЛАГ ЧТО ЗАДАЧА СОЗДАНА
					$r = $entity_data_class::update($idForUpdate,array(
						"UF_TASK"=>true,
				   ));
				$countTasks++;
				}
				$countUsers++;
			}
		}else{$countFailed++;}

	}
	echo json_encode("Добавлено ".$countTasks." задач для ".$countUsers." сотрудников."." Для ".$countFailed." сотрудников задачи уже были созданы.", JSON_UNESCAPED_UNICODE);
}

//СОХРАНЕНИЕ РЕЗУЛЬТАТОВ МЕСЯЦА
function upload($str_json){
	$month = json_decode($str_json)->month;
	$year = json_decode($str_json)->year;
	$completed = json_decode($str_json)->completed;
	$clientTime = json_decode($str_json)->clientTime;
	$overdue = json_decode($str_json)->overdue;
	$all = json_decode($str_json)->all;
	$projectTime = json_decode($str_json)->projectTime;

	$highblock_id = 7;
	$hl_block = HLBT::getById($highblock_id)->fetch();
	$entity = HLBT::compileEntity($hl_block);
	$entity_data_class = $entity->getDataClass();

	
	//ЗАПРОС В ХАЙЛОАД
	$rs_data = $entity_data_class::getList(array(
	   'select' => array('UF_MONTH','UF_YEAR'),
		'filter' => array('UF_MONTH'=>$month,'UF_YEAR'=>$year)
	));
	$data = $rs_data->fetch();
	if($data){
		echo json_encode("Данные за этот меясц уже выгружались в БД", JSON_UNESCAPED_UNICODE);
	}else{
		$result = $entity_data_class::add(array(
			"UF_YEAR"=> $year,
			"UF_MONTH"=> $month,
			"UF_PROJECT_MINUTES"=> $clientTime,
			"UF_DEPLOY_MINUTES"=> $projectTime,
			"UF_COMPLETED"=>$completed,
			"UF_OVERDUE" => $overdue,
			"UF_ALL" => $all
	   ));
		echo json_encode("Данные выгружены", JSON_UNESCAPED_UNICODE);
	}
}
	//Тип 
function getTypeOfWork($id){
	$rsUser = CUser::GetByID($id);
	$arUser = $rsUser->Fetch();
	return $arUser['UF_TYPE_OF_WORK'];
}
