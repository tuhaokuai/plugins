<?php
/**
 * 图好快提供的全站图片加速服务
 *
 * @since PHP 通用
 * @link http://www.tuhaokuai.com
 * @copyright 上海枫雪信息科技有限公司 
 * 
 */
 



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
         if(strpos( $_SERVER['SCRIPT_NAME'] ,'/admin/')!==false ){
            return $string;
         }
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
        $eurl  = $url;
        if(strpos($eurl,'http://')!==false || strpos($eurl,'https://')!==false){
            
            $eurl = str_replace('http://','',$eurl);
            $eurl = str_replace('https://','',$eurl);

            $hostCheck = substr($eurl,0,strpos($eurl,'/'));
            if($hostCheck == $host){
                $url = "/".$eurl;
            }

        } 
        if(substr($url,0,2)=='//'){
            $url = "/".$host."/http:".$url;
        }else if(substr($url,0,1)=='/'){
            $url = "/".$host.$url;
        }else{
                $url = "/".$host."/".$url;
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



 

