<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>


      
               <!--XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX  TABLE Exception Management  XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX  -->
                 
               <div class="col-sm-9">
  
                 <div id="global-exceptions" class="admin-exceptions  dynamic">
                 
                      <table id="exceptions-table" class="display hover" cellpadding="0" cellspacing="0" border="0" width="100%">
                          
                          <caption><center>
                                  <h3>Exception Management &nbsp;(<?php echo count($exceptions); ?>)</h3>
                                   <input id='ex-management' type="button" title="Add New" class="removeButton" value="+"/>
                           </center></caption>
                         
                       <thead>
                          <tr>
                              <th>Category</th>                  
                              <th>Code</th>
                              <th>Description</th> 
                              <th>Default<br />Ex Sev</th>
                              
                              <?php foreach ($exceptionFlags as $row){                                
                                  echo "<th>".htmlentities($row->exceptionflagdescription)."</th>";  
                                }
                               ?>
                              <th title="Add a default message">M</th>
                              <th title="Remove an exception">E</th>          
                             
                          </tr>
                      </thead>

                      <tbody>
                         
                          <?php foreach ($exceptions as $row){ ?>
                          
                              <tr>
                                  
                                <td>
                                <select id="ex-catg-<?php echo $row->exceptionid; ?>" class="ex-catg-select"> 
                                                                      
                                    <?php $str=""; foreach($exceptionCategories as $val){ 
                                        if($val->exceptioncategoryid==$row->exceptioncategoryid){
                                          $str.= "<option value='".$val->exceptioncategoryid."' selected >".htmlentities($val->exceptioncategorydescription)."</option>";   
                                    }else{
                                        $str.= "<option value='".$val->exceptioncategoryid."'>".htmlentities($val->exceptioncategorydescription)."</option>";
                                       }
                                     }
                                     
                                     echo $str;
                                    ?>
                                    
                                </select>
                                </td>
                                
                                
                                <td><?php echo htmlentities($row->exceptioncode); ?></td>
                                <td><?php echo htmlentities($row->exceptiondescription); ?></td>
                                
                                
                                <td>
                                <select id="ex-sevg-<?php echo $row->exceptionid; ?>" class="ex-sevg-select"> 
                                      <option value="0">---</option>                               
                                    <?php $str=""; foreach($exceptionSeverity as $val){ 
                                        if($val->exceptionseverityid==$row->defaultexceptionseverity){
                                          $str.= "<option value='".$val->exceptionseverityid."' selected >".htmlentities($val->exceptionseverityvalue)."</option>";   
                                    }else{
                                        $str.= "<option value='".$val->exceptionseverityid."'>".htmlentities($val->exceptionseverityvalue)."</option>";
                                       }
                                     }
                                     
                                     echo $str;
                                    ?>
                                    
                                </select>
                                </td>
                                
                                <!-- START  EXCEPTION FLAGS-->
                                
                                <?php foreach ($exceptionFlags as $val){ 
                                    $checked="";
                                    
                                    foreach ($exceptionHasFlags as $flag){
                                       
                                       if($val->exceptionflagid==$flag->exceptionflagid && $row->exceptionid==$flag->exceptionid){ 
                                      
                                           $checked="checked";
                                           break 1;                                
                                       }
                                    }
                                      
                                 ?>                                
                                  
                                   <td><input class="ex-flag-set" id="flag-<?php echo $row->exceptionid.'-'.$val->exceptionflagid; ?>" type="checkbox" value="" <?php echo $checked; ?> /></td> 
                                
                                 <?php };?>    <!-- END  EXCEPTION FLAGS-->
                              
                                <td><a href="#"><span id="<?php echo $row->exceptionid; ?>"  title="<?php echo htmlentities($row->defaultwording); ?>" class="ex-message-set glyphicon glyphicon-list-alt"></span></a></td>
                                <td><a href="#"><span id="<?php echo $row->exceptionid; ?>" class="ex-row-delete glyphicon glyphicon-trash"></span></a></td>
                                
                                
                              </tr>

                         <?php };?>
                         
                    </tbody>
                    
                   </table>
                        
                
                 </div>
                                 
               </div>
                
           </div> <!-- <div class="container-fluid mimin-wrapper">  SEE HEADER -->        
     
      </div>   
      
      
      <!-- MODAL FORMS  START -->
      
      <!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXX Exception flag XXXXXXXXXXXXXXXXXXX -->
      
   <div class="modal fade" id="myModal-exception-flag" tabindex="-1" role="dialog" 
     aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            
            <!-- Modal Header -->
            <div class="modal-header">
                <button type="button" class="close" 
                   data-dismiss="modal">
                       <span aria-hidden="true">&times;</span>
                       <span class="sr-only">Close</span>
                </button>
                <h4 class="modal-title" id="myModalLabel">
                    Add a new Exception flag
                </h4>
            </div>
            
            <!-- Modal Body -->
            <div class="modal-body">
                
             <form id="modal-form-ex-flag" role="form">
                  <div class="form-group">
                    <label for="ex-flag">Enter a description</label>                                   
                    <input type="text" class="form-control" id="ex-flag"  maxlength="<?php echo SECTION_NAME_MAXLENGTH - 10;?>" name="ex-flag" placeholder="Enter a flag name here ..." data-toggle="tooltip" data-placement="left" title="Maximum length: <?php echo SECTION_NAME_MAXLENGTH - 10;?>" required />
                  </div>
                 <button id="first-modal-form-submit-ex-flag" type="submit" class="btn btn-primary">Save changes</button>
             </form>
                
            </div>
            
            <!-- Modal Footer -->
            <div class="modal-footer">
                   
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                      
                
            </div>
        </div>
    </div>
  </div>
     
      <!--  XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX  EXCEPTION Category   XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX --->
      
     <div class="modal fade" id="myModal-exception-cat" tabindex="-1" role="dialog" 
     aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            
            <!-- Modal Header -->
            <div class="modal-header">
                <button type="button" class="close" 
                   data-dismiss="modal">
                       <span aria-hidden="true">&times;</span>
                       <span class="sr-only">Close</span>
                </button>
                <h4 class="modal-title" id="myModalLabe2">
                    Add a new Category
                </h4>
            </div>
            
            <!-- Modal Body -->
            <div class="modal-body">
                
             <form id="modal-form-ex-cat" role="form">
                  <div class="form-group">
                    <label for="ex-cat">Enter a description</label>                                   
                    <input type="text" class="form-control" id="ex-cat" name="ex-cat" maxlength="<?php echo SECTION_NAME_MAXLENGTH - 10;?>" placeholder="Enter a category name here ..." data-toggle="tooltip" data-placement="left" title="Maximum length: <?php echo SECTION_NAME_MAXLENGTH - 10;?>" required />
                  </div>
                 <button id="first-modal-form-submit-ex-cat" type="submit" class="btn btn-primary">Save changes</button>
             </form>
                
            </div>
            
            <!-- Modal Footer -->
            <div class="modal-footer">
                   
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                      
                
            </div>
        </div>
    </div>
  </div>
      
        <!--  XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX  EXCEPTION Severity   XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX --->
      
     <div class="modal fade" id="myModal-exception-sev" tabindex="-1" role="dialog" 
     aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            
            <!-- Modal Header -->
            <div class="modal-header">
                <button type="button" class="close" 
                   data-dismiss="modal">
                       <span aria-hidden="true">&times;</span>
                       <span class="sr-only">Close</span>
                </button>
                <h4 class="modal-title" id="myModalLabe2">
                    Add a new Severity
                </h4>
            </div>
            
            <!-- Modal Body -->
            <div class="modal-body">
                
             <form id="modal-form-ex-sev" role="form">
                  <div class="form-group">
                    <label for="ex-sev">Enter a description</label>                                   
                    <input type="text" class="form-control" id="ex-sev" name="ex-sev" maxlength="<?php echo SECTION_NAME_MAXLENGTH - 10;?>" placeholder="Enter a Severity name here ..." data-toggle="tooltip" data-placement="left" title="Maximum length: <?php echo SECTION_NAME_MAXLENGTH - 10;?>" required />
                  </div>
                 <button id="first-modal-form-submit-ex-sev" type="submit" class="btn btn-primary">Save changes</button>
             </form>
                
            </div>
            
            <!-- Modal Footer -->
            <div class="modal-footer">
                   
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                      
                
            </div>
        </div>
    </div>
  </div>
      
        
      <!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXX Exception Management - add wording OR Add a new exception (the form can submitted in 2 ways - see exceptions.js) XXXXXXXXXXXXXXXXXXX -->
      
     <div class="modal fade" id="myModal-exception-default-wording" tabindex="-1" role="dialog" 
     aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            
            <!-- Modal Header -->
            <div class="modal-header">
                <button type="button" class="close" 
                   data-dismiss="modal">
                       <span aria-hidden="true">&times;</span>
                       <span class="sr-only">Close</span>
                </button>
                <h4 class="modal-title" id="form-title">
                    Add default wording
                </h4>
            </div>
            
            <!-- Modal Body -->
            <div class="modal-body">
                
             <form id="modal-form-ex-default-wording" role="form">
                  <div class="form-group">
                     
                      <!-- Add new Exception -->
                      <div class="ane hideall">
                        
                        <label for="cat_name">Enter Category</label>                       
                        <select class="form-control add-ex"  name="cat_name">                        
                            <?php  foreach($exceptionCategories as $row){ ?>?>
                              <option value="<?php echo $row->exceptioncategoryid; ?>"> <?php echo $row->exceptioncategorydescription; ?> </option>  
                            <?php };?>                     
                        </select><br />
                        
                        <label for="cat_code">Enter Code</label>
                        <input class="form-control add-ex"  maxlength="<?php echo EXCEPTION_CODE_MAXLENGTH;?>"  type="input" name="cat_code" id="cat_code" placeholder="Enter a Code name ..." data-toggle="tooltip" data-placement="left" title="Maximum length: <?php echo EXCEPTION_CODE_MAXLENGTH;?> chars" required />
                        
                        <label for="cat_desc">Enter Description</label>
                        <input class="form-control add-ex" maxlength="<?php echo SECTION_NAME_MAXLENGTH;?>"  type="input" name="cat_desc" id="cat_desc" placeholder="Enter a description ..." data-toggle="tooltip" data-placement="left" title="Maximum length: <?php echo SECTION_NAME_MAXLENGTH;?> chars" required />                   
                        
                         <br /><label for="check_flags">Select exception flag(s)</label>
                        <?php foreach ($exceptionFlags as $row){ ?>
                        
                            <div class="checkbox add-ex">
                               <label><input type="checkbox" name="check_flags[]" value="<?php echo $row->exceptionflagid;?>"><?php echo $row->exceptionflagdescription;?></label>
                            </div>
                       
                        <?php };?>
                                
                         
                         <br /><label for="select_sev">Select default severity</label>
                         
                         <div class="radio add-ex">
                            <label><input type="radio" name="select_sev" checked="checked" value="0">None</label>  
                         </div>
                        <?php foreach ($exceptionSeverity as $row){ ?>                        
                            <div class="radio add-ex">
                               <label><input type="radio" name="select_sev" value="<?php echo $row->exceptionseverityid;?>"><?php echo $row->exceptionseverityvalue;?></label>
                            </div>                       
                        <?php };?>
                         
                         
                      </div>
                    
                      
                      <!-- Add default wording -->
                     <div class="adw hideall">
                        <label for="ex-default-wording">Enter details</label>                                                    
                        <textarea maxlength="<?php echo DATAPOINT_NAME_MAXLENGTH;?>" class="form-control" rows="5" name="ex-default-wording" id="ex-default-wording" placeholder="Enter default wording here ..." data-toggle="tooltip" data-placement="left"  required ></textarea>
                        <input type="hidden" name="id" id="id" value="0"/>
                    </div>
                 
                  </div>
                 <button id="ex-default-wording-btn" type="submit" class="btn btn-primary">Save changes</button>
             </form>
                
            </div>
            
            <!-- Modal Footer -->
            <div class="modal-footer">
                   
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                      
                
            </div>
        </div>
    </div>
  </div>
      
      <!-- MODAL FORMS  END -->
      
    
    </div>  <!-- <div class="content">  -->


      
</html>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.0/jquery.min.js"></script>
 <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
 <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>

<script src="/public/asset/js/exceptions.js"></script>

 