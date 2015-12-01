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
		$data = $this->content($data);
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


class tuhaokuai_dev {

    public $url = "http://s1.tuhaokuai.com";
    public $useJsLink = true;
    public $useCssLink = true;
    public $useImageLink = true;
    public $useHrefLink = true;

    public $https = false;
    static $NoRepeat = array();
    public $allowExt = array(
            'jpg','png','jpeg','bmp','gif','js','css'
        );
    public $allowDomain = array(
            'googleapis.com'
    );
    function output($string){
         if($this->useImageLink === true){
             $string = $this->replace('image',$string);
         }
         if($this->useHrefLink === true){
             $string = $this->replace('href',$string,['jpg','jpeg','png','gif']);
         }
         if($this->useCssLink === true){
             $string = $this->replace('linkStyle',$string);
         }
         if($this->useJsLink === true){
             $string = $this->replace('script',$string);
         }
         return $string;
    }
    

    function replace($type="image",$string,$in = false){
         $ar = $this->$type($string);
         if(!$ar){
            return $string;
         }
          
         foreach($ar as $v){
            if(strpos($v,$this->url) === false){
                if(is_array($in)){
                    $ext  = substr($v,strrpos($v,'.')+1);
                    if(!in_array($ext,$in)){
                        continue;
                    }

                } 
               // echo $v."\n";
               // echo $this->linkNew($v)."\n";

                $string = str_replace($v,$this->linkNew($v),$string);
            }
         } 
         return $string; 
    }
    
    /**
     * 
     * create new link
     * @param $url
     */
    function linkNew($url){
        $key = 'tuhaokuailink'.md5($url);
        if(isset(static::$NoRepeat[$key])){
            return  static::$NoRepeat[$key];
        }
        $ext = substr($url,strrpos($url,'.')+1); 
        
        if(!in_array($ext,$this->allowExt) && !$this->allowDomain($url)){
            static::$NoRepeat[$key] = $url;
            return  $url;
        }

        $host = $_SERVER['HTTP_HOST'];
        $top = 'http:';
        if($this->https === true){
            $top = 'https:';
        } 

        if(substr($url,0,2)=='//'){
            $url = "/".$host."/http:".$url;
        }else if(substr($url,0,1)=='/'){
            $url = "/".$host.$url;
        }

        $eurl  = $url;
        if(strpos($eurl,'http://')!==false || strpos($eurl,'https://')!==false){
            
            $eurl = str_replace('http://','',$eurl);
            $eurl = str_replace('https://','',$eurl);

            $hostCheck = substr($eurl,0,strpos($eurl,'/'));
            if($hostCheck == $host){
                $url = "/".$eurl;
            }else{
                $url = "/".$host."/".$url;
            }

        } 

       
        $url = str_replace('//', '/', $url);
        $url = $this->url.$url;
        static::$NoRepeat[$key] = $url;
        return  $url;
    }
    
    function allowDomain($url){
        foreach($this->allowDomain as $v){
            if(strpos($url,$v)!==false){
                return true;
            }
        }
        return false;
    }
    /**
    *  get image url
    */
    function image($content,$tag = 'src'){ 
        $preg = "/<\s*img\s+[^>]*?$tag\s*=\s*(\'|\")(.*?)\\1[^>]*?\/?\s*>/i";
        preg_match_all($preg,$content,$out);
        return $out[2];  
    }

    function href($content){ 
        $preg = "/<\s*a\s+[^>]*?href\s*=\s*(\'|\")(.*?)\\1[^>]*?\/?\s*>/i";
        preg_match_all($preg,$content,$out);
        return $out[2];  
    }

    function linkStyle($content){ 
        $preg = "/<\s*link\s+[^>]*?href\s*=\s*(\'|\")(.*?)\\1[^>]*?\/?\s*>/i";
        preg_match_all($preg,$content,$out);
        return $out[2];  
    }

    function script($content){ 
        $preg = "/<\s*script\s+[^>]*?src\s*=\s*(\'|\")(.*?)\\1[^>]*?\/?\s*>/i";
        preg_match_all($preg,$content,$out);
        return $out[2];  
    }
    
    
     

}


 