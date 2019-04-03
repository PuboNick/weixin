<?php

class App
{
  var $base_uri = 'https://api.weixin.qq.com/cgi-bin';
  var $appid = 'wxc14068a536ab918c';
  var $secret = 'bb7f50f320a97b5042f228388199875b';
  var $templates = [
    'temp' => 'CJLF6KomEcU_-EgW4uZGtqjHHoV6M-AE-OIhzAI28po',
    'status' => 'HFblz-UJEhmrbTudTbbG1SauNZgZxoSZ0V3JCGw5Was',
    'warning' => 'GbJ_2b_6pfDc4C7XjjIi9E2EK_AXapRkJblEW3TVzcY',
    'gprs' => 'SjPd8w33ojvOnrKcXXmliCn5jDF1Hi47vATxzck4UMg',
    'equ' => 'A2uD_rCiiB6wd1Hde1zHWsRzHHY8tjgiNCwJ423e7es'
  ];
  var $access_file = 'access_token.json';
  function __construct()
  {
    if (!empty($_GET['echostr'])) {
      echo $_GET['echostr'];
    } elseif (!empty($_GET['action']) && !empty($_GET['timestamp']) && !empty($_GET['secret']) && $this->check_values()) {
      $this->handle_web();
    } elseif (!empty(file_get_contents('php://input'))) {
      $this->handle_app();
    } else {
      echo "Wechat Backend.";
    }
  }
  function check_values() {
    date_default_timezone_set("Asia/Shanghai");
    $str = date('YmdHi',time());
    return $_GET['secret'] === md5($this->appid . $this->secret . $_GET['timestamp']) && $str === substr($_GET['timestamp'], 0, 12);
  }
  function handle_web()
  {
    $action = $_GET['action'];
    if ($action === 'login' && !empty($_GET['str'])) {
      $this->scan_login();
    } elseif ($action === 'send_wendu_message' && $this->check_params_5()) {
      $this->set_message_5($this->templates['temp']);
    } elseif ($action === 'send_warning_message' && $this->check_params_3()) {
      $this->set_message_3($this->templates['warning']);
    } elseif ($action === 'send_status_message' && $this->check_params_5()) {
      $this->set_message_5($this->templates['status']);
    } elseif ($action === 'send_gprs_message' && $this->check_params_3()) {
      $this->set_message_3($this->templates['gprs']);
    } elseif ($action === 'send_equ_message' && $this->check_params_3()) {
      $this->set_message_3($this->templates['equ']);
    } elseif ($action === 'send_url_message' && $this->check_url_param()) {
      $this->send_url_message();
    } elseif ($action === 'create_menu') {
      $this->set_menu();
    } elseif ($action === 'create_tag' && !empty($_GET['tag_name'])) {
      $this->create_tag();
    } elseif ($action === 'delete_tag'  && !empty($_GET['tag_id'])) {
      $this->delete_tag();
    } elseif ($action === 'get_tags_list') {
      $this->get_tags_list();
    } elseif ($action === 'add_user_tag' && !empty($_GET['openid']) && !empty($_GET['tag_id'])) {
      $this->add_user_tag();
    } elseif ($action === 'delete_user_tag' && !empty($_GET['openid']) && !empty($_GET['tag_id'])) {
      $this->delete_user_tag();
    } elseif ($action === 'conditional_menu') {
      $this->conditional_menu();
    } elseif ($action === 'delete_menu') {
      $this->delete_menu();
    } elseif ($action === 'get_user_tags' && !empty($_GET['openid'])) {
      $this->get_user_tags();
    } elseif ($action === 'get_user_information' && !empty($_GET['openid'])) {
      $this->get_user_information();
    } else {
      print_r(json_encode(['code' => '401', 'errmsg' => '参数错误'], JSON_UNESCAPED_UNICODE));
    }
  }
  function handle_app()
  {
    $message_content = file_get_contents('php://input');
    $message = simplexml_load_string($message_content);
    if ($message->MsgType == 'event' && $message->Event == 'SCAN') {
      $this->handle_scan($message);
    } elseif ($message->MsgType == 'text' && $message->Content == 'openid') {
      $this->send_back_openid($message);
    }
  }
  /* 系统模块 */
  function do_get($url)
  {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $result = json_decode(curl_exec($ch), true);
    curl_close($ch);
    return $result;
  }
  function do_post($url, $params)
  {
    $params = json_encode($params, JSON_UNESCAPED_UNICODE);
    $headers = array(
      "Content-Type:application/json;charset=utf-8",
      "Accept:application/json;charset=utf-8"
    );
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $result = json_decode(curl_exec($ch), true);
    curl_close($ch);
    return $result;
  }
  function save_data($name, $data)
  {
    $file = fopen($name, 'w') or die('Unable to open file!');
    fwrite($file, json_encode($data, JSON_UNESCAPED_UNICODE));
    fclose($file);
  }
  function get_data($name)
  {
    $file = fopen($name, 'r') or die('Unable to open file!');
    $size = filesize($name);
    $data = json_decode(fread($file, $size), true);
    fclose($file);
    return $data;
  }
  function save_access_token()
  {
    $url = $this->base_uri . "/token?grant_type=client_credential&appid=".$this->appid."&secret=".$this->secret;
    $access_token = $this->do_get($url)['access_token'];
    date_default_timezone_set("Asia/Shanghai");
    $end_time = date('Y-m-d H:i:s',strtotime("+19 minute"));
    $data = ['access_token' => $access_token, 'end_time' => $end_time];
    $this->save_data($this->access_file, $data);
    return $access_token;
  }
  function get_access_token()
  {
    $data = $this->get_data($this->access_file);
    date_default_timezone_set("Asia/Shanghai");
    if ($data['end_time'] > date('Y-m-d H:i:s',time())) {
      return $data['access_token'];
    } else {
      return $this->save_access_token();
    }
  }
  function set_message_5($template_id)
  {
    $first = array('value' => $_GET['title']);
    $keyword1 = array('value' => $_GET['keyword1']);
    $keyword2 = array('value' => $_GET['keyword2']);
    $keyword3 = array('value' => $_GET['keyword3']);
    $keyword4 = array('value' => $_GET['keyword4']);
    $keyword5 = array('value' => $_GET['keyword5']);
    $remark = array('value' => $_GET['remark']);
    $data = array('first' => $first, 'keyword1' => $keyword1, 'keyword2' => $keyword2, 'keyword3' => $keyword3, 'keyword4' => $keyword4, 'keyword5' => $keyword5, 'remark' => $remark);
    $template_message = array('touser' => $_GET['openid'], 'template_id' => $template_id,'data' => $data);
    $this->send_template_message($template_message);
  }
  function set_message_3($template_id)
  {
    $first = array('value' => $_GET['title']);
    $keyword1 = array('value' => $_GET['keyword1']);
    $keyword2 = array('value' => $_GET['keyword2']);
    $keyword3 = array('value' => $_GET['keyword3']);
    $remark = array('value' => $_GET['remark']);
    $data = array('first' => $first, 'keyword1' => $keyword1, 'keyword2' => $keyword2, 'keyword3' => $keyword3, 'remark' => $remark);
    $template_message = array('touser' => $_GET['openid'], 'template_id' => $template_id,'data' => $data);
    $this->send_template_message($template_message);
  }
  function send_template_message($template_message)
  {
    $access_token = $this->get_access_token();
    $url = $this->base_uri . "/message/template/send?access_token=".$access_token;
    $result = $this->do_post($url, $template_message);
    print_r(json_encode($result, JSON_UNESCAPED_UNICODE));
  }
  function get_code($scene)
  {
    $access_token = $this->get_access_token();
    $url = $this->base_uri . "/qrcode/create?access_token=".$access_token;
    $action_info = array('scene'=>$scene);
    $params = array('expire_seconds' => 604800, 'action_name' => 'QR_STR_SCENE', 'action_info' => $action_info);
    $result = $this->do_post($url, $params);
    if (array_key_exists('ticket', $result)) {
      $ticket = $result['ticket'];
      $path = "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=" . $ticket;
      echo "$path";
    } else {
      print_r(json_encode($result, JSON_UNESCAPED_UNICODE));
    }
  }
  function set_menu()
  {
    $access_token = $this->get_access_token();
    $url = $this->base_uri . '/menu/create?access_token=' . $access_token;
    $redirect_url = urlencode('http://www.woleit.com/login/wechat');
    $btn_bind_url1 = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$this->appid.'&redirect_uri='.$redirect_url.'&response_type=code&scope=snsapi_base&state=#wechat_redirect';
    $btn_bind_url2 = 'http://www.woleit.com/wole';
    $button1 = array('type' => 'view','name' => '我的设备', 'url' => $btn_bind_url1);
    $button2 = array('type' => 'view','name' => '沃勒官网', 'url' => $btn_bind_url2);
    $button = array($button1, $button2);
    $menu_button = array('button' => $button);
    $result = $this->do_post($url, $menu_button);
    print_r(json_encode($result, JSON_UNESCAPED_UNICODE));
  }
  function create_tag()
  {
    $access_token = $this->get_access_token();
    $url = $this->base_uri . '/tags/create?access_token=' . $access_token;
    $tag = array('name' => $_GET['tag_name']);
    $params = array('tag' => $tag);
    $result = $this->do_post($url, $params);
    print_r(json_encode($result, JSON_UNESCAPED_UNICODE));
  }
  function delete_tag()
  {
    $access_token = $this->get_access_token();
    $url = $this->base_uri . '/tags/delete?access_token=' . $access_token;
    $tag = array('id' => $_GET['tag_id']);
    $params = array('tag' => $tag);
    $result = $this->do_post($url, $params);
    print_r(json_encode($result, JSON_UNESCAPED_UNICODE));
  }
  function get_tags_list()
  {
    $access_token = $this->get_access_token();
    $url = $this->base_uri . '/tags/get?access_token=' . $access_token;
    $result = $this->do_get($url);
    print_r(json_encode($result, JSON_UNESCAPED_UNICODE));
  }
  function add_user_tag()
  {
    $access_token = $this->get_access_token();
    $url = $this->base_uri . '/tags/members/batchtagging?access_token=' . $access_token;
    $openid_list = array($_GET['openid']);
    $params = array('openid_list' => $openid_list, 'tagid' => $_GET['tag_id']);
    $result = $this->do_post($url, $params);
    print_r(json_encode($result, JSON_UNESCAPED_UNICODE));
  }
  function delete_user_tag()
  {
    $access_token = $this->get_access_token();
    $url = $this->base_uri . '/tags/members/batchuntagging?access_token=' . $access_token;
    $openid_list = array($_GET['openid']);
    $params = array('openid_list' => $openid_list, 'tagid' => $_GET['tag_id']);
    $result = $this->do_post($url, $params);
    print_r(json_encode($result, JSON_UNESCAPED_UNICODE));
  }
  function conditional_menu()
  {
    $access_token = $this->get_access_token();
    $url = $this->base_uri . '/menu/addconditional?access_token=' . $access_token;
    $redirect_url2 = urlencode('http://www.woleit.com/wechat/login');
    $btn_bind_url3 = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$this->appid.'&redirect_uri='.$redirect_url2.'&response_type=code&scope=snsapi_base&state=#wechat_redirect';
    $button1 = array('name' => '电缆沟监控', 'type' => 'view', 'url' => $btn_bind_url3);
    $button = array($button1);
    $matchrule = array('tag_id' => '100');
    $params = array('button' => $button, 'matchrule' => $matchrule);
    $result = $this->do_post($url, $params);
    print_r(json_encode($result, JSON_UNESCAPED_UNICODE));
  }
  function delete_menu()
  {
    $access_token = $this->get_access_token();
    $url = $this->base_uri . '/menu/delete?access_token=' . $access_token;
    $result = $this->do_get($url);
    print_r(json_encode($result, JSON_UNESCAPED_UNICODE));
  }
  function get_user_tags(){
    $access_token = $this->get_access_token();
    $tag_url = $this->base_uri . '/tags/getidlist?access_token=' . $access_token;
    $params = array('openid' => $_GET['openid']);
    $tag_result = $this->do_post($tag_url, $params);
    if (array_key_exists('tagid_list', $tag_result)) {
      $tags_id = $tag_result['tagid_list'];
      print_r(json_encode($tags_id, JSON_UNESCAPED_UNICODE));
    }
  }
  function get_user_information()
  {
    $access_token = $this->get_access_token();
    $users = array();
    $user = array('openid' => $_GET['openid'], 'lang' => 'zh_CN');
    array_push($users, $user);
    $user_list_url = $this->base_uri . '/user/info/batchget?access_token=' . $access_token;
    $params = array('user_list' => $users);
    $user_list = $this->do_post($user_list_url, $params)['user_info_list'];
    print_r(json_encode($user_list, JSON_UNESCAPED_UNICODE));
  }
  /* web模块 */
  function scan_login()
  {
    $str = $_GET['str'];
    $scene = array('scene_str'=>'{"str":"'.$str.'","action":"login"}');
    $this->get_code($scene);
  }
  function check_params_3()
  {
    $a = !empty($_GET['keyword1']) && !empty($_GET['keyword2']);
    $b = !empty($_GET['keyword3']) && !empty($_GET['openid']);
    $c = !empty($_GET['title']);
    return $a && $b && $c;
  }
  function check_params_5()
  {
    $a = !empty($_GET['keyword1']) && !empty($_GET['keyword2']);
    $b = !empty($_GET['keyword3']) && !empty($_GET['openid']);
    $c = !empty($_GET['keyword4']) && !empty($_GET['keyword5']) && !empty($_GET['title']);
    return $a && $b && $c;
  }
  function check_url_param()
  {
    return !empty($_GET['users']) && !empty($_GET['content']) && !empty($_GET['msgid']);
  }
  function send_url_message()
  {
    $access_token = $this->get_access_token();
    $uri = $this->base_uri . '/message/mass/send?access_token=' . $access_token;
    $message = ['touser' => json_decode($_GET['users']), 'msgtype' => 'text', 'text' => ['content' => $_GET['content']], 'clientmsgid' => $_GET['msgid']];
    $result = $this->do_post($uri, $message);
    print_r(json_encode($result, JSON_UNESCAPED_UNICODE));
  }
  /* APP模块 */
  function handle_scan($message)
  {
    $data = json_decode($message->EventKey, true);
    if ($data['action'] === 'login') {
      $this->res_openid($message, $data['str']);
    }
  }
  function send_back_openid($message)
  {
    echo "
    <xml>
    <ToUserName><![CDATA[{$message->FromUserName}]]></ToUserName>
    <FromUserName><![CDATA[$message->ToUserName]]></FromUserName>
    <CreateTime>1537183193</CreateTime>
    <MsgType><![CDATA[text]]></MsgType>
    <Content><![CDATA[{$message->FromUserName}]]></Content>
    <MsgId>6602151542321792697</MsgId>
    </xml>
    ";
  }
  function res_openid($message, $str)
  {
    $result = $this->do_get('http://123.56.246.56:8099/api/weixin/scan?openid=' . $message->FromUserName . '&str=' . $str);
    echo "<xml>
    <ToUserName><![CDATA[{$message->FromUserName}]]></ToUserName>
    <FromUserName><![CDATA[$message->ToUserName]]></FromUserName>
    <CreateTime>1537183193</CreateTime>
    <MsgType><![CDATA[text]]></MsgType>
    <Content><![CDATA[{$result}]]></Content>
    <MsgId>6602151542321792697</MsgId>
    </xml>";
  }
}

new App;
