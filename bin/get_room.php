#!/usr/bin/php
<?php
error_reporting(E_ALL ^ E_NOTICE);
$t=iconv("GBK", "UTF-8", $argv[1]);
if (empty($t)) {
	print 0;
	exit(0);
}
$e=$argv[3];
$id=$argv[2];
$filename="/tmp/data_".$id."_".str_rand(12);

$trans = array(")" => "", 
		"(" => "",
		" " => "",
		"  " => "",
		"（"=>"" ,
		"）"=>""," 
		"=>"",
		"的"=>"",
		"白驼山" => "白驼",
		"华山村" => "小山村",
		"回族小镇" => "回疆小镇",
		"镇江府" => "镇江",
		"长江南岸" => "长江",
		"峨嵋派" => "峨嵋",
		"扬州城" => "扬州",
		"湟中城" => "湟中",
		"晋阳城" => "晋阳",
		"闽南" => "福州",
		"洛阳城" => "洛阳",
		"长安城" => "长安",
		"兰州城" => "兰州",
		"北京城" => "北京",
		"苏州城" => "苏州",
		"灵鹫宫" => "灵鹫",
		"全真教" => "全真");
$t=strtr($t, $trans);

$trans2 = array("正气山庄大门" => "正气山庄的大门",
		"白驼壁" => "白驼山壁"
			);
$t=strtr($t, $trans2);

$db = new SQLite3('bin/maps.db');

$results = $db->query('select * from mud_room where zone||roomname like "%' . $t .'"');
$num=0;
while ($row = $results->fetchArray()) {
	$num++;
	$rooms[]=$row;
}
if ($num == 1 ) {
	$output= "#var result {". $num."|".$rooms[0]["roomno"] ."|". iconv("UTF-8","GBK",$rooms[0]["roomname"]). "};\n";
	$output .= "#var roomno {" . $rooms[0]["roomno"] ."};\n" ;
	$output .= "#var num {" . $num ."};\n" ;
	file_put_contents($filename, $output);
	print $filename;
	exit(0);
}
if ($num == 0) {
	print 0;
	exit(0);
} 

$num=0;
foreach ($rooms as $row) {
	$tmp_dir_a=explode(";", trim($e,";"));
	$tmp_dir_b=explode(";", trim($row["exits"],";"));
	if( !empty($e) &&  ( count(array_diff ($tmp_dir_a , $tmp_dir_b)) > 0  ||  count($tmp_dir_a) != count($tmp_dir_b) )  ) continue;
	$no[]=$row["roomno"];
	$name[]=iconv("UTF-8","GBK",$row["roomname"]);
	$num++;
}

if ( $num > 0 ) {
	$output= "#var result {". $num."|".implode(";", $no). "|" . implode(";", $name) ."};\n";
	$output .= "#var num {" . $num ."};\n" ;
	$output .= "#var roomno {" . $no[0] ."};\n" ;
	file_put_contents($filename, $output);
	print $filename;
}
else {
	print 0;
}

function str_rand($length = 32, $char = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ') {
	if(!is_int($length) || $length < 0) return false;
	$string = '';
	for($i = $length; $i > 0; $i--) {
         	$string .= $char[mt_rand(0, strlen($char) - 1)];
	}
     	return $string;
}

?>
