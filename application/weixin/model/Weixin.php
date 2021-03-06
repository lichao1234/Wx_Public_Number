<?php
namespace app\weixin\model;
use think\Model;
class Weixin extends Model
{
    public function reponseMsg($postObj){
		if( strtolower( $postObj->MsgType) == 'event'){
			//如果是关注 subscribe 事件
			if( strtolower($postObj->Event == 'subscribe') ){
				//回复用户消息(纯文本格式)	
				$content  = '欢迎关注我们的微信公众账号'.$postObj->FromUserName.'-'.$postObj->ToUserName;
				$this->reponseText($postObj,$content);
			}
			// 取消关注事件
			if( strtolower($postObj->Event == 'unsubscribe') ){
				// 此处写你自己的业务逻辑
			}
			// 单击事件
			if(strtolower($postObj->Event == 'CLICK')){
				if(strtolower($postObj->EventKey == 'item1')){
					$content  = '这是item1';
					$this->reponseText($postObj,$content);
				}
				if(strtolower($postObj->EventKey == 'item2')){
					$content  = '这是item2';
					$this->reponseText($postObj,$content);
				}
			}
			// 跳转事件
			if(strtolower($postObj->Event == 'VIEW')){
				$content  = '跳转链接是'.$postObj->EventKey;
				$this->reponseText($postObj,$content);
			}
		}
		if(strtolower($postObj->MsgType) == 'text'){
			$toUser   = $postObj->FromUserName;
			$fromUser = $postObj->ToUserName;
			$time     = time();
			$msgType  =  'text';
			switch(trim($postObj->Content)){
				case '你好':
					$content = '小傻子';
				break;
				case '早上好':
					$content = '小呆子';
				break;
				case '晚上好':
					$content = '小混蛋';
				break;
				case '我爱你':
					$content = '呵呵';
				break;
				case '英文':
					$content = '世界是中国的';
				break;
				case '链接':
					$content = '<a href="http://www.l73c67.wang/">博客</a>';
				break;
				case '天气':
					$url = 'https://www.sojson.com/open/api/weather/json.shtml?city=北京';
					$ch = curl_init();
					curl_setopt($ch,CURLOPT_URL,$url);
					curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
					$output = curl_exec($ch);
					curl_close($ch);
					$arr = json_decode($output,true);
					$high_temperature = $arr['data']['forecast'][0]['high'];
					$low_temperature = $arr['data']['forecast'][0]['low'];
					$fx = $arr['data']['forecast'][0]['fx'];
					$fl = $arr['data']['forecast'][0]['fl'];
					$type = $arr['data']['forecast'][0]['type'];
					$notice = $arr['data']['forecast'][0]['notice'];
					$content = '今日天气'.$high_temperature.','.$low_temperature.','.$type.','.$fx.$fl.','.$notice.'';
				break;
				// 用户发送图文关键字的时候,回复一个单图文	
				case '图文':
					$msgType  =  'news';
					$arr = array(
						array(
							'title'=>'imooc',
							'description'=>"imooc is very cool",
							'picUrl'=>'http://www.imooc.com/static/img/common/logo.png',
							'url'=>'http://www.imooc.com',
						),
						array(
							'title'=>'hao123',
							'description'=>"hao123 is very cool",
							'picUrl'=>'https://www.baidu.com/img/bdlogo.png',
							'url'=>'http://www.hao123.com',
						),
						array(
							'title'=>'qq',
							'description'=>"qq is very cool",
							'picUrl'=>'http://www.imooc.com/static/img/common/logo.png',
							'url'=>'http://www.qq.com',
						),
					);
					$template = "<xml>
								<ToUserName><![CDATA[%s]]></ToUserName>
								<FromUserName><![CDATA[%s]]></FromUserName>
								<CreateTime>%s</CreateTime>
								<MsgType><![CDATA[%s]]></MsgType>
								<ArticleCount>".count($arr)."</ArticleCount>
								<Articles>";
					foreach($arr as $k=>$v){
						$template .="<item>
									<Title><![CDATA[".$v['title']."]]></Title> 
									<Description><![CDATA[".$v['description']."]]></Description>
									<PicUrl><![CDATA[".$v['picUrl']."]]></PicUrl>
									<Url><![CDATA[".$v['url']."]]></Url>
									</item>";
					}
					
					$template .="</Articles>
								</xml> ";
					$info = sprintf($template, $toUser, $fromUser, $time, $msgType); 
				break;
				default:
					$content = '听不懂你在说什么';
				break;
			}	
			if(!isset($template)){
				$template = 
				"<xml>
					<ToUserName><![CDATA[%s]]></ToUserName>
					<FromUserName><![CDATA[%s]]></FromUserName>
					<CreateTime>%s</CreateTime>
					<MsgType><![CDATA[%s]]></MsgType>
					<Content><![CDATA[%s]]></Content>
				</xml>";
				$info = sprintf($template, $toUser, $fromUser, $time, $msgType, $content);
			}
			echo $info;
		}	
	}
	public function reponseText($postObj,$content){
		$toUser   = $postObj->FromUserName;
		$fromUser = $postObj->ToUserName;
		$time     = time();
		$msgType  =  'text';
		$template = "<xml>
					<ToUserName><![CDATA[%s]]></ToUserName>
					<FromUserName><![CDATA[%s]]></FromUserName>
					<CreateTime>%s</CreateTime>
					<MsgType><![CDATA[%s]]></MsgType>
					<Content><![CDATA[%s]]></Content>
					</xml>";
		$info     = sprintf($template, $toUser, $fromUser, $time, $msgType, $content);
		echo $info;
	}
}
