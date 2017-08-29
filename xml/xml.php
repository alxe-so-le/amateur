<?
//php 数组生成xml
function arrtoxml($arr,$dom=0,$item=0){
if (!$dom){
    $dom = new DOMDocument("1.0","UTF-8");
}
if(!$item){
    $item = $dom->createElement("response");
    $dom->appendChild($item);
}
foreach ($arr as $key=>$val){
    $itemx = $dom->createElement(is_string($key)?$key:"item");
    $item->appendChild($itemx);
    if (!is_array($val)){
      $text = $dom->createTextNode($val);
      $itemx->appendChild($text);
    }else {
      arrtoxml($val,$dom,$itemx);
    }
}
header('Content-type:text/xml');
return $dom->saveXML();
}
$arr=array(
array(

	array(
	'e'=>12,
	'b'=>array(
			'a'=>'sd',
			'cs'=>'123'
		),
	),
	array(
	'e'=>12,
	array(
			'a'=>'',
			'cs'=>'123'
		),
	),
)
);
echo  arrtoxml($arr);
?>