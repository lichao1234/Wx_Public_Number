<?php
namespace app\weixin\controller;
use app\weixin\model\Weixin as WeixinModel;
use think\Controller;
class Index extends Common
{
    public function index()
    {
		$nonce     = $_GET['nonce'];
		$token     = 'omJNpZEhZeHj1ZxFECKkP48B5VFbk1HP';
		$timestamp = $_GET['timestamp'];
		if(isset($_GET['echostr'])){
            $echostr   = $_GET['echostr'];
        }
		$signature = $_GET['signature'];
		//形成数组，然后按字典序排序
		$array = array();
		$array = array($nonce, $timestamp, $token);
		sort($array);
		//拼接成字符串,sha1加密 ，然后与signature进行校验
		$str = sha1( implode( $array ) );
		if( $str  == $signature && isset($_GET['echostr']) ){
			//第一次接入weixin api接口的时候
			echo  $echostr;
			exit;
		}else{
			$this->reponseMsg();
		}
    }
	public function reponseMsg(){
		//1.获取到微信推送过来post数据（xml格式）
		$xml = file_get_contents('php://input');
		//2.处理消息类型，并设置回复类型和内容
		$postObj = simplexml_load_string( $xml );
		//判断该数据包是否是订阅的事件推送
		$weixin = new WeixinModel();
        $weixin->reponseMsg($postObj);
	}
	
	public function http_curl(){
		// 获取imooc
		// 1.初始化curl
		$ch = curl_init();
		$url = 'https://www.baidu.com';
		// 2.设置url的参数
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		// 3.采集
		$output = curl_exec($ch);
		// 4.关闭
		curl_close($ch);
		var_dump($output);
	}
	
	public function getWxAccessToken(){
		$appid = 'wx6d24a088f8fff603';
		$appsecret = '65f3aadc8bccc63f9f26b2dd8449904b';
		$url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$appid.'&secret='.$appsecret.'';
		// 2.初始化curl
		$ch = curl_init();
		// 3.设置参数
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		// 4.调用接口
		$output = curl_exec($ch);
		$errno = curl_errno($ch);
		if(isset($errno)){
			var_dump(curl_error($ch));
		}
		curl_close($ch);
		$arr = json_decode($output,true);
		var_dump($arr);
	}
	
	public function getWxServerIp(){
		$accessToken = '6_PPQGiDpZ6Hq00geRl_XoFnTvkIaILLLy3r2Yyv08pmCzVxc5npNGLI1Xm7YQR7NIZpxuIn2P-jXQQwJNnwyr2WX5IT_oUYLqzKZxdkIxSO2cixFA557lzZLLYaj3yQarpokxSKKQcc0LNTE8JXHiAGAZNX';
		$url = 'https://api.weixin.qq.com/cgi-bin/getcallbackip?access_token='.$accessToken;
		// 2.初始化curl
		$ch = curl_init();
		// 3.设置参数
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		// 4.调用接口
		$output = curl_exec($ch);
		$errno = curl_errno($ch);
		if(isset($errno)){
			var_dump(curl_error($ch));
		}
		curl_close($ch);
		$arr = json_decode($output,true);
		var_dump($arr);
	}
}
