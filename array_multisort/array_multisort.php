<?php
//多维数组排序
$arr1 = array_map(create_function('$n', 'return $n["distance"];'), $stores);
asort($arr1);
array_multisort($arr1,SORT_ASC,$stores );//多维数组的排序
?>

