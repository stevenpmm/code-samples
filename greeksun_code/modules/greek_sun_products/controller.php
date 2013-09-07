<?
	class module_greek_sun_products_core_controller extends controller{

		// Called by core index.php
		public function client(){
		
			$this->parent->init_view();
			$this->parent->init_model();
			
		//	echo '<PRE>'.print_r($_POST).'</PRE>';
		
			$request = $this->parse_url();  // dump request array	
			
			// validate URI fragments as alpha numeric and hyphen ONLY
			if(!empty($request['action'])){
				
				if(!preg_match('/^([a-zA-Z0-9-]*)$/', $request['action'])){   
				   echo '<h2>Error: invalid parameters in the address bar - page cannot be displayed</h2>';
				   exit;	
				}
				else{
					if(count($request['arguments']) > 0){
					   foreach($request['arguments'] as $arg){
					   	  if(!preg_match('/^([a-zA-Z0-9-]*)$/',$arg)){
				             echo '<h2>Error: invalid parameters in the address bar - page cannot be displayed</h2>';
				             exit;
					      }
					   }
				    }
			    }
			}    
		
			// global data
			$results['islands'] = $this->model->get_island_names();	 // get island list
			template::append( $this->view->render_left_panel($results), 'search_form'); 
			
			
			// get islands and groups for left panel
			template::append( $this->view->render_left_panel_listing($results), 'grk_islands_and_groups');  

			
			
// XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX

			// get accomodation listing
			if($request['module'] == 'resort-view') {

				// fetch main template
				template::name('search_results');  // from profiles/2248_Greek_Sun/templates/ folder
				
				// get the data - an accomodation listing in this case
				$results['data'] = $this->model->get_search_results(); // model.php, line 41
				
				// pass data to view
				template::append( $this->view->render_search_results($results) ); // view.php, line 12
				
			}

// XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX

			
		    // controls page http://www.greeksun.co.uk/island-hopping-holidays
		    if($request['module'] == 'island-hopping-holidays') {
				template::name('island_hopping');  // from profile ... templates folder
				$results['data'] = $this->model->get_island_hopping_results();
				$results['groups'] = $this->model->get_island_group_names();
				template::append( $this->view->render_search_results_islands_hopping($results) );
			}
	
			// controls page http://www.greeksun.co.uk/holidays-your-way
		    if($request['module'] == 'holidays-your-way') {
				template::name('holidays_your_way');  // from profile ... templates folder
				$results['data'] = $this->model->get_island_results();
				template::append( $this->view->render_holidays_your_way($results),'holidays_your_way' );
			} 
			
			// etc ... more pages ...

		} // end of function		
		
	}// end of class		

?>