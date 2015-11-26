# 图好快 图片加速插件 

ecshop2.7.3

安装步骤

1.系复制 `tuhaokuai.php` 到 `ecshop2.7.3` 安装好的根目录。

2.打开 `includes/cls_template.php` 找到 `display` 方法，部分代码如下

或直接复制 `cls_template.php` 替换  `includes/cls_template.php` 


	 /**
     * 显示页面函数
     *
     * @access  public
     * @param   string      $filename
     * @param   sting      $cache_id
     *
     * @return  void
     */
    function display($filename, $cache_id = '')
    {
         
        //ini_set('display_errors',1);
         

        $this->_seterror++;
        error_reporting(E_ALL ^ E_NOTICE);

        $this->_checkfile = false;
        $out = $this->fetch($filename, $cache_id);

        //使用图片快图片加速功能
        include dirname(__FILE__).'/../tuhaokuai.php';
        $tu = new tuhaokuai;
        $out = $tu->output($out);
        
        //

        if (strpos($out, $this->_echash) !== false)
        {

        



其中

        
        //使用图片快图片加速功能
        include dirname(__FILE__).'/../tuhaokuai.php';
        $tu = new tuhaokuai;
        $out = $tu->output($out);
        
        //


为重要代码，使用本代码可使用图片加速功能
