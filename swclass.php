<?php
class Server{
  protected static $instance = null;
  private static $config = array(
    'reactor_num'=>1,
    'worker_num'=>1,
    'max_conn'=>100,
    'backlog'=>128,
    'max_request'=>50,
    'open_cpu_affinity'=>1,
    'open_http2_protocol'=>true,
  );
  /*
  server parameters
  */
  private static $serv = null;

  private static $table;

  const SERV_PORT = 9502;

  private function __clone(){}

  private function __construct(){}

  public static function getInstance(){
    if(self::$instance == null){
      self::$instance = new Server();
    }
    return self::$instance;
  }

  public static function setConf($config = array()){
    if(!empty($config)){
      self::$config = $config;
    }
    return self::$instance;
  }

  public static function initTable(){
    self::$table = new swoole_table(1024);
    self::$table->column('id', swoole_Table::TYPE_INT, 4);
    self::$table->create();
    return self::$instance;
  }

  public static function run(){
    self::$serv = new swoole_websocket_server("0.0.0.0", 9502);

    self::$serv->set(self::$config);

    self::$serv->on('open', function($server, $frame) {
        $arr = array();
        foreach(self::$table as $n=>$i){
          $arr[$n]=$i['id'];
        }
        self::$serv->push($frame->fd, JSON_encode($arr));
    });

    self::$serv->on('message', function($server, $frame){
        $action = json_decode($frame->data)->{'action'};
        if($action === "login"){
        	$user = json_decode($frame->data)->{'user'};
          self::$table->set("{$user}", array('id'=>$frame->fd));
        }else if($action === "sendTo"){
          $to = json_decode($frame->data)->{'to'};
          if(self::$table->get($to)){
            self::$serv->push(self::$table->get($to)['id'], "update");
          }
        }else if($action === "logout"){
          $user = json_decode($frame->data)->{'user'};
          self::$table::del($user);
        }
    });

    self::$serv->on('request', function($request, $response){
      $response->end("<h1>Hello Swoole. #".rand(1000, 9999)."</h1>");
    });

    self::$serv->on('close', function($server, $frame) {
    });

    self::$serv->start();
    return self::$instance;
  }
}

header("Content-type: text/html; charset=utf-8");
ini_set('display_errors', 1);
ini_set('displat_startup_errors', 1);
error_reporting(E_ALL);

Server::getInstance()->setConf()->initTable()->run();
?>
