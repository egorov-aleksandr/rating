<?//ПОЛУЧАЕМ ПЕРЕМЕННЫЕ
$str_json = file_get_contents('php://input');
$author = json_decode($str_json)->author;
$month = json_decode($str_json)->month;
$year = json_decode($str_json)->year;
$S = json_decode($str_json)->S;
$O = json_decode($str_json)->O;
$P = json_decode($str_json)->P;
?>

<?//ФУНКЦИЯ ДЛЯ ОТЛАДКИ В ФАЙЛ
function var_dump_f ($val) {
  ob_start();
  var_dump($val);
  $output = ob_get_clean();
  file_put_contents('test.txt', $output);
}
?>

<?//РАБОТА С ХАЙЛОАД
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule("tasks");?>
<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();
use Bitrix\Highloadblock\HighloadBlockTable as HLBT;
CModule::IncludeModule('highloadblock');
$highblock_id = 6;
$hl_block = HLBT::getById($highblock_id)->fetch();
$entity = HLBT::compileEntity($hl_block);
$entity_data_class = $entity->getDataClass();
?>

<?//ЗАПРОС В ХАЙЛОАД
$rs_data = $entity_data_class::getList(array(
   'select' => array('UF_AUTHOR', 'UF_MONTH','UF_YEAR'),
	'filter' => array('UF_AUTHOR'=>$author,'UF_MONTH'=>$month,'UF_YEAR'=>$year)
));
?>

<?//ПРОВЕРКА И ЗАПИСЬ
if(!$rs_data->fetch()){
	foreach($P as $item){
		$result = $entity_data_class::add(array(
		  "UF_TYPE"=>'P',
		  "UF_EMPLOYEE"=>$item->id,
		  "UF_RATE"=>$item->value,
			"UF_AUTHOR" => $author,
			"UF_MONTH" => $month,
			"UF_YEAR" => $year
	   ));
	}
	
	foreach($O as $item){
		$result = $entity_data_class::add(array(
		  "UF_TYPE"=>'O',
		  "UF_EMPLOYEE"=>$item->id,
		  "UF_RATE"=>$item->value,
			"UF_AUTHOR" => $author,
			"UF_MONTH" => $month,
			"UF_YEAR" => $year
	   ));
	}
	
	foreach($S as $item){
		$result = $entity_data_class::add(array(
		  "UF_TYPE"=>'S',
		  "UF_EMPLOYEE"=>$item->id,
		  "UF_RATE"=>$item->value,
			"UF_AUTHOR" => $author,
			"UF_MONTH" => $month,
			"UF_YEAR" => $year
	   ));
	}
	echo json_encode("Данные сохранены", JSON_UNESCAPED_UNICODE);
}else{echo json_encode("Вы уже оставляли оценки за выбранный месяц", JSON_UNESCAPED_UNICODE);}
?>