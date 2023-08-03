 <?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("ОТЛАДКА");
?>

<?
$month = $_GET['month'];
$year = $_GET['year'];
$id =  $_GET['id'];
$type = $_GET['type'];
$lastday =  cal_days_in_month(CAL_GREGORIAN, $month, $year);//последний день месяца
$date1 = '1.'.$month.'.'.$year.' 00:00:00';//Дата1 для селекта в функциях
$date2 = $lastday.'.'.$month.'.'.$year.' 00:00:00';//Дата2 для селекта в функциях
echo "месяц: ".$month;
echo "<br>";
echo "год: ".$year;
echo "<br>";
$rsUser = CUser::GetByID($id);
$arUser = $rsUser->Fetch();
echo $arUser["NAME"]." ".$arUser["LAST_NAME"];
echo "<br>";
switch($type){
	case "BOSS":
		boss($date1,$date2);
		break;
	case "TP":
		TP($id, $date1,$date2);
		break;
	case "HEADTP":
		headTP($id, $date1,$date2);
		break;
	case "HEADOP":
		rop($id, $date1,$date2);
		break;
	case "DEVELOPER":
		developer($id, $date1,$date2);
		break;
	case "PMANAGER":
		projectmanager($id, $date1,$date2);
		break;
	case "VERSTKA":
		verstka($id, $date1,$date2);
		break;
	case "INFO":
		info($id, $date1,$date2);
		break;
}
?>
<?
function getOklad($id){//Получение оклада компетенции
	$rsUser = CUser::GetByID($id);
	$arUser = $rsUser->Fetch();

	//достаем компетенцию и задаем часовую ставку
	$comp = $arUser['UF_PUBLIC_COMPETENCE'];
	switch($comp){
		case 6440:
			$result=21600;
			break;
		case 6441:
			$result=25200;
			break;
		case 6442:
			$result=28800;
			break;
		case 6443:
			$result=32400;
			break;
		case 6444:
			$result=36000;
			break;
	}
	return $result;
}

function isHours($id, $date1, $date2){
	$res = CTaskElapsedTime::GetList(
        Array(), 
        Array(">=CREATED_DATE" => $date1,
   				"<=CREATED_DATE" => $date2,
				"USER_ID"=> $id)
    );
    if($res->Fetch())
    {
        return true;
	}else{
		return false;
	}
}
function developer($id, $date1, $date2){
	$K=250;//Ставка часа, зависит от компетенции
	$N=0.5;//Условие от задач сотрудника
	$H = 1;// Часы
	$P=1; //Положительные оценки
	$T=1.1;//Отсутствие просроченных задач
	$C=0;//Наставничество

	$rsUser = CUser::GetByID($id);
	$arUser = $rsUser->Fetch();

	//достаем компетенцию и задаем часовую ставку
	$comp = $arUser['UF_PUBLIC_COMPETENCE'];
	switch($comp){
		case 6440:
			$K=250;
			break;
		case 6441:
			$K=350;
			break;
		case 6442:
			$K=450;
			break;
		case 6443:
			$K=550;
			break;
		case 6444:
			$K=650;
			break;
	}
	echo "Ставка часа К = ".$K;
	echo "<br>";
	//Достаем и считаем количество завершенных задач
	$completed = 0;
	echo "Завершенные задачи в статусе 'Ответственный':";
	echo "<br>";
	$res = CTasks::GetList(
        Array("TITLE" => "ASC"), 
        Array(
		"RESPONSIBLE_ID" => $id,
		">=CLOSED_DATE" => $date1,
   		"<=CLOSED_DATE" => $date2,
		'REAL_STATUS' => CTasks::STATE_COMPLETED
        )
    );
    while ($arTask = $res->GetNext())
    {
		$time=substr($arTask["CLOSED_DATE"], 0, strpos($arTask["CLOSED_DATE"], ' '));
		if($arTask['MARK']=="P"){$P=1.1;}
        $completed = $completed + 1;
		echo $time." - ".'<a target="_blank" href="https://b24.opti.ooo/company/personal/user/1/tasks/task/view/'.$arTask['ID'].'/">'.$arTask['TITLE'].'</a>';
		echo "<br>";
    }
	echo "<br>";
	echo "Завершенные задачи в статусе 'Соисполнитель':";
	echo "<br>";
	$res = CTasks::GetList(
        Array("TITLE" => "ASC"), 
        Array(
		"ACCOMPLICE" => $id,
		">=CLOSED_DATE" => $date1,
   		"<=CLOSED_DATE" => $date2,
		'REAL_STATUS' => CTasks::STATE_COMPLETED
        )
    );
    while ($arTask = $res->GetNext())
    {
		$time=substr($arTask["CLOSED_DATE"], 0, strpos($arTask["CLOSED_DATE"], ' '));
		echo $time." - ".'<a target="_blank" href="https://b24.opti.ooo/company/personal/user/1/tasks/task/view/'.$arTask['ID'].'/">'.$arTask['TITLE'].'</a>';
		echo "<br>";
        $completed = $completed + 1;
    }
	//Считаем N в зависимости от колва завершенных задач
	if($completed<5){$N=0.5;}
	elseif($completed<10){$N=0.6;}
	elseif($completed<15){$N=0.7;}
	elseif($completed<20){$N=0.8;}
	elseif($completed<30){$N=0.9;}
	else{$N=1;}
	echo "<br>";
	echo "Кол-во завершенных задач = ".$completed;
	echo "<br>";
	echo "Коэффициент N = ".$N;
	echo "<br>";

	//Достаем и считаем минуты
	$res = CTaskElapsedTime::GetList(
        Array(), 
        Array(">=CREATED_DATE" => $date1,
   				"<=CREATED_DATE" => $date2,
				"USER_ID"=> $id)
    );
	$minutes = 0;
    while ($arElapsed = $res->Fetch())
    {
        $minutes += $arElapsed["MINUTES"];
    }
	$H=round($minutes/60);//переводим в часы
	echo "Колв-во часов H = ".$H;
	echo "<br>";
	$res = CTasks::GetList(
        Array("TITLE" => "ASC"), 
        Array(
		"RESPONSIBLE_ID" => $id,
		'REAL_STATUS' => array(CTasks::STATE_NEW,CTasks::STATE_IN_PROGRESS),
		'STATUS'=>-1
        )
    );
    $arTask = $res->GetNext();
	if($arTask){$T=1;}
	echo "Коэффициент T = ".$T;
	echo "<br>";
	$sallary = $K*$H*$N*$P*$T+$C;
	echo "ЗП = ".$sallary;
	echo "<br>";
	return $sallary;
}

function verstka($id, $date1, $date2){
	$K=250;//Ставка часа, зависит от компетенции
	$N=1;//Условие от задач сотрудника
	$H = 1;// Часы
	$P=1; //Положительные оценки
	$T=1.1;//Отсутствие просроченных задач
	$C=0;//Наставничество

	$rsUser = CUser::GetByID($id);
	$arUser = $rsUser->Fetch();

	//достаем компетенцию и задаем часовую ставку
	$comp = $arUser['UF_PUBLIC_COMPETENCE'];
	switch($comp){
		case 6440:
			$K=250;
			break;
		case 6441:
			$K=350;
			break;
		case 6442:
			$K=450;
			break;
		case 6443:
			$K=550;
			break;
		case 6444:
			$K=650;
			break;
	}
	$K=$K/2;
	echo "Ставка часа К = ".$K;
	echo "<br>";
	//Достаем и считаем количество завершенных задач
	$completed = 0;
	echo "Завершенные задачи в статусе 'Ответственный':";
	echo "<br>";
	$res = CTasks::GetList(
        Array("TITLE" => "ASC"), 
        Array(
		"RESPONSIBLE_ID" => $id,
		">=CLOSED_DATE" => $date1,
   		"<=CLOSED_DATE" => $date2,
		'REAL_STATUS' => CTasks::STATE_COMPLETED
        )
    );
    while ($arTask = $res->GetNext())
    {
		$time=substr($arTask["CLOSED_DATE"], 0, strpos($arTask["CLOSED_DATE"], ' '));
		if($arTask['MARK']=="P"){$P=1.1;}
        $completed = $completed + 1;
		echo $time." - ".'<a target="_blank" href="https://b24.opti.ooo/company/personal/user/1/tasks/task/view/'.$arTask['ID'].'/">'.$arTask['TITLE'].'</a>';
		echo "<br>";
    }
	echo "<br>";
	echo "Завершенные задачи в статусе 'Соисполнитель':";
	echo "<br>";
	$res = CTasks::GetList(
        Array("TITLE" => "ASC"), 
        Array(
		"ACCOMPLICE" => $id,
		">=CLOSED_DATE" => $date1,
   		"<=CLOSED_DATE" => $date2,
		'REAL_STATUS' => CTasks::STATE_COMPLETED
        )
    );
    while ($arTask = $res->GetNext())
    {
		$time=substr($arTask["CLOSED_DATE"], 0, strpos($arTask["CLOSED_DATE"], ' '));
		echo $time." - ".'<a target="_blank" href="https://b24.opti.ooo/company/personal/user/1/tasks/task/view/'.$arTask['ID'].'/">'.$arTask['TITLE'].'</a>';
		echo "<br>";
        $completed = $completed + 1;
    }
	//Считаем N в зависимости от колва завершенных задач
	/*if($completed<5){$N=0.5;}
	elseif($completed<10){$N=0.6;}
	elseif($completed<15){$N=0.7;}
	elseif($completed<20){$N=0.8;}
	elseif($completed<30){$N=0.9;}
	else{$N=1;}*/
	echo "<br>";
	echo "Кол-во завершенных задач = ".$completed;
	echo "<br>";
	//echo "Коэффициент N = ".$N;
	//echo "<br>";

	//Достаем и считаем минуты
	$res = CTaskElapsedTime::GetList(
        Array(), 
        Array(">=CREATED_DATE" => $date1,
   				"<=CREATED_DATE" => $date2,
				"USER_ID"=> $id)
    );
	$minutes = 0;
    while ($arElapsed = $res->Fetch())
    {
        $minutes += $arElapsed["MINUTES"];
    }
	$H=round($minutes/60);//переводим в часы
	echo "Колв-во часов H = ".$H;
	echo "<br>";
	$res = CTasks::GetList(
        Array("TITLE" => "ASC"), 
        Array(
		"RESPONSIBLE_ID" => $id,
		'REAL_STATUS' => array(CTasks::STATE_NEW,CTasks::STATE_IN_PROGRESS),
		'STATUS'=>-1
        )
    );
    $arTask = $res->GetNext();
	if($arTask){$T=1;}
	echo "Коэффициент T = ".$T;
	echo "<br>";
	$sallary = $K*$H*$N*$P*$T+$C;
	echo "ЗП = ".$sallary;
	echo "<br>";
	return $sallary;
}

//Менеджеры проектов
function projectmanager($id, $date1, $date2){
	$O=getOklad($id);//Оклад
	$D=0;//колво Колво закрытых сделок
	$Ds=0;//Сумма закрытых сделок
	$Dk=0.9; //коэффициент от количества активных сделок
	$N = 0.5;// условие от закрытых задач
	$Hp=1; //Часы
	$P= 1;//Положительные оценки по задачам
	$T=1;//Просроченные задачи

	$rsUser = CUser::GetByID($id);
	$arUser = $rsUser->Fetch();

	echo "Завершенные сделки:";
	echo "<br>";

	//Достаем и считаем количество завершенных сделок
	$res = CCrmDeal::GetList(
        Array('DATE_CREATE' => 'DESC'), 
		Array("ASSIGNED_BY_ID" => $id, "STAGE_ID" => 'C14:WON', "CLOSED" => 'Y',
			">=CLOSEDATE" => $date1,
   				"<=CLOSEDATE" => $date2,)
    );
	while($arDeal=$res->GetNext()){
		$D++;
		$time=substr($arDeal["CLOSEDATE"], 0, strpos($arDeal["CLOSEDATE"], ' '));
		$Ds+=$arDeal['OPPORTUNITY'];
		echo $time." - ".'<a target="_blank" href="https://b24.opti.ooo/crm/deal/details/'.$arDeal['ID'].'/">'.$arDeal['TITLE'].'</a>';
		echo "<br>";
 	}
	echo "<br>";
	echo "Кол-во завершенных сделок: ".$D;
	echo "<br>";
	echo "Сумма завершенных сделок: ".$Ds;
	echo "<br>";
	echo "<br>";

	//Достаем и считаем количество активных сделок
	echo "Активные сделки:";
	echo "<br>";
	$workdeals=0;
	$res = CCrmDeal::GetList(
        Array('DATE_CREATE' => 'DESC'), 
		Array("ASSIGNED_BY_ID" => $id, "CLOSED" => 'N',
			">=DATE_CREATE" => $date1,
   				"<=DATE_CREATE" => $date2,)
    );
	while($arDeal=$res->GetNext()){
		$workdeals++;
		$time=substr($arDeal["CLOSEDATE"], 0, strpos($arDeal["CLOSEDATE"], ' '));
		echo $time." - ".'<a target="_blank" href="https://b24.opti.ooo/crm/deal/details/'.$arDeal['ID'].'/">'.$arDeal['TITLE'].'</a>';
		echo "<br>";
 	}
	if($workdeals>10){$Dk=1;}else{$Dk=0.9;}
	echo "<br>";
	echo "Кол-во активных сделок: ".$workdeals;
	echo "<br>";
	echo "<br>";

	//Достаем и считаем количество завершенных задач
	echo "Завершенные задачи в статусе 'Ответственный':";
	echo "<br>";
	$completed = 0;
	$res = CTasks::GetList(
        Array("TITLE" => "ASC"), 
        Array(
		"RESPONSIBLE_ID" => $id,
		">=CLOSED_DATE" => $date1,
   		"<=CLOSED_DATE" => $date2,
		'REAL_STATUS' => CTasks::STATE_COMPLETED
        )
    );
    while ($arTask = $res->GetNext())
    {
		$time=substr($arTask["CLOSED_DATE"], 0, strpos($arTask["CLOSED_DATE"], ' '));
		if($arTask['MARK']=="P"){$P=1.1;}
        $completed = $completed + 1;
		echo $time." - ".'<a target="_blank" href="https://b24.opti.ooo/company/personal/user/1/tasks/task/view/'.$arTask['ID'].'/">'.$arTask['TITLE'].'</a>';
		echo "<br>";
    }
	echo "<br>";
	echo "Завершенные задачи в статусе 'Соисполнитель':";
	echo "<br>";
	$res = CTasks::GetList(
        Array("TITLE" => "ASC"), 
        Array(
		"ACCOMPLICE" => $id,
		">=CLOSED_DATE" => $date1,
   		"<=CLOSED_DATE" => $date2,
		'REAL_STATUS' => CTasks::STATE_COMPLETED
        )
    );
    while ($arTask = $res->GetNext())
    {
		$time=substr($arTask["CLOSED_DATE"], 0, strpos($arTask["CLOSED_DATE"], ' '));
        $completed = $completed + 1;
		echo $time." - ".'<a target="_blank" href="https://b24.opti.ooo/company/personal/user/1/tasks/task/view/'.$arTask['ID'].'/">'.$arTask['TITLE'].'</a>';
		echo "<br>";
    }
	//Считаем N в зависимости от колва завершенных задач
	if($completed<5){$N=0.5;}
	elseif($completed<10){$N=0.6;}
	elseif($completed<15){$N=0.7;}
	elseif($completed<20){$N=0.8;}
	elseif($completed<30){$N=0.9;}
	else{$N=1;}

	echo "<br>";
	echo "Кол-во завершенных задач = ".$completed;
	echo "<br>";
	echo "Коэффициент N = ".$N;
	echo "<br>";
	//Достаем и считаем минуты
	$res = CTaskElapsedTime::GetList(
        Array(), 
        Array(">=CREATED_DATE" => $date1,
   				"<=CREATED_DATE" => $date2,
				"USER_ID"=> $id)
    );
	$minutes = 0;
    while ($arElapsed = $res->Fetch())
    {
        $minutes += $arElapsed["MINUTES"];
    }
	$hours=round($minutes/60);//переводим в часы
	if($hours<50){$Hp=0.8;}
	echo "Колв-во часов H = ".$hours;
	echo "<br>";
	//Просроченные задачи
	$res = CTasks::GetList(
        Array("TITLE" => "ASC"), 
        Array(
		"RESPONSIBLE_ID" => $id,
		'REAL_STATUS' => array(CTasks::STATE_NEW,CTasks::STATE_IN_PROGRESS),
		'STATUS'=>-1
        )
    );
    $arTask = $res->GetNext();
	if($arTask){$T=0.9;}
	echo "Коэффициент T = ".$T;
	echo "<br>";

	$sallary=($O+($Ds*0.1))*($Dk*$N*$Hp*$P*$T);
	echo "ЗП: ".$sallary;
	return $sallary;
}

function TP($id, $date1,$date2){
	$deals=0;//Колво сделок
	$dialogs=array();//Массив всех айди диалогов
	$Np=0.8;//коэфициент сделок
	$Nd=0;//Колво уникальных диалогов
	$fuck = 1;//Коэфициент отсутсвия айди диалога
	$O=getOklad($id)/2;//Оклад

	echo "Оклад: ".$O;
	echo "<br>";
	echo "<br>";
	echo "Сделки: ";
	echo "<br>";
	//Получаем сделки сотрудника
	$res = CCrmDeal::GetList(
        Array('DATE_CREATE' => 'DESC'), 
		Array("ASSIGNED_BY_ID" => $id, 
				">=DATE_CREATE" => $date1,
   				"<=DATE_CREATE" => $date2,)
    );
	while($arDeal=$res->GetNext()){
		$deals++;//колво сделок
		$time=substr($arDeal["CLOSEDATE"], 0, strpos($arDeal["CLOSEDATE"], ' '));
		echo $time.'<a target="_blank" href="https://b24.opti.ooo/crm/deal/details/'.$arDeal['ID'].'/">'.$arDeal['TITLE'].'</a>';
		echo "<br>";
		//если айди диалога не валидный иил не указан, меняем коэф, в обратном случае записываем в массив айди диаога
		if($arDeal["UF_CRM_1678442726507"]==1 || $arDeal["UF_CRM_1678442726507"]==2 || $arDeal["UF_CRM_1678442726507"]==3 || $arDeal["UF_CRM_1678442726507"]>999){
			$dialogs[]=$arDeal["UF_CRM_1678442726507"];
		}else{$fuck = 0.6;}
		echo 'ID диалога: '.$arDeal["UF_CRM_1678442726507"];
		echo "<br>";
 	}
	echo "<br>";
	//если колво сделок меньше 30, уменьшаем коэффициент
	if($deals < 30){$Np=0.8;}else{$Np=1;}
	echo "Кол-во сделок: ".$deals;
	echo "<br>";
	//echo "Коэффициент Np: ".$Np;
	//echo "<br>";

	$K=150;//Ставка часа, зависит от компетенции

	$comp = $arUser['UF_PUBLIC_COMPETENCE'];
	switch($comp){
		case 6440:
			$K=150;
			break;
		case 6441:
			$K=200;
			break;
		case 6442:
			$K=250;
			break;
		case 6443:
			$K=300;
			break;
		case 6444:
			$K=400;
			break;
	}
	echo "Ставка в час: ".$K;
	echo "<br>";
	$tasksId=[];
	$res = CTasks::GetList(
        Array("TITLE" => "ASC"), 
        Array(
			"GROUP_ID"=> 9
        )
    );
    while ($arTask = $res->GetNext())
    {
        $tasksId[] = $arTask["ID"];
    }

	$res = CTaskElapsedTime::GetList(
        Array(), 
        Array(">=CREATED_DATE" => $date1,
   				"<=CREATED_DATE" => $date2,
				"TASK_ID"=>$tasksId,
				"USER_ID"=> $id)
    );
	$minutes = 0;
    while ($arElapsed = $res->Fetch())
    {
        $minutes += $arElapsed["MINUTES"];
    }
	$H=round($minutes/60);//переводим в часы
	echo "Часы по задачам: ".$H;
	echo "<br>";
	$Nd=count(array_unique($dialogs));//считаем колво уникальных айди диалогов
	echo "Кол-во уникальных ID диалогов: ".$Nd;
	echo "<br>";
	if($Nd==0 || $deals==0){
		$sallary = $O*$fuck+$K*$H;
	}else{
		$sallary = $O*($Nd/$deals)*$fuck+$deals*100+$K*$H;//Считаем ЗП
	}
	echo "ЗП: ".round($sallary,2);
	echo "<br>";
	return round($sallary,2);
}

function headTP($id, $date1, $date2){
	$O=getOklad($id);//Оклад
	$completed=0;//Колво закрытых сделок
	$workdeals=0;//Колво незавершенных сделок
	$summ=0;//Сумма закрытых сделок
	$B=0;//Процент закрытых сделок
	$Q=1;//Отношение закрытых к незавершенным сделкам

	echo "Оклад: ".$O;
	echo "<br>";
	echo "<br>";
	echo " Закрытые сделки: ";
	echo "<br>";

	//Считаем колво и сумму закрытых сделок
	$res = CCrmDeal::GetList(
			Array('DATE_CREATE' => 'DESC'), 
			Array(
				'TYPE_ID' => "SERVICE",
				'STAGE_ID'=>"C22:LOSE",
					">=DATE_CREATE" => $date1,
					"<=DATE_CREATE" => $date2,)
		);
		while($arDeal=$res->GetNext()){
			//if(in_array(5496,$arDeal["UF_CRM_608139A3D90BE"])){
				$time=substr($arDeal["CLOSEDATE"], 0, strpos($arDeal["CLOSEDATE"], ' '));
				$completed++;
				$summ+=$arDeal['OPPORTUNITY'];
				echo $time." - ".'<a target="_blank" href="https://b24.opti.ooo/crm/deal/details/'.$arDeal['ID'].'/">'.$arDeal['TITLE'].'</a>';
				echo "<br>";
			//}
		}
	echo "<br>";
	echo " Незавершенные сделки: ";
	echo "<br>";
	//Считаем колво незавершенных сделок
	$res = CCrmDeal::GetList(
			Array('DATE_CREATE' => 'DESC'), 
			Array("ASSIGNED_BY_ID" => $mainsection["UF_HEAD"], 
					"CLOSED" => 'N',
					">=DATE_CREATE" => $date1,
					"<=DATE_CREATE" => $date2,)
		);
		while($arDeal=$res->GetNext()){
			if(in_array(5496,$arDeal["UF_CRM_608139A3D90BE"])){
				$workdeals++;
				echo '<a target="_blank" href="https://b24.opti.ooo/crm/deal/details/'.$arDeal['ID'].'/">'.$arDeal['TITLE'].'</a>';
				echo "<br>";
			}
		}
	echo "<br>";
	echo "Кол-во закрытых сделок: ".$completed;
	echo "<br>";
	echo "Сумма закрытых сделок: ".$summ;
	echo "<br>";
	echo "Кол-во незавершенных сделок: ".$workdeals;
	echo "<br>";
	//Считаем отношение закрытых к незавершенным
	if($workdeals==0 || $completed==0){
		$Q=1;
	}else{
		$K=$workdeals/$completed;
		if($K>0.2){$Q=0.6;}elseif($K>0.15){$Q=0.7;}elseif($K>0.1){$Q=0.8;}elseif($K>0.05){$Q=0.9;}else{$Q=1;}
	}
	echo "Отношение закрытых к незавершенным: ".$K;
	echo "<br>";
	echo "Коэффициент Q: ".$Q;
	echo "<br>";
	//Считаем процент от закрытых сделок
	if($summ==0){
		$B=0;
	}else{
		$B=$summ/10;
	}
	echo "10% от суммы закрытых сделок: ".$B;
	echo "<br><br>";
	echo "ЗП = (Оклад + Колво закрытых сделок * 50) * Коэф соотношения незавершенных к закрытым + 10% от закрытых сделок";
	echo "<br>";
	echo "ЗП = (".$O." + ".$completed." * 50)*".$Q." + ".$B;
	echo "<br>";
	$sallary= ($O + $completed*50)*$Q+$B; 
	echo "ЗП: ".$sallary;
	echo "<br>";
	return $sallary;
}

function rop($id,$date1, $date2){
	$O=40000;//Оклад
	$M=0.02;//маржа от всех сделок
	$Mi=0.05;//Маржа от своих сделок
	$l1=0;//Колво незавершенных сделок
	$l2=0;//Сумма закрытых сделок
	$summO=0;//Процент закрытых сделок
	$deals=0;
	$dealsO=0;
	echo "Оклад: ".$O;
	echo "<br>";
	echo "Все закрытые сделки";
	echo "<br>";
	//Считаем колво и сумму всех закрытых сделок
	$res = CCrmDeal::GetList(
        Array('DATE_CREATE' => 'DESC'), 
		Array("STAGE_ID" => array('C14:WON', 'C1:WON'), 'CLOSED'=> 'Y',
			">=CLOSEDATE" => $date1,
   				"<=CLOSEDATE" => $date2,)
    );
	while($arDeal=$res->GetNext()){
		$summO+=$arDeal['OPPORTUNITY'];
		$time=substr($arDeal["CLOSEDATE"], 0, strpos($arDeal["CLOSEDATE"], ' '));
		echo $time." - ".'<a target="_blank" href="https://b24.opti.ooo/crm/deal/details/'.$arDeal['ID'].'/">'.$arDeal['TITLE'].'</a>';
		echo "<br>";
		echo $arDeal['OPPORTUNITY'];
		$dealsO++;
		echo "<br>";
 	}
	echo "<br>";
	echo "Свои закрытые сделки";
	echo "<br>";
	//Считаем колво и сумму своих закрытых сделок
	$res = CCrmDeal::GetList(
        Array('DATE_CREATE' => 'DESC'), 
		Array("STAGE_ID" => array('C14:WON', 'C1:WON'), 'CLOSED'=> 'Y',
			"ASSIGNED_BY_ID" => $id,
			">=CLOSEDATE" => $date1,
   				"<=CLOSEDATE" => $date2,)
    );
	while($arDeal=$res->GetNext()){
		$time=substr($arDeal["CLOSEDATE"], 0, strpos($arDeal["CLOSEDATE"], ' '));
		$summ+=$arDeal['OPPORTUNITY'];
		echo $time." - ".'<a target="_blank" href="https://b24.opti.ooo/crm/deal/details/'.$arDeal['ID'].'/">'.$arDeal['TITLE'].'</a>';
		echo "<br>";
		echo $arDeal['OPPORTUNITY'];
		$deals++;
		echo "<br>";
 	}

	echo "<br>";
	echo "Кол-во всех завершенных сделок = ".$dealsO;
	echo "<br>";
	echo "Сумма всех завершенных сделок = ".$summO;
	echo "<br>";
	echo "<br>";
	echo "Кол-во своих завершенных сделок = ".$deals;
	echo "<br>";
	echo "Сумма своих завершенных сделок = ".$summ;
	echo "<br>";
	if($summ>=1000000){$l1=20000;}
	if($summ>=1500000){$l2=20000;}

	echo "Коэффициент l1 = ".$l1;
	echo "<br>";
	echo "Коэффициент l2 = ".$l2;
	echo "<br>";
	$sallary= $O + $l1+$l2+$summO*$M+$summ*$Mi;
	echo "ЗП: ".$sallary;
	return $sallary;
}

function boss($date1, $date2){
	$O=40000;//Оклад
	$M=0.03;//маржа
	$l1=0;//Колво незавершенных сделок
	$l2=0;//Сумма закрытых сделок
	$summ=0;//Процент закрытых сделок
	$deals=0;

	echo "Оклад: ".$O;
	echo "<br>";
	//Считаем колво и сумму закрытых сделок
	echo "Закрытые сделки";
	echo "<br>";
	$res = CCrmDeal::GetList(
        Array('DATE_CREATE' => 'DESC'), 
		Array("STAGE_ID" => array('C14:WON', 'C1:WON'), 'CLOSED'=> 'Y',
			">=CLOSEDATE" => $date1,
   				"<=CLOSEDATE" => $date2,)
    );
	while($arDeal=$res->GetNext()){
		$time=substr($arDeal["CLOSEDATE"], 0, strpos($arDeal["CLOSEDATE"], ' '));
		$summ+=$arDeal['OPPORTUNITY'];
		$deals++;
		echo $time." - ".'<a target="_blank" href="https://b24.opti.ooo/crm/deal/details/'.$arDeal['ID'].'/">'.$arDeal['TITLE'].'</a>';
		echo "<br>";
		echo $arDeal['OPPORTUNITY'];
		echo "<br>";
 	}
	echo "<br>";
	echo "Кол-во завершенных сделок = ".$deals;
	echo "<br>";
	echo "Сумма завершенных сделок = ".$summ;
	echo "<br>";
	if($summ>=1000000){$l1=20000;}
	if($summ>=1500000){$l2=20000;}

	echo "Коэффициент l1 = ".$l1;
	echo "<br>";
	echo "Коэффициент l2 = ".$l2;
	echo "<br>";
	$sallary= $O + $l1+$l2+$summ*$M;
	echo "ЗП: ".$sallary;
	return $sallary;
}

function info($id, $date1, $date2){
	$D=0;
	$Ds=0;
	$workdeals=0;
	$rsUser = CUser::GetByID($id);
	$arUser = $rsUser->Fetch();

	echo "Завершенные сделки:";
	echo "<br>";

	//Достаем и считаем количество завершенных сделок
	$res = CCrmDeal::GetList(
        Array('DATE_CREATE' => 'DESC'), 
		Array("ASSIGNED_BY_ID" => $id, "STAGE_ID" => 'C14:WON', "CLOSED" => 'Y',
			">=CLOSEDATE" => $date1,
   				"<=CLOSEDATE" => $date2,)
    );
	while($arDeal=$res->GetNext()){
		$D++;
		$Ds+=$arDeal['OPPORTUNITY'];
		$time=substr($arDeal["CLOSEDATE"], 0, strpos($arDeal["CLOSEDATE"], ' '));
		echo $time." - ".'<a target="_blank" href="https://b24.opti.ooo/crm/deal/details/'.$arDeal['ID'].'/">'.$arDeal['TITLE'].'</a>';
		echo "<br>";
 	}
	echo "<br>";
	echo "Кол-во завершенных сделок: ".$D;
	echo "<br>";
	echo "Сумма завершенных сделок: ".$Ds;
	echo "<br>";
	echo "<br>";

	//Достаем и считаем количество активных сделок
	echo "Активные сделки:";
	echo "<br>";
	$workdeals=0;
	$res = CCrmDeal::GetList(
        Array('DATE_CREATE' => 'DESC'), 
		Array("ASSIGNED_BY_ID" => $id, "CLOSED" => 'N',
			">=DATE_CREATE" => $date1,
   				"<=DATE_CREATE" => $date2,)
    );
	while($arDeal=$res->GetNext()){
		$workdeals++;
		echo '<a target="_blank" href="https://b24.opti.ooo/crm/deal/details/'.$arDeal['ID'].'/">'.$arDeal['TITLE'].'</a>';
		echo "<br>";
 	}
	echo "<br>";
	echo "Кол-во активных сделок: ".$workdeals;
	echo "<br>";
	echo "<br>";

	//Достаем и считаем количество завершенных задач
	echo "Завершенные задачи в статусе 'Ответственный':";
	echo "<br>";
	$completed = 0;
	$res = CTasks::GetList(
        Array("TITLE" => "ASC"), 
        Array(
		"RESPONSIBLE_ID" => $id,
		">=CLOSED_DATE" => $date1,
   		"<=CLOSED_DATE" => $date2,
		'REAL_STATUS' => CTasks::STATE_COMPLETED
        )
    );
    while ($arTask = $res->GetNext())
    {
		$time=substr($arTask["CLOSED_DATE"], 0, strpos($arTask["CLOSED_DATE"], ' '));
        $completed = $completed + 1;
		echo $time." - ".'<a target="_blank" href="https://b24.opti.ooo/company/personal/user/1/tasks/task/view/'.$arTask['ID'].'/">'.$arTask['TITLE'].'</a>';
		echo "<br>";
    }
	echo "<br>";
	echo "Завершенные задачи в статусе 'Соисполнитель':";
	echo "<br>";
	$res = CTasks::GetList(
        Array("TITLE" => "ASC"), 
        Array(
		"ACCOMPLICE" => $id,
		">=CLOSED_DATE" => $date1,
   		"<=CLOSED_DATE" => $date2,
		'REAL_STATUS' => CTasks::STATE_COMPLETED
        )
    );
    while ($arTask = $res->GetNext())
    {
		$time=substr($arTask["CLOSED_DATE"], 0, strpos($arTask["CLOSED_DATE"], ' '));
        $completed = $completed + 1;
		echo $time." - ".'<a target="_blank" href="https://b24.opti.ooo/company/personal/user/1/tasks/task/view/'.$arTask['ID'].'/">'.$arTask['TITLE'].'</a>';
		echo "<br>";
    }
	echo "<br>";
	echo "Кол-во завершенных задач = ".$completed;
	echo "<br>";
	//Достаем и считаем минуты
	$res = CTaskElapsedTime::GetList(
        Array(), 
        Array(">=CREATED_DATE" => $date1,
   				"<=CREATED_DATE" => $date2,
				"USER_ID"=> $id)
    );
	$minutes = 0;
    while ($arElapsed = $res->Fetch())
    {
        $minutes += $arElapsed["MINUTES"];
    }
	$hours=round($minutes/60);//переводим в часы
	echo "Колв-во часов H = ".$hours;
	echo "<br>";
}
?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>