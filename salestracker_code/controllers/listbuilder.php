<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Listbuilder extends CI_Controller {

	/**
	 *  Listbuilder controller.
	 */
	
	public function __construct(){
		parent::__construct();
		$this->load->model('Sales_manager');
		$this->load->model('Preferences2');
		$this->load->helper('get_status');
	}	
	
	public function index(){
		
		if($this->session->userdata('login_expiry') < time()){
			redirect('home/logout','refresh');
		}
		
		// reset list search criteria
		$this->session->set_userdata('search_vls','');
		
		$data['title'] = ' - List Builder';
		$data['include'] = 'listbuilder_common';
		$data['include_func'] = 'listbuilder_common_functions';
		$data['statuses'] = get_status_values(); //Uses helper function
		$data['listitems'] = $this->Preferences2->fetch_user_pref($field = 'display_fields');
		$data['listitems_template'] = unserialize(DEFAULT_LIST_FIELDS); //Uses constant
		
		// get list of databases
		$data['db_list'] = $this->Search2->getDataBaseList();
		
		$this->load->view('header',$data);
		$this->load->view('listbuilder',$data);
		$this->load->view('footer');
		
	}
    
    //Gets string of ids to exclude in same format as tagged records - AJAX
    public function get_exclude_ids(){
		
		//Clean dsn - if we need this function modded to only remove Do not contact,
		//mod this bit to accept a fixed value when the function is called!
		$dsn = trim($this->input->post('status_dsn'));
		
		//Create an array and string
		$results = array();
		$result_ids = "";
		
		//If ID exists
		if($this->input->post('list_id')){
			
			//Set ID and list type
			$listid = trim($this->input->post('list_id'));
			$listtype = "full";
		
		} else {
		
			//Grab the temp list ID from session
			$listid = $this->session->userdata('sql_id_temp');
		
			//Set listtype (in this case, "temp")
			$listtype = "temp";
			
		}
		
		//If there's statuses selected...
		if ($this->input->post('exclude_status') || $this->input->post('exclude_credit_rating') > 0){
			
			$alloptions = "";
			
			/*
			// get credit safe exclusions 
			if($this->input->post('exclude_credit_rating') > 0){
				
				// get list ids
				$row = $this->Sales_manager->get_one_group($listid);
				$record_ids = unserialize($row[0]->record_ids);

				//echo rtrim($record_ids,',');die;
				$temparray = array();
			    $temparray = explode(',', rtrim($record_ids,','));
			    
			    $tempstr="";
			    foreach ($temparray as $val) {
			    	$tempstr.= "'".$val."',";
			    }
			    
				$ids = $this->Sales_manager->filter_by_credit($temparray,$this->input->post('exclude_credit_rating'),$dsn);	
				print_r($ids);die;

				//If the query returns a result, append array to results array!
				if ($ids){		
					  $results = $ids;
				}
				
			}
			*/
			
			//Loop through the checkbox array
			foreach ($this->input->post('exclude_status') as $option){
				
				//Clean!
				$option = trim($option);
				
				//Add to options string
				$alloptions .= $option . ";";
				
				//Fetch array of IDs
				$query = $this->Sales_manager->get_ids_by_status($option, $dsn);
				
				//If the query returns a result, append array to results array!
				if ($query){
					$results[] = $query;
				}
				//print_r($results);die;
			}
			
			//Trim/clean alloptions string
			$alloptions = trim($alloptions, ';');
			
			//Loop through array of arrays
			foreach ($results as $result){
				
				//If there is something to loop through, loop again...
				if ($result){
					asort($result);
					foreach($result as $item){
						//Append ID to string!
						$result_ids .= "-" . $item->companyid;
					}
				}
				
			}
			
			//Trim off extraneous dashes
			$result_ids = trim($result_ids, "-");
			
			//Remove duplicates and reorder the IDs just to make them easier to read
			$result_ids = explode('-', $result_ids);
			array_unique($result_ids);
			asort($result_ids);
			$result_ids = implode('-',$result_ids);
			$result_ids = serialize($result_ids);
			
			//Load Search2 model
			$this->load->model('Search2');
			
			//Update db!
			if($this->Search2->update_sales_exclusions($listid, $alloptions, $result_ids, $listtype)){
				echo "Companies with relevant relationships will be removed from this list when exporting or once saved.";
				//DEBUG
				//echo " " . $listid . " " . $result_ids . " " . $alloptions;
			} else {
				echo "Error: relevant companies could not be excluded - they may have already been excluded";
			}
			
		//...Otherwise, remove all exclusions
		} else {
			
			$alloptions = NULL;
			$result_ids = NULL;
			
			if($this->Search2->update_sales_exclusions($listid, $alloptions, $result_ids, $listtype)){
				echo "All exclusions have been revoked - including 'Do not contact'!";
			} else {
				echo "Error: exclusions could not be revoked - they may have already been revoked";
			}
		
		}
		
	}
	
	//Creates a global note on all records in a list - AJAX
    public function create_global_note(){
		
		//Clean DSN
		$dsn = trim($this->input->post('status_dsn_globnote'));
		
		//Load Search2 and Viewlist models
		$this->load->model('Search2');
		$this->load->model('Viewlist');
		
		if($this->input->post('the_global_note')){
			
			//Escape input
			$note = mysql_real_escape_string($this->input->post('the_global_note'));
			
			//Create note array, populate
			$clean = array();
			$clean['note_detail'] = strip_tags($note); // remove tags		
			$clean['note_detail'] = htmlspecialchars($clean['note_detail'],ENT_QUOTES); // replace apostrophes
			$clean['note_type'] = 5;//5 is the code for global note
			$clean['origin_dsn'] = $dsn;
			
			//Saved or temp list
			if($this->input->post('list_id_globnote')){
				$listtype = "full";
				$listid = $this->input->post('list_id_globnote');
				$listid = trim($listid);
			} else {
				$listtype = "temp";
				$listid = $this->session->userdata('sql_id_temp');
			}
			
			//DEBUG
			//echo $note . " " . $listid . " " . $listtype;
			
			//Fetch array of IDs
			$record_ids = $this->Search2->fetch_list_ids($listid, $listtype);
			$record_ids = unserialize($record_ids);
			$record_ids = explode(',', trim($record_ids, ','));
			
			//DEBUG
			//echo "Record ID count: " . count($record_ids) . " <br />";
			//print_r($record_ids);
			
			//If list is temp, reset to 0 as that's what get_tagged_records relies on
			if ($listtype == "temp"){
				$listid_exc = 0;
			} else {
				$listid_exc = $listid;
			}
			
			//Get tagged records and sales manager exclusions, stick in array
			$exclude_ids = $this->Search2->get_tagged_records($listid_exc);
			$exclude_ids = explode('-', $exclude_ids);
			
			//DEBUG
			//echo "Exclude ID count: " . count($exclude_ids) . "<br />";
			//echo "Exclude IDs: "; print_r($exclude_ids); echo "<br />";
			
			//Remove all excluded IDs from the list's record IDs
			$loop_ids = array_diff($record_ids, $exclude_ids);
			$loop_count = count($loop_ids);
			
			//Counters
			$updates = 0;
			$errors = 0;
			
			//echo "Error: "; print_r($clean);
			
			foreach ($loop_ids as $recordid){
				if($this->Viewlist->create_new_note($recordid,$clean)){
					$updates++;
				} else {
					$errors++;
				}
			}
			
			//Create message
			$message = $updates . " records have been given this global note. ";
			
			//If errors inform user
			if ($errors > 0){
				$message .= $errors . " records could not be given this note due to technical errors.";
			}
			
			//Return message
			echo $message;
			
		} else {
			echo "Error: no note entered! Please enter a note.";
		}
		
	}
	
}


