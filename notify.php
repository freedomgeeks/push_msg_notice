<?php

require_once __DIR__ . '/vendor/autoload.php';

use Workerman\Worker;
use Workerman\Connection\TcpConnection;

$worker = new Worker("websocket://0.0.0.0:1234");

// 启动1个进程保证同信道
$worker->count = 1;

// 新增加一个属性，用来保存uid到connection的映射(uid是用户id或者客户端唯一标识)
$worker->uidConnections = array();

$worker->onWorkerStart = function() {
    echo "Worker started\n";
};

$worker->onConnect = function(TcpConnection $connection) {
    echo "Client connected\n";
};


$worker->onMessage = function(TcpConnection $connection, $data) {
    echo "Received message: $data\n";
    global $worker;
    
    $msgArr=json_decode($data,true);
    
    $recv_uid=$msgArr['to'];
    if(!isset($connection->uid))
    { 
        
        $connection->uid=$msgArr['uid'];
        /* 保存uid到connection的映射，这样可以方便的通过uid查找connection，
         * 实现针对特定uid推送数据
         */
        $worker->uidConnections[$connection->uid] = $connection;
        
          $connection->send('login success, your uid is ' . $connection->uid);
       }
 
 
//   $connection->send("Received message: q \n");
   
   // 其它逻辑，针对某个uid发送 或者 全局广播
   // 假设消息格式为 uid:message 时是对 uid 发送 message
   // uid 为 all 时是全局广播
   
      
  print_r($msgArr);
   //内容
   $message=json_encode($msgArr['message']);
  
   

   // 全局广播
   if($recv_uid == 'all')
   {
       broadcast($message);
   }
   // 给特定uid发送
   else
   {
       sendMessageByUid($recv_uid, $message);
   }
 
 
};

$worker->onClose = function(TcpConnection $connection) {
    echo "Client closed\n";
       global $worker;
       if(isset($connection->uid))
        {
            // 连接断开时删除映射
            unset($worker->uidConnections[$connection->uid]);
        }
  
};

// 向所有验证的用户推送数据
function broadcast($message)
{
    global $worker;
    foreach($worker->uidConnections as $connection)
    {
        $connection->send($message);
    }
}

// 针对uid推送数据
function sendMessageByUid($uid, $message)
{
    global $worker;
    if(isset($worker->uidConnections[$uid]))
    {
        $connection = $worker->uidConnections[$uid];
        $connection->send($message);
    }
}


Worker::runAll();