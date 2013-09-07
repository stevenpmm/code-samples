<?
	$_ENV['api_methods']['greek_sun_products']['model'][] = 'get_search_results_names';
	$_ENV['api_methods']['greek_sun_products']['model'][] = 'get_search_results';
	
	class module_greek_sun_products_core_model {

		function __construct($parent){
			$this->parent = $parent;
		}
		
		// ajax function called by js in view->render_search_results_islands_slider()
		function get_island_results_slider($ratings){
			
			$values = explode(',', $ratings);
			
			// var param (from ajax call) = be+','+si+','+ea+','+wa+','+sc+','+ex;
			$orderby_values = array('rating_beaches' => $values[0],
			                        'rating_sights' => $values[1],
			                        'rating_eating' => $values[2],
									'rating_walking' => $values[3],
									'rating_scenery' => $values[4],
									'rating_excursions' => $values[5]
									);
		
			arsort($orderby_values, SORT_NUMERIC); // re-order array with values going from HIGH priority to LOW (arsort does a reverse sort keeping KEY association)

			$SQL="SELECT * FROM `grk_islands` a, `grk_islands_overview` b WHERE a.island_id = b.island_id ORDER BY ";
			while (current($orderby_values)) {	   
			        $SQL.= key($orderby_values).' DESC,';	   
			        next($orderby_values);
			}
	
	        return db::select(rtrim($SQL, ','));	
		}
		

// XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX

		
		// called by controller (ref: get accomodation listing)
		function get_search_results() {
			
			$cmd="SELECT * FROM `grk_islands_accommodation`";
			
			// $_POST data here - refine SQL
			// sanitise post data
			if($_POST['submit_type'] == 'resorts')
			{
				$SQL="";
				foreach($_POST AS $name => $value){
					if($name == 'btnSubmit' || 
					   $name == 'stay' ||
					   $name == 'submit_type') 
					   continue;
					   
					if($name == 'pool'){
					    $SQL.= ' pool="yes" AND';
					  }    
					else if($name == 'beach'){
						$SQL.= ' beach="yes" AND';
					  }
					else{ 
					  if($value){	  	
	                    $SQL.= ' '.$name.'="'.$value.'" AND';
					  }
					}
				}			
				if(!empty($SQL)){
				  $cmd.= ' WHERE '.rtrim($SQL, "A..Z");
				}    
			}
	
			return db::select($cmd." ORDER BY name");
		}	
	
		// an ajax function called within the view - line 30
	    function get_search_results_names($id) { // ajax call
			return db::select("SELECT `id`, `name` FROM `grk_islands_resort` WHERE `island_id` = {$id} ORDER BY name");
		}
		
		
// XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX		
		
		// other functions .....
		
		
		// validation (wrapper) functions for booking form
	    function validate_alpha_space($data){
			
	    	$ret_value="";
			if(!validation::check_alpha_space($data)){
				$ret_value = " invalid entry";
			}
			return $ret_value;
		}
		
	    function validate_alphanum_space($data){
			
	    	$ret_value="";
			if(!validation::check_alphanum_space($data)){
				$ret_value = " invalid entry";
			}
			return $ret_value;
		}
		
	    function validate_email($data){
			
	    	$ret_value="";
			if(!validate::email($data)){
				$ret_value = " invalid email";
			}
			return $ret_value;
		}
		
	   function validate_postcode($data){
			
	    	$ret_value="";
			if(!validation::check_UK_postcode($data)){
				$ret_value = " invalid post code (try uppercase)";
			}
			return $ret_value;
		}
		
	   function validate_numbers($data){
			
	    	$ret_value="";
			if(!validation::check_number($data)){
				$ret_value = " invalid number (no spaces)";
			}
			return $ret_value;
		}
		
	}
?>