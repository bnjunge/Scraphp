<?php

/* Inclue required files by yourself */

// Get Scraphp instant
$scraphp = new \Scraphp\Scraphp();

// 1: command line. 
// 0: output real-time reponses to browser 
$scraphp->set_cli(0);

// 1: To save debug messages to daily log files. 
// 0: off
$scraphp->set_log(1);

// Use PDO to connect MySQL database

$servername = "localhost";
$username = "test";
$password = "1234";
$db_name = 'scraphp_test';

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$db_name", $username, $password);
    // set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $scraphp->debug_message('notice', "DB Connected for RotateProxy successfully.";
    $is_connection = true;
} catch(PDOException $e){
    $scraphp->debug_message('error', "RotateProxy DB Connection failed: " . $e->getMessage());
    $is_connection = false;
}

if ($is_connection) {

    // RotateProxy with PDO instant injection
    $rotateproxy = new \Scraphp\Modules\RotateProxy($pdo);

    /**
     * Note that the content format is ip:port:username:password per line.
     * i.g:
     * 195.25.17.111:8080:terrylin:12345678
     * 195.25.17.111:8080 (without username and password) 
     */
  
    $proxy_file_path = SCRAPHP_DIR . '/script_data/proxy.txt';
    
    // return proxy ips array  
    $import_proxies = $rotateproxy->import_ips($proxy_file_path);
    
    foreach ($import_proxies as $proxy) {
        $scraphp->debug_message('notice', "$proxy is imported.\n");
    }
}
