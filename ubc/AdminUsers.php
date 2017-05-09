<?php
session_start();
require_once BASE.'/app/model/base/DAO.php';


class AdminUsers extends BaseController
{
	public function view()
	{
		
		if ($this->isPosting()) {
				return $this->processPost();
		}

		if(!isset($_SESSION['user']['id'])) {
			header("Location: {$url_prefix}/Login");
			die();
		}
		
			$dao = DAO::getDAO('UserDAO');
			$r = $dao->getAllUsers();
			$c = $dao->fetchBrokerCompanies();

			$v = $this->processTemplate(v('admin.html'),
							array('<!--CONTENT-->' => v('admin-users.html')),
							array('results' => $r, 'title' => "All Users",'brokerCompanies' => $c, 'heading' => "User Management", 'subheading' => "")
			);

		
		
		$this->display($v);
	}
	
   public function processPost() {
	   
	   // sanity check
	   foreach($_POST as $key => $val) {
	      $_POST[$key] = addslashes($val);
	   }
   
		
   	  $dao = DAO::getDAO('UserDAO');
   	  $messages = array();
   	  $status = "";
   	  
   	    // ADD A NEW / UPDATE AN INTRODUCER COMPANY
   	    if($_POST['submit_c']=='Add Company' || $_POST['submit_c']=='Update Company'){
   	  	
	   	    if(!$_POST['company'] || !$_POST['url'] || !$_POST['details'] || !$_POST['pcon'] || !$_POST['address'] || !$_POST['bankname'] || !$_POST['banknumber'] || !$_POST['banksort']){
				$messages[] = "ALL fields need to be completed !";
			}
			
   	        //proceed if no errors
		    if(count($messages) == 0) {
		    	
		    	$data = array(
						'name' => trim($_POST['company']),
						'url' => trim($_POST['url']),		    	
						'details' => $_POST['details'],    	
				    	'pcon' => $_POST['pcon'],
				    	'address' => $_POST['address'],
				    	'bankname' => $_POST['bankname'],
				    	'banknumber' => $_POST['banknumber'],
				    	'banksort' => $_POST['banksort'] 	
				    );
		    	
		    	// update an existing company
		    	if($_POST['submit_c']=='Update Company'){
		    		 $data['id']=$_POST['cid']; 		    		 
		    		 //print_r($data);die;		    		 
		    		 $dao->updateIntroducerCompany($data);
		    		$status = "The Introducer Company has been successfully updated...";
		    	}else{
		    	
			    	// add new company 
				    $dao->addIntroducerCompany($data);			    	
			    	$status = "The Introducer Company has been successfully added ...";
		    	}
		    	
		    	// reset
   	            $data = array();
		        unset($_POST);
		    }
		    
		  // REVOKE AN INTRODUCER COMPANY
   	    }else if($_POST['submit_r_c']=='Remove Company'){

   	    	if(!$_POST['broker_company_update_r']){
   	    		$messages[] = "Please select a company !";
   	    	}
   	    	
   	    	//proceed if no errors
		    if(count($messages) == 0) {
		    	
		    	// remove broker wide settings (Tables users, settings_broker)
		    	// get user_id list from table broker_company_users
		    			    	
		    	$ids = $dao->getBrokersForCompany($_POST['broker_company_update_r']);
		    	//print_r($ids);die;
		    	foreach($ids as $id){
		     	 
			    	$email = $dao->get_email($id['user_id']); // convert to email addresses 		    	
			    	$dao->removeBrokersettings($email); // settings_broker
			    	$dao->deleteUser_admin($email); // Users		    		
		    	}		    	
		    	
		    	// remove company wide settings
		    	$dao->updateBrokerCompany($_POST['broker_company_update_r']);
		    	$dao->removeIntroducerCompany($_POST['broker_company_update_r']);
		    	
		    	$status = "The Introducer Company has been successfully removed ...";
		    	
		    	$data = array();
		        unset($_POST);		    	
		    }
   	    
   	  
   	    // REVOKE A USER   submit_r_c Remove Company
   	    }else if($_POST['submit_r']=='Revoke User'){
   	    	
   	        // basic email regex; allows for domains up tp 20 chars
			if(!preg_match('/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,20}$/i',$_POST['email'])) {
				$messages[] = "Not a valid email address !";
			}
			
   	        // check that email already exists
			if($_POST['email'] && !$dao->_check_user($_POST['email'])){
			   $messages[] = "No user found with this email address !";
			}
			
			//proceed if no errors
		    if(count($messages) == 0) {
		    	
		    	// remove broker settings
		    	$dao->removeBrokersettings($_POST['email']);
		    	
		    	// remove entry from table broker_company_users
		    	// ONLY DELETES RECORD IF USER IS A BROKER
		    	$dao->removeBrokerCompany($_POST['email']);
		    	
		    	$dao->deleteUser_admin($_POST['email']);
		    	//$dao->suspendUser_admin($_POST['email']);
		    	$status = "The user has been successfully removed ...";
		    	
		    	// reset
   	            $data = array();
		        unset($_POST);
		    }
			
   	  	 
   	    }else{

   	    //  ADD A USER
   	  
		//validation 
		if(!$_POST['name'] || !$_POST['email']){
			$messages[] = "ALL fields need to be completed !";
		}else{
		
	        // basic email regex; allows for domains up tp 20 chars
			if(!preg_match('/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,20}$/i',$_POST['email'])) {
				$messages[] = "Not a valid email address !";
			}
			
			// check that email already exists
			if($_POST['email'] && $dao->_check_user($_POST['email'])){
			   $messages[] = "A user with this email address already exists !";
			}

			// broker URL / Fee checking
			if($_POST['user_type']=="admin-broker"){
				
				if(!$_POST['url']){
				  $messages[] = "URL needs to be completed !";	
				}
				
				 if(!preg_match('/^[A-Za-z0-9]*$/', $_POST['url'])) {
					$messages[] = "Custom URL should only contain alphanumeric values";
				 }
				
				
			     if(strlen($_POST['url']) > 50 || !$_POST['url']) {
					$messages[] = "Custom URL should be up to 50 characters";
				}
				 
				$dao2 = DAO::getDAO('SettingsDAO');
				if($dao2->lookupSlug($_POST['url'])) {
					$messages[] = "This URL '{$_POST['url']}' is already in use";
				}	

			    if(!$_POST['fee'] || !is_numeric($_POST['fee']) ){
				  $messages[] = "A numeric fee needs to be set !";	
				}
				
				if($_POST['fee_type']=='1'){
				    if((float) $_POST['fee'] > BROKER_COMMISSION_PERCENT_MAX) {
						$messages[] = "The maximum permissable percentage commission is " . BROKER_COMMISSION_PERCENT_MAX." %";
					}
				}
			}				
		}
		
		// BUSINESS RULES
		
        // broker CANNOT be superadmin
		if($_POST['user_type']=='admin-broker' && $_POST['superadmin']){
			$messages[] = "A broker CANNOT be superadmin !";
		}
		
		//print_r($_POST);die;

		//proceed if no errors
		if(count($messages) == 0) {
			
			$user_type = $_POST['user_type'];
			
			// broker checks
			$broker = $_POST['user_type']=='admin-broker'? '1' : '0';
			if($_POST['user_type']=='admin-broker'){$user_type = 'admin';}
			
			// create password
			$pword =  $dao->create_password();
			
			$data = array(
			'name' => trim($_POST['name']),
			'email' => trim($_POST['email']),
			'password' => $dao->_generateHash($pword, false),
			'timestamp' => time(),
		    'user_type' => $user_type,
		    'enabled' => '1',
			'broker' => $broker,
			'superadmin' => $_POST['superadmin']
		    );
		    
		    //print_r($data);
		    
		    // add to users table
		    $dao->addUser_admin($data);
		    $status = "The new User has been successfully added ...";
		    
		    $link=false;
		    
		    // if broker, add entry in table broker_company_users
		    if($broker){	    	
		    	$dao->addBrokerCompany($_POST['broker_company']);
		    	
		    	// get user id from newly created record
		    	$user_id = $dao->checkUsers(trim($_POST['email']));
		    	
		    	// set broker defaults
		    	/*
			    (11, 'commission_pc', '0', 'Commission (%)'),
				(11, 'commission_pounds', '0', 'Commission (£)'),
				(11, 'commission_type', 'pc', 'Commission Type'),
				(11, 'url_slug', 'myUrl', 'Custom URL');
				*/
		    	
		    	$data2 = array();
		    	if($_POST['fee_type']=='1'){ // pc
		    		$data2[] = array('broker_id' => $user_id, 'setting' => 'commission_pc', 'value' => $_POST['fee'], 'display' => 'Commission (%)');
			    	$data2[] = array('broker_id' => $user_id, 'setting' => 'commission_pounds', 'value' => '0', 'display' => 'Commission (£)');
			    	$data2[] = array('broker_id' => $user_id, 'setting' => 'commission_type', 'value' => 'pc', 'display' => 'Commission Type');
			    	$data2[] = array('broker_id' => $user_id, 'setting' => 'url_slug', 'value' => $_POST['url'], 'display' => 'Custom URL');
		    	}else{ // pounds
		    		$data2[] = array('broker_id' => $user_id, 'setting' => 'commission_pc', 'value' => '0', 'display' => 'Commission (%)');
			    	$data2[] = array('broker_id' => $user_id, 'setting' => 'commission_pounds', 'value' => $_POST['fee'], 'display' => 'Commission (£)');
			    	$data2[] = array('broker_id' => $user_id, 'setting' => 'commission_type', 'value' => 'pounds', 'display' => 'Commission Type');
			    	$data2[] = array('broker_id' => $user_id, 'setting' => 'url_slug', 'value' => $_POST['url'], 'display' => 'Custom URL');
		    	}
		    			    	 
		    	// populate table settings_broker
		    	foreach($data2 as $row){	    		
		    		$dao->setBrokerdefaults($row);
		    	}

		    	$link = $_POST['url'];
		    }
		    
		    // send email
		    $this->notify($data,$pword,$link);
		    
		    // reset
   	        $data = array();
		    unset($_POST);
		    
		   }
		   
   	    } // end of add a user
   	    
		
		    $r = $dao->getAllUsers();
		    
		    $c = $dao->fetchBrokerCompanies();
		
			$v = $this->processTemplate(v('admin.html'),
							array('<!--CONTENT-->' => v('admin-users.html')),
							array('results' => $r, 'messages' => $messages, 'status' => $status,'brokerCompanies' => $c, 'title' => "All Users", 'heading' => "User Management", 'subheading' => "")
			);
			
			$this->display($v);
	}
	
	// send email notification
	private function notify($data,$pword,$url) {
		
		// set url
		$baseUrl = "https://www.ultimatebusinesscash.co.uk/";
		if(stristr($_SERVER['SERVER_NAME'],"ubcdtest")){
			$baseUrl = "https://www.ubcdtest.co.uk/";
		}
		
		require_once BASE.'/lib/MimeMail/MimeMail.php';
		
		if(!$data['broker']){
			
			$msg = "Good Day,"."\n\n";
	        $msg .= "You have been given access to the UBC admin panel. Your login credentials are as follows ..."."\n\n";
	        $msg .= "username: ".$data['email']."  \npassword: ".$pword."\n\n";
	        $msg .= "Please go to ".$baseUrl."Admin to login";
	        	
		}else{
			
			$msg = "Good Day,"."\n\n";
	        $msg .= "You have been given access to the UBC introducer admin panel. Your login credentials are as follows ..."."\n\n";
	        $msg .= "username: ".$data['email']."  \npassword: ".$pword."\n\n";
	        $msg .= "The link to send to your clients is ".$baseUrl.$url."\n\n";
	        $msg .= "Please go to ".$baseUrl."AdminApplicationsBroker/ to login and view your deals"."\n\n\n";
	        $msg .= "PLEASE NOTE THAT PASSWORDS ARE CHANGED EVERY MONTH - YOU WILL RECEIVE AN EMAIL NOTIFICATION ABOUT THIS.";
			
		}
		
		//echo $msg;
		
		$m = new MIMEMail();
		$m->add(MIMEMAIL_TEXT, $msg);
		
		$m->addHeader('Bcc', 'test@ultimatefinance.co.uk');
		$m->addHeader('Bcc', 'test@ultimatefinance.co.uk');
		$m->send('info@ultimatebusinesscash.co.uk', $data['email'], 'Ultimate Business Cash Administration');
	}
	
}

















