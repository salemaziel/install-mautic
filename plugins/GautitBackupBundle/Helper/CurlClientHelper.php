<?php 
namespace MauticPlugin\GautitBackupBundle\Helper;

class CurlClientHelper {
	
	public $ch = null;
	public function __construct(){
		$this->ch = curl_init();
	}
	public function setOpt($opt,$value){
		curl_setopt($this->ch, $opt, $value);        
	}

	public function exec(){
		return curl_exec($this->ch);
	}
	public function error(){
		return curl_error($this->ch);
	}
	public function __destruct(){
		curl_close($this->ch);

	}
}

