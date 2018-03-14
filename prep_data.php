<?php

// Creator: S Matthews
// Date:  14/09/2017
// Parameters (4): $argv[1] (database name), $argv[2] ("db_ref"), $argv[3] (username), $argv[4] (password)

// Description: This script will do 2 things: 
//  1.Add a  new field (db_ref) to ALL tables in the database (default value = 1)
//  2. Remove or re-assign the primary key in each table (to avoid data conflicts during merging); Table loan - PK changed from loanid to loanid AND db_ref

// USAGE: stand alone for master db creation and called within the bash script "merge_database.sh" for slave prepping

error_reporting(-1);
ini_set('display_errors', 'On');

set_time_limit(0);
date_default_timezone_set('Europe/London');

// SET PARAMETERS

$db = "master";
if(!empty($argv[1])){
 $db = $argv[1];
}

if(empty($argv[2])){
  echo '['.date('d-m-Y H:i:s').'] Error: no db_ref provided! '."\n";
  die;
}
$db_ref = $argv[2];

if(empty($argv[3]) || empty($argv[4])){
	echo '['.date('d-m-Y H:i:s').'] Error: no database credentials! '."\n";
	die;
}

$dbh = new PDO('mysql:host=localhost;dbname='.$db, $argv[3], $argv[4], array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
        if(!$dbh) { echo '['.date('d-m-Y H:i:s').'] Error: no database connection'."\n"; die;}

		
$sql = "show tables;";

    $stmt = $dbh->prepare($sql);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if(!$data){
    	echo '['.date('d-m-Y H:i:s').'] Error: no tables found ! '."\n";
		die("");
    }
	
	//print_r($data);die;
	
	// Alter tables - add new field (db_ref) AND UPDATE the primary key
	$loop=0;
	foreach($data as $table){
		
          	//echo $table['Tables_in_'.$db]."\n";
		
		// add new field
		$sql = "ALTER TABLE ".$table['Tables_in_'.$db]." ADD db_ref VARCHAR(50)  DEFAULT '".$db_ref."' FIRST;";		
		$stmt = $dbh->prepare($sql);
        $stmt->execute();
		
		// get PK, if there is one !
		$sql = "SHOW INDEX FROM ".$table['Tables_in_'.$db]." WHERE Key_name = 'PRIMARY';";	
		
		$stmt = $dbh->prepare($sql);
		$stmt->execute();
		$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
		
		if(empty($data[0]['Column_name'])){continue;} // ie NO PK		
		//print_r($data[0]['Column_name']);die;
		$pk = $data[0]['Column_name'];
			
		if(count($data)==1){ // ie ONE PK
			$sql = "ALTER TABLE ".$table['Tables_in_'.$db]."  MODIFY ".$pk." int(11), DROP PRIMARY KEY,  ADD PRIMARY KEY (".$pk.",db_ref);"; // update PK
		}else{  // ie composite PK		
			 // remove composite PK 
             $sql = "";			 
			foreach($data as $key){
				$sql .= "ALTER TABLE ".$table['Tables_in_'.$db]." MODIFY ".$key['Column_name']." int(11);";
			}
			
            $sql .= "ALTER TABLE ".$table['Tables_in_'.$db]." DROP PRIMARY KEY;";
            //echo $sql;die;			
		}
		
		$stmt = $dbh->prepare($sql);
		$stmt->execute();
		
		++$loop; 
	}
	
	echo '['.date('d-m-Y H:i:s').'] '.$db.': Tables successfully updated ('.$loop.' tables ) '."\n";
    
	
	
	
	
	
	
	
