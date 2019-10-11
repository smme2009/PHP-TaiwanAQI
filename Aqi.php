<?php

namespace Lib;

class Aqi
{
	private $url = 'https://opendata.epa.gov.tw/ws/Data/AQI/?$format=json';

	private $factor = [
		'AQI' => '',
		'O3' => 'ppb',
		'PM2.5' => 'μg/m3',
		'PM10' => 'μg/m3',
		'CO' => 'ppm',
		'SO2' => 'ppb',
		'NO2' => 'ppb',
	];

	private $data = [];
	private $location = [];

	function __construct(){
		$this->getData();
		$this->setData();
	}

	public function getFactorList(){
		return array_keys($this->factor);
	}

	public function getLocationList(){
		return $this->location;
	}

	public function getLocationData($location_id, $unit = false){
		$location = $this->location[$location_id];
		$aqi = $this->data[$location];

		if($unit) $aqi = $this->setUnit($aqi);

		return $aqi;
	}

	public function getAllData($unit = false){
		$aqi = $this->data;
		
		if($unit){
			foreach($aqi as $key => $val){
				$aqi[$key] = $this->setUnit($val); 
			}
		}

		return $aqi;
	}

	private function getData(){
		$curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $this->url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $aqi = curl_exec($curl);
		curl_close($curl);
		
		$aqi = json_decode($aqi, true, JSON_UNESCAPED_UNICODE);

		$this->data = $aqi;
	}

	private function setData(){
		$aqi = [];
		foreach($this->data as $val){
			$location = $val['County'] . '-' . $val['SiteName'];
			$this->location[] = $location;
			$aqi[$location] = array_intersect_key($val, $this->factor);
		}

		$this->data = $aqi;
	}

	private function setUnit($data){
		foreach($data as $key => $val){
			$data[$key] = $val . $this->factor[$key];
		}

		return $data;
	}
}
