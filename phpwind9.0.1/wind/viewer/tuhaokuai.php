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
    public $doti = true;//是否开启加速域名
    public $fixPort  = false; // 修复../
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
         if($this->doti !== true){
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
                if(!static::$only[md5($v)]){
                    $string = str_replace($v,$this->linkNew($v),$string);
                    static::$only[md5($v)] = true;
                }
            }
         } 
         return $string; 
    }
    
    static $tryFixPortNum = 0;
    
    function tryFixPort($url){
        $i = substr_count($url,'../');
        if($i>0){

            $res = $_SERVER['REQUEST_URI'];     
            $ai = explode('/', $res);
            array_pop($ai);
            $ai = array_filter($ai);
            
            $ai = array_merge($ai,[]);
            $ai = array_reverse($ai);
      //      print_r($ai);
            $url = str_replace('../', '', $url);
            $url = str_replace('./', '/', $url);
            $q = $i;
           // echo $url.'/';
            for($j=0;$j<$i;$j++){
                $r[] = $ai[$j];
             //   echo $url."<br>";
              //  print_r($ai);
                
            }
            $r = array_reverse($r);
            $rs = implode('/',$r);
            
            $url = $rs.'/'.$url;
            $url = str_replace('//', '/', $url);

        }
        //echo $url;exit;

        return $url;
    }
    /**
     * 
     * create new link
     * @param $url
     */
    function linkNew($url){ 

        static::$tryFixPortNum = 0;

        if(strpos($url,$this->url)!==false){
            return $url;
        }
        



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

        //对URL分析
        // http://yourdomain/a/b/c.jpg
        // return  http://s1.tuhaokuai.com/yourdomain/a/b/c.jpg
        if(strpos($url,$top.'//'.$host)!==false){ 
            $url = $this->url.'/'.substr($url,strlen($top."//"));
            static::$NoRepeat[$key] = $url;
            return  $url;
        }

        // http://notyourdomain/a/b/c.jpg
        // return  http://s1.tuhaokuai.com/yourdomain/http://notyourdomain/a/b/c.jpg
        
        if(substr($url,0,7)=='http://'){
            $url = "/".$host."/".$url;
            $url = $this->url.$url;
            static::$NoRepeat[$key] = $url;
            return  $url;
        }

        if(substr($url,0,2)=='//'){
            $url = "/".$host."/http:".$url;
            $url = $this->url.$url;
            static::$NoRepeat[$key] = $url;
            return  $url;
        }


        if(substr($url,0,1)=='/'){
            $url = "/".$host.$url;
            $url = $this->url.$url;
            static::$NoRepeat[$key] = $url;
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