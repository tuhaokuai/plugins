<?php
/**
 * 图好快提供的全站图片加速服务
 *
 * @since discuz 7.2
 * @link http://www.tuhaokuai.com
 * @copyright 上海枫雪信息科技有限公司 
 * 
 */
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(defined('IN_ADMINCP')){
	return;
}

class plugin_tuhaokuai {
	public $url = "http://s1.tuhaokuai.com";
	function viewthread_postheader_output(){
		global  $postlist;
		foreach($postlist  as $k=>$v){
			unset($c,$c1,$arr);
			if(array_key_exists('message',$v) && $v['message']){
				$c = $v['message'];
				$arr = $this->image($c);
				$arr1 = $this->image($c,'file');
				if($arr){
					foreach ($arr as $ui){
						$c = str_replace($ui,$this->link($ui),$c);
					}
				}
				if($arr1){
					foreach ($arr1 as $ui){
						$c = str_replace($ui,$this->link($ui),$c);
					}
				}
				$postlist[$k]['message'] = $v['message'] = $c;
			}
			
			if(array_key_exists('avatar',$v) && $v['avatar']){
				$c = $v['avatar'];
				$arr = $this->image($c);
				$ui = $arr[0];
				if(strpos($ui,'.php')===false){
					$postlist[$k]['avatar'] = str_replace($ui,$this->link($ui),$c);
				}
			}
			if(array_key_exists('attachments',$v) && $v['attachments']){
				$c1 = $v['attachments'];
				foreach($c1 as $key1=>$value1){
					$c = $value1['attachment'];
					$postlist[$k]['attachments'][$key1]['url'] =  $this->link($value1['url']);
				}
			}
		}
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


class plugin_tuhaokuai_forum extends plugin_tuhaokuai {

	function common() {
		
		
	}


}


 

