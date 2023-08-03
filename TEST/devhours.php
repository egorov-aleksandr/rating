<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Отладка часов разработки");
?><?
$month = $_GET['month'];
$year = $_GET['year'];
echo "месяц: ".$month;
echo "<br>";
echo "год: ".$year;
echo "<br><br>";
$lastday =  cal_days_in_month(CAL_GREGORIAN, $month, $year);//последний день месяца
$date1 = '1.'.$month.'.'.$year.' 00:00:00';//Дата1 для селекта в функциях, предыдущий от выбранного месяц
$date2 = $lastday.'.'.$month.'.'.$year.' 00:00:00';//Дата2 для селекта в функциях, предыдущий от выбранного месяц

$usersID=[];
$arrayTime=[];
//ЧАСЫ ПО ПРОЕКТАМ
		//список задач группы
		$res = CTasks::GetList(
				Array("TITLE" => "ASC"), 
				Array(
				"GROUP_ID" => 9
				)
			);
			$projectTasks = array();
			while ($arTask = $res->GetNext())
			{
					$task=array("ID"=>$arTask['ID'], "TITLE"=>$arTask['TITLE']);
					$projectTasks[] = $task;
			}
		//список записей времени в интервале дат

		//если айди задачи из группы совпадает с айди в записи времени
			$projectTime = 0;

				foreach($projectTasks as $task){
					$res = CTaskElapsedTime::GetList(
						Array(), 
						Array(">=CREATED_DATE" => $date1,
								"<=CREATED_DATE" => $date2,
								'TASK_ID'=>$task["ID"])
					);
					while ($arElapsed = $res->Fetch())
					{
						if(!in_array($arElapsed["USER_ID"],$usersID)){
							$usersID[]=$arElapsed["USER_ID"];
							$minutes=$arElapsed["MINUTES"];
							$r = CTaskElapsedTime::GetList(
								Array(), 
								Array(">=CREATED_DATE" => $date1,
										"<=CREATED_DATE" => $date2,
										'USER_ID'=>$arElapsed["USER_ID"])
							);
							while ($Elapsed = $r->Fetch())
							{
								$minutes+=$Elapsed["MINUTES"];
							}
							$arrayTime[]=array("ID"=>$arElapsed["USER_ID"],"MINUTES"=>$minutes);
						}
					}
				}
				foreach($arrayTime as $time){
					$rsUser = CUser::GetByID($time["ID"]);
					$arUser = $rsUser->Fetch();
					$hours=round($time["MINUTES"]/60);
					$minutes=$time["MINUTES"]%60;
					echo $arUser["NAME"]." ".$arUser["LAST_NAME"].":  ";
					if($hours<10){echo "0";}
					echo $hours;
					echo ":";
					if($minutes<10){echo "0";}
					echo $minutes;
					echo "<br><br>";
				}
?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>