<?php

// Creator: S Matthews
// Date:  14/09/2017
// Parameters (3): $argv[1] (database name),  $argv[2] (username), $argv[3] (password)
// DESC: Communicates with the master database to carry out some checks and to update table "databases", as follows ..

// 1. checks that the slave db exists
// 2. check master.databases table for slave name - has it been imported already ?
// 3. Add a new record to the databases table
// 4. Capture "db_ref" for the newly imported database

// USAGE: NOT STAND ALONE - called within the bash script "merge_database.sh"

// GUID generator function -- http://guid.us/GUID/PHP
function get_guid(){
    if (function_exists('com_create_guid')){
        return com_create_guid();
    }   
    else {
        mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
        $charid = strtoupper(md5(uniqid(rand(), true)));
        $hyphen = chr(45);// "-"
      //  $uuid = chr(123)// "{"
        $uuid ="" 
            .substr($charid, 0, 8).$hyphen
            .substr($charid, 8, 4).$hyphen
            .substr($charid,12, 4).$hyphen
            .substr($charid,16, 4).$hyphen
            .substr($charid,20,12);
           // .chr(125);// "}"
        return $uuid;
    }   
}

//echo  get_guid();die;

error_reporting(-1);
ini_set('display_errors', 'On');

set_time_limit(0);
date_default_timezone_set('Europe/London');

if(empty($argv[1])){
	echo '['.date('d-m-Y H:i:s').'] Error: slave database name required! '."\n";
	die;
}

if(empty($argv[2]) || empty($argv[3])){
	echo '['.date('d-m-Y H:i:s').'] Error: no database credentials! '."\n";
	die;
}
 
$db = "master";
$slave = $argv[1];

$dbh = new PDO('mysql:host=localhost;dbname='.$db, $argv[2] ,$argv[3], array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
        if(!$dbh) { echo '['.date('d-m-Y H:i:s').'] Error: no MASTER database connection'."\n"; die;}
		
		
// check for database exitence - sanity check
$sql = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME ='".$slave."';";

    $stmt = $dbh->prepare($sql);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if(!$data){
    	//echo '['.date('d-m-Y H:i:s').'] Error: slave database "'.$slave.'" does not exist! '."\n";
		echo "-2";
		die("");
    }


		
// check master.databases table for $slave		
$sql = "SELECT db_name FROM `databases` WHERE db_name ='".$slave."';";

    $stmt = $dbh->prepare($sql);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if($data){
    	//echo '['.date('d-m-Y H:i:s').'] Error: slave database "'.$slave.'" already imported! '."\n";
		echo "-1";
		die("");
    }

// Add a new record to the databases table
$guid = get_guid();
$sql ="INSERT INTO `databases` (db_ref,db_name) VALUES (:db_ref,:slave)";

    $stmt = $dbh->prepare($sql);
	$stmt->bindParam(':slave',$slave,PDO::PARAM_STR);  
        $stmt->bindParam(':db_ref',$guid,PDO::PARAM_STR);
    $stmt->execute();
    
echo $guid;	
	
	
	
    
	
	
	
	
	
	
	
