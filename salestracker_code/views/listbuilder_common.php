<!-- CONTAINS LIST BUILDER PAGE 3 CODE THAT IS ALSO USED IN SAVED LISTS-->
<?php // Bit of a hack, prevents the popups from appearing briefly while page refreshes ?>
<?php if (strstr(uri_string(),'listbuilder') || $listitems == "" || $this->session->userdata('display_flag') == 'y'){ //Sets whether to update view... ?>

              <div id="listbuilder-step3">  
					 <div class="clear">&nbsp;</div>
						<div id="listbuilder-left">
						    <input type = "hidden" class = "hidden" id="selected-criteria-hidden" name = "selected-criteria-hidden" value = "" />
							<div class="box" id="selected-criteria">
							
							<?php if($mycriteria){
								
							    echo $mycriteria;
								
							}else{ ?>
							    
								<h2 class="boxhead">Selected Criteria</h2>
								<!--  
								<h3>No selections ...</h3>	
								-->	
							<?php }?>	
													
							</div>
						</div>
                       
                       <div id="listbuilder-right">
							<div id="listbuilder-actions" class="box">
								<h2 id="lb_options_title" class="boxhead">List Builder Options</h2>
								<p>What would you like to do with your list?</p>
								<?php if(strstr(uri_string(),'savedlists')) { ?>
									<p><b><a href="<?php echo base_url().'savedlists/viewlist/'.$id; ?>">Return to viewing the full list</a></b></p>
								<?php } ?>
								<table>
									<?php if(!strstr(uri_string(),'savedlists')) { ?>
										<tr>
											<td>View the list <br /><input id="view-button" class="view-button" type="button" value="View" /></td>
											<td>Save this list <br /><a class="fancybox" href="#popup-listname"><input id="save-button" class="save-button" type="button" value="Save" /></a></td>
											<td>Finish and return to dashboard <br /><a href="/home"><input class="dashboard-button" type="button" value="Dashboard" /></a></td>
										</tr>
									<?php } ?>
									<tr>
									    <!-- 
										<td>Exclude certain records <br /><a class="fancybox" href="#popup-exclude"><input class="exclude-button" type="button" value="Exclude" /></a></td>
										 
										<td>Add a note/document to the <br/>records on this list <br /><a class="fancybox" href="#popup-note"><input id="add-button" class="add-button" type="button" value="Add" /></a></td>
										
										<td>Print this list <br /><input class="print-button" type="button" value="Print" /></td>
										-->
									</tr>
									<tr>
										<td>Export to a CSV file <br /><a class="fancybox" href="#popup-export"><input id="csv-button" class="csv-button" type="button" value="CSV" /></a></td>
										<td>Export to Microsoft Excel Workbook <br /><a class="fancybox" href="#popup-export"><input class="excel-button" id="excel-button" type="button" value="Excel" /></a></td>
										<!--  
										<td>Export to XML feed <br /><input class="xml-button" id="xml_export" type="button" value="XML" />&nbsp;<img id="ajax-anim_2" height="15px" height="15px" class="ajax-anim"  src="/media/images/ajax-loader.gif"></td>
									    -->
									</tr>
									<?php if(strstr(uri_string(),'savedlists')) { ?>
									<tr>
										<td>Push this list to another user <br /><a class="fancybox" href="#popup-push-list"><input id="push-button" class="push-button" type="button" value="Push" /></a></td>
										<!--
										<td>Push this list to Insight Data <br /><input class="push-id-button" type="button" value="Push to Insight Data" /></td>
										<td>Push this list to other software <br /><a class="fancybox" href="#popup-export-other"><input class="other-button" type="button" value="Other" /></a></td>
									     -->
									</tr>
									<?php } ?>
								</table>												
								<div class="clear">&nbsp;</div>
							</div>
						</div>
					
						
			<!-- START page 3 pop ups -->	
						    <input type = "hidden" class = "hidden" name = "csv_choices" id = "csv_choices" value = "" />  
						    <input type = "hidden" class = "hidden" name = "csv_include_checks" id = "csv_include_checks" value = "" />  
							<div id="popup-export" class="page3-floating-divs">
								<h2 class="boxhead">Export list to a standard CSV file</h2>  
								<h3><strong>List format</strong></h3>
								<ul>
									<li><input type="radio" id="format-standard" name="csv" value="format-standard" checked /> Standard Format. One record per company (primary contact only)</li>
									<li><input type="radio" id="format-full-list" name="csv" value="format-full-list" /> Full List Format. One record per contact name (a record is listed for every contact)</li>
									<li><input type="radio" id="format-email-list" name="csv" value="format-email-list" /> Email List Format. One record per email address (company and individual emails with full company details) </li>
								    <li><input type="radio" id="format-email-list-quick" name="csv" value="format-email-list-quick" /> Quick Email Format. One record per email address (company and individual emails with abridged details) </li>
								</ul>
								<h3><strong>Also include:</strong></h3>
								<div class="popup-export-column">
									<ul>
										<li><input type="checkbox" class="csv_includes" id="list-business-details" name="csv_includes[]" value="list-business-details" /> Business Details (premises type, market sectors etc)</li>
										<li><input type="checkbox" class="csv_includes" id="list-business-products" name="csv_includes[]" value="list-business-products" /> Product Details (fabricate/buy in, materials, products, brands, volume)</li>
										<!--  
										<li><input type="checkbox" class="csv_includes" id="list-business-manager" name="csv_includes[]" value="list-business-manager" /> Sales Manager (status, lead source, sales person, etc)</li>
										
										<li><input type="checkbox" class="csv_includes" id="list-business-credit" name="csv_includes[]" value="list-business-credit" /> Credit Manager (financial information where available)</li>
									    -->
									</ul>
									<p style="color: #ff0000;padding-left:20px;" id="csv_export_message"></p>
								</div>
								
								<input class="export-button" id="csv_submit" name="csv_submit" type="button" value="csv_submit" />
								<p><img class="ajax-animation ajax-anim"  id="ajax-anim_3" src="/media/images/ajax-loader.gif"></p>
								<div class="clear">&nbsp;</div>
							</div>
							
							<div id="popup-export-other" class="page3-floating-divs">
							<h2 class="boxhead">Export list for use in other software</h2>
							<input class="datasetup-button" type="button" value="Data Setup" /><p class="note">If you haven't yet created the format and fields for your software, set it up first. This will ensure that all future exports use the same data sets.</p>
							<h3>Export restrictions</h3>
							<span class="full-data"><p><input type="checkbox" id="full-data-set" name="full-data-set"/> Full data set&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>or</b></p></span>
							<div id="popup-export-restrictions">
								<p><input type="checkbox" id="inc-changed-records" name="inc-changed-records"/> Only include records that have had changes, since</p>
								<p><input type="checkbox" id="inc-added-records" name="inc-added-records"/> Only include records that have been added, since</p>
								<p><input type="checkbox" id="mark-deleted-records" name="mark-deleted-records"/> Mark records that have been quarantined / deleted, since</p>
							</div>
							<div id="popup-export-range">
								<p><input class="lastoutput-button" type="button" value="Last Output" /> <br /><b>or</b> <br />
								   Date: <input type="text" id="datepicker" size="12" value="click here">
								</p>
							</div>
							<input class="export-button" type="button" value="Export" />
							<div class="clear">&nbsp;</div>
						   </div>
							
							<div id="popup-listname" class="page3-floating-divs">
							<h2 style="text-align:left;" class="boxhead">Save this list</h2>
							<h3>Enter a name for this list:</h3>										
							<input title="list name can only contain letters, numbers, dots, spaces and hyphens" type="text" id="listname" size="20">
							<br />
							<input style="margin-top:5px;" class="save-button" type="button" id="listname_button" value="Save" onClick="javascript:$.fancybox.close();" />						
							<div class="clear">&nbsp;</div>
							</div>
							
							
							<script>

							$(document).ready(function() {

								
								$('#csv_submit').click(function() { 

									$('p#csv_export_message').text('The export may take a few minutes ...');
									$('#csv_submit').hide();
										
								 });
								
								$('#push-button').click(function() { 

									$.ajax({
									      type: "POST",
									      url: "/index.php/ajax/getUserList",  
									      success: function(html){
											if(html.indexOf("Error") != -1){
									             alert(html);
										      }	
									    	else{
									    	     $('#push_list').empty().append(html);	
									    	}
									       }
									    });               
									 return true;
							    	   		   
					  		    });

								$('#pushname_button').click(function() { 

									var users = "";

									// sanity check
									if($('.pushusers:checked').length == 0){
										alert('Error: no users selected');
										return false;
									}

									// get users
					                 $('.pushusers:checked').each(function() {
					 	 		    	    users+=$(this).val()+'-';		    	   
					 		    	 });
									
									$.ajax({
									      type: "POST",
									      url: "/index.php/ajax/pushUserList/"+users,  
									      success: function(html){
										     alert(html);
									       }
									    });               
									 return true;
							    	   		   
					  		    });
					  		    
							});

							</script>
							
							<div id="popup-push-list" class="page3-floating-divs">
							<h2 class="boxhead">Select a user to transfer this list to:</h2>
								<div name="push_list" id="push_list">
	
									<!-- AJAX DATA HERE -->
									
								</div><br />										
							<input type="button" id="pushname_button" value="Push" onClick="javascript:$.fancybox.close();" />	
							<input type="button" id="pushname_button_cancel" value="Cancel" onClick="javascript:$.fancybox.close();" />								
							<div class="clear">&nbsp;</div>
							</div>
							
							
			<!-- END page 3 pop ups -->	
			 </div>
					
<?php } ?>
