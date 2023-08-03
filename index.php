<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Сотрудник");
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

$filter = Array
(
"ACTIVE"              => "Y",
"GROUPS_ID"           => Array(26)
);
$rsUsers = CUser::GetList(($by="work_department"), ($order="asc"), $filter);?>

<input id="author" type="hidden" value="<?=$ID?>">
<input id="year" type="hidden" value="<?=$month.".".$year?>">

<div class="conteiner">
    <div class="form">
        <div class="form-nav">
            <div class="form-nav-elem selected" id="tag1" onclick="changepage(this.id)">
                Самостоятельность
            </div>
            <div class="form-nav-elem" id="tag2" onclick="changepage(this.id)">
                Обучаемость
            </div>    
            <div class="form-nav-elem" id="tag3" onclick="changepage(this.id)">
                Полезность
            </div>
        </div>
<?
$rs_data = $entity_data_class::getList(array(
				'select' => array('*'),
				'filter'=>array('UF_TYPE'=> 'S','UF_MONTH'=>$month,'UF_YEAR'=>$year,'UF_AUTHOR'=>$ID)
				));
?>
	<?if($rs_data->fetch()):?>
        <div class = "page" id="page1">
            <div class="page-description">
                Проставь оценки от 1 до 10 по каждому сотруднику описание описание описание описание
            </div>
            <div class="emp-list">
                <div class="results-emp-title">
                    <div class="results-emp-value-title">
                        <div class="rate-title">1</div>
                        <div class="rate-title">2</div>
                        <div class="rate-title">3</div>
                        <div class="rate-title">4</div>
                        <div class="rate-title">5</div>
                        <div class="rate-title">6</div>
                        <div class="rate-title">7</div>
                        <div class="rate-title">8</div>
                        <div class="rate-title">9</div>
                        <div class="rate-title">10</div>
                    </div>
                </div>
				<?while($el = $rs_data->fetch()):?>

				<?$rsUser = CUser::GetByID($el['UF_EMPLOYEE']);
					$arUser = $rsUser->Fetch();
					if($ID==$arUser["ID"]){continue;}?>
					<?$photoID = $arUser['PERSONAL_PHOTO'];//ищем id фото
						$photoPath = CFile::GetPath($photoID);//ищем путь к фото?>
                <div class="results-emp">
                    <div class="results-emp-img" style="background-image: url('<?echo $photoPath;?>'); background-size: cover">
                        
                    </div>
                    <div class="results-emp-scale">
                        <?=$arUser["NAME"]." ".$arUser["LAST_NAME"]?>
                    </div>
                    <div class="results-emp-value">
						<div class="rate"  data-id="<?= $arUser["ID"]?>" data-value="1" <?if($el["UF_RATE"]==1){echo 'style="background:#D74747;"';}?>>
                        </div>
                        <div class="rate"  data-id="<?= $arUser["ID"]?>" data-value="2"  <?if($el["UF_RATE"]==2){echo 'style="background:#D74747;"';}?>>
                        </div>
                        <div class="rate"  data-id="<?= $arUser["ID"]?>" data-value="3"  <?if($el["UF_RATE"]==3){echo 'style="background:#EAB223;"';}?>>
                        </div>
                        <div class="rate"  data-id="<?= $arUser["ID"]?>" data-value="4"  <?if($el["UF_RATE"]==4){echo 'style="background:#EAB223;"';}?>>
                        </div>
                        <div class="rate"  data-id="<?= $arUser["ID"]?>" data-value="5"  <?if($el["UF_RATE"]==5){echo 'style="background:#EAB223;"';}?>>
                        </div>
                        <div class="rate"  data-id="<?= $arUser["ID"]?>" data-value="6"  <?if($el["UF_RATE"]==6){echo 'style="background:#EAB223;"';}?>>
                        </div>
                        <div class="rate"  data-id="<?= $arUser["ID"]?>" data-value="7"  <?if($el["UF_RATE"]==7){echo 'style="background:#528FA3;"';}?>>
                        </div>
                        <div class="rate"  data-id="<?= $arUser["ID"]?>" data-value="8"  <?if($el["UF_RATE"]==8){echo 'style="background:#528FA3;"';}?>>
                        </div>
                        <div class="rate"  data-id="<?= $arUser["ID"]?>" data-value="9"  <?if($el["UF_RATE"]==9){echo 'style="background:#2A824D;"';}?>>
                        </div>
                        <div class="rate"  data-id="<?= $arUser["ID"]?>" data-value="10"  <?if($el["UF_RATE"]==10){echo 'style="background:#2A824D;"';}?>>
                        </div>
                    </div>
                </div>
				<?endwhile;?>
            </div>
        </div>

<?
$rs_data = $entity_data_class::getList(array(
				'select' => array('*'),
				'filter'=>array('UF_TYPE'=> 'O','UF_MONTH'=>$month,'UF_YEAR'=>$year,'UF_AUTHOR'=>$ID)
				));
?>
        <div class = "page hide" id="page2">
            <div class="page-description">
                Проставь оценки от 1 до 10 по каждому сотруднику описание описание описание описание
            </div>
            <div class="emp-list">
                <div class="results-emp-title">
                    <div class="results-emp-value-title">
                        <div class="rate-title">1</div>
                        <div class="rate-title">2</div>
                        <div class="rate-title">3</div>
                        <div class="rate-title">4</div>
                        <div class="rate-title">5</div>
                        <div class="rate-title">6</div>
                        <div class="rate-title">7</div>
                        <div class="rate-title">8</div>
                        <div class="rate-title">9</div>
                        <div class="rate-title">10</div>
                    </div>
                </div>
				<?while($el = $rs_data->fetch()):?>

				<?$rsUser = CUser::GetByID($el['UF_EMPLOYEE']);
					$arUser = $rsUser->Fetch();
					if($ID==$arUser["ID"]){continue;}?>
					<?$photoID = $arUser['PERSONAL_PHOTO'];//ищем id фото
						$photoPath = CFile::GetPath($photoID);//ищем путь к фото?>
                <div class="results-emp">
                    <div class="results-emp-img" style="background-image: url('<?echo $photoPath;?>'); background-size: cover">
                        
                    </div>
                    <div class="results-emp-scale">
                        <?=$arUser["NAME"]." ".$arUser["LAST_NAME"]?>
                    </div>
                    <div class="results-emp-value">
						<div class="rate"  data-id="<?= $arUser["ID"]?>" data-value="1" <?if($el["UF_RATE"]==1){echo 'style="background:#D74747;"';}?>>
                        </div>
                        <div class="rate"  data-id="<?= $arUser["ID"]?>" data-value="2"  <?if($el["UF_RATE"]==2){echo 'style="background:#D74747;"';}?>>
                        </div>
                        <div class="rate"  data-id="<?= $arUser["ID"]?>" data-value="3"  <?if($el["UF_RATE"]==3){echo 'style="background:#EAB223;"';}?>>
                        </div>
                        <div class="rate"  data-id="<?= $arUser["ID"]?>" data-value="4"  <?if($el["UF_RATE"]==4){echo 'style="background:#EAB223;"';}?>>
                        </div>
                        <div class="rate"  data-id="<?= $arUser["ID"]?>" data-value="5"  <?if($el["UF_RATE"]==5){echo 'style="background:#EAB223;"';}?>>
                        </div>
                        <div class="rate"  data-id="<?= $arUser["ID"]?>" data-value="6"  <?if($el["UF_RATE"]==6){echo 'style="background:#EAB223;"';}?>>
                        </div>
                        <div class="rate"  data-id="<?= $arUser["ID"]?>" data-value="7"  <?if($el["UF_RATE"]==7){echo 'style="background:#528FA3;"';}?>>
                        </div>
                        <div class="rate"  data-id="<?= $arUser["ID"]?>" data-value="8"  <?if($el["UF_RATE"]==8){echo 'style="background:#528FA3;"';}?>>
                        </div>
                        <div class="rate"  data-id="<?= $arUser["ID"]?>" data-value="9"  <?if($el["UF_RATE"]==9){echo 'style="background:#2A824D;"';}?>>
                        </div>
                        <div class="rate"  data-id="<?= $arUser["ID"]?>" data-value="10"  <?if($el["UF_RATE"]==10){echo 'style="background:#2A824D;"';}?>>
                        </div>
                    </div>
                </div>
				<?endwhile;?>
            </div>
        </div>

		<?
$rs_data = $entity_data_class::getList(array(
				'select' => array('*'),
				'filter'=>array('UF_TYPE'=> 'P','UF_MONTH'=>$month,'UF_YEAR'=>$year,'UF_AUTHOR'=>$ID)
				));
?>
        <div class = "page hide" id="page3">
            <div class="page-description">
                Проставь оценки от 1 до 10 по каждому сотруднику описание описание описание описание
            </div>
            <div class="emp-list">
                <div class="results-emp-title">
                    <div class="results-emp-value-title">
                        <div class="rate-title">1</div>
                        <div class="rate-title">2</div>
                        <div class="rate-title">3</div>
                        <div class="rate-title">4</div>
                        <div class="rate-title">5</div>
                        <div class="rate-title">6</div>
                        <div class="rate-title">7</div>
                        <div class="rate-title">8</div>
                        <div class="rate-title">9</div>
                        <div class="rate-title">10</div>
                    </div>
                </div>
				<?while($el = $rs_data->fetch()):?>

				<?$rsUser = CUser::GetByID($el['UF_EMPLOYEE']);
					$arUser = $rsUser->Fetch();
					if($ID==$arUser["ID"]){continue;}?>
					<?$photoID = $arUser['PERSONAL_PHOTO'];//ищем id фото
						$photoPath = CFile::GetPath($photoID);//ищем путь к фото?>
                <div class="results-emp">
                    <div class="results-emp-img" style="background-image: url('<?echo $photoPath;?>'); background-size: cover">
                        
                    </div>
                    <div class="results-emp-scale">
                        <?=$arUser["NAME"]." ".$arUser["LAST_NAME"]?>
                    </div>
                    <div class="results-emp-value">
						<div class="rate"  data-id="<?= $arUser["ID"]?>" data-value="1" <?if($el["UF_RATE"]==1){echo 'style="background:#D74747;"';}?>>
                        </div>
                        <div class="rate"  data-id="<?= $arUser["ID"]?>" data-value="2"  <?if($el["UF_RATE"]==2){echo 'style="background:#D74747;"';}?>>
                        </div>
                        <div class="rate"  data-id="<?= $arUser["ID"]?>" data-value="3"  <?if($el["UF_RATE"]==3){echo 'style="background:#EAB223;"';}?>>
                        </div>
                        <div class="rate"  data-id="<?= $arUser["ID"]?>" data-value="4"  <?if($el["UF_RATE"]==4){echo 'style="background:#EAB223;"';}?>>
                        </div>
                        <div class="rate"  data-id="<?= $arUser["ID"]?>" data-value="5"  <?if($el["UF_RATE"]==5){echo 'style="background:#EAB223;"';}?>>
                        </div>
                        <div class="rate"  data-id="<?= $arUser["ID"]?>" data-value="6"  <?if($el["UF_RATE"]==6){echo 'style="background:#EAB223;"';}?>>
                        </div>
                        <div class="rate"  data-id="<?= $arUser["ID"]?>" data-value="7"  <?if($el["UF_RATE"]==7){echo 'style="background:#528FA3;"';}?>>
                        </div>
                        <div class="rate"  data-id="<?= $arUser["ID"]?>" data-value="8"  <?if($el["UF_RATE"]==8){echo 'style="background:#528FA3;"';}?>>
                        </div>
                        <div class="rate"  data-id="<?= $arUser["ID"]?>" data-value="9"  <?if($el["UF_RATE"]==9){echo 'style="background:#2A824D;"';}?>>
                        </div>
                        <div class="rate"  data-id="<?= $arUser["ID"]?>" data-value="10"  <?if($el["UF_RATE"]==10){echo 'style="background:#2A824D;"';}?>>
                        </div>
                    </div>
                </div>
				<?endwhile;?>
            </div>
        </div>
		<div class="buttons">
            <div class="selectdate">
                <div class="selectdate-con">
					<button class="selectdate-btn" onclick="backmonth(<?=$month.",".$year?>)">Пред.</button>
						<?=$months[$month]." / ".$year?>
					<button class="selectdate-btn" onclick="nextmonth(<?=$month.",".$year?>)">След.</button>
                </div>            
            </div>   
            <button class="next-button" id="next-button" onclick="nextpage(this)">Далее</button>
        </div>
		<?else:?>
		<?$filter = Array
					(
					"ACTIVE"              => "Y",
					"GROUPS_ID"           => Array(26)
					);
		$rsUsers = CUser::GetList(($by="work_department"), ($order="asc"), $filter);?>

		 <div class = "page" id="page1">
            <div class="page-description">
                Проставь оценки от 1 до 10 по каждому сотруднику описание описание описание описание
            </div>
            <div class="emp-list">
                <div class="results-emp-title">
                    <div class="results-emp-value-title">
                        <div class="rate-title">1</div>
                        <div class="rate-title">2</div>
                        <div class="rate-title">3</div>
                        <div class="rate-title">4</div>
                        <div class="rate-title">5</div>
                        <div class="rate-title">6</div>
                        <div class="rate-title">7</div>
                        <div class="rate-title">8</div>
                        <div class="rate-title">9</div>
                        <div class="rate-title">10</div>
                    </div>
                </div>
				<?while($arUser = $rsUsers->GetNext()):?>
				<?if($ID==$arUser["ID"]){continue;}?>
					<?$photoID = $arUser['PERSONAL_PHOTO'];//ищем id фото
						$photoPath = CFile::GetPath($photoID);//ищем путь к фото?>
                <div class="results-emp">
                    <div class="results-emp-img" style="background-image: url('<?echo $photoPath;?>'); background-size: cover">
                        
                    </div>
                    <div class="results-emp-scale">
                        <?=$arUser["NAME"]." ".$arUser["LAST_NAME"]?>
                    </div>
                    <div class="results-emp-value">
                        <div class="rate" onclick="rate(this.parentNode, this,1)" data-id="<?= $arUser["ID"]?>" data-value="1">
                        </div>
                        <div class="rate" onclick="rate(this.parentNode, this,1)" data-id="<?= $arUser["ID"]?>" data-value="2">
                        </div>
                        <div class="rate" onclick="rate(this.parentNode, this,1)" data-id="<?= $arUser["ID"]?>" data-value="3">
                        </div>
                        <div class="rate" onclick="rate(this.parentNode, this,1)" data-id="<?= $arUser["ID"]?>" data-value="4">
                        </div>
                        <div class="rate rate-selected" onclick="rate(this.parentNode, this,1)" data-id="<?= $arUser["ID"]?>" data-value="5" style="background: rgb(234, 178, 35);">
                        </div>
                        <div class="rate" onclick="rate(this.parentNode, this,1)" data-id="<?= $arUser["ID"]?>" data-value="6">
                        </div>
                        <div class="rate" onclick="rate(this.parentNode, this,1)" data-id="<?= $arUser["ID"]?>" data-value="7">
                        </div>
                        <div class="rate" onclick="rate(this.parentNode, this,1)" data-id="<?= $arUser["ID"]?>" data-value="8">
                        </div>
                        <div class="rate" onclick="rate(this.parentNode, this,1)" data-id="<?= $arUser["ID"]?>" data-value="9">
                        </div>
                        <div class="rate" onclick="rate(this.parentNode, this,1)" data-id="<?= $arUser["ID"]?>" data-value="10">
                        </div>
                    </div>
                </div>
				<?endwhile;?>
            </div>
        </div>

        <div class = "page hide" id="page2">
            <div class="page-description">
                Проставь оценки от 1 до 10 по каждому сотруднику описание описание описание описание
            </div>
            <div class="emp-list">
                <div class="results-emp-title">
                    <div class="results-emp-value-title">
                        <div class="rate-title">1</div>
                        <div class="rate-title">2</div>
                        <div class="rate-title">3</div>
                        <div class="rate-title">4</div>
                        <div class="rate-title">5</div>
                        <div class="rate-title">6</div>
                        <div class="rate-title">7</div>
                        <div class="rate-title">8</div>
                        <div class="rate-title">9</div>
                        <div class="rate-title">10</div>
                    </div>
                </div>
				<?$rsUsers = CUser::GetList(($by="work_department"), ($order="asc"), $filter);?>
                <?while($arUser = $rsUsers->GetNext()):?>
				<?if($ID==$arUser["ID"]){continue;}?>
					<?$photoID = $arUser['PERSONAL_PHOTO'];//ищем id фото
						$photoPath = CFile::GetPath($photoID);//ищем путь к фото?>
                <div class="results-emp">
                    <div class="results-emp-img" style="background-image: url('<?echo $photoPath;?>'); background-size: cover">
                        
                    </div>
                    <div class="results-emp-scale">
                        <?=$arUser["NAME"]." ".$arUser["LAST_NAME"]?>
                    </div>
                    <div class="results-emp-value">
                        <div class="rate" onclick="rate(this.parentNode, this,2)" data-id="<?= $arUser["ID"]?>" data-value="1">
                        </div>
                        <div class="rate" onclick="rate(this.parentNode, this,2)" data-id="<?= $arUser["ID"]?>" data-value="2">
                        </div>
                        <div class="rate" onclick="rate(this.parentNode, this,2)" data-id="<?= $arUser["ID"]?>" data-value="3">
                        </div>
                        <div class="rate" onclick="rate(this.parentNode, this,2)" data-id="<?= $arUser["ID"]?>" data-value="4">
                        </div>
                        <div class="rate rate-selected" onclick="rate(this.parentNode, this,2)" data-id="<?= $arUser["ID"]?>" data-value="5" style="background: rgb(234, 178, 35);">
                        </div>
                        <div class="rate" onclick="rate(this.parentNode, this,2)" data-id="<?= $arUser["ID"]?>" data-value="6">
                        </div>
                        <div class="rate" onclick="rate(this.parentNode, this,2)" data-id="<?= $arUser["ID"]?>" data-value="7">
                        </div>
                        <div class="rate" onclick="rate(this.parentNode, this,2)" data-id="<?= $arUser["ID"]?>" data-value="8">
                        </div>
                        <div class="rate" onclick="rate(this.parentNode, this,2)" data-id="<?= $arUser["ID"]?>" data-value="9">
                        </div>
                        <div class="rate" onclick="rate(this.parentNode, this,2)" data-id="<?= $arUser["ID"]?>" data-value="10">
                        </div>
                    </div>
                </div>
				<?endwhile;?>
            </div>
        </div>

        <div class = "page hide" id="page3">
            <div class="page-description">
                Проставь оценки от 1 до 10 по каждому сотруднику описание описание описание описание
            </div>
            <div class="emp-list">
                <div class="results-emp-title">
                    <div class="results-emp-value-title">
                        <div class="rate-title">1</div>
                        <div class="rate-title">2</div>
                        <div class="rate-title">3</div>
                        <div class="rate-title">4</div>
                        <div class="rate-title">5</div>
                        <div class="rate-title">6</div>
                        <div class="rate-title">7</div>
                        <div class="rate-title">8</div>
                        <div class="rate-title">9</div>
                        <div class="rate-title">10</div>
                    </div>
                </div>
				<?$rsUsers = CUser::GetList(($by="work_department"), ($order="asc"), $filter);?>
                <?while($arUser = $rsUsers->GetNext()):?>
				<?if($ID==$arUser["ID"]){continue;}?>
					<?$photoID = $arUser['PERSONAL_PHOTO'];//ищем id фото
						$photoPath = CFile::GetPath($photoID);//ищем путь к фото?>
                <div class="results-emp">
                    <div class="results-emp-img" style="background-image: url('<?echo $photoPath;?>'); background-size: cover">
                        
                    </div>
                    <div class="results-emp-scale">
                        <?=$arUser["NAME"]." ".$arUser["LAST_NAME"]?>
                    </div>
                   <div class="results-emp-value">
                        <div class="rate" onclick="rate(this.parentNode, this,3)" data-id="<?= $arUser["ID"]?>" data-value="1">
                        </div>
                        <div class="rate" onclick="rate(this.parentNode, this,3)" data-id="<?= $arUser["ID"]?>" data-value="2">
                        </div>
                        <div class="rate" onclick="rate(this.parentNode, this,3)" data-id="<?= $arUser["ID"]?>" data-value="3">
                        </div>
                        <div class="rate" onclick="rate(this.parentNode, this,3)" data-id="<?= $arUser["ID"]?>" data-value="4">
                        </div>
                        <div class="rate rate-selected" onclick="rate(this.parentNode, this,3)" data-id="<?= $arUser["ID"]?>" data-value="5" style="background: rgb(234, 178, 35);">
                        </div>
                        <div class="rate" onclick="rate(this.parentNode, this,3)" data-id="<?= $arUser["ID"]?>" data-value="6">
                        </div>
                        <div class="rate" onclick="rate(this.parentNode, this,3)" data-id="<?= $arUser["ID"]?>" data-value="7">
                        </div>
                        <div class="rate" onclick="rate(this.parentNode, this,3)" data-id="<?= $arUser["ID"]?>" data-value="8">
                        </div>
                        <div class="rate" onclick="rate(this.parentNode, this,3)" data-id="<?= $arUser["ID"]?>" data-value="9">
                        </div>
                        <div class="rate" onclick="rate(this.parentNode, this,3)" data-id="<?= $arUser["ID"]?>" data-value="10">
                        </div>
                    </div>
                </div>
				<?endwhile;?>
            </div>
        </div>
        <div class="buttons">
            <div class="selectdate">
                <div class="selectdate-con">
					<button class="selectdate-btn" onclick="backmonth(<?=$month.",".$year?>)">Пред.</button>
						<?=$months[$month]." / ".$year?>
					<button class="selectdate-btn" onclick="nextmonth(<?=$month.",".$year?>)">След.</button>
                </div>            
            </div>      
            <button class="next-button" id="next-button" onclick="nextpage(this)">Далее</button>
            <button class="next-button hide" id="upload-button" onclick="send()">Сохранить</button>
        </div>
		<?endif;?>


    </div>
	<?
	$rsUser = CUser::GetByID($ID);
	$arUser = $rsUser->Fetch();
	$photoID = $arUser['PERSONAL_PHOTO'];//ищем id фото
	$photoPath = CFile::GetPath($photoID);//ищем путь к фото
	$comp = "I";
	$color = "#EADC94";
	switch($arUser['UF_PUBLIC_COMPETENCE']){
		case 6440:
			$comp="I";
			$color = "#EADC94";
			break;
		case 6441:
			$comp="B";
			$color = "#D4DF98";
			break;
		case 6442:
			$comp="J";
			$color = "#A5D8E8";
			break;
		case 6443:
			$comp="M";
			$color = "#33BBE6";
			break;
		case 6444:
			$comp="S";
			$color = "#2E76B6";
			break;
	}
	?>
    <div class="user">
        <div class="user-info">
            <div class="user-info-img" style="background-image: url('<?echo $photoPath;?>'); background-size: cover">
            </div>
            <div class="user-info-text">
                <div class="user-info-text-name">
                    <?=$arUser["NAME"]." ".$arUser["LAST_NAME"]?>
                </div>
                <div class="user-info-text-work">
                    <?=$arUser["WORK_POSITION"]?>
                </div>
            </div>
			<?if($arUser['ID']==1):?>
				<div class="user-info-competence" style="color:<?=$color?>;">
					<img src="/rating/imgs/Boss.png">
           		</div>
			<?elseif($arUser['WORK_POSITION']=="Бухгалтер"):?>
				<div class="user-info-competence" style="color:<?=$color?>;">
					<img src="/rating/imgs/Buh.png">
           		</div>
			<?else:?>
            <div class="user-info-competence" style="color:<?=$color?>;">
                <?=$comp?>
            </div>
			<?endif;?>
        </div>
		<?// Вывод элементов Highload-блока
					$highblock_id = 6;
					$hl_block = HLBT::getById($highblock_id)->fetch();
					
					// Получение имени класса
					$entity = HLBT::compileEntity($hl_block);
					$entity_data_class = $entity->getDataClass();

					$rs_data = $entity_data_class::getList(array(
					   'select' => array('*'),
						'filter'=>array('UF_MONTH'=>$month,'UF_YEAR'=>$year,'UF_TYPE'=>"S",)
					));
					$allval=0;
					$allcount=0;
					$empval=0;
					$empcount=0;
					while($el = $rs_data->fetch()){
						$allval=$allval + $el['UF_RATE'];
						$allcount = $allcount + 1;
						if($el['UF_EMPLOYEE']==$ID){
							$empval=$empval + $el['UF_RATE'];
							$empcount = $empcount + 1;
						}
					}

					$allaverageS = round($allval/$allcount*10);
					if($allcount<1){$allaverageS=0;}
					$empaverageS = round($empval/$empcount*10);
					if($empcount<1){$empaverageS=0;}


					$rs_data = $entity_data_class::getList(array(
					   'select' => array('*'),
						'filter'=>array('UF_MONTH'=>$month,'UF_YEAR'=>$year,'UF_TYPE'=>"O",)
					));
					$allval=0;
					$allcount=0;
					$empval=0;
					$empcount=0;
					while($el = $rs_data->fetch()){
						$allval=$allval + $el['UF_RATE'];
						$allcount = $allcount + 1;
						if($el['UF_EMPLOYEE']==$ID){
							$empval=$empval + $el['UF_RATE'];
							$empcount = $empcount + 1;
						}
					}
					$allaverageO = round($allval/$allcount*10);
					if($allcount<1){$allaverageO=0;}
					$empaverageO = round($empval/$empcount*10);
					if($empcount<1){$empaverageO=0;}

					$rs_data = $entity_data_class::getList(array(
					   'select' => array('*'),
						'filter'=>array('UF_MONTH'=>$month,'UF_YEAR'=>$year,'UF_TYPE'=>"P",)
					));
					$allval=0;
					$allcount=0;
					$empval=0;
					$empcount=0;
					while($el = $rs_data->fetch()){
						$allval=$allval + $el['UF_RATE'];
						$allcount = $allcount + 1;
						if($el['UF_EMPLOYEE']==$ID){
							$empval=$empval + $el['UF_RATE'];
							$empcount = $empcount + 1;
						}
					}

					$allaverageP = round($allval/$allcount*10);
					if($allcount<1){$allaverageP=0;}
					$empaverageP = round($empval/$empcount*10);
					if($empcount<1){$empaverageP=0;}
					?>
        <div class="user-ratings-con">
            <div class="user-ratings">
                <div class="user-ratings-scale">
                    <div class="user-ratings-scale-progress">
                        <div class="user-independ-bar" style="width: <?=$empaverageS?>%;"></div>
                    </div>
                    <div class="user-ratings-scale-progress">
                        <div class="all-independ-bar" style="width: <?=$allaverageS?>%;"></div>
                    </div>
                </div>
            </div>
            <div class="user-ratings-text" style="color:<?if($empaverageS-$allaverageS >= 0){echo "#2A824D";}else{echo "#D74747";}?>;">
				<?if($empaverageS-$allaverageS >= 0){echo "+";}?>
                <?=$empaverageS-$allaverageS?>%
            </div>
        </div>
        <div class="user-ratings-con">
            <div class="user-ratings">
                <div class="user-ratings-scale">
                    <div class="user-ratings-scale-progress">
                        <div class="user-learn-bar" style="width: <?=$empaverageO?>%;"></div>
                    </div>
                    <div class="user-ratings-scale-progress">
                        <div class="all-learn-bar" style="width: <?=$allaverageO?>%;"></div>
                    </div>
                </div>
            </div>
            <div class="user-ratings-text" style="color:<?if($empaverageO-$allaverageO >= 0){echo "#2A824D";}else{echo "#D74747";}?>;">
				<?if($empaverageO-$allaverageO >= 0){echo "+";}?>
                <?=$empaverageO-$allaverageO?>%
            </div>
        </div>
        <div class="user-ratings-con">
            <div class="user-ratings">
                <div class="user-ratings-scale">
                    <div class="user-ratings-scale-progress">
                        <div class="user-usefull-bar" style="width: <?=$empaverageP?>%;"></div>
                    </div>
                    <div class="user-ratings-scale-progress">
                        <div class="all-usefull-bar" style="width: <?=$allaverageP?>%;"></div>
                    </div>
                </div>
            </div>
            <div class="user-ratings-text" style="color:<?if($empaverageP-$allaverageP >= 0){echo "#2A824D";}else{echo "#D74747";}?>;">
				<?if($empaverageP-$allaverageP >= 0){echo "+";}?>
                <?=$empaverageP-$allaverageP?>%
            </div>
        </div>
		<?
		$created = 0;
		$res = CTasks::GetList(
			Array("TITLE" => "ASC"), 
			Array(
			"CREATED_BY" => $ID,
			">=CLOSED_DATE" => $date1,
			"<=CLOSED_DATE" => $date2,
			'REAL_STATUS' => CTasks::STATE_COMPLETED
			)
		);
		while ($arTask = $res->GetNext())
		{
			$created = $completed + 1;
		}

		$responsible = 0;
		$res = CTasks::GetList(
			Array("TITLE" => "ASC"), 
			Array(
			"RESPONSIBLE_ID" => $ID,
			">=CLOSED_DATE" => $date1,
			"<=CLOSED_DATE" => $date2,
			'REAL_STATUS' => CTasks::STATE_COMPLETED
			)
		);
		while ($arTask = $res->GetNext())
		{
			$responsible = $responsible + 1;
		}

		$accomplice = 0;
		$res = CTasks::GetList(
			Array("TITLE" => "ASC"), 
			Array(
			"ACCOMPLICE" => $ID,
			">=CLOSED_DATE" => $date1,
			"<=CLOSED_DATE" => $date2,
			'REAL_STATUS' => CTasks::STATE_COMPLETED
			)
		);
		while ($arTask = $res->GetNext())
		{
			$accomplice = $accomplice + 1;
		}
		?>

        <div class="tasks">
            <div class="tasks-title">
                <div class="tasks-title-text">
                    Выполнено задач
                </div>
                <img src="/rating/imgs/tasks.png" class="tasks-title-img">
            </div>
            <div class="tasks-elem">
                <div class="tasks-subtitle">
                    Постановщик
                </div>
                <div class="tasks-created-value">
                    <img src="/rating/imgs/Icon(1).png" class="tasks-created-img">
                    <?=$created?>
                </div>
            </div>
            <div class="tasks-elem">
                <div class="tasks-subtitle">
                    Отвественный
                </div>
                <div class="tasks-responsible-value">
                    <img src="/rating/imgs/Icon(2).png" class="tasks-responsible-img">
                    <?=$responsible?>
                </div>
            </div>
            <div class="tasks-elem">
                <div class="tasks-subtitle">
                    Соисполнитель
                </div>
                <div class="tasks-accomplice-value">
                    <img src="/rating/imgs/Icon(3).png" class="tasks-accomplice-img">
                    <?=$accomplice?>
                </div>
            </div>
        </div>

<?
$awardsumm=0;//процент от всех успешных сделок
$allawardsumm=0;//Сумма бонуса всех сотрудников
$empawardsumm=0;//Бонус текущего сотрудника
$freesumm=0;//нераспределенный бонус
for($m=1; $m<=$month; $m++){//Цикл по месяцам
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
		$awardsumm+=$arDeal['OPPORTUNITY'];//Сделки
 	}
	$awardsumm=$awardsumm/100;//процент
	$filter = Array
		(
		"ACTIVE"              => "Y",
		"GROUPS_ID"           => Array(26),
			"DATE_REGISTER_2" => $d1
		);
	$emps=0;//колво сотрудников

	$rsUsers = CUser::GetList(($by="work_department"), ($order="asc"), $filter);
	while($arUser = $rsUsers->GetNext()){
		if(isEmp($arUser['ID'])){
			$emps++;
		}
	}

	$rsUsers = CUser::GetList(($by="work_department"), ($order="asc"), $filter);
	while($arUser = $rsUsers->GetNext()){//Цикл по сотрудникам, работавших в течении месяца и не уволенных по сей день
		if(isEmp($arUser['ID'])){
			//ОЦЕНКИ
			$rs_data = $entity_data_class::getList(array(
				'select' => array('*'),
				'filter'=>array('UF_MONTH'=>$m,'UF_YEAR'=>$year,'UF_EMPLOYEE'=>$arUser["ID"])
				));
			$count=0;//Колво оценок
			$value=0;//Значение оценок
			while($el = $rs_data->fetch()){
				$value+=$el['UF_RATE'];
				$count++;
			}
			if($count==0){$value=1; $count=1;}//Если оценок нет, ставим минимальные
			$rate=round($value/$count/10,1);//Среднее арифметическое оценок

			$allawardsumm+=round($awardsumm/$emps*$rate,2);//Бонус всех сотрудников + бонус сотрудника

			if($arUser["ID"]==$USER->GetID()){//Если вычисляемый сотрудник является текущим авторизованным
				$empawardsumm+=round($awardsumm/$emps*$rate,2);
			}
		}
	}
	$freesumm+=round($awardsumm - $allawardsumm, 2);//Нераспределенный бонус = процент от всех сделок - бонус всех сотрудников
}

?>
        <div class="award">
            <div class="award-title">
                <div class="award-title-text">
					<a style="color: #2A824D;" href="https://b24.opti.ooo/rating/TEST/yearAward.php">Бонус</a>
                    <div class="award-subtitle">
                        Сумма премирования формируется командой =% от выручки <br>выплата по итогам года пропорционально вкладу в работу компании 
                    </div>
                </div>
                <img src="/rating/imgs/awards.png" class="tasks-title-img">
            </div>
            <div class="tasks-elem">
                <div class="tasks-subtitle">
                </div>
                <div class="award-bonus-value">
					<?if($USER->GetID()==1||$USER->GetID()==5462|| !isEmp($USER->GetID())):?>
						Вы не учавствуете в расчете бонуса
					<?else:?>
                    	<?=$empawardsumm?> ₽
					<?endif;?>
                </div>
            </div>
            <div class="tasks-elem">
                <div class="tasks-subtitle">
                    Нераспределенный бонус
                </div>
                <div class="award-allbonus-value">
                    <?=$freesumm?>
                </div>
            </div>
        </div>
    </div>
	<div class="navigation">
		<a href="https://b24.opti.ooo/rating/"><button class="active">Сотрудник</button></a>
		<a href="https://b24.opti.ooo/rating/results.php"><button >Команда</button></a>
		<a href="https://b24.opti.ooo/rating/proekty.php"><button >Проекты</button></a>
		<?if($USER->IsAdmin()||$USER->GetID()==5462||$USER->GetID()==3262):?>
			<a href="https://b24.opti.ooo/rating/money.php"><button >Начисления</button></a>
		<?endif;?>
		<?if($USER->GetID()==1||$USER->GetID()==3262):?>
			<a href="https://b24.opti.ooo/rating/yearResults.php"><button>Результаты</button></a>
		<?endif;?>
	</div>
</div>
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