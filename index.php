<?php

/**
 * @Author: jsy135135
 * @email:732677288@qq.com
 * @Date:   2017-12-26 11:55:20
 * @Last Modified by:   jsy135135
 * @Last Modified time: 2017-12-26 11:59:01
 */
// 引入类
require './wechat.class.php';
$wechat = new Wechat();
// 判断是进行调用何种方法
if($_GET["echostr"])
{
  // 验证方法
  $wechat->valid();
}else{
  // 调用消息管理方法
  $wechat->responseMsg();
}