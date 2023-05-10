<?php
  
use Workerman\Worker;
use Workerman\Connection\AsyncTcpConnection;
require_once __DIR__ . '/vendor/autoload.php';

$worker = new Worker();

$worker->onWorkerStart = function($worker){
  
    
    $con = new AsyncTcpConnection('ws://127.0.0.1:1234');
    
    $con->onConnect = function(AsyncTcpConnection $con) {
        $con->send('hello');
    };
    
    $con->onMessage = function(AsyncTcpConnection $con, $data) {
       

 //         while (1){
            
             $chan['uid']='server';
             $chan['to']=1;
             $chan['message'] = 'test message';
             
             $channel=json_encode($chan,JSON_UNESCAPED_UNICODE);
             echo $channel,"\n";
             $con->send($channel);
     
//           }
         
       
    };
    
    $con->connect();
};

Worker::runAll();
 

  