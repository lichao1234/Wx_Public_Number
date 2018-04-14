<?php
namespace app\weixin\controller;
use app\weixin\model\Weixin as WeixinModel;

use think\Controller;
class Index extends Common
{
	// 入口验证
    public function index()
    {
		$nonce = $_GET['nonce'];
		$token = 'omJNpZEhZeHj1ZxFECKkP48B5VFbk1HP';
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
	
	// 获取微信返回的信息
	public function reponseMsg(){
		//1.获取到微信推送过来post数据（xml格式）
		$xml = file_get_contents('php://input');
		//2.处理消息类型，并设置回复类型和内容
		$postObj = simplexml_load_string($xml);
		//判断该数据包是否是订阅的事件推送
		$weixin = new WeixinModel();
        $weixin->reponseMsg($postObj);
	}
	
	// 自定义菜单
	public function definedItem(){
		$access_token = $this->getWxAccessToken();
		echo $access_token;
		echo '<hr>';
		$url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$access_token.'';
		$postArr = array(
			'button' => array(
				array('type'=>'click','name'=>urlencode('菜单1'),'key'=>'item1'),
				array('name'=>urlencode('菜单2'),'sub_button'=>array(array('type'=>'view','name'=>urlencode('搜索'),'url'=>'http://www.soso.com/'),array('type'=>'click','name'=>urlencode('赞一下我们'),'key'=>'item2'),array('type'=>'pic_photo_or_album','name'=>urlencode('拍一张照片吧'),'key'=>'1'))),
			), 
		);
		$postJson = urldecode(json_encode($postArr));
		echo $postJson;
		$res = $this->http_curl($url,'post','json',$postJson);
		echo '<hr>';
		var_dump($res);
	}
	
	// curl请求
	public function http_curl($url,$type='get',$res='json',$arr=''){
		// 获取imooc
		// 1.初始化curl
		$ch = curl_init();
		// 2.设置url的参数
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		if($type=='post'){
			curl_setopt($ch,CURLOPT_POST,1);
			curl_setopt($ch,CURLOPT_POSTFIELDS,$arr);
		}
		// 3.采集
		$output = curl_exec($ch);
		if($res=='json'){
			return json_decode($output,true);
		}
		// 4.关闭
		curl_close($ch);
		var_dump($output);
	}
	
	// 获取access_token
	public function getWxAccessToken(){
		// 将access_token 存在session/cookie中
		if(isset($_SESSION['access_token']) && $_SESSION['exprice_time']>time()){
			// 如果access_token在session且并未过期
			return $_SESSION['access_token'];
		}else{
			// 如果access_token不在session中或过期
			$appid = 'wxcc5fd17ef877309e';
			$appsecret = '2cf114dd291fcb4b11851917a1298db8';
			$url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$appid.'&secret='.$appsecret.'';
			$res = $this->http_curl($url,'get','json');
			$access_token = $res['access_token'];
			// 将重新获取到的access_token存到session
			$_SESSION['access_token'] = $access_token;
			$_SESSION['exprice_time'] = time()+7000;
			return $access_token;
		}	
	}

	// 获取微信服务器ip
	public function getWxServerIp(){
		$access_token = $this->getWxAccessToken();
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
	
	// 群发消息
	public function sendAll(){
		// 1.获取全局access_token
		$access_token = $this->getWxAccessToken();
		$url = 'https://api.weixin.qq.com/cgi-bin/message/mass/preview?access_token='.$access_token.'';
		// 2.组装群发接口数据array
			// 单文本
			// $array = array('touser'=>'oNl3owbMCSvkJJ9FeSqi5wWBLeOk','text'=>array('content'=>'very happy'),'msgtype'=>'text');
			// 单图文
			$media_id = $this->uploadnews();
			$array = array('touser'=>'oNl3owbMCSvkJJ9FeSqi5wWBLeOk','image'=>array('media_id'=>$media_id),'msgtype'=>'image');
		// 3.将数组->json
		$postJson = json_encode($array);
		// 4.调用curl
		$res = $this->http_curl($url,'post','json',$postJson);
		var_dump($res);
	}
	
	
	// 上传图文素材
	public function uploadnews(){
		if(isset($_SESSION['media_id'])){
			return $_SESSION['media_id'];
		}else{
			$access_token = $this->getWxAccessToken();
			// $wx_url = "https://api.weixin.qq.com/cgi-bin/material/add_material?access_token={$access_token}&type=image";
			$wx_url = "https://api.weixin.qq.com/cgi-bin/media/upload?access_token={$access_token}&type=image";
			$url = '/uploads/timgasdfa.jpg';
			$real_path = "{$_SERVER['DOCUMENT_ROOT']}{$url}";
			$result = $this->http_post($wx_url, $real_path);
			$arr = json_decode($result,true);
			$media_id = $arr['media_id'];   //4uaGn9eya3b_LaqHp4N0bsd99dBsRogQuB-q5JYnCoQ
			$_SESSION['media_id'] = $media_id;
			return $media_id;
		}
	}
	
	// 上传素材专用http_post
	public function http_post($url ='' , $fileurl = '' ){
		$curl = curl_init();
		if(class_exists('\CURLFile')){
			curl_setopt ( $curl, CURLOPT_SAFE_UPLOAD, true); 
			$data = array('media' => new \CURLFile($fileurl));
		}else{
			if (defined ( 'CURLOPT_SAFE_UPLOAD' )) {  
				curl_setopt ( $curl, CURLOPT_SAFE_UPLOAD, false );  
			}  
			$data = array('media' => '@' . realpath($fileurl));
		}
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); 
		$output = curl_exec($curl);
		curl_close($curl);
		return $output;
	}
	
	// 模板消息
	public function sendTemplateMsg(){
		// 1.获取access_token
		$access_token = $this->getWxAccessToken();
		// 2.组装数组
		$postArr = array(
			'touser' => 'oNl3owbMCSvkJJ9FeSqi5wWBLeOk',
			'template_id' => 'Qd7nR2_b8rTLdaVe3w6KYzmv9uCQQsDpoC3q9Wjs5_s',
			'url' => 'http://www.baidu.com',
			'data' => array(
				'name' => array('value'=>'hello','color'=>'#173177'),
				'money' => array('value'=>'100','color'=>'#173177'),
				'date' => array('value'=>date('Y-m-d H:i:s'),'color'=>'#173177'),
			),
		);
		$url = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token='.$access_token.'';
		// 3.将数组转成json
		$postJson = json_encode($postArr);
		// 4.调用curl函数
		$res = $this->http_curl($url,'post','json',$postJson);
		var_dump($res);
	}
	
	// 网页授权获取用户openid
	public function getBaseInfo(){
		// 1.获取到code
		$appid = 'wxcc5fd17ef877309e';
		$redirect_uri = urlencode('http://www.l73c67.wang/weixin/index/getUserOpenId');
		$url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$appid.'&redirect_uri='.$redirect_uri.'&response_type=code&scope=snsapi_base&state=123#wechat_redirect';
		ob_start();
		ob_end_flush();
		header('Location:'.$url);
		exit;
	}
	
	// 基础授权
	public function getUserOpenId(){
		// 获取变量
		$appid = 'wxcc5fd17ef877309e';
		$appsecret = '2cf114dd291fcb4b11851917a1298db8';
		if(isset($_GET['code'])){
			$code = $_GET['code'];
        }
		// 2.获取到网页授权的access_token
		$url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$appid.'&secret='.$appsecret.'&code='.$code.'&grant_type=authorization_code';
		// 3.拉取用户的openid
		$res = $this->http_curl($url,'get');
		var_dump($res);
	}
	
	// 详细授权、
	public function getUserDetail(){
		// 1.获取到code
		$appid = 'wxcc5fd17ef877309e';
		$redirect_uri = urlencode('http://www.l73c67.wang/weixin/index/getUserInfo');
		$url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$appid.'&redirect_uri='.$redirect_uri.'&response_type=code&scope=snsapi_userinfo&state=123#wechat_redirect';
		ob_start();
		ob_end_flush();
		header('Location:'.$url);
		exit;
	}
	
	// 获取用户详细信息
	public function getUserInfo(){
		// 获取变量
		$appid = 'wxcc5fd17ef877309e';
		$appsecret = '2cf114dd291fcb4b11851917a1298db8';
		if(isset($_GET['code'])){
			$code = $_GET['code'];
        }
		// 2.获取到网页授权的access_token
		$url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$appid.'&secret='.$appsecret.'&code='.$code.'&grant_type=authorization_code';
		// 3.拉取用户的openid
		$res = $this->http_curl($url,'get');
		$access_token = $res['access_token'];
		$openid = $res['openid'];
		// 拉取用户的详细信息
		$url2 = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$access_token.'&openid='.$openid.'&lang=zh_CN';
		$userinfo = $this->http_curl($url2,'get');
		var_dump($userinfo);
	}
	
	// 分享朋友圈
	public function shareWx(){
		$time = time();
		$noncestr = $this->getRandCode(6);
		// 获取jsapi_ticket
		$jsapi_ticket = $this->getJsapiTicket();
		exit;
		$signature = '';
		$this->assign('time',$time);
		$this->assign('nonceStr',$nonceStr);
		$this->assign('signature',$signature);
		return view();
	}
	
	// 获取随机码
	public function getRandCode($length){
		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'; 
		$noncestr = '';
		for ( $i = 0; $i < $length; $i++ ){
			// 这里提供两种字符获取方式 
			// 第一种是使用 substr 截取$chars中的任意一位字符； 
			// 第二种是取字符数组 $chars 的任意元素 
			// $password .= substr($chars, mt_rand(0, strlen($chars) – 1), 1); 
			$noncestr .= $chars[ mt_rand(0, strlen($chars) - 1) ]; 
		} 
		return $noncestr; 
	}
	
	// 获取jsapi_ticket
	public function getJsapiTicket(){
		if(isset($_SESSION['jsapi_ticket']) && $_SESSION['exprice_jsapi_time']>time()){
			$jsapi_ticket = $_SESSION['jsapi_ticket'];
			return $jsapi_ticket;
		}else{
			$access_token = $this->getWxAccessToken();
			$url = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token='.$access_token.'&type=jsapi';
			$res = $this->http_curl($url,'get');
			if(isset($res)){
				if($res['errmsg'] == 'ok'){
					$jsapi_ticket = $res['ticket'];
					$_SESSION['jsapi_ticket'] = $jsapi_ticket;
					$_SESSION['exprice_jsapi_time'] = time()+7000;
					return $jsapi_ticket;
				}else{
					echo 'jsapi_ticket获取失败1';
					exit;
				}
			}else{
				echo 'jsapi_ticket获取失败2';
				exit;
			}
		}
	}
}
