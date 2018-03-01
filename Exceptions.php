<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Exceptions extends CI_Controller {
    
    protected $db_ref = 'BB4C03E6-BB3B-3455-7CEE-11C291DC5740';
    
    
        public function index(){
            
            // note: exceptions is a global model
            $data['exceptions'] = $this->exceptions_data->get_exceptions_data();
            $data['exceptionFlags'] = $this->exceptions_data->get_exception_flags();
            $data['exceptionHasFlags'] = $this->exceptions_data->get_exception_has_flags();
            
            $data['exceptionCategories'] = $this->exceptions_data->get_exception_categories();
            $data['exceptionSeverity'] = $this->exceptions_data->get_exception_severity();
            
            //get settingvalue from setting description FROM setting table
            $data['exceptionSeverity_value'] = $this->exceptions_data->get_setting_value('exceptionSeverity');
            $data['exceptionSeverity_value'] = $data['exceptionSeverity_value'][0]->settingvalue;
            

            $this->load->view('header');
            $this->load->view('exceptions_main',$data);
            $this->load->view('exceptions_main_global',$data);
        }
    
       
        // ajax functions for Exception operations
        
         public function update_exception_flag(){
              
              $data = array('exceptionflagdescription' =>$this->input->post('exceptionflagdescription')); 
            
              $this->exceptions_data->update_exception_flag($this->input->post('exceptionflagid'),$data);  
           
          }
          
          public function update_exception_flag_active(){
              
              $data = array('exceptionflagactive' => $this->input->post('exceptionflagactive')); 
            
              $this->exceptions_data->update_exception_flag($this->input->post('exceptionflagid'),$data);  
           
          }
          
          public function update_exception_category(){
              
              $data = array('exceptioncategorydescription' =>$this->input->post('exceptioncategorydescription')); 
            
              $this->exceptions_data->update_exception_category($this->input->post('exceptioncategoryid'),$data);  
           
          }
          
          public function update_exception_severity(){
              
              $data = array('exceptionseverityvalue' =>$this->input->post('exceptionseverityvalue')); 
            
              $this->exceptions_data->update_exception_severity($this->input->post('exceptionseverityid'),$data);  
           
          }
        
       // delete flags,categories and severities 
        
        public function delete_exception_others(){
              
             
               //print_r($_POST);die;
               
               
               switch($this->input->post('type')){
                    case "ex-flag-delete":
                        $this->exceptions_data->delete_exception_flag($this->input->post('id'));
                        break;
                    case "ex-cat-delete":
                        $this->exceptions_data->delete_exception_category($this->input->post('id'));
                        break;
                    case "ex-sev-delete":
                        $this->exceptions_data->delete_exception_severity($this->input->post('id'));
                        break;
                    
                }
           
          }
          
          
          public function process_exception_others_data(){
              
              
              parse_str($this->input->post('data'), $output);
              //print_r($output['ex-flag']);die;
              //print_r($_POST);die;
              
              
              
              switch($this->input->post('id')){
                  
                 case "modal-form-ex-flag":
                                               
                    $id=$this->exceptions_data->get_table_max_id('exceptionflag','exceptionflagid');
                    $newid = $id[0]->exceptionflagid;
                    ++$newid;
                        
                     $data = array(
                     'db_ref' => $this->db_ref,                    
                     'exceptionflagdescription' => $output['ex-flag'],
                     'exceptionflagid' => $newid
                     );                        
                        $this->exceptions_data->add_exception_flag($data);
                        break;
                    
                 case "modal-form-ex-cat":
                        
                        $id=$this->exceptions_data->get_table_max_id('exceptioncategory','exceptioncategoryid');
                        $newid = $id[0]->exceptioncategoryid;
                        ++$newid;
                    
                       $data = array(
                       'db_ref' => $this->db_ref,                    
                       'exceptioncategorydescription' => $output['ex-cat'],
                       'exceptioncategoryid' => $newid   
                       );
                        $this->exceptions_data->add_exception_category($data);
                        break;
                    
                 case "modal-form-ex-sev":
                     
                        $id=$this->exceptions_data->get_table_max_id('exceptionseverity','exceptionseverityid');
                        $newid = $id[0]->exceptionseverityid;
                        ++$newid;
                        
                        $data = array(
                       'db_ref' => $this->db_ref,                    
                       'exceptionseverityvalue' => $output['ex-sev'],
                       'exceptionseverityid' => $newid 
                       );                     
                        $this->exceptions_data->addexception_severity($data);
                        break;
                    
                }
          }
        
          
           public function set_exception_severity_switch(){
               
               $data = array('settingvalue' =>$this->input->post('settingvalue'));
               
               //print_r($_POST);die;
               
               $this->exceptions_data->set_exception_severity_switch(($this->input->post('settingdescription')),$data);
           }
           
           
           public function get_exception_severity_switch(){
               
               $data['exceptionSeverity_value'] = $this->exceptions_data->get_setting_value('exceptionSeverity');
               $data['exceptionSeverity_value'] = ($data['exceptionSeverity_value'][0]->settingvalue);
               echo $data['exceptionSeverity_value'];
           }
           
           
           
           //  EXCEPTION MANAGEMENT OPERATIONS - table Exceptions
           
            public function update_exception_management_flag(){
                
               // print_r($_POST);die;
                
                if($this->input->post('status')==0){
                    //print_r($_POST);die;
                    $this->exceptions_data->delete_exception_management_flag($_POST);                  
                }else{              
                $id=$this->exceptions_data->get_table_max_id('exception_has_flag','exceptionhasflagid');
                $newid = $id[0]->exceptionhasflagid;
                ++$newid;
                
                 $data = array(
                    'db_ref' => $this->db_ref,
                    'exceptionhasflagid' => $newid,  
                    'exceptionid' => $this->input->post('exceptionid'), 
                    'exceptionflagid' => $this->input->post('exceptionflagid')       
                  );
                 $this->exceptions_data->add_exception_management_flag($data);
               }
           }
           
           
           public function update_default_wording(){ // it can also delete an exception + dependencies
               
               parse_str($this->input->post('data'), $output);   
               
               //print_r($output);die;
               
               
                if(isset($output['id']) && $output['id'] > 0){ // update def wording                     
                    $data = array('defaultwording' => $output['ex-default-wording']);        
                    $output['id'] = $output['id'];            
                    $this->exceptions_data->update_default_wording($output['id'],$data);
               }else{ // insert new exception
                   $id=$this->exceptions_data->get_table_max_id('exception','exceptionid');
                    $newid = $id[0]->exceptionid;
                    ++$newid;
                
                 $data = array(
                    'db_ref' => $this->db_ref,
                    'exceptionid' => $newid,  
                    'exceptioncode' =>$output['cat_code'], 
                    'exceptiondescription' => $output['cat_desc'],
                    'exceptioncategoryid' => $output['cat_name']                    
                  );
                  //print_r($data);die;
                   $this->exceptions_data->addexception($data);
                   
                 // update exception_severity
                    $data = array('defaultexceptionseverity' => $output['select_sev']);            
                    $this->exceptions_data->update_exception_severity_global($newid,$data);  
                   
                   // update_exception_management_flags ??                    
                   if(isset($output['check_flags'])){
                       
                       $exceptionid = $newid; // back up
                       foreach ($output['check_flags'] as $val){
                           
                            $id=$this->exceptions_data->get_table_max_id('exception_has_flag','exceptionhasflagid');
                            $newid = $id[0]->exceptionhasflagid;
                            ++$newid;

                             $data2 = array(
                                'db_ref' => $this->db_ref,
                                'exceptionhasflagid' => $newid,  
                                'exceptionid' =>  $exceptionid, 
                                'exceptionflagid' => $val       
                              );
                             $this->exceptions_data->add_exception_management_flag($data2);
                           
                           
                           
                       } // end of loop
                   }  // end of if
                   
               }  // end of else            
           }
           
           
           
          public function delete_exception_global(){
             
              $this->exceptions_data->delete_exception_global($this->input->post('id'));            
          }  
            
          
          
          public function update_exception_category_global(){
              
              $data = array('exceptioncategoryid' => $this->input->post('exceptioncategoryid')); 
            
              $this->exceptions_data->update_exception_category_global($this->input->post('exceptionid'),$data);  
           
          }
          
          public function update_exception_severity_global(){
              
              $data = array('defaultexceptionseverity' => $this->input->post('defaultexceptionseverity')); 
            
              $this->exceptions_data->update_exception_severity_global($this->input->post('exceptionid'),$data);  
           
          }
                       
 } // END OF CLASS 