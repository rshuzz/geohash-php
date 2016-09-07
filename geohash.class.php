<?php

class Geohash
{
	private $BITS = array(16, 8, 4, 2, 1);
	private $BASE32 = "0123456789bcdefghjkmnpqrstuvwxyz";
	private $NEIGHBORS = array();
	private $BORDERS = array();
	
    public function __construct()
    {
		$this->NEIGHBORS["right"]["even"] = "bc01fg45238967deuvhjyznpkmstqrwx";
		$this->NEIGHBORS["left"]["even"] = "238967debc01fg45kmstqrwxuvhjyznp";
		$this->NEIGHBORS["top"]["even"] = "p0r21436x8zb9dcf5h7kjnmqesgutwvy";
		$this->NEIGHBORS["bottom"]["even"] = "14365h7k9dcfesgujnmqp0r2twvyx8zb";
		
		$this->BORDERS["right"]["even"] = "bcfguvyz";
		$this->BORDERS["left"]["even"] = "0145hjnp";
		$this->BORDERS["top"]["even"] = "prxz";
		$this->BORDERS["bottom"]["even"] = "028b";
		
		$this->NEIGHBORS["bottom"]["odd"] = $this->NEIGHBORS["left"]["even"];
		$this->NEIGHBORS["top"]["odd"] = $this->NEIGHBORS["right"]["even"];
		$this->NEIGHBORS["left"]["odd"] = $this->NEIGHBORS["bottom"]["even"];
		$this->NEIGHBORS["right"]["odd"] = $this->NEIGHBORS["top"]["even"];

		$this->BORDERS["bottom"]["odd"] = $this->BORDERS["left"]["even"];
		$this->BORDERS["top"]["odd"] = $this->BORDERS["right"]["even"];
		$this->BORDERS["left"]["odd"] = $this->BORDERS["bottom"]["even"];
		$this->BORDERS["right"]["odd"] = $this->BORDERS["top"]["even"];
    }
	
	function get_adjacent($geohash, $dir) 
	{
		$geohash = strtolower($geohash);
		$last_ch = $geohash[strlen($geohash) - 1];
		$type = (strlen($geohash) % 2) ? 'odd' : 'even';
		$base = substr($geohash, 0, strlen($geohash) - 1);
		if (strpos($this->BORDERS[$dir][$type], $last_ch) !== false) {
			$base = $this->get_adjacent($base, $dir);
		}
			
		return $base.$this->BASE32[strpos($this->NEIGHBORS[$dir][$type], $last_ch)];
	}

	function refine_interval(&$interval, $cd, $mask) 
	{
		if ($cd & $mask)
			$interval[0] = ($interval[0] + $interval[1])/2;
		else
			$interval[1] = ($interval[0] + $interval[1])/2;
	}
	
	function decode($geohash) 
	{
		$is_even = 1;
		$lat = array(-90.0, 90.0);
		$lon = array(-180.0, 180.0);
		
		for ($i = 0; $i < strlen($geohash); $i++) 
		{
			$cd = strpos($this->BASE32, $geohash[$i]);
			for ($j = 0; $j < 5; $j++) 
			{
				$mask = $this->BITS[$j];
				if ($is_even) {
					$this->refine_interval($lon, $cd, $mask);
				} else {
					$this->refine_interval($lat, $cd, $mask);
				}
				$is_even = !$is_even;
			}
		}
		$lat[2] = ($lat[0] + $lat[1]) / 2;
		$lon[2] = ($lon[0] + $lon[1]) / 2;

		return array("latitude" => $lat[2], "longitude" => $lon[2]);
	}
	
	function encode($longitude, $latitude, $geohash_len = 6)
	{
		return $this->encode_precise($longitude, $latitude, $geohash_len * 5);
	}

	function encode_precise($longitude, $latitude, $precision = 30)
	{
		if ($precision < 2)
			return false;
		
		$is_even = 1;
		$lat = array(-90.0, 90.0);
		$lon = array(-180.0, 180.0);
		$bit = 0;
		$ch = 0;
		$geohash = "";
		$total_bit = 0;
		
		while ($total_bit < $precision) 
		{
			if ($is_even) 
			{
				$mid = ($lon[0] + $lon[1]) / 2;
				if ($longitude > $mid) 
				{
					$ch |= $this->BITS[$bit];
					$lon[0] = $mid;
				} 
				else {
					$lon[1] = $mid;
				}
			} 
			else 
			{
				$mid = ($lat[0] + $lat[1]) / 2;
				if ($latitude > $mid) 
				{
					$ch |= $this->BITS[$bit];
					$lat[0] = $mid;
				} 
				else {
					$lat[1] = $mid;
				}
			}

			$is_even = !$is_even;
			$total_bit++;
			
			if ($bit < 4) {
				$bit++;
			}
			else 
			{
				$geohash .= $this->BASE32[$ch];
				$bit = 0;
				$ch = 0;
			}
		}
		
		if ($bit > 0) {
			$geohash .= $this->BASE32[$ch];
		}
		
		return $geohash;
	}
}

?>