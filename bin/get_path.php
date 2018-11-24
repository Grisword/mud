#!/usr/bin/php
<?php
error_reporting(E_ALL ^ E_NOTICE);
$start=$argv[1];
$end=$argv[2];
$id=$argv[3];
$filename="/tmp/data_".$id."_".str_rand(12);

$db = new SQLite3('bin/maps.db');
$sql="select src_room_no, dst_room_no, direction, weight_type, is_boundary, dst_room_name, src_room_zone, dst_room_zone from room_and_entrance" ;
$results = $db->query($sql);
while ($row = $results->fetchArray()) {
	$edge[$row["src_room_no"]][]=array("roomno" => $row["dst_room_no"] , "direction" => $row["direction"] , "weight" => $row["weight_type"], "is_boundary" => $row["is_boundary"] );
}
//var_dump($edge);

$sql="select roomno,roomname from mud_room";
$results = $db->query($sql);
$num=0;
while ($row = $results->fetchArray()) {
      $num++;
	$dis[$row["roomno"]]= -1;
	$mark[$row["roomno"]]= 0;
	$cost[$row["roomno"]]= 0;
}

$dis[$start]="";
$mark[$start] = 1;//whether this node is in the aggregate
$newP = $start; //new dot needed to add

for($i = 1 ; $i < $num ; $i++){
	for($j = 0 ; $j <count($edge[$newP]); $j++){
		//all the nodes in the newP
                //get info
                $next = $edge[$newP][$j]["roomno"];
                $len = $edge[$newP][$j]["direction"];
                $cos = $edge[$newP][$j]["weight"]+1;
                 
                if($mark[$next] == 1) continue;//already in the aggreagte
                //can't reach or has a less length or has smaller cost
                if($dis[$next]==-1 || $cost[$next] > $cost[$newP]+$cos) {
                    $dis[$next] = $dis[$newP] . $len .";";
                    $cost[$next] = $cost[$newP] + $cos;
        	}
		//if($next == $end) break 2;
	}
        $min = 10000000;
        //for($j = 1 ; $j <= $num ; $j++){//find a new node to add
	foreach ($dis as $j => $value ) {
                if($mark[$j]==1) continue;//not in the aggregate
                if($dis[$j] == -1) continue;//can't  reach
                if($cos[$j]<$min){
                    $min = $cos[$j];
                    $newP = $j;
                }
        }
        $mark[$newP] = 1;
}

$output= "#list {fly_path} {create} {". trim($dis[$end],";") ."};\n";
$output .= "#var fly_num {" . $cost[$end] ."};\n" ;
file_put_contents($filename, $output);
print $filename;

//var_dump($dis[$end]);
//var_dump($cost[$end]);
function str_rand($length = 32, $char = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ') {
      if(!is_int($length) || $length < 0) return false;
      $string = '';
      for($i = $length; $i > 0; $i--) {
            $string .= $char[mt_rand(0, strlen($char) - 1)];
      }
      return $string;
}

?>
