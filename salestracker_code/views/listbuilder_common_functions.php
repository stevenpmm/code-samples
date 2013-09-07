

     <!-- CONTAINS LIST BUILDER PAGE 3 FUNCTIONS THAT IS ALSO USED IN SAVED LISTS -->
			
		<script>
				$(function() {
					$( "#sortable" ).sortable();
					$( "#sortable" ).disableSelection();
				});

				$(document).ready(function() {
					
					$('#default-button').click(function() { 
						$('.popup-fields-listbox-items').attr('checked', false);
						$('#applytothislist-button').click();								    	     		   
					}); 
					
					//Click on list order save button...
					$('#applytoalllists-button').click(function() { 
						
						//Post details
						$.post('/index.php/preferences/process_listorder',
						$('#list-form').serialize(),
						function(result) {
							
							//DEBUG
							//alert(result);
							
							if(result.indexOf("Error") != -1){
								
								//Show error
								alert('There was an error while trying to process your request. Please check the following error.\n'+result);

							} else {
								
								alert(result);
								$('#applytothislist-button').click();
								
							}
							
						});
						
					});

					// setting view list display fields
					$('#applytothislist-button').click(function(e) {

						if (document.location.href.indexOf('listbuilder') > -1) {
							
							var event_flag = 4;
							var criteria = "";
							var offset = $('#offset_store').val();

							// sanity check
							if($('.popup-fields-listbox-items:checked').length > 6){
								alert('Error: maximum of six choices');
								return false;
							}

							// get user criteria
							$('.popup-fields-listbox-items:checked').each(function() {
								criteria+=$(this).val()+'-';		    	   
							});

							if(criteria == ""){ criteria = 0;}

							//$.post('/index.php/ajax/process_lb_data/'+event_flag+'/'+criteria,
							$.post('/index.php/ajax/process_lb_data/'+event_flag+'/'+criteria+'/'+offset,

							$('form#listbuilder_form').serialize()+'&dsn='+$('#database-select').val(),	 

							// Web server responds to the request
							function(result) {

								// if the result is TRUE write data
								if (result) {
									$("#records-box").empty().html(result);
									//Simulate click on non-existant button to trigger sales excludes
									$('#hidden_excludes').trigger('click');		      	      	             
								} else {
									//Simulate click on non-existant button to trigger sales excludes
									$('#hidden_excludes').trigger('click');	
								}

							});

							$.fancybox.close();

							return e.preventDefault();
						
						} else if (document.location.href.indexOf('savedlists') > -1){
							
							//Checks hidden var
							if ($('#display_type').val() == 'user'){
								
								//Post details
								$.post('/index.php/savedlists/update_temp_display_vals',
								$('#list-form').serialize(),
								function(result) {
									
									var criteria = "";

									// sanity check
									if($('.popup-fields-listbox-items:checked').length > 6){
										alert('Error: maximum of six choices');
										return false;
									}

									// get user criteria
									$('.popup-fields-listbox-items:checked').each(function() {
										criteria+=$(this).val()+'-';		    	   
									});

									if(criteria == ""){ criteria = 0;}

									//   alert(criteria);

									$('#myselections').val(criteria);

									$('#form_selections').submit();

									$.fancybox.close();

									return e.preventDefault();	
									
								});
								
							} else {
								
								var criteria = "";

								// sanity check
								if($('.popup-fields-listbox-items:checked').length > 6){
									alert('Error: maximum of six choices');
									return false;
								}

								// get user criteria
								$('.popup-fields-listbox-items:checked').each(function() {
									criteria+=$(this).val()+'-';		    	   
								});

								if(criteria == ""){ criteria = 0;}

								//   alert(criteria);

								$('#myselections').val(criteria);

								$('#form_selections').submit();

								$.fancybox.close();

								return e.preventDefault();	
								
							}
							
							//return false;

						}
					
					});

				});
			</script>
			
			<!-- view list popup -->
			<input type = "hidden" class = "hidden" name = "offset_store" id = "offset_store" value = "" /> 	
			<div id="popup-fields" class="page3-floating-divs">
				<h2 class="boxhead">Fields to display on your list</h2>
				<form id="list-form">
					<div id="popup-fields-listbox">
						<ul id="sortable">
							<?php
								//Process list items
								if($listitems){
									$listitems = explode(';',$listitems);
								} else {
									$listitems = array();
								}
							?>
							<?php
								if($listitems){ //If items
									foreach ($listitems as $list){ //Loop through
										if (array_key_exists($list, $listitems_template)){ //Compare against template array
											$term = $listitems_template[$list]; //Grab the term
											?>
											<li><input class="popup-fields-listbox-items" type="checkbox" id="<?php echo $list; ?>" name="list[]" value="<?php echo $list; ?>" checked /> <?php echo $term; ?></li>
										<?php
										}
									}
								}
							?>
							<?php foreach ($listitems_template as $value => $term){ //Loop through values template array ?>
								<?php if (!in_array($value, $listitems)){ ?>
									<li><input class="popup-fields-listbox-items" type="checkbox" id="<?php echo $value; ?>" name="list[]" value="<?php echo $value; ?>" /> <?php echo $term; ?></li>
								<?php } ?>
							<?php } ?>
						</ul>
					</div>
				</form>
				<div id="popup-fields-buttons-right">
					<!--  
					<input class="moveup-button" type="button" value="Move Up" />
					<input class="movedown-button" type="button" value="Move Down" />
					-->
				</div>
				<div id="popup-fields-buttons-lower">
					<input id="applytothislist-button" class="applytothislist-button" type="button" value="Apply to this list" />
					<input id="applytoalllists-button" class="applytoalllists-button" type="button" value="Apply to all lists" />
					<input id="default-button" class="default-button" type="button" value="Reset" />
				</div>
				<div class="clear">&nbsp;</div>
			</div>
			<!-- END view list popup -->
		
           <!-- EXTRA page 3 FORMS -->
           	
				<!-- WORKS WITH XLS EXPORT ABOVE (id="xls_submit") and form processing with ajax -->				
				<form id="hiddenform_xls" method="POST" action="/index.php/utilities/export_to_xls">
				  <input type="hidden" id="filedata_xls" name="data_xls" value="">
				</form>
				
				<!-- WORKS WITH CSV EXPORT ABOVE (id="csv_submit") and form processing with ajax -->				
				<form id="hiddenform" method="POST" action="/index.php/utilities/export_to_csv">
				  <input type="hidden" id="filedata" name="data" value="">
				</form>
				
				<!-- WORKS WITH XML EXPORT ABOVE (id="xml_export") and form processing with ajax -->				
				<form id="hiddenform_xml" method="POST" action="/index.php/utilities/export_to_xml">
				  <input type="hidden" id="filedata_xml" name="data_xml" value="">
				</form>
				
				<!-- Exclude sales manager data -->
				<script type = "text/javascript">
					$(document).ready(function(){
						
						$('.exclude-response').hide('');
						
						//Default hidden var
						//(listbuilder relies on select boxes, savedlists populates with scripting
						if (!$('#status_dsn').val()){
							$('#status_dsn').val($('#database-select').val());
						}
						$('#status_dsn').val($('#database-select').val());
						
						//Populate hidden form field if database changes
						$('#database-select').change(function() {
							$('#status_dsn').val($('#database-select').val());
						});
						
						//Click exclude button...
						$('#hidden_excludes, #get-excludes').click(function() {
							
							var event_trigger = "";
							var timeout = "";
							
							//Set flag
							if($(this).is('#get-excludes')) {
								event_trigger = "button";
							} else {
								event_trigger = "page";
							}
							
							//Auto-tick do not contact if we're transitioning page
							if (event_trigger == "page"){
								$('#notcontact').prop('checked', true);
							}
							
							$.ajax({
						      type: "POST",
						      url: "/index.php/listbuilder/get_exclude_ids", 
						      data: $('form#status_excludes').serialize(),
						      success: function(result){
				//alert(result);return;
								//Only alert the user if explicitly clicking button
								//Else run transparently (i.e. moving from second page)
								if(event_trigger == "button"){
									
									<?php if (isset($id)){ //Savedlists ?>
										$('#status_excludes').hide('fast');//Hide form
									<?php } ?>
									
									if(result.indexOf("Error") != -1){
										$('.exclude-response').show('fast');
										$('.exclude-response').html('<p style="color:#ff0000"><b>There was an error while trying to process your request. Please check the following error.</b></p><p style="color:#ff0000">'+result+'</p>');
									} else {
										$('.exclude-response').show('fast');
										<?php if (isset($id)){ //Savedlists ?>
											$('.exclude-response').html('<p class="center"><b>Companies with relevant relationships will be removed from this list. Refreshing page...</b></p><p class="center"><img src="/media/images/ajax-loader.gif" /></p>');
											setTimeout(function(){
												window.location.reload();
											}, 250);
										<?php } else { ?>
											alert(result);
										<?php }?>
									}
									
								}
								
						      },
						      async: false
						    });
							
						});
						
					});
				</script>
				
				<span id="hidden_excludes"></span>
				
				<div id="popup-exclude" class="page3-floating-divs">
					<h2 class="boxhead">Exclude the Following Records</h2>
					<div class="exclude-response"></div>
					<form name="status_excludes" id="status_excludes">
					
					    <div style="float:left">
						<h3>Relationships:</h3>
						<input type="hidden" value="" id="status_dsn" name="status_dsn" />
						<?php if (isset($id)){ ?>
							<input type="hidden" value="<?=$id;?>" id="list_id" name="list_id" />
						<?php } ?>
						<ul>
							<?php foreach ($statuses as $status){ ?>
								<?php if ($status != "Raw data"){ ?>
									<li><input type="checkbox" name="exclude_status[]" <?php if ($status == "Do not contact"){ ?>id="notcontact"<?php } ?> value="<?=$status;?>" <?php if (isset($selected_statuses)){ if (in_array($status, $selected_statuses)){ echo "checked"; } } ?>/> <?php if ($status == "Do not contact"){ ?><span style="color:#ff0000;"><?=$status;?></span><?php } else { echo $status; } ?></li>
								<?php } ?>
							<?php } ?>
						</ul>
						</div>
						
						<!-- 
						<div style="">
						<h3>Credit Rating:</h3>
						<ul>
						    <li><input type="radio" name="exclude_credit_rating" value="10" />Less than 10</li> 
						    <li><input type="radio" name="exclude_credit_rating" value="30" />Less than 30</li>
						    <li><input type="radio" name="exclude_credit_rating" value="50" />Less than 50</li>
						    <li><input type="radio" name="exclude_credit_rating" value="0" checked />No Preference</li>
						</ul>
						</div>
						 -->
						
						<input type="button" class="update-button" id="get-excludes" value="Update" />
					</form>
					<div class="clear">&nbsp;</div>
				</div>
				
				<!-- Add global note -->
				<script type = "text/javascript">
					$(document).ready(function(){
						
						$('.global-response').hide('');
						
						//Default hidden var
						//(listbuilder relies on select boxes, savedlists populates with scripting
						if (!$('#status_dsn_globnote').val()){
							$('#status_dsn_globnote').val($('#database-select').val());
						}
						
						//Populate hidden form field if database changes
						$('#database-select').change(function() {
							$('#status_dsn_globnote').val($('#database-select').val());
						});
						
						//Click exclude button...
						$('#save-global').click(function() {
							
							//Show "loading" gif
							$('.global-response').show('fast');
							$('.global-response').html('<p class="center">Adding global note to records...</p><p class="center"><img src="/media/images/ajax-loader.gif" /></p>');
							$('#global-notes').hide();
							
							//POST...
							$.ajax({
								type: "POST",
								url: "/index.php/listbuilder/create_global_note", 
								data: $('form#global-notes').serialize(),
								success: function(result){
									
									//If error
									if(result.indexOf("Error") != -1){
										$('#global-notes').show('fast');
										$('.global-response').html('<p style="color:#ff0000"><b>There was an error while trying to process your request. Please check the following error.</b></p><p style="color:#ff0000">'+result+'</p>');
									//If success, reset form
									} else {
										$('.global-response').html('<p><b>'+result+'</b></p>');
										setTimeout(function(){
											$.fancybox.close([{
												href : '#popup-globalnote'
											}]);
										}, 1000);
										setTimeout(function(){
											$('#the-global-note').val();
											$('.global-response').html('');
											$('#global-notes').show();
											$('.global-response').hide();
										}, 1300);
									}

								}
							
							});
							
						});
						
					});
				</script>
				<div id="popup-globalnote" class="page3-floating-divs">
					<h2 class="boxhead">Add a global note to these records</h2>
					<div class="global-response"></div>
					<form name="global-notes" id="global-notes">
						<?php if (isset($id)){ ?>
							<input type="hidden" value="<?=$id;?>" id="list_id_globnote" name="list_id_globnote" />
						<?php } ?>
						<p>Adding a global note will create a note that appears in the "Activity" section of every record on this list.</p>
						<p>Type your note below and then click "Save" to add the note.</p>
						<input type="hidden" value="" id="status_dsn_globnote" name="status_dsn_globnote" />
						<textarea id="the_global_note" name="the_global_note" width="50"></textarea>
						<input type="button" class="save-button" id="save-global" value="Save" />
					</form>
					<div class="clear">&nbsp;</div>
				</div>
				
				<script> 
		        // for file upload with ajax plugin
		        $(document).ready(function() { 
		            // bind 'myUploadForm' and provide a simple callback function 
		            $('#myUploadForm').ajaxForm(function(result) { 
		                alert(result); 
		            }); 
		            
		            // reset button
		            $('#myreset').click(function() { 
		                $('#myUploadForm').clearForm();
		            });

		            // checks file uploads are associated with a saved list  
					$("#add-button").click(function() {  
						
					    $.ajax({
					      type: "POST",
					      url: "/index.php/ajax/check_list_name",  
					      success: function(html){
					    	if(html == 1){
						        alert('Error: list must first be saved.');		
						        $('.fileuploads').each(function() {
						        	$(this).prop('disabled', true); 
						        }); 
					         }
					    	else{
					    		$('.fileuploads').each(function() {
						        	$(this).prop('disabled', false); 
						        }); 
					    	}
					       }
					    });               
					    return true;
					    
					  });
				            
		        }); 
		        </script> 
	           	<!-- WORKS WITH file upload with ajax plugin ABOVE  -->		
				<div id="popup-note" class="page3-floating-divs">
				
				    <form id="myUploadForm" action="/index.php/upload/do_upload/List" method="post">
					<h2 class="boxhead">Add a note and / or document</h2>
					<h3>Add a note to appear on each record on this list</h3>
					<textarea name="mynote" rows="5" cols="50"></textarea>
					<h4>Attach a document / file to these records</h4>								
					<input class="fileuploads" type="file" name="userfile" size="20" />							
					<input class="fileuploads" id="myUploadForm_submit" type="submit" value="upload" />	
					<input class="fileuploads" id="myreset" type="button" value="reset" />							
					</form>
					
					<div class="clear">&nbsp;</div>
				</div>
				
				<!-- END EXTRA page 3 FORMS -->			
