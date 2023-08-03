<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Начисления");
echo '<link rel="stylesheet" href="/rating/css/money2.css">';
echo '<link rel="stylesheet" href="/rating/css/nav.css">';
echo '<script type="text/javascript" src="/rating/js/money.js"></script>';
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();
use Bitrix\Highloadblock\HighloadBlockTable as HLBT;
CModule::IncludeModule('highloadblock');
CModule::IncludeModule('support');
global $USER;
?>
<?
$date = getdate();
$tomonth = $date['mon'];//ТЕКУЩИЙ МЕСЯЦ
if($tomonth == 1){
	$lastmonth=12;
}else{$lastmonth = $tomonth-1;}
$months = array( 1 => 'Январь' , 'Февраль' , 'Март' , 'Апрель' , 'Май' , 'Июнь' , 'Июль' , 'Август' , 'Сентябрь' , 'Октябрь' , 'Ноябрь' , 'Декабрь' );

if($_GET['month'] && $_GET['year']){//ЕСЛИ ПЕРЕДАНЫ МЕСЯЦ ГОД
	$month = $_GET['month'];
	$year = $_GET['year'];
}
else{
	$month=$tomonth;
	$year=2023;
}
	$lastday =  cal_days_in_month(CAL_GREGORIAN, $month, $year);//последний день месяца
	$date1 = '1.'.$month.'.'.$year.' 00:00:00';//Дата1 для селекта в функциях
	$date2 = $lastday.'.'.$month.'.'.$year.' 00:00:00';//Дата2 для селекта в функциях
	$monthyear = $month."I".$year;//Для передачи в js через id

	$IS_PAYMENTS=false;//ФЛАГ
	$employess=[];//массив сотрудников
	$sum=0;//сумма
	$taxes=0;//налоги
	$oklades=0; //сумма окладов оф
	$count=0;//количество
	$costhour=0;//стоимость часа

	projectmanager(5867,$date1,$date2);
if($month==$tomonth||$month==$lastmonth){////если выбран текущий или пред месяц
		$highblock_id = 8;//инфоблок с начислениями
		$hl_block = HLBT::getById($highblock_id)->fetch();
		$entity = HLBT::compileEntity($hl_block);
		$entity_data_class = $entity->getDataClass();

		$filter = Array
			(
			"ACTIVE"              => "Y",
			"GROUPS_ID"           => Array(27)
			);//селект активных сотрудников
		$rsUsers = CUser::GetList(($by="work_department"), ($order="asc"), $filter, array('SELECT'=>array("UF_DEPARTMENT")));

		while($arUser = $rsUsers->GetNext()){
			$rs_data = $entity_data_class::getList(array(
			'select' => array('*'),
			'filter'=>array('UF_EMPLOYEE'=> $arUser['ID'],'UF_MONTH'=>$month,'UF_YEAR'=>$year,)
			));
			$ar_data = $rs_data->fetch();
			if($ar_data){
				$emp = array('ID'=> $arUser['ID'], 'OKLAD'=>$ar_data['UF_OKLAD'],'TAX'=>$ar_data['UF_TAX'],
					'AVANS'=>$ar_data['UF_AVANS'],'AWARD'=>$ar_data['UF_AWARD'],'PAYOFF'=>$ar_data['UF_PAYOFF'], 'SAVED'=>true);
			}else{
				if($arUser['UF_DEPARTMENT'][0]==224){
					$sallary=developer($arUser['ID'],$date1,$date2);
					$award=$sallary-18000;
					$emp = array('ID'=> $arUser['ID'], 'OKLAD'=>18000,'TAX'=>2340,
					'AVANS'=>8000,'AWARD'=>$award,'PAYOFF'=>7660, 'SAVED'=>false);
				}elseif($arUser['UF_DEPARTMENT'][0]==122){
					$sallary=projectmanager($arUser['ID'],$date1,$date2);
					$award=$sallary-18000;
					$emp = array('ID'=> $arUser['ID'], 'OKLAD'=>18000,'TAX'=>2340,
					'AVANS'=>8000,'AWARD'=>$award,'PAYOFF'=>7660, 'SAVED'=>false);
				}else{
				$emp = array('ID'=> $arUser['ID'], 'OKLAD'=>18000,'TAX'=>2340,
					'AVANS'=>8000,'AWARD'=>0,'PAYOFF'=>7660, 'SAVED'=>false);
				}
			}
			$sum=$sum+$emp['OKLAD']+$emp['AWARD'];
			$oklades=$oklades+$emp['OKLAD'];
			$count++;
			$employees[]=$emp;//добавляем в массив
		}
		$taxes=$oklades*0.208;
		$IS_PAYMENTS=true;
	}else{//Если выбран другой месяц
		$highblock_id = 8;//инфоблок с начислениями
		$hl_block = HLBT::getById($highblock_id)->fetch();
		$entity = HLBT::compileEntity($hl_block);
		$entity_data_class = $entity->getDataClass();
		//селект начисления месяца
		$rs_data = $entity_data_class::getList(array(
			'select' => array('*'),
			'filter'=>array('UF_MONTH'=>$month,'UF_YEAR'=>$year,)
			));
		while($el = $rs_data->fetch()){//для кажлого начисления месяца
			$emp = array('ID'=> $el['UF_EMPLOYEE'], 'OKLAD'=>$el['UF_OKLAD'],'TAX'=>$el['UF_TAX'],
						'AVANS'=>$el['UF_AVANS'],'AWARD'=>$el['UF_AWARD'],'PAYOFF'=>$el['UF_PAYOFF'], 'SAVED'=>true);
			$sum=$sum+$emp['OKLAD']+$emp['AWARD'];
			$oklades=$oklades+$emp['OKLAD'];
			$count++;
			$employees[]=$emp;//добавляем в массив
		}
		$taxes=round($oklades*0.208,2);
	}
	if($employees){//если были начисления
		$res = CTaskElapsedTime::GetList(
        Array(), 
        Array(">=CREATED_DATE" => $date1,
   				"<=CREATED_DATE" => $date2)
		);
		$minutes = 0;
		while ($arElapsed = $res->Fetch())
		{
			$minutes += $arElapsed["MINUTES"];
		}
		$hours=round($minutes/60);//переводим в часы
		$costhour = round($sum / $hours,2);//считаем стоимость часа
		$IS_PAYMENTS=true;
	}else{
		$sum='---';//сумма
		$taxes='---';//налоги
		$count='---';//количество
		$costhour='---';//стоимость часа
	}


?>
<div class = "conteiner">
    <div class = "upper-inf">
        <div class = "upper-inf-elem">
            <div class="upper-inf-elem-maintext">
                Сумма
                <div class="upper-inf-elem-img">
                        <img src="/rating/imgs/folder-add.png" alt="альтернативный текст">
                </div>
            </div>
            <div class="upper-inf-elem-value">
                <?=number_format($sum, 2, '.','');?>
            </div>
            <div class="upper-inf-elem-maintext">
                Всего начислено
                <div class="upper-inf-elem-lowerimg">
                    <img src="/rating/imgs/Arrow.png" alt="альтернативный текст">
                </div>
            </div>
        </div>
        <div class = "upper-inf-elem">
            <div class="upper-inf-elem-maintext">
                Численность
                <div class="upper-inf-elem-img">
                    <img src="/rating/imgs/users.png" alt="альтернативный текст">
                </div>
            </div>
            <div class="upper-inf-elem-value">
                <?=$count;?>
            </div>
            <div class="upper-inf-elem-maintext">
                Сотрудников в команде
                <div class="upper-inf-elem-lowerimg">
                    <img src="/rating/imgs/Arrow.png" alt="альтернативный текст">
                </div>
            </div>
        </div>
        <div class = "upper-inf-elem">
            <div class="upper-inf-elem-maintext">
                Налоги
                <div class="upper-inf-elem-img">
                        <img src="/rating/imgs/log-out.png" alt="альтернативный текст">
                </div>
            </div>
            <div class="upper-inf-elem-value">
                <?=number_format($taxes, 2, '.','');?>
            </div>
            <div class="upper-inf-elem-maintext">
                Фонды и начисления
                <div class="upper-inf-elem-lowerimg">
                    <img src="/rating/imgs/Arrow.png" alt="альтернативный текст">
                </div>
            </div>
        </div>
        <div class = "upper-inf-elem">
            <div class="upper-inf-elem-maintext">
                Выработка руб./час
                <div class="upper-inf-elem-img">
                        <img src="/rating/imgs/user-add.png" alt="альтернативный текст">
                </div>
            </div>
            <div class="upper-inf-elem-value">
                <?=number_format($costhour, 2, '.','');?>
            </div>
            <div class="upper-inf-elem-maintext">
                Стоимость часа
                <div class="upper-inf-elem-lowerimg">
                    <img src="/rating/imgs/Arrow.png" alt="альтернативный текст">
                </div>
            </div>
        </div>
		<div class = "upper-inf-elem">
            <div class="selectdate">
                <div class="selectdate-con">
                    <select  id="year">
						<option selected style="display:none;"><?=$months[$month]." / ".$year?> </option>
						<option value="1.2023">Январь / 2023</option>
						<option value="2.2023">Февраль / 2023</option>
                    </select>
                </div>            
            </div>
            <button class="update" onclick="update()">Обновить</button>
        </div>
    </div>

    <table class="table">
	<?if($IS_PAYMENTS)://БЫЛИ ЛИ ВЫПЛАТЫ?>
        <tr class="row">
            <td class="column">
                <div class="title-emp">
                    Сотрудник
                </div>
            </td>
            <td class="column">
                <div class="title">
                    Оклад комп.
                </div>
            </td>
            <td class="column">
                <div class="title">
                    Оклад оф.
                </div>
            </td>
            <td class="column">
                <div class="title">
                    Удержано
                </div>
            </td>
            <td class="column">
                <div class="title">
                    Аванс
                </div>
            </td>
            <td class="column">
                <div class="title">
                   К выплате
                </div>
            </td>
            <td class="column">
                <div class="title">
                    Премия
                </div>
            </td>
			<td class="column">
                <div class="title">
                    Сумма
                </div>
            </td>
            <td class="column">
                
            </td>

        </tr>
		<?foreach($employees as $emp):?>
		<?
			$rsUser = CUser::GetByID($emp['ID']);
			$arUser = $rsUser->Fetch();
			$photoID = $arUser['PERSONAL_PHOTO'];//ищем id фото
			$photoPath = CFile::GetPath($photoID);//ищем путь к фото
			$typeofwork = getTypeOfWork($emp['ID']);
switch($typeofwork){
	case 6622:
		$okladcomp = getOklad($emp['ID']);
		break;
	case 6623;
		$okladcomp = getOklad($emp['ID']) / 2;
		break;
	case 6624;
		$okladcomp = getOklad($emp['ID']);
		break;
	case 6625;
		$okladcomp = 0;
		$emp['OKLAD'] = 0;
		$emp['TAX'] = 0;
		$emp['AVANS'] = 0;
		break;
}
		?>
        <tr class="row">
          <td class="column">
            <div class="emp-conteiner">
                <div class="emp-img" style="background-image: url('<?echo $photoPath;?>'); background-size: cover">

                </div>
                <div class="emp-info">
                    <div class="emp-info-name">
                         <?=$arUser["NAME"]." ".$arUser["LAST_NAME"]?>
                    </div>
                    <div class="emp-info-work">
                         <?=$arUser["WORK_POSITION"]?>
                    </div>
                </div>
                <div class="emp-info-atributes">
					<div class="<?if($typeofwork!=6622 && $typeofwork!=6623){echo "emp-info-atributes-hide";}?> emp-info-atributes-of">
						<?if($typeofwork==6622){echo "ОФ 1";}else{echo "ОФ 0.5";}?>
                    </div>
                    <div class="<?if($typeofwork!=6625){echo "emp-info-atributes-hide";}?> emp-info-atributes-p">
                        П
                    </div>
                    <div class="<?if($typeofwork!=6624){echo "emp-info-atributes-hide";}?> emp-info-atributes-st">
                        СТ
                    </div>
                </div>
            </div>
          </td>
          <td class="column">
            <input id="okladcomp-<?=$emp['ID']?>" value="<?=number_format($okladcomp, 2, '.','');//получаем оклад?>">
          </td>
          <td class="column">
            <input id="okladof-<?=$emp['ID']?>" value="<?=number_format($emp['OKLAD'], 2, '.','');?>" onchange="changed(this.id)">
          </td>
          <td class="column">
            <input id="tax-<?=$emp['ID']?>" value="<?=number_format($emp['TAX'], 2, '.','');?>" onchange="changed(this.id)">
          </td>
			<td class="column">
            <input id="avans-<?=$emp['ID']?>" value="<?=number_format($emp['AVANS'], 2, '.','');?>" onchange="changed(this.id)">
          </td>
          <td class="column">
            <input id="payoff-<?=$emp['ID']?>" value="<?=number_format($emp['PAYOFF'], 2, '.','');?>">
          </td>
          <td class="column">
			  <input id="award-<?=$emp['ID']?>" <?if($emp['AWARD']<0):?> style="color: #f93000;"<?endif;?> value="<?=number_format($emp['AWARD'], 2, '.','');?>">
          </td>
          <td class="column">
            <input id="sum-<?=$emp['ID']?>" value="<?=number_format($emp['AWARD']+$emp['OKLAD'], 2, '.','');?>" onchange="changed(this.id)">
          </td>
          <td class="column">
            <button class="save-button" id="<?=$emp['ID']?>" onclick="save(this.id)" <?if($emp['SAVED']){echo 'style="background: #2A824D;"';}?>>
				<?if(!$emp['SAVED']){echo "Сохранить";}else{echo "Изменить";}?>
			</button>
          </td>
        </tr>
		<?endforeach;?>
<?endif;//БЫЛИ ЛИ ВЫПЛАТЫ?>
      </table>

	<button class="upload-button" onclick='addtasks("<?=$monthyear?>")'>Сформировать реестр</button>
	<div class="navigation">
		<a href="https://b24.opti.ooo/rating/"><button>Сотрудник</button></a>
		<a href="https://b24.opti.ooo/rating/results.php"><button >Команда</button></a>
		<?if($USER->IsAdmin()||$USER->GetID()==5462||$USER->GetID()==3262):?>
			<a href="https://b24.opti.ooo/rating/money.php"><button class="active">Начисления</button></a>
			<a href=""><button >Проекты</button></a>
		<?endif;?>
	</div>
</div>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>

<?//ФУНКЦИИ РАСЧЕТА ЗП
function getTypeOfWork($id){
	$rsUser = CUser::GetByID($id);
	$arUser = $rsUser->Fetch();
	return $arUser['UF_TYPE_OF_WORK'];
}
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

	//Достаем и считаем количество завершенных задач
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
		if($arTask['MARK']=="P"){$P=1.1;}
        $completed = $completed + 1;
    }
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
        $completed = $completed + 1;
    }
	//Считаем N в зависимости от колва завершенных задач
	if($completed<5){$N=0.5;}
	elseif($completed<10){$N=0.6;}
	elseif($completed<15){$N=0.7;}
	elseif($completed<20){$N=0.8;}
	elseif($completed<30){$N=0.9;}
	else{$N=1;}

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

	$sallary = $K*$H*$N*$P*$T+$C;
	return $sallary;
}

//Специалисты ТП
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

	//Достаем и считаем количество завершенных сделок
	$res = CCrmDeal::GetList(
        Array('DATE_CREATE' => 'DESC'), 
		Array("ASSIGNED_BY_ID" => $id, "STAGE_SEMANTIC_ID" => 'S', "CLOSED" => 'Y',
			">=CLOSEDATE" => $date1,
   				"<=CLOSEDATE" => $date2,)
    );
	while($arDeal=$res->GetNext()){
		$D++;
		$Ds+=$arDeal['OPPORTUNITY'];
 	}

	//Достаем и считаем количество активных сделок
	$workdeals=0;
	$res = CCrmDeal::GetList(
        Array('DATE_CREATE' => 'DESC'), 
		Array("ASSIGNED_BY_ID" => $id, "CLOSED" => 'N',
			">=DATE_CREATE" => $date1,
   				"<=DATE_CREATE" => $date2,)
    );
	while($arDeal=$res->GetNext()){
		$workdeals++;
 	}
	if($workdeals>10){$Dk=1;}else{$Dk=0.9;}

	//Достаем и считаем количество завершенных задач
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
		if($arTask['MARK']=="P"){$P=1.1;}
        $completed = $completed + 1;
    }
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
        $completed = $completed + 1;
    }
	//Считаем N в зависимости от колва завершенных задач
	if($completed<5){$N=0.5;}
	elseif($completed<10){$N=0.6;}
	elseif($completed<15){$N=0.7;}
	elseif($completed<20){$N=0.8;}
	elseif($completed<30){$N=0.9;}
	else{$N=1;}

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

	$sallary=($O+($Ds*0.1))*($Dk*$N*$Hp*$P*$T);
	//echo "Оклад ".$O;
	//echo "<br><br>";
	//echo "Сумма ".$Ds;
	//echo "<br><br>";
	//echo "активных сделок ".$workdeals;
	//echo "<br><br>";
	//echo "завершенных задач".$completed;
	//echo "<br><br>";
	//echo "Часы ".$hours;
	//echo "<br><br>";
	//echo $sallary;
	return $sallary;
}
?>
