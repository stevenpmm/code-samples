<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Ajax extends CI_Controller {

  public function __construct()
	{
		parent::__construct();
		$this->load->model('Logging2');
		$this->results = array();
		$this->productsArray = array();
		
		$this->display_fields = array();  // for view list
		
		$this->criteria = ""; // human readable criteria
	  	
	  	$this->display_fields['companyname']='Company Name';
	  	$this->display_fields['companyid']='Company ID';
	  	$this->display_fields['city']='Town / City';
	  	$this->display_fields['address1']='Address';
	  	$this->display_fields['postcode']='Postcode';
	  	$this->display_fields['firstname']='First Name';
	  	$this->display_fields['surname']='Surname';
	  	$this->display_fields['position']='Position';
	  	$this->display_fields['region']='Region';
	  	$this->display_fields['telephoneno']='Telephone';
	  	$this->display_fields['companyemail']='Company Email';
	  	$this->display_fields['addresstypes']='Address Types';
	  	$this->display_fields['markets']='Markets';
	  	
	  	// sectors arrays
		$this->sectors_sbd = array('Home Improvements','Extensions','Roofing','Driveways','Renovation','New Build','Specialist Services','Solar Panels');
		
		$this->sectors_nrg = array('Solar Thermal','Solar PV','Wind Turbines','Air Source Heat Pumps','Ground Source Heat Pumps','Hydro Power','Combined Heat and Power','Biomass','Anaerobic Digestion','Heat Recovery Units');
		
		$this->sectors_asd = array('Public Sector' => array('Government/Local Authority','Defence','Civic/Community'),
		                     'Commercial' => array('Offices','Industrial Units/Buildings'),
		 					 'Education' => array('Schools & Colleges','Student Accomodation','Universities'),
							 'Retail' => array('Shops/Shopfronts','Supermarkets/Superstores','Shopping Centres/Retail Parks'),
							 'Leisure' => array('Hotels','Conference Centres','Restaurants/Bars','Culture/Entertaiment'),
		 					 'Health' => array('Hospitals','Care Homes/Hospices','Health Centres/Surgeries'),
							 'Housing' => array('Private Housing','Social Housing','Commercial Housing'));
		
		$this->sectors_mcd = array('Public Sector' => array('Government/Local Authority','Defence','Civic/Community'),
		                     'Commercial' => array('Offices','Industrial Units/Buildings'),
		 					 'Education' => array('Schools & Colleges','Student Accomodation','Universities'),
							 'Retail' => array('Shops/Shopfronts','Supermarkets/Superstores','Shopping Centres/Retail Parks'),
		                     'Leisure' => array('Hotels','Conference Centres','Restaurants/Bars','Culture/Entertaiment'),
		                     'Health' => array('Hospitals','Care Homes/Hospices','Health Centres/Surgeries'),
							 'Housing' => array('Private Housing','Social Housing','Commercial Housing'),
							 'Civil' => array('Ground Works/Civil Projects','Utilities/Infrastructure'),
							 'Restoration/Refurbishment' => array('Restoration/Refurbishment'));
		
		//$this->regex_postcode = "/^([A-PR-UWYZ0-9][A-HK-Y0-9][AEHMNPRTVXY0-9]?[ABEHMNPRVWXY0-9]? {1,2}[0-9][ABD-HJLN-UW-Z]{2}|GIR 0AA)$/";
		$this->regex_postcode = "/^(GIR|[A-Z]\d[A-Z\d]??|[A-Z]{2}\d[A-Z\d]??)([ ]\d[A-Z]{2})??$/";
		
		$this->load->helper('url');
        $this->load->library('pagination');
        $this->load->model('Viewlist');
        
        require_once('utilities.php'); // have databases been hacked ?	
	}

  // checks for pcode changes with developer tools !	
  private function _check_region_hacks(){
		 
		$str = $this->session->userdata('postcodes');
		 
		if($this->input->post('allregions')){
	
			foreach ($this->input->post('allregions') as $pcode) {
	
				if(!in_array($pcode, explode(',',$str))){
					return false;
				}
			}
		}
		 
		return true;
	}	

  public function process_lb_data($event_flag="",$criteria="",$offset="")
  {
  	
  	 //print_r($this->input->post('allregions'));
  	//print_r($this->session->all_userdata());
  	//echo $this->session->userdata('postcodes');
  	//print_r($this->input->post()); die;
  	
  	// capture product permissions
  	$tokensArray = explode(',',$this->session->userdata('products')); 
  	
  	// security checks
  	// checks for region postcodes hacks with developer tools !
  	if(!$this->_check_region_hacks()){
  		echo "Error: Incorrect regions.\nPlease go back and change your criteria.";
  		return;
  	}
  	
  	// checks for db hacks with developer tools !
  	if($this->input->post('dsn')){
  		if(!Utilities::check_db_hack($this->input->post('dsn'),$this->session->userdata('databases'))){
  			echo "Error: Incorrect input.\nPlease go back and change your criteria.";
  			return;
  		}
  	}
  	
  	
  	// validate ONLY input fields
  	if($this->input->post('postcode')){
	  	if(!preg_match($this->regex_postcode, strtoupper($this->input->post('postcode')))){
	  	   echo "Error: incorrect Postcode entered";	
	  	   return;
	  	}
	  	// check permissions
	  	$temparray = array();
    	$temparray = explode(',',$this->session->userdata('postcodes'));
    	$temppcstore = substr(strtoupper($this->input->post('postcode')),0,2);
    	$temppcstore2 = "";
    	$found=false;
  	    foreach ($temparray as $pcode_def) {
  	    	
  	           if(strlen($pcode_def) == 1)	{
        	   	  $temppcstore2 = substr(strtoupper($this->input->post('postcode')),0,1);
        	   }
         
			   if($pcode_def == $temppcstore || $pcode_def == $temppcstore2){	
			   	  $found=true; 
			   	  break;
			   }
			   
        	}
  	
  	     if(!$found) {
           echo "Error: the entered post code is outside of your subscription or invalid entry";	
	  	   return;
          }	
  	}
  	
  	// check default postcode subscriptions
  
  	// DEFAULT SQL IF NO PAGE ACTIVITY ...
  	
  	// process main products, if any
  	$filters = $this->input->post('filters');   
  	
  	//print_r($filters);die;
  	
  	// check filters for content
  	$filters_count=0;
  	$filters_flag=false;
  	foreach ($filters as $val) {
  		if($val){
  			$filters_flag=true;
  			$filters_count++;
  		}
  	}
  	
  	// limit selections
  	/*
  	if($filters_count > 4) {
  		echo "Error: only FOUR product selections can be made.";
  		return;
  	}
  	*/
  	
  	// process submitted post criteria
  	$sql="";
  	$ifd_roi_flag=false;
  	$this->criteria .= '<h3>Product/Sector Information:</h3>'; // capture selected criteria
  	if($filters_flag){
  		
  		// process main products tokens ONCE
  		$tempstr="";
  		foreach ($tokensArray as $val) {
  			if($val == 'wd:f' || $val == 'wd:b')
  				$tempstr.= "'".$val."',";
  		}
  		$wd_sql=" AND type IN (".rtrim($tempstr,',').")";
  			
  		//if($val == 'filter-windoors-other'){// && $this->input->post('OTHER_choices')){ 
  		if(in_array('filter-windoors-other',$filters)){	 
  				
  				/*
				Other materials/hybrids:
				filters[] -> filter-windoors-other   				
				OTHER_choices => other_w
				wd_act_other[] // method
				wd_fpw_other[] // volume
				material = Other Materials
				
				exclude_fabricate
				exclude_buyin
				  	*/
  				
  				$sql="";
  				$ifd_roi_flag=true;
  				
  			//	$this->criteria .= '<br /><h3>Main Products - Other materials/hybrids</h3><br />'; // capture selected criteria
  				$this->criteria .= '<h4>Main Products - Other materials/hybrids</h4>'; // capture selected criteria
  				
  				// sub menu OTHER
  			//	if($this->input->post('OTHER_choices') == 'all'){
  					$sql.=" description = 'Composite Windows' ";  // ONLY ONE ITEM HERE
  					
  					$this->criteria .= 'Composite Windows<br />'; // capture selected criteria
  			//	}

  				// append products tokens 					
  				$sql.=$wd_sql;
  				
  				// methods (activity)
  				if($this->input->post('wd_act_other')){
	  				$tempstr="";
		  			foreach ($this->input->post('wd_act_other') as $val) {
		  				 if($val == 'buy-In') $tempstr.= "'Buy In',"; // 2 entries here
				    	 $tempstr.= "'".$val."',";
				     }
				    if($tempstr){ 
				      $sql.=" AND method IN (".rtrim($tempstr,',').")";
				    }
				    $this->criteria .= 'Activity: '.rtrim($tempstr,',').'<br />'; // capture selected criteria
				    
				    // (with exclusions, if any)
				    if($this->input->post('exclude_fabricate')){ 
				       $sql.=" AND companyid NOT IN (select distinct companyid from products where method IN ('fabricate'))";  
				       $this->criteria .= 'Exclude Fabricate<br />'; // capture selected criteria
				    }
  				    if($this->input->post('exclude_buyin')){ 
				       $sql.=" AND companyid NOT IN (select distinct companyid from products where method IN ('Buy In','Buy-In'))";
				       $this->criteria .= 'Exclude Buy In<br />'; // capture selected criteria
				    }
  				} 
  		    
			    // volume (Frames per week)
  				if($this->input->post('wd_fpw_other')){
	  				$tempstr="";
		  			foreach ($this->input->post('wd_fpw_other') as $val) {
				    	 $tempstr.= "'".$val."',";
				     }
				    $sql.=" AND companyid in (select distinct companyid from products p where description='Frames per week'"; 
				    $sql.=" AND volume IN (".rtrim($tempstr,',')."))";
				    $this->criteria .= 'Frames per week: '.rtrim($tempstr,',').'<br />'; // capture selected criteria
  				}
			   
			    // material
			    $sql.=" AND material = 'Other Materials' ";
			    $this->criteria .= 'Material: Other Materials<br />'; // capture selected criteria
			    
			    // store sql
			    $this->productsArray['products'][] = $sql; 
			    
  				
  //	echo $sql;			
  				
  			}		
  			
  		//if($val == 'filter-windoors-tim'){// && $this->input->post('TIM_choices')){  
  		if(in_array('filter-windoors-tim',$filters)){
  				
  				/*
				timber:
				filters[] -> filter-windoors-tim   				
				TIM_choices => tim_w;tim_v
				wd_act_tim[]   // method
				wd_fpw_tim[] // volume
				material = timber
				
				exclude_fabricate
				exclude_buyin
				  	*/
  				
  				$sql="";
  				$ifd_roi_flag=true;
  			//	$this->criteria .= '<br /><h3>Main Products - Timber</h3><br />'; // capture selected criteria
  				$this->criteria .= '<h4>Main Products - Timber</h4>'; // capture selected criteria
  				
  				// sub menu Timber
  				if($this->input->post('TIM_choices') == 'all'){
  					$sql.=" description IN ('Timber Sash Windows','Timber Windows/Doors') ";
  					$this->criteria .= 'Timber Sash Windows,Timber Windows/Doors<br />'; // capture selected criteria
  				}
  			    else if($this->input->post('TIM_choices') == 'tim_w'){
  					$sql.=" description = 'Timber Windows/Doors' ";
  					$this->criteria .= 'Timber Windows/Doors<br />'; // capture selected criteria
  				}
  			    else if($this->input->post('TIM_choices') == 'tim_v'){
  					$sql.=" description = 'Timber Sash Windows' ";
  					$this->criteria .= 'Timber Sash Windows<br />'; // capture selected criteria
  				}
  				else{
  					$sql.=" description IN ('Timber Sash Windows','Timber Windows/Doors') "; // defaults to all
  					$this->criteria .= 'Timber Sash Windows,Timber Windows/Doors<br />'; // capture selected criteria
  				}		
  				
  				// append products tokens
  				$sql.=$wd_sql;
  				
  				// methods (activity)
  				if($this->input->post('wd_act_tim')){
	  				$tempstr="";
		  			foreach ($this->input->post('wd_act_tim') as $val) {
		  				 if($val == 'buy-In') $tempstr.= "'Buy In',"; // 2 entries here
				    	 $tempstr.= "'".$val."',";
				     }
				    if($tempstr){ 
				       $sql.=" AND method IN (".rtrim($tempstr,',').")";
				    }
				    $this->criteria .= 'Activity: '.rtrim($tempstr,',').'<br />'; // capture selected criteria
				     
				    
				    // (with exclusions, if any)
				    if($this->input->post('exclude_fabricate')){ 
				       $sql.=" AND companyid NOT IN (select distinct companyid from products where method IN ('fabricate'))"; 
				       $this->criteria .= 'Exclude Fabricate<br />'; // capture selected criteria
				    }
  				    if($this->input->post('exclude_buyin')){ 
				       $sql.=" AND companyid NOT IN (select distinct companyid from products where method IN ('Buy In','Buy-In'))";
				       $this->criteria .= 'Exclude Buy In<br />'; // capture selected criteria
				    }
  				} 
  		    
			    // volume (Frames per week)
  				if($this->input->post('wd_fpw_tim')){
	  				$tempstr="";
		  			foreach ($this->input->post('wd_fpw_tim') as $val) {
				    	 $tempstr.= "'".$val."',";
				     }
				    $sql.=" AND companyid in (select distinct companyid from products p where description='Frames per week'"; 
				    $sql.=" AND volume IN (".rtrim($tempstr,',')."))";
				    $this->criteria .= 'Frames per week: '.rtrim($tempstr,',').'<br />'; // capture selected criteria
  				}
			   
			    // material
			    $sql.=" AND material = 'Other Materials' ";
			    $this->criteria .= 'Material: Timber<br />'; // capture selected criteria
			    
			    // store sql
			    $this->productsArray['products'][] = $sql; 
  				
  //	echo $sql;			
  				
  			}	
  			
  		//  if($val == 'filter-windoors-al'){// && $this->input->post('ALUM_choices')){
  		if(in_array('filter-windoors-al',$filters)){
  				
  				/*
				aluminium:
				filters[] -> filter-windoors-al    				
				ALUM_choices => alum_w;alum_b;alum_c
				wd_act_al[]   // method
				con_profile_al[] // system
				wd_fpw_al[] // volume
				material = aluminium
				
				exclude_fabricate
				exclude_buyin
				  	*/
  				
  				$sql="";
  				$ifd_roi_flag=true;
  			//	$this->criteria .= '<br /><h3>Main Products - Aluminium</h3><br />'; // capture selected criteria
  				$this->criteria .= '<h4>Main Products - Aluminium</h4>'; // capture selected criteria
  				
  				// sub menu PVCu
  				if($this->input->post('ALUM_choices') == 'all'){
  					$sql.=" description IN ('Windows and Doors','Bi-Fold Doors','Specialist') ";
  					$this->criteria .= 'Windows and Doors,Bi-Fold Doors,Specialist<br />'; // capture selected criteria
  				}
  			    else if($this->input->post('ALUM_choices') == 'alum_w'){
  					$sql.=" description = 'Windows and Doors' ";
  					$this->criteria .= 'Windows and Doors<br />'; // capture selected criteria
  				}
  			    else if($this->input->post('ALUM_choices') == 'alum_b'){
  					$sql.=" description = 'Bi-Fold Doors' ";
  					$this->criteria .= 'Bi-Fold Doors<br />'; // capture selected criteria
  				}
  				else if($this->input->post('ALUM_choices') == 'alum_c'){
  					$sql.=" description = 'Specialist' ";
  					$this->criteria .= 'Specialist<br />'; // capture selected criteria
  				}
  				else{
  					$sql.=" description IN ('Windows and Doors','Bi-Fold Doors','Specialist') "; // defaults to all
  					$this->criteria .= 'Windows and Doors,Bi-Fold Doors,Specialist<br />'; // capture selected criteria
  				}

  				// append products tokens
  				$sql.=$wd_sql;
  				
  				// methods (activity)
  				if($this->input->post('wd_act_al')){
	  				$tempstr="";
		  			foreach ($this->input->post('wd_act_al') as $val) {
		  				 if($val == 'buy-In') $tempstr.= "'Buy In',"; // 2 entries here
				    	 $tempstr.= "'".$val."',";
				     }
				    if($tempstr){ 
				       $sql.=" AND method IN (".rtrim($tempstr,',').")";
				    }
				    $this->criteria .= 'Activity: '.rtrim($tempstr,',').'<br />'; // capture selected criteria
				     
				    
				    // (with exclusions, if any)
				    if($this->input->post('exclude_fabricate')){ 
				       $sql.=" AND companyid NOT IN (select distinct companyid from products where method IN ('fabricate'))";  
				       $this->criteria .= 'Exclude Fabricate<br />'; // capture selected criteria
				    }
  				    if($this->input->post('exclude_buyin')){ 
				       $sql.=" AND companyid NOT IN (select distinct companyid from products where method IN ('Buy In','Buy-In'))";
				       $this->criteria .= 'Exclude Buy In<br />'; // capture selected criteria
				    }
  				} 
			     
			    // system
  				if($this->input->post('con_profile_al')){
	  				$tempstr="";
		  			foreach ($this->input->post('con_profile_al') as $val) {
				    	 $tempstr.= "'".$val."',";
				     }
				    
				    // system exclusions OR
				    if($this->input->post('con_profile_list_radio_al')){
				    	//$sql.=" AND system NOT IN (".rtrim($tempstr,',').")";
				    	$sql.=" AND companyid NOT IN (select distinct p.companyid from products p where system in  (".rtrim($tempstr,',')."))";
				    	$this->criteria .= 'Profile(s) excluded: '.rtrim($tempstr,',').'<br />'; // capture selected criteria
				    }else{ // system inclusions
				    	$sql.=" AND system IN (".rtrim($tempstr,',').")";
				    	$this->criteria .= 'Profile(s): '.rtrim($tempstr,',').'<br />'; // capture selected criteria
				    }
				    
  				}else{
  					$this->criteria .= 'Profile(s): All<br />'; // capture selected criteria
  				}
  				
  		    
			    // volume (Frames per week)
  				if($this->input->post('wd_fpw_al')){
	  				$tempstr="";
		  			foreach ($this->input->post('wd_fpw_al') as $val) {
				    	 $tempstr.= "'".$val."',";
				     }
				    $sql.=" AND companyid in (select distinct companyid from products p where description='Frames per week'"; 
				    $sql.=" AND volume IN (".rtrim($tempstr,',')."))";
				    $this->criteria .= 'Frames per week: '.rtrim($tempstr,',').'<br />'; // capture selected criteria
  				}
			   
			    // material
			    $sql.=" AND material = 'aluminium' ";
			    $this->criteria .= 'Material: Aluminium<br />'; // capture selected criteria
			    
			    // store sql
			    $this->productsArray['products'][] = $sql; 
  				
  //	echo $sql;			
  				
  			}
  			
  			//if($val == 'filter-windoors'){// && $this->input->post('PVCu_choices')){
  			if(in_array('filter-windoors',$filters)){
  				
  				/*
				PVCu:
				filters[] -> filter-windoors    				
				PVCu_choices => pvcu_w;pvcu_v;pvcu_b // description  Windows and Doors;Vertical Sliders;Bi-Fold Doors 
				wd_act[]   // method
				con_profile[] // system
				wd_fpw[] // volume
				material = PVCu
				
				exclude_fabricate
				exclude_buyin
				  	*/
  				
  				$sql="";
  				$ifd_roi_flag=true;
  			//	$this->criteria .= '<br /><h3>Main Products - PVCu</h3><br />'; // capture selected criteria
  				$this->criteria .= '<h4>Main Products - PVCu</h4>'; // capture selected criteria
  				
  				// sub menu PVCu
  				if($this->input->post('PVCu_choices') == 'all'){
  					$sql.=" description IN ('Windows and Doors','Vertical Sliders','Bi-Fold Doors') ";
  					$this->criteria .= 'Windows and Doors,Bi-Fold Doors,Vertical Sliders<br />'; // capture selected criteria
  				}
  			    else if($this->input->post('PVCu_choices') == 'pvcu_w'){
  					$sql.=" description = 'Windows and Doors' ";
  					$this->criteria .= 'Windows and Doors<br />'; // capture selected criteria
  				}
  			    else if($this->input->post('PVCu_choices') == 'pvcu_v'){
  					$sql.=" description = 'Vertical Sliders' ";
  					$this->criteria .= 'Vertical Sliders<br />'; // capture selected criteria
  				}
  				else if($this->input->post('PVCu_choices') == 'pvcu_b'){
  					$sql.=" description = 'Bi-Fold Doors' ";
  					$this->criteria .= 'Bi-Fold Doors<br />'; // capture selected criteria
  				}
  				else{
  					$sql.=" description IN ('Windows and Doors','Vertical Sliders','Bi-Fold Doors') "; // defaults to all
  					$this->criteria .= 'Windows and Doors,Bi-Fold Doors,Vertical Sliders<br />'; // capture selected criteria
  				}

  				// append products tokens
  				$sql.=$wd_sql;
  				
  				// methods (activity)
  				if($this->input->post('wd_act')){				
	  				$tempstr="";
		  			foreach ($this->input->post('wd_act') as $val) {
		  				 if($val == 'buy-In') $tempstr.= "'Buy In',"; // 2 entries here
				    	 $tempstr.= "'".$val."',";
				     }
				    if($tempstr){ 
				      $sql.=" AND method IN (".rtrim($tempstr,',').")";
				    }
				    $this->criteria .= 'Activity: '.rtrim($tempstr,',').'<br />'; // capture selected criteria
				     
				    
				    // (with exclusions, if any)
				    if($this->input->post('exclude_fabricate')){ 
				       $sql.=" AND companyid NOT IN (select distinct companyid from products where method IN ('fabricate'))";  
				       $this->criteria .= 'Exclude Fabricate<br />'; // capture selected criteria
				    }
  				    if($this->input->post('exclude_buyin')){ 
				       $sql.=" AND companyid NOT IN (select distinct companyid from products where method IN ('Buy In','Buy-In'))";
				       $this->criteria .= 'Exclude Buy In<br />'; // capture selected criteria
				    }
  				} 
		     
			    // system
  				if($this->input->post('con_profile')){
	  				$tempstr="";
		  			foreach ($this->input->post('con_profile') as $val) {
				    	 $tempstr.= "'".$val."',";
				     }
				     
				     // system exclusions OR 
				     if($this->input->post('con_profile_list_radio')){
				     	//$sql.=" AND system NOT IN (".rtrim($tempstr,',').")";
				     	$sql.=" AND companyid NOT IN (select distinct p.companyid from products p where system in  (".rtrim($tempstr,',')."))";
				     	$this->criteria .= 'Profile(s) excluded: '.rtrim($tempstr,',').'<br />'; // capture selected criteria
				     }else{ // system inclusions
				     	$sql.=" AND system IN (".rtrim($tempstr,',').")";
				     	$this->criteria .= 'Profile(s): '.rtrim($tempstr,',').'<br />'; // capture selected criteria
				     }			   
  				}else{
  					$this->criteria .= 'Profile(s): All<br />'; // capture selected criteria
  				}
  		    
			    // volume (Frames per week)
  				if($this->input->post('wd_fpw')){
	  				$tempstr="";
		  			foreach ($this->input->post('wd_fpw') as $val) {
				    	 $tempstr.= "'".$val."',";
				     }
				    $sql.=" AND companyid in (select distinct companyid from products p where description='Frames per week'"; 
				    $sql.=" AND volume IN (".rtrim($tempstr,',')."))";
				    $this->criteria .= 'Frames per week: '.rtrim($tempstr,',').'<br />'; // capture selected criteria
  				}
			   
			    // material
			    $sql.=" AND material = 'PVCu' ";
			    $this->criteria .= 'Material: PVCu<br />'; // capture selected criteria
			    
			    // store sql
			    $this->productsArray['products'][] = $sql; 
  				
  //	echo $sql;			
  				
  			}
  			
  		    //if($val == 'filter-cons-roofs'){
  		    if(in_array('filter-cons-roofs',$filters)){
  				
  		    	/*
  		    	 Conservatory roofs:
				filters[] -> filter-cons-roofs
				con_act[]
				con_alu[]
				con_rpm[]
				exclude_fabricate
				exclude_buyin
  		    	*/
  		    	
  		    		$sql="";
  		    		$ifd_roi_flag=true;
  		    	//	$this->criteria .= '<br /><h3>Specialist Products - Conservatory Roofs</h3><br />'; // capture selected criteria
  		    		$this->criteria .= '<h4>Specialist Products - Conservatory Roofs</h4>'; // capture selected criteria
  		    		//$sql.=" type like 'cr%' ";
  		    		
  		    	// process products tokens		    				
  		    	$tempstr="";
  		    	foreach ($tokensArray as $val) {    			
  		    		if($val == 'cr:f' || $val == 'cr:b')				
  		    			$tempstr.= "'".$val."',";
  		    	} 		    	
  		    	$sql.=" type IN (".rtrim($tempstr,',').")";
  			
  		    	// methods (activity)
  				if($this->input->post('con_act')){
	  				$tempstr="";
		  			foreach ($this->input->post('con_act') as $val) {
		  				 if($val == 'buy-In') $tempstr.= "'Buy In',"; // 2 entries here
		  				 
		  				 $tempstr.= "'".$val."',";
				     }
				    if($tempstr){ 
				       $sql.=" AND method IN (".rtrim($tempstr,',').")";
				    }
				    $this->criteria .= 'Activity: '.rtrim($tempstr,',').'<br />'; // capture selected criteria
				    
				    // (with exclusions, if any)
				    if($this->input->post('exclude_fabricate')){ 
				       $sql.=" AND companyid NOT IN (select distinct companyid from products where method IN ('fabricate'))";  
				       $this->criteria .= 'Exclude Fabricate<br />'; // capture selected criteria
				    }
  				    if($this->input->post('exclude_buyin')){ 
  				       $sql.=" AND companyid NOT IN (select distinct companyid from products where method IN ('Buy In','Buy-In'))"; 
  				       $this->criteria .= 'Exclude Buy In<br />'; // capture selected criteria
				    }
  				} 
			     
			    // system
  				if($this->input->post('con_alu')){
	  				$tempstr="";
		  			foreach ($this->input->post('con_alu') as $val) {
				    	 $tempstr.= "'".$val."',";
				     }
				     
				     // system exclusions OR
				     if($this->input->post('con_alu_list_radio')){
				     	//$sql.=" AND system NOT IN (".rtrim($tempstr,',').")";
				     	$sql.=" AND companyid NOT IN (select distinct p.companyid from products p where system in  (".rtrim($tempstr,',')."))";
				     	$this->criteria .= 'Profile(s) excluded: '.rtrim($tempstr,',').'<br />'; // capture selected criteria
				     }else{ // system inclusions
				     	$sql.=" AND system IN (".rtrim($tempstr,',').")";
				     	$this->criteria .= 'Profile(s): '.rtrim($tempstr,',').'<br />'; // capture selected criteria
				     }
				     
  				}else{
  					$this->criteria .= 'Profile(s): All<br />'; // capture selected criteria
  				}
			    
			    // volume (Roofs per month)
  				if($this->input->post('con_rpm')){
	  				$tempstr="";
		  			foreach ($this->input->post('con_rpm') as $val) {
				    	 $tempstr.= "'".$val."',";
				     }
				    $sql.=" AND companyid in (select distinct companyid from products p where description='Roofs per month'";  
				    $sql.=" AND volume IN (".rtrim($tempstr,',')."))";
				    $this->criteria .= 'Roofs per month: '.rtrim($tempstr,',').'<br />'; // capture selected criteria
  				}
			       
			    // store sql
			    $this->productsArray['products'][] = $sql; 
  				
  			}
  			
  		    //if($val == 'filter-com-doors'){
  		    if(in_array('filter-com-doors',$filters)){
  				
  				/*
  				 Composite doors:
				filters[] -> filter-com-doors
				com_act[]
				con_slab[]
				com_dpw[]
  				 */
  		    	
  		    		$sql="";
  		    		$ifd_roi_flag=true;
  		    	//	$this->criteria .= '<br /><h3>Specialist Products - Composite Doors</h3><br />'; // capture selected criteria
  		    		$this->criteria .= '<h4>Specialist Products - Composite Doors</h4>'; // capture selected criteria
  		    		
  		        // description  		    	
  				// $sql.=" type like 'cd%' ";

  		    		// process products tokens
  		    		$tempstr="";
  		    		foreach ($tokensArray as $val) {
  		    			if($val == 'cd:f' || $val == 'cd:b')
  		    				$tempstr.= "'".$val."',";
  		    		}
  		    		$sql.=" type IN (".rtrim($tempstr,',').")";
  		    	
  		    	// methods (activity)  ? Part fabricate (prepped slabs) ?
  				if($this->input->post('com_act')){
	  				$tempstr="";
		  			foreach ($this->input->post('com_act') as $val) {
		  				 if($val == 'buy-In') $tempstr.= "'Buy In',"; // 2 entries here
				    	 $tempstr.= "'".$val."',";
				     }
				    if($tempstr){ 
				      $sql.=" AND method IN (".rtrim($tempstr,',').")";
				    }
				    $this->criteria .= 'Activity: '.rtrim($tempstr,',').'<br />'; // capture selected criteria
  				} 
			     
			    // system(s)
  				$tempstr="";
  				if($this->input->post('con_slab')){	  				
		  			foreach ($this->input->post('con_slab') as $val) {   // WILL NOT FIND - Full fabricate (blank slabs) 
				    	 $tempstr.= "'".$val."',";
				     }
  				}
  				
  				if($this->input->post('con_slab2')){
  					foreach ($this->input->post('con_slab2') as $val) {   // WILL NOT FIND - Full fabricate (blank slabs)
  						$tempstr.= "'".$val."',";
  					}
  				}
  				
  				if($tempstr){
	  				
  					// system exclusions OR
  					if($this->input->post('con_slab_list_radio')){
  						//$sql.=" AND system NOT IN (".rtrim($tempstr,',').")";
  						$sql.=" AND companyid NOT IN (select distinct p.companyid from products p where system in  (".rtrim($tempstr,',')."))";
  						$this->criteria .= 'Profile(s) excluded: '.rtrim($tempstr,',').'<br />'; // capture selected criteria
  					}else{ // system inclusions
  						$sql.=" AND system IN (".rtrim($tempstr,',').")";
  						$this->criteria .= 'Profile(s): '.rtrim($tempstr,',').'<br />'; // capture selected criteria
  					}
  					
  				}else{
  					$this->criteria .= 'Profile(s): All<br />'; // capture selected criteria
  				}
			    
			    // volume (Doors per week)
  				if($this->input->post('com_dpw')){
	  				$tempstr="";
		  			foreach ($this->input->post('com_dpw') as $val) {
				    	 $tempstr.= "'".$val."',";
				     }
				    $sql.=" AND companyid in (select distinct companyid from products p where description='Doors per week'"; 
				    $sql.=" AND volume IN (".rtrim($tempstr,',')."))";
				    $this->criteria .= 'Doors per week: '.rtrim($tempstr,',').'<br />'; // capture selected criteria
  				}
			       
			    // store sql
			    $this->productsArray['products'][] = $sql; 
  				
  			}
  			
  		    //if($val == 'filter-sealed-units'){
  		    if(in_array('filter-sealed-units',$filters)){
  				
  				/*
  				 Sealed units:
				filters[] -> filter-sealed-units
				su_act[]
				su_upw[]
  				 */
  		    	
  		    		$sql="";
  		    		$ifd_roi_flag=true;
  		    	//	$this->criteria .= '<br /><h3>Specialist Products - Sealed Units</h3><br />'; // capture selected criteria
  		    		$this->criteria .= '<h4>Specialist Products - Sealed Units</h4>'; // capture selected criteria
  		    		
  		    	// description  		    		
  				//$sql.=" description = 'Sealed Units' and type like 'su%' ";
  				
  		    		// process products tokens
  		    		$tempstr="";
  		    		foreach ($tokensArray as $val) {
  		    			if($val == 'su:m' || $val == 'su:b')
  		    				$tempstr.= "'".$val."',";
  		    		}
  		    		$sql.=" type IN (".rtrim($tempstr,',').")";
  		    	
  		    	// methods (activity)  
  				if($this->input->post('su_act')){
	  				$tempstr="";
		  			foreach ($this->input->post('su_act') as $val) {
		  				 if($val == 'buy-In') $tempstr.= "'Buy In',"; // 2 entries here
				    	 $tempstr.= "'".$val."',";
				     }
				    $sql.=" AND method IN (".rtrim($tempstr,',').")";
				    $this->criteria .= 'Activity: '.rtrim($tempstr,',').'<br />'; // capture selected criteria
  				}
  				
  		        // volume (Units per week)  
  				if($this->input->post('su_upw')){
	  				$tempstr="";
		  			foreach ($this->input->post('su_upw') as $val) {
				    	 $tempstr.= "'".$val."',";
				     }
				    $sql.=" AND companyid in (select distinct companyid from products p where ";  
				    $sql.=" volume IN (".rtrim($tempstr,',')."))";
				    $this->criteria .= 'Units per week: '.rtrim($tempstr,',').'<br />'; // capture selected criteria
  				}

  				// store sql
			    $this->productsArray['products'][] = $sql; 
  				
  			}
  			
  		     //if($val == 'filter-roofline'){
  		     if(in_array('filter-roofline',$filters)){
  				
  				/*
  				 Roofline:
				filters[] -> filter-roofline
				rl_act[]
				con_sup_brand[]
  				 */
  		    	
  		    		$sql="";
  		    		$ifd_roi_flag=true;
  		    	//	$this->criteria .= '<br /><h3>Specialist Products - Roofline</h3><br />'; // capture selected criteria
  		    		$this->criteria .= '<h4>Specialist Products - Roofline</h4>'; // capture selected criteria
  		    		
  		    	// description  		    	
  				$sql.=" type = 'rl:i' ";	
  		    	
  		    	// methods (activity)  
  				if($this->input->post('rl_act')){
	  				$tempstr="";
		  			foreach ($this->input->post('rl_act') as $val) {
		  				 if($val == 'buy-In') $tempstr.= "'Buy In',"; // 2 entries here
				    	 $tempstr.= "'".$val."',";
				     }
				    $sql.=" AND method IN (".rtrim($tempstr,',').")";
				    $this->criteria .= 'Activity: '.rtrim($tempstr,',').'<br />'; // capture selected criteria
  				}
  				
  		        // system
  				if($this->input->post('con_sup_brand')){
	  				$tempstr="";
		  			foreach ($this->input->post('con_sup_brand') as $val) {
				    	 $tempstr.= "'".$val."',";
				     }
				    
				     // system exclusions OR
				     if($this->input->post('con_sup_brand_list_radio')){
				     	//$sql.=" AND system NOT IN (".rtrim($tempstr,',').")";
				     	$sql.=" AND companyid NOT IN (select distinct p.companyid from products p where system in  (".rtrim($tempstr,',')."))";
				     	$this->criteria .= 'Profile(s) excluded: '.rtrim($tempstr,',').'<br />'; // capture selected criteria
				     }else{ // system inclusions
				     	$sql.=" AND system IN (".rtrim($tempstr,',').")";
				     	$this->criteria .= 'Profile(s): '.rtrim($tempstr,',').'<br />'; // capture selected criteria
				     }
				     
  				}else{
  					$this->criteria .= 'Profile(s): All<br />'; // capture selected criteria
  				}

  				// store sql
			    $this->productsArray['products'][] = $sql; 
  				
  			}
  		
  	} // end of filters if
  	
  	if(!$ifd_roi_flag && ($this->input->post('dsn') == 'ifd' || $this->input->post('dsn') == 'roi')){
  		$this->criteria .= 'All Fenestration Products ('.strtoupper($this->input->post('dsn')).')<br />'; // capture selected criteria
  	}
  	
  	// process installer options
  	$installer_option = $this->input->post('installer-dialogue-options');
  	$dsn_cur = $this->input->post('dsn');
 
  	if($installer_option == 1){	
  		$this->productsArray['installers'] =" and t.companyid in (select distinct companyid from products where origin_dsn = '".$dsn_cur."' AND service = 'Installer of Windows,Doors or Roofs')"; // include
  	}
  	
  	if($installer_option == 2){
  		$this->productsArray['installers'] =" and t.companyid in (select distinct companyid from products where origin_dsn = '".$dsn_cur."' AND service <> 'Installer of Windows,Doors or Roofs')"; // exclude
  	}
  	// end process installer options
  	
  	// print_r($this->input->post());die;
  	
  	// process sectors
  	
    // asd 
  	$asd_flag=false;
  	if($this->input->post('asd')){    // $this->sectors_asd
  		$asd_flag=true;
  		$tempstr="";
  		$tempstr2="";
  		foreach ($this->input->post('asd') as $val) {		  				
	    	 $tempstr.= "'sector:".$val."',";
	    	 
	    	 $keys = array_keys($this->sectors_asd); // get array keys
	    	 $g = substr($val, 0, 1);
	    	 $c = substr($val, 1, 1);  	 
	    	 $tempstr2.= $keys[$g-1].':'.$this->sectors_asd[$keys[$g-1]][$c-1].'<br />';
	    	 
	     }
	    $sql.=" type IN (".rtrim($tempstr,',').")";
	    $sql.=" AND system = '".$this->input->post('asd_category')."' ";
	    $this->productsArray['products'][] = $sql;
	    
	 //   $this->criteria .= '<h3>Architects & Specifiers - Sectors</h3><br />'; // capture selected criteria
	    $this->criteria .= '<h4>Architects & Specifiers - Sectors</h4>'; // capture selected criteria
	    $this->criteria .= 'Category: '.$this->input->post('asd_category').'<br />'; // capture selected criteria
	    $this->criteria .= $tempstr2; // capture selected criteria
  	}
  	
  	if(!$asd_flag && $this->input->post('dsn') == 'asd'){
  	//	$this->criteria .= '<h3>Architects & Specifiers - Sectors</h3><br />'; // capture selected criteria
  		$this->criteria .= '<h4>Architects & Specifiers - Sectors</h4>'; // capture selected criteria
  		$this->criteria .= 'All Architects & Specifiers Sectors<br />'; // capture selected criteria
  	}
  	
  	// mcd
  	$mcd_flag=false;
    if($this->input->post('mcd')){    // $this->sectors_mcd
    	$mcd_flag=true;
  		$tempstr="";
  		$tempstr2="";
  		foreach ($this->input->post('mcd') as $val) {		  				
	    	 $tempstr.= "'sector:".$val."',";

	    	 $keys = array_keys($this->sectors_mcd); // get array keys
	    	 $g = substr($val, 0, 1);
	    	 $c = substr($val, 1, 1);
	    	 $tempstr2.= $keys[$g-1].':'.$this->sectors_mcd[$keys[$g-1]][$c-1].'<br />';
	     }
	    $sql.=" type IN (".rtrim($tempstr,',').")";
	    $this->productsArray['products'][] = $sql;
	    
	  //  $this->criteria .= '<h3>Construction file - Sectors</h3><br />'; // capture selected criteria
	    $this->criteria .= '<h4>Construction file - Sectors</h4>'; // capture selected criteria
	    $this->criteria .= $tempstr2; // capture selected criteria
  	}
  	
  	if(!$mcd_flag && $this->input->post('dsn') == 'mcd'){
  	//	$this->criteria .= '<h3>Construction file - Sectors</h3><br />'; // capture selected criteria
  		$this->criteria .= '<h4>Construction file - Sectors</h4>'; // capture selected criteria
  		$this->criteria .= 'All Construction file Sectors<br />'; // capture selected criteria
  	}
  	
    // sbd  (regex search)   // $this->sectors_sbd
  	$sbd_flag=false;
    if($this->input->post('sbd')){
    	$sbd_flag=true;
  		$tempstr="";
  		$tempstr2="";
  		foreach ($this->input->post('sbd') as $val) {		  				
	    	 $tempstr.= $val."|";
	    	 $tempstr2.= $this->sectors_sbd[$val-1].'<br />';
	     }
	    $sql.=" type regexp '(".rtrim($tempstr,'|').")'";
	    $this->productsArray['products'][] = $sql;
	    
	  //  $this->criteria .= '<h3>Local Builders - Sectors</h3><br />'; // capture selected criteria
	    $this->criteria .= '<h4>Local Builders - Sectors</h4>'; // capture selected criteria
	    $this->criteria .= $tempstr2; // capture selected criteria
  	}
  	
  	if(!$sbd_flag && $this->input->post('dsn') == 'sbd'){
  	 //	$this->criteria .= '<h3>Local Builders - Sectors</h3><br />'; // capture selected criteria
  		$this->criteria .= '<h4>Local Builders - Sectors</h4>'; // capture selected criteria
  		$this->criteria .= 'All Local Builders Sectors<br />'; // capture selected criteria
  	}
  	
    // nrg (regex search)   // $this->sectors_nrg
  	$nrg_flag=false;
    if($this->input->post('nrg')){
    	$nrg_flag=true;
  		$tempstr="";
  		$tempstr2="";
  		foreach ($this->input->post('nrg') as $val) {		  				
	    	 $tempstr.= $val."|";
	    	 $tempstr2.= $this->sectors_nrg[$val-1].'<br />';
	     }
	    $sql.=" type regexp '(".rtrim($tempstr,'|').")'";
	    $this->productsArray['products'][] = $sql;
	    
	 //   $this->criteria .= '<h3>Renewable energy installers - Sectors</h3><br />'; // capture selected criteria
	    $this->criteria .= '<h4>Renewable energy installers - Sectors</h4>'; // capture selected criteria
	    $this->criteria .= $tempstr2; // capture selected criteria
  	}
  	
  	if(!$nrg_flag && $this->input->post('dsn') == 'nrg'){
  	//	$this->criteria .= '<h3>Renewable energy installers - Sectors</h3><br />'; // capture selected criteria
  		$this->criteria .= '<h4>Renewable energy installers - Sectors</h4>'; // capture selected criteria
  		$this->criteria .= 'All Renewable energy installers Sectors<br />'; // capture selected criteria
  	}

  //	$this->criteria .= '<br /><hr><br /><h3>REGION INFORMATION:</h3><br />'; // capture selected criteria
  	$this->criteria .= '<hr /><h3>Region Information:</h3>'; // capture selected criteria
  	
  	// process postcodes (postcode, distance > 0)
    if($this->input->post('postcode') && $this->input->post('distance') > 0){
	  	 $this->productsArray['postcode'] = $this->input->post('postcode');
	  	 $this->productsArray['distance'] = $this->input->post('distance');
	  	 
	  	 $this->criteria .= 'Postcode: '.$this->input->post('postcode').'<br />'; // capture selected criteria
	  	 $this->criteria .= 'Distance: '.$this->input->post('distance').' miles<br />'; // capture selected criteria
	}else{
  	
	  	// process regions
	    if($this->input->post('allregions')){
	  		$tempstr="";
	  		$tempstr2="";
	  		foreach ($this->input->post('allregions') as $val) {		  				
		    	 $tempstr = "'".$val."'";
		    	 $tempstr2 .= "'".$val."',";
		    //	 $this->productsArray['regions'][] =  $tempstr;
		    	 $this->productsArray['regions'][] =  $val;
		     }
		     //$this->criteria .= 'Regions: '.wordwrap(rtrim($tempstr2,','), 50, "<br />", true).'<br /><br />'; // capture selected criteria 
		     $this->criteria .= 'Regions: '.wordwrap(rtrim($tempstr2,','), 50, "<br />", true).'<br />'; // capture selected criteria
	  	}else{
	  		$this->criteria .= 'Regions: all regions<br />'; // capture selected criteria
	  	}
	}
  	  	  	
  	// process globals (markets, address type)
  	if($this->input->post('con_mar') || $this->input->post('con_add')){
  	  // $this->criteria .= '<h3>GLOBAL SELECTIONS:</h3><br />'; // capture selected criteria
  	   $this->criteria .= '<hr /><h3>Global Selections:</h3>'; // capture selected criteria
  	}
  	
  	if($this->input->post('con_mar')){ 
  	    $markets = array_unique($this->input->post('con_mar'));
  	    $this->productsArray['markets'] = $markets; 
  	    
  	    $tempstr2="";
  	    foreach($markets as $market){
  	    	$tempstr2 .= $market.',';
  	    }
  	 //   $this->criteria .= 'Markets: '.rtrim($tempstr2,',').'<br /><br />'; // capture selected criteria
  	    $this->criteria .= 'Markets: '.rtrim($tempstr2,',').'<br />'; // capture selected criteria
  	}
  	
  	if($this->input->post('con_add')){
	  	$addresstypes = array_unique($this->input->post('con_add'));
	  	$this->productsArray['addresstypes'] = $addresstypes;
	  	
	  	$tempstr2="";
	  	foreach($addresstypes as $market){
	  		$tempstr2 .= $market.',';
	  	}
	  	$this->criteria .= 'Address types: '.rtrim($tempstr2,',').'<br />'; // capture selected criteria
  	}
  	
  	$this->criteria .= '<br />';
  	
  	// process page 3 button choices eg $this->productsArray['excludes'] for Export list to a standard CSV file
  	
  	// set default products for ifd / roi if none selected ...
  	if(!isset($this->productsArray['products']) && !is_numeric($tokensArray[0])){
	  	$tempstr="";
	  	foreach ($tokensArray as $val) {
	  			$tempstr.= "'".$val."',";
	  	}
	  	$this->productsArray['products'][] = " type IN (".rtrim($tempstr,',').")";
  	}
  	
  	// get data
    $this->results = $this->Search2->get_lb_data($this->input->post('dsn'), $this->productsArray, $event_flag);
    $this->session->set_userdata('search_dsn', $this->input->post('dsn'));
    
    // error checking ??
    
    //echo is_numeric($tokensArray[0]);die;
   
    // determine return content
    
    // echo $this->results;die;
    
    //echo $this->results['sql'];die;
    
    // print_r($this->input->post());die;
    
    // update counter
    if($event_flag == 1){
       if($this->results['rowCount']=="" || $this->results['rowCount']==0){ 
	       $this->results['rowCount']=0;
	    } 	   
       echo $this->results['rowCount'];	 // update count
    }
    
    if($event_flag == 2){ 
    	
        if(!$this->results){
	   	  	echo "Error: No records found.\nPlease go back and change your criteria.";
	   	  	return;
	   	 }   	
    	
        $this->export_to_csv();	
    }
    
    if($event_flag == 3){  

        if(!$this->results){
	   	  	echo "Error: No records found.\nPlease go back and change your criteria.";
	   	  	return;
	   	 }
    	
    	$this->export_to_xml();	
    }
   
     // save records
    if($event_flag == 4){ 
    	
    	    if(!$this->results){
		   	  	echo "Error: No records found.\nPlease go back and change your criteria.";
		   	  	return;
	   	     }

	   	    // emulate event_flag == 5
	   	     $ids = $this->get_list_ids();
	   	     $this->Search2->store_sql_temp($this->results,$ids,serialize(addslashes($this->criteria)));
 
			// set pagination		
			$config['base_url'] = base_url('index.php/ajax/process_lb_data'); // index.php/search/index
			$this->result =  $this->results = $this->Search2->get_lb_data($this->input->post('dsn'), $this->productsArray, $event_flag); // get total records
			$config['total_rows'] = $this->result['rowCount'];
			$config['per_page'] = 100;	
			$config['num_links'] =  $config['total_rows'] / $config['per_page'];
			$config['uri_segment'] = 5;
			$config['cur_tag_open'] = '&nbsp;<b class="active-page">';
			$config['cur_tag_close'] = '</b>';	
			$config['full_tag_open'] = '<span class="pag-num">Page: ';
			$config['full_tag_close'] = '</span>';
			$this->pagination->initialize($config); 
			
	    	if($this->uri->segment(5)){
				$offset=$this->uri->segment(5);
			}
			else{
				$offset=0;
			}
			
			// store offset
			//$this->session->set_userdata('offset',$offset); 
			
			// update display
		    $offset_d = $offset+1;
		    $cur_total = $config['per_page']+$offset; 
			if( $config['per_page']+$offset > $config['total_rows'] ){
			  	$cur_total = $config['total_rows'];
			 }
	        $display_message = "Showing records ".$offset_d." to ".$cur_total." (of ".$config['total_rows'].")";
	       		
			$this->results = $this->Search2->get_lb_data($this->input->post('dsn'), $this->productsArray, $event_flag, $offset, $config['per_page']);
			
		//print_r($this->results['rawData']);die;
			
	    	$temp = $this->display_records($criteria,$display_message);
	    	
	    	$temp.= $this->result['rowCount'];
	    	
	    	echo $temp;
	    	//echo '#'.$this->results['rowCount'];	 // update final count
     }
    
     // store last record in temp table
     if($event_flag == 5){
     	
	    if(!$this->results['rawData']){
	   	  	echo "Error: No records found.\nPlease go back and change your criteria.";
	   	  	return;
	   	 }
	   	 
	   	//echo $this->criteria;
	   	//return;
	   	 
        $ids = $this->get_list_ids();
    	$this->Search2->store_sql_temp($this->results,$ids,serialize(addslashes($this->criteria)));
    	echo $this->results['rowCount'];	 // update final count
     }
     
     if($event_flag == 6){ 
     	
        if(!$this->results){
	   	  	echo "Error: No records found.\nPlease go back and change your criteria.";
	   	  	return;
	   	 }
     	
        //$this->export_to_csv();
	   	 $this->export_to_xls();	 // Was CSV
    }
     
    //print_r($this->results['rawData']);
    
    // print_r($this->input->post());
    
  } 
  
  
  // XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX  // end of function process_lb_data()  XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX	
  
   // push list to another user
   public function pushUserList($names) {
   	
	   	$str="";
	      	
   		if(!$this->Search2->pushUserList(rtrim($names,'-'))){
   		   $str = 'Error: the list cannot be pushed';	
   		}
   		else{
   		   $str = 'The list has been pushed';	
   		}
	   	  	
	   	echo $str;   	
   }
  
   // get list of users for this client
   public function getUserList($flag=false,$selected=false) {
   
   	$str="";
   	$result = $this->Search2->getUserList('current');
   
   	if($result){
   			
   		foreach ($result as $item){
   			if($flag){
   				$temp="";
   				if($selected == $item->userid) {$temp = "selected='selected'";}
   				$str .= '<option value ="'.$item->userid.'" '.$temp.'>'.$item->firstname.' '.$item->surname.'</option>';
   			}else{
   				$str .= '<input type="checkbox" name="pushusers[]" class="pushusers" value="'.$item->userid.'" />'.$item->firstname.' '.$item->surname.'<br />';
   			}
   		}
   
   	}
   	else{
   		$str='Error: no users available';
   	}
   		
   	echo trim($str);
   
   }
  
  // checks file uploads are associated with a saved list
  public function check_list_name() {
  	 echo $this->Search2->check_list_name();
  }
	
  // store SQL in table savedlists 
  public function store_sql($lname)
  {
  	 echo $this->Search2->store_sql($lname);
  	 //Log saved list
  	 $this->Logging2->log_activity($code = '25', $note = $lname);
  } 
 
  // called by ajax in function display_records()
  public function delete_tagged_records($list="",$count="") 
  {
  	if(!empty($list)){
		if(!$this->Search2->delete_tagged_records($list,$count)){
			echo 'Error: list not updated with tagged records (DB error)';
		} else{
			echo $count.' Tagged record(s) deleted';
			//Log tagged records removed
			$this->Logging2->log_activity($code = '29', $note = $this->session->userdata('savedlist_id') . ":" . trim($list, "-") . ":" . $this->session->userdata('search_dsn'));
				
		}
  	}else{
  		echo 'Error: no records selected for deletion';  
  	}		
  }

  // capture user permissions
  public function set_default_display($dsn)
  {
  	echo $this->Search2->get_product_permissions($dsn);
  } 

  // generate list of profiles (ref 'system')
  public function create_profiles_list($dsn, $criteria, $class){
  	
  	$str="<ul>";
  	$list = array();	
  	$list = $this->Search2->get_profiles_list($dsn, $criteria);
  	
    if(empty($list)){
  	   echo "No profiles data available";	
  	   return;
  	}
  	
  	foreach ($list as $ls) {
  		$str.='<li><input type="checkbox" name="'.$class.'[]" class="'.$class.'" value="'.$ls.'" />'.$ls.'<li>';
  	}
  	
  	$str.="</ul>";
  	 	
  	echo $str;
  	
  	//print_r($list);
  }
  
  // generate list of composite doors profiles (ref 'system')
  // two lists built
  public function create_profiles_list_cd($dsn, $criteria, $class){
  	 
  	$str="<ul>";
  	$list = array();
  	
  	$list = $this->Search2->get_profiles_list($dsn, 'cd_list1');
  	 
  	if(empty($list)){
  		echo "No profiles data available (list: Slab Supplier)";
  		return;
  	}
  	 
  	foreach ($list as $ls) {
  		$str.='<li><input type="checkbox" name="'.$class.'[]" class="'.$class.'" value="'.$ls.'" />'.$ls.'<li>';
  	}
  	
  	$str.="</ul>";
  	
  	$str.='<br /><h3 id="con_slab_list2">Complete Door Supplier</h3>';
  	
  	$list2 = array();
  	$list2 = $this->Search2->get_profiles_list($dsn, 'cd_list2');
  	
  	if(empty($list2)){
  		echo "No profiles data available (list: Complete Door Supplier)";
  		return;
  	}
  	
  	$str.= "<script type='text/javascript'>";
  	$str.= "$('#con_slab_list2').click(function() {
				$('.con_slab2').attr('checked', !$('.con_slab2').is(':checked')); 
			});";
  	$str.= "</script>";
  	
  	$str.="<ul>";
  	
  	$class .= '2';
  	foreach ($list2 as $ls) {
  		   $str.='<li><input type="checkbox" name="'.$class.'[]" class="'.$class.'" value="'.$ls.'" />'.$ls.'<li>';
  	}
  	 
  	$str.="</ul>";
  		
  	echo $str;
  	 
  	//print_r($list);
  }
  
  // creates regions list on page 2 
  public function create_regions_list($dsn){
  	
  	$str="";
  	$str_codes="";
  	$lists = array();	
  	$lists = $this->Search2->get_regions_list($dsn,$pcodes_flag);
  	
  	if(empty($lists)){
  	   echo "No post code data available";	
  	   return;
  	}
  	
  	//print_r($lists);
  	//print_r($lists);die;
 	
  	// create list
  	if($dsn == 'roi'){
  		$str.= '<ul class="postcodes">'; 
  		foreach ($lists as $list) {
  			$str.= '<li><input class="allregions" type="checkbox" name="allregions[]"  value="'.$list.'"  />'.$list.'</li>';
  			$str_codes .= $list.',';
  		}
  		$str.= '</ul><br /><div class="clear">&nbsp;</div>';
  	}else{
  		
	  	$keys = array_keys($lists);
	  	foreach ($keys as $key) {
	  		
	  	  $key2 = str_ireplace(" ", "_", $key);	
	  		
	  	  $pcvalue = "'".$key2."'";	
	  		
	  	 // $str.='<div id="region-header" class="'.$key2.'"><h4>'.$key.'</h4> <input type="checkbox" class="allregions" id="'.$key2.'-all" name="'.$key2.'-all"  onChange="select_postcodes('.$pcvalue.')" /></div>';     
	  	  
	  	  $str.='<div id="region-header" class="'.$key2.'_border"><h3>'.$key.'</h3> <input type="checkbox" class="allheaders allregions" id="'.$key2.'-all" name="'.$key2.'-all"  onChange="select_postcodes('.$pcvalue.')" /></div>';     
	  	  
	      $str.= '<ul class="postcodes">'; 
	      
	      //Checkbox counter
	      $inc = 0;
	      
	  	  foreach ($lists[$key] as $code){
	  	  	    // $str.= '<li><input class="'.$key2.' allregions" type="checkbox" name="'.$key2.'[]"  value="'.$code.'" checked="checked" />'.$code.'</li>';	
	  	  	    $str.= '<li><input id="'.$key2.'_'.$inc.'" class="'.$key2.' allregions" type="checkbox" name="allregions[]" onChange="remove_allboxes('."'".trim($pcvalue, "'").'-'.$key2.'_'.$inc."'".')" value="'.$code.'"  />'.$code.'</li>';
	  	  	    $str_codes .= $code.',';
	  	  	    $inc++;
	          }
	      
	      $str.= '</ul><br /><div class="clear">&nbsp;</div>';
	    }
	  
    }

    // store postcodes in session var
    $this->session->set_userdata('postcodes',rtrim($str_codes,','));
    
    echo $str.'#'.$pcodes_flag;
  }
  
   // PRIVATE FUNCTIONS
   
   // capture list ids
   private function get_list_ids() {
   	
   	  $str="";  	 
   	  foreach ($this->results['rawData'] as $item){ 
   	  	  $str.= $item->companyid.',';
   	  }
   	  return rtrim($str);
   } 
                
   public function display_records($criteria="", $display="", $external_data=false, $listid=false, $history=false)
	  {
	  	
	    // check for external data source
	    if($external_data) { $rawdata = $external_data; } else { $rawdata = $this->results['rawData'];}
  
	    // sanity check
	    if(!$rawdata){
	    	echo "No records found";
	    	return;
	    }
	    
	    // check for external listid  // $this->session->set_userdata('savedlist', '1');$this->session->set_userdata('savedlist_id', $listid);
	    if($listid) { $external_listid = $listid; } 
	    else { $external_listid = $this->session->userdata('sql_id_temp'); $this->session->set_userdata('savedlist', '0');}
	  	
	    // if no criteria selected look in preferences table first  	
	  	$criteria_array = explode('-', rtrim($criteria,'-')); 
	  	
	  	// get tagged records, if any
	  	$tagged_list="";
	  	$tagged_list_array="";
	  	$tagged_list = $this->Search2->get_tagged_records($listid);
	  	if($tagged_list !=""){
	  		$tagged_list_array = explode('-',rtrim($tagged_list,'-'));   
	  	}

	  	$str='';
	  	$str='<script language="javascript" type="text/javascript"> 
	  	   
					$(document).ready(function() {	
							
					  // jquery table widget	
					  $("#records-list").advancedtable({searchField: "#search", loadElement: "#loader", searchCaseSensitive: false, ascImage: "/media/images/up.png", descImage: "/media/images/down.png"});					   					 

					  /*
					  $("#search").click(function() {
					       $("#search").val("");
					   });
					   */
					  
	                });	
	                
	                $(document).ready(function() {	
	                
	                    // pagination links
		                $("#ajax_paging a").click(function() {  
		                
		                     var href = $(this).attr("href");	                     
                             var offset = href.substr(href.lastIndexOf("/") + 1);
                             
                             // store the offset in hidden field
                             $("#offset_store").val(offset);
 	
						    $.ajax({
						      type: "POST",
						      url: "/index.php/ajax/process_lb_data/4/0/"+offset,  
						      data: $("form#listbuilder_form").serialize()+"&dsn="+$("#database-select").val(),
						      success: function(html){
						        $("#records-box").html(html);
						      }
						    });               
						    return false;
						    
						  });
	  			
	  			        
						  
						// process tagged records
						$(".remove-tags").click(function() {  
						  
		                     var criteria = "";
		                     var count = 0;
		                     var max = $(".record-company-name input").length;
		                     var min = $(".record-company-name input:checked").length;
		                     
		                     // sanity check
		                     if(min >= max){
		                       alert("error: too many records selected.\n The list must contain at least one record");
		                       return false;
		                     }	                    
		                     
			                 $(".record-company-name input:checked").each(function() {
			                 
			                     // get criteria
			                     criteria+=$(this).val()+"-";
				 		    	 
			 	 		    	 // remove row 
			 	 		    	 $(this).parent().parent().remove();  // 2 parent levels here to remove a row (<tr>)	 
			 	 		    	 count++;		   
			 		    	 });
			 		    	 
			 		    	 if(criteria == ""){ criteria = "0" }
	 		    	 
	 					     $.ajax({
						      type: "POST",
						      url: "/index.php/ajax/delete_tagged_records/"+criteria+"/"+count,  						      
						      success: function(html){
						          alert(html);
						      }
						    });               
						    return false;
						    
						  });
					  
					  });
			 
		     </script>';
	  	     
	  	if($display != ""){
	  		$str.='<p style="color:#666666"><strong>'.$display.'</strong></p>';
	  	}
	  	
	  	$str.='<!--<input type="text" name="search" id="search" value="Enter filter criteria" />&nbsp;(Search these records)<br />-->';
	  	
	  	$pagination_code = "";
	  	/*
	  	if(!$external_data){ $pagination_code = $this->pagination->create_links();}
	  	
	  	$str.= '<div class="pagination ajax-pag" id="ajax_paging">';
	  	
	  	if($pagination_code){
	  	   $str.= $pagination_code;
	  	}
	  	*/
	  	
	  	//Remove this following line if returning to pagination
	  	$str.= '<div class="pagination ajax-pag" id="ajax_paging"><span style="float:left;">This is a sample of your list - please save to see the full list.</span>';
	  	
	  	$str.= ' <span class="remove-tags" style="float:right!important;">Remove Tagged Records</span><div class="clear">&nbsp;</div></div>';
	  	
	  	$str.='<table id="records-list">';
		$str.='<thead>';
		$str.='<tr class="table-head">';
		$str.='<th style="width:20px;">Tag</th>';
		
		if(empty($criteria)){ // first look in preferences else use default headers 
				
				if($this->session->userdata('demouser')){ // demo user
					$str.='
						<th>Company</th>
						<th>Town / City</th>
						<th>region</th>
					  ';
				}else{
					$str.='
						<th>Company</th>
						<th>Town / City</th>
						<th>Postcode</th>
						<th>Contact</th>
						<th>Position</th>
						<th>Telephone</th>
					  ';
					
					if($rawdata[0]->origin_dsn == 'prd'){
						$str.='<th>Activity</th>';
					}
				}
			}
		else{
			
			foreach ($criteria_array as $header){
				if ($header == "post_code"){ $header = "postcode"; }
				$str.='<th>'.$this->display_fields[$header].'</th>'; // display_fields[] is global var in constructor
			}
		}		
				
		$str.='</tr>';
		$str.='</thead>';
		
		$str.='<tbody id="records-wrap-results">';
		
		// add deleted records FIRST - assumes that count < = $config['per_page'] (ie 200); 
		// ONLY DISPLAYED ON FIRST PAGE
		if(!$this->uri->segment(4)){
	       if($history['deletions']){$str.= $history['deletions'];}
		}
	  	
		if(empty($criteria)){ // set default values
			
			$last_record="";
		  	foreach ($rawdata as $item){  
		  		
		  	    // check for duplicates ??	

		  		/*
		  		if($last_record !=""){
		  			if($last_record == $item->companyid){
		  				$last_record = $item->companyid;
		  				continue 1;
		  				//echo $last_record;die;
		  			}
		  		}
		  		*/
		  		
		  		
		  		// remove tagged records
		  		if($tagged_list_array !=""){
			  		foreach ($tagged_list_array as $companyid) {
			  		   if($companyid == $item->companyid){
			  		   	 continue 2;
			  		   }
			  		}
		  		}

		  		
		  		// default html
		  		if(!$history['additions']){
		  			$str.="<tr class='listrow'>";
		  		}
		  		
		  		// add new records
		  		if($history['additions']){
		  			if(in_array($item->companyid,$history['additions'])){
		  				$str.="<tr class='listrow records-list-added'>";
		  			}else{
		  				$str.="<tr class='listrow'>";
		  			}
		  		}
		  		
		  		// process companyname
		        if($item->companyname !="" && $item->tradingas !=""){
		      	   $item->companyname = $item->tradingas.' (reg. as '.$item->companyname.')';
		        }	

		        // last record viewed ?
		        $style="";
		        if($this->Viewlist->capture_last_viewed_record($listid) == $item->companyid){
		        	$item->companyname = '--> '.$item->companyname;
		        	$style = 'color:#ff0000;';
		        }
		  		
		        $disable_flag = ($item->origin_dsn == 'prd' ? 'disabled="disabled"' : '');
		  		$str.='<td class="record-company-name"><input type="checkbox" value="'.$item->companyid.'" '.$disable_flag.' ></td>';
		  		
		  		$link = base_url().'viewrecord/index/'.$item->companyid.'/'.$external_listid;

		  		if(!$external_data){
		  		    $str.="<td><span title='Please save list to view records'>".trim($item->companyname)."</span></td>";
		  		}else{
		  			$str.="<td><a style='".$style."' class='link-to-record'  href='".$link."'>".trim($item->companyname)."</a></td>";
		  		}	
		  		
		  		$str.="<td>".$item->city."</td>";
		  				  		
		  		if(!$this->session->userdata('demouser')){ // demo user
		  			$str.="<td>".$item->postcode."</td>";
		  		}else{
		  			$str.="<td>".$item->region."</td>";
		  		}		  		
		  		
		  		if(!$this->session->userdata('demouser')){ // demo user
			  		$str.="<td>".$item->firstname.' '.$item->surname."</td>";
			  		$str.="<td>".$item->position."</td>";
			  		$str.="<td>".$item->telephoneno."</td>";
			  		
			  		if($item->origin_dsn == 'prd'){
			  		   $str.='<td><input id="'.$item->companyid.'" class="delete-button remove-row-list-prd" type="button" value="Delete" /></td>';
		  		    }
		  		}
		  		
		  		$str.="</tr>";
		  		
		  	    //$last_record = $item->companyid;
		  	}
		}else{ // user selections
			
			foreach ($rawdata as $item){ 
				
			    // remove tagged records
			    if($tagged_list_array !=""){
			  		foreach ($tagged_list_array as $companyid) {
			  		   if($companyid == $item->companyid){
			  		   	 continue 2;
			  		   }
			  		}
		  		}

			    // default html
		  		if(!$history['additions']){
		  			$str.="<tr class='listrow'>";
		  		}
		  		
		  		// add new records
		  		if($history['additions']){
		  			
		  			if(in_array($item->companyid,$history['additions'])){
		  				$str.="<tr class='listrow records-list-added'>";
		  			}else{
		  				$str.="<tr class='listrow'>";
		  			}
		  		}
		  		
		  		 // process companyname
		        if($item->companyname !="" && $item->tradingas !=""){
		      	   $item->companyname = $item->tradingas.' (reg. as '.$item->companyname.')';
		        }

			    // last record viewed ?
		        $style="";
		        if($this->Viewlist->capture_last_viewed_record($listid) == $item->companyid){
		        	$item->companyname = '--> '.$item->companyname;
		        	$style = 'color:#ff0000;';
		        }
				
		        $disable_flag = ($item->origin_dsn == 'prd' ? 'disabled="disabled"' : '');
		  		$str.='<td class="record-company-name"><input type="checkbox" value="'.$item->companyid.'" '.$disable_flag.' ></td>';
		  		
		  		// reset($criteria_array);
		  		foreach ($criteria_array as $value){
		  			
		  			    $link = base_url().'viewrecord/index/'.$item->companyid.'/'.$external_listid;
		  				
		  				if($value == 'companyid') { $str.="<td>".$item->companyid."</td>"; }
		  			   // if($value == 'companyname') { $str.="<td>".$item->companyname."</td>"; }		  			    
		  		      		  		        
		  		        if($value == 'companyname') {
			  		        if(!$external_data){
					  		    $str.="<td><span style='' title='Please save list to view records'>".trim($item->companyname)."</span></td>";
					  		}else{
					  			$str.="<td><a style='".$style."' class='link-to-record'  href='".$link."'>".trim($item->companyname)."</a></td>";
					  		}
		  		        }
		  		        
		  			    if($value == 'city') { $str.="<td>".$item->city."</td>"; }
		  			    if($value == 'address1') { $str.="<td>".$item->address1."</td>"; }
		  			    if($value == 'post_code') { $str.="<td>".$item->postcode."</td>"; }
			  			if($value == 'firstname') { $str.="<td>".$item->firstname."</td>"; }
			  			if($value == 'surname') { $str.="<td>".$item->surname."</td>"; }
			  			if($value == 'position') { $str.="<td>".$item->position."</td>"; }
			  			if($value == 'region') { $str.="<td>".$item->region."</td>"; }
			  			if($value == 'telephoneno') { $str.="<td>".$item->telephoneno."</td>"; }
			  			if($value == 'companyemail') { $str.="<td>".$item->companyemail."</td>"; }
			  			if($value == 'addresstypes') { $str.="<td>".$item->addresstypes."</td>"; }
			  			if($value == 'markets') { $str.="<td>".$item->markets."</td>"; }
			  				  			    
		  		}
		  		
		  		$str.="</tr>";
				
			}
		}
	  	
	  	$str.='</tbody></table>';
	  	
	  	/*
	  	$str.= '<div class="pagination" id="ajax_paging">'.$pagination_code.' <span class="remove-tags" style="float:right!important;">Remove Tagged Records</div>';
	   */
	  	
	  	if($external_data) {
	  		return $str;
	  	}
	  	
	 	echo $str;
	  }  
  
  public function export_to_xml($externaldata = false)
  {
    // data source ?
	if($externaldata){ $data_source = $externaldata;}else{ $data_source = $this->results;}
  	
  	$this->load->dbutil();
	    
    	$config = array (
                  'root'    => 'root',
                  'companies' => 'companies',
                  'newline' => "\n",
                  'tab'    => "\t"
                );
		
	echo $this->dbutil->xml_from_result($data_source, $config); // xml dump
  } 
  
  // called by export_to_csv()
  private function get_credit_info($id){
     $str="";
     $delim = ",";
	 $newline = "\r\n";
	 $enclosure = '"';
	 
  	 $result = $this->Search2->get_credit_info($id);
  	 
  	 if($result){
  	 	$str .= $enclosure.str_replace($enclosure, $enclosure.$enclosure, $result->companynumber).$enclosure.$delim;
  	 	$str .= $enclosure.str_replace($enclosure, $enclosure.$enclosure, $result->creditrating).$enclosure.$delim;
  	 	$str .= $enclosure.str_replace($enclosure, $enclosure.$enclosure, $result->creditlimit).$enclosure.$delim;
  	 	$str .= $enclosure.str_replace($enclosure, $enclosure.$enclosure, $result->numccj).$enclosure.$delim;
  	 	$str .= $enclosure.str_replace($enclosure, $enclosure.$enclosure, $result->companytype).$enclosure.$delim;
  	 	$str .= $enclosure.str_replace($enclosure, $enclosure.$enclosure, $result->creditratingdesc).$enclosure.$delim;
  	 }
  	 else{
  	 	for ($i = 0; $i < 6; $i++) {
  	 		$str .= $enclosure.str_replace($enclosure, $enclosure.$enclosure, 'N/A').$enclosure.$delim;
  	 	}
  	 }
  	 return $str;
  }
  
   // called by get_product_info()
   private function get_product_info_sectors($compid,$dsn,$varname,$delimiter){
   
   	          $str="";
   	          $delim = $delimiter;
			  $newline = "\r\n";
			  $enclosure = '"';
   	          
              $this->tempArray = $this->Viewlist->get_products_data($compid,$dsn);
              $temp = substr($this->tempArray[0]->type, 7);
		      $this->products = explode(',',$temp);	
		       
		      for($k=0; $k<count($varname); $k++){
		       	  if(in_array($k+1, $this->products)){
		       	  	 $str.= $enclosure.str_replace($enclosure, $enclosure.$enclosure, 'YES').$enclosure.$delim;
		       	  }else{
		       	  	 $str.= $enclosure.str_replace($enclosure, $enclosure.$enclosure, 'NO').$enclosure.$delim;
		       	  }
		      }

		      return $str;
   }
   
// called by get_product_info()
   private function get_product_info_sectors2($compid,$dsn,$delimiter){
   
   	          $str="";
   	          $delim = $delimiter;     
			  $newline = "\r\n";
			  $enclosure = '"';
			  $this->products = array();
			  $this->tempArray = array();
			  
			  
			  $mcd_array = array(11,12,13,21,22,31,32,33,41,42,43,51,52,53,54,61,62,63,71,72,73,81,82,91);
			  $asd_array = array(11,12,13,21,22,31,32,33,41,42,43,51,52,53,54,61,62,63,71,72,73);
			  
			  if($dsn == 'mcd'){$myarray = $mcd_array;}
              if($dsn == 'asd'){$myarray = $asd_array;}
			    
              $this->tempArray = $this->Viewlist->get_products_data($compid,$dsn);
              foreach($this->tempArray as $row){		  				
		    	 $this->products[] = substr($row->type, 7);
			  }
      
		      foreach($myarray as $val){
		       	  if(in_array($val, $this->products)){
		       	  	 $str.= $enclosure.str_replace($enclosure, $enclosure.$enclosure, 'YES').$enclosure.$delim;
		       	  }else{
		       	  	 $str.= $enclosure.str_replace($enclosure, $enclosure.$enclosure, 'NO').$enclosure.$delim;
		       	  }
		      }
		      
		      if($dsn == 'asd'){
		         $str.= $enclosure.str_replace($enclosure, $enclosure.$enclosure, $this->tempArray[0]->system).$enclosure.$delim;
		      }
   
		      return $str;
   }
   
   // called by get_product_info()
  private function get_product_info_fen($compid,$dsn,$delimiter=","){
  	 
  	 		  $str="";
   	          $delim = ",";     
			  $newline = "\r\n";
			  $enclosure = '"';
			  $this->products = array();
			  $this->tempArray = array();
			  
			  // check delim
			  if($delimiter){
			  	 $delim=$delimiter;
			  }
			  
			  $fpw=$rpm=$dpw=$upw='N/A';
			  
			  $met=$des="";
			  $met2=$des2="";
			  $met3=$des3="";
			  
			  $met4=$des4="";
			  $met5=$des5="";
			  $met6=$des6="";
			  
			  $met7=$des7="";
			  $met8=$des8="";
			  $met9=$des9="";
			  
			  $met10=$des10="";
			  $met11=$des11="";
			  
			  $met12=$des12="";
			 
			  $met13=$des13=""; 
			  
			  $met14=$des14=""; 
			  
			  $this->tempArray = $this->Viewlist->get_products_data($compid,$dsn);
			  
			  foreach($this->tempArray as $row){
			  	 
			  // Windows and Doors PVCu   (Description-PVCu,Method-PVCu,System-PVCu')
				if(strstr($row->type,'wd') && $row->material == 'PVCu' && $row->description != 'Frames per week'){
					
				   if($row->description == 'Windows and Doors') {
				   	  if($des != $row->description){ $des.= $row->description; }			      
				      $met.=$row->method.'-'.$row->system.';';			      
				   } // products data - wd
				   
				   if($row->description == 'Vertical Sliders') {
				      if($des2 != $row->description){ $des2.= $row->description; }
				      $met2.=$row->method.'-'.$row->system.';';	
				     
				   } // products data - vs	
				   	   
				   if($row->description == 'Bi-Fold Doors') {
				      if($des3 != $row->description){ $des3.= $row->description; }
				      $met3.=$row->method.'-'.$row->system.';';	
				     
				   } // products data - bd	
	
				   	
				} // end of PVCu W/D 

			   // Windows and Doors - Aluminium	
			  if(strstr($row->type,'wd') && $row->material == 'Aluminium' && $row->description != 'Frames per week'){
			    	
			       if($row->description == 'Windows and Doors') {
			          if($des4 != $row->description){ $des4.= $row->description; }
				      $met4.=$row->method.'-'.$row->system.';';	
			       } // products data - wd
				   		       
			       if($row->description == 'Specialist') {
			          if($des5 != $row->description){ $des5.= $row->description; }
				      $met5.=$row->method.'-'.$row->system.';';	
			       } // products data - sp
			       	       
				   if($row->description == 'Bi-Fold Doors') {
				      if($des6 != $row->description){ $des6.= $row->description; }
				      $met6.=$row->method.'-'.$row->system.';';	
				   } // products data - bd		
	
		
				}  // end of Aluminium W/D 
				
			  // Windows and Doors - Other (Timber Windows/Doors,Timber Sash Windows,Composite Windows)
			  if(strstr($row->type,'wd') && $row->material == 'Other Materials' && $row->description != 'Frames per week'){ 
			  	
				  	if($row->description == 'Timber Windows/Doors') {
				  	     if($des7 != $row->description){ $des7.= $row->description; }
					     $met7.=$row->method.'-'.$row->system.';';	
				  	}
				  	
			        if($row->description == 'Timber Sash Windows') {
			             if($des8 != $row->description){ $des8.= $row->description; }
					     $met8.=$row->method.'-'.$row->system.';';	
				  	}
				  	
			        if($row->description == 'Composite Windows') {
			             if($des9 != $row->description){ $des9.= $row->description; }
					     $met9.=$row->method.'-'.$row->system.';';	
				  	}
				  	
				}
				
			  if(strstr($row->type,'wd') && $row->description == 'Frames per week' && $row->material == ''){ // one value
				   $fpw = $row->volume;	
				}

			  // Conservatory Roofs
			  if(strstr($row->type,'cr') && $row->description == 'PVCuOrAluminium'){
			       if($des10 != $row->description){ $des10.= $row->description; }
				   $met10.=$row->method.'-'.$row->system.';';	
				}
				
			  if(strstr($row->type,'cr') && $row->description == 'TimberOrOther'){
			       if($des11 != $row->description){ $des11.= $row->description; }
				   $met11.=$row->method.'-'.$row->system.';';
				}
				
			  if(strstr($row->type,'cr') && $row->description == 'Roofs per month'){ // one value
				   $rpm = $row->volume;	
				}
				
			  // Composite Doors
			  if(strstr($row->type,'cd') && $row->description == 'Activity'){ 
			       if($des12 != $row->description){ $des12.= $row->description; }
				   $met12.=$row->method.'-'.$row->system.';';
				}
				
			  if(strstr($row->type,'cd') && $row->description == 'Doors per week'){ // one value
				   $dpw = $row->volume;	
				}

			  // PVC-U/PVC-UE Roofline Installer
			  if(strstr($row->type,'rl')){ // or empty description ?
			        if($des13 != $row->description){ $des13.= $row->description; }
				    $met13.=$row->method.'-'.$row->system.';'; 
				}
				
			  // Sealed Units
			  if(strstr($row->type,'su') && $row->description == 'Sealed Units'){ // one value
				   if($des14 != $row->description){ $des14.= $row->description; }
				   $met14.=$row->method.'-'.$row->system.';';  
				   $upw = $row->volume;	
				}
				
		} // end of loop
			  
			  // PVCu WD
			 $str.= $enclosure.str_replace($enclosure, $enclosure.$enclosure, $des).$enclosure.$delim;
			 $str.= $enclosure.str_replace($enclosure, $enclosure.$enclosure, $met).$enclosure.$delim;
				 
			 $str.= $enclosure.str_replace($enclosure, $enclosure.$enclosure, $des2).$enclosure.$delim;
			 $str.= $enclosure.str_replace($enclosure, $enclosure.$enclosure, $met2).$enclosure.$delim;
			 		 
			 $str.= $enclosure.str_replace($enclosure, $enclosure.$enclosure, $des3).$enclosure.$delim;
			 $str.= $enclosure.str_replace($enclosure, $enclosure.$enclosure, $met3).$enclosure.$delim;
			 
			 // ALUMINIUM WD
			 $str.= $enclosure.str_replace($enclosure, $enclosure.$enclosure, $des4).$enclosure.$delim;
			 $str.= $enclosure.str_replace($enclosure, $enclosure.$enclosure, $met4).$enclosure.$delim;
			 
			 $str.= $enclosure.str_replace($enclosure, $enclosure.$enclosure, $des5).$enclosure.$delim;
			 $str.= $enclosure.str_replace($enclosure, $enclosure.$enclosure, $met5).$enclosure.$delim;
			 
			 $str.= $enclosure.str_replace($enclosure, $enclosure.$enclosure, $des6).$enclosure.$delim;
			 $str.= $enclosure.str_replace($enclosure, $enclosure.$enclosure, $met6).$enclosure.$delim;
			 
			 // OTHER
			 $str.= $enclosure.str_replace($enclosure, $enclosure.$enclosure, $des7).$enclosure.$delim;
			 $str.= $enclosure.str_replace($enclosure, $enclosure.$enclosure, $met7).$enclosure.$delim;
			 
			 $str.= $enclosure.str_replace($enclosure, $enclosure.$enclosure, $des8).$enclosure.$delim;
			 $str.= $enclosure.str_replace($enclosure, $enclosure.$enclosure, $met8).$enclosure.$delim;
			 
			 $str.= $enclosure.str_replace($enclosure, $enclosure.$enclosure, $des9).$enclosure.$delim;
			 $str.= $enclosure.str_replace($enclosure, $enclosure.$enclosure, $met9).$enclosure.$delim;
			 
			 $str.= $enclosure.str_replace($enclosure, $enclosure.$enclosure, $fpw).$enclosure.$delim;  // FPW volume
			 
			 $str.= $enclosure.str_replace($enclosure, $enclosure.$enclosure, $des10).$enclosure.$delim;
			 $str.= $enclosure.str_replace($enclosure, $enclosure.$enclosure, $met10).$enclosure.$delim;
			 
			 $str.= $enclosure.str_replace($enclosure, $enclosure.$enclosure, $des11).$enclosure.$delim;
			 $str.= $enclosure.str_replace($enclosure, $enclosure.$enclosure, $met11).$enclosure.$delim;
			 
			 $str.= $enclosure.str_replace($enclosure, $enclosure.$enclosure, $rpm).$enclosure.$delim;  // RPM volume
			 
			 $str.= $enclosure.str_replace($enclosure, $enclosure.$enclosure, $des12).$enclosure.$delim;
			 $str.= $enclosure.str_replace($enclosure, $enclosure.$enclosure, $met12).$enclosure.$delim;
			 
			 $str.= $enclosure.str_replace($enclosure, $enclosure.$enclosure, $dpw).$enclosure.$delim;  // DPW volume
			 
			 $str.= $enclosure.str_replace($enclosure, $enclosure.$enclosure, $des13).$enclosure.$delim;
			 $str.= $enclosure.str_replace($enclosure, $enclosure.$enclosure, $met13).$enclosure.$delim;
			 
			 $str.= $enclosure.str_replace($enclosure, $enclosure.$enclosure, $des14).$enclosure.$delim;
			 $str.= $enclosure.str_replace($enclosure, $enclosure.$enclosure, $met14).$enclosure.$delim;
			 
			 $str.= $enclosure.str_replace($enclosure, $enclosure.$enclosure, $upw).$enclosure.$delim;  // UPW volume
			
			  
		 return $str;	  
  }
  
  // called by export_to_csv()
  private function get_product_info($compid,$dsn,$headers=false,$delim="comma"){
  	
  	// return headers or product info ?
  	
  		$headers_data="";  
  		$products_data="";
  		
  		if($delim == 'comma'){
  			$delimiter=",";
  		}else{
  			$delimiter="\t";
  		}
  		
  		switch ($dsn) {
	           	
           	case 'asd':
           	if($headers){	
           	   foreach ($this->sectors_asd as $group){ 
				 foreach ($group as $sector){ 	
   		       	    $headers_data.= $delimiter.str_replace(' ','-',$sector);
				 }
  		       }
           	}else{
           	   $products_data = $this->get_product_info_sectors2($compid,$dsn,$delimiter);	
           	}
           	break;
           	
           	case 'nrg':
  		    if($headers){	
  		       foreach ($this->sectors_nrg as $sector){  // eg ",Company-Number,Credit-Rating,Credit-Limit,CCJ's,Company-Type,Rating-Description";
  		       	  $headers_data.= $delimiter.str_replace(' ','-',$sector);
  		       }
           	}else{   
           	      $products_data = $this->get_product_info_sectors($compid,$dsn,$this->sectors_nrg,$delimiter);
           	}
           	break;
           	
           	case 'mcd':
  		    if($headers){	
  		      foreach ($this->sectors_mcd as $group){ 
				 foreach ($group as $sector){ 	
   		       	    $headers_data.= $delimiter.str_replace(' ','-',$sector);
				 }
  		       }
           	}else{
           	   $products_data = $this->get_product_info_sectors2($compid,$dsn,$delimiter);		
           	}
           	break;
           	
           	case 'sbd':
  		    if($headers){
  		       foreach ($this->sectors_sbd as $sector){  
  		       	  $headers_data.= $delimiter.str_replace(' ','-',$sector);
  		       }		
           	}else{
           	    $products_data = $this->get_product_info_sectors($compid,$dsn,$this->sectors_sbd,$delimiter);
           	}
           	break;	

           	case 'roi':
           	case 'ifd':
  		    if($headers){
  		       if($delim == 'comma'){	// csv	
	           	   $headers_data = ',Product-Description-PVCu,Method-System-PVCu,Product-Description-PVCu,Method-System-PVCu,Product-Description-PVCu,Method-System-PVCu';
	           	   $headers_data .= ',Product-Description-Aluminium,Method-System-Aluminium,Product-Description-Aluminium,Method-System-Aluminium,Product-Description-Aluminium,Method-System-Aluminium';
	           	   $headers_data .= ',Product-Description-Other,Method-System-Other,Product-Description-Other,Method-System-Other,Product-Description-Other,Method-System-Other';
	           	   $headers_data .= ',FPW';          	   
	           	   $headers_data .= ',Product-Description-Conservatory-Roofs,Method-System-Conservatory-Roofs,Product-Description-Conservatory Roofs,Method-System-Conservatory Roofs';
	           	   $headers_data .= ',RPM';         	   
	           	   $headers_data .= ',Product-Description-Composite-Doors,Method-System-Composite-Doors';
	           	   $headers_data .= ',DPW';           	   
	           	   $headers_data .= ',Product-Description-Roofline,Method-System-Roofline';           	   
	           	   $headers_data .= ',Product-Description-Sealed-Units,Method-System-Sealed-Units';
	           	   $headers_data .= ',UPW';
  		       }else{ // tab delimited
	  		       	$headers_data = "\tProduct-Description-PVCu\tMethod-System-PVCu\tProduct-Description-PVCu\tMethod-System-PVCu\tProduct-Description-PVCu\tMethod-System-PVCu";
	  		       	$headers_data .= "\tProduct-Description-Aluminium\tMethod-System-Aluminium\tProduct-Description-Aluminium\tMethod-System-Aluminium\tProduct-Description-Aluminium\tMethod-System-Aluminium";
	  		       	$headers_data .= "\tProduct-Description-Other\tMethod-System-Other\tProduct-Description-Other\tMethod-System-Other\tProduct-Description-Other\tMethod-System-Other";
	  		       	$headers_data .= "\tFPW";
	  		       	$headers_data .= "\tProduct-Description-Conservatory-Roofs\tMethod-System-Conservatory-Roofs\tProduct-Description-Conservatory Roofs\tMethod-System-Conservatory Roofs";
	  		       	$headers_data .= "\tRPM";
	  		       	$headers_data .= "\tProduct-Description-Composite-Doors\tMethod-System-Composite-Doors";
	  		       	$headers_data .= "\tDPW";
	  		       	$headers_data .= "\tProduct-Description-Roofline\tMethod-System-Roofline";
	  		       	$headers_data .= "\tProduct-Description-Sealed-Units\tMethod-System-Sealed-Units";
	  		       	$headers_data .= "\tUPW";
  		       }
           	}else{
           		if($delim == 'comma'){
           	       $products_data =  $this->get_product_info_fen($compid,$dsn,",");	
           		}else{
           		   $products_data =  $this->get_product_info_fen($compid,$dsn,"\t"); // tab, Excel
           		}
           	}
           	break;
           	
           	default:
           	return 0;	
           	
        } // end of switch

      if($headers){	
         return  $headers_data;
      }else{
         return  $products_data;	
      }
  }
 
  public function export_to_csv($externaldata=false, $dsn=false, $listid=false)
  {
 	$this->load->dbutil();
 	
  	// Create file name & Setup some vars
    $timestamp = date("d_m_y_H_i_s");
    $userID = $this->session->userdata['uid'];
    $clientid = $this->session->userdata['cid'];
  	
  	    // globals
  	    $str="";
	    $delim = ",";
		$newline = "\r\n";
		$enclosure = '"';
		$tagged_list_array="";	
		$tagged_list="";
		$tagged_records_count=0;
		$demo_count=0;
		$main_count=0;

		$includes_bc = false;
		$includes_bp = false;
		$includes_bm = false;
		$omitted_fields = array('quarantined','origin_dsn','markets','addresstypes');
		$formatfulllist=0; // for FORMATS 2,3,4	
		
		// data source ?
		if($externaldata){ $data_source = $externaldata;}else{ $data_source = $this->results->result_array();}
			
		
        // get tagged records, if any
	  	$tagged_list = $this->Search2->get_tagged_records($listid);
	  	if($tagged_list !=""){
	  		$tagged_list_array = explode('-',rtrim($tagged_list,'-'));  
	  	}
	  	
	  	// check dsn
	  	if(!$dsn){$dsn = $this->input->post('dsn');}
	  	
	  	/*
	  	$installer="";
	  	if($dsn == 'ifd' || $dsn == 'roi'){ // NEW STUFF
	  		$installer=",Installer";
	  	}
	  	*/

	  	/*
		// default headers
		$str="Companyname,Companyid,Website,Address1,Address2,Address3,City,Postcode,Region,Title,Firstname,"; // NEW STUFF
		$str.="Surname,Position,Directno,Telephoneno,EmailAddress";
		*/
	  	
	  	$str="Companyname,Companyid,Address1,Address2,Address3,City,Postcode,Region,Title,Firstname,"; // NEW STUFF
	  	//$str="Companyname,Companyid,Address1,City,Postcode,Region,Title,Firstname,";
	  	$str.="Surname,Position,Telephoneno,EmailAddress";
	  	
		
        // process includes xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
        
		// Business Details (premises type, market sectors)
        if(strstr($this->input->post('csv_include_checks'),'list-business-details')){  
		    $omitted_fields = array('quarantined','origin_dsn');
			$str.=",Addresstypes,Markets";
		}
		// Credit Manager (financial information where available)
		if(strstr($this->input->post('csv_include_checks'),'list-business-credit')){ 
			$includes_bc = true;
			$str.=",Company-Number,Credit-Rating,Credit-Limit,CCJ's,Company-Type,Rating-Description";
		}
		
		//$str.= $installer; // fen only  // NEW STUFF
		
		// Product Details (fabricate/buy in, materials, products, brands, volume)
        if(strstr($this->input->post('csv_include_checks'),'list-business-products')){ 
			$includes_bp = true;
			
			// check dsn
			if(!$dsn){$dsn = $this->input->post('dsn');}
			$str.= $this->get_product_info("",$dsn,true); // headers only
		}
		// Sales Manager (status, lead source, sales person, etc)
        if(strstr($this->input->post('csv_include_checks'),'list-business-manager')){ 
			$includes_bm = true;
		}
		
		// END process includes xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		
		$str.=$newline;
		
		 // FORMAT 4,  selected fields - 5 fields only
		if($this->input->post('csv_choices') == 'format-email-list-quick'){ 
			  $str="Companyname,Title,Firstname,Surname,EmailAddress".$newline;
		}
			
		$row_store = array();
		$str2 = $newline;
		$lastcompid="";
         
		 // FORMAT 1: standard format (1 primary contact) DEFAULT CHOICE
		 // FORMAT 2: full format (all contacts) and 
		 // FORMAT 3: Email format  and
		 // FORMAT 4: Email format (Quick, 4 fields) 
		  	
	      foreach ($data_source as $row) // loop through rows
			 {	
			 	// CHECK FOR DUPLICATE RECORDS - ONLY USEFUL IF SORTED BY COMPANY IDS
			 	/*
			 	if($lastcompid == $row->companyid){
			 		$lastcompid = $row->companyid;
			 		continue;
			 	}
			 	*/
			 	
			 	// demo account limit ?
			 	if($this->session->userdata('demouser')){ 
			 		if($demo_count >= DEMO_EXPORT_MAX){
			 			continue;
			 		}
			 	}
			 	
			    // check for correct format
				if($externaldata){ $compid = $row->companyid;}	else { $compid = $row['companyid']; } 
				if($externaldata){ $companyemail = $row->companyemail;}	else { $companyemail = $row['companyemail']; } // for format 3/4, later
				
				// FORMAT 3/4 ONLY ... if no company email address at all, drop record
				if(($this->input->post('csv_choices') == 'format-email-list' || $this->input->post('csv_choices') == 'format-email-list-quick') && !$companyemail){
					continue;
				}
				
			    // remove tagged records
		  		if($tagged_list_array !=""){
			  		foreach ($tagged_list_array as $companyid) {
			  		   if($companyid == $compid){
			  		   	 $tagged_records_count++;
			  		   	 continue 2;
			  		   }
			  		}
		  		}
			 	
			 	// check for valid companyemail for email exports (formats 3 and 4)
			    if($this->input->post('csv_choices') == 'format-email-list-quick' ||
				   	$this->input->post('csv_choices') == 'format-email-list'){
				   			if(empty($compid)) {
				   				continue;
				   			}
				    }	
			 	
				 // process fields in row  
				 $tradingas_flag = false; 
				 $tradingas_fix = "";
				 foreach ($row as $fieldname => $item) 
				   {
				   	
				   	// omit some fields
				    for($j=0; $j<count($omitted_fields); $j++){			    	
				    		if($fieldname == $omitted_fields[$j]){
				    			continue 2; // for the second outer foreach loop
				    		}			    	
				     }

				   // company name update if there is a trading name 
					   if($externaldata){ 
						   if($row->companyname !="" && $row->tradingas !=""){
					      	   $tradingas_fix = $row->tradingas;
					      	   $tradingas_flag = true;
					        } 
					   }else{
					   	   if($row['companyname'] !="" && $row['tradingas'] !=""){
					      	   $tradingas_fix = $row['tradingas'];
					      	   $tradingas_flag = true;
					        } 
					   }

					// omit tradingas, companyregno
					if($fieldname == 'tradingas' || $fieldname == 'companyregno'){
						continue;
					} 

					// format telephone numbers
					if($fieldname == 'telephoneno' || $fieldname == 'directno'){
						
						if($item){
						   $item = '#'.$item;
						}
					}
				   	
				   	if($this->input->post('csv_choices') == 'format-email-list-quick'){  // FORMAT 4,  selected fields
				   		
					   	if($fieldname == 'companyname') {
					   		
					   		if($tradingas_flag){$item = $tradingas_fix;}
					   		
					   		$str .= $enclosure.str_replace($enclosure, $enclosure.$enclosure, htmlspecialchars_decode($item,ENT_QUOTES)).$enclosure.$delim;
					        $row_store[$fieldname]=$enclosure.str_replace($enclosure, $enclosure.$enclosure, htmlspecialchars_decode($item,ENT_QUOTES)).$enclosure.$delim;
					   	}
					   	
					   	
					   	if($fieldname == 'title') {  // NEW STUFF
					   		$str .= $enclosure.str_replace($enclosure, $enclosure.$enclosure, $item).$enclosure.$delim;
					   		$row_store[$fieldname]=$enclosure.str_replace($enclosure, $enclosure.$enclosure, $item).$enclosure.$delim;
					   	}
					   
					   	
				  	    if($fieldname == 'firstname') {
				  	    	$str .= $enclosure.str_replace($enclosure, $enclosure.$enclosure, $item).$enclosure.$delim;
				  	    	$row_store[$fieldname]=$enclosure.str_replace($enclosure, $enclosure.$enclosure, $item).$enclosure.$delim;
				  	    }
				  	    
				  	    if($fieldname == 'surname') {
				  	    	$str .= $enclosure.str_replace($enclosure, $enclosure.$enclosure, $item).$enclosure.$delim;
				  	    	$row_store[$fieldname]=$enclosure.str_replace($enclosure, $enclosure.$enclosure, $item).$enclosure.$delim;
				  	    }
				  	    
				  	    if($fieldname == 'companyemail') {
				  	    	$str .= $enclosure.str_replace($enclosure, $enclosure.$enclosure, $item).$enclosure.$delim;
				  	    	$row_store[$fieldname]=$enclosure.str_replace($enclosure, $enclosure.$enclosure, $item).$enclosure.$delim;
				  	    }
				   	}
				   	else{ // all fields
				   		
				   		/*
				   	    // fen installer ?
				   		if($dsn == 'ifd' || $dsn == 'roi'){  // NEW STUFF
				   			
				   			$fen_installer='NO';
				   			if($this->Search2->check_fen_installer($compid,$dsn)){
				   				$fen_installer='YES';
				   			}
				   			
				   			$fenstr = $enclosure.str_replace($enclosure, $enclosure.$enclosure, $fen_installer).$enclosure.$delim;
				   		}
				   		*/
				   		
				   	   // process companyname
				   	  if($tradingas_flag && $fieldname == 'companyname'){$item = $tradingas_fix;}	
				   	  
				   	  $str .= $enclosure.str_replace($enclosure, $enclosure.$enclosure, htmlspecialchars_decode($item,ENT_QUOTES)).$enclosure.$delim;
				   	  
					  // store this field
					  $row_store[$fieldname]=$enclosure.str_replace($enclosure, $enclosure.$enclosure, htmlspecialchars_decode($item,ENT_QUOTES)).$enclosure.$delim;
				   	}
					
				 } // end of field processing
				 
				 /*
				 // append fen installer ?
				 if($dsn == 'ifd' || $dsn == 'roi'){
				    $str .= $fenstr;  // NEW STUFF
				 }
				 */
				
				 // check dsn
				 if(!$dsn){$dsn = $this->input->post('dsn');}	  
				 
				 // process email / contacts - FORMATS 2,3,4 ONLY
				 if($this->input->post('csv_choices') != 'format-standard'){ 

				 if($this->input->post('csv_choices') == 'format-email-list'){  // FORMAT 3
				    $result = $this->Search2->getContacts($compid,$dsn,false,true);
				 }else{
				 	$result = $this->Search2->getContacts($compid,$dsn,false,false);
				 }
		 
				 if($result){
				 	
					foreach ($result->result() as $row2) {
						
						   if($this->input->post('csv_choices') == 'format-full-list'){  // FORMAT 2	
						   	
						   	   if(!empty($row2->title)) {$row_store['title'] = $row2->title.$delim;}
							   if(!empty($row2->firstname)) {$row_store['firstname'] = $row2->firstname.$delim;}	
							   if(!empty($row2->surname)) {$row_store['surname'] = $row2->surname.$delim;}
							   if(!empty($row2->position)) {$row_store['position'] = $row2->position.$delim;}								   
							   
							   foreach ($row_store as $value){
								$str2 .= $value;
							   }
							    
							   $str2 .= $newline;
							   $formatfulllist++;						   
							}

						   if($this->input->post('csv_choices') == 'format-email-list'){  // FORMAT 3	
								
						   	   $email_flag=false; 
						   	   if(!empty($row2->email)){ 
						   	   	   if(!empty($row2->title)) {$row_store['title'] = $row2->title.$delim;}
								   if(!empty($row2->firstname)) {$row_store['firstname'] = $row2->firstname.$delim;}	
								   if(!empty($row2->surname)) {$row_store['surname'] = $row2->surname.$delim;}
								   if(!empty($row2->position)) {$row_store['position'] = $row2->position.$delim;}
							       if(!empty($row2->email)) {$row_store['companyemail'] = $row2->email.$delim;}	
							       $email_flag=true; 						       							       
						   	   }
							  if($email_flag){
							  	 foreach ($row_store as $value){
									$str2 .= $value;
								   }
								    
								   $str2 .= $newline;
								   $formatfulllist++;
							  }
							}	

							if($this->input->post('csv_choices') == 'format-email-list-quick'){  // FORMAT 4
								
						   	   $email_flag=false; 
						   	   if(!empty($row2->email)){ 
						   	   	   if(!empty($row2->title)) {$row_store['title'] = $row2->title.$delim;}
								   if(!empty($row2->firstname)) {$row_store['firstname'] = $row2->firstname.$delim;}	
								   if(!empty($row2->surname)) {$row_store['surname'] = $row2->surname.$delim;}
							       if(!empty($row2->email)) {$row_store['companyemail'] = $row2->email.$delim;}	
							       $email_flag=true; 						       							       
						   	   }
							  if($email_flag){
							  	 foreach ($row_store as $fieldname => $value){ // select fields
							  	 	if($fieldname == 'companyname') {$str2 .= $value;}
							  	 	if($fieldname == 'title') {$str2 .= $value;}
							  	    if($fieldname == 'firstname') {$str2 .= $value;}
							  	    if($fieldname == 'surname') {$str2 .= $value;}
							  	    if($fieldname == 'companyemail') {$str2 .= $value;}
								   }
								    
								   $str2 .= $newline;
								   $formatfulllist++;
							  }
							}		
		
					  } // end of foreach loop
	
					// append to str
					$str .= rtrim($str2); // rtrim removes newline
					
					// reset
					$str2 = $newline;
					$row_store = array();
				 } // end of contacts / email  - FORMATS 2,3,4 
				 
			   } // end of if
				 
			 // process includes 
			 
			 // credit data  
			 if($includes_bc){
				   $str3 = $this->get_credit_info($compid);
				   $str .= rtrim($str3);	
			  }

			  //product data
			 if($includes_bp){
				   $str4 = $this->get_product_info($compid,$dsn);
				   $str .= rtrim($str4);
			  }
			 
			 // END process includes 
			
			 $str = rtrim($str);
			 $str .= $newline;
			 
			 $demo_count++;
			 $main_count++;
				
		   } // end of outer loop (records)
	
	
		   
     // get totals
     if($this->session->userdata('demouser')){
     	$totrec = $newline.'TOTAL RECORDS = ' .$demo_count;
     }else{
     	$totrec = $newline.'TOTAL RECORDS = ' .($main_count+$formatfulllist-$tagged_records_count);
     }
     
    
	 // START SEAN PRYCE OPTIMISATIONS 
	    
	 // Directory Structure to use
	  $directorystructure = CSV_FOLDER . $clientid;
	  $userIDstructure = CSV_FOLDER . $clientid . "/" . $userID;
	 
	  $filename = CSV_FILENAME_GENERIC;
	
	  // If the directory doesn't exist - create it and also create a directory for the user who just built the list
	  if(!file_exists($directorystructure))
	  {
			mkdir(CSV_FOLDER . $clientid, 0777);
			mkdir(CSV_FOLDER . $clientid . "/" . $userID, 0777);
			mkdir(CSV_FOLDER. $clientid ."/". $userID ."/". $timestamp, 0777);
	  }
	// If the folder does exist, no need to create it but check the user folder on its own and create where necessary
	  else
	  {
			if(!file_exists($userIDstructure))
			{
			mkdir(CSV_FOLDER. $clientid ."/". $userID, 0777);
			}
			else
			{
			mkdir(CSV_FOLDER. $clientid ."/". $userID ."/". $timestamp, 0777);
			}
	   }
	    
	  // Define the file that will be created
	  $file = CSV_FOLDER. $clientid . "/" . $userID . "/" . $timestamp . "/" . $filename;
	  
	  // This prevents the random occurance of not being able to load the file
	  ob_start();
	
	  // Open file
	  $current = file_get_contents($file);
	  
	  ob_end_clean();
	 
	  // Add info to the file
	  $current .= $str;
	  
	  // get totals
	  $current .= $totrec;
	 
	  // Write the contents back to the file
	  file_put_contents($file, $current);
	 
	// Send the data to utilities.php
	echo CSV_FOLDER. $clientid . "/" . $userID . "/" . $timestamp . "/" . $filename;
	
	// END SEAN PRYCE OPTIMISATIONS
  } 
  
  public function export_to_xls($externaldata=false, $dsn=false, $listid=false)
  {
  	$this->load->dbutil();
  
  	// Create file name & Setup some vars
  	$timestamp = date("d_m_y_H_i_s");
  	$userID = $this->session->userdata['uid'];
  	$clientid = $this->session->userdata['cid'];
  
  	// globals
  	$str="";
  	$delim = "	";
  	$newline = "\r\n";
  	$enclosure = '"';
  	$tagged_list_array="";
  	$tagged_list="";
  	$tagged_records_count=0;
  	$demo_count=0;
  	$main_count=0;
  
  	$includes_bc = false;
  	$includes_bp = false;
  	$includes_bm = false;
  	$omitted_fields = array('quarantined','origin_dsn','markets','addresstypes');
  	$formatfulllist=0; // for FORMATS 2,3,4
  
  	// data source ?
  	if($externaldata){ $data_source = $externaldata;}else{ $data_source = $this->results->result_array();}
  
  
  	// get tagged records, if any
  	$tagged_list = $this->Search2->get_tagged_records($listid);
  	if($tagged_list !=""){
  		$tagged_list_array = explode('-',rtrim($tagged_list,'-'));
  	}
  
  	// check dsn
  	if(!$dsn){$dsn = $this->input->post('dsn');}
  
  	/*
  	 $installer="";
  	if($dsn == 'ifd' || $dsn == 'roi'){ // NEW STUFF
  	$installer=",Installer";
  	}
  	*/
  
  	/*
  	 // default headers
  	$str="Companyname,Companyid,Website,Address1,Address2,Address3,City,Postcode,Region,Title,Firstname,"; // NEW STUFF
  	$str.="Surname,Position,Directno,Telephoneno,EmailAddress";
  	*/
  
  	//$str="Companyname	Companyid	Address1	City	Postcode	Region	Title	Firstname	";
  	$str="Companyname	Companyid	Address1	Address2	Address3	City	Postcode	Region	Title	Firstname	"; // NEW STUFF
  	$str.="Surname	Position	Telephoneno	EmailAddress";
  
  	// process includes xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
  
  	// Business Details (premises type, market sectors)
  	if(strstr($this->input->post('csv_include_checks'),'list-business-details')){
  		$omitted_fields = array('quarantined','origin_dsn');
  		$str.="	Addresstypes	Markets";
  	}
  	// Credit Manager (financial information where available)
  	if(strstr($this->input->post('csv_include_checks'),'list-business-credit')){
  		$includes_bc = true;
  		$str.="	Company-Number	Credit-Rating	Credit-Limit	CCJ's	Company-Type	Rating-Description";
  	}
  
  	//$str.= $installer; // fen only  // NEW STUFF
  
  	// Product Details (fabricate/buy in, materials, products, brands, volume)
  	if(strstr($this->input->post('csv_include_checks'),'list-business-products')){
  		$includes_bp = true;
  
  		// check dsn
  		if(!$dsn){$dsn = $this->input->post('dsn');}
  		$str.= $this->get_product_info("",$dsn,true,"tab"); // headers only
  	}
  	// Sales Manager (status, lead source, sales person, etc)
  	if(strstr($this->input->post('csv_include_checks'),'list-business-manager')){
  		$includes_bm = true;
  	}
  
  	// END process includes xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
  
  	$str.=$newline;
  
  	// FORMAT 4,  selected fields - 5 fields only
  	if($this->input->post('csv_choices') == 'format-email-list-quick'){
  		$str="Companyname	Title	Firstname	Surname	EmailAddress".$newline;
  	}
  
  	$row_store = array();
  	$str2 = $newline;
  	$lastcompid="";
  
  	// FORMAT 1: standard format (1 primary contact) DEFAULT CHOICE
  	// FORMAT 2: full format (all contacts) and
  	// FORMAT 3: Email format  and
  	// FORMAT 4: Email format (Quick, 4 fields)
  
  	foreach ($data_source as $row) // loop through rows
  	{
  		// CHECK FOR DUPLICATE RECORDS - ONLY USEFUL IF SORTED BY COMPANY IDS
  		/*
  		if($lastcompid == $row->companyid){
  		$lastcompid = $row->companyid;
  		continue;
  		}
  		*/
  
  		// demo account limit ?
  		if($this->session->userdata('demouser')){
  		if($demo_count >= DEMO_EXPORT_MAX){
  		continue;
  		}
  		}
  
  		// check for correct format
  			if($externaldata){ $compid = $row->companyid;}	else { $compid = $row['companyid']; }
  			if($externaldata){ $companyemail = $row->companyemail;}	else { $companyemail = $row['companyemail']; } // for format 3/4, later
  			
  			// FORMAT 3/4 ONLY ... if no company email address at all, drop record
  			if(($this->input->post('csv_choices') == 'format-email-list' || $this->input->post('csv_choices') == 'format-email-list-quick') && !$companyemail){
  				continue;
  			}
  
  			// remove tagged records
  			if($tagged_list_array !=""){
  					foreach ($tagged_list_array as $companyid) {
  			if($companyid == $compid){
  					$tagged_records_count++;
  					continue 2;
  					}
  	}
  	}
  
  	// check for valid companyemail for email exports (formats 3 and 4)
  	if($this->input->post('csv_choices') == 'format-email-list-quick' ||
  	$this->input->post('csv_choices') == 'format-email-list'){
  	if(empty($compid)) {
  	continue;
  }
  }
  
  // process fields in row
  		$tradingas_flag = false;
  		$tradingas_fix = "";
  				foreach ($row as $fieldname => $item)
  	{
  
  	// omit some fields
  					for($j=0; $j<count($omitted_fields); $j++){
  					if($fieldname == $omitted_fields[$j]){
  					continue 2; // for the second outer foreach loop
  }
  }
  
  // company name update if there is a trading name
  	if($externaldata){
  	if($row->companyname !="" && $row->tradingas !=""){
  		$tradingas_fix = $row->tradingas;
  		$tradingas_flag = true;
  		}
  		}else{
  		if($row['companyname'] !="" && $row['tradingas'] !=""){
  		$tradingas_fix = $row['tradingas'];
  		$tradingas_flag = true;
  }
  }
  
  // omit tradingas,companyregno
  if($fieldname == 'tradingas' || $fieldname == 'companyregno'){
  continue;
  }
  
  // format telephone numbers
  	if($fieldname == 'telephoneno' || $fieldname == 'directno'){
  
  	if($item){
  $item = '#'.$item;
  }
  }
  
  if($this->input->post('csv_choices') == 'format-email-list-quick'){  // FORMAT 4,  selected fields
  
  		if($fieldname == 'companyname') {
  
  		if($tradingas_flag){$item = $tradingas_fix;}
  
  $str .= $enclosure.str_replace($enclosure, $enclosure.$enclosure, htmlspecialchars_decode($item,ENT_QUOTES)).$enclosure.$delim;
  $row_store[$fieldname]=$enclosure.str_replace($enclosure, $enclosure.$enclosure, htmlspecialchars_decode($item,ENT_QUOTES)).$enclosure.$delim;
  }
  
  
  if($fieldname == 'title') {  // NEW STUFF
  $str .= $enclosure.str_replace($enclosure, $enclosure.$enclosure, $item).$enclosure.$delim;
  	$row_store[$fieldname]=$enclosure.str_replace($enclosure, $enclosure.$enclosure, $item).$enclosure.$delim;
  	}
  
  
  		if($fieldname == 'firstname') {
  		$str .= $enclosure.str_replace($enclosure, $enclosure.$enclosure, $item).$enclosure.$delim;
  		$row_store[$fieldname]=$enclosure.str_replace($enclosure, $enclosure.$enclosure, $item).$enclosure.$delim;
  	}
  
  	if($fieldname == 'surname') {
  		$str .= $enclosure.str_replace($enclosure, $enclosure.$enclosure, $item).$enclosure.$delim;
  		$row_store[$fieldname]=$enclosure.str_replace($enclosure, $enclosure.$enclosure, $item).$enclosure.$delim;
  		}
  
  		if($fieldname == 'companyemail') {
  		$str .= $enclosure.str_replace($enclosure, $enclosure.$enclosure, $item).$enclosure.$delim;
  			$row_store[$fieldname]=$enclosure.str_replace($enclosure, $enclosure.$enclosure, $item).$enclosure.$delim;
  		}
  		}
  		else{ // all fields
  
  		/*
  		// fen installer ?
  		if($dsn == 'ifd' || $dsn == 'roi'){  // NEW STUFF
  
  			$fen_installer='NO';
  			if($this->Search2->check_fen_installer($compid,$dsn)){
  			$fen_installer='YES';
  			}
  
  			$fenstr = $enclosure.str_replace($enclosure, $enclosure.$enclosure, $fen_installer).$enclosure.$delim;
  			}
  			*/
  
  			// process companyname
  			if($tradingas_flag && $fieldname == 'companyname'){$item = $tradingas_fix;}
  
  				$str .= $enclosure.str_replace($enclosure, $enclosure.$enclosure, htmlspecialchars_decode($item,ENT_QUOTES)).$enclosure.$delim;
  				// store this field
  				$row_store[$fieldname]=$enclosure.str_replace($enclosure, $enclosure.$enclosure, htmlspecialchars_decode($item,ENT_QUOTES)).$enclosure.$delim;
  }
  
  } // end of field processing
  
  	/*
  	// append fen installer ?
  	 if($dsn == 'ifd' || $dsn == 'roi'){
  	 $str .= $fenstr;  // NEW STUFF
  	 }
  	 */
  
  	 // check dsn
  	 if(!$dsn){$dsn = $this->input->post('dsn');}
  
  	 // process email / contacts - FORMATS 2,3,4 ONLY
  	 if($this->input->post('csv_choices') != 'format-standard'){
  	
  	 	if($this->input->post('csv_choices') == 'format-email-list'){  // FORMAT 3
  	 		$result = $this->Search2->getContacts($compid,$dsn,false,true);
  	 	}else{
  	 		$result = $this->Search2->getContacts($compid,$dsn,false,false);
  	 	}
  
  	 if($result){
  
  	 foreach ($result->result() as $row2) {
  
  	 if($this->input->post('csv_choices') == 'format-full-list'){  // FORMAT 2
  
  	 if(!empty($row2->title)) {$row_store['title'] = $row2->title.$delim;}
  	 if(!empty($row2->firstname)) {$row_store['firstname'] = $row2->firstname.$delim;}
  	 if(!empty($row2->surname)) {$row_store['surname'] = $row2->surname.$delim;}
  	 if(!empty($row2->position)) {$row_store['position'] = $row2->position.$delim;}
  
  	 foreach ($row_store as $value){
  	 $str2 .= $value;
  	 }
  
  	 $str2 .= $newline;
  	 $formatfulllist++;
  	 }
  
  	 if($this->input->post('csv_choices') == 'format-email-list'){  // FORMAT 3
  
  	 $email_flag=false;
  	 if(!empty($row2->email)){
  	 if(!empty($row2->title)) {$row_store['title'] = $row2->title.$delim;}
  	 if(!empty($row2->firstname)) {$row_store['firstname'] = $row2->firstname.$delim;}
  	 if(!empty($row2->surname)) {$row_store['surname'] = $row2->surname.$delim;}
  	 if(!empty($row2->position)) {$row_store['position'] = $row2->position.$delim;}
  	 if(!empty($row2->email)) {$row_store['companyemail'] = $row2->email.$delim;}
  	 $email_flag=true;
  	 }
  	 if($email_flag){
  	 		foreach ($row_store as $value){
  	 $str2 .= $value;
  	 }
  
  	 $str2 .= $newline;
  	 		$formatfulllist++;
  	 }
  	 }
  
  	 if($this->input->post('csv_choices') == 'format-email-list-quick'){  // FORMAT 4
  
  	 $email_flag=false;
  	 if(!empty($row2->email)){
  	 		if(!empty($row2->title)) {$row_store['title'] = $row2->title.$delim;}
  	 		if(!empty($row2->firstname)) {$row_store['firstname'] = $row2->firstname.$delim;}
  	 		if(!empty($row2->surname)) {$row_store['surname'] = $row2->surname.$delim;}
  	 		if(!empty($row2->email)) {$row_store['companyemail'] = $row2->email.$delim;}
  	 		$email_flag=true;
  	 }
  	 if($email_flag){
  	 foreach ($row_store as $fieldname => $value){ // select fields
  	 if($fieldname == 'companyname') {$str2 .= $value;}
  	 if($fieldname == 'title') {$str2 .= $value;}
  	 		if($fieldname == 'firstname') {$str2 .= $value;}
  	 		if($fieldname == 'surname') {$str2 .= $value;}
  	 		if($fieldname == 'companyemail') {$str2 .= $value;}
  	 }
  
  	 $str2 .= $newline;
  	 		$formatfulllist++;
  	 }
  	 }
  
  	 } // end of foreach loop
  
  	 // append to str
  	 		$str .= rtrim($str2); // rtrim removes newline
  
  	 		// reset
  	 $str2 = $newline;
  	 $row_store = array();
  	 } // end of contacts / email  - FORMATS 2,3,4
  
  	 } // end of if
  
  	 // process includes
  
  	 // credit data
  	 if($includes_bc){
  	 $str3 = $this->get_credit_info($compid);
  	 $str .= rtrim($str3);
  	 }
  
  	 //product data
  	 if($includes_bp){
  	 $str4 = $this->get_product_info($compid,$dsn,false,"tab");
  	 $str .= rtrim($str4);
  	 }
  
  	 // END process includes
  
  	 $str = rtrim($str);
  	 $str .= $newline;
  
  	 	$demo_count++;
  	 	$main_count++;
  
  	 } // end of outer loop (records)
  
  
  
  	 		// get totals
  	 		if($this->session->userdata('demouser')){
  	 		$totrec = $newline.'TOTAL RECORDS = ' .$demo_count;
  	 	}else{
  	 	$totrec = $newline.'TOTAL RECORDS = ' .($main_count+$formatfulllist-$tagged_records_count);
  	 }
  
  
  	 // START SEAN PRYCE OPTIMISATIONS
  
  	 		// Directory Structure to use
  	 	$directorystructure = XLS_FOLDER . $clientid;
  	 	$userIDstructure = XLS_FOLDER . $clientid . "/" . $userID;
  
  	 	$filename = XLS_FILENAME_GENERIC;
  
  	 	// If the directory doesn't exist - create it and also create a directory for the user who just built the list
  	 	if(!file_exists($directorystructure))
  	 {
  	 mkdir(XLS_FOLDER . $clientid, 0777);
  	 mkdir(XLS_FOLDER . $clientid . "/" . $userID, 0777);
  	 mkdir(XLS_FOLDER. $clientid ."/". $userID ."/". $timestamp, 0777);
  	 }
  	 // If the folder does exist, no need to create it but check the user folder on its own and create where necessary
  	 else
  	 {
  	 	if(!file_exists($userIDstructure))
  	 	{
  	 	mkdir(XLS_FOLDER. $clientid ."/". $userID, 0777);
  }
  else
  {
  	 	mkdir(XLS_FOLDER. $clientid ."/". $userID ."/". $timestamp, 0777);
  }
  }
  
  // Define the file that will be created
  $file = XLS_FOLDER. $clientid . "/" . $userID . "/" . $timestamp . "/" . $filename;
  
  // This prevents the random occurance of not being able to load the file
  ob_start();
  
  // Open file
  $current = file_get_contents($file);
  
  	 	ob_end_clean();
  
  	 	// Add info to the file
  	 	$current .= $str;
  
  	 	// get totals
  	 	$current .= $totrec;
  
  	 	// Write the contents back to the file
  	 	file_put_contents($file, $current);
  
  	 	// Send the data to utilities.php
  	 	echo XLS_FOLDER. $clientid . "/" . $userID . "/" . $timestamp . "/" . $filename;
  
  	 	// END SEAN PRYCE OPTIMISATIONS
  }

}
/* End of file ajax.php */

