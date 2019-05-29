<?php

class App
{
  function __construct()
  {
    if (!empty($_GET['echostr'])) {
      echo $_GET['echostr'];
    } else {
      echo "Wechat Backend.";
    }
  }
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
}

new App;
