<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Результаты");
use Bitrix\Highloadblock\HighloadBlockTable as HLBT;
CModule::IncludeModule('highloadblock');
CModule::IncludeModule('support');
echo '<link rel="stylesheet" href="/rating/css/yearResults.css">';
echo '<link rel="stylesheet" href="/rating/css/nav.css">';
?>
<?
$yearsum=0;//выручка текущий год
$yearcommand=0;//команда среднее значение год
$allcountdeals=0; //колво выполненных сделок за всё время
$yeardevhours=0;//Часы разработки год
$yearcosthour=0;//Стоимость часа год

$monthsum=0;//выручка текущий месяц
$command=0;//команда текущее значение
$newempcount=0;//новые сотрудники месяц
$kickyearcount=0;//уволенные месяц
$yearcountdeals=0;//колво выполненных сделок за год
$monthcountdeals=0;//колво выполненных сделок за месяц
$monthdevhours=0;//Часы разработки пред месяц
$monthcosthour=0;//Стоимость часа пред месяц
$tomonthdevhours=0;//Часы разработки месяц

$workprojects=0;//колво проектов в работе
$yearfot=0;//Начислено всего, включая налоги
$monthfot=0;//Начислено всего, включая налоги
$workdeals=0;//Кол-во сделок в работе, сумма больше 0

$acts=0;//Сумма работы выполнены
$yeardealssum=0;//выручка год

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
//СЧИТАЕМ КОЛВО СДЕЛОК ВСЕГО
$res = CCrmDeal::GetList(
        Array('DATE_CREATE' => 'DESC'), 
		Array("STAGE_ID" => array('C14:WON', 'C1:WON','WON'), 'CLOSED'=> 'Y'),
		Array("ID")
    );
	while($arDeal=$res->GetNext()){
		$allcountdeals++;
 	}

//СЧИТАЕМ ВЫРУЧКУ ГОД
$res = CCrmDeal::GetList(
        Array('DATE_CREATE' => 'DESC'), 
		Array("STAGE_ID" => array('C14:WON', 'C1:WON','WON'), 'CLOSED'=> 'Y',
			">=CLOSEDATE" => $yeardate1,
   				"<=CLOSEDATE" => $yeardate2)
    );
	while($arDeal=$res->GetNext()){
		$yearsum+=$arDeal['OPPORTUNITY'];
		$yearcountdeals++;
 	}
$yeardealssum=$yearsum;
//СЧИТАЕМ ВЫРУЧКУ МЕСЯЦ
$res = CCrmDeal::GetList(
        Array('DATE_CREATE' => 'DESC'), 
		Array("STAGE_ID" => array('C14:WON', 'C1:WON','WON'), 'CLOSED'=> 'Y',
			">=CLOSEDATE" => $monthdate1,
   				"<=CLOSEDATE" => $monthdate2)
    );
	while($arDeal=$res->GetNext()){
		$monthsum+=$arDeal['OPPORTUNITY'];
		$monthcountdeals++;
 	}
//СЧИТАЕМ СУММУ СДЕЛОК В СТАТУСЕ "ПРОЕКТ В РАБОТЕ"
$res = CCrmDeal::GetList(
        Array('DATE_CREATE' => 'DESC'), 
		Array("STAGE_ID" => array('3'))
    );
	while($arDeal=$res->GetNext()){
		$workprojects+=$arDeal['OPPORTUNITY'];
		if($arDeal['OPPORTUNITY']>0){
			$workdeals++;
		}
 	}

//СЧИТАЕМ СУММУ СДЕЛОК В СТАТУСЕ "РАБОТЫ ВЫПОЛНЕНЫ"
$res = CCrmDeal::GetList(
        Array('DATE_CREATE' => 'DESC'), 
		Array("STAGE_ID" => array('9'))
    );
	while($arDeal=$res->GetNext()){
		$workprojects+=$arDeal['OPPORTUNITY'];
		if($arDeal['OPPORTUNITY']>0){
			$acts+=$arDeal['OPPORTUNITY'];
		}
 	}

//СЧИТАЕМ ЛИЦЕНЗИИ
$res = CCrmDeal::GetList(
        Array('DATE_CREATE' => 'DESC'), 
		Array("STAGE_ID" => array('C14:WON', 'C1:WON','WON'), 'CLOSED'=> 'Y',
			">=CLOSEDATE" => $yeardate1,
   				"<=CLOSEDATE" => $yeardate2,
				">UF_CRM_1565116630"=>0)
    );
	while($arDeal=$res->GetNext()){
		$licenses+=$arDeal['UF_CRM_1565116630'];
 	}

//СЧИТАЕМ ФОТ ГОД

$highblock_id = 8;//инфоблок с начислениями
$hl_block = HLBT::getById($highblock_id)->fetch();
$entity = HLBT::compileEntity($hl_block);
$entity_data_class = $entity->getDataClass();
//селект начисления месяца
$rs_data = $entity_data_class::getList(array(
	'select' => array('*'),
	'filter'=>array('UF_YEAR'=>$toyear,)
	));
while($el = $rs_data->fetch()){//для кажлого начисления месяца
	$yearfot+=$el['UF_OKLAD']+$el['UF_AWARD']+$el['UF_HOLIDAY']+$el['UF_HOSPITAL']+$el['UF_TAX'];
	if($el["UF_MONTH"]==$fotmonth){
		$monthfot+=$el['UF_OKLAD']+$el['UF_AWARD']+$el['UF_HOLIDAY']+$el['UF_HOSPITAL']+$el['UF_TAX'];
	}
}


//СЧИТАЕПМ ЧАСЫ РАЗРАБОТКИ ГОД
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
$res = CTaskElapsedTime::GetList(
        Array(), 
        Array("TASK_ID"=>$projectTasks,
				">=CREATED_DATE" => $yeardate1,
   				"<=CREATED_DATE" => $yeardate2)
		);
	$minutes = 0;
	while ($arElapsed = $res->Fetch())
	{
		$minutes += $arElapsed["MINUTES"];
	}
	$yeardevhours=round($minutes/60);//переводим в часы
	$yearcosthour = round($yearfot / $yeardevhours,2);//считаем стоимость часа

//СЧИТАЕПМ ЧАСЫ РАЗРАБОТКИ МЕСЯЦ
$res = CTaskElapsedTime::GetList(
        Array(), 
        Array("TASK_ID"=>$projectTasks,
				">=CREATED_DATE" => $monthdate1,
   				"<=CREATED_DATE" => $monthdate2)
		);
	$minutes = 0;
	while ($arElapsed = $res->Fetch())
	{
		$minutes += $arElapsed["MINUTES"];
	}
	$tomonthdevhours=round($minutes/60);//переводим в часы

//СЧИТАЕПМ ЧАСЫ РАЗРАБОТКИ ПРЕД МЕСЯЦ
$res = CTaskElapsedTime::GetList(
        Array(), 
        Array("TASK_ID"=>$projectTasks,
				">=CREATED_DATE" => $fotdate1,
   				"<=CREATED_DATE" => $fotdate2)
		);
	$minutes = 0;
	while ($arElapsed = $res->Fetch())
	{
		$minutes += $arElapsed["MINUTES"];
	}
	$monthdevhours=round($minutes/60);//переводим в часы
	$monthcosthour = round($monthfot / $monthdevhours,2);//считаем стоимость часа

//ЧИСЛЕННОСТЬ
$filter = Array
	(
	"ACTIVE"=>'Y',
	"GROUPS_ID"           => Array(27)
	);//селект активных сотрудников
$rsUsers = CUser::GetList(($by="last_name"), ($order="asc"), $filter, array('SELECT'=>array("ID")));

while($arUser = $rsUsers->GetNext()){
	$command++;
}

$emps=0;
$highblock_id = 8;//инфоблок с начислениями
$hl_block = HLBT::getById($highblock_id)->fetch();
$entity = HLBT::compileEntity($hl_block);
$entity_data_class = $entity->getDataClass();
//селект начисления месяца
$rs_data = $entity_data_class::getList(array(
	'select' => array('*'),
	'filter'=>array('UF_YEAR'=>$toyear,)
	));
while($el = $rs_data->fetch()){//для кажлого начисления месяца
	$emps++;
}

$yearcommand=round($emps/$tomonth);
?>

<div class = "conteiner">
    <div class = "upper-inf">
		<div class = "upper-inf-elem" style="background:#282828;">
		<!--1СТРОКА-->
            <div class="upper-inf-elem-maintext">
                <a style="color: #DFEBE9;" target="_blank" href="https://b24.opti.ooo/rating/yearResultsOtladka.php?TYPE=yeardeals">
					Выручка, ₽
				</a>
                <div class="upper-inf-elem-img">
                        <img src="/rating/imgs/folder-add.png" alt="альтернативный текст">
                </div>
            </div>
            <div class="upper-inf-elem-value">
                <?=number_format($yearsum, 2, '.','');?>
            </div>
            <div class="upper-inf-elem-maintext">
                Текущий год
            </div>
        </div>

        <div class = "upper-inf-elem" style="background:#D74747;">
            <div class="upper-inf-elem-maintext">
               Команда
                <div class="upper-inf-elem-img">
                    <img src="/rating/imgs/users.png" alt="альтернативный текст">
                </div>
            </div>
            <div class="upper-inf-elem-value">
                <?=$yearcommand;?>
            </div>
            <div class="upper-inf-elem-maintext">
                Год (ср.знач.)
            </div>
        </div>

        <div class = "upper-inf-elem" style="background:#005893;">
            <div class="upper-inf-elem-maintext">
				<a style="color: #DFEBE9;" target="_blank" href="https://b24.opti.ooo/rating/TEST/licenses.php?year=<?=$year?>&month=<?=$month?>">
                	Сделки
				</a>
                <div class="upper-inf-elem-img">
                        <img src="/rating/imgs/log-out.png" alt="альтернативный текст">
                </div>
            </div>
            <div class="upper-inf-elem-value">
                <?=$allcountdeals;?>
            </div>
            <div class="upper-inf-elem-maintext">
                Выполнено всего
            </div>
        </div>

        <div class = "upper-inf-elem" style="background:#142647;">
            <div class="upper-inf-elem-maintext">
                Часы разработки
                <div class="upper-inf-elem-img">
                    <img src="/rating/imgs/users.png" alt="альтернативный текст">
                </div>
            </div>
            <div class="upper-inf-elem-value">
                <?=$yeardevhours;?>
            </div>
            <div class="upper-inf-elem-maintext">
                Текущий год
            </div>
        </div>

        <div class = "upper-inf-elem" style="background:#142647;">
            <div class="upper-inf-elem-maintext">
                Выработка руб./час
                <div class="upper-inf-elem-img">
                        <img src="/rating/imgs/user-add.png" alt="альтернативный текст">
                </div>
            </div>
            <div class="upper-inf-elem-value">
                <?=number_format($yearcosthour, 2, '.','');?>
            </div>
            <div class="upper-inf-elem-maintext">
                Стоимость часа(год)
            </div>
        </div>

	<!--2СТРОКА-->

		<div class = "upper-inf-elem" style="background:#EAB223;">
			<div class="upper-inf-elem-maintext">
                <a style="color: #DFEBE9;" target="_blank" href="https://b24.opti.ooo/rating/yearResultsOtladka.php?TYPE=monthdeals">
					Выручка, ₽
				</a>
                <div class="upper-inf-elem-img">
                        <img src="/rating/imgs/folder-add.png" alt="альтернативный текст">
                </div>
            </div>
            <div class="upper-inf-elem-value">
                <?=number_format($monthsum, 2, '.','');?>
            </div>
            <div class="upper-inf-elem-maintext">
                Текущий месяц
            </div>
        </div>

        <div class = "upper-inf-elem" style="background:#D74747;">
            <div class="upper-inf-elem-maintext">
               Численность
                <div class="upper-inf-elem-img">
                    <img src="/rating/imgs/users.png" alt="альтернативный текст">
                </div>
            </div>
            <div class="upper-inf-elem-value">
                <?=$command;?>
            </div>
            <div class="upper-inf-elem-maintext">
                Сотрудников в команде
            </div>
        </div>

        <div class = "upper-inf-elem" style="background:#005893;">
            <div class="upper-inf-elem-maintext">
				<a style="color: #DFEBE9;" target="_blank" href="">
                	Сделки
				</a>
                <div class="upper-inf-elem-img">
                        <img src="/rating/imgs/log-out.png" alt="альтернативный текст">
                </div>
            </div>
            <div class="upper-inf-elem-value">
				<?=$yearcountdeals."/".$monthcountdeals;?>
            </div>
            <div class="upper-inf-elem-maintext">
				Год/месяц
            </div>
        </div>

        <div class = "upper-inf-elem" style="background:#142647;">
            <div class="upper-inf-elem-maintext">
                Часы разработки
                <div class="upper-inf-elem-img">
                    <img src="/rating/imgs/users.png" alt="альтернативный текст">
                </div>
            </div>
            <div class="upper-inf-elem-value">
                <?=$monthdevhours;?>
            </div>
            <div class="upper-inf-elem-maintext">
                Предыдущий месяц
            </div>
        </div>

        <div class = "upper-inf-elem" style="background:#142647;">
            <div class="upper-inf-elem-maintext">
                Выработка руб./час
                <div class="upper-inf-elem-img">
                        <img src="/rating/imgs/user-add.png" alt="альтернативный текст">
                </div>
            </div>
            <div class="upper-inf-elem-value">
                <?=number_format($monthcosthour, 2, '.','');?>
            </div>
            <div class="upper-inf-elem-maintext">
                Стоимость часа(пред.месяц)
            </div>
        </div>
		<!--3СТРОКА-->
		<div class = "upper-inf-elem" style="background:#D74747;">
			<div class="upper-inf-elem-maintext">
                <a style="color: #DFEBE9;" target="_blank" href="https://b24.opti.ooo/rating/yearResultsOtladka.php?TYPE=projectdeals">
					Проекты, ₽
				</a>
                <div class="upper-inf-elem-img">
                        <img src="/rating/imgs/folder-add.png" alt="альтернативный текст">
                </div>
            </div>
            <div class="upper-inf-elem-value">
                <?=number_format($workprojects, 2, '.','');?>
            </div>
            <div class="upper-inf-elem-maintext">
                Всего в работе
            </div>
        </div>

        <div class = "upper-inf-elem" style="background:#FC730C;">
            <div class="upper-inf-elem-maintext">
               ФОТ
                <div class="upper-inf-elem-img">
                    <img src="/rating/imgs/users.png" alt="альтернативный текст">
                </div>
            </div>
            <div class="upper-inf-elem-value">
                <?=number_format($yearfot, 2, '.','');?>
            </div>
            <div class="upper-inf-elem-maintext">
                Начислено, в т.ч. налоги
            </div>
        </div>

        <div class = "upper-inf-elem" style="background:#005893;">
            <div class="upper-inf-elem-maintext">
				<a style="color: #DFEBE9;" target="_blank" href="https://b24.opti.ooo/rating/TEST/licenses.php?year=<?=$year?>&month=<?=$month?>">
                	Сделки
				</a>
                <div class="upper-inf-elem-img">
                        <img src="/rating/imgs/log-out.png" alt="альтернативный текст">
                </div>
            </div>
            <div class="upper-inf-elem-value">
                <?=$workdeals;?>
            </div>
            <div class="upper-inf-elem-maintext">
                В работе (сумма больше 0)
            </div>
        </div>

		<div class = "upper-inf-elem" style="background:#142647;">
            <div class="upper-inf-elem-maintext">
                Часы разработки
                <div class="upper-inf-elem-img">
                    <img src="/rating/imgs/users.png" alt="альтернативный текст">
                </div>
            </div>
            <div class="upper-inf-elem-value">
                <?=$tomonthdevhours;?>
            </div>
            <div class="upper-inf-elem-maintext">
                Текущий месяц
            </div>
        </div>

        <div class = "upper-inf-elem" style="background:none;"></div>

		<!--4СТРОКА-->
		<div class = "upper-inf-elem" style="background:#696969;">
			<div class="upper-inf-elem-maintext">
                <a style="color: #DFEBE9;" target="_blank" href="https://b24.opti.ooo/rating/yearResultsOtladka.php?TYPE=actsdeals">
					Акты, ₽
				</a>
                <div class="upper-inf-elem-img">
                        <img src="/rating/imgs/folder-add.png" alt="альтернативный текст">
                </div>
            </div>
            <div class="upper-inf-elem-value">
                <?=number_format($acts, 2, '.','');?>
            </div>
            <div class="upper-inf-elem-maintext">
                Работы выполнены
            </div>
        </div>

        <div class = "upper-inf-elem" style="background:#0BBBEF;">
			<div class="upper-inf-elem-maintext">
				<a style="color: #DFEBE9;" target="_blank" href="https://b24.opti.ooo/rating/yearResultsOtladka.php?TYPE=licenses">
                	Лицензии
				</a>
                <div class="upper-inf-elem-img">
                        <img src="/rating/imgs/log-out.png" alt="альтернативный текст">
                </div>
            </div>
            <div class="upper-inf-elem-value">
                <?=number_format($licenses, 2, '.','');?>
			</div>
            <div class="upper-inf-elem-maintext">
                Сумма за год
            </div>
		</div>

        <div class = "upper-inf-elem" style="background:#2A824D;">
            <div class="upper-inf-elem-maintext">
				<a style="color: #DFEBE9;" target="_blank" href="https://b24.opti.ooo/rating/TEST/licenses.php?year=<?=$year?>&month=<?=$month?>">
                	Сделки
				</a>
                <div class="upper-inf-elem-img">
                        <img src="/rating/imgs/log-out.png" alt="альтернативный текст">
                </div>
            </div>
            <div class="upper-inf-elem-value">
                <?=number_format($yeardealssum, 2, '.','');?>
			</div>
            <div class="upper-inf-elem-maintext">
                Сумма за год
            </div>
        </div>

        <div class = "upper-inf-elem" style="background:none;"></div>

        <div class = "upper-inf-elem" style="background:none;"></div>
    </div>
	<div class="navigation">
		<a href="https://b24.opti.ooo/rating/"><button >Сотрудник</button></a>
		<a href="https://b24.opti.ooo/rating/results.php"><button >Команда</button></a>
		<a href="https://b24.opti.ooo/rating/proekty.php"><button >Проекты</button></a>
		<?if($USER->IsAdmin()||$USER->GetID()==5462||$USER->GetID()==3262):?>
			<a href="https://b24.opti.ooo/rating/money.php"><button >Начисления</button></a>
		<?endif;?>
		<?if($USER->GetID()==1||$USER->GetID()==3262):?>
			<a href="https://b24.opti.ooo/rating/yearResults.php"><button class="active">Результаты</button></a>
		<?endif;?>
	</div>
</div>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>