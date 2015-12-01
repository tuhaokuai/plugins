<?php
defined( 'ABSPATH' )  or exit; 
include dirname(__FILE__)."/tuhaokuai.class.php";
/*
Plugin Name: 图好快-全站加速
Plugin URI: http://www.tuhaokuai.com
Description: 对网站JS CSS 图片等进行加速
Author:  枫雪信息科技有限公司
Version: 1.0
Author URI: http://tuhaokuai.com
*/
class tuhaokuai extends tuhaokuai_dev{
	public $content;
	 
	function __construct(){
		 
		if(strpos($_SERVER['REQUEST_URI'],'/wp-admin/') !== false) return;
		//更改 CSS JS 链接地址
		add_action('style_loader_src',  array($this, 'wp_link')    );
		add_action('script_loader_src',  array($this, 'wp_link')    );
		//更改内容中的图片地址
		add_filter('the_content',array($this,'wp_content') );
		add_action('init',array($this,'wp_header') );
		add_action('wp_footer',array($this,'wp_footer') );
		
		
	} 
	/*********图片链接换成CDN***********/
	function wp_header(){
		ob_start();
	}
	
	function wp_footer(){  
	    $data = trim(ob_get_contents());   
		ob_end_clean();
		$data = $this->wp_content($data);
		//$data =  preg_replace(array('/ {2,}/','/<!--.*?-->|\t|(?:\r?\n[\t]*)+/s'),array(' ',''),$data);
		echo $data;
	}
    
	 
	
	function wp_content($content){  
		return $this->output($content);
	}
	
	function wp_link($src){
		if( strpos( $src, 'ver=' ) )
			$src = remove_query_arg( 'ver', $src );
		return $this->linkNew($src);
	}
	 
}

$cdnzip =  new tuhaokuai; 


 

 