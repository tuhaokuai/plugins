<?php
include 'tuhaokuai.php';

$s = file_get_contents('test.html');

$tu = new tuhaokuai;
echo $tu->output($s);
