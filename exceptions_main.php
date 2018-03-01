

<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>



         
    <br /><br /><div id="content">
              
            
              
             <div class="row">
                 
                 
                 
                 <div class="col-sm-3">
                     
                     <!--XXXXXXXXXXXXXXXXXXX  TABLE Exception flags  XXXXXXXXXX  -->
                     
                    <div id="exceptions-tables-flags" class="admin-exceptions-other dynamic">
                        
                     <input id='ex-flag' type="button" data-toggle="modal" data-target="#myModal-exception-flag" title="Add New" class="removeButton" value="+"/>
                     
                    <table class="table table-striped_">
                        
                    <caption><center><h3>Exception flags(<?php echo count($exceptionFlags); ?>)</h3></center></caption>
                        
                    <thead>
                      <tr>
                        <th>Description</th>
                        <th>Active</th>
                        <th>&nbsp;</th>
                        
                      </tr>
                      
                    </thead>  
                    
                     <tbody>  
                         
                        <?php if(!$exceptionFlags){
                            echo "<tr><td colspan='2'><center>No Data Available</center></td></tr>";
                          }else{
                         
                        foreach ($exceptionFlags as $row){ ?>
                        
                      <tr>
                          
                        <td><input size="10" maxlength="20" title="Click here to edit - Max 20 chars" readonly type="text" class="ex-flag-desc" id="<?php echo $row->exceptionflagid; ?>" value="<?php echo htmlentities($row->exceptionflagdescription); ?>"></td>
                        
                        
                          <td>
                              <select id="<?php echo $row->exceptionflagid;?>" class="ex-flag-yesno" >
                                  <option value="0" <?php  if($row->exceptionflagactive==0){echo "selected";}?> >No</option>
                                  <option value="1" <?php  if($row->exceptionflagactive==1){echo "selected";}?>  >Yes</option>
                              </select>
                          </td> 
                        
                        
                        
                        <td title="Delete flag" id="<?php echo $row->exceptionflagid; ?>" class="ex-flag-delete glyph glyphicon glyphicon-trash"></td>
                        
                      </tr>
                      
                      <?php }};?>
                      
                    </tbody>
                    
                   </table>             
                     
                  </div>  
                     
                     <!--XXXXXXXXXXXXXXXXXXX  TABLE Exception categories  XXXXXXXXXX  --> 
                     
                     <div id="exceptions-tables-categories" class="admin-exceptions-other dynamic">
                         
                      <input id='ex-category' type="button" data-toggle="modal" data-target="#myModal-exception-cat" title="Add New" class="removeButton" value="+"/>
                     
                    <table class="table table-striped_">
                        
                    <caption><center><h3>Exception Categories(<?php echo count($exceptionCategories); ?>)</h3></center></caption>
                        
                    <thead>
                      <tr>
                        <th>Category Description</th>                      
                        <th>&nbsp;</th>
                        <th>&nbsp;</th>
                      </tr>
                      
                    </thead>  
                    
                     <tbody>    
                        
                         
                         <?php if(!$exceptionCategories){
                            echo "<tr><td colspan='2'><center>No Data Available</center></td></tr>";
                          }else{
                         
                        foreach ($exceptionCategories as $row){ ?>
                         
                      <tr>
                          
                        <td><input size="10" maxlength="20" readonly title="Click here to edit - Max 20 chars" type="text" class="ex-cat-desc" id="<?php echo $row->exceptioncategoryid; ?>" value="<?php echo htmlentities($row->exceptioncategorydescription); ?>"></td>
                         
                        <td title="Delete Category" id="<?php echo $row->exceptioncategoryid; ?>" class="ex-cat-delete glyph glyphicon glyphicon-trash"></td>
                        
                      </tr>
                      
                      <?php }};?>
                      
                    </tbody>
                    
                   </table>             
                     
                  </div>
                     
                     <div id="sev-switch" class="admin-exceptions-other-sev-switch dynamic">
                         
                         Exception Severity: &nbsp;&nbsp;
                         <input type="radio" class="sev-switch-setting" name="sev-switch" id="1"  <?php echo ($exceptionSeverity_value =='1' )? 'checked' : ''; ?> value="1"> On &nbsp;
                         <input type="radio" class="sev-switch-setting" name="sev-switch" id="0"  <?php echo ($exceptionSeverity_value =='0')? 'checked' : ''; ?>  value="0"> Off
                         
                        
                     </div>
                     
                     <!--XXXXXXXXXXXXXXXXXXX  TABLE Exception severity  XXXXXXXXXX  --> 
                     
                     <div id="exceptions-tables-severity" class="admin-exceptions-other dynamic">
                         
                      <input id='ex-severity' type="button" data-toggle="modal" data-target="#myModal-exception-sev" title="Add New" class="removeButton" value="+"/>
                     
                    <table class="table table-striped_">
                        
                    <caption><center><h3>Exception Severity(<?php echo count($exceptionSeverity); ?>)</h3></center></caption>
                        
                    <thead>
                      <tr>
                        <th>Severity Value</th>                      
                        <th>&nbsp;</th>
                        
                      </tr>
                      
                    </thead>  
                    
                     <tbody>    
                        
                        <?php if(!$exceptionSeverity){
                            echo "<tr><td colspan='1'><center>No Data Available</center></td></tr>";
                          }else{
                         
                        foreach ($exceptionSeverity as $row){ ?>
                         
                         
                      <tr>
                          
                        
                        <td><input size="10" maxlength="20" readonly title="Click here to edit - Max 20 chars" type="text" class="ex-sev-desc" id="<?php echo $row->exceptionseverityid; ?>" value="<?php echo htmlentities($row->exceptionseverityvalue); ?>"></td>
                       
                       
                        <td title="Delete Severity" id="<?php echo $row->exceptionseverityid; ?>" class="ex-sev-delete glyph glyphicon glyphicon-trash"></td>
                        
                      </tr>
                      
                      <?php }};?>
                      
                    </tbody>
                    
                   </table>             
                     
                  </div>
                     
                 </div>
              
                 
                 <!--  CONTINUES WITH VIEW  exceptions_main_global  -->
    