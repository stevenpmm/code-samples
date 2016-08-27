<?php

/**
  * Search functionality
	---------------------------------------------------------------------------------------------------------
 */

class Search2 extends CI_Model {

	
	public function __construct() {
		
		parent::__construct();
		
		$resultsArray = array();
	    $regionsArray = array();
	    $productsArray = array();
	}
	
	// called by controller search index() (ref search)
	public function getDataBaseList() {
		
		return explode(',', $this->session->userdata('databases'));
	}
	
	public function demo_users_add_record($id) {
		
		$sql = "INSERT INTO	demo_users (`userid`,`companyid`)";
		$sql .= "values(";		
		$sql .= "'".$this->session->userdata('uid')."',";
		$sql .= "'".$id."'";	
		$sql .= ")";
		
	//	echo $sql;die;
		
		$this->db->query($sql);
		if (!$this->db->affected_rows()){
			log_message('error', 'function: demo_users_add_record() failed (no insert)');
			return false;
		}
		
	}
	
	public function demo_users_view_limit() {
	
		$sql = "SELECT count(distinct companyid) as num FROM demo_users WHERE userid=".$this->session->userdata('uid');
	//	echo $sql;die;
		$result = $this->db->query($sql);
		$row = $result->row();
		if ($row->num >= DEMO_RECORDS_MAX){
			return true;
		}
		
		return false;
	}
	
    // push list to another user
    public function pushUserList($names="") {
   	
    	if($names == "") { return false; }
    	
    	$names_array = explode('-',$names);
    	
    	// INSERT RECORDS
    	foreach ($names_array as $name) {

    		// insert into saved lists log table
	    	$sql = "INSERT INTO	savedlists_push (`searchid`,`userid`,`unixtimestamp`,`pushedby`)";
		    $sql .= "values(";
		    
	    	if($this->session->userdata('savedlist_id_push')){ 
				$listid = $this->session->userdata('savedlist_id_push');  // already saved list
			}else{
				$listid = $this->session->userdata('sql_id_push'); // temp list
			}
			
		    $sql .= "'".$listid."',";		    		    
		    $sql .= "'".$name."',";
		    $sql .= "'".time()."',";
		    $sql .= "'".$this->session->userdata('uid')."'";				    			    
		    $sql .= ")";
			
			//DEBUG
			//echo $sql;
			
		    $this->db->query($sql);
		    if (!$this->db->affected_rows()){
		        log_message('error', 'function: pushUserList() failed (no insert into savedlists_push)');
		        return false;
		     }

		     
		    // insert duplicate record into savedlists table  
		    $sql = "INSERT INTO savedlists (userid,clientid,listname,searchsql,taggedrecords,unixtimestamp,datecreated,searchcriteria,id,record_ids,origin_dsn,rec_count,searchcriteria_html,searchcriteria_read,exclude_sales,excluded_recs) SELECT userid,clientid,listname,searchsql,taggedrecords,unixtimestamp,datecreated,searchcriteria,id,record_ids,origin_dsn,rec_count,searchcriteria_html,searchcriteria_read,exclude_sales,excluded_recs FROM savedlists WHERE searchid=".$listid;
    	    $this->db->query($sql);
		    if (!$this->db->affected_rows()){
		        log_message('error', 'function: pushUserList() failed (no insert into savedlists)');
		        return false;
		     }
		    
		    //Check the existing type 
		    $sql = "SELECT type FROM savedlists WHERE searchid=".$listid;
		    $result = $this->db->query($sql);
			$row = $result->row();
			if ($row){
				$orig_type = $row->type;
			} else {
				$orig_type = "";
			}

		    // update userid in new savedlists table entry
		    if ($orig_type = "Group" && $orig_type != ""){ //Groups have to retain their type, otherwise it just dumps out all values!
				$type = $orig_type;
			} else {
				$type='Pushed By '.$this->session->userdata('username');
			}
		    $sql = "UPDATE savedlists SET userid=".$name.",type='".$type."' WHERE searchid=".mysql_insert_id();
		    $this->db->query($sql);
    	    if (!$this->db->affected_rows()){
		        log_message('error', 'function: pushUserList() failed (no update in savedlists)');
		        return false;
		     } 
		    
		    // send email notification
		    $message = "Good Morning, you have been pushed a new list by ".$this->session->userdata('username')."\n";		
			$message .= "\nKind Regards,\nThe Sales Tracker Support Team";
			$message = wordwrap($message, 70);
		    
		    $this->load->library('email');
			$this->email->from(EMAIL_FROM_DEFAULT, EMAIL_FROM_DEFAULT_NAME);
			$this->email->to($this->_get_username($name));
			$this->email->subject('Salestracker Push List Service');
			$this->email->message($message);		
			$this->email->send();	   
		    
    	} // end of loop
  
	    return true;
    	
    }
	
	// get list of users for this client
    public function getUserList($type = FALSE) {
    	
    	//Full list of users, past and present 
        if ($type == "full"){ 
              
            $sql = "SELECT userid, firstname, surname, status FROM zeus_users WHERE clientid='".$this->session->userdata('cid'). "' ORDER BY status,firstname ASC"; 
          
        //Full list of users, minus removed users 
        } elseif ($type == "current"){ 
              
            $sql = "SELECT userid, firstname, surname FROM zeus_users WHERE clientid='".$this->session->userdata('cid'). "' AND status <> '2' AND password NOT LIKE '%REMOVED%' ORDER BY firstname ASC"; 
          
        //Full list of users, minus removed users and self 
        } elseif ($type == "noself"){ 
              
            $sql = "SELECT userid, firstname, surname FROM zeus_users WHERE clientid='".$this->session->userdata('cid'). "' AND userid <> '".$this->session->userdata('uid')."' AND status <> '2' AND password NOT LIKE '%REMOVED%' ORDER BY firstname ASC"; 
         
            //Removed users
        } elseif ($type == "removed"){
            	 
            	$sql = "SELECT userid, firstname, surname FROM zeus_users WHERE clientid='".$this->session->userdata('cid'). "' AND status = '2' OR clientid='".$this->session->userdata('cid'). "' AND password LIKE '%REMOVED%' ORDER BY firstname ASC";
            	 
        } else { 
          
            $sql = "SELECT userid, firstname, surname FROM zeus_users WHERE clientid='".$this->session->userdata('cid'). "' AND userid <> '".$this->session->userdata('uid')."' AND password NOT like '%REMOVED%' ORDER BY firstname ASC"; 
              
        } 
       
        $result = $this->db->query($sql); 
        if (!$result->num_rows()){ 
           return false; 
         } 
          
         return $result->result(); 
		
	}
	
	// called by ajax::get_credit_info($id)
    public function get_credit_info($id,$dsn){
  	
    	if(ctype_digit($id)){
    	   //$sql = "SELECT * FROM creditsafe WHERE dsn='".$dsn."' and companynumber=".$id. " LIMIT 1";
    	   $sql = "SELECT * FROM creditsafe WHERE companynumber=".$id. " LIMIT 1";
    	}else{
    	   //$sql = "SELECT * FROM creditsafe WHERE dsn='".$dsn."' and companynumber='".$id. "' LIMIT 1";
    		$sql = "SELECT * FROM creditsafe WHERE companynumber='".$id. "' LIMIT 1";
    	}
    	  	
		$result = $this->db->query($sql);
        $row = $result->row();
        
         if ($row){
         	return $row;
         }
         
         return false;
    }
	
    // ensures that uploaded files or notes can only be associated with a saved list. 
	public function check_list_name() {
		
		// if already saved list, exit
		if(!$this->session->userdata('sql_id_temp')){
			return false;
		}
		
		$sql = "SELECT * FROM savedlists WHERE id=".$this->session->userdata('sql_id_temp'). " LIMIT 1";
		$result = $this->db->query($sql);
        $row = $result->row();
        
         if ($row){
         	return false;
         }
         
         return true;
	}
	
	// inserts / updates user selected records into table 'savedlists_temp'
	// called by ajax::display_records() using ajax
    public function delete_tagged_records($list="",$count="") {
    	
        // UPDATE RECORD
        if($this->session->userdata('savedlist') == 1){ 
           $sql = "UPDATE `savedlists` SET taggedrecords = concat(taggedrecords,'".$list."') WHERE searchid=".$this->session->userdata('savedlist_id');	
        }else{
           $sql = "UPDATE `savedlists_temp` SET taggedrecords = concat(taggedrecords,'".$list."') WHERE id=".$this->session->userdata('sql_id_temp')." AND userid=".$this->session->userdata('uid');
        }
     //   echo $sql;die;
	    $this->db->query($sql);
	    if (!$this->db->affected_rows()){
	        log_message('error', 'function: delete_tagged_records() failed (no update)');
	        return false;
	     } 

	    return true;
	}
	
	// returns tagged records, if any
	// called within ajax::display_records() to suppress display of any tagged records
	public function get_tagged_records($slid) {
		
		    $alltags = "";
		
		    // temp list or saved list ?
		    if($slid > 0){ 
		    	$SQL = "SELECT taggedrecords, excluded_recs FROM savedlists WHERE searchid=".$slid;
		    }else{
		    	$SQL = "SELECT taggedrecords, excluded_recs FROM savedlists_temp WHERE id=".$this->session->userdata('sql_id_temp')." AND userid=".$this->session->userdata('uid')." LIMIT 1";
		    }
	//echo $SQL;die;	     
		    $result = $this->db->query($SQL);
		    if (!$result->num_rows()){
		       return false;
		     }

		     $row = $result->row();
	         
		     //Add tagged records and unserialised sales manager exclusions
		     $alltags .= $row->taggedrecords;
		     $alltags .= unserialize($row->excluded_recs);
		      
		     //Just to be on the safe side, a bit of clean up - unique IDs and order them!
		     $alltags = explode('-', $alltags);
		     array_unique($alltags);
		     asort($alltags);
		     $alltags = implode('-',$alltags);
		      
		     return $alltags;
		
	}
	
	// store generated sql in a temp table (until used in store listbuilder list, below )
	// this table contents are deleted after use upon logout 
	// function called when moving from page 2 to page 3
	// CALLED ONCE ONLY FROM AJAX CONTROLLER
    public function store_sql_temp($sqlobject,$ids,$criteria) {  
    
		        // INSERT RECORD
                $sql = "INSERT INTO	savedlists_temp (`userid`,`clientid`,`searchsql`,`searchcriteria`,`unixtimestamp`,`record_ids`,`origin_dsn`,`rec_count`,`searchcriteria_html`,`searchcriteria_read`)";
    	          
                $sql .= "values(";
			    
			    $sql .= "'".$this->session->userdata('uid')."',";
			    $sql .= "'".$this->session->userdata('cid')."',";
			    $sql .= '"'.$sqlobject['sql'].'",';
			    $sql .= "'".serialize($this->input->post())."',";			    		    	    
				$sql .= "'".time()."',";
				$sql .= "'".serialize($ids)."',";
				$sql .= "'".$this->session->userdata('search_dsn')."',";
				$sql .= "'".$sqlobject['rowCount']."',";
				$sql .= "'".$this->input->post('selected-criteria-hidden')."',";
				$sql .= "'".$criteria."'";
			    			    
			    $sql .= ")";
   //return $sql;
			    $this->db->query($sql);
			    if (!$this->db->affected_rows()){
			        log_message('error', 'function: store_sql_temp() failed (no insert)');
			        return 'Error: sql not saved (DB error)';
			     } 
			     
			    // store the new id so that 'store_sql', below can get the last sql generated
			    $this->session->set_userdata('sql_id_temp', mysql_insert_id());  
	   	    
	}
	
	// store listbuilder list
	// function called when a list is saved - uses the id generated when 'store_sql_temp' is populated above
	// generates a new id that is stored for later use - ie tagging records
	// CALLED TWICE: 1. LOGOUT (CONTROLLER: HOME);  OR
	//				 2. USER SAVES A LIST (CONTROLLER: AJAX)
    public function store_sql($lname) {
    	
    	        // remove spaces
    	        $spaces = array('%20',' ');
    	        $lname = str_ireplace($spaces,";",$lname);
    	        
    	        //return 'Error:'.$lname;
    	
    			// check list name for malicious chars
			    $pattern="/^[A-Za-z0-9-.;]+$/"; 
				if(!preg_match($pattern, trim($lname))){
					return 'Error: list name can only contain letters, numbers, dots, spaces and hyphens';
				}		
				
				// get stored sql
				$sql = "SELECT searchsql,searchcriteria,taggedrecords,record_ids,origin_dsn,rec_count,searchcriteria_html,searchcriteria_read,exclude_sales,excluded_recs FROM savedlists_temp WHERE id=".$this->session->userdata('sql_id_temp'). " AND userid=".$this->session->userdata('uid')." LIMIT 1";
				$result = $this->db->query($sql);
	            $row = $result->row();
   	
		        // INSERT RECORD
	            $sql = "INSERT INTO	savedlists (`userid`,`clientid`,`listname`,`searchsql`,`taggedrecords`,`unixtimestamp`,`datecreated`,`searchcriteria`,`id`,`record_ids`,`origin_dsn`,`rec_count`,`searchcriteria_html`,`searchcriteria_read`,`exclude_sales`,`excluded_recs`)";
			    $sql .= "values(";
			    
			    $sql .= "'".$this->session->userdata('uid')."',";
			    $sql .= "'".$this->session->userdata('cid')."',";
			    $sql .= "'".str_ireplace($spaces,'_',mysql_real_escape_string($lname))."_".date('d-m-Y_H:i:s')."',";
			    $sql .= '"'.$row->searchsql.'",';	
			    $sql .= '"'.$row->taggedrecords.'",';		    		    	    
			    $sql .= "".time().",";	
			    $sql .= "'".date('Y-m-d H:i:s')."',";
				$sql .= "'".$row->searchcriteria."',";
				$sql .= "'".$this->session->userdata('sql_id_temp')."',";
				$sql .= "'".$row->record_ids."',";
				$sql .= "'".$row->origin_dsn."',";
				$sql .= "'".$row->rec_count."',";
				$sql .= "'".$row->searchcriteria_html."',";
				$sql .= "'".addslashes($row->searchcriteria_read)."',";
				$sql .= "'".$row->exclude_sales."',";
				$sql .= "'".$row->excluded_recs."'";
			    			    
			    $sql .= ")";
	//return $sql;
			    $this->db->query($sql);
			    if (!$this->db->affected_rows()){
			        log_message('error', 'function: store_sql() failed (no insert)');
			        return 'Error: list not saved (DB error)';
			     } 
			     
			    // store the new id so that table can be updated: see Upload::_get_file_upload_details()
			    $this->session->set_userdata('sql_id', mysql_insert_id());
			    
			    // reset push vars
			    $this->session->set_userdata('sql_id_push', mysql_insert_id()); 
			    $this->session->set_userdata('savedlist_id_push',0);
			    
	   	        //return mysql_insert_id();
	   	        return  $this->session->userdata('sql_id');
	}
	
   	
    public function getContacts($id,$dsn,$cronflag=false,$emailflag=false) {
		
    	    if($cronflag){
    	       $SQL = 'SELECT count(*) as num FROM contacts where companyid=' .$id.' and origin_dsn="'.$dsn.'" LIMIT 1';	
    	    }else{
		       $SQL = 'SELECT firstname,surname,position,email FROM contacts where primarycontact = 0 and companyid=' .$id.' and origin_dsn="'.$dsn.'"';
    	    }
    	    
    	    // csv / xls export for  FORMAT 3
    	    if($emailflag){
    	       $SQL = 'SELECT firstname,surname,position,email FROM contacts where companyid=' .$id.' and origin_dsn="'.$dsn.'"';
    	    }
    	    
		    $result = $this->db->query($SQL);
		    if (!$result->num_rows()){
		       return false;
		     }
	    
	   return $result;  
	   	    
	}
	
	// similar to '_get_database_permissions($dsn)' 
	// but without sessions var population
	public function get_product_permissions($dsn){
		    $SQL = 'select products from `zeus_permissions_new` where userid="' . mysql_real_escape_string($this->session->userdata('uid')) . '" AND dsn = "'.$dsn.'" LIMIT 1';
	   	    //   echo $SQL;die;
			$result = $this->db->query($SQL);
	        $row = $result->row();
					
	        if (!$row){
	        	log_message('error', 'function: get_product_permissions() failed.');	
            	return false;
            }
            else{
            	return $row->products;
            }			
	}
	
	// originally internal function - now public
	// populates sessions vars on the fly according to db selected
	/*
	public function _get_database_permissions($dsn){
		 
		$SQL = 'select * from `zeus_permissions_new` where userid="' . mysql_real_escape_string($this->session->userdata('uid')) . '" AND dsn = "'.$dsn.'" LIMIT 1';
		//echo $SQL;die;
		$result = $this->db->query($SQL);
		$row = $result->row();
	
		if (!$row){
			log_message('error', 'function: _get_database_permissions() failed.');
			return false;
		}

			//Set session vars
			$this->session->set_userdata('regions', $row->regions);
			$this->session->set_userdata('products', $row->products);
			return true;
	
	}
	*/
	
	// populates sessions vars on the fly according to db selected - Sean Payne
	public function _get_database_permissions($dsn,$userid=FALSE){
	
		//Set flag
		$flag = 'n';
	
		//If no ID is being passed in, set to session and change flag
		if ($userid == FALSE){
			$userid = mysql_real_escape_string($this->session->userdata('uid'));
			$flag = 'y';
		}
		 
		$SQL = 'select * from `zeus_permissions_new` where userid="' . $userid . '" AND dsn = "'.$dsn.'" LIMIT 1';
		//echo $SQL;die;
		$result = $this->db->query($SQL);
		$row = $result->row();
	
		if (!$row){
			log_message('error', 'function: _get_database_permissions() failed.');
			return false;
		}
	
		//Either update sessions or return values!
		if ($flag == 'y'){
			//Set session vars
			$this->session->set_userdata('regions', $row->regions);
			$this->session->set_userdata('products', $row->products);
			return true;
		} else {
			return $row->regions . "#" . $row->products;
		}
	
	}
	

	// called by controller ajax create_regions_list() (ref listbuilder)   
	public function get_regions_list($dsn,&$pcodes_flag){
		
		 // get permissions (on the fly)
		 $this->_get_database_permissions($dsn);		 
		 $pcodes_flag=0;
		 
		 // roi or other ?
		 if($dsn == 'roi'){
		 	 $query = "SELECT countyname FROM county_lookup";
		     $result = $this->db->query($query);
		     if (!$result->num_rows()){
		       log_message('error', 'db: no roi county results.');	
		       return false;
		     }
		     
		     foreach ($result->result() as $row) {
	    	    $regionsArray[] = $row->countyname;
		     }
		 }else{
		 
		 	 if(preg_match('/^([A-Za-z]{1,2},*)+$/', $this->session->userdata('regions'))){ // post codes
		 	 	$query = "SELECT regionid, regionname FROM st3_master.regions where regionid > 0 ORDER BY regionname";
		 	 	$pcodes_array = explode(',',$this->session->userdata('regions'));
		 	 	$pcodes_array = array_unique($pcodes_array);
		 	 	$pcodes_array = array_map("strtoupper", $pcodes_array);
		 	 	$pcodes_flag=1;
		 	 }else{
	            $query = "SELECT regionid, regionname FROM st3_master.regions where regionid IN (".$this->session->userdata('regions').") ORDER BY regionname";
		 	 }
	         $result = $this->db->query($query);
		     if (!$result->num_rows()){
		       log_message('error', 'db: no regionname results.');	
		       return false;
		     }
		    
		     foreach ($result->result() as $row) {
		     	
				     $query = "SELECT shortcode FROM st3_master.postcodes where region=".$row->regionid;
				     $result2 = $this->db->query($query);
				     if (!$result2->num_rows()){
				       log_message('error', 'db: no postcode results.');	
				       return false;
				     }
				     foreach ($result2->result() as $row2) {
				     	if($pcodes_flag){
				     	   if(in_array($row2->shortcode,$pcodes_array)){
				     	   	  $regionsArray[$row->regionname][] = $row2->shortcode;
				     	   }	
				     	}else{
				     	   if($dsn == 'ifd' and $row2->shortcode == 'ZZ'){ // no roi with ifd !
				     	   	  continue;
				     	   }	
			    	       $regionsArray[$row->regionname][] = $row2->shortcode;
				     	}
				     }
			    }
		 } // end of else
	    
		 return $regionsArray;   
	} 

	// called by controller ajax create_profiles_list() (ref listbuilder)
	public function get_profiles_list($dsn, $criteria){
		
		 
		 // composite doors 'cd_list1'
		 if($criteria == 'cd_list1'){
			$query = "SELECT distinct lookuplabel FROM systems_lookup WHERE systems_lookup.group = 2 AND (systems_lookup.activity = 1 OR systems_lookup.activity = 0) GROUP BY systems_lookup.lookupvalue ORDER BY systems_lookup.lookuplabel";
		 }
		 // composite doors 'cd_list2'
		 else if($criteria == 'cd_list2'){
		 	$query = "SELECT distinct lookuplabel FROM systems_lookup WHERE systems_lookup.group = 2 AND (systems_lookup.activity = 2 OR systems_lookup.activity = 0) GROUP BY systems_lookup.lookupvalue ORDER BY systems_lookup.lookuplabel";
		 }
		 else if($criteria == 'all'){
		 	  $query = "select distinct lookuplabel from st3_master.systems_lookup where  `group` = 1 and `product` IN(1,2,3) and `material` = 1 order by lookuplabel";	// PVCu	 	
		 }
	     else if($criteria == 'all-al'){
		 	  $query = "select distinct lookuplabel from st3_master.systems_lookup where  `group` = 1 and `product` IN(1,3,4) and `material` = 2 order by lookuplabel";	// Alum	 	
		 }
		 else{
		      $values = explode('-', $criteria);  // group;product;material		
	          $query = "select distinct lookuplabel from st3_master.systems_lookup where  `group` = '".$values[0]."' and `product` = '".$values[1]."' and `material` = '".$values[2]."' order by lookuplabel";
		 }
		 
	     $result = $this->db->query($query);
	     if (!$result->num_rows()){
	       log_message('error', 'db: no systems results.');	
	       return false;
	    }
	    
	     foreach ($result->result() as $row) {
	     	$resultsArray[] = $row->lookuplabel;
	     }
		
		return $resultsArray;
		
	}
	
	// called by controller ajax 'process_lb_data()' 
	public function get_lb_data($mydsn="", $criteria="", $event_flag="", $offset="", $num="") { 	
		
		// no selections made, get ALL data, according to subscription
		$multiselect_flag=false;
		if(!isset($criteria['products']) && !isset($criteria['postcode']) && !isset($criteria['regions'])){				
			return $this->getData($mydsn,"",$event_flag,$offset,$num);	// get total records in db
		}
		else{	
		    // create SQL
		    //$count=0;
  
		    if(isset($criteria['products'])){
		    	
				//foreach ($criteria['products'] as $product_info) {
					
					//echo $product_info;die;
					
		// XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
					
					if($mydsn == 'ifd'){
						
						// capture material for windows / doors from list builder without full permissions
						/*
						$materials_sql="";
						$tokensArray = explode(',',$this->session->userdata('products'));
						if(($mydsn == 'ifd' || $mydsn == 'roi') && IFD_FULL_PERM != count($tokensArray)){
							$tempstr="";
							foreach ($tokensArray as $val) {
								if($val == 'wd:pvc'){$tempstr.= "'PVCu',";}
								if($val == 'wd:alu'){$tempstr.= "'Aluminium',";}
								if($val == 'wd:oth' || $val == 'wd:tim'){$tempstr.= "'Other Materials',";}
							}
							 
							if($tempstr != ""){
								$materials_sql.=" AND material IN (".rtrim($tempstr,',').")";
							}
						}
						*/
						
						// process installers
						$install_sql="";
						if(isset($criteria['installers'])){
							$i_option = $this->input->post('installer-dialogue-options');
							if($i_option == 1){
								$install_sql = " AND service = 'Installer of Windows,Doors or Roofs'";
							}
							if($i_option == 2){
								$install_sql = " AND service <> 'Installer of Windows,Doors or Roofs'";
							}
						}
																	
						// process $criteria['products']
						$multiselect_flag = true;
						$loop=0;
						$max = count($criteria['products']);
						$product_sql=" where ( p.origin_dsn = '$mydsn' ";
						if($install_sql){
							$product_sql .= $install_sql;
						}
						foreach ($criteria['products'] as $product_info) {
						
							$products = explode('AND',$product_info);
						
							foreach ($products as $prod) {
								$product_sql .= ' AND p.'.$prod;
							}
						
							$product_sql .= ')';
						
							if(count($criteria['products']) > 1 && $loop < $max-1){
								$product_sql .=" OR ( p.origin_dsn = '$mydsn'";
							}
							$loop++;
							
							if($install_sql){
								$product_sql .= $install_sql;
							}
						}
				
						$SQL = "SELECT DISTINCT c.companyname,c.tradingas,a.quarantined,c.companyid,a.address1,a.address2,a.address3,a.city,a.postcode,a.region,c.origin_dsn,d.title,d.firstname,";
						//$SQL = "SELECT DISTINCT c.companyname,c.tradingas,a.quarantined,c.companyid,a.address1,a.city,a.postcode,a.region,c.origin_dsn,d.title,d.firstname,";
						$SQL .= "d.surname,d.position,a.telephoneno,a.companyemail,a.addresstypes,c.markets FROM ";
						$SQL .="
							      ( select distinct 
							              p.companyid
							           from 
							              products p".$product_sql.
							           
							
							         ") as PreQuery ";
						$SQL .="JOIN companies c ON PreQuery.CompanyID = c.CompanyID ";
						
						$SQL .="join addresses a 
					               on c.companyid = a.companyid
					              and c.origin_dsn = a.origin_dsn 
					              and a.quarantined = 0";
								
						          /*
					              and a.Region in ( 'Northern Counties',
					                                'North West',
					                                'Yorkshire',
					                                'East Midlands',
					                                'West Midlands',
					                                'South West',
					                                'Home Counties',
					                                'Southern Counties',
					                                'Greater London',
					                                'Scotland',
					                                'Wales',
					                                'Northern Ireland' )
					              join contacts d 
					               on c.companyid = d.companyid
					              AND c.origin_dsn = d.origin_dsn 
					              and d.clientid = 0
								   where 
								          primarycontact = 1
								      and c.origin_dsn = 'ifd' 
								   order by 
								      c.companyname";
								      */
						
						//echo $SQL;die;
					}else{
		// XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
						
					
					$SQL = "SELECT * FROM (( ";
					$SQL .= "SELECT DISTINCT c.companyname,c.tradingas,a.quarantined,c.companyid,a.address1,a.address2,a.address3,a.city,a.postcode,a.region,c.origin_dsn,d.title,d.firstname,";
				   // $SQL .= "SELECT DISTINCT c.companyname,c.tradingas,a.quarantined,c.companyid,a.address1,a.city,a.postcode,a.region,c.origin_dsn,d.title,d.firstname,";
	                $SQL .= "d.surname,d.position,a.telephoneno,a.companyemail,addresstypes,markets FROM companies c "; 
				    $SQL .= "join addresses a on c.companyid = a.companyid ";
				    $SQL .= "join contacts d on c.companyid = d.companyid where d.origin_dsn = '$mydsn' and primarycontact = 1 and d.clientid = 0 and ";
				    $SQL .= "c.origin_dsn = '$mydsn' and a.origin_dsn = '$mydsn' and ";
				    $SQL .= "c.companyid in (select distinct companyid from products where ".$criteria['products'][0]." AND origin_dsn = '$mydsn') ";    
				 
				    $SQL .= ")) as t";
				    $SQL .= " where t.quarantined = 0 and t.origin_dsn = '$mydsn' ";	
				  
					}	   
				   
		    }else{

		    	$SQL = "SELECT * FROM (( ";
		    	$SQL .= "SELECT DISTINCT c.companyname,c.tradingas,a.quarantined,c.companyid,a.address1,a.address2,a.address3,a.city,a.postcode,a.region,c.origin_dsn,d.title,d.firstname,";
		    	//$SQL .= "SELECT DISTINCT c.companyname,c.tradingas,a.quarantined,c.companyid,a.address1,a.city,a.postcode,a.region,c.origin_dsn,d.title,d.firstname,";
		    	$SQL .= "d.surname,d.position,a.telephoneno,a.companyemail,addresstypes,markets FROM companies c ";
		    	$SQL .= "join addresses a on c.companyid = a.companyid ";
		    	$SQL .= "join contacts d on c.companyid = d.companyid where d.origin_dsn = '$mydsn' and primarycontact = 1 and d.clientid = 0 and ";
		    	$SQL .= "c.origin_dsn = '$mydsn' and a.origin_dsn = '$mydsn' and ";
		    	$SQL .= "c.companyid in (select distinct companyid from products where origin_dsn = '$mydsn') ";
		    	
		    	$SQL .= ")) as t";
		    	$SQL .= " where t.quarantined = 0 and t.origin_dsn = '$mydsn' ";
		    } 

		  
			   // add globals
		       $marketsSQL = "";
		       $atypesSQL = "";
			   if(isset($criteria['markets'])){
			   	
			   	if($multiselect_flag){
			   		
			   		$marketsSQL = " and (";
			   		foreach ($criteria['markets'] as $market) {
			   			$marketsSQL .= " c.markets LIKE '%".$market."%' OR";
			   		}
			   		$marketsSQL = rtrim($marketsSQL,'OR');
			   		$marketsSQL .= ")";
			   		
			   	}else{			   	
				    $SQL .= " and (";
					foreach ($criteria['markets'] as $market) {
					   $SQL .= " t.markets LIKE '%".$market."%' OR";  
					}
					$SQL = rtrim($SQL,'OR');
					$SQL .= ")";
			   	   }
			    }

			   if(isset($criteria['addresstypes'])){
			   	
			   	if($multiselect_flag){
			   	
			   		$atypesSQL = " and (";
			   		foreach ($criteria['addresstypes'] as $atype) {
			   			$atypesSQL .= " a.addresstypes LIKE '%".$atype."%' OR";
			   		}
			   		$atypesSQL = rtrim($atypesSQL,'OR');
			   		$atypesSQL .= ")";
			   	
			   	}else{
					$SQL .= " and (";
					foreach ($criteria['addresstypes'] as $atype) {
					   $SQL .= " t.addresstypes LIKE '%".$atype."%' OR"; 
					}
					$SQL = rtrim($SQL,'OR');
					$SQL .= ")";
			   	  }
			    }
			    
			   if(isset($criteria['installers']) && !$multiselect_flag){				   
			    	$SQL .= $criteria['installers'];
			    }
			    
			   // process page 3 button choices eg $criteria['excludes'] for Export list to a standard CSV file 
			
			   // nearest postcodes within give distance and postcode
			   if(isset($criteria['postcode']) && isset($criteria['distance'])){ 
			   	
			   	    $zipcode = $criteria['postcode'];
					if(strlen($zipcode)>4)
						{
							$zipcode = substr($zipcode,0,4); 
						}
						
					$zipCodeArray = $this->getZipCodes(strtoupper($zipcode),0,$criteria['distance']);  
					if(sizeof($zipCodeArray)>1)
						{
							$tempstr="";
							
							// check default postcodes from session var
		                    $this->_check_postcode_permissions($zipCodeArray); 		
			
		                    $tempstr.= rtrim(strtoupper($zipcode)).'|'; // add entered postcode
					        foreach ($zipCodeArray as $pcode) {
						    	$tempstr.= $pcode.' |'; // eg postcode REGEXP '^(LA23 |PE34 |TR12)'
					        }
					        					        
					        if($multiselect_flag){
					        	$regions_sql = " AND a.postcode REGEXP '^(".rtrim($tempstr,'|').")' ";	
					        }else{
					        	$SQL .= "AND t.postcode REGEXP '^(".rtrim($tempstr,'|').")' ";
					        }

					       // echo $SQL;die;
						}
				    else if((sizeof($zipCodeArray) == 1)){
				    	
				    	if($multiselect_flag){
				    		$regions_sql = " AND a.postcode REGEXP '^".$zipcode."'";
				    	}else{
				    		$SQL .= "AND t.postcode REGEXP '^".$zipcode."'";
				    	}
				    }
				    else{
				    	log_message('error', 'db: no postcode lookup results.');
				    	return '0';
				    }							    	
			    }
			
			   // OR convert postcodes to regions 
			   else if(isset($criteria['regions'])){
			   	
			   	    // INDIVIDUAL POST CODES LOOKUP - METHOD
			   	    
			   	    // roi or other ?
			   	    if($mydsn == 'roi'){
			   	        foreach ($criteria['regions'] as $county) {
				        	$regionsArray[] = "'".$county."'";
				        }
			   	    }else{      
			   	
				   	    $tempstr="";
				        foreach ($criteria['regions'] as $pcode) {
				        	if(strlen($pcode)==1) $pcode.='[0-9]+';
					    	$tempstr.= $pcode.'|'; // eg postcode REGEXP '^(L[0-9]|PE)'
				        }
				
				        $qry = "SELECT distinct postcode from addresses where origin_dsn = '$mydsn' "; 
		                $qry .= "AND postcode REGEXP '^(".rtrim($tempstr,'|').")' ";  
		        	                
				        $result = $this->db->query($qry);
					    if (!$result->num_rows()){
					       log_message('error', 'db: no postcode results.');	
					       return false;
					    }
					    			     
					    foreach ($result->result() as $row) {
					    	$regionsArray[] = "'".$row->postcode."'";
				        }		   	
			   	     }
			   	    
			   	     
			   	     if($multiselect_flag){
			   	     	$regions_sql = " and a.postcode IN (".implode(',',$regionsArray).") ";
			   	     }else{
			   	     	$SQL .= " and t.postcode IN (".implode(',',$regionsArray).") ";
			   	     }
			        
			    }
			   else{ // get defaults, no regions OR postcodes selected
			   	
					// get / process region permissions
					$regions_store = $this->session->userdata('regions');
										
				    if(preg_match('/^([A-Za-z]{1,2},*)+$/', $regions_store)){ // post codes
				    	
				    	// get post codes
				    	$regions_store_a = explode(',',rtrim($regions_store,','));
				    	$regions_store_a = array_map("strtoupper", $regions_store_a);
				    	$regions_temp = array();
				    	
				        $tempstr="";
				        foreach ($regions_store_a as $pcode) {
				        	if(strlen($pcode)==1) $pcode.='[0-9]+';
					    	$tempstr.= $pcode.'|'; // eg postcode REGEXP '^(L[0-9]|PE)'
				        }
				        
				        $qry = "SELECT distinct postcode from addresses where origin_dsn = '$mydsn' "; 
		                $qry .= "AND postcode REGEXP '^(".rtrim($tempstr,'|').")' ";    	                
				        $result = $this->db->query($qry);
					    if (!$result->num_rows()){
					       log_message('error', 'db: no postcode results.');	
					       return false;
					    }
			    
					    foreach ($result->result() as $row) {
					    	$regions_temp[] = "'".$row->postcode."'";
				        }	
				        
				        if($multiselect_flag){
				        	$regions_sql = " and a.postcode IN (".implode(',',$regions_temp).") ";
				        }else{
				        	$SQL .= " and t.postcode IN (".implode(',',$regions_temp).") ";
				        }
				 	 }
				 	 else{
				 	 	
				 	 	$query = "SELECT regionname FROM st3_master.regions where regionid IN (".$regions_store.")";
					    $result = $this->db->query($query);
					    if (!$result->num_rows()){
					       log_message('error', 'db: no regionname results.');	
					       return false;
					    }
					    
					    foreach ($result->result() as $row) {
					    	 $regionsArray[] = "'".$row->regionname."'";
					    }
					    
					    if($multiselect_flag){
					    	$regions_sql = " and a.region IN (".implode(',',$regionsArray).") ";
					    }else{
					    	$SQL .= " and t.region IN (".implode(',',$regionsArray).") ";
					    }
					    
					 }			    
			   	
			   } 
		        // END convert postcodes to regions 
		        
			    if($multiselect_flag){
			    	$SQL .= $regions_sql;
			    	$SQL .= $marketsSQL;
			    	$SQL .= $atypesSQL;
			    	$SQL .= "join contacts d 
				               on c.companyid = d.companyid
				              AND c.origin_dsn = d.origin_dsn 
				              and d.clientid = 0
						      where 
						          primarycontact = 1
						      and c.origin_dsn = '$mydsn' ";
			    } 
			    
			    // sorting logic
			    if($this->input->post('sortc')){
			       $SQL .= " order by ".$this->input->post('sortc');	
			    }else{
			       $SQL .= " order by companyname";	
			    }
		        		   
				// pagination
		        if($num != ""){
		           $SQL .= " LIMIT ".$offset.",".$num;
		        }
			
	     // return $SQL;
	     
		 // echo $SQL;
		
			// run query
	        $result = $this->db->query($SQL);
			
			if($result->result()){	
				if($event_flag == 1 || $event_flag == 4 || $event_flag == 5){			
					 $resultsArray['rowCount'] = $result->num_rows(); 
					 $resultsArray['rawData'] = $result->result();
					 $resultsArray['sql'] = $SQL;
					 return $resultsArray;
				}
				if($event_flag == 2 || $event_flag == 3 || $event_flag == 6){ // for csv / xls / xml dump
					return $result;
				}
			}
			else{
				log_message('error', 'sql: '. $SQL.' failed');	
				return false;
			}
			
		}
		
	}

	  // called by controller search index() 
	  // also called by public function get_lb_data(...)
	  public function getData($mydsn="", $criteria="", $event_flag="", $offset="", $num="") {
		    
		    //Assign DSN
		    if($mydsn != ""){
				$dsn = $mydsn;
		    } else {
				$dsn = $this->input->post('data_dsn');
		    }
		    
		    //If the $dsn variable is empty, grab the first database from their permissions
		    if(empty($dsn)){
				$dbs = explode(',',$this->session->userdata('databases'));
				$dbs = explode(':',$dbs[0]);
				$dsn = rtrim($dbs[1],','); 	// get first db in list
		    }
	    
		    //Get permissions (on the fly)
		    $this->_get_database_permissions($dsn);
		    
		    //print_r($this->session->all_userdata());
		    //print_r($this->input->post());die;
		    
		    if($dsn != 'prd'){
	
		    //Get/process region permissions
		    $regions_store = $this->session->userdata('regions');
		    if(preg_match('/^([A-Za-z]{1,2},*)+$/', $regions_store)){ // post codes
		     	
		    	// get post codes
		    	$regions_store_a = explode(',',rtrim($regions_store,','));
		    	$regions_store_a = array_map("strtoupper", $regions_store_a);
		    	$regions_temp = array();
	 	    	
		        $tempstr="";
		        foreach ($regions_store_a as $pcode) {
		        	if(strlen($pcode)==1) $pcode.='[0-9]+';
			    	$tempstr.= $pcode.'|'; // eg postcode REGEXP '^(L[0-9]|PE)'
		        }
		        
		        $qry = "SELECT distinct postcode from addresses where origin_dsn = '$mydsn' "; 
                $qry .= "AND postcode REGEXP '^(".rtrim($tempstr,'|').")'";   
                  	                
		        $result = $this->db->query($qry); 
			    if (!$result->num_rows()){
			       log_message('error', 'db: no postcode results (function getData()).');	
			       return false;
			    }
		
			    foreach ($result->result() as $row) {
			    	$regions_temp[] = "'".$row->postcode."'";
		        }	

		        $SQL_frag = " and a.postcode IN (".implode(',',$regions_temp).") "; 	
		         	
		 	 }else{ // regions
			 	 // run query 
			 	$query = "SELECT regionname FROM st3_master.regions where regionid IN (".$regions_store.")";		 	 
			    $result = $this->db->query($query);
			    
			    if (!$result->num_rows()){
					log_message('error', 'db: no regionname results.');
					return false;
			    }
			    
			    //Loop through, assign results to array
			    foreach ($result->result() as $row) {
					$regionsArray[] = "'".$row->regionname."'";
			    }
			    
			    $SQL_frag = "and a.region IN (".implode(',',$regionsArray).") ";
		 	 }	    
		    
		    //Get/process product permissions from list builder	
		    $products_sql="";
		    $fieldtype=' type IN '; // default
		    
		    if ($dsn == 'asd' || $dsn == 'mcd'){
				
				$fieldtype=' SUBSTRING(type,8) IN '; // pull out numbers, always 2 digits, eg 'sector:12'
		    
		    } elseif ($dsn == 'nrg' || $dsn == 'sbd'){
				
				$fieldtype=' type regexp '; // pull out numbers as a string eg 'sector:1,2,3'
		    
		    }
	    	
		    $type_store="";   
		    $productsArray = explode(',',$this->session->userdata('products'));
		    
		    foreach ($productsArray as $type) {
				
				if($dsn == 'nrg' || $dsn == 'sbd'){
					  $type_store .= $type."|";
				} else {
					  $type_store .= "'".$type."',";
				}
		    
		    }
		    
		    if($dsn == 'nrg' || $dsn == 'sbd'){
				$products_sql = $fieldtype."'(".rtrim($type_store,'|').")'";
		    } else {
				$products_sql = $fieldtype."(".rtrim($type_store,',').")";	
		    }
		    
		    // capture material for windows / doors from list builder without full permissions
		    /*
		    $tokensArray = explode(',',$this->session->userdata('products'));
		    if(($dsn == 'ifd' || $dsn == 'roi') && IFD_FULL_PERM != count($tokensArray)){     
			    $tempstr="";
			    foreach ($tokensArray as $val) {
			    	if($val == 'wd:pvc'){$tempstr.= "'PVCu',";}
			    	if($val == 'wd:alu'){$tempstr.= "'Aluminium',";}
			    	if($val == 'wd:oth' || $val == 'wd:tim'){$tempstr.= "'Other Materials',";}
			    }
			    
			    if($tempstr != ""){
			       $products_sql.=" AND material IN (".rtrim($tempstr,',').")";
			    }
		    }
		    */
		    
		    // search ALL contacts ?
		    $contact_status = " and primarycontact = 1 ";
		    if($this->input->post('filter_s') != 'Enter filter criteria' && $this->input->post('criterion_s') == 'surname_s'){
		    	$contact_status = "";
		    }
		    
		    //Process listbuilder page 3 button choices eg $criteria['excludes'] for Export list to a standard CSV file
		    $SQL = "SELECT DISTINCT c.companyname,c.tradingas,a.quarantined,c.companyid,c.companyregno,a.address1,a.address2,a.address3,a.city,a.postcode,a.region,c.origin_dsn,d.title,d.firstname,";
		    //$SQL = "SELECT DISTINCT c.companyname,c.tradingas,a.quarantined,c.companyid,c.companyregno,a.address1,a.city,a.postcode,a.region,c.origin_dsn,d.title,d.firstname,";
		    $SQL .= "d.surname,d.position,a.telephoneno,a.companyemail,addresstypes,markets FROM companies c ";     
		    $SQL .= "join addresses a on c.companyid = a.companyid ";    
		    $SQL .= "join contacts d on c.companyid = d.companyid where d.origin_dsn = '$dsn'".$contact_status." and d.clientid = 0 and ";
		    $SQL .= "c.origin_dsn = '$dsn' and a.origin_dsn = '$dsn' and ";	    
		    $SQL .= "c.companyid in (select distinct companyid from products where ".$products_sql." AND origin_dsn = '$dsn') ";
		    $SQL .= $SQL_frag; // regions
		    $SQL .= "and a.quarantined = 0 AND c.origin_dsn = '$dsn'";
		    
		    } // starts line 913
		    
		    //print_r($this->input->post());
		    
		    // XXXXXXX PRIVATE DATA ONLY XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
		    
		    if($dsn == 'prd'){
		       $SQL = $this->_get_mydata_sql();
		       $event_flag == 1; // full processing
		    }
		    
		    // XXXXXXX PRIVATE DATA ONLY XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
		    
		    // APPEND SEARCH CRITERIA FOR MAIN SEARCH
		    // VALIDATION IN CONTROLLER
		    
		    $run_flag = false;
		    
		    //If a search term has been entered
		    if($this->input->post('filter_s') != ""){
				
				$SQL_s = "";
				
				//If search term isn't the default "Enter filter criteria"
				if($this->input->post('filter_s') != 'Enter filter criteria'){
					  
					  //Extra precaution - clean the user's search term
					  $this->data = mysql_escape_string(trim($this->input->post('filter_s')));
					  
					  //Wildcard or default search
					  if(strstr($this->data, '*')){
						    $this->data = str_ireplace("*", "%", $this->data);	
					  }elseif ($this->input->post('criterion_s') == 'cid_s' || $this->input->post('criterion_s') == 'crn_s'){
					  	    $this->data = $this->data;	// do  nothing				    	        
					  } else {
						    $this->data = '%'.$this->data.'%';
					  }
					  			      
					  
					  //If the criteria is company name ... also search tradingas field
					  if($this->input->post('criterion_s') == 'cname_s'){
						    
						    //Form SQL - Split the words - Replace multiple spaces with a single space
					  	    $this->data = preg_replace("/[[:blank:]]+/"," ",$this->data);
						    $SQL_s .= " AND ( c.companyname LIKE '".$this->data."'";
						    $SQL_s .= " OR c.tradingas LIKE '".$this->data."'";
						    
							// "and" or "&"?
							                            if(stristr($this->data,'&')){
							                                $this->data2 = str_ireplace("&", "and", $this->data);
							                                $SQL_s .= " OR c.companyname LIKE '".$this->data2."'";
							                                $SQL_s .= " OR c.tradingas LIKE '".$this->data2."'";
							                                $this->data2 = str_ireplace("&", "&amp;", $this->data);
							                                $SQL_s .= " OR c.companyname LIKE '".$this->data2."'";
							                                $SQL_s .= " OR c.tradingas LIKE '".$this->data2."'";
							                            } elseif (stristr($this->data,'and')) {
							                                $this->data2 = str_ireplace("and", "&", $this->data);
							                                $SQL_s .= " OR c.companyname LIKE '".$this->data2."'";
							                                $SQL_s .= " OR c.tradingas LIKE '".$this->data2."'";
							                                $this->data2 = str_ireplace("and", "&amp;", $this->data);
							                                $SQL_s .= " OR c.companyname LIKE '".$this->data2."'";
							                                $SQL_s .= " OR c.tradingas LIKE '".$this->data2."'";
							                            } elseif (stristr($this->data,'&amp;')){
							                                $this->data2 = str_ireplace("&amp;", "&", $this->data);
							                                $SQL_s .= " OR c.companyname LIKE '".$this->data2."'";
							                                $SQL_s .= " OR c.tradingas LIKE '".$this->data2."'";
							                                $this->data2 = str_ireplace("&amp;", "and", $this->data);
							                                $SQL_s .= " OR c.companyname LIKE '".$this->data2."'";
							                                $SQL_s .= " OR c.tradingas LIKE '".$this->data2."'";
							                            }
						    
						    //"limited" or "ltd"? 
						    if(stristr($this->data,'ltd')){
								$this->data2 = str_ireplace("ltd", "limited", $this->data);
								$SQL_s .= " OR c.companyname LIKE '".$this->data2."'";
								$SQL_s .= " OR c.tradingas LIKE '".$this->data2."'";
						    } elseif (stristr($this->data,'limited')){
								$this->data2 = str_ireplace("limited", "ltd", $this->data);
								$SQL_s .= " OR c.companyname LIKE '".$this->data2."'";
								$SQL_s .= " OR c.tradingas LIKE '".$this->data2."'";
						    }
						    
						    $SQL_s .= ")";

				      //if the criteria is company reg no...
				      } elseif ($this->input->post('criterion_s') == 'crn_s'){
				    	    $SQL_s .= ' AND c.companyregno = "'.$this->data.'"';
					  //If the criteria is company ID...
					  } elseif ($this->input->post('criterion_s') == 'cid_s'){
						    $SQL_s .= ' AND c.companyid = "'.$this->data.'"';
					  //If the criteria is postcode...
					  } elseif ($this->input->post('criterion_s') == 'pcode_s'){
						    $SQL_s .= " AND a.postcode LIKE '".$this->data."'";
					  //If the criteria is company email...
					  } elseif ($this->input->post('criterion_s') == 'cemail_s'){
						    $SQL_s .= " AND a.companyemail LIKE '".$this->data."'";
					  //If the criteria is town/city...
					  } elseif ($this->input->post('criterion_s') == 'city_s'){
						    $SQL_s .= " AND a.city LIKE '".$this->data."'";
					  //If the criteria is contact name
					  } elseif($this->input->post('criterion_s') == 'surname_s'){
					
							//Split the words - Replace multiple spaces with a single space
							$words = explode(" ",  preg_replace("/[[:blank:]]+/"," ",$this->data)); 
							
							//Count the words
							$wordnum = count($words);
							
						//	echo $wordnum;die;
							
							// trim up to 3 words
							if(isset($words[0])) {$words[0] = trim($words[0]);}
							if(isset($words[1])) {$words[1] = trim($words[1]);}
							if(isset($words[2])) {$words[2] = trim($words[2]);}
						
							//If only one word was entered, search both the firstname and surname for the word!
							if ($wordnum <= 1){
							
								$SQL_s .= " AND (d.surname LIKE '".trim($this->data)."' OR d.firstname LIKE '".trim($this->data)."%')";
							
							//If it's a triple name (or bigger), chances are that the middle name is saved in the surname field...I'm not even entertaining the idea of four names!
							} elseif ($wordnum > 2){
								
								$SQL_s .= " AND (d.surname LIKE '%".$words[1]." ".$words[2]."' AND d.firstname LIKE '".$words[0]."%') OR (d.surname LIKE '%".$words[2]."' AND d.firstname LIKE '".$words[0]." ".$words[1]."%')";
								
							// Otherwise, treat it like 'firstname surname' OR 'surname firstname'
							} else {
						
								$SQL_s .= " AND (d.surname LIKE '%".$words[1]."' AND d.firstname LIKE '".$words[0]."%')";
							//	$SQL_s .= " OR (d.surname LIKE '%".$words[0]."' AND d.firstname LIKE '".$words[1]."%')";
						
							}
					  	
					  } elseif($this->input->post('criterion_s') == 'telephone_s'){
						    $SQL_s .= " AND a.telephoneno LIKE '".$this->data."'";
					  }
					  
				}//End of "if not default search message"
				
				$SQL .= $SQL_s;
				
		    	//echo  $SQL;die;
				
				//Set sessions for search
				$this->session->set_userdata('filter_s_store', str_replace("%",'#',$SQL_s));
				$this->session->set_userdata('searchterm', $this->input->post('filter_s'));
				$this->session->set_userdata('searchcriteria', $this->input->post('criterion_s'));
	         
		     }

		
		    if(!$run_flag && strstr(uri_string(),'search') && !$this->session->userdata('relations_sql')){  // ie no relationships form submitted   			   
			   	   $SQL .=  str_replace('#','%',$this->session->userdata('filter_s_store')); // required to keep pagination when criteria entered
	         }
	         
	         // relationships SQL here
	         $run_flag_relations = false;
	         if($this->input->post('search-relationships-btn') == 'Search'){
	         	
	         	$SQL_r = ' and c.companyid in (SELECT companyid FROM customer WHERE clientid='.$this->session->userdata('cid').' and c.origin_dsn="'.$this->input->post('data_dsn_dash2').'"';
	         	
	         	if($this->input->post('status')){
	         		$SQL_r .= ' and cconstatus="'.$this->input->post('status').'"';
	         	}
	         	
	         	if($this->input->post('category')){
	         		$SQL_r .= ' and category="'.$this->input->post('category').'"';
	         	}
	         	
	         	if($this->input->post('custom1')){
	         		$SQL_r .= ' and ccustom1="'.$this->input->post('custom1').'"';
	         	}

	         	if($this->input->post('custom2')){
	         		$SQL_r .= ' and ccustom2="'.$this->input->post('custom2').'"';
	         	}
	         	
	         	$SQL_r .= ')';
	         	
	         	$SQL .= $SQL_r;
	         	$this->session->set_userdata('relations_sql', $SQL_r);
	         	$run_flag_relations = true;
	         }	

	         // required to keep pagination
	         if(!$run_flag_relations && strstr(uri_string(),'search') && $this->session->userdata('search_dsn_relationships')){
	         	$SQL .= $this->session->userdata('relations_sql');
	         }
	         // END relationships SQL 
		    
	         // sorting logic
		     if($this->input->post('sortc')){
		       $SQL .= " order by ".$this->input->post('sortc');	
		     }else{
		       $SQL .= " order by companyname";	
		     }
		    
	         // pagination
	         if($num != ""){
	           $SQL .= " LIMIT ".$offset.",".$num;
	         }
	        
	        //  return $SQL;
	        
	       //   echo  $SQL;die;
			
	        $result = $this->db->query($SQL);
			
	        if($result->result()){	
				if($event_flag == 1 || $event_flag == 4 || $event_flag == 5){			
					 $resultsArray['rowCount'] = $result->num_rows(); 
					 $resultsArray['rawData'] = $result->result();
					 $resultsArray['sql'] = $SQL;
					 return $resultsArray;
				}
				if($event_flag == 2  || $event_flag == 3 || $event_flag == 6){ // for csv / xls / xml dump
					return $result;
				}
			}
			else{
				log_message('error', 'sql: '. $SQL.' failed');	
				return false;
			}
	}
	
	// assigns a color to a credit rating
	public function get_credit_status($companyregno){
		
		if(ctype_digit($companyregno)){
			$sql = "SELECT creditrating  FROM creditsafe WHERE companynumber=".$companyregno." LIMIT 1"; // ignores leading zeros
		}else{
			$sql = "SELECT creditrating  FROM creditsafe WHERE companynumber='".$companyregno."' LIMIT 1";
		}
		
	//	echo $sql;die;
		$result = $this->db->query($sql);
		$row = $result->row();
		
		if($row){	
		   return $row->creditrating;
		}
		
		return 0;
		
		/*
		$colour="";
		
		 if ($row){
				
			if($row->creditrating < 30){
				$colour="#fb4135"; // red
			}elseif($row->creditrating < 51) {
				$colour="#fbd60c";  // amber
			}else{
				$colour="#56ce2f"; // green
			}
		 }
			
		return $colour;
		*/
	}
	
	// returns base sql for my data
	private function _get_mydata_sql(){
		
		$sql = "SELECT c.companyname,c.tradingas,a.quarantined,c.companyid,a.address1,a.address2,a.address3,a.city,a.postcode,a.region,c.origin_dsn,d.title,d.firstname,d.surname,d.position,a.telephoneno,a.companyemail,addresstypes,markets FROM companies c join addresses a on c.companyid = a.companyid join contacts d on c.companyid = d.companyid where d.origin_dsn = 'prd' and c.origin_dsn = 'prd' and a.origin_dsn = 'prd' and a.quarantined = 0 AND c.origin_dsn = 'prd' ";
		//$sql = "SELECT c.companyname,c.tradingas,a.quarantined,c.companyid,a.city,a.address1,a.postcode,c.origin_dsn,d.firstname,d.surname,d.position,a.region,a.telephoneno,a.companyemail,addresstypes,markets FROM companies c join addresses a on c.companyid = a.companyid join contacts d on c.companyid = d.companyid where d.origin_dsn = 'prd' and c.origin_dsn = 'prd' and a.origin_dsn = 'prd' and a.quarantined = 0 AND c.origin_dsn = 'prd' ";
	    $sql .= ' and c.clientid='.$this->session->userdata('cid');
		return $sql;
	}
	
	public function _get_username($id){
		
		$sql = "SELECT username FROM zeus_users WHERE userid=".$id. " LIMIT 1";
		$result = $this->db->query($sql);
        $row = $result->row();
        
         if ($row){
         	return $row->username;
         }
         
         return false;
		
	}
	
	public function check_fen_installer($id,$dsn){
	
		$sql = "SELECT * FROM products WHERE companyid=".$id. " and origin_dsn='".$dsn."' and service='Installer of Windows,Doors or Roofs' LIMIT 1";
		$result = $this->db->query($sql);
		$row = $result->row();
	
		if ($row){
			return true;
		}
		 
		return false;
	
	}
	
	// POSTCODE LOOKUP FUNCTIONS (4)
    private function selectQry($sql,$return_type='')
				{						
						//echo $sql;
						$retResultSelect	=	array();
						$rs	=	mysql_query($sql) or die("MySQL Error Happend : " .mysql_error());
						if($return_type == "")
						{
							while( ($row	=	mysql_fetch_assoc($rs)))
							{
								$retResultSelect[]	=	$row;
							}
						
							return $retResultSelect;
							
						}
						else if($retun_type == "resource") return $rs;
				}
				
	private function zipRadiusSQL($varZip, $varLatitude, $varLongitude, $varMiles) {
		
		$varLatRange = $varMiles / ((6076 / 5280) * 60) + ($varMiles / 1000);
		$varLonRange = $varMiles / (((cos($varLatitude * 3.141592653589 / 180) * 6076.) / 5280.) * 60) + ($varMiles / 1000);
			
		$zipRadiusSQL_str = "SELECT latitude, longitude, district , zipcode";
		$zipRadiusSQL_str = $zipRadiusSQL_str . " FROM ukpostcodes_lookup WHERE zipcode != ''";
		$zipRadiusSQL_str = $zipRadiusSQL_str . " AND (";
			$zipRadiusSQL_str = $zipRadiusSQL_str . "longitude <= (" . $varLongitude . " + " . $varLonRange . ")";
			$zipRadiusSQL_str = $zipRadiusSQL_str . " AND ";
			$zipRadiusSQL_str = $zipRadiusSQL_str . "longitude >= (" . $varLongitude . " - " . $varLonRange . ")";
		$zipRadiusSQL_str = $zipRadiusSQL_str . ")";
		$zipRadiusSQL_str = $zipRadiusSQL_str . " AND (";
			$zipRadiusSQL_str = $zipRadiusSQL_str . "latitude <= (" . $varLatitude . " + " . $varLatRange . ")";
			$zipRadiusSQL_str = $zipRadiusSQL_str . " AND ";
			$zipRadiusSQL_str = $zipRadiusSQL_str . "latitude >= (" . $varLatitude . " - " . $varLatRange . ")";
		$zipRadiusSQL_str = $zipRadiusSQL_str . ")";
		if ($varZip != "") {
			$zipRadiusSQL_str = $zipRadiusSQL_str . " AND zipcode <> '" . $varZip . "'";
		}
		$zipRadiusSQL_str = $zipRadiusSQL_str . " AND longitude <> 0";
		$zipRadiusSQL_str = $zipRadiusSQL_str . " AND latitude <> 0";
		$zipRadiusSQL_str = $zipRadiusSQL_str . " ORDER BY zipcode ASC";
		$zipRadiusSQL = $zipRadiusSQL_str;
	
		return $zipRadiusSQL;
       }

    private function zipDistCalc($Lat1, $Lon1, $Lat2, $Lon2, $UnitFlag) {
	
	    $PI = 3.141592654;
	    if (is_null($Lat1)) {
	    	return;
		}
	
	    if($Lat1 == 0 or $Lon1 == 0 or $Lat2 == 0 or $Lon2 == 0) {
	        $DistCalc = -1;
	        return $DistCalc;
	    } elseif ($Lat1 == $Lat2 and $Lon1 == $Lon2) {
	        $DistCalc = 0;
	        return $DistCalc;
	    }
	
	    $LatRad1 = $Lat1 * $PI / 180;
	    $LonRad1 = $Lon1 * $PI / 180;
	    $LatRad2 = $Lat2 * $PI / 180;
	    $LonRad2 = $Lon2 * $PI / 180;
	    $LonRadDif = Abs($LonRad1 - $LonRad2);
	    $X = Sin($LatRad1) * Sin($LatRad2) + Cos($LatRad1) * Cos($LatRad2) * Cos($LonRadDif);
	    $RadDist = atan(-$X / sqrt(-$X * $X + 1)) + 2 * atan(1);
	    $DistMI = $RadDist * 3958.754;
	    $DistKM = $DistMI * 1.609344;
	    If (strtoupper($UnitFlag) == "M") {
			$zipDistCalc = $DistMI;
		} else {
			$zipDistCalc = $DistKM;
		}
		return $zipDistCalc;
       }
       
    private function getZipCodes($zip_code,$radiusRangeLow='0',$radiusRangeHeigh='10')
	{
		$zipCodesArray=array();
		$fetchZipInfoQry="SELECT zipcode,latitude,longitude FROM ukpostcodes_lookup WHERE zipcode='".addslashes($zip_code)."'";	
		$zipInfo=$this->selectQry($fetchZipInfoQry);
		if(sizeof($zipInfo)>0)
		{	
			$fetchZipsInRangeSql = $this->zipRadiusSQL($zipInfo[0]['zipcode'], $zipInfo[0]['latitude'], $zipInfo[0]['longitude'], $radiusRangeHeigh);
			$zipRangeInfo=$this->selectQry($fetchZipsInRangeSql);
			$zipRangeInfoSize=sizeof($zipRangeInfo);
			if($zipRangeInfoSize>0)
			{
				for($i=0;$i<$zipRangeInfoSize;$i++)
				{
					$zipLatitude = $zipRangeInfo[$i]["latitude"];
					$zipLongitude = $zipRangeInfo[$i]["longitude"];
					$zipZipCode = $zipRangeInfo[$i]["zipcode"];
					$zipDistance = $this->zipDistCalc($zipInfo[0]['latitude'], $zipInfo[0]['longitude'], $zipLatitude, $zipLongitude, "M");
					if(($zipDistance > $radiusRangeLow) and ($zipDistance < $radiusRangeHeigh))
					{
						$zipCodesArray[]="".$zipZipCode."";
					}
				}
				// rturn the $zipCodesArray
				unset($zipcodeClass);
				return $zipCodesArray;
			
			}else
			{
				## no matching zip codes found
				return $zipCodesArray;
			}	
		}else
		{
			## zip code is invalid ( not exist in the zipcode db)
			return $zipCodesArray;
		}

       }

    // compares 2 arrays to ensure that postcodes returned from radius search 
    // are within the user subscription   
    private function _check_postcode_permissions(&$zipCodeArray){
    	
    	$temparray = array();
    	$temparray = explode(',',$this->session->userdata('postcodes'));	
    	
        foreach ($zipCodeArray as $pcode) {
        	
        	$pcode = substr($pcode,0,2); 
        	$pcode2="";
        	$found=false;
        	foreach ($temparray as $pcode_def) {
        		
        	   if(strlen($pcode_def) == 1)	{
        	   	  $pcode2 = substr($pcode,0,1);
        	   }
        	   
			   if($pcode_def == $pcode || $pcode_def == $pcode2){
			   	  $found=true; 
			   	  break;
			   }
        	}
        	// remove element from $zipCodeArray	
        	if(!$found)	{
        		unset($zipCodeArray[$pcode]);
        	}	    	
		}
     }   
				
	// END POSTCODE LOOKUP FUNCTIONS
	
     //Update the sales exclusions for any given list
     public function update_sales_exclusions($listid, $statuses, $records, $listtype = FALSE){
     
     	$sql = "UPDATE savedlists";
     
     	//Temp or regular table?
     	if ($listtype == "temp"){
     		$sql .= "_temp";
     	}
     
     	$sql .= " SET exclude_sales = '" . $statuses . "', excluded_recs = '" . $records . "' WHERE ";
     
     	//Table id column has different name in both tables!
     	if ($listtype == "temp"){
     		$sql .= "id";
     	} else {
     		$sql .= "searchid";
     	}
     
     	$sql .= " = '" . $listid . "'";
     
     	//DEBUG
     	//echo $sql . "<br />";
     
     	$this->db->query($sql);
     
     	if (!$this->db->affected_rows()){
     	log_message('error', 'function: update_sales_exclusions failed (no update)');
			return false;
     	}
     
     	return true;
     
     }
     
     //Get the relationships excluded on a list
     public function get_sales_exclusions($listid){
     
     $sql = "SELECT exclude_sales FROM savedlists WHERE searchid = '" . $listid . "'";
     
		//echo $sql;
     
     				$result = $this->db->query($sql);
     				$row = $result->row();
     
     				if ($row){
     				return $row->exclude_sales;
     				}
     
     				return false;
     
     }
     
     //Fetch the IDs from a list
     public function fetch_list_ids($listid, $listtype){
     
     $sql = "SELECT record_ids FROM savedlists";
     
		if ($listtype == "temp"){
		     $sql .= "_temp";
		     }
		     
				$sql .= " WHERE ";
		     
		     				if ($listtype == "temp"){
		     				$sql .= "id";
		     } else {
     					$sql .= "searchid";
     				}
     
     				$sql .= " = " . $listid;
     
     				//DEBUG
		//echo " " . $sql . " ";
     
     				$result = $this->db->query($sql);
     				$row = $result->row();
     
     				if ($row){
     				return $row->record_ids;
     				}
     
     				return false;
     
     }
}

?>
