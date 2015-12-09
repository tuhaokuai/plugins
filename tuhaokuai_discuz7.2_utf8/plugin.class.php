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

if($_SERVER['SCRIPT_NAME'] == '/admincp.php'){
	return;
}

include dirname(__FILE__)."/tuhaokuai.php";

 
class plugin_tuhaokuai extends tuhaokuai {
	public $url = "http://s1.tuhaokuai.com";
	 
	function viewthread_postheader_output(){
		global  $postlist;
		foreach($postlist  as $k=>$v){
			unset($c,$arr);
			if(array_key_exists('message',$v) && $v['message']){
				$c = $v['message'];
				$arr = $this->image($c);
				$arr1 = $this->image($c,'file');
				if($arr){
					foreach ($arr as $ui){
						$c = str_replace($ui,$this->linkNew($ui),$c);
					}
				}
				if($arr1){
					foreach ($arr1 as $ui){
						$c = str_replace($ui,$this->linkNew($ui),$c);
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
			
			 
			
		}
		
	 
		
	}
	
	
	 
     

}


 

