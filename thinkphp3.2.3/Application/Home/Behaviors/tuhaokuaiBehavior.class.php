<?php
namespace Home\Behaviors;
use tuhaokuai;

class tuhaokuaiBehavior extends \Think\Behavior{
    
    public function run(&$param){
    	$tu = new tuhaokuai;
    	$param = $tu->output($param);
     
    }

    
}