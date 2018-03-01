<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Exceptions_data extends CI_Model {
    
    protected $db_ref = 'BB4C03E6-BB3B-3455-7CEE-11C291DC5740';

        public function __construct()
        {
            parent::__construct();
                
        }
        
        
        public function get_exceptions_data() 
        {
                 
                $sql="SELECT 
                    
                    a.exceptionid,
                    a.exceptioncategoryid,
                    b.exceptioncategorydescription,
                    a.exceptioncode,
                    a.exceptiondescription,
                    a.defaultexceptionseverity,
                    c.exceptionseverityid,
                    a.defaultwording
                    
                    FROM exception a  
                    LEFT JOIN exceptioncategory b ON a.exceptioncategoryid = b.exceptioncategoryid
                    LEFT JOIN exceptionseverity c ON a.defaultexceptionseverity = c.exceptionseverityid 
                    
                    WHERE a.db_ref=b.db_ref AND a.db_ref='".$this->db_ref."' ORDER BY b.exceptioncategorydescription ASC;";
                
                $query = $this->db->query($sql);                
                return $query->result(); // returns an array
        }
        
        
        
        public function get_exception_has_flags(){
             
                $this->db->order_by('exceptionflagid', 'ASC');
                $this->db->where('db_ref',$this->db_ref);                   
                $query = $this->db->get('exception_has_flag');
                return $query->result(); // returns an array
         }
        
         public function get_exception_flags(){
             
                $this->db->order_by('exceptionflagid', 'ASC');
                $this->db->where('db_ref',$this->db_ref);                   
                $query = $this->db->get('exceptionflag');
                return $query->result(); // returns an array
         }
         
         public function get_exception_categories(){
             
                $this->db->order_by('exceptioncategorydescription', 'ASC');
                $this->db->where('db_ref',$this->db_ref);                   
                $query = $this->db->get('exceptioncategory');
                return $query->result(); // returns an array
        } 
        
        public function get_exception_severity(){
             
                $this->db->order_by('exceptionseverityvalue', 'ASC');
                $this->db->where('db_ref',$this->db_ref);                   
                $query = $this->db->get('exceptionseverity');
                return $query->result(); // returns an array
        } 
        
        
        // ajax functions for Exception operations
        
        // UPDATES
         public function update_exception_flag($id,$data){
             
             $this->db->where('exceptionflagid', $id);
            $this->db->where('db_ref',$this->db_ref);
            $this->db->update('exceptionflag', $data); 
           
         }
         
          public function update_exception_category($id,$data){
             
             $this->db->where('exceptioncategoryid', $id);
            $this->db->where('db_ref',$this->db_ref);
            $this->db->update('exceptioncategory', $data); 
           
         }
         
         public function update_exception_severity($id,$data){
             
             $this->db->where('exceptionseverityid', $id);
            $this->db->where('db_ref',$this->db_ref);
            $this->db->update('exceptionseverity', $data); 
           
         }
         
         
         
         // DELETIONS
         public function delete_exception_severity($id){
             
              $this->db->where('exceptionseverityid',$id);
              $this->db->where('db_ref',$this->db_ref);
              $this->db->delete('exceptionseverity'); 
           
         }
         
          public function delete_exception_flag($id){
             
              $this->db->where('exceptionflagid',$id);
              $this->db->where('db_ref',$this->db_ref);
              $this->db->delete('exceptionflag'); 
              
              // update table exception_has_flag
              $this->db->where('exceptionflagid',$id);
              $this->db->where('db_ref',$this->db_ref);
              $this->db->delete('exception_has_flag'); 
           
         }
         
          public function delete_exception_category($id){
             
              $this->db->where('exceptioncategoryid',$id);
              $this->db->where('db_ref',$this->db_ref);
              $this->db->delete('exceptioncategory'); 
           
         }
         
         // CREATE
         public function add_exception_flag($data){
             
             
             $this->db->insert('exceptionflag', $data);  
           
         }
         
         public function add_exception_category($data){
             
             
             $this->db->insert('exceptioncategory', $data);  
           
         }
         
         public function  addexception_severity($data){
             
             
             
             $this->db->insert('exceptionseverity', $data);  
           
         }
         
         public function get_table_max_id($tablename,$idname){
             
                $this->db->select_max($idname);
                $this->db->where('db_ref',$this->db_ref);
                $this->db->from($tablename);
                $query = $this->db->get();
                return $query->result();
         }
        
         public function set_exception_severity_switch($settingdescription,$data){
               
           $this->db->where('settingdescription', $settingdescription);
           $this->db->where('db_ref',$this->db_ref);
           $this->db->update('setting', $data);
         }
         
         //get settingvalue from setting description - a generic function
         public function get_setting_value($settingdescription){
             
                $this->db->select('settingvalue');
                $this->db->where('db_ref',$this->db_ref); 
                $this->db->where('settingdescription', $settingdescription);
                $query = $this->db->get('setting');
                return $query->result(); // returns an array
         }
         
         
         
         
         //  EXCEPTION MANAGEMENT OPERATIONS
         
         public function add_exception_management_flag($data){
             
             
             $this->db->insert('exception_has_flag', $data);  
           
         }
         
         public function delete_exception_management_flag($data){ 
             
              $this->db->where('exceptionid',$data['exceptionid']);
              $this->db->where('exceptionflagid',$data['exceptionflagid']);
              $this->db->where('db_ref',$this->db_ref);
              $this->db->delete('exception_has_flag'); 
           
         }
         
          public function update_default_wording($id,$data){
                
             $this->db->where('exceptionid', $id);
            $this->db->where('db_ref',$this->db_ref);
            $this->db->update('exception', $data); 
           }
           
          public function  addexception($data){
             
             $this->db->insert('exception', $data);       
         } 
           
         
         public function delete_exception_global($id){
             
              $this->db->where('exceptionid',$id);
              $this->db->where('db_ref',$this->db_ref);
              $this->db->delete('exception'); 
           
              // update table exception_has_flag
              $this->db->where('exceptionid',$id);
              $this->db->where('db_ref',$this->db_ref);
              $this->db->delete('exception_has_flag'); 
         }
         
         public function update_exception_severity_global($id,$data){
             
            $this->db->where('exceptionid', $id);
            $this->db->where('db_ref',$this->db_ref);
            $this->db->update('exception', $data); 
           
         }
        
         public function update_exception_category_global($id,$data){
             
            $this->db->where('exceptionid', $id);
            $this->db->where('db_ref',$this->db_ref);
            $this->db->update('exception', $data); 
           
         }
         
 
         
} // END OF CLASS  