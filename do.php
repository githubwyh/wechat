<?php 
require './wechat.class.php';
$wechat = new Wechat();
echo $wechat->getAccessToken();