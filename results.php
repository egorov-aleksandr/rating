<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Команда");
echo '<link rel="stylesheet" href="/rating/css/style.css">';
echo '<script type="text/javascript" src="/rating/js/results.js"></script>';
echo '<link rel="stylesheet" href="/rating/css/nav.css">';
?>
<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();
use Bitrix\Highloadblock\HighloadBlockTable as HLBT;
CModule::IncludeModule('highloadblock');
?>


<?//СЕЛЕКТ ИНФЫ ПО ЗАДАЧАМ
$date = getdate();
$tomonth = $date['mon'];//ТЕКУЩИЙ МЕСЯЦ
if($_GET['month'] && $_GET['year']){//ЕСЛИ ПЕРЕДАНЫ МЕСЯЦ ГОД
	$month = $_GET['month'];
	$year = $_GET['year'];
}
else{
	$month=$tomonth;
	$year=2023;
}
$months = array( 1 => 'Январь' , 'Февраль' , 'Март' , 'Апрель' , 'Май' , 'Июнь' , 'Июль' , 'Август' , 'Сентябрь' , 'Октябрь' , 'Ноябрь' , 'Декабрь' );
$lastday =  cal_days_in_month(CAL_GREGORIAN, $month, $year);

$date1 = '1.'.$month.'.'.$year.' 00:00:00';
$date2 = $lastday.'.'.$month.'.'.$year.' 00:00:00';

$format = "d.m.Y H:i:s";
$datestart = DateTime::createFromFormat($format, $date1);
$datefinish = DateTime::createFromFormat($format, $date2);

$highblock_id = 7;
	$hl_block = HLBT::getById($highblock_id)->fetch();
	$entity = HLBT::compileEntity($hl_block);
	$entity_data_class = $entity->getDataClass();

	
	//ЗАПРОС В ХАЙЛОАД
	$rs_data = $entity_data_class::getList(array(
	   'select' => array('*'),
		'filter' => array('UF_MONTH'=>$month,'UF_YEAR'=>$year)
	));
	$data = $rs_data->fetch();
	if($data){
		$completed = $data['UF_COMPLETED'];
		$all =  $data['UF_ALL'];
		$overdue = $data['UF_OVERDUE'];
		$projectTime = $data['UF_DEPLOY_MINUTES'];
		$clientTime = $data['UF_PROJECT_MINUTES'];
		$uploaded = true;
	}else{
		$res = CTasks::GetList(
				Array("TITLE" => "ASC"), 
				Array(
				"GROUP_ID" => array(9,3),
				'REAL_STATUS' => CTasks::STATE_COMPLETED
				)
			);
			$completed = 0;
			while ($arTask = $res->GetNext())
			{
				$format = "d.m.Y H:i:s";
				$datetask = DateTime::createFromFormat($format, $arTask['CLOSED_DATE']);
				if($datetask > $datestart && $datetask < $datefinish){
					$completed = $completed + 1;
				}
			}
		
		$res = CTasks::GetList(
				Array("TITLE" => "ASC"), 
				Array(
				"GROUP_ID" => array(9,3),
				'REAL_STATUS' => array(CTasks::STATE_NEW,CTasks::STATE_IN_PROGRESS)
				)
			);
			$all = 0;
			while ($arTask = $res->GetNext())
			{
					$all = $all + 1;
			}
		
		$res = CTasks::GetList(
				Array("TITLE" => "ASC"), 
				Array(
				"GROUP_ID" => array(9,3),
				'REAL_STATUS' => array(CTasks::STATE_NEW,CTasks::STATE_IN_PROGRESS),
				'STATUS'=>-1
				)
			);
			$overdue = 0;
			while ($arTask = $res->GetNext())
			{
					$overdue = $overdue + 1;
			}
		
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
					$projectTasks[] = $arTask['ID'];
			}
		//список записей времени в интервале дат
		 $res = CTaskElapsedTime::GetList(
				Array(), 
				Array(">=CREATED_DATE" => $date1,
						"<=CREATED_DATE" => $date2)
			);
		//если айди задачи из группы совпадает с айди в записи времени
			$projectTime = 0;
			while ($arElapsed = $res->Fetch())
			{
				foreach($projectTasks as $task){
					if($task==$arElapsed['TASK_ID']){
						$projectTime += $arElapsed["MINUTES"];
					}
				}
			}
		
		//ЧАСЫ ПО КЛИЕНТАМ
		$res = CTasks::GetList(
				Array("TITLE" => "ASC"), 
				Array(
				"GROUP_ID" => 3
				)
			);
			$clientTasks = array();
			while ($arTask = $res->GetNext())
			{
					$clientTasks[] = $arTask['ID'];
			}
		
		$res = CTaskElapsedTime::GetList(
				Array(), 
				Array(">=CREATED_DATE" => $date1,
						"<=CREATED_DATE" => $date2)
			);
		$clientTime = 0;
			while ($arElapsed = $res->Fetch())
			{
				foreach($clientTasks as $task){
					if($task==$arElapsed['TASK_ID']){
						$clientTime += $arElapsed["MINUTES"];
					}
				}
			}
	}
?>
<link rel="stylesheet" href="style.css">

<div class = "conteiner">
    <div class = "upper-inf">
        <div class = "upper-inf-elem">
            <div class="upper-inf-elem-maintext">
                	<a style="color: #DFEBE9;" target="_blank" href="https://b24.opti.ooo/rating/TEST/devhours.php?year=<?=$year?>&month=<?=$month?>">
						Часы разработки
				</a>
                <div class="upper-inf-elem-img">
					<img src="/rating/imgs/clock.png" alt="альтернативный текст">
                </div>
            </div>
            <div class="upper-inf-elem-value">
               <?echo floor($projectTime / 60)."ч. ".($projectTime % 60)."м.";?>
            </div>
            <div class="upper-inf-elem-maintext">
                Сумма за месяц
                <div class="upper-inf-elem-lowerimg">
                    <img src="/rating/imgs/Arrow.png" alt="альтернативный текст">
                </div>
            </div>
        </div>
        <div class = "upper-inf-elem">
            <div class="upper-inf-elem-maintext">
				Незавершенные
                <div class="upper-inf-elem-img">
                    <img src="/rating/imgs/switch.png" alt="альтернативный текст">
                </div>
            </div>
            <div class="upper-inf-elem-value">
				<?=$all.'/'.$overdue?>
            </div>
            <div class="upper-inf-elem-maintext">
                Просроченные задачи
                <div class="upper-inf-elem-lowerimg">
                    <img src="/rating/imgs/Arrow.png" alt="альтернативный текст">
                </div>
            </div>
        </div>
        <div class = "upper-inf-elem">
            <div class="upper-inf-elem-maintext">
                Переговоры
                <div class="upper-inf-elem-img">
                        <img src="/rating/imgs/megaphone.png" alt="альтернативный текст">
                </div>
            </div>
            <div class="upper-inf-elem-value">
                <?echo floor($clientTime / 60)."ч. ".($clientTime % 60)."м.";?>
            </div>
            <div class="upper-inf-elem-maintext">
                Сумма за месяц
                <div class="upper-inf-elem-lowerimg">
                    <img src="/rating/imgs/Arrow.png" alt="альтернативный текст">
                </div>
            </div>
        </div>
        <div class = "upper-inf-elem">
            <div class="upper-inf-elem-maintext">
                Выполнено задач
                <div class="upper-inf-elem-img">
                        <img src="/rating/imgs/calendar.png" alt="альтернативный текст">
                </div>
            </div>
            <div class="upper-inf-elem-value">
                <?=$completed?>
            </div>
            <div class="upper-inf-elem-maintext">

                <div class="upper-inf-elem-lowerimg">
                    <img src="/rating/imgs/Arrow.png" alt="альтернативный текст">
                </div>
            </div>
        </div>
        <div class = "upper-inf-elem">
            <div class="selectdate">
                <div class="selectdate-con">
					<button class="selectdate-btn" onclick="backmonth(<?=$month.",".$year?>)">Пред.</button>
						<?=$months[$month]." / ".$year?>
					<button class="selectdate-btn" onclick="nextmonth(<?=$month.",".$year?>)">След.</button>
                </div>            
            </div>
			<?if($USER->IsAdmin() && !$uploaded):?>
				<button class="update" id="upload" onclick="upload(<?=$month.",".$year.",".$projectTime.",".$all.",".$overdue.",".$clientTime.",".$completed?>)">Выгрузить данные</button>
			<?endif;?>
        </div>
    </div>

    <div class="results">
	<?if($month&&$year):?>
		<?
		$filter = Array
		(
		"ACTIVE"              => "Y",
		"GROUPS_ID"           => Array(26)
		);
		$rsUsers = CUser::GetList(($by="work_department"), ($order="asc"), $filter);?>
		<div class="results-row">
            <div class = "results-elem">
                <div class="results-title">
                    Самостоятельность
                </div>
				<?$color=1;?>
				<?$highblock_id = 6;
				$hl_block = HLBT::getById($highblock_id)->fetch();
				
				// Получение имени класса
				$entity = HLBT::compileEntity($hl_block);
				$entity_data_class = $entity->getDataClass();?>
				<?while($arUser = $rsUsers->GetNext()):?>
					<?// Вывод элементов Highload-блока
					$rs_data = $entity_data_class::getList(array(
					   'select' => array('*'),
						'filter'=>array('UF_MONTH'=>$month,'UF_YEAR'=>$year,'UF_TYPE'=>"S",'UF_EMPLOYEE'=>$arUser['ID'],)
					));
					$val=0;
					$count=0;
					while($el = $rs_data->fetch()){
						$val=$val + $el['UF_RATE'];
						$count = $count + 1;
					}
					$average = round($val/$count*10);
					if($count<1){$average=0;}
					?>

			<?if($average >0):?>
					<?$photoID = $arUser['PERSONAL_PHOTO'];//ищем id фото
					$photoPath = CFile::GetPath($photoID);//ищем путь к фото?>
					<??>
                <div class="results-emp">
                    <div class="results-emp-img" style="background-image: url('<?echo $photoPath;?>'); background-size: cover">
                        
                    </div>
                    <div class="results-emp-scale">
                        <div class="progress">
							<div class="bar" style="width: <?=$average?>%; background-color:var(--color<?=$color?>);"></div>
                        </div>
                        <?=$arUser["NAME"]." ".$arUser["LAST_NAME"]?>
                    </div>
                    <div class="results-emp-value">
                        <?=$average?>%
                    </div>
                </div>
				<?$color++;?>
			<?endif;?>
 				<?endwhile;?>
            </div>

            <div class = "results-elem">
                <div class="results-title">
                    Обучаемость
                </div>
				<?$rsUsers = CUser::GetList(($by="work_department"), ($order="asc"), $filter);?>
				<?$color=1;?>
                <?while($arUser = $rsUsers->GetNext()):?>
					<?// Вывод элементов Highload-блока
					$rs_data = $entity_data_class::getList(array(
					   'select' => array('*'),
						'filter'=>array('UF_MONTH'=>$month,'UF_YEAR'=>$year,'UF_TYPE'=>"O",'UF_EMPLOYEE'=>$arUser['ID'],)
					));
					$val=0;
					$count=0;
					while($el = $rs_data->fetch()){
						$val=$val + $el['UF_RATE'];
						$count = $count + 1;
					}
					$average = round($val/$count*10);
					if($count<1){$average=0;}
					?>
			<?if($average >0):?>
					<?$photoID = $arUser['PERSONAL_PHOTO'];//ищем id фото
					$photoPath = CFile::GetPath($photoID);//ищем путь к фото?>
					<??>
                <div class="results-emp">
                    <div class="results-emp-img" style="background-image: url('<?echo $photoPath;?>'); background-size: cover">
                        
                    </div>
                    <div class="results-emp-scale">
                        <div class="progress">
                            <div class="bar" style="width: <?=$average?>%; background-color:var(--color<?=$color?>);"></div>
                        </div>
                        <?=$arUser["NAME"]." ".$arUser["LAST_NAME"]?>
                    </div>
                    <div class="results-emp-value">
                        <?=$average?>%
                    </div>
                </div>
				<?$color++;?>
			<?endif;?>
 				<?endwhile;?>
            </div>
        </div>
        <div class="results-row">
            <div class = "results-elem">
                <div class="results-title">
                    Полезность
                </div>
                <?$rsUsers = CUser::GetList(($by="work_department"), ($order="asc"), $filter);?>
				<?$color=1;?>
                <?while($arUser = $rsUsers->GetNext()):?>
					<?// Вывод элементов Highload-блока
					$rs_data = $entity_data_class::getList(array(
					   'select' => array('*'),
						'filter'=>array('UF_MONTH'=>$month,'UF_YEAR'=>$year,'UF_TYPE'=>"P",'UF_EMPLOYEE'=>$arUser['ID'],)
					));
					$val=0;
					$count=0;
					while($el = $rs_data->fetch()){
						$val=$val + $el['UF_RATE'];
						$count = $count + 1;
					}
					$average = round($val/$count*10);
					if($count<1){$average=0;}
					?>
				<?if($average >0):?>
					<?$photoID = $arUser['PERSONAL_PHOTO'];//ищем id фото
					$photoPath = CFile::GetPath($photoID);//ищем путь к фото?>
					<??>
                <div class="results-emp">
                    <div class="results-emp-img" style="background-image: url('<?echo $photoPath;?>'); background-size: cover">
                        
                    </div>
                    <div class="results-emp-scale">
                        <div class="progress">
                            <div class="bar" style="width: <?=$average?>%; background-color:var(--color<?=$color?>);"></div>
                        </div>
                        <?=$arUser["NAME"]." ".$arUser["LAST_NAME"]?>
                    </div>
                    <div class="results-emp-value">
                        <?=$average?>%
                    </div>
                </div>
				<?$color++;?>
			<?endif;?>
 				<?endwhile;?>
            </div>
        </div>
	<?endif;?>
    </div>

<div class="navigation">
	<a href="https://b24.opti.ooo/rating/"><button>Сотрудник</button></a>
	<a href="https://b24.opti.ooo/rating/results.php"><button class="active">Команда</button></a>
	<a href="https://b24.opti.ooo/rating/proekty.php"><button>Проекты</button></a>
	<?if($USER->IsAdmin()||$USER->GetID()==5462||$USER->GetID()==3262):?>
		<a href="https://b24.opti.ooo/rating/money.php"><button >Начисления</button></a>
	<?endif;?>
	<?if($USER->GetID()==1||$USER->GetID()==3262):?>
		<a href="https://b24.opti.ooo/rating/yearResults.php"><button>Результаты</button></a>
	<?endif;?>
</div>
</div>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>