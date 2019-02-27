<?php
namespace Bootstrap;

class Route
{

  function __construct()
  {
    $this->route = [
      'test' => 'Test'
    ];
  }
  function handle($value) {
    if (array_key_exists($value, $this->route)) {
      include 'App/Http/' . $this->route[$value] . '.php';
    } else {
      echo "weixin backend.";
    }
  }
}
