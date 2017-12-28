<?php

// 引入配置文件
require './wechat.cfg.php';

/**
 * @Author: jsy135135
 * @email:732677288@qq.com
 * @Date:   2017-12-26 11:53:10
 * @Last Modified by:   jsy135135
 * @Last Modified time: 2017-12-26 15:32:38
 */
class Wechat
{
    public function __construct(){
        $this->textTpl = "<xml>
        <ToUserName><![CDATA[%s]]></ToUserName>
        <FromUserName><![CDATA[%s]]></FromUserName>
        <CreateTime>%s</CreateTime>
        <MsgType><![CDATA[%s]]></MsgType>
        <Content><![CDATA[%s]]></Content>
        <FuncFlag>0</FuncFlag>
        </xml>";
        $this->appid = APPID;
        $this->appsecret = APPSECRET;
      }
    // 验证方法
    public function valid()
    {
        $echoStr = $_GET["echostr"];
        //valid signature , option
        if ($this->checkSignature()) {
            echo $echoStr;
            exit;
        }
    }
    // 消息管理
    public function responseMsg()
    {
        //get post data, May be due to the different environments
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        // file_put_contents('str.xml', $postStr);
        //extract post data
        if (!empty($postStr)) {
            /* libxml_disable_entity_loader is to prevent XML eXternal Entity Injection,
              the best way is to check the validity of xml by yourself */
            libxml_disable_entity_loader(true);
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            // 不同的消息类型，使用不同的处理方法
            switch ($postObj->MsgType) {
              case 'text':
                //接收文本消息处理方法
                $this->doText($postObj);
                break;
              case 'image':
                //接收图片消息处理方法
                $this->doImage($postObj);
                break;
              case 'voice':
                //接收语音消息处理方法
                $this->doVoice($postObj);
                break;
              case 'location':
                //接收地理位置消息处理方法
                $this->doLocation($postObj);
                break;
              default:
                # code...
                break;
            }
        }
    }
    // 校验签名
    private function checkSignature()
    {
        // you must define TOKEN by yourself
        if (!defined("TOKEN")) {
            throw new Exception('TOKEN is not defined!');
        }

        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        // use SORT_STRING rule
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);

        if ($tmpStr == $signature) {
            return true;
        } else {
            return false;
        }
    }

    // 文本消息处理
    private function doText($postObj)
    {
      $keyword = trim($postObj->Content);
      
      if (!empty($keyword)) {
          // $contentStr = "Welcome to wechat world!";
          $contentStr = "Hello PHP world!";
          if($keyword == '你是谁'){
            $contentStr = "目前我也不知道，我是谁，我是谁的谁！";
          }
          // 接入自动回复机器人
          $url = "http://api.qingyunke.com/api.php?key=free&appid=0&msg=".$keyword;
          $content = file_get_contents($url);
          // json转对象
          $content = json_decode($content);
          // 调用对象属性，回复用户的内容
          $contentStr = str_replace("{br}", "\r", $content->content);
          $resultStr = sprintf($this->textTpl, $postObj->FromUserName, $postObj->ToUserName, time(), "text", $contentStr);
          echo $resultStr;
      }
    }
    //图片消息
    private function doImage($postObj){
      $contentStr = $postObj->PicUrl;
      $resultStr = sprintf($this->textTpl, $postObj->FromUserName, $postObj->ToUserName, time(), "text", $contentStr);
      echo $resultStr;
    }
    
    // 语音消息
    private function doVoice($postObj){
      $contentStr = $postObj->MediaId;
      $resultStr = sprintf($this->textTpl, $postObj->FromUserName, $postObj->ToUserName, time(), "text", $contentStr);
      echo $resultStr;
    }
    // 地理位置消息
    private function doLocation($postObj){
      $contentStr = '您所在位置的经度:'.$postObj->Location_Y.',纬度'.$postObj->Location_X;
      $resultStr = sprintf($this->textTpl, $postObj->FromUserName, $postObj->ToUserName, time(), "text", $contentStr);
      echo $resultStr;
    }
    //封装发送请求
    public function request($url,$https=true,$method='get',$data=null){
      //1.curl初始化
      $ch = curl_init($url);
      //设置响应信息不直接输出,以文件流的返回
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      //2.设置参数
      if($https===true){
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
      }
      //支持post
      if($method==='post'){
        curl_setopt($ch,CURLOPT_POST,true);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
      }
      //发送请求
      $content = curl_exec($ch);
      //关闭
      curl_close($ch);
      return $content;
    }
    // 获取access_token
    public function getAccessToken(){
      $memcache = new Memcache();
      $memcache->connect('127.0.0.1',11211);
      $access_token = $memcache->get('access_token');
      //没有缓存
      if(!$access_token){
        //1.url
        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$this->appid.'&secret='.$this->appsecret;
        //2.请求方式
        //3.发送请求
        $content = $this->request($url);
        //4.处理返回值
        $content = json_decode($content);
        $access_token = $content->access_token;
        $memcache->set('access_token',$access_token,0,7200);
      }
      return $access_token;
    }
    // 获取二维码ticket
    public function getTicket($scene_id,$tmp=true,$expire_seconds=604800){
      
    }
}
