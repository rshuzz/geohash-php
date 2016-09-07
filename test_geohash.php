<?
include("./geohash.class.php");
$geohash_ins = new Geohash();
$longitude = 116.40452;
$latitude = 39.922164;
$geohash_len = 5;
$geohash = $geohash_ins->encode($longitude, $latitude, $geohash_len);

$decode = $geohash_ins->decode($geohash);
echo "source[$longitude, $latitude] geohash_len[$geohash_len]<br />";
echo "encode[".$geohash."] decode[".$decode["longitude"].",".$decode["latitude"]."]<br />";

$top = $geohash_ins->get_adjacent($geohash, "top");
$left = $geohash_ins->get_adjacent($geohash, "left");
$right = $geohash_ins->get_adjacent($geohash, "right");
$bottom = $geohash_ins->get_adjacent($geohash, "bottom");

$lefttop = $geohash_ins->get_adjacent($left, "top");
$leftbottom = $geohash_ins->get_adjacent($left, "bottom");
$righttop = $geohash_ins->get_adjacent($right, "top");
$rightbottom = $geohash_ins->get_adjacent($right, "bottom");

echo $lefttop."|".$top."|".$righttop."<br />";
echo $left."|".$geohash."|".$right."<br />";
echo $leftbottom."|".$bottom."|".$rightbottom."<br />";

?>