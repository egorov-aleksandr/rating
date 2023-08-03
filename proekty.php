<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Проекты");
global $USER;
$UserID=$USER->GetID();
$usersID=[];
?>
<style>
.workarea-content-paddings{
height:100%;
box-sizing: border-box;
}
</style>
<?

?>
<link rel="stylesheet" href="/rating/css/nav.css">
<link rel="stylesheet" href="css/projects.css">
<script src="js/projects.js"></script>
<div class="conteiner">
<div class="projects">
	<?
	if($UserID==1 || $UserID==3262){
		$filter=array("STAGE_ID" => array(245,0),"GROUP_ID" => 9,"REAL_STATUS"=>array(1,2,3,4,7));
	}else{
		$filter=array("RESPONSIBLE_ID"=>$UserID,"STAGE_ID" => array(245,0),"GROUP_ID" => 9,"REAL_STATUS"=>array(1,2,3,4,7));
	}

	$res = CTasks::GetList(//получаем список активных проектов
        Array("CREATED_DATE" => "DESC"), 
        $filter,
		Array("*","UF_*")
    );?>
	<?
	$countProjects=0;//счётчик колва
	$allhours=0;
	$allfacthours=0;
	$allclosedhours=0;
	$projects=[];//массив для вывода
	while($r = $res->GetNext()){//цикл по проектам
		$hours=0;//план часы
		$deals=[];//массив айди сделок привязанных к задачам проекта

		$rTasks = CTasks::GetList(
			Array("CREATED_DATE" => "DESC"), 
			Array("ID" => $r["ID"]),
			Array("UF_CRM_TASK")
		 );
		while($arTask=$rTasks->GetNext()){
			$crm = $arTask["UF_CRM_TASK"];//поле задачи, с элементами CRM, содержит строки вида CO_1234, C_1234, D_1234
			foreach($crm as $c){//перебираем элементы CRM задачи
				$c=explode("_", $c);//разделяем строку, чтбы вычленить айди сделки
				if($c[0]=="D" && !in_array($c[1], $deals)){$deals[]=$c[1];}//если это сделка, вычленяем айди и записываем в массив
			}
		}





		$rTasks = CTasks::GetList(//достаем список задач проекта
			Array("CREATED_DATE" => "DESC"), 
			Array("PARENT_ID" => $r["ID"],"GROUP_ID" => 9),
			Array("UF_CRM_TASK")
		 );
		while($arTask=$rTasks->GetNext()){
			$crm = $arTask["UF_CRM_TASK"];//поле задачи, с элементами CRM, содержит строки вида CO_1234, C_1234, D_1234
			foreach($crm as $c){//перебираем элементы CRM задачи
				$c=explode("_", $c);//разделяем строку, чтбы вычленить айди сделки
				if($c[0]=="D" && !in_array($c[1], $deals)){$deals[]=$c[1];}//если это сделка, вычленяем айди и записываем в массив
			}
		}

		$rDeals = CCrmDeal::GetList(//получаем сделки задачи
			Array('DATE_CREATE' => 'DESC'), 
			Array("ID"=>$deals, "STAGE_ID"=>array(3,9,"C1:EXECUTING","C40:EXECUTING","C22:1","C1:13","C30:2","C30:3","C36:2",
													"WON","C1:WON","C30:WON","C40:WON","C36:FINAL_INVOICE","C36:WON","C22:WON"))
			);
		while($arDeal=$rDeals->GetNext()){//цикл по сделкам
			$hours+=$arDeal["UF_CRM_1684760362373"];//считаем план часы
			$allhours+=$arDeal["UF_CRM_1684760362373"];//считаем план часы
		}
		//записываем инфу проекта в массив, инкрементируем счётчик
		$p=array("ID"=>$r["ID"],"CREATED_DATE"=>$r["CREATED_DATE"],"RESPONSIBLE_ID"=>$r["RESPONSIBLE_ID"],"TITLE"=>$r["TITLE"],"HOURS"=>$hours);
		if(!in_array($r["RESPONSIBLE_ID"], $usersID)){$usersID[]=$r["RESPONSIBLE_ID"];}
		$projects[]=$p;
		$countProjects++;
	}
	?>

	<?
$projectResult=[];
foreach($projects as $project){
	$projectHours=0;//часы
	$projectMinutes=0;//минуты
	$projectPlus=0;//пол оценки
	$projectMinus=0;//отр оценки
	$res = CTaskElapsedTime::GetList(//достаем часы проекта
        Array(), 
        Array("TASK_ID" => $project["ID"])
		);
		while ($arElapsed = $res->Fetch())
		{
			$projectMinutes += $arElapsed["MINUTES"];//добавляем минуты
			$allfacthours+=$arElapsed["MINUTES"];
		}

	$tasks=[];//массив задач проекта
	$res = CTasks::GetList(//достаем список задач проекта
        Array("CREATED_DATE" => "DESC"), 
        Array("PARENT_ID" => $project["ID"],"GROUP_ID" => 9)
    );

	while($t = $res->GetNext()){//цикл по задачам
		$hours=0;//часы
		$minutes=0;//минуты
		$status="NORM";
		$r = CTaskElapsedTime::GetList(//достаем время задачи
        Array(), 
        Array("TASK_ID" => $t["ID"])
		);
		while ($arElapsed = $r->Fetch())
		{
			$minutes += $arElapsed["MINUTES"];//добавляем минуты
			$allfacthours+= $arElapsed["MINUTES"];//добавляем минуты
			if($t["CLOSED_DATE"]){
				$allclosedhours+= $arElapsed["MINUTES"];//добавляем минуты
			}
		}
		$projectMinutes+=$minutes;//добавляем минуты проекта
		$hours=intdiv($minutes,60);//переводим в часы
		$minutes=$minutes%60;//считаем остаток минут

		if($t["MARK"]=='P'){$projectPlus++;}//пол оценки
		if($t["MARK"]=='N'){$projectMinus++;}//отр оценки
		//Если задача просрочены - красный статус, если у задачи нет крайнего срока - оранжевый статус
		if($t["STATUS"]==-1){$status="RED";}elseif(!$t["DEADLINE"]){$status="ORANGE";}
		//добавляем инфу о задаче в массив
		$task=array("ID"=>$t["ID"],"RESPONSIBLE_ID"=> $t["RESPONSIBLE_ID"],
					"TITLE"=> $t["TITLE"], "CREATED_DATE"=> $t["CREATED_DATE"],"CLOSED_DATE"=>$t["CLOSED_DATE"], 
					"MARK"=>$t["MARK"],"STATUS"=>$status, "HOURS"=> $hours, "MINUTES"=>$minutes);
		$tasks[]=$task;
		//всё тоже самое, для второго уровня задач
		$secondlevel = CTasks::GetList(
        Array("CREATED_DATE" => "DESC"), 
        Array("PARENT_ID" => $t["ID"],"GROUP_ID" => 9)
   		 );

		while($l2 = $secondlevel->GetNext()){
			$hours=0;
			$minutes=0;
			$r = CTaskElapsedTime::GetList(
			Array(), 
			Array("TASK_ID" => $l2["ID"])
			);
			while ($arElapsed = $r->Fetch())
			{
				$minutes += $arElapsed["MINUTES"];
				$allfacthours+= $arElapsed["MINUTES"];//добавляем минуты
				if($t["CLOSED_DATE"]){
					$allclosedhours+= $arElapsed["MINUTES"];//добавляем минуты
				}
			}
			$projectMinutes+=$minutes;
			$hours=intdiv($minutes,60);//переводим в часы
			$minutes=$minutes%60;
	
			if($l2["MARK"]=='P'){$projectPlus++;}
			if($l2["MARK"]=='N'){$projectMinus++;}
	
			$task=array("ID"=>$l2["ID"],"RESPONSIBLE_ID"=> $l2["RESPONSIBLE_ID"],
						"TITLE"=> $l2["TITLE"], "CREATED_DATE"=> $l2["CREATED_DATE"],"CLOSED_DATE"=>$l2["CLOSED_DATE"], 
						"MARK"=>$l2["MARK"], "HOURS"=> $hours, "MINUTES"=>$minutes);
			$tasks[]=$task;
		}
	}

	$projectHours=intdiv($projectMinutes,60);//переводим в часы
	$projectMinutes=$projectMinutes%60;

	$project+=["TASKS"=>$tasks];
	$project+=["FACT_HOURS"=>$projectHours];//часы
	$project+=["FACT_MINUTES"=>$projectMinutes];//минуты
	$project+=["PLUS"=>$projectPlus];//пол оценки
	$project+=["MINUS"=>$projectMinus];//отр оценки

	$projectResult[]=$project;
}
$allfacthours=round($allfacthours/60);
$allclosedhours=round($allclosedhours/60);
$allfacthours=$allhours-$allfacthours;

	?>
	<div class="upper" onclick="showTasks('img-p-active','p-active')">
		<div class="allhours">
			<div>
				<?=$allhours?>
				&nbsp;
			</div>
			/
			<div style="color:red;">
				&nbsp;
				<?=$allfacthours?>
				&nbsp;
			</div>
			/
			<div <?if($allclosedhours/$allhours>1.4){echo "style='color:red;'";}?>>
				&nbsp;
				<?=$allclosedhours?>
			</div>
		</div>
		<div class="upper-title">Активные</div>
		<div class="upper-count"><?=count($projects)?></div>
		 <div class="show-projects" >
			<img  class="img-p-active hide" src="imgs/show-tasks.png">
			<img  class="img-p-active" src="imgs/close-tasks.png">
         </div>
	</div>
<div id="p-active" >
    <div class="row">
        <div class="column project-name">
            <div class="title">
                Проекты
            </div>
        </div>
        <div class="column project-emp">
            <div class="title">
                Сотрудник
            </div>
        </div>
        <div class="column project-hours">
            <div class="title">
                Часы
            </div>
        </div>
        <div class="column project-start">
            <div class="title">
                Старт
            </div>
        </div>
        <div class="column project-rate">
            <div class="title">
                Оценки
            </div>
        </div>
        <div class="project-null">
        </div>
    </div>

	<?foreach($projectResult as $project):?>

    <div class="project-row <?="filter-".$project['RESPONSIBLE_ID']?>" >
        <div class="column project-name" onclick="showTasks('img<?=$project["ID"]?>','task<?=$project["ID"]?>')"style="cursor:pointer;">
            <div class="name-con">
				<div class="name">
					<a href="https://b24.opti.ooo/company/personal/user/<?=$UserID?>/tasks/task/view/<?=$project["ID"]?>/"><?=$project["TITLE"]?></a>
				</div>
                <div class="what">
					<?=$project["HOURS"]?>
                </div>
				<?if($tasks):?>
                <div class="show-tasks" >
					<img  class="img<?=$project["ID"]?>" src="imgs/show-tasks.png">
					<img  class="img<?=$project["ID"]?> hide" src="imgs/close-tasks.png">
                </div>
				<?endif;?>
            </div>
        </div>
		<?
		$rsUser = CUser::GetByID($project['RESPONSIBLE_ID']);
		$arUser = $rsUser->Fetch();
		$photoID = $arUser['PERSONAL_PHOTO'];//ищем id фото
		$photoPath = CFile::GetPath($photoID);//ищем путь к фото
		$filterString = $arUser['ID'].','."'".$arUser["NAME"]." ".$arUser["LAST_NAME"]."'";
		?>

        <div class="column project-emp" onclick="filter(<?=$filterString?>)"style="cursor:pointer;">
            <div class="project-emp-con">
                <div class="project-emp-img" style="background-image: url('<?echo $photoPath;?>'); background-size: cover"></div>
                <div class="project-emp-info">
                    <div class="project-emp-info-name">
                        <?=$arUser["NAME"]." ".$arUser["LAST_NAME"]?>
                    </div>
                    <div class="project-emp-info-work">
                        <?=$arUser["WORK_POSITION"]?>
                    </div>
                </div>
            </div>
        </div>
        <div class="column project-hours">
			<div class="project-hours-con"<?if(($project["FACT_HOURS"]/$project["HOURS"])>=1.4 && $project["HOURS"]>0){echo "style='color:red;'";}?>>
				<?
					if($project["FACT_HOURS"]<10){echo "0".$project["FACT_HOURS"];}else{echo $project["FACT_HOURS"];}
					echo ":";
					if($project["FACT_MINUTES"]<10){echo "0".$project["FACT_MINUTES"];}else{echo $project["FACT_MINUTES"];}
				?>
            </div>
        </div>
        <div class="column project-start">
			<div style="line-height:55px;">
			<?$projectDate= explode(" ",$project["CREATED_DATE"]);?>
                <?=$projectDate[0]?>
			</div>
        </div>
        <div class="column project-rate">
            <div class="project-rate-con">
                <div class="project-rate-minus"><?=$project["MINUS"]?></div>
                /
                <div class="project-rate-plus"><?=$project["PLUS"]?></div>
            </div>
        </div>
        <div class="project-null">
        </div>
    </div>
	<?if($project["TASKS"]):?>
	<?$first=true;?>
    <div class="tasks hide" id="task<?=$project["ID"]?>">
		<?foreach ($project["TASKS"] as $task):?>
		<?$rsUser = CUser::GetByID($task['RESPONSIBLE_ID']);
		$arUser = $rsUser->Fetch();
		$photoID = $arUser['PERSONAL_PHOTO'];//ищем id фото
		$photoPath = CFile::GetPath($photoID);//ищем путь к фото
		?>
        <div class="task-row">
            <div class="task-null">
				<?if($first):?>
					<div class="tasks-title">Задачи</div>
					<?$first=false;?>
				<?endif;?>
            </div>
            <div class="column task-name">
                <div class="task-name-con">
					<a href="">
						<div class="name">
							<a <?if($task['CLOSED_DATE']){echo 'class="finished" ';}?>href="https://b24.opti.ooo/company/personal/user/<?=$UserID?>/tasks/task/view/<?=$task["ID"]?>/"><?=$task["TITLE"]?></a>
						</div>
					</a>
                </div>
            </div>
            <div class="column project-emp">
                <div class="task-emp-con">
                    <div class="task-emp-img" style="background-image: url('<?echo $photoPath;?>'); background-size: cover"></div>
                    <div class="task-emp-info">
                        <div class="task-emp-info-name">
                            <?=$arUser["NAME"]." ".$arUser["LAST_NAME"]?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="column project-hours">
                <div class="task-hours-con">
					<?
					if($task["HOURS"]<10){echo "0".$task["HOURS"];}else{echo $task["HOURS"];}
					echo ":";
					if($task["MINUTES"]<10){echo "0".$task["MINUTES"];}else{echo $task["MINUTES"];}
				?>
                </div>
            </div>
			<div class="column task-start"<?if($task["STATUS"]=="RED"){echo 'style="color:#ee1d24;"';}elseif($task["STATUS"]=="ORANGE"){echo 'style="color:#f7941d;"';}?>>
				<?$taskDate= explode(" ",$task["CREATED_DATE"]);?>
                <?=$taskDate[0]?>
            </div>
            <div class="column project-rate">
                <div class="task-rate-con">
					<?if($task["MARK"]=='P'):?>
						<div class="task-rate-plus">+</div>
					<?endif;?>
					<?if($task["MARK"]=='N'):?>
                    	<div class="task-rate-minus">-</div>
					<?endif;?>
                </div>
            </div>
            <div class="project-null">
            </div>
        </div>
		<?endforeach;?>
    </div>
	<?endif;?>
<?endforeach;?>
</div>

<?
	if($UserID==1 || $UserID==3262){
		$filter=array("STAGE_ID" => array(245,0),"GROUP_ID" => 9,"REAL_STATUS"=>6);
	}else{
		$filter=array("RESPONSIBLE_ID"=>$UserID,"STAGE_ID" => array(245,0),"GROUP_ID" => 9,"REAL_STATUS"=>6);
	}
	$res = CTasks::GetList(
        Array("CREATED_DATE" => "DESC"), 
        $filter
    );?>
<?
	$countProjects=0;//счётчик колва
	$allhours=0;
	$allfacthours=0;
	$allclosedhours=0;
	$projects=[];//массив для вывода
	while($r = $res->GetNext()){//цикл по проектам
		$hours=0;//план часы
		$deals=[];//массив айди сделок привязанных к задачам проекта

		$rTasks = CTasks::GetList(
			Array("CREATED_DATE" => "DESC"), 
			Array("ID" => $r["ID"]),
			Array("UF_CRM_TASK")
		 );
		while($arTask=$rTasks->GetNext()){
			$crm = $arTask["UF_CRM_TASK"];//поле задачи, с элементами CRM, содержит строки вида CO_1234, C_1234, D_1234
			foreach($crm as $c){//перебираем элементы CRM задачи
				$c=explode("_", $c);//разделяем строку, чтбы вычленить айди сделки
				if($c[0]=="D" && !in_array($c[1], $deals)){$deals[]=$c[1];}//если это сделка, вычленяем айди и записываем в массив
			}
		}



		$rTasks = CTasks::GetList(//достаем список задач проекта
			Array("CREATED_DATE" => "DESC"), 
			Array("PARENT_ID" => $r["ID"],"GROUP_ID" => 9),
			Array("UF_CRM_TASK")
		 );
		while($arTask=$rTasks->GetNext()){
			$crm = $arTask["UF_CRM_TASK"];//поле задачи, с элементами CRM, содержит строки вида CO_1234, C_1234, D_1234
			foreach($crm as $c){//перебираем элементы CRM задачи
				$c=explode("_", $c);//разделяем строку, чтбы вычленить айди сделки
				if($c[0]=="D" && !in_array($c[1], $deals)){$deals[]=$c[1];}//если это сделка, вычленяем айди и записываем в массив
			}
		}

		$rDeals = CCrmDeal::GetList(//получаем сделки задачи
			Array('DATE_CREATE' => 'DESC'), 
			Array("ID"=>$deals, "STAGE_ID"=>array(3,9,"C1:EXECUTING","C40:EXECUTING","C22:1","C1:13","C30:2","C30:3","C36:2",
													"WON","C1:WON","C30:WON","C40:WON","C36:FINAL_INVOICE","C36:WON","C22:WON"))
			);
		while($arDeal=$rDeals->GetNext()){//цикл по сделкам
			$hours+=$arDeal["UF_CRM_1684760362373"];//считаем план часы
			$allhours+=$arDeal["UF_CRM_1684760362373"];//считаем план часы
		}
		//записываем инфу проекта в массив, инкрементируем счётчик
		$p=array("ID"=>$r["ID"],"CREATED_DATE"=>$r["CREATED_DATE"],"RESPONSIBLE_ID"=>$r["RESPONSIBLE_ID"],"TITLE"=>$r["TITLE"],"HOURS"=>$hours);
		if(!in_array($r["RESPONSIBLE_ID"], $usersID)){$usersID[]=$r["RESPONSIBLE_ID"];}
		$projects[]=$p;
		$countProjects++;
	}
	?>

	<?
$projectResult=[];
foreach($projects as $project){
	$projectHours=0;//часы
	$projectMinutes=0;//минуты
	$projectPlus=0;//пол оценки
	$projectMinus=0;//отр оценки
	$res = CTaskElapsedTime::GetList(//достаем часы проекта
        Array(), 
        Array("TASK_ID" => $project["ID"])
		);
		while ($arElapsed = $res->Fetch())
		{
			$projectMinutes += $arElapsed["MINUTES"];//добавляем минуты
			$allfacthours+=$arElapsed["MINUTES"];
		}

	$tasks=[];//массив задач проекта
	$res = CTasks::GetList(//достаем список задач проекта
        Array("CREATED_DATE" => "DESC"), 
        Array("PARENT_ID" => $project["ID"],"GROUP_ID" => 9)
    );

	while($t = $res->GetNext()){//цикл по задачам
		$hours=0;//часы
		$minutes=0;//минуты
		$status="NORM";
		$r = CTaskElapsedTime::GetList(//достаем время задачи
        Array(), 
        Array("TASK_ID" => $t["ID"])
		);
		while ($arElapsed = $r->Fetch())
		{
			$minutes += $arElapsed["MINUTES"];//добавляем минуты
			$allfacthours+= $arElapsed["MINUTES"];//добавляем минуты
			if($t["CLOSED_DATE"]){
				$allclosedhours+= $arElapsed["MINUTES"];//добавляем минуты
			}
		}
		$projectMinutes+=$minutes;//добавляем минуты проекта
		$hours=intdiv($minutes,60);//переводим в часы
		$minutes=$minutes%60;//считаем остаток минут

		if($t["MARK"]=='P'){$projectPlus++;}//пол оценки
		if($t["MARK"]=='N'){$projectMinus++;}//отр оценки
		//Если задача просрочены - красный статус, если у задачи нет крайнего срока - оранжевый статус
		if($t["STATUS"]==-1){$status="RED";}elseif(!$t["DEADLINE"]){$status="ORANGE";}
		//добавляем инфу о задаче в массив
		$task=array("ID"=>$t["ID"],"RESPONSIBLE_ID"=> $t["RESPONSIBLE_ID"],
					"TITLE"=> $t["TITLE"], "CREATED_DATE"=> $t["CREATED_DATE"],"CLOSED_DATE"=>$t["CLOSED_DATE"], 
					"MARK"=>$t["MARK"],"STATUS"=>$status, "HOURS"=> $hours, "MINUTES"=>$minutes);
		$tasks[]=$task;
		//всё тоже самое, для второго уровня задач
		$secondlevel = CTasks::GetList(
        Array("CREATED_DATE" => "DESC"), 
        Array("PARENT_ID" => $t["ID"],"GROUP_ID" => 9)
   		 );

		while($l2 = $secondlevel->GetNext()){
			$hours=0;
			$minutes=0;
			$r = CTaskElapsedTime::GetList(
			Array(), 
			Array("TASK_ID" => $l2["ID"])
			);
			while ($arElapsed = $r->Fetch())
			{
				$minutes += $arElapsed["MINUTES"];
				$allfacthours+= $arElapsed["MINUTES"];//добавляем минуты
				if($t["CLOSED_DATE"]){
					$allclosedhours+= $arElapsed["MINUTES"];//добавляем минуты
				}
			}
			$projectMinutes+=$minutes;
			$hours=intdiv($minutes,60);//переводим в часы
			$minutes=$minutes%60;
	
			if($l2["MARK"]=='P'){$projectPlus++;}
			if($l2["MARK"]=='N'){$projectMinus++;}
	
			$task=array("ID"=>$l2["ID"],"RESPONSIBLE_ID"=> $l2["RESPONSIBLE_ID"],
						"TITLE"=> $l2["TITLE"], "CREATED_DATE"=> $l2["CREATED_DATE"],"CLOSED_DATE"=>$l2["CLOSED_DATE"], 
						"MARK"=>$l2["MARK"], "HOURS"=> $hours, "MINUTES"=>$minutes);
			$tasks[]=$task;
		}
	}

	$projectHours=intdiv($projectMinutes,60);//переводим в часы
	$projectMinutes=$projectMinutes%60;

	$project+=["TASKS"=>$tasks];
	$project+=["FACT_HOURS"=>$projectHours];//часы
	$project+=["FACT_MINUTES"=>$projectMinutes];//минуты
	$project+=["PLUS"=>$projectPlus];//пол оценки
	$project+=["MINUS"=>$projectMinus];//отр оценки

	$projectResult[]=$project;
}
$allfacthours=round($allfacthours/60);
$allclosedhours=round($allclosedhours/60);
$allfacthours=$allhours-$allfacthours;
	?>
	<div class="upper" onclick="showTasks('img-p-def','p-def')">
		<div class="allhours">
			<div>
				<?=$allhours?>
				&nbsp;
			</div>
			/
			<div style="color:red;">
				&nbsp;
				<?=$allfacthours?>
				&nbsp;
			</div>
			/
			<div <?if($allclosedhours/$allhours>1.4){echo "style='color:red;'";}?>>
				&nbsp;
				<?=$allclosedhours?>
			</div>
		</div>
		<div class="upper-title">Отложенные</div>
		<div class="upper-count"><?=count($projects)?></div>
		 <div class="show-projects" >
			<img  class="img-p-def" src="imgs/show-tasks.png">
			<img  class="img-p-def hide" src="imgs/close-tasks.png">
         </div>
	</div>
<div id="p-def" class="hide">
    <div class="row">
        <div class="column project-name">
            <div class="title">
                Проекты
            </div>
        </div>
        <div class="column project-emp">
            <div class="title">
                Сотрудник
            </div>
        </div>
        <div class="column project-hours">
            <div class="title">
                Часы
            </div>
        </div>
        <div class="column project-start">
            <div class="title">
                Старт
            </div>
        </div>
        <div class="column project-rate">
            <div class="title">
                Оценки
            </div>
        </div>
        <div class="project-null">
        </div>
    </div>

	<?foreach($projectResult as $project):?>

    <div class="project-row <?="filter-".$project['RESPONSIBLE_ID']?>" >
        <div class="column project-name" onclick="showTasks('img<?=$project["ID"]?>','task<?=$project["ID"]?>')"style="cursor:pointer;">
            <div class="name-con">
				<div class="name">
					<a href="https://b24.opti.ooo/company/personal/user/<?=$UserID?>/tasks/task/view/<?=$project["ID"]?>/"><?=$project["TITLE"]?></a>
				</div>
                <div class="what">
					<?=$project["HOURS"]?>
                </div>
				<?if($tasks):?>
                <div class="show-tasks" >
					<img  class="img<?=$project["ID"]?>" src="imgs/show-tasks.png">
					<img  class="img<?=$project["ID"]?> hide" src="imgs/close-tasks.png">
                </div>
				<?endif;?>
            </div>
        </div>
		<?
		$rsUser = CUser::GetByID($project['RESPONSIBLE_ID']);
		$arUser = $rsUser->Fetch();
		$photoID = $arUser['PERSONAL_PHOTO'];//ищем id фото
		$photoPath = CFile::GetPath($photoID);//ищем путь к фото
		$filterString = $arUser['ID'].','."'".$arUser["NAME"]." ".$arUser["LAST_NAME"]."'";
		?>

        <div class="column project-emp" onclick="filter(<?=$filterString?>)"style="cursor:pointer;">
            <div class="project-emp-con">
                <div class="project-emp-img" style="background-image: url('<?echo $photoPath;?>'); background-size: cover"></div>
                <div class="project-emp-info">
                    <div class="project-emp-info-name">
                        <?=$arUser["NAME"]." ".$arUser["LAST_NAME"]?>
                    </div>
                    <div class="project-emp-info-work">
                        <?=$arUser["WORK_POSITION"]?>
                    </div>
                </div>
            </div>
        </div>
        <div class="column project-hours">
			<div class="project-hours-con"<?if(($project["FACT_HOURS"]/$project["HOURS"])>=1.4 && $project["HOURS"]>0){echo "style='color:red;'";}?>>
				<?
					if($project["FACT_HOURS"]<10){echo "0".$project["FACT_HOURS"];}else{echo $project["FACT_HOURS"];}
					echo ":";
					if($project["FACT_MINUTES"]<10){echo "0".$project["FACT_MINUTES"];}else{echo $project["FACT_MINUTES"];}
				?>
            </div>
        </div>
        <div class="column project-start">
			<div style="line-height:55px;">
			<?$projectDate= explode(" ",$project["CREATED_DATE"]);?>
                <?=$projectDate[0]?>
			</div>
        </div>
        <div class="column project-rate">
            <div class="project-rate-con">
                <div class="project-rate-minus"><?=$project["MINUS"]?></div>
                /
                <div class="project-rate-plus"><?=$project["PLUS"]?></div>
            </div>
        </div>
        <div class="project-null">
        </div>
    </div>
	<?if($project["TASKS"]):?>
	<?$first=true;?>
    <div class="tasks hide" id="task<?=$project["ID"]?>">
		<?foreach ($project["TASKS"] as $task):?>
		<?$rsUser = CUser::GetByID($task['RESPONSIBLE_ID']);
		$arUser = $rsUser->Fetch();
		$photoID = $arUser['PERSONAL_PHOTO'];//ищем id фото
		$photoPath = CFile::GetPath($photoID);//ищем путь к фото
		?>
        <div class="task-row">
            <div class="task-null">
				<?if($first):?>
					<div class="tasks-title">Задачи</div>
					<?$first=false;?>
				<?endif;?>
            </div>
            <div class="column task-name">
                <div class="task-name-con">
					<a href="">
						<div class="name">
							<a <?if($task['CLOSED_DATE']){echo 'class="finished" ';}?>href="https://b24.opti.ooo/company/personal/user/<?=$UserID?>/tasks/task/view/<?=$task["ID"]?>/"><?=$task["TITLE"]?></a>
						</div>
					</a>
                </div>
            </div>
            <div class="column project-emp">
                <div class="task-emp-con">
                    <div class="task-emp-img" style="background-image: url('<?echo $photoPath;?>'); background-size: cover"></div>
                    <div class="task-emp-info">
                        <div class="task-emp-info-name">
                            <?=$arUser["NAME"]." ".$arUser["LAST_NAME"]?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="column project-hours">
                <div class="task-hours-con">
					<?
					if($task["HOURS"]<10){echo "0".$task["HOURS"];}else{echo $task["HOURS"];}
					echo ":";
					if($task["MINUTES"]<10){echo "0".$task["MINUTES"];}else{echo $task["MINUTES"];}
				?>
                </div>
            </div>
			<div class="column task-start"<?if($task["STATUS"]=="RED"){echo 'style="color:#ee1d24;"';}elseif($task["STATUS"]=="ORANGE"){echo 'style="color:#f7941d;"';}?>>
				<?$taskDate= explode(" ",$task["CREATED_DATE"]);?>
                <?=$taskDate[0]?>
            </div>
            <div class="column project-rate">
                <div class="task-rate-con">
					<?if($task["MARK"]=='P'):?>
						<div class="task-rate-plus">+</div>
					<?endif;?>
					<?if($task["MARK"]=='N'):?>
                    	<div class="task-rate-minus">-</div>
					<?endif;?>
                </div>
            </div>
            <div class="project-null">
            </div>
        </div>
		<?endforeach;?>
    </div>
	<?endif;?>
<?endforeach;?>
</div>
<?
	if($UserID==1 || $UserID==3262){
		$filter=array("STAGE_ID" => array(245,0),"GROUP_ID" => 9,"REAL_STATUS"=>5);
	}else{
		$filter=array("RESPONSIBLE_ID"=>$UserID,"STAGE_ID" => array(245,0),"GROUP_ID" => 9,"REAL_STATUS"=>5);
	}
	$res = CTasks::GetList(
        Array("CREATED_DATE" => "DESC"), 
        $filter
    );?>
<?
	$countProjects=0;//счётчик колва
	$allhours=0;
	$allfacthours=0;
	$allclosedhours=0;
	$projects=[];//массив для вывода
	while($r = $res->GetNext()){//цикл по проектам
		$hours=0;//план часы
		$deals=[];//массив айди сделок привязанных к задачам проекта


		$rTasks = CTasks::GetList(
			Array("CREATED_DATE" => "DESC"), 
			Array("ID" => $r["ID"]),
			Array("UF_CRM_TASK")
		 );
		while($arTask=$rTasks->GetNext()){
			$crm = $arTask["UF_CRM_TASK"];//поле задачи, с элементами CRM, содержит строки вида CO_1234, C_1234, D_1234
			foreach($crm as $c){//перебираем элементы CRM задачи
				$c=explode("_", $c);//разделяем строку, чтбы вычленить айди сделки
				if($c[0]=="D" && !in_array($c[1], $deals)){$deals[]=$c[1];}//если это сделка, вычленяем айди и записываем в массив
			}
		}



		$rTasks = CTasks::GetList(//достаем список задач проекта
			Array("CREATED_DATE" => "DESC"), 
			Array("PARENT_ID" => $r["ID"],"GROUP_ID" => 9),
			Array("UF_CRM_TASK")
		 );
		while($arTask=$rTasks->GetNext()){
			$crm = $arTask["UF_CRM_TASK"];//поле задачи, с элементами CRM, содержит строки вида CO_1234, C_1234, D_1234
			foreach($crm as $c){//перебираем элементы CRM задачи
				$c=explode("_", $c);//разделяем строку, чтбы вычленить айди сделки
				if($c[0]=="D" && !in_array($c[1], $deals)){$deals[]=$c[1];}//если это сделка, вычленяем айди и записываем в массив
			}
		}

		$rDeals = CCrmDeal::GetList(//получаем сделки задачи
			Array('DATE_CREATE' => 'DESC'), 
			Array("ID"=>$deals, "STAGE_ID"=>array(3,9,"C1:EXECUTING","C40:EXECUTING","C22:1","C1:13","C30:2","C30:3","C36:2",
													"WON","C1:WON","C30:WON","C40:WON","C36:FINAL_INVOICE","C36:WON","C22:WON"))
			);
		while($arDeal=$rDeals->GetNext()){//цикл по сделкам
			$hours+=$arDeal["UF_CRM_1684760362373"];//считаем план часы
			$allhours+=$arDeal["UF_CRM_1684760362373"];//считаем план часы
		}
		//записываем инфу проекта в массив, инкрементируем счётчик
		$p=array("ID"=>$r["ID"],"CREATED_DATE"=>$r["CREATED_DATE"],"RESPONSIBLE_ID"=>$r["RESPONSIBLE_ID"],"TITLE"=>$r["TITLE"],"HOURS"=>$hours);
		if(!in_array($r["RESPONSIBLE_ID"], $usersID)){$usersID[]=$r["RESPONSIBLE_ID"];}
		$projects[]=$p;
		$countProjects++;
	}
	?>

	<?
$projectResult=[];
foreach($projects as $project){
	$projectHours=0;//часы
	$projectMinutes=0;//минуты
	$projectPlus=0;//пол оценки
	$projectMinus=0;//отр оценки
	$res = CTaskElapsedTime::GetList(//достаем часы проекта
        Array(), 
        Array("TASK_ID" => $project["ID"])
		);
		while ($arElapsed = $res->Fetch())
		{
			$projectMinutes += $arElapsed["MINUTES"];//добавляем минуты
			$allfacthours+=$arElapsed["MINUTES"];
		}

	$tasks=[];//массив задач проекта
	$res = CTasks::GetList(//достаем список задач проекта
        Array("CREATED_DATE" => "DESC"), 
        Array("PARENT_ID" => $project["ID"],"GROUP_ID" => 9)
    );

	while($t = $res->GetNext()){//цикл по задачам
		$hours=0;//часы
		$minutes=0;//минуты
		$status="NORM";
		$r = CTaskElapsedTime::GetList(//достаем время задачи
        Array(), 
        Array("TASK_ID" => $t["ID"])
		);
		while ($arElapsed = $r->Fetch())
		{
			$minutes += $arElapsed["MINUTES"];//добавляем минуты
			$allfacthours+= $arElapsed["MINUTES"];//добавляем минуты
			if($t["CLOSED_DATE"]){
				$allclosedhours+= $arElapsed["MINUTES"];//добавляем минуты
			}
		}
		$projectMinutes+=$minutes;//добавляем минуты проекта
		$hours=intdiv($minutes,60);//переводим в часы
		$minutes=$minutes%60;//считаем остаток минут

		if($t["MARK"]=='P'){$projectPlus++;}//пол оценки
		if($t["MARK"]=='N'){$projectMinus++;}//отр оценки
		//Если задача просрочены - красный статус, если у задачи нет крайнего срока - оранжевый статус
		if($t["STATUS"]==-1){$status="RED";}elseif(!$t["DEADLINE"]){$status="ORANGE";}
		//добавляем инфу о задаче в массив
		$task=array("ID"=>$t["ID"],"RESPONSIBLE_ID"=> $t["RESPONSIBLE_ID"],
					"TITLE"=> $t["TITLE"], "CREATED_DATE"=> $t["CREATED_DATE"],"CLOSED_DATE"=>$t["CLOSED_DATE"], 
					"MARK"=>$t["MARK"],"STATUS"=>$status, "HOURS"=> $hours, "MINUTES"=>$minutes);
		$tasks[]=$task;
		//всё тоже самое, для второго уровня задач
		$secondlevel = CTasks::GetList(
        Array("CREATED_DATE" => "DESC"), 
        Array("PARENT_ID" => $t["ID"],"GROUP_ID" => 9)
   		 );

		while($l2 = $secondlevel->GetNext()){
			$hours=0;
			$minutes=0;
			$r = CTaskElapsedTime::GetList(
			Array(), 
			Array("TASK_ID" => $l2["ID"])
			);
			while ($arElapsed = $r->Fetch())
			{
				$minutes += $arElapsed["MINUTES"];
				$allfacthours+= $arElapsed["MINUTES"];//добавляем минуты
				if($t["CLOSED_DATE"]){
					$allclosedhours+= $arElapsed["MINUTES"];//добавляем минуты
				}
			}
			$projectMinutes+=$minutes;
			$hours=intdiv($minutes,60);//переводим в часы
			$minutes=$minutes%60;
	
			if($l2["MARK"]=='P'){$projectPlus++;}
			if($l2["MARK"]=='N'){$projectMinus++;}
	
			$task=array("ID"=>$l2["ID"],"RESPONSIBLE_ID"=> $l2["RESPONSIBLE_ID"],
						"TITLE"=> $l2["TITLE"], "CREATED_DATE"=> $l2["CREATED_DATE"],"CLOSED_DATE"=>$l2["CLOSED_DATE"], 
						"MARK"=>$l2["MARK"], "HOURS"=> $hours, "MINUTES"=>$minutes);
			$tasks[]=$task;
		}
	}

	$projectHours=intdiv($projectMinutes,60);//переводим в часы
	$projectMinutes=$projectMinutes%60;

	$project+=["TASKS"=>$tasks];
	$project+=["FACT_HOURS"=>$projectHours];//часы
	$project+=["FACT_MINUTES"=>$projectMinutes];//минуты
	$project+=["PLUS"=>$projectPlus];//пол оценки
	$project+=["MINUS"=>$projectMinus];//отр оценки

	$projectResult[]=$project;
}
$allfacthours=round($allfacthours/60);
$allclosedhours=round($allclosedhours/60);
$allfacthours=$allhours-$allfacthours;
	?>
	<div class="upper" onclick="showTasks('img-p-finished','p-finished')">
		<div class="allhours">
			<div>
				<?=$allhours?>
				&nbsp;
			</div>
			/
			<div style="color:red;">
				&nbsp;
				<?=$allfacthours?>
				&nbsp;
			</div>
			/
			<div <?if($allclosedhours/$allhours>1.4){echo "style='color:red;'";}?>>
				&nbsp;
				<?=$allclosedhours?>
			</div>
		</div>
		<div class="upper-title">Завершенные</div>
		<div class="upper-count"><?=count($projects)?></div>
		 <div class="show-projects" >
			<img  class="img-p-finished" src="imgs/show-tasks.png">
			<img  class="img-p-finished hide" src="imgs/close-tasks.png">
         </div>
	</div>
<div id="p-finished" class="hide">
    <div class="row">
        <div class="column project-name">
            <div class="title">
                Проекты
            </div>
        </div>
        <div class="column project-emp">
            <div class="title">
                Сотрудник
            </div>
        </div>
        <div class="column project-hours">
            <div class="title">
                Часы
            </div>
        </div>
        <div class="column project-start">
            <div class="title">
                Старт
            </div>
        </div>
        <div class="column project-rate">
            <div class="title">
                Оценки
            </div>
        </div>
        <div class="project-null">
        </div>
    </div>

	<?foreach($projectResult as $project):?>

    <div class="project-row <?="filter-".$project['RESPONSIBLE_ID']?>" >
        <div class="column project-name" onclick="showTasks('img<?=$project["ID"]?>','task<?=$project["ID"]?>')"style="cursor:pointer;">
            <div class="name-con">
				<div class="name">
					<a href="https://b24.opti.ooo/company/personal/user/<?=$UserID?>/tasks/task/view/<?=$project["ID"]?>/"><?=$project["TITLE"]?></a>
				</div>
                <div class="what">
					<?=$project["HOURS"]?>
                </div>
				<?if($tasks):?>
                <div class="show-tasks" >
					<img  class="img<?=$project["ID"]?>" src="imgs/show-tasks.png">
					<img  class="img<?=$project["ID"]?> hide" src="imgs/close-tasks.png">
                </div>
				<?endif;?>
            </div>
        </div>
		<?
		$rsUser = CUser::GetByID($project['RESPONSIBLE_ID']);
		$arUser = $rsUser->Fetch();
		$photoID = $arUser['PERSONAL_PHOTO'];//ищем id фото
		$photoPath = CFile::GetPath($photoID);//ищем путь к фото
		$filterString = $arUser['ID'].','."'".$arUser["NAME"]." ".$arUser["LAST_NAME"]."'";
		?>

        <div class="column project-emp" onclick="filter(<?=$filterString?>)"style="cursor:pointer;">
            <div class="project-emp-con">
                <div class="project-emp-img" style="background-image: url('<?echo $photoPath;?>'); background-size: cover"></div>
                <div class="project-emp-info">
                    <div class="project-emp-info-name">
                        <?=$arUser["NAME"]." ".$arUser["LAST_NAME"]?>
                    </div>
                    <div class="project-emp-info-work">
                        <?=$arUser["WORK_POSITION"]?>
                    </div>
                </div>
            </div>
        </div>
        <div class="column project-hours">
			<div class="project-hours-con"<?if(($project["FACT_HOURS"]/$project["HOURS"])>=1.4 && $project["HOURS"]>0){echo "style='color:red;'";}?>>
				<?
					if($project["FACT_HOURS"]<10){echo "0".$project["FACT_HOURS"];}else{echo $project["FACT_HOURS"];}
					echo ":";
					if($project["FACT_MINUTES"]<10){echo "0".$project["FACT_MINUTES"];}else{echo $project["FACT_MINUTES"];}
				?>
            </div>
        </div>
        <div class="column project-start">
			<div style="line-height:55px;">
			<?$projectDate= explode(" ",$project["CREATED_DATE"]);?>
                <?=$projectDate[0]?>
			</div>
        </div>
        <div class="column project-rate">
            <div class="project-rate-con">
                <div class="project-rate-minus"><?=$project["MINUS"]?></div>
                /
                <div class="project-rate-plus"><?=$project["PLUS"]?></div>
            </div>
        </div>
        <div class="project-null">
        </div>
    </div>
	<?if($project["TASKS"]):?>
	<?$first=true;?>
    <div class="tasks hide" id="task<?=$project["ID"]?>">
		<?foreach ($project["TASKS"] as $task):?>
		<?$rsUser = CUser::GetByID($task['RESPONSIBLE_ID']);
		$arUser = $rsUser->Fetch();
		$photoID = $arUser['PERSONAL_PHOTO'];//ищем id фото
		$photoPath = CFile::GetPath($photoID);//ищем путь к фото
		?>
        <div class="task-row">
            <div class="task-null">
				<?if($first):?>
					<div class="tasks-title">Задачи</div>
					<?$first=false;?>
				<?endif;?>
            </div>
            <div class="column task-name">
                <div class="task-name-con">
					<a href="">
						<div class="name">
							<a <?if($task['CLOSED_DATE']){echo 'class="finished" ';}?>href="https://b24.opti.ooo/company/personal/user/<?=$UserID?>/tasks/task/view/<?=$task["ID"]?>/"><?=$task["TITLE"]?></a>
						</div>
					</a>
                </div>
            </div>
            <div class="column project-emp">
                <div class="task-emp-con">
                    <div class="task-emp-img" style="background-image: url('<?echo $photoPath;?>'); background-size: cover"></div>
                    <div class="task-emp-info">
                        <div class="task-emp-info-name">
                            <?=$arUser["NAME"]." ".$arUser["LAST_NAME"]?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="column project-hours">
                <div class="task-hours-con">
					<?
					if($task["HOURS"]<10){echo "0".$task["HOURS"];}else{echo $task["HOURS"];}
					echo ":";
					if($task["MINUTES"]<10){echo "0".$task["MINUTES"];}else{echo $task["MINUTES"];}
				?>
                </div>
            </div>
			<div class="column task-start"<?if($task["STATUS"]=="RED"){echo 'style="color:#ee1d24;"';}elseif($task["STATUS"]=="ORANGE"){echo 'style="color:#f7941d;"';}?>>
				<?$taskDate= explode(" ",$task["CREATED_DATE"]);?>
                <?=$taskDate[0]?>
            </div>
            <div class="column project-rate">
                <div class="task-rate-con">
					<?if($task["MARK"]=='P'):?>
						<div class="task-rate-plus">+</div>
					<?endif;?>
					<?if($task["MARK"]=='N'):?>
                    	<div class="task-rate-minus">-</div>
					<?endif;?>
                </div>
            </div>
            <div class="project-null">
            </div>
        </div>
		<?endforeach;?>
    </div>
	<?endif;?>
<?endforeach;?>
</div>


<?//ЗАДАЧИ БЕЗ ПРОЕКТА

	if($UserID==1 || $UserID==3262){
		$filter=array("!STAGE_ID" => array(245,0),"PARENT_ID" => "","GROUP_ID" => 9);
	}else{
		$filter=array("!STAGE_ID" => array(245,0),"RESPONSIBLE_ID"=>$UserID,"PARENT_ID" => "","GROUP_ID" => 9);
	}

	$res = CTasks::GetList(
        Array("CREATED_DATE" => "DESC"), 
        $filter
    );
	$tasks=[];
	while($t = $res->GetNext()){
		$hours=0;
		$minutes=0;
		$status="NORM";
		$r = CTaskElapsedTime::GetList(
        Array(), 
        Array("TASK_ID" => $t["ID"])
		);
		while ($arElapsed = $r->Fetch())
		{
			$minutes += $arElapsed["MINUTES"];
		}
		$hours=intdiv($minutes,60);//переводим в часы
		$minutes=$minutes%60;

		if($t["STATUS"]==-1){$status="RED";}elseif(!$t["DEADLINE"]){$status="ORANGE";}

		$task=array("ID"=>$t["ID"],"RESPONSIBLE_ID"=> $t["RESPONSIBLE_ID"],
					"TITLE"=> $t["TITLE"], "CREATED_DATE"=> $t["CREATED_DATE"],"CLOSED_DATE"=>$t["CLOSED_DATE"], 
					"MARK"=>$t["MARK"], "HOURS"=> $hours, "MINUTES"=>$minutes, "STATUS"=>$status);
		if(!in_array($t["RESPONSIBLE_ID"], $usersID)){$usersID[]=$t["RESPONSIBLE_ID"];}
		$tasks[]=$task;
	}
	?>
<div class="upper" onclick="showTasks('img-p-nonp','p-nonp')">
		<div class="upper-title">Задачи без проекта</div>
		<div class="upper-count"><?=count($tasks)?></div>
		 <div class="show-projects" >
			<img  class="img-p-nonp" src="imgs/show-tasks.png">
			<img  class="img-p-nonp hide" src="imgs/close-tasks.png">
         </div>
</div>
<div id="p-nonp" class="hide">
    <div class="row">
        <div class="column project-name">
            <div class="title">
                Задача
            </div>
        </div>
        <div class="column project-emp">
            <div class="title">
                Сотрудник
            </div>
        </div>
        <div class="column project-hours">
            <div class="title">
                Часы
            </div>
        </div>
        <div class="column project-start">
            <div class="title">
                Старт
            </div>
        </div>
        <div class="column project-rate">
            <div class="title">
                Оценка
            </div>
        </div>
        <div class="project-null">
        </div>
    </div>

	<?foreach($tasks as $task):?>
	<div class="project-row <?="filter-".$task['RESPONSIBLE_ID']?>">
        <div class="column project-name">
            <div class="name-con">
				<div class="name">
					<a <?if($task['CLOSED_DATE']){echo 'class="finished" ';}?> href="https://b24.opti.ooo/company/personal/user/<?=$UserID?>/tasks/task/view/<?=$task["ID"]?>/"><?=$task["TITLE"]?></a>
				</div>
                <div class="what">

                </div>
            </div>
        </div>
		<?
		$rsUser = CUser::GetByID($task['RESPONSIBLE_ID']);
		$arUser = $rsUser->Fetch();
		$photoID = $arUser['PERSONAL_PHOTO'];//ищем id фото
		$photoPath = CFile::GetPath($photoID);//ищем путь к фото
		$filterString = $arUser['ID'].','."'".$arUser["NAME"]." ".$arUser["LAST_NAME"]."'";
		?>
        <div class="column project-emp" onclick="filter(<?=$filterString?>)"style="cursor:pointer;">
            <div class="project-emp-con">
                <div class="project-emp-img" style="background-image: url('<?echo $photoPath;?>'); background-size: cover"></div>
                <div class="project-emp-info">
                    <div class="project-emp-info-name">
                        <?=$arUser["NAME"]." ".$arUser["LAST_NAME"]?>
                    </div>
                    <div class="project-emp-info-work">
                        <?=$arUser["WORK_POSITION"]?>
                    </div>
                </div>
            </div>
        </div>
        <div class="column project-hours">
            <div class="project-hours-con">
				<?
					if($task["HOURS"]<10){echo "0".$task["HOURS"];}else{echo $task["HOURS"];}
					echo ":";
					if($task["MINUTES"]<10){echo "0".$task["MINUTES"];}else{echo $task["MINUTES"];}
				?>
            </div>
        </div>
        <div class="column project-start" <?if($task["STATUS"]=="RED"){echo 'style="color:#ee1d24;"';}elseif($task["STATUS"]=="ORANGE"){echo 'style="color:#f7941d;"';}?>>
			<div style="line-height:55px;">
			<?$taskDate= explode(" ",$task["CREATED_DATE"]);?>
                <?=$taskDate[0]?>
			</div>
        </div>
        <div class="column project-rate">
            <div class="project-rate-con">
                <?if($task["MARK"]=='P'):?>
					<div class="task-rate-plus">+</div>
				<?endif;?>
				<?if($task["MARK"]=='N'):?>
                   	<div class="task-rate-minus">-</div>
				<?endif;?>
            </div>
        </div>
        <div class="project-null">
        </div>
    </div>
<?endforeach;?>
</div>
<div id="filter" class="hide" onclick="removeFilter()">
	<div id="filter-text">
	</div>
	<div class="filter-close">
	</div>
</div>
</div>
<div class="navigation" >
		<a href="https://b24.opti.ooo/rating/"><button>Сотрудник</button></a>
		<a href="https://b24.opti.ooo/rating/results.php"><button >Команда</button></a>
		<a href=""><button class="active">Проекты</button></a>
		<?if($USER->IsAdmin()||$USER->GetID()==5462||$USER->GetID()==3262):?>
			<a href="https://b24.opti.ooo/rating/money.php"><button >Начисления</button></a>
		<?endif;?>
		<?if($USER->GetID()==1||$USER->GetID()==3262):?>
			<a href="https://b24.opti.ooo/rating/yearResults.php"><button>Результаты</button></a>
		<?endif;?>
	</div>
</div>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>