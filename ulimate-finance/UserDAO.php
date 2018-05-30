<?php

defined('BASE') or exit('Direct script access is not allowed!');
require_once BASE.'/app/model/base/BaseDAO.php';


class UserDAO extends BaseDAO {

	public function __construct() {
		parent::__construct('users');
	}

	// admin panel security -> Reports access
	// see admin-reports.html
	public function checkLogin_reports($pass) {

		// get password
		$sql = "SELECT password FROM users WHERE user_id=:user_id";
		$stmt = $this->prepareExecute($sql, array('user_id' => $_SESSION['user']['id']) );
		$data = $stmt->fetch(PDO::FETCH_ASSOC);
		 
		// check password
		$sql = "SELECT user_id FROM users WHERE user_id = :user_id AND password = :password AND user_type='admin' and superadmin=1 LIMIT 1";
		$stmt = $this->prepareExecute($sql, array('user_id' => $_SESSION['user']['id'], 'password' => $this->_generateHash($pass, substr($data['password'], 0, SALT_LENGTH))));

		if ($stmt) {
			return $stmt->fetchAll();
		}
	}

	// XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX  BROKERS FUNCTIONS XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX

	// ref: new password functionality - brokers (assumes that email is a unique field)
	public function checkUsers($email) {
		
		// get user_id
		$sql = "SELECT user_id FROM users WHERE email= :email LIMIT 1";
		$stmt = $this->prepareExecute($sql, array('email' => trim($email)));
		$data = $stmt->fetch(PDO::FETCH_ASSOC);
		return $data['user_id'];
	}

	// ref: new password functionality - brokers (assumes that email is a unique field)
	public function get_email($id) {

		// get email
		$sql = "SELECT email FROM users WHERE user_id=:id";
		$stmt = $this->prepareExecute($sql, array('id' => $id) );
		$data = $stmt->fetch(PDO::FETCH_ASSOC);
		return $data['email'];
	}

	// ref: new password functionality - brokers (assumes that link is a unique field)
	public function remove_new_password_link($link) {

		$d = array('link' => $link);
		$sql = "DELETE from new_password_link  WHERE link = :link";
		$this->prepareExecute($sql, $d);
	}

	// ref: new password functionality - brokers (assumes that link is a unique field)
	public function add_new_password_link($link) {

		$data = array(
			'link' => trim($link)			
		);
			
		$sql = "INSERT INTO new_password_link (link) VALUES(:link)";
		$this->prepareExecute($sql, $data);
	}

	// ref: new password functionality - brokers (assumes that link is a unique field)
	public function check_new_password_link($link) {

		// get link
		$sql = "SELECT link FROM new_password_link WHERE link=:link";
		$stmt = $this->prepareExecute($sql, array('link' => $link) );
		$data = $stmt->fetch(PDO::FETCH_ASSOC);
		return $data['link'];
	}

	// XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX


	public function checkLogin($email, $pass) {

		// get password
		$sql = "SELECT password FROM users WHERE email=:email";
		$stmt = $this->prepareExecute($sql, array('email' => $email) );
		$data = $stmt->fetch(PDO::FETCH_ASSOC);
		 
		// check password
		$sql = "SELECT user_id,broker,name,user_type,enabled,superadmin FROM users WHERE email = :email AND password = :password  LIMIT 1";
		$stmt = $this->prepareExecute($sql, array('email' => $email, 'password' => $this->_generateHash($pass, substr($data['password'], 0, SALT_LENGTH))));

		if ($stmt) {
			return $stmt->fetchAll();
		}
	}


	// called by controller Account for client login
	public function loginUser($p) {

		// get password
		$sql = "SELECT password, enabled FROM users_clients WHERE email=:email and enabled=:enabled";
		$stmt = $this->prepareExecute($sql, array('email' => $p['email'],'enabled' => '1'));
		$data = $stmt->fetch(PDO::FETCH_ASSOC);

		if(!$data) return false;
		 
		// check password
		$sql = "SELECT user_id FROM users_clients WHERE email = :email AND password = :password AND user_type='user' LIMIT 1";
		$stmt = $this->prepareExecute($sql, array('email' => $p['email'], 'password' => $this->_generateHash($p['password'], substr($data['password'], 0, SALT_LENGTH))));

		if ($stmt) {
			$i =  $stmt->fetch(PDO::FETCH_ASSOC);
			return $i['user_id'];
		}
		return false;
	}

	public function suspendUser($email) {

		$d = array('enabled' => '0', 'email' => $email);
		$sql = "UPDATE users_clients SET enabled = :enabled WHERE email = :email";
		$this->prepareExecute($sql, $d);
	}
	
	// revoke a user (called in cron job: admin-users.php)
    public function suspendUser_admin($email) {

		$d = array('enabled' => '0', 'email' => $email);
		$sql = "UPDATE users SET enabled = :enabled WHERE email = :email";
		$this->prepareExecute($sql, $d);
	}

	public function getAllUsers() {
		$sql = "SELECT user_id, name, email,superadmin,broker, DATE_FORMAT( FROM_UNIXTIME( TIMESTAMP ) ,  '%d/%m/%Y' ) AS signup_date, user_type, enabled FROM users ORDER BY user_id";
		$stmt = $this->prepareExecute($sql, array() );
		if ($stmt) {
			return $stmt->fetchAll();
		}
	}

	public function getAllUsers_clients() {
		$sql = "SELECT user_id, name, email, DATE_FORMAT( FROM_UNIXTIME( TIMESTAMP ) ,  '%d/%m/%Y' ) AS signup_date, user_type, enabled FROM users_clients ORDER BY user_id";
		$stmt = $this->prepareExecute($sql, array() );
		if ($stmt) {
			return $stmt->fetchAll();
		}
	}


	// ZZZZZZZZZZZZZZZZZZZZZZ ALL NEW FUNCTIONS  ZZZZZZZZZZZZZZZZZZZZZZZZZZZ

	// delete an existing user (users_clients)
	public function deleteUser_users_clients($email) {

		$email = trim($email);
		if(!$this->_check_users_clients($email)){
			echo "User '".$email."' does not exist !"."\n";
			return false;
		}

		$d = array('email' => $email);
		$sql = "DELETE from users_clients WHERE email = :email";
		$this->prepareExecute($sql, $d);
		echo "User '".$email."' DELETED"."\n";
		return true;
	}

	// delete an existing user
	public function deleteUser($email) {

		$email = trim($email);
		if(!$this->_check_user($email)){
			echo "User '".$email."' does not exist !"."\n";
			return false;
		}

		$d = array('email' => $email);
		$sql = "DELETE from users WHERE email = :email";
		$this->prepareExecute($sql, $d);
		echo "User '".$email."' DELETED"."\n";
		return true;
	}
	
	// delete a user (called in cron job: admin-users.php)
	public function deleteUser_admin($email) {
		
		$d = array('email' => $email);
		$sql = "DELETE from users WHERE email = :email";
		$this->prepareExecute($sql, $d);
		return true;
	}

	// create a new user (users_clients)
	public function addUser_users_clients($id,$name,$email,$password) {

		if($this->_check_users_clients($email)){
			echo "User '".$email."' already exists !"."\n";
			return false;
		}

		$data = array(
		    'user_id' => trim($id),
			'name' => trim($name),
			'email' => trim($email),
			'password' => $this->_generateHash($password, false),
			'timestamp' => time(),
		    'user_type' => 'user',
		    'enabled' => '1'
		    );
		    	
		    $sql = "INSERT INTO users_clients (user_id,name,email,password,timestamp,user_type,enabled) VALUES(:user_id, :name, :email, :password, :timestamp, :user_type, :enabled)";
		    $this->prepareExecute($sql, $data);
		    echo "User '".$name."' ADDED"."\n";
		    return true;
	}

	// create a new user (called in cron job: admin-users.php)
	public function addUser($name,$email,$password) {

		if($this->_check_user($email)){
			echo "User '".$email."' already exists !"."\n";
			return false;
		}

		$data = array(
			'name' => trim($name),
			'email' => trim($email),
			'password' => $this->_generateHash($password, false),
			'timestamp' => time(),
		    'user_type' => 'admin',
		    'enabled' => '1'
		    );
		    	
		    $sql = "INSERT INTO users (name,email,password,timestamp,user_type,enabled) VALUES(:name, :email, :password, :timestamp, :user_type, :enabled)";
		    $this->prepareExecute($sql, $data);
		    echo "User '".$name."' ADDED"."\n";
		    return true;
	}
	
	// create a new user (called in ubc admin panel, AdminUsers.php)
	public function addUser_admin($data) {
		
		$sql = "INSERT INTO users (name,email,password,timestamp,user_type,enabled,broker,superadmin) VALUES(:name, :email, :password, :timestamp, :user_type, :enabled, :broker, :superadmin)";
		$this->prepareExecute($sql, $data);
	}

	// update a password for an existing user (users_clients)
	public function update_password_users_clients($email,$password){

		$email = trim($email);
		if(!$this->_check_users_clients($email)){
			echo "User '".$email."' does not exist !"."\n";
			return false;
		}

		$d = array('email' => $email, 'password' => $this->_generateHash($password, false),'enabled' => '1');
		$sql = "UPDATE users_clients SET password = :password, enabled = :enabled WHERE email = :email";
		$this->prepareExecute($sql, $d);
		echo "User '".$email."' UPDATED"."\n";
		return true;
	}

	// update a password for an existing user
	public function update_password($email,$password){

		$email = trim($email);
		if(!$this->_check_user($email)){
			echo "User '".$email."' does not exist !"."\n";
			return false;
		}

		$d = array('email' => $email, 'password' => $this->_generateHash($password, false));
		$sql = "UPDATE users SET password = :password WHERE email = :email";
		$this->prepareExecute($sql, $d);
		echo "User '".$email."' UPDATED"."\n";
		return true;
	}

	public function create_password($length=0) {

		if(!$length){ $length = PASSWORD_LENGTH;}
		 
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		return substr(str_shuffle($chars),0,$length);

	}

	// adds a user generated password to history table
	public function add_to_password_history($email,$password) {

		$data = array(
			'email' => trim($email),
			'password' => $this->_generateHash($password, false),
			'timestamp' => time()
		);
			
		$sql = "INSERT INTO users_password_history (email,password,timestamp) VALUES(:email, :password, :timestamp)";
		$this->prepareExecute($sql, $data);
	}

	// returns last 3 user generated passwords for checking
	// to prevent re-usage of most recent passwords
	public function check_password_history($email,$password) {

		$sql = "SELECT password FROM users_password_history WHERE email = :email order by timestamp DESC LIMIT 0,3";
		$stmt = $this->prepareExecute($sql, array('email' => trim($email)));
		if ($stmt) {
			 
			$found=false;
			foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $p) {

				$hash = $this->_generateHash($password, substr($p['password'], 0, SALT_LENGTH));

				if($p['password'] == $hash){
					$found=true;
					break;
				}
			}

			return $found;
			 
		}else{
			return false;
		}

	}

	// called in the admin panel- Risk Scoring Matrix
	public function get_user_name($id) {
		 
		$sql = "SELECT email,superadmin,name FROM users WHERE user_id = :id LIMIT 1";
		$stmt = $this->prepareExecute($sql, array('id' => $id));
		if ($stmt) {
			return $stmt->fetch(PDO::FETCH_ASSOC);
		}else{
			return false;
		}

	}

	// called in AdminApplication.php to activate a new client login
	public function get_business_regno($id) {
		 
		$sql = "SELECT business_regno,business_name FROM applications WHERE application_id = :id LIMIT 1";
		$stmt = $this->prepareExecute($sql, array('id' => $id));
		if ($stmt) {
			return $stmt->fetch(PDO::FETCH_ASSOC);
		}else{
			return false;
		}

	}

	// called in AdminApplication.php to activate a new client login
	public function update_client($business_regno,$id) {

		// set user_id = business_regno for this application_id in table applications

		$d = array('regno' => $business_regno, 'id' => $id);
		$sql = "UPDATE applications SET user_id = :regno WHERE application_id = :id and status=5";
		$this->prepareExecute($sql, $d);
	}

	// called in AdminApplication.php to activate a new client login
	public function check_users_clients($user_id) {
		 
		$sql = "SELECT user_id FROM users_clients WHERE user_id = :user_id LIMIT 1";
		$stmt = $this->prepareExecute($sql, array('user_id' => $user_id));
		if ($stmt) {
			if($stmt->fetch(PDO::FETCH_ASSOC)){
				return true;
			}else{
				return false;
			}
				
		}
		return false;
	}


	// zzzzzzzzzzzzzzzzzzzzzz   Security functions used by Applicationform::check_ip_address()  zzzzzzzzzzzzzzzzz

	public function check_IP_whitelist() {
		 
		$sql = "select ip from application_ip_whitelist where ip = :ip";
		$stmt = $this->prepareExecute($sql, array('ip' => trim($_SERVER['REMOTE_ADDR'])));
		if ($stmt) {
			return $stmt->fetch(PDO::FETCH_ASSOC);
		}

		return false;
	}

	public function check_IP() {
		 
		$sql = "select ip, count from application_ip where ip = :ip";
		$stmt = $this->prepareExecute($sql, array('ip' => trim($_SERVER['REMOTE_ADDR'])));
		if ($stmt) {
			return $stmt->fetch(PDO::FETCH_ASSOC);
		}

		return false;
	}

	public function insert_IP() {
		 
		$data = array(':ip' => $_SERVER['REMOTE_ADDR'],
		              ':count' => 1,
		              ':status' => 0);
			
		$sql = "INSERT INTO application_ip (ip, count, status, timestamp) VALUES (:ip, :count, :status, NOW())";
		return $this->prepareExecute($sql, $data);
		 
	}

	public function update_IP_status($ip_cur) {
		 
		$d = array(':status' => 1,
		           ':ip' => $ip_cur);
		$sql = "UPDATE application_ip SET status = :status WHERE ip=:ip";
		return $this->prepareExecute($sql, $d);
		 
	}

	public function update_IP_count($ip_cur,$count_cur) {
		 
		$d = array(':count' => ++$count_cur,
		           ':ip' => $ip_cur);
		$sql = "UPDATE application_ip SET count = :count WHERE ip=:ip";
		return $this->prepareExecute($sql, $d);
		 
	}

	// zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz


	// PRIVATE FUNCTIONS

	// (users_clients)
	private function _check_users_clients($email) {
		 
		$sql = "SELECT user_id FROM users_clients WHERE email = :email LIMIT 1";
		$stmt = $this->prepareExecute($sql, array('email' => trim($email)));
		if ($stmt) {
			if($stmt->fetch(PDO::FETCH_ASSOC)){
				return true;
			}else{
				return false;
			}
				
		}
		return false;
	}

	public function _check_user($email) {
		 
		$sql = "SELECT user_id FROM users WHERE email = :email LIMIT 1";
		$stmt = $this->prepareExecute($sql, array('email' => trim($email)));
		if ($stmt) {
			if($stmt->fetch(PDO::FETCH_ASSOC)){
				return true;
			}else{
				return false;
			}
				
		}
		return false;
	}


	// PASSWORD management with salting
	public function _generateHash($plainText, $salt){
		 
		if ($salt === FALSE)
		{
			$salt = substr(md5(uniqid(rand(), true)), 0, SALT_LENGTH);
		}
		else
		{
			$salt = substr($salt, 0, SALT_LENGTH);
		}

		return $salt . sha1($salt . $plainText);
	}

	public function fetchBrokerSlug($broker_id) {

		$sql = "select value FROM settings_broker WHERE setting='url_slug' AND broker_id = :broker_id";
		$stmt = $this->prepareExecute($sql, array('broker_id' => $broker_id));
		if ($stmt) {
			return $stmt->fetch(PDO::FETCH_ASSOC);
		}

		return false;
	}
	
	//called in ubc admin panel, AdminUsers.php 
	// ONLY DELETES RECORD IF USER IS A BROKER
	public function removeBrokerCompany($email) {
		
		$user_id = $this->checkUsers($email);
		$d = array('user_id' => $user_id);
		
		$sql = "DELETE from broker_company_users  WHERE user_id = :user_id";
		$this->prepareExecute($sql, $d);
	}
	
	//called in ubc admin panel, AdminUsers.php
	public function getIntroducerCompany($id) {
		
		$data = array('id' => $id);
		$sql = "SELECT * FROM broker_company where id = :id LIMIT 1";
		$stmt = $this->prepareExecute($sql, $data);
	    $data = $stmt->fetch(PDO::FETCH_ASSOC);
		
        if($data){
        	return $data;
        }else{
        	return false;
        }
	}
	
	//called in ubc admin panel, AdminUsers.php
	public function addIntroducerCompany($data) {
			
		$sql = "INSERT INTO broker_company (name,url,details,pcon,address,bankname,banknumber,banksort) VALUES(:name,:url,:details,:pcon,:address,:bankname,:banknumber,:banksort)";
		$this->prepareExecute($sql, $data);
	}
	
	//called in ubc admin panel, AdminUsers.php
	public function updateIntroducerCompany($data) {

		$sql = "UPDATE broker_company SET 
		
		        name = :name,
		        url = :url,
		        details = :details,
		        pcon = :pcon,
		        address = :address,
		        bankname = :bankname,
    	        banknumber = :banknumber,
		        banksort = :banksort
		        
		        WHERE id = :id";
		
		
		$this->prepareExecute($sql, $data);
	}
	
	//called in ubc admin panel, AdminUsers.php
	public function addBrokerCompany($id) {
		
		// get last user_id
		$sql = "SELECT MAX(user_id) as id FROM users";
		$stmt = $this->prepareExecute($sql, null);
		$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
		
		$data = array(
		   'id' => $id,
		   'user_id' => $res[0]['id']
		);
		
		$sql = "INSERT INTO broker_company_users (id,user_id) VALUES(:id,:user_id)";
		$this->prepareExecute($sql, $data);
	}
	
	//called in ubc admin panel, AdminUsers.php
	public function fetchBrokerCompanies() {
		
		$sql = "SELECT id, name FROM broker_company order by name";
			$stmt = $this->prepareExecute($sql, null);
			$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
			
			$brokerCompanies= array();
			
	        if($data){
	        	foreach($data as $p) {
	        		$brokerCompanies[] = $p;
	        	}
	        	return $brokerCompanies;
	        }else{
	        	return false;
	        }
	}
	
	//called in ubc admin panel, AdminUsers.php
	public function setBrokerdefaults($data) {
		    	
		    $sql = "INSERT INTO `settings_broker` (`broker_id`, `setting`, `value`, `display`) VALUES(:broker_id, :setting, :value, :display)";
		    $this->prepareExecute($sql, $data);		
	}
	
	//called in ubc admin panel, AdminUsers.php
	public function removeBrokersettings($email) {
		
		$user_id = $this->checkUsers($email);
		
		$d = array('id' => $user_id);
		$sql = "DELETE from `settings_broker` WHERE broker_id = :id";
		$this->prepareExecute($sql, $d);
	}
	
    //called in ubc admin panel, AdminUsers.php
	public function removeIntroducerCompany($id) {
		
		$d = array('id' => $id);
		$sql = "DELETE from `broker_company` WHERE id = :id";
		$this->prepareExecute($sql, $d);
	}
	
	 // called in ubc admin panel, AdminUsers.php
	 // removes ALL brokers associated with a company
     public function updateBrokerCompany($id) {
		
		$d = array('id' => $id);	
		$sql = "DELETE from broker_company_users  WHERE id = :id";
		$this->prepareExecute($sql, $d);
	}
	
	 // called in ubc admin panel, AdminUsers.php
	 // gets ALL broker ids associated with a company
     public function getBrokersForCompany($id) {

     	$sql = "SELECT user_id FROM broker_company_users WHERE id=:id";
		$stmt = $this->prepareExecute($sql, array('id' => $id));   	
        if ($stmt) {
			return $stmt->fetchAll();
		}
		return null;
     }
     
     // called in ubc admin panel, Admin.html
	 // gets ALL company names associated with a broker
     public function getCompaniesForBroker($user_id) {

     	$sql = "SELECT id FROM broker_company_users WHERE user_id=:user_id";
		$stmt = $this->prepareExecute($sql, array('user_id' => $user_id));   	
        if ($stmt) {
        	$str="";
			$ids = $stmt->fetchAll();
			//print_r($ids);die;			
            foreach($ids as $id){
			     $temp = $this->getIntroducerCompany($id['id']);		    
			     $str .= $temp['name']."<br />";		    			    		    		
		    }
		    return $str;
		}
		return null;
     }
     
     // called in ubc admin panel, AdminUsers.php
	 // gets company count for one company
     public function getCompanyCount($id) {

     	$sql = "SELECT * FROM applications WHERE business_regno=:id";
		$stmt = $this->prepareExecute($sql, array('id' => $id));   	
        if ($stmt) {
			$rows = $stmt->fetchAll();
            return count($rows);
		}
		return 0;
     }
     
      // called in view admin-application.html
      // alerts user that a record is being viewed
      public function check_record_view_status($appid,$userCur) {
      	
        $sql = "SELECT user_id FROM record_view_status WHERE appid = :id and user_id <> :userCur LIMIT 1";
		$stmt = $this->prepareExecute($sql, array('id' => $appid, 'userCur' => $userCur));
		if ($stmt) {
			return $stmt->fetch(PDO::FETCH_ASSOC);
		}else{
			return false;
		}
      }
      
      // called in view admin-application.html
      // deletes YOUR lock, if any
      public function delete_record_view_status($user_id) {
      	
      	$d = array('user_id' => $user_id);
		$sql = "DELETE from record_view_status WHERE user_id = :user_id";
		$this->prepareExecute($sql, $d);
      	
      }
      
      // called in view admin-application.html
      // adds a "viewed" record to table record_view_status
      public function update_record_view_status($appid,$user_id) {
      	
      	$this->delete_record_view_status($user_id);
		
		// create new record
      	$data = array(
			'user_id' => $user_id,			
			'appid' => $appid
		);
			
		$sql = "INSERT INTO record_view_status (user_id,appid) VALUES(:user_id, :appid)";
		$this->prepareExecute($sql, $data); 
      }
      
      // called in logout
      // empty table record_view_status
      public function empty_view_status() {
      	
      	$sql = "DELETE from record_view_status";
		$this->prepareExecute($sql, null);
      }
      

}