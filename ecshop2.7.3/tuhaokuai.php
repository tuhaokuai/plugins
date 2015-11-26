<?php
/**
 * 图好快提供的全站图片加速服务
 *
 * @since ECSHOP  2.7.3
 * @link http://www.tuhaokuai.com
 * @copyright 上海枫雪信息科技有限公司 
 * 
 */
 



//ini_set('display_errors', 1);
class tuhaokuai {

	public $url = "http://s1.tuhaokuai.com";

	function output($string){
		 if(strpos( $_SERVER['SCRIPT_NAME'] ,'/admin/')!==false ){
			return $string;
		 }
		 $ar = $this->image($string);
		 if(!$ar){
		 	return $string;
		 }
		 foreach($ar as $v){
		 	$string = str_replace($v,$this->link($v),$string);
		 }
		 return $string; 
		
	}
	
	
	/**
	 * 
	 * create new link
	 * @param $url
	 */
	function link($url){
		
		$ext = substr($url,strrpos($url,'.'));
		
		$arr = [
			'jpg','png','jpeg','bmp','gif','js','css'
		];
		 
		$host = $_SERVER['HTTP_HOST'];
		$a = 'http://'.$host;
		$b = 'https://'.$host;
		if(strpos($url,$a) === false){
			return $this->url.'/'.substr($a,7).'/'.$url;
		}else{
			return str_replace($a,$this->url.'/'.$host,$url);
		}
		if(strpos($url,$b) === false){
			return $this->url.'/'.substr($b,8).'/'.$url;
		}else{
			return str_replace($a,$this->url.'/'.$host,$url);
		}
		
		return  $url;
	}
	
	/**
	*  get image url
	*/
	function image($content,$tag = 'src'){ 
		$preg = "/<\s*img\s+[^>]*?$tag\s*=\s*(\'|\")(.*?)\\1[^>]*?\/?\s*>/i";
		preg_match_all($preg,$content,$out);
		return $out[2];  
	}
	
     

}


 

