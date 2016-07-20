<?php
/**
 * 图好快提供的全站图片加速服务
 *
 * @since PHP 通用
 * @link http://www.tuhaokuai.com
 * @copyright 上海枫雪信息科技有限公司 
 * 
 */
/*
$tu = new tuhaokuai;
echo $tu->linkNew('/baidu.jpg')."<br>";
echo $tu->linkNew('http://www/baidu.jpg')."<br>";
echo $tu->linkNew('http://a/baidu.jpg')."<br>";
*/
class tuhaokuai {
    public $url = "http://s1.tuhaokuai.com";
    public $open = true;//是否开启加速域名
    public $fixPort = true;
    public $useJsLink = false;
    public $useCssLink = false;
    public $useImageLink = true;
    public $useHrefLink = false;
    public $https = false;
    static $NoRepeat = array();
    static $only = array();
    public $allowExt = array(
            'jpg','png','jpeg','bmp','gif','js','css'
        );
    public $allowDomain = array(
            'googleapis.com'
    );
    function output($string){
         if($this->open !== true){
            return $string;
         }
         if(strpos( $_SERVER['SCRIPT_NAME'] ,'/admin/')!==false ){
            return $string;
         }
         if($this->useImageLink === true){
             $string = $this->replace('image',$string);
         }
         if($this->useHrefLink === true){
             $string = $this->replace('href',$string,array('jpg','jpeg','png','gif'));
         }
         if($this->useCssLink === true){
             $string = $this->replace('linkStyle',$string);
         }
         if($this->useJsLink === true){
             $string = $this->replace('script',$string);
         }

         $string = $this->replace_script_ecshop($string);
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
                if(!self::$only[md5($v)]){
                    $string = str_replace($v,$this->linkNew($v),$string);
                    self::$only[md5($v)] = true;
                }
            }
         } 
         return $string; 
    }
    

    function replace_script_ecshop($string){
        if(strpos($_SERVER['REQUEST_URI'],'mobile')!==false){
            $host = $_SERVER['HTTP_HOST'];
            preg_match("/img1=new Image\(\);img1\.src='(.*)'/", $string, $matches);
            if($matches[0] && $matches[1]){
                $cdn_img = $matches[1];
                $cdn_img = '/'.str_replace('./../','',$cdn_img);

                $new = "img1=new Image();img1.src='".$this->url.'/'.$host.$cdn_img."'";
                $string = str_replace($matches[0],$new,$string);
            }
            

            
        }
        return $string;
    }

    
   
    
    function tryFixPort($url){
        if(strpos($_SERVER['REQUEST_URI'],'mobile')!==false){
            if(strpos($url,'./../')!==false){
                $url = str_replace('./../','',$url);
            }
            
        }
        return $url;
    }


    /**
     * 
     * create new link
     * @param $url
     */
    function linkNew($url){ 
        if(strpos($url,$this->url)!==false){
            return $url;
        }
        
        $key = 'tuhaokuailink'.md5($url);
        if(isset(self::$NoRepeat[$key])){
            return  self::$NoRepeat[$key];
        }
        $ext = substr($url,strrpos($url,'.')+1); 
        
        if(!in_array($ext,$this->allowExt) && !$this->allowDomain($url)){
            self::$NoRepeat[$key] = $url;
            return  $url;
        }
        $host = $_SERVER['HTTP_HOST'];
        $top = 'http:';
        if($this->https === true){
            $top = 'https:';
        }  
        //对URL分析
        // http://yourdomain/a/b/c.jpg
        // return  http://s1.tuhaokuai.com/yourdomain/a/b/c.jpg
        if(strpos($url,$top.'//'.$host)!==false){ 
            $url = $this->url.'/'.substr($url,strlen($top."//"));
            self::$NoRepeat[$key] = $url;
            return  $url;
        }
        // http://notyourdomain/a/b/c.jpg
        // return  http://s1.tuhaokuai.com/yourdomain/http://notyourdomain/a/b/c.jpg
        
        if(substr($url,0,7)=='http://'){
            $url = "/".$host."/".$url;
            $url = $this->url.$url;
            self::$NoRepeat[$key] = $url;
            return  $url;
        }
        if(substr($url,0,2)=='//'){
            $url = "/".$host."/http:".$url;
            $url = $this->url.$url;
            self::$NoRepeat[$key] = $url;
            return  $url;
        }
        if(substr($url,0,1)=='/'){
            $url = "/".$host.$url;
            $url = $this->url.$url;
            self::$NoRepeat[$key] = $url;
            return  $url;
        }
     
         //处理../../目录
        
        if($this->fixPort === true && strpos($url,'../')!==false){
             
            $url = $this->tryFixPort($url);
            
        }
        
        return $this->url.'/'.$host.'/'.$url;
        
        
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