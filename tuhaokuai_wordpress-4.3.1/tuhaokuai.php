<?php
defined( 'ABSPATH' )  or exit; 
/*
Plugin Name: 图好快-全站加速
Plugin URI: http://www.tuhaokuai.com
Description: 对网站JS CSS 图片等进行加速
Author:  枫雪信息科技有限公司
Version: 1.0
Author URI: http://tuhaokuai.com
*/
class tuhaokuai{
	public $content;
	public $url = "http://s1.tuhaokuai.com";
	function __construct(){
		//后台菜单
		add_action('admin_menu',array($this, 'add_menu' ));
		if(strpos($_SERVER['REQUEST_URI'],'/wp-admin/') !== false) return;
		//更改 CSS JS 链接地址
		add_action('style_loader_src',  array($this, 'cdn_link')    );
		add_action('script_loader_src',  array($this, 'cdn_link')    );
		//更改内容中的图片地址
		add_filter('the_content',array($this,'content') );
		add_action('init',array($this,'wp_header') );
		add_action('wp_footer',array($this,'wp_footer') );
		
		
	}
	/*********后台菜单******************/
	function add_menu(){
		global $aad_settings;
		//$aad_settings = add_menu_page(__('CDNZIP', 'aad'), __('CDNZIP Setting', 'aad'), 'manage_options',dirname(__FILE__) ,array($this,'add_menu_view'),false, 99);
	}
	 
	
	function add_menu_view(){
		return;
		if($_POST){
			$key = array('cdnzip_client_id','cdnzip_client_secret');
			foreach($key as $k){
				$value = trim($_POST[$k]);
				if(!$value) continue;
				if(get_option($k)){
					update_option($k , $value);
				}else{
					add_option($k , $value);
				}
			}
			$msg = __('Save Succcess','add');
		}
		 echo '  
		<div class="wrap">
	
	        <h2>'. __('CDNZIP Setting', 'aad') .'</h2>
			<h3>'.__('please visit http://www.cdnzip.com  get client_id/client_secret code','add').'</h3>
	        <form id="aad-form" method="post">
		        <table class="form-table">
				<tbody>
					<tr>
						<th scope="row"><label for="client_id">'.__('client_id').'</label></th>
						<td><input type="text" class="regular-text" value="'.get_option('cdnzip_client_id').'" id="client_id" name="cdnzip_client_id"></td>
					<tr>
		 			<tr>
		 				<th scope="row"><label for="client_secret">'.__('client_secret').'</label></th>
						<td><input type="text" class="regular-text" value="'.get_option('cdnzip_client_secret').'" id="client_secret" name="cdnzip_client_secret"></td>
					</tr>
					<tr>
						<th scope="row"><input type="submit" name="aad-submit" id="aad_submit" class="button-primary" value="'. __('Save', 'aad').'"/></th>
						<td>  </td>
					</tr>
				</tbody>
				</table>
				'.$msg.'
			</form>
        

       	 

    	</div>';
    
	}
	
	
	/*********图片链接换成CDN***********/
	function wp_header(){
		ob_start();
	}
	
	function wp_footer(){  
	    $data = trim(ob_get_contents());   
		ob_end_clean();
		$data = $this->content($data);
		//$data =  preg_replace(array('/ {2,}/','/<!--.*?-->|\t|(?:\r?\n[\t]*)+/s'),array(' ',''),$data);
		echo $data;
	}
    
	 
	
	function content($content){  
		$r = $this->image($content);
		if(!$r) return $content;
		foreach($r as $v){
			$content = str_replace($v,$this->link($v) , $content);
		}
		return $content;
	}
	
	function cdn_link($src){
		if( strpos( $src, 'ver=' ) )
			$src = remove_query_arg( 'ver', $src );
		return $this->link($src);
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
	function image($content){ 
		$preg = "/<\s*img\s+[^>]*?src\s*=\s*(\'|\")(.*?)\\1[^>]*?\/?\s*>/i";
		preg_match_all($preg,$content,$out);
		$out1 = $out[1];
		if($out1){
			$i = 0;
			foreach($out1 as $v){
				$new_img[] = $v.$out[2][$i];
				$i++;
			}
			
		}
		return $new_img;  
	} 

}

$cdnzip =  new tuhaokuai; 

 