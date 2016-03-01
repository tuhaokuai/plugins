安装说明
===
1.复制`include/tuhaokuai.php`到网站`include`目录下

2.手动修改 `include/dedetag.class.php`

第二行加入以下代码

    include dirname(__FILE__).'/tuhaokuai.php';

查找 `function GetResult()`在`return $ResultString; `这行直接替换为以下代码

        //图好快代码
        $tu = new tuhaokuai;
        $ResultString = $tu->output($ResultString);
        //end 图好快
        
        return $ResultString;






官方测试时并没有改变 `GetResultNP` 方法
如发现网站中有部分使用了 `GetResultNP` 替换方法与上相同。     

     