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
	$factmonth = $_GET['month'];
	$factyear = $_GET['year'];
}
else{
	$factmonth=$tomonth;
	$factyear=2023;
}
$month=$factmonth-1;
if($month == 0){$month=12;$year=$factyear-1;}else{$year = $factyear;}
	$lastday =  cal_days_in_month(CAL_GREGORIAN, $month, $year);//последний день месяца
	$date1 = '1.'.$month.'.'.$year.' 00:00:00';//Дата1 для селекта в функциях, предыдущий от выбранного месяц
	$date2 = $lastday.'.'.$month.'.'.$year.' 00:00:00';//Дата2 для селекта в функциях, предыдущий от выбранного месяц

	$lastday4 =  cal_days_in_month(CAL_GREGORIAN, $factmonth, $factyear);//последний день месяца
	$date3 = '1.'.$factmonth.'.'.$factyear.' 00:00:00';//Дата3 для селекта сотрудников, фактический месяц
	$date4 = $lastday4.'.'.$factmonth.'.'.$factyear.' 00:00:00';//Дата4 для селекта сотрудников, фактический месяц

	$monthyear = $month."I".$year;//Для передачи в js через id
	$IS_PAYMENTS=false;//ФЛАГ
	$employess=[];//массив сотрудников
	$sum=0;//сумма
	$licenses=0;//лицензии
	$dealssumm=0;//сумма сделок месяца
	$taxes=0;//налоги
	$oklades=0; //сумма окладов оф
	$count=0;//количество
	$costhour=0;//стоимость часа

//Ищем отдел ТП и находим айди руководителя
	$arFilter = Array('IBLOCK_ID'=>1, 'GLOBAL_ACTIVE'=>'Y', 'SECTION_ID'=>1, "ID"=>75);
	$sections = CIBlockSection::GetList(Array("SORT"=>"ASC"), $arFilter, true, Array("UF_HEAD"));
	$TPsection = $sections->GetNext();
	$headTP_ID =  $TPsection["UF_HEAD"];//Айди руководителя ТП

	//Ищем отдел Продаж и находим айди руководителя
	$arFilter = Array('IBLOCK_ID'=>1, 'GLOBAL_ACTIVE'=>'Y', 'SECTION_ID'=>1, "ID"=>92);
	$sections = CIBlockSection::GetList(Array("SORT"=>"ASC"), $arFilter, true, Array("UF_HEAD"));
	$OPsection = $sections->GetNext();
	$headOP_ID =  $OPsection["UF_HEAD"];//Айди руководителя ТП

if($month==$tomonth||$month==$tomonth-1 ||$month==$tomonth-2 || $month==$tomonth-3){////если выбран текущий или пред месяц
		$highblock_id = 8;//инфоблок с начислениями
		$hl_block = HLBT::getById($highblock_id)->fetch();
		$entity = HLBT::compileEntity($hl_block);
		$entity_data_class = $entity->getDataClass();


	//ДЛЯ АКТИВНЫХ СОТРУДНИКОВ
		$filter = Array
			(
			"DATE_REGISTER_2"=>$date4,
			"ACTIVE"=>'Y',
			"GROUPS_ID"           => Array(27)
			);//селект активных сотрудников
		$rsUsers = CUser::GetList(($by="last_name"), ($order="asc"), $filter, array('SELECT'=>array("UF_DEPARTMENT")));

		while($arUser = $rsUsers->GetNext()){
			if($arUser["ACTIVE"]=="Y" || isHours($arUser["ID"], $date1, $date2)){
				$rs_data = $entity_data_class::getList(array(
				'select' => array('*'),
				'filter'=>array('UF_EMPLOYEE'=> $arUser['ID'],'UF_MONTH'=>$month,'UF_YEAR'=>$year,)
				));
				$ar_data = $rs_data->fetch();
				if($ar_data){
					if($arUser["ID"]==1){
						$type="BOSS";
					}elseif($arUser["ID"]==$headTP_ID){
						$type="HEADTP";
					}elseif($arUser["ID"]==$headOP_ID){
						$type="HEADOP";
					}elseif($arUser['UF_DEPARTMENT'][0]==224){
						$type="DEVELOPER";
					}elseif($arUser['UF_DEPARTMENT'][0]==283){
						$type="VERSTKA";
					}elseif($arUser['UF_DEPARTMENT'][0]==122){
						$type="PMANAGER";
					}elseif($arUser['UF_DEPARTMENT'][0]==75 || $arUser['UF_DEPARTMENT'][0]==280 && $arUser["ID"]!=4223){
						$type="TP";
					}else{
						$type="INFO";
					}
					$emp = array('ID'=> $arUser['ID'], 'OKLAD'=>$ar_data['UF_OKLAD'],'TAX'=>$ar_data['UF_TAX'],
						'AVANS'=>$ar_data['UF_AVANS'],'AWARD'=>$ar_data['UF_AWARD'],'PAYOFF'=>$ar_data['UF_PAYOFF'],'HOSPITAL'=>$ar_data['UF_HOSPITAL'],'HOLIDAY'=>$ar_data['UF_HOLIDAY'], 'SAVED'=>true, 'TYPE'=>$type);
				}else{
					if($arUser["ID"]==1){
						$sallary=boss($date1,$date2);
						$O=19000;
						$award=$sallary-$O;
						$emp = array('ID'=> $arUser['ID'], 'OKLAD'=>$O,'TAX'=>2470,
						'AVANS'=>8000,'AWARD'=>$award,'PAYOFF'=>7660, 'HOSPITAL'=>0,'HOLIDAY'=>0,'SAVED'=>false,'TYPE'=>"BOSS");
					}elseif($arUser["ID"]==$headTP_ID){
						$sallary=headTP($arUser['ID'],$date1,$date2);
						$O=24000;
						$award=$sallary-$O;
						$emp = array('ID'=> $arUser['ID'], 'OKLAD'=>$O,'TAX'=>3120,
						'AVANS'=>8000,'AWARD'=>$award,'PAYOFF'=>7660,'HOSPITAL'=>0,'HOLIDAY'=>0, 'SAVED'=>false,'TYPE'=>"HEADTP");
					}elseif($arUser["ID"]==$headOP_ID){
						$sallary=rop($arUser['ID'],$date1,$date2);
						$O=19000;
						$award=$sallary-$O;
						$emp = array('ID'=> $arUser['ID'], 'OKLAD'=>$O,'TAX'=>2470,
						'AVANS'=>8000,'AWARD'=>$award,'PAYOFF'=>7660,'HOSPITAL'=>0,'HOLIDAY'=>0, 'SAVED'=>false,'TYPE'=>"HEADOP");
					}elseif($arUser['UF_DEPARTMENT'][0]==224){
						$sallary=developer($arUser['ID'],$date1,$date2);
						$O=19000;
						$award=$sallary-$O;
						$emp = array('ID'=> $arUser['ID'], 'OKLAD'=>$O,'TAX'=>2470,
						'AVANS'=>8000,'AWARD'=>$award,'PAYOFF'=>7660,'HOSPITAL'=>0,'HOLIDAY'=>0, 'SAVED'=>false,'TYPE'=>"DEVELOPER");
					}elseif($arUser['UF_DEPARTMENT'][0]==283){
						$sallary=verstka($arUser['ID'],$date1,$date2);
						$O=19000;
						$award=$sallary-$O;
						$emp = array('ID'=> $arUser['ID'], 'OKLAD'=>$O,'TAX'=>2470,
						'AVANS'=>8000,'AWARD'=>$award,'PAYOFF'=>7660,'HOSPITAL'=>0,'HOLIDAY'=>0, 'SAVED'=>false,'TYPE'=>"VERSTKA");
					}elseif($arUser['UF_DEPARTMENT'][0]==122){
						$sallary=projectmanager($arUser['ID'],$date1,$date2);
						$O=24000;
						$award=$sallary-$O;
						$emp = array('ID'=> $arUser['ID'], 'OKLAD'=>$O,'TAX'=>3120,
						'AVANS'=>8000,'AWARD'=>$award,'PAYOFF'=>7660,'HOSPITAL'=>0,'HOLIDAY'=>0, 'SAVED'=>false,'TYPE'=>"PMANAGER");
					}elseif($arUser['UF_DEPARTMENT'][0]==75 || $arUser['UF_DEPARTMENT'][0]==280 && $arUser["ID"]!=4223){
						$sallary=TP($arUser['ID'],$date1,$date2);
						$O=19000;
						$award=$sallary-$O;
						$emp = array('ID'=> $arUser['ID'], 'OKLAD'=>$O,'TAX'=>2470,
						'AVANS'=>8000,'AWARD'=>$award,'PAYOFF'=>7660,'HOSPITAL'=>0,'HOLIDAY'=>0, 'SAVED'=>false,'TYPE'=>"TP");
					}else{
					$emp = array('ID'=> $arUser['ID'], 'OKLAD'=>19000,'TAX'=>2470,
						'AVANS'=>8000,'AWARD'=>0,'PAYOFF'=>7660,'HOSPITAL'=>0,'HOLIDAY'=>0, 'SAVED'=>false, 'TYPE'=>"INFO");
					}
					if(getTypeOfWork($emp['ID'])==6625){
						$emp['OKLAD']=0;
						$emp['TAX']=0;
						$emp['AVANS']=0;
						$emp['AWARD']=0;
						$emp['PAYOFF']=0;
						$emp['HOSPITAL']=0;
						$emp['HOLIDAY']=0;
					}
				}
				$sum=$sum+$emp['OKLAD']+$emp['AWARD']+$emp['HOLIDAY']+$emp['HOSPITAL'];
				$oklades=$oklades+$emp['OKLAD'];
				$count++;
				$employees[]=$emp;//добавляем в массив
			}
		}

		//ДЛЯ УВОЛЕННЫХ СОТРУДНИКОВ
		$filter = Array
			(
			"ACTIVE"=>'N',
			">=UF_DATE_DISMISSAL" => $date3,
			"GROUPS_ID"           => Array(27)
			);//селект активных сотрудников
		$rsUsers = CUser::GetList(($by="last_name"), ($order="asc"), $filter, array('SELECT'=>array("*","UF_*")));

		while($arUser = $rsUsers->GetNext()){
			//if(isHours($arUser["ID"], $date1, $date2)){
				$rs_data = $entity_data_class::getList(array(
				'select' => array('*'),
				'filter'=>array('UF_EMPLOYEE'=> $arUser['ID'],'UF_MONTH'=>$month,'UF_YEAR'=>$year,)
				));
				$ar_data = $rs_data->fetch();
				if($ar_data){
					if($arUser["ID"]==1){
						$type="BOSS";
					}elseif($arUser["ID"]==$headTP_ID){
						$type="HEADTP";
					}elseif($arUser["ID"]==$headOP_ID){
						$type="HEADOP";
					}elseif($arUser['UF_DEPARTMENT'][0]==224){
						$type="DEVELOPER";
					}elseif($arUser['UF_DEPARTMENT'][0]==283){
						$type="VERSTKA";
					}elseif($arUser['UF_DEPARTMENT'][0]==122){
						$type="PMANAGER";
					}elseif($arUser['UF_DEPARTMENT'][0]==75 || $arUser['UF_DEPARTMENT'][0]==280 && $arUser["ID"]!=4223){
						$type="TP";
					}else{
						$type="INFO";
					}
					$emp = array('ID'=> $arUser['ID'], 'OKLAD'=>$ar_data['UF_OKLAD'],'TAX'=>$ar_data['UF_TAX'],
						'AVANS'=>$ar_data['UF_AVANS'],'AWARD'=>$ar_data['UF_AWARD'],'PAYOFF'=>$ar_data['UF_PAYOFF'],'HOSPITAL'=>$ar_data['UF_HOSPITAL'],'HOLIDAY'=>$ar_data['UF_HOLIDAY'], 'SAVED'=>true, 'TYPE'=>$type);
				}else{
					if($arUser["ID"]==1){
						$sallary=boss($date1,$date2);
						$O=19000;
						$award=$sallary-$O;
						$emp = array('ID'=> $arUser['ID'], 'OKLAD'=>$O,'TAX'=>2470,
						'AVANS'=>8000,'AWARD'=>$award,'PAYOFF'=>7660, 'HOSPITAL'=>0,'HOLIDAY'=>0,'SAVED'=>false,'TYPE'=>"BOSS");
					}elseif($arUser["ID"]==$headTP_ID){
						$sallary=headTP($arUser['ID'],$date1,$date2);
						$O=24000;
						$award=$sallary-$O;
						$emp = array('ID'=> $arUser['ID'], 'OKLAD'=>$O,'TAX'=>3120,
						'AVANS'=>8000,'AWARD'=>$award,'PAYOFF'=>7660,'HOSPITAL'=>0,'HOLIDAY'=>0, 'SAVED'=>false,'TYPE'=>"HEADTP");
					}elseif($arUser["ID"]==$headOP_ID){
						$sallary=rop($arUser['ID'],$date1,$date2);
						$O=19000;
						$award=$sallary-$O;
						$emp = array('ID'=> $arUser['ID'], 'OKLAD'=>$O,'TAX'=>2470,
						'AVANS'=>8000,'AWARD'=>$award,'PAYOFF'=>7660,'HOSPITAL'=>0,'HOLIDAY'=>0, 'SAVED'=>false,'TYPE'=>"HEADOP");
					}elseif($arUser['UF_DEPARTMENT'][0]==224){
						$sallary=developer($arUser['ID'],$date1,$date2);
						$O=19000;
						$award=$sallary-$O;
						$emp = array('ID'=> $arUser['ID'], 'OKLAD'=>$O,'TAX'=>2470,
						'AVANS'=>8000,'AWARD'=>$award,'PAYOFF'=>7660,'HOSPITAL'=>0,'HOLIDAY'=>0, 'SAVED'=>false,'TYPE'=>"DEVELOPER");
					}elseif($arUser['UF_DEPARTMENT'][0]==283){
						$sallary=verstka($arUser['ID'],$date1,$date2);
						$O=19000;
						$award=$sallary-$O;
						$emp = array('ID'=> $arUser['ID'], 'OKLAD'=>$O,'TAX'=>2470,
						'AVANS'=>8000,'AWARD'=>$award,'PAYOFF'=>7660,'HOSPITAL'=>0,'HOLIDAY'=>0, 'SAVED'=>false,'TYPE'=>"VERSTKA");
					}elseif($arUser['UF_DEPARTMENT'][0]==122){
						$sallary=projectmanager($arUser['ID'],$date1,$date2);
						$O=24000;
						$award=$sallary-$O;
						$emp = array('ID'=> $arUser['ID'], 'OKLAD'=>$O,'TAX'=>3120,
						'AVANS'=>8000,'AWARD'=>$award,'PAYOFF'=>7660,'HOSPITAL'=>0,'HOLIDAY'=>0, 'SAVED'=>false,'TYPE'=>"PMANAGER");
					}elseif($arUser['UF_DEPARTMENT'][0]==75 || $arUser['UF_DEPARTMENT'][0]==280 && $arUser["ID"]!=4223){
						$sallary=TP($arUser['ID'],$date1,$date2);
						$O=19000;
						$award=$sallary-$O;
						$emp = array('ID'=> $arUser['ID'], 'OKLAD'=>$O,'TAX'=>2470,
						'AVANS'=>8000,'AWARD'=>$award,'PAYOFF'=>7660,'HOSPITAL'=>0,'HOLIDAY'=>0, 'SAVED'=>false,'TYPE'=>"TP");
					}else{
					$emp = array('ID'=> $arUser['ID'], 'OKLAD'=>19000,'TAX'=>2470,
						'AVANS'=>8000,'AWARD'=>0,'PAYOFF'=>7660,'HOSPITAL'=>0,'HOLIDAY'=>0, 'SAVED'=>false, 'TYPE'=>"INFO");
					}
					if(getTypeOfWork($emp['ID'])==6625){
						$emp['OKLAD']=0;
						$emp['TAX']=0;
						$emp['AVANS']=0;
						$emp['AWARD']=0;
						$emp['PAYOFF']=0;
						$emp['HOSPITAL']=0;
						$emp['HOLIDAY']=0;
					}
				}
				$sum=$sum+$emp['OKLAD']+$emp['AWARD']+$emp['HOLIDAY']+$emp['HOSPITAL'];
				$oklades=$oklades+$emp['OKLAD'];
				$count++;
				$employees[]=$emp;//добавляем в массив
				//}
		}
		$taxes=$oklades*0.206;
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
						'AVANS'=>$el['UF_AVANS'],'AWARD'=>$el['UF_AWARD'],'PAYOFF'=>$el['UF_PAYOFF'],'HOSPITAL'=>$el['UF_HOSPITAL'],'HOLIDAY'=>$el['UF_HOLIDAY'], 'SAVED'=>true);
			$sum=$sum+$emp['OKLAD']+$emp['AWARD']+$emp['HOLIDAY']+$emp['HOSPITAL'];
			$oklades=$oklades+$emp['OKLAD'];
			$count++;
			$employees[]=$emp;//добавляем в массив
		}
		$taxes=round($oklades*0.208,2);
	}
//$sortemployees=$employees;
//СОРТИРОВКА
$sortemployees=[];
$head;
$rop;
$sale=[];
$buh;
$helper;
$htp;
$tp=[];
$project=[];
$hdev;
$devs=[];
$verst=[];
$cont=[];
$other=[];
$out=[];

foreach($employees as $e){
	$rsUser = CUser::GetByID($e['ID']);
	$arUser = $rsUser->Fetch();
	$work=$arUser['UF_DEPARTMENT'][0];
	if($e['ID']==1){
		$head=$e;
	}elseif($e['ID']==$headOP_ID){
		$rop=$e;
	}elseif($e['ID']==$headTP_ID){
		$htp=$e;
	}elseif(getTypeOfWork($e['ID'])==6625){
		$out[]=$e;
	}elseif($e['ID']==3262){
		$hdev=$e;
	}elseif($e['ID']==5462){
		$buh=$e;
	}elseif($e['ID']==6325){
		$helper=$e;
	}elseif($work==92){
		$sale[]=$e;
	}elseif($work==75 || $work==280){
		$tp[]=$e;
	}elseif($work==122){
		$project[]=$e;
	}elseif($work==224){
		$devs[]=$e;
	}elseif($work==283){
		$verst[]=$e;
	}else{
		$other[]=$e;
	}
}
if($head) $sortemployees[]=$head;
if($rop) $sortemployees[]=$rop;
if($sale) $sortemployees = array_merge($sortemployees, $sale);//добавляем в массив
if($buh) $sortemployees[]=$buh;
if($helper) $sortemployees[]=$helper;
if($htp) $sortemployees[]=$htp;
if($tp) $sortemployees = array_merge($sortemployees, $tp);//добавляем в массив
if($project) $sortemployees = array_merge($sortemployees, $project);//добавляем в массив
if($hdev) $sortemployees[]=$hdev;
if($devs) $sortemployees = array_merge($sortemployees, $devs);//добавляем в массив
if($verst) $sortemployees = array_merge($sortemployees, $verst);//добавляем в массив
if($cont) $sortemployees = array_merge($sortemployees, $cont);//добавляем в массив
if($other) $sortemployees = array_merge($sortemployees, $other);//добавляем в массив
if($out) $sortemployees = array_merge($sortemployees, $out);//добавляем в массив

	if($sortemployees){//если были начисления
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
//Считаем сумму сделок
$res = CCrmDeal::GetList(
        Array('DATE_CREATE' => 'DESC'), 
		Array("STAGE_ID" => array('C14:WON', 'C1:WON','WON'), 'CLOSED'=> 'Y',
			">=CLOSEDATE" => $date1,
   				"<=CLOSEDATE" => $date2,)
    );
	while($arDeal=$res->GetNext()){
		$dealssumm+=$arDeal['OPPORTUNITY'];
 	}
$res = CCrmDeal::GetList(
        Array('DATE_CREATE' => 'DESC'), 
		Array("STAGE_ID" => array('C14:WON', 'C1:WON','WON'), 'CLOSED'=> 'Y',
			">=CLOSEDATE" => $date1,
   				"<=CLOSEDATE" => $date2,
				">UF_CRM_1565116630"=>0)
    );
	while($arDeal=$res->GetNext()){
		$licenses+=$arDeal['UF_CRM_1565116630'];
 	}
?>
<div class = "conteiner">
    <div class = "upper-inf">
		<div class = "upper-inf-elem">
            <div class="upper-inf-elem-maintext">
                <a style="color: #DFEBE9;" target="_blank" href="https://b24.opti.ooo/rating/TEST/completedDeals.php?year=<?=$year?>&month=<?=$month?>&object=LICENSES">
					Сумма сделок
				</a>
                <div class="upper-inf-elem-img">
                        <img src="/rating/imgs/folder-add.png" alt="альтернативный текст">
                </div>
            </div>
            <div class="upper-inf-elem-value">
                <?=number_format($dealssumm, 2, '.','');?>
            </div>
            <div class="upper-inf-elem-maintext">
                Результаты месяца
                <div class="upper-inf-elem-lowerimg">
                    <img src="/rating/imgs/Arrow.png" alt="альтернативный текст">
                </div>
            </div>
        </div>
        <div class = "upper-inf-elem">
            <div class="upper-inf-elem-maintext">
                ФОТ
                <div class="upper-inf-elem-img">
					<div class="countText"><?=$count?></div>
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
				<a style="color: #DFEBE9;" target="_blank" href="https://b24.opti.ooo/rating/TEST/licenses.php?year=<?=$year?>&month=<?=$month?>">
                	Лицензии
				</a>
                <div class="upper-inf-elem-img">
                    <img src="/rating/imgs/users.png" alt="альтернативный текст">
                </div>
            </div>
            <div class="upper-inf-elem-value">
                <?=$licenses;?>
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
    </div>


<div class="selectdate">
                <div class="selectdate-con">
					<button class="selectdate-btn" onclick="backmonth(<?=$factmonth.",".$factyear?>)">Пред.</button>
						<?=$months[$factmonth]." / ".$factyear?>
					<button class="selectdate-btn" onclick="nextmonth(<?=$factmonth.",".$factyear?>)">След.</button>
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
                    Оклад
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
                <div class="title">
                    БЛ
                </div>
            </td>
			<td class="column">
                <div class="title">
                    🌴
                </div>
            </td>
            <td class="column">
                
            </td>

        </tr>
		<?foreach($sortemployees as $emp):?>
		<?
			$rsUser = CUser::GetByID($emp['ID']);
			$arUser = $rsUser->Fetch();
			$photoID = $arUser['PERSONAL_PHOTO'];//ищем id фото
			$photoPath = CFile::GetPath($photoID);//ищем путь к фото
			$typeofwork = getTypeOfWork($emp['ID']);
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
			  <input id="okladcomp-<?=$emp['ID']?>" value="<?if($typeofwork==6625){echo "0.00";}else{echo number_format(getOklad($emp['ID']), 2, '.','');}//получаем оклад?>">
			<input id="okladcomphide-<?=$emp['ID']?>" type="hidden" value="<?if($typeofwork==6625){echo "0.00";}else{echo number_format(getOklad($emp['ID']), 2, '.','');}//получаем оклад?>">
          </td>
          <td class="column">
            <input id="okladof-<?=$emp['ID']?>" value="<?=number_format($emp['OKLAD'], 2, '.','');?>" onchange="changed(this.id)">
			<input id="okladofhide-<?=$emp['ID']?>" type="hidden" value="<?=number_format($emp['OKLAD'], 2, '.','');?>" onchange="changed(this.id)">
          </td>
          <td class="column">
            <input id="tax-<?=$emp['ID']?>" value="<?=number_format($emp['TAX'], 2, '.','');?>" onchange="changed(this.id)">
			<input id="taxhide-<?=$emp['ID']?>" type="hidden" value="<?=number_format($emp['TAX'], 2, '.','');?>" onchange="changed(this.id)">
          </td>
			<td class="column">
            <input id="avans-<?=$emp['ID']?>" value="<?=number_format($emp['AVANS'], 2, '.','');?>" onchange="changed(this.id)">
			 <input id="avanshide-<?=$emp['ID']?>" type="hidden" value="<?=number_format($emp['AVANS'], 2, '.','');?>" onchange="changed(this.id)">
          </td>
          <td class="column">
            <input id="payoff-<?=$emp['ID']?>" value="<?=number_format($emp['PAYOFF'], 2, '.','');?>">
			<input id="payoffhide-<?=$emp['ID']?>" type="hidden" value="<?=number_format($emp['PAYOFF'], 2, '.','');?>">
          </td>
          <td class="column">
			  <input id="award-<?=$emp['ID']?>" <?if($emp['AWARD']<0):?> style="color: #f93000;"<?endif;?> value="<?=number_format($emp['AWARD'], 2, '.','');?>">
			<input id="awardhide-<?=$emp['ID']?>" type="hidden" <?if($emp['AWARD']<0):?> style="color: #f93000;"<?endif;?> value="<?=number_format($emp['AWARD'], 2, '.','');?>">
          </td>
          <td class="column">
            <input id="sum-<?=$emp['ID']?>" value="<?=number_format($emp['AWARD']+$emp['OKLAD'], 2, '.','');?>" onchange="changed(this.id)">
			<input id="sumhide-<?=$emp['ID']?>" type="hidden" value="<?=number_format($emp['AWARD']+$emp['OKLAD'], 2, '.','');?>" onchange="changed(this.id)">
          </td>
			<td class="column">
            <input id="hos-<?=$emp['ID']?>" value="<?=number_format($emp['HOSPITAL'], 2, '.','');?>">
			<input id="hoshide-<?=$emp['ID']?>" type="hidden" value="<?=number_format($emp['HOSPITAL'], 2, '.','');?>" onchange="changed(this.id)">
          </td>
			<td class="column">
            <input id="hol-<?=$emp['ID']?>" value="<?=number_format($emp['HOLIDAY'], 2, '.','');?>">
			<input id="holhide-<?=$emp['ID']?>" type="hidden" value="<?=number_format($emp['HOLIDAY'], 2, '.','');?>" onchange="changed(this.id)">
          </td>
          <td class="column">
			<div style="display:flex; flex-direction:row;">
				<button class="save-button" id="<?=$emp['ID']?>" onclick="save(this.id,<?=$month?>,<?=$year?>)" <?if($emp['SAVED']){echo 'style="background: #2A824D;"';}?>>
					<?if(!$emp['SAVED']){echo "Сохранить";}else{echo "Изменить";}?>
				</button>
				<?if(!$emp['SAVED']):?>
				<button class="otladka" style="background:#EAB223;width:50px;" onclick="auto(<?=$emp['ID']?>)">
						Авто
					</button>
				<?endif;?>
				<?if($emp['TYPE']):?>
				<a target="_blank" href="https://b24.opti.ooo/rating/TEST/index.php?year=<?=$year?>&month=<?=$month?>&id=<?=$emp['ID']?>&type=<?=$emp['TYPE']?>">
					<button class="otladka">
						Отладка
					</button>
				</a>
				<?endif;?>
			</div>
          </td>
        </tr>
		<?endforeach;?>
<?endif;//БЫЛИ ЛИ ВЫПЛАТЫ?>
      </table>
	<?
	$titlemonths = array( 1 => 'январь' , 'февраль' , 'март' , 'апрель' , 'май' , 'июнь' , 'июль' , 'август' , 'сентябрь' , 'октябрь' , 'ноябрь' , 'декабрь' );
	$titlemonth = $month+1;
	$titleyear = $year;
	if($titleyear==2023){$titleyear=23;}
	if($titleyear==2022){$titleyear=22;}
	if($titlemonth>12){$titlemonth=1;$titleyear++;}
	$isTasks = false;

	$res = CTasks::GetList(
			Array("TITLE" => "ASC"), 
			Array(
			"TITLE" => "ЗП _".$months[$titlemonth]."'".$titleyear." (неоф)",
			)
		);
		if($arTask = $res->GetNext())
		{
			$isTasks = true;
		}
	?>
	<div class="upload-button-con">
		<?if(!$isTasks):?>
			<button class="upload-button" onclick='addtasks(<?=$month.",".$year?>)'>Сформировать реестр</button>
		<?endif;?>
	</div>
	<div class="navigation">
		<a href="https://b24.opti.ooo/rating/"><button>Сотрудник</button></a>
		<a href="https://b24.opti.ooo/rating/results.php"><button >Команда</button></a>
		<a href="https://b24.opti.ooo/rating/proekty.php"><button >Проекты</button></a>
		<?if($USER->IsAdmin()||$USER->GetID()==5462||$USER->GetID()==3262):?>
			<a href="https://b24.opti.ooo/rating/money.php"><button class="active">Начисления</button></a>
		<?endif;?>
		<?if($USER->GetID()==1||$USER->GetID()==3262):?>
			<a href="https://b24.opti.ooo/rating/yearResults.php"><button>Результаты</button></a>
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
	$K = $K/2;
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
	/*if($completed<5){$N=0.5;}
	elseif($completed<10){$N=0.6;}
	elseif($completed<15){$N=0.7;}
	elseif($completed<20){$N=0.8;}
	elseif($completed<30){$N=0.9;}
	else{$N=1;}*/

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

function TP($id, $date1,$date2){
	$deals=0;//Колво сделок
	$dialogs=array();//Массив всех айди диалогов
	$Np=0.8;//коэфициент сделок
	$Nd=0;//Колво уникальных диалогов
	$fuck = 1;//Коэфициент отсутсвия айди диалога
	$O=getOklad($id)/2;//Оклад

	//Получаем сделки сотрудника
	$res = CCrmDeal::GetList(
        Array('DATE_CREATE' => 'DESC'), 
		Array("ASSIGNED_BY_ID" => $id, 
				">=DATE_CREATE" => $date1,
   				"<=DATE_CREATE" => $date2,)
    );
	while($arDeal=$res->GetNext()){
		$deals++;//колво сделок
		//если айди диалога не валидный иил не указан, меняем коэф, в обратном случае записываем в массив айди диаога
		if($arDeal["UF_CRM_1678442726507"]==1 || $arDeal["UF_CRM_1678442726507"]==2 || $arDeal["UF_CRM_1678442726507"]==3 || $arDeal["UF_CRM_1678442726507"]>999){
			$dialogs[]=$arDeal["UF_CRM_1678442726507"];
		}else{$fuck = 0.6;}
 	}
	//если колво сделок меньше 30, уменьшаем коэффициент
	if($deals < 30){$Np=0.8;}else{$Np=1;}

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

	$Nd=count(array_unique($dialogs));//считаем колво уникальных айди диалогов

	if($Nd==0 || $deals==0){
		$sallary = $O*$fuck + $K*$H;
	}else{
		$sallary = $O*($Nd/$deals)*$fuck+$deals*100 + $K*$H;//Считаем ЗП
	}
	return $sallary;
}

function headTP($id, $date1, $date2){
	$O=getOklad($id);//Оклад
	$completed=0;//Колво закрытых сделок
	$workdeals=0;//Колво незавершенных сделок
	$summ=0;//Сумма закрытых сделок
	$B=0;//Процент закрытых сделок
	$Q=1;//Отношение закрытых к незавершенным сделкам


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
				$completed++;
				$summ+=$arDeal['OPPORTUNITY'];
			//}
		}
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
			}
		}

	//Считаем отношение закрытых к незавершенным
	if($workdeals==0 || $completed==0){
		$Q=1;
	}else{
		$K=$workdeals/$completed;
		if($K>0.2){$Q=0.6;}elseif($K>0.15){$Q=0.7;}elseif($K>0.1){$Q=0.8;}elseif($K>0.05){$Q=0.9;}else{$Q=1;}
	}

	//Считаем процент от закрытых сделок
	if($summ==0){
		$B=0;
	}else{
		$B=$summ/10;
	}

	$sallary= ($O + $completed*50)*$Q+$B; 
	return $sallary;
}

function rop($id,$date1, $date2){
	$O=40000;//Оклад
	$M=0.02;//маржа от всех сделок
	$Mi=0.05;//Маржа от своих сделок
	$l1=0;//Колво незавершенных сделок
	$l2=0;//Сумма закрытых сделок
	$summO=0;//Процент закрытых сделок

	//Считаем колво и сумму всех закрытых сделок
	$res = CCrmDeal::GetList(
        Array('DATE_CREATE' => 'DESC'), 
		Array("STAGE_ID" => array('C14:WON', 'C1:WON'), 'CLOSED'=> 'Y',
			">=CLOSEDATE" => $date1,
   				"<=CLOSEDATE" => $date2,)
    );
	while($arDeal=$res->GetNext()){
		$summO+=$arDeal['OPPORTUNITY'];
		//echo $arDeal['OPPORTUNITY'];
		//echo "<br>";
 	}
	//Считаем колво и сумму своих закрытых сделок
	$res = CCrmDeal::GetList(
        Array('DATE_CREATE' => 'DESC'), 
		Array("STAGE_ID" => array('C14:WON', 'C1:WON'), 'CLOSED'=> 'Y',
			"ASSIGNED_BY_ID" => $id,
			">=CLOSEDATE" => $date1,
   				"<=CLOSEDATE" => $date2,)
    );
	while($arDeal=$res->GetNext()){
		$summ+=$arDeal['OPPORTUNITY'];

 	}

	if($summO>=1000000){$l1=20000;}
	if($summO>=1500000){$l2=20000;}

	$sallary= $O + $l1+$l2+$summO*$M+$summ*$Mi;

	return $sallary;
}

function boss($date1, $date2){
	$O=40000;//Оклад
	$M=0.03;//маржа
	$l1=0;//Колво незавершенных сделок
	$l2=0;//Сумма закрытых сделок
	$summ=0;//Процент закрытых сделок

	//Считаем колво и сумму закрытых сделок
	$res = CCrmDeal::GetList(
        Array('DATE_CREATE' => 'DESC'), 
		Array("STAGE_ID" => array('C14:WON', 'C1:WON'), 'CLOSED'=> 'Y',
			">=CLOSEDATE" => $date1,
   				"<=CLOSEDATE" => $date2,)
    );
	while($arDeal=$res->GetNext()){
		$summ+=$arDeal['OPPORTUNITY'];
		//echo $arDeal['OPPORTUNITY'];
		//echo "<br>";
 	}

	if($summ>=1000000){$l1=20000;}
	if($summ>=1500000){$l2=20000;}

	$sallary= $O + $l1+$l2+$summ*$M;
	//echo "Сумма : ".$summ;
	//echo "<br>";
	return $sallary;
}
?>
