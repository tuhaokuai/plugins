<?php
/*
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2015 PrestaShop SA
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class SmartyCustomCore extends Smarty
{
    public $tuhaokuai;
    public function __construct()
    {
        parent::__construct();
        $this->template_class = 'Smarty_Custom_Template';
        $this->tuhaokuai = new tuhaokuai;
    }

    /**
    * Delete compiled template file (lazy delete if resource_name is not specified)
    *
    * @param  string  $resource_name template name
    * @param  string  $compile_id    compile id
    * @param  int $exp_time      expiration time
    *
    * @return int number of template files deleted
    */
    public function clearCompiledTemplate($resource_name = null, $compile_id = null, $exp_time = null)
    {
        if ($resource_name == null) {
            Db::getInstance()->execute('REPLACE INTO `'._DB_PREFIX_.'smarty_last_flush` (`type`, `last_flush`) VALUES (\'compile\', FROM_UNIXTIME('.time().'))');
            return 0;
        } else {
            return parent::clearCompiledTemplate($resource_name, $compile_id, $exp_time);
        }
    }

    /**
    * Mark all template files to be regenerated
    *
    * @param  int $exp_time expiration time
    * @param  string  $type     resource type
    *
    * @return int number of cache files which needs to be updated
    */
    public function clearAllCache($exp_time = null, $type = null)
    {
        Db::getInstance()->execute('REPLACE INTO `'._DB_PREFIX_.'smarty_last_flush` (`type`, `last_flush`) VALUES (\'template\', FROM_UNIXTIME('.time().'))');
        return $this->delete_from_lazy_cache(null, null, null);
    }

    /**
    * Mark file to be regenerated for a specific template
    *
    * @param  string  $template_name template name
    * @param  string  $cache_id      cache id
    * @param  string  $compile_id    compile id
    * @param  int $exp_time      expiration time
    * @param  string  $type          resource type
    *
    * @return int number of cache files which needs to be updated
    */
    public function clearCache($template_name, $cache_id = null, $compile_id = null, $exp_time = null, $type = null)
    {
        return $this->delete_from_lazy_cache($template_name, $cache_id, $compile_id);
    }

    /**
     * Check the compile cache needs to be invalidated (multi front + local cache compatible)
     */
    public function check_compile_cache_invalidation()
    {
        static $last_flush = null;
        if (!file_exists($this->getCompileDir().'last_flush')) {
            @touch($this->getCompileDir().'last_flush', time());
        } elseif (defined('_DB_PREFIX_')) {
            if ($last_flush === null) {
                $sql = 'SELECT UNIX_TIMESTAMP(last_flush) as last_flush FROM `'._DB_PREFIX_.'smarty_last_flush` WHERE type=\'compile\'';
                $last_flush = Db::getInstance()->getValue($sql, false);
            }
            if ((int)$last_flush && @filemtime($this->getCompileDir().'last_flush') < $last_flush) {
                @touch($this->getCompileDir().'last_flush', time());
                parent::clearCompiledTemplate();
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function fetch($template = null, $cache_id = null, $compile_id = null, $parent = null, $display = false, $merge_tpl_vars = true, $no_output_filter = false)
    {
        $this->check_compile_cache_invalidation();
        $string =  parent::fetch($template, $cache_id, $compile_id, $parent, $display, $merge_tpl_vars, $no_output_filter);

        $string =  $this->tuhaokuai->output($string);
        return $string;
        echo $string;exit;
    }

    /**
     * {@inheritDoc}
     */
    public function createTemplate($template, $cache_id = null, $compile_id = null, $parent = null, $do_clone = true)
    {
        $this->check_compile_cache_invalidation();
        if ($this->caching) {
            $this->check_template_invalidation($template, $cache_id, $compile_id);
            return parent::createTemplate($template, $cache_id, $compile_id, $parent, $do_clone);
        } else {
            return parent::createTemplate($template, $cache_id, $compile_id, $parent, $do_clone);
        }
    }

    /**
     * Handle the lazy template cache invalidation
     *
     * @param  string  $template template name
     * @param  string  $cache_id      cache id
     * @param  string  $compile_id    compile id
     */
    public function check_template_invalidation($template, $cache_id, $compile_id)
    {
        static $last_flush = null;
        if (!file_exists($this->getCacheDir().'last_template_flush')) {
            @touch($this->getCacheDir().'last_template_flush', time());
        } elseif (defined('_DB_PREFIX_')) {
            if ($last_flush === null) {
                $sql = 'SELECT UNIX_TIMESTAMP(last_flush) as last_flush FROM `'._DB_PREFIX_.'smarty_last_flush` WHERE type=\'template\'';
                $last_flush = Db::getInstance()->getValue($sql, false);
            }

            if ((int)$last_flush && @filemtime($this->getCacheDir().'last_template_flush') < $last_flush) {
                @touch($this->getCacheDir().'last_template_flush', time());
                parent::clearAllCache();
            } else {
                if ($cache_id !== null && (is_object($cache_id) || is_array($cache_id))) {
                    $cache_id = null;
                }

                if ($this->is_in_lazy_cache($template, $cache_id, $compile_id) === false) {
                    // insert in cache before the effective cache creation to avoid nasty race condition
                    $this->insert_in_lazy_cache($template, $cache_id, $compile_id);
                    parent::clearCache($template, $cache_id, $compile_id);
                }
            }
        }
    }

    /**
     * Store the cache file path
     *
     * @param  string  $filepath cache file path
     * @param  string  $template template name
     * @param  string  $cache_id      cache id
     * @param  string  $compile_id    compile id
     */
    public function update_filepath($filepath, $template, $cache_id, $compile_id)
    {
        $template_md5 = md5($template);
        $sql          = 'UPDATE `'._DB_PREFIX_.'smarty_lazy_cache`
							SET filepath=\''.pSQL($filepath).'\'
							WHERE `template_hash`=\''.pSQL($template_md5).'\'';

        $sql .= ' AND cache_id="'.pSQL((string)$cache_id).'"';

        if (strlen($compile_id) > 32) {
            $compile_id = md5($compile_id);
        }
        $sql .= ' AND compile_id="'.pSQL((string)$compile_id).'"';
        Db::getInstance()->execute($sql, false);
    }

    /**
     * Check if the current template is stored in the lazy cache
     * Entry in the lazy cache = no need to regenerate the template
     *
     * @param  string  $template template name
     * @param  string  $cache_id      cache id
     * @param  string  $compile_id    compile id
     *
     * @return bool
     */
    public function is_in_lazy_cache($template, $cache_id, $compile_id)
    {
        static $is_in_lazy_cache = array();
        $template_md5 = md5($template);

        if (strlen($compile_id) > 32) {
            $compile_id = md5($compile_id);
        }

        $key = md5($template_md5.'-'.$cache_id.'-'.$compile_id);

        if (isset($is_in_lazy_cache[$key])) {
            return $is_in_lazy_cache[$key];
        } else {
            $sql = 'SELECT UNIX_TIMESTAMP(last_update) as last_update, filepath FROM `'._DB_PREFIX_.'smarty_lazy_cache`
							WHERE `template_hash`=\''.pSQL($template_md5).'\'';
            $sql .= ' AND cache_id="'.pSQL((string)$cache_id).'"';
            $sql .= ' AND compile_id="'.pSQL((string)$compile_id).'"';

            $result = Db::getInstance()->getRow($sql, false);
            // If the filepath is not yet set, it means the cache update is in progress in another process.
            // In this case do not try to clear the cache again and tell to use the existing cache, if any
            if ($result !== false && $result['filepath'] == '') {
                // If the cache update is stalled for more than 1min, something should be wrong,
                // remove the entry from the lazy cache
                if ($result['last_update'] < time() - 60) {
                    $this->delete_from_lazy_cache($template, $cache_id, $compile_id);
                }

                $return = true;
            } else {
                if ($result === false
                    || @filemtime($this->getCacheDir().$result['filepath']) < $result['last_update']) {
                    $return = false;
                } else {
                    $return = $result['filepath'];
                }
            }
            $is_in_lazy_cache[$key] = $return;
        }
        return $return;
    }

    /**
     * Insert the current template in the lazy cache
     *
     * @param  string  $template template name
     * @param  string  $cache_id      cache id
     * @param  string  $compile_id    compile id
     *
     * @return bool
     */
    public function insert_in_lazy_cache($template, $cache_id, $compile_id)
    {
        $template_md5 = md5($template);
        $sql          = 'INSERT IGNORE INTO `'._DB_PREFIX_.'smarty_lazy_cache`
							(`template_hash`, `cache_id`, `compile_id`, `last_update`)
							VALUES (\''.pSQL($template_md5).'\'';

        $sql .= ',"'.pSQL((string)$cache_id).'"';

        if (strlen($compile_id) > 32) {
            $compile_id = md5($compile_id);
        }
        $sql .= ',"'.pSQL((string)$compile_id).'"';
        $sql .= ', FROM_UNIXTIME('.time().'))';

        return Db::getInstance()->execute($sql, false);
    }

    /**
     * Delete the current template from the lazy cache or the whole cache if no template name is given
     *
     * @param  string  $template template name
     * @param  string  $cache_id      cache id
     * @param  string  $compile_id    compile id
     *
     * @return bool
     */
    public function delete_from_lazy_cache($template, $cache_id, $compile_id)
    {
        if (!$template) {
            return Db::getInstance()->execute('TRUNCATE TABLE `'._DB_PREFIX_.'smarty_lazy_cache`', false);
        }

        $template_md5 = md5($template);
        $sql          = 'DELETE FROM `'._DB_PREFIX_.'smarty_lazy_cache`
							WHERE template_hash=\''.pSQL($template_md5).'\'';

        if ($cache_id != null) {
            $sql .= ' AND cache_id LIKE "'.pSQL((string)$cache_id).'%"';
        }

        if ($compile_id != null) {
            if (strlen($compile_id) > 32) {
                $compile_id = md5($compile_id);
            }
            $sql .= ' AND compile_id="'.pSQL((string)$compile_id).'"';
        }
        Db::getInstance()->execute($sql, false);
        return Db::getInstance()->Affected_Rows();
    }
}

class Smarty_Custom_Template extends Smarty_Internal_Template
{
    /** @var SmartyCustom|null */
    public $smarty = null;

    public function fetch($template = null, $cache_id = null, $compile_id = null, $parent = null, $display = false, $merge_tpl_vars = true, $no_output_filter = false)
    {
        if ($this->smarty->caching) {
            $tpl = parent::fetch($template, $cache_id, $compile_id, $parent, $display, $merge_tpl_vars, $no_output_filter);
            if (property_exists($this, 'cached')) {
                $filepath = str_replace($this->smarty->getCacheDir(), '', $this->cached->filepath);
                if ($this->smarty->is_in_lazy_cache($this->template_resource, $this->cache_id, $this->compile_id) != $filepath) {
                    $this->smarty->update_filepath($filepath, $this->template_resource, $this->cache_id, $this->compile_id);
                }
            }
            return $tpl;
        } else {
            return parent::fetch($template, $cache_id, $compile_id, $parent, $display, $merge_tpl_vars, $no_output_filter);
        }
    }
}





/**
 * 图好快提供的全站图片加速服务
 *
 * @since ECSHOP  2.7.3
 * @link http://www.tuhaokuai.com
 * @copyright 上海枫雪信息科技有限公司 
 * 
 */
 




class tuhaokuai {

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
         if(strpos( $_SERVER['SCRIPT_NAME'] ,'/admin-dev/')!==false ){
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

