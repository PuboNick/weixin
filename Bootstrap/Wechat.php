<?php
namespace Bootstrap;
use Config\Config;
use Bootstrap\Http;

class Wechat
{
  function __construct()
  {
    $this->http = new Http;
    $this->config = new Config;
  }
  function save_access_token() {
    $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $this->config->appid . "&secret=" . $this->config->secret;
    $result = $this->http->do_get($url);
    if (array_key_exists('access_token', $result)) {
      $access_token = $result['access_token'];
      $file = fopen('Data\access_token.txt', 'w') or die('Unable to open file!');
      fwrite($file, $access_token);
      fclose($file);
      return $access_token;
    } else {
      return $result;
    }
  }
}
