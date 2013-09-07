		
		//Warn user when pressing back button
        /*
		window.onbeforeunload = function(){
			return "Leaving this page will reset your current list.";
		}
		*/
		
		// Globals
		var dsn = "";
		var postcodes_flag = 0;
		var region_ajax_store = [];
		var prod_count=0;
		//var regPostcode = /^([a-zA-Z]){1}([0-9][0-9]|[0-9]|[a-zA-Z][0-9][a-zA-Z]|[a-zA-Z][0-9][0-9]|[a-zA-Z][0-9]){1}([ ])([0-9][a-zA-z][a-zA-z]){1}$/;		    	
		
		//Steve's original Postcode REGEX
		/*/^([A-PR-UWYZ0-9][A-HK-Y0-9][AEHMNPRTVXY0-9]?[ABEHMNPRVWXY0-9]? {1,2}[0-9][ABD-HJLN-UW-Z]{2}|GIR 0AA)$/;*/
		
		//Postcode matches full and partial e.g. BS24 or BS24 9AY
		var regPostcode = /^(GIR|[A-Z]\d[A-Z\d]??|[A-Z]{2}\d[A-Z\d]??)([ ]\d[A-Z]{2})??$/;
		
		// Page 2 checkbox groups toggle
		function select_postcodes(key){
			
			var totalcheck = $('.allheaders').length;
			var currentcheck = $('.allheaders').filter(':checked').length;
			//alert(totalcheck + ' ' + currentcheck);
			
			if (currentcheck == totalcheck){
				$('#region-all').attr('checked', true);
			}
			
			if ($('#'+key+'-all').is(':checked')){
				$('.'+key).attr('checked', true);
				//alert('ON!');
			} else {
				$('.'+key).attr('checked', false);
				$('#region-all').attr('checked', false);
				//alert('OFF!');
			}
			
		}

		// Page 2 checkbox groups toggle - works on individual checkboxes
		function remove_allboxes(key){
			
			var keyvals = key.split("-");
			var pcvalue = keyvals[0];
			var checkid = keyvals[1];
			
			//alert(checkid);
			
			if (!$('#'+checkid).is(":checked")){
				//alert("NOT CHECKED!");
				$('#'+pcvalue+'-all').attr('checked', false);
				$('#region-all').attr('checked', false);
			}
			
			//Get total checkboxes in region and current amount ticked
			var totalcheck = $('.'+pcvalue).length;
			var currentcheck = $('.'+pcvalue).filter(':checked').length;
			//alert(totalcheck + ' ' + currentcheck);
			
			//If current amount = total, tick the "all in region" box for that region
			if (currentcheck == totalcheck){
				//alert('TOTAL! - this should check' + '#'+pcvalue+'-all');
				$('#'+pcvalue+'-all').attr('checked', true);
				$('input:checkbox').filter('#'+pcvalue+'-all').attr(":checked")
			}
			
		}
		
		// Page 2 check postcode entered is within subscription
		function check_postcodes(val){
			
			val = val.toUpperCase();
			
			for(var k=0; k<region_ajax_store.length; k++){
			
				if(region_ajax_store[k] == val.substr(0,2) || region_ajax_store[k] == val.substr(0,1)){
					return true;
				}
				
			}
			
			return false;
			
		}
		
		// Populates intial record count
		function set_default_db_count(){  // similar to $('#btn_submit').click(function(e)
		
			var event_flag = 1;
			
			$.post('/index.php/ajax/process_lb_data/'+event_flag,
				   
			$('form#listbuilder_form').serialize()+'&dsn='+$('#database-select').val(),
			
			// Web server responds to the request
			function(result) {
				
				// if the result is TRUE write a message OR process
				if (result) {
					// alert(result);
					$('span#rec_count').html(result);
				}
						
			});
			
		}
		
		// Populates regions (page 2) with default db permissions
		function set_default_db_regions(){
		
			//Defaults to first database
			dsn = $('#database-select').val();

			$.post('/index.php/ajax/create_regions_list/'+dsn,
			
			{ 'dsn': dsn },

			// Web server responds to the request
			function(result) {

				//If the result is TRUE write data
				if (result) {
       //  alert(result);
					// postcodes or regions ?
					var myarray = result.split('#');

					if(myarray[1] == 1){
						postcodes_flag = 1;
						$('#distance-filter').hide();
					}else{
						postcodes_flag = 0;
						$('#distance-filter').show();
					}
					
					$('#region_ajax').html(myarray[0]);

					//alert(result);
					//$('#region_ajax').html(result);

					// Store regions in global array
					region_ajax_store=[];

					$('#region_ajax').find("input:checkbox").each(function(index, value) {

						if($(this).val() != 'on'){
			
							region_ajax_store[index] = $(this).val();
			
						}

					});
				
				}
			
			});

		}
			  
		//Sets template selections with default db products permissions
		// ie only permitted check boxes will be enabled
		function set_default_db_products(dsn){

			$.post('/index.php/ajax/set_default_display/'+dsn,

			{ 'dsn': dsn },

			// Web server responds to the request
			function(result) {

				if(!result){
				
					alert('Error: cannot enforce view permissions');
				
				} else {

					// Set permissions / display
					var myarray = result.split(',');

					$('.'+dsn).prop('disabled', true);
					$('.'+dsn).each(function(index) {

						// First index
						if(index == 0 && myarray[0] > 0) { 
							//$(this).eq(myarray[0]-1).removeAttr('disabled'); 
							$(this).removeAttr('disabled');
						}

						if($.inArray($(this).val(), myarray) != -1){
							$(this).removeAttr('disabled');
						}

					});

				}

			});

		}
			  
		// Page loading - initialise
		$(document).ready(function() {

			var event_flag;

			// Form processing with ajax
			// See http://stackoverflow.com/questions/3346072/download-csv-file-using-ajax for csv stuff
			$('#btn_submit, #csv-button, #xml_export, #excel-button, #csv_export, #csv_submit, #view-button, #next-button2, #next-button2_top, #next-button2_bottom').click(function(e) {
			
				// Determine submit button
				if($(e.target).attr('id') == 'btn_submit') { event_flag = 1; $('#ajax-anim_1').show();} // update count
				if($(e.target).attr('value') == 'csv_export') { event_flag = 2; $('#ajax-anim_3').show();} // csv dump
			//	if($(e.target).attr('value') == 'xml_export') { event_flag = 3; $('#ajax-anim_2').show();} // xml dump
				if($(e.target).attr('value') == 'xls_export') { event_flag = 6; $('#ajax-anim_3').show();} // xls dump 
				//if($(e.target).attr('id') == 'view-button') { event_flag = 4; $('#ajax-anim_33').show();} // display records
				
				// A HACK TO DIRECT PAGE 2 DIRECTLY TO DISPLAY RECORDS. // originally set to 5 (save records in temp table)
				/*if($(e.target).attr('id') == 'next-button2' || $(e.target).attr('id') == 'next-button2_top' || $(e.target).attr('id') == 'next-button2_bottom') { 
						event_flag = 4; 
						$('#ajax-anim_33').show();
						$('#listbuilder-header-buttons').hide();
				}*/
				
				if(this.id === 'next-button2' || this.id === 'next-button2_top' || this.id === 'next-button2_bottom'){
					$('#ajax-anim_33').show('fast');
					$('#listbuilder-header-buttons').hide('fast');
					event_flag = 4; // DISPLAY RECORDS
				}
				
			    // Ajax request sent to the CodeIgniter controller "ajax" method "process_lb_data"
			    // Post the username field's value
				$.ajax({
					  type: "POST",
					  url: '/index.php/ajax/process_lb_data/'+event_flag, 
					  data: $('form#listbuilder_form').serialize()+'&dsn='+$('#database-select').val(),
					  
					  /*
					  timeout : 15000,	// 15 seconds			  
					  error : function(xhr, textStatus, errorThrown ) {
					        if (textStatus == 'timeout' && event_flag == 4) { // DISPLAY RECORDS ONLY
					            xhr.abort(); 
					            alert('The request has timed out !\n You will be re-directed back to the start of list builder');
					            window.location.reload();
				                return;
					        }		        
				      },
				      */
					  
					  success: function(result) {
						 //alert(result);
							
						// If the result is TRUE write a message OR process
						//if (result) {
							
							if(result.indexOf("Error") != -1){							
								
								$('.ajax-anim').hide(); 
								$('#listbuilder-step3').hide();
								$('#listbuilder-page2').show();
								$('#previous-button2_top').show();

								$('span#rec_count').html('0');
								$('#listbuilder-header-buttons').show();
								alert(result);			      
							
							} else {
						  
								if(event_flag == 1){
									
									$('span#rec_count').html(result);
									$('.ajax-anim').hide();
								
								} else if(event_flag == 2){  
										
									$('.ajax-anim').hide();
									//$('#csv_submit').prop('disabled', true);
									$("#filedata").val(result);
									$("#hiddenform").submit(); // Push csv data to form for download
								
								} else if(event_flag == 3){  
								
									$('.ajax-anim').hide();  
									//$('#csv_submit').prop('disabled', true);  
									$("#filedata_xml").val(result);
									$("#hiddenform_xml").submit(); // Push xml data to form for download
									
								} else if(event_flag == 4){
						//alert($(e.target).attr('id'));				
									var rdata = result.split('</table>');
									
									$('#previous-button2').hide();									
									$('#listbuilder-step1').hide();
									$('#listbuilder-page2').hide();
									$('#listbuilder-step3').hide();
									//$('.next-button').hide(); 
									//$('#previous-button2_top').hide();  
									//$('#previous-button2').show(); 
												
									// Display records (list)
									$('.ajax-anim').hide();
									$('#offset_store').val('');
									$("#records-box").empty().html(rdata[0]);	
									$('#records-wrap').show();
									
									// update final count ??
									$('span#rec_count').html(rdata[1]);
									
									//Simulate click on non-existant button to trigger sales excludes
									$('#hidden_excludes').click();

								}else if(event_flag == 5) {
									
									if($('#pc_distance').val() == 0 || check_postcodes($('#postcode_box').val())){

										$('#listbuilder-step1').hide();
										$('#listbuilder-page2').hide();
										$('#listbuilder-step3').show();
										$('.next-button').hide(); 
										$('#previous-button2_top').hide();  
										$('#previous-button2').show(); 
										
										$('span#rec_count').html(result); // update final count

									} else {					
										alert('Error: the entered post code is outside of your subscription or invalid entry');				
									}
														   
									
								} else if(event_flag == 6){
									
									$('.ajax-anim').hide();    
									$("#filedata_xls").val(result);
									$("#hiddenform_xls").submit(); // Push xls data to form for download
								
								}
								
							}		
					  }
					  /*,async: false*/
				  });
				
				/*
			    $.post('/index.php/ajax/process_lb_data/'+event_flag,
				$('form#listbuilder_form').serialize()+'&dsn='+$('#database-select').val(),	 

					// Web server responds to the request
					function(result) {
					
						 //alert(result);
						
						// If the result is TRUE write a message OR process
						if (result) {
							
							if(result.indexOf("Error") != -1){							
								
								$('.ajax-anim').hide(); 
								$('#listbuilder-step3').hide();
								$('#listbuilder-page2').show();
								$('#previous-button2_top').show();

								$('span#rec_count').html('0');
								$('#listbuilder-header-buttons').show();
								alert(result);			      
							
							} else {
					      
								if(event_flag == 1){
									
									$('span#rec_count').html(result);
									$('.ajax-anim').hide();
								
								} else if(event_flag == 2){  
									
									$('.ajax-anim').hide();
									//$('#csv_submit').prop('disabled', true);
									$("#filedata").val(result);
									$("#hiddenform").submit(); // Push csv data to form for download
								
								} else if(event_flag == 3){  
								
									$('.ajax-anim').hide();  
									//$('#csv_submit').prop('disabled', true);  
									$("#filedata_xml").val(result);
									$("#hiddenform_xml").submit(); // Push xml data to form for download
									
								} else if(event_flag == 4){
									
									var rdata = result.split('</table>');
									
									$('#previous-button2').hide();									
									$('#listbuilder-step1').hide();
									$('#listbuilder-page2').hide();
									$('#listbuilder-step3').hide();
									//$('.next-button').hide(); 
									//$('#previous-button2_top').hide();  
									//$('#previous-button2').show(); 
									
									// Display records (list)
									$('.ajax-anim').hide();
									$('#offset_store').val('');
									$("#records-box").empty().html(rdata[0]);	
									$('#records-wrap').show();
									
									// update final count ??
									$('span#rec_count').html(rdata[1]); 

								}else if(event_flag == 5) {
									
									if($('#pc_distance').val() == 0 || check_postcodes($('#postcode_box').val())){

										$('#listbuilder-step1').hide();
										$('#listbuilder-page2').hide();
										$('#listbuilder-step3').show();
										$('.next-button').hide(); 
										$('#previous-button2_top').hide();  
										$('#previous-button2').show(); 
										
										$('span#rec_count').html(result); // update final count

									} else {					
										alert('Error: the entered post code is outside of your subscription or invalid entry');				
									}
												    	   
									
								} else if(event_flag == 6){
									
									$('.ajax-anim').hide();    
									$("#filedata_xls").val(result);
									$("#hiddenform_xls").submit(); // Push xls data to form for download
								
								}
								
							}
						
						}
						
					});
					
				return e.preventDefault();
				*/
					
			});  
			
			//Change database	
			$('#database-select').change(function() {
			
				// Get selection	
				dsn = $('#database-select').val();
		
				// Initialise all filters
		        $('input:checked').attr('checked', false);
			    $('.allregions').attr('checked', false);	
			    $('#region-all').attr('checked', false);
			    $('.hidden').val('');
			    $('.ifd-filter_s').hide();
			    $(".menu-headers").removeClass("selected active");
			    $('.pvcu, .alum, .timber').removeClass("selected active");
			    $('#distance-filter').show();
			    
				// display correct templates: sectors or products?
				if(dsn == 'sbd'){
					
					$('#listbuilder-left').hide();
					$('#listbuilder-right').hide();
					$('.ifd-filter_s').hide();
					$('#filter-sbd').show();
				
				} else if(dsn == 'nrg'){
					
					$('#listbuilder-left').hide();
					$('#listbuilder-right').hide();
					$('.ifd-filter_s').hide();
					$('#filter-nrg').show();								
				
				} else if(dsn == 'asd'){
					
					$('#listbuilder-left').hide();
					$('#listbuilder-right').hide();
					$('.ifd-filter_s').hide();
					$('#filter-asd').show();								
				
				} else if(dsn == 'mcd'){
					
					$('#listbuilder-left').hide();
					$('#listbuilder-right').hide();
					$('.ifd-filter_s').hide();
					$('#filter-mcd').show();									
				
				} else { // ifd or roi
					
					$('.ifd-filter_s').hide();
					$('#listbuilder-left').show();
					$('#listbuilder-right').show();
					//$('#filter-none').show();
					$('#distance-filter').show();
				}
			    
			    $.post('/index.php/ajax/create_regions_list/'+dsn,  // Page 2
				
				{ 'dsn': dsn },

				// Web server responds to the request
				function(result) {
				
					// If the result is TRUE write data
					if (result) {
					
						// alert(result);
						
						// postcodes or regions ?
						var myarray = result.split('#');

						if(myarray[1] == 1){
							postcodes_flag = 1;
							$('#distance-filter').hide();
						}else{
							postcodes_flag = 0;
							$('#distance-filter').show();
						}
						
						$('#region_ajax').html(myarray[0]);
						
						// Store regions in global array
						region_ajax_store=[];
						
						$('#region_ajax').find("input:checkbox").each(function(index, value) {
						
							if($(this).val() != 'on'){
								
								region_ajax_store[index] = $(this).val();
								
							}
							
						});
						
						// Reset defaults	
						$('#selected-criteria').empty(); 
						$('.allregions').attr('checked', false);	
						$('#region-all').attr('checked', false);	
						$('#pc_distance').val('0');
						$('#postcode_box').val(''); 
						$('#pc_check').html('');
						$('#format-standard').prop('checked', true); // Sets first radio button to selected 

						// Reset menu selections (remove asterisk)
						$('.menu-headers').each(function() {
							$(this).html($(this).html().replace(/\*/g, ""));
						});

						// Set total records
						set_default_db_count();
						
			        }
					
				});
				
			});
			
			// XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX  DEFAULTS  XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
            
            // Custom js for page 3 buttons 
			set_default_db_regions();
			
			// set profile radio buttons to selected
			$('.profile_radio_select input:radio').prop('checked', true);
			
            
            // Csv export controls
            $('#popup-export input:radio').click(function() { 
				$('#csv_choices').val($(this).val());	 // populate hidden field with csv choices          		   
  		    });  

			// Captures csv includes
			$('#popup-export .csv_includes').click(function() { 
				
				var criteria = "";
				$("#popup-export .csv_includes").each(function() {  
				
					// Get criteria
					if(this.checked){
						criteria+=$(this).val()+"#";
					}
				
				});
		 
				$('#csv_include_checks').val(criteria);	 // Populate hidden field with csv includes
				
			});
			
			// List name popup data capture
			$('#listname_button').click(function() {
				
				if($.trim($('#listname').val()) == ""){
					
					alert('Error: no list name entered. List not saved.');  // Populate hidden field with list name    
				
				} else {
				
					$('#view_title').val($('#listname').val());

					// Store sqltrim(
					$.post('/index.php/ajax/store_sql/'+$('#view_title').val(),
					
					$('form#listbuilder_form').serialize(),
					
					// Web server responds to the request
					function(result) {
			//alert(result);		
						// If error, display message
						if(result.indexOf("Error") != -1){
							alert(result)
							return false;
						}else{
							var url = 'savedlists/viewlist/';
								//alert(url+result);
							window.location.href = url+result; // redirect
							return true;
						}
					});
					
				}
			
			});
			
			// Page 3 pop up controls (plugins) 
			$(".fancybox").fancybox({ openEffect  : 'elastic' });

			// Date selection (plugin)       
			$( "#datepicker" ).datepicker({ dateFormat: 'dd/mm/yy' });

			// Sets first csv radio button to selected
			$('#format-standard').prop('checked', true);

			// Sets default sort radio button to selected
			$('#format-sort1').prop('checked', true);  

			// Functions to toggle between list view and default page 3 view
			$("#view-button").click(function() {
				
				$('#listbuilder-step3').hide();
				$('#records-wrap').show();
				$('#previous-button2').hide();
				
			});
			
			$("#action-button").click(function() {
				
				/*
				$('#records-wrap').hide();
				$('#listbuilder-step3').show();
				$('#previous-button2').show();    
				*/	
			
			});
			
			// Hide search menu
            $('#search_menu_header').hide();

            // Hide ajax animations
            $('.ajax-anim').hide();
			
			// Hide activity specific messages
			$('.fab_buyin_messages').hide(); 

			// BLANK DISPLAY 
			$('#filter-none').show(); // Default message
			$('.ifd-filter').hide(); // products
			$('.ifd-filter_s').hide(); // sectors
			$('.page3-floating-divs').hide(); // page 3 floating divs associated with the button cntrols

			//$('#filter-windoors').show();
			
			// Hide pages 2 and 3
		    $('#listbuilder-page2,#listbuilder-step3').hide();

		    set_default_db_count();

		    // clear choices
		    $('#selected-criteria').empty();

		    // page 2 'Select all regions in your subscription' checked
			$('.allregions').attr('checked', false);	
			$('#region-all').attr('checked', false);	
			$('#pc_distance').val('0');
			$('#postcode_box').val('');
			$('#pc_check').html('');	
			$('#postcode_box').prop('disabled', true);  
			$('#next-button2_top').hide();
			$('#next-button2_bottom').hide();
			$('#previous-button2_top').hide();
			$('#previous-button2').hide();
			
			// XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX  BUTTON CONTROLS / NAVIGATION  XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
			
			/*
			$('#button-installer-dialogue').click(function() {
				$('#installer-dialogue-options-hidden').val($('#installer-dialogue-options option:selected').val());
				//alert($('#installer-dialogue-options-hidden').val());
				alert($('#installer-dialogue-options option:selected').val());
			});
			*/
			
			// Next button 1 (pages 1-2)
		    $('#next-button').click(function() {
		    	
		    	// check product selections
		    	/*
		    	if(prod_count > 4){
		    		alert("Error: only FOUR product selections can be made.\n\nClick Clear Filters.");
		    		return
		    	}
		    	*/
			
				// Get id of current visible filter / set hidden field  (if any check boxes been selected) 
				var cur = $('.ifd-filter:visible').attr('id'); 
				
				// Set currently active class and hidden fields (have any check boxes been selected ?)
				$("#"+cur+" input:checked").length > 0 ?  $(".active").addClass("selected") : $(".active").removeClass("selected"); 
				
				// Page 2 defaults
				// $('.allregions').attr('checked', false);	
				// $('#region-all').attr('checked', false);	

				$('#listbuilder-step1').hide();
				$('#listbuilder-page2').show();

				//Hide distance filter 
                if (dsn == 'roi'){ 
                    $('#distance-filter').hide(); 
                    $('.postcodes').addClass("roi-counties"); 
                }

                if(postcodes_flag == 1){
                	$('#distance-filter').hide(); 
                }
				
				$('.clear-filter-button').hide();	
				$('#database-select').prop('disabled', true);
				$('#database-select').css('color', '#cccccc');
				$('#next-button').hide(); 
				$('#next-button2_top').show();
				$('#next-button2_bottom').show();
				$('#previous-button2_top').show();
				$('#previous-button2').hide(); 


				// Create selections list for page 3
				$('#selected-criteria').empty(); 
				$('#selected-criteria').append('<h2 class="boxhead">Selected Criteria</h2>');
		    	 
				if($('#maincat-pvcu').hasClass('selected')){
					
					$('#selected-criteria').append('<h3><strong>PVCu</strong></h3>'); 
				
				}

				if($('#pvcu_w,#pvcu_v,#pvcu_b').hasClass('selected')){

					if(!$('#maincat-pvcu').hasClass('selected')){
						$('#selected-criteria').append('<h3><strong>PVCu</strong></h3>'); 
					}

					if($('#pvcu_w').hasClass('selected')){
						$('#selected-criteria').append('<h4>&nbsp;&nbsp;> Windows &amp; doors</h4>'); 
					}
					
					if($('#pvcu_v').hasClass('selected')){
						$('#selected-criteria').append('<h4>&nbsp;&nbsp;> Vertical sliding sash</h4>'); 
					}
					
					if($('#pvcu_b').hasClass('selected')){
						$('#selected-criteria').append('<h4>&nbsp;&nbsp;> Bi-fold doors</h4>'); 
					} 
				
				}
				
				if($('#maincat-alu').hasClass('selected')){
					$('#selected-criteria').append('<h3><strong>Aluminium</strong></h3>'); 
				}

				if($('#alum_w,#alum_c,#alum_b').hasClass('selected')){

					if(!$('#maincat-alu').hasClass('selected')){
						$('#selected-criteria').append('<h3><strong>Aluminium</strong></h3>'); 
					}

					if($('#alum_w').hasClass('selected')){
						$('#selected-criteria').append('<h4>&nbsp;&nbsp;> Windows &amp; doors</h4>'); 
					}
				
					if($('#alum_c').hasClass('selected')){
						$('#selected-criteria').append('<h4>&nbsp;&nbsp;> Commercial glazing</h4>'); 
					}
					
					if($('#alum_b').hasClass('selected')){
						$('#selected-criteria').append('<h4>&nbsp;&nbsp;> Bi-fold doors</h4>'); 
					}
					
				}

				if($('#maincat-timber').hasClass('selected')){
					$('#selected-criteria').append('<h3><strong>Timber</strong></h3>'); 
				}

				if($('#tim_w,#tim_v').hasClass('selected')){
					
					if(!$('#maincat-timber').hasClass('selected')){
						$('#selected-criteria').append('<h3><strong>Timber</strong></h3>'); 
					}
					
					if($('#tim_w').hasClass('selected')){
						$('#selected-criteria').append('<h4>&nbsp;&nbsp;> Windows &amp; doors</h4>'); 
					}
					
					if($('#tim_v').hasClass('selected')){
						$('#selected-criteria').append('<h4>&nbsp;&nbsp;> Vertical sliding sash</h4>'); 
					}
				
				}

				if($('#other_w,#maincat-other').hasClass('selected')){			    	
					$('#selected-criteria').append('<h3><strong>Other materials/hybrids</strong></h3>'); 
					$('#selected-criteria').append('<h4>&nbsp;&nbsp;> Windows & Doors</h4>');
				}

				if($('#maincat-roofs').hasClass('selected')){
					$('#selected-criteria').append('<h3><strong>Conservatory Roofs</strong></h3>'); 
				}

				if($('#maincat-comp').hasClass('selected')){
					$('#selected-criteria').append('<h3><strong>Composite doors</strong></h3>'); 
				}

				if($('#maincat-igu').hasClass('selected')){
					$('#selected-criteria').append('<h3><strong>Sealed units</strong></h3>'); 
				}

				if($('#maincat-roofline').hasClass('selected')){
					$('#selected-criteria').append('<h3><strong>Roofline</strong></h3>'); 
				} 
		 
				// Sectors filters
				if($("#filter-sbd .sbd").is(':checked')){						
					$('#selected-criteria').append('<h3>Local Builders</h3>'); 
				} 

				if($("#filter-nrg .nrg").is(':checked')){			
					$('#selected-criteria').append('<h3>Renewable Energy Installers</h3>'); 
				}

				if($("#filter-mcd .mcd").is(':checked')){						
					$('#selected-criteria').append('<h3>Construction File</h3>'); 
				}  

				if($("#filter-asd .asd").is(':checked')){					
					$('#selected-criteria').append('<h3>Architects &amp; Specifiers</h3>'); 
				} 
				
				//Check the seletced-criteria div's content...
				//var criteriaDiv = document.getElementById("selected-criteria");
				
				// alert (criteriaDiv.innerHTML);
				
				//...if there aren't any selections then they've selected everything.
			
				if ($('#selected-criteria').html() == '<h2 class="boxhead">Selected Criteria</h2>'){
					$('#selected-criteria').append('<h3 class="all_regions_selected"><strong>All products selected</strong></h3><br />'); 
				}
							
				// Store ALL selections
				$('#selected-criteria-hidden').val($('#selected-criteria').html());    	 	    	 
		    	 
		    });
			
			// Next button 2 (pages 2-3)
		    $('#next-button2,#next-button2_top, #next-button2_bottom').click(function() {
		    	
		    	//$('#listbuilder-header-buttons').hide();
		   	
		    	// get region selections
		    	var criteria = "<div id='regions_select'><br /><h3><strong>Region(s)</strong></h3><br />";

		    	if($('#postcode_box').val()){
		    		criteria += 'within '+ $('#pc_distance').val() +' miles of '+ $('#postcode_box').val(); 
		    	}else if($('#region-all').attr('checked')){
		    		criteria += '<h4 class="all_regions_selected">All regions selected</h4>';
		    	}else if($(".allregions").length){
	    	
					$(".allregions").each(function(idx) {  

						var nl="<br />";
						var classes="";
						var myarray;
															
						// Get criteria
						if(this.checked){

							// get group name
							classes = $(this).attr('class');
							myarray = classes.split(' ');
							 				
						//	if(idx % 5 == 0){nl='<br />'}

							if($(this).val() != 'on'){
							   criteria+=$(this).val()+' ('+myarray[0].replace(/_/g, " ")+"),"+nl;
							}
						}				
					});
					
		    	}

		    	// sanity check			    
		    	if(criteria == "<div id='regions_select'><br /><h3><strong>Region(s)</strong></h3><br />"){
		    		criteria += '<h4 class="all_regions_selected">All regions selected</h4>';
		    	}

		    	criteria += '</div>';

				$('#selected-criteria').append(criteria);
				$('#selected-criteria-hidden').val($('#selected-criteria').html());  		
		    });
    

			// Previous button 1 (pages 2-1)
			$('#previous-button,#previous-button2_top').click(function() { 

				$('#listbuilder-step1').show();
				$('#listbuilder-page2').hide();
				$('.clear-filter-button').show();	
				$('#database-select').prop('disabled', false);
				$('#database-select').css('color', '#000000');
				$('#next-button').show();
				$('#next-button2_top').hide(); 
				$('#next-button2_bottom').hide(); 
				$('#previous-button2_top').hide();  	

			});

			// Previous button 2  (pages 3-2)
			$('#previous-button').click(function() { 
				
				$('#listbuilder-header-buttons').show();
				$('#records-wrap').hide(); // page 3
				$('#database-select').prop('disabled', true);
				$('#database-select').css('color', '#cccccc');
				
				// remove (refresh) region criteria
				$('#selected-criteria div#regions_select').empty();
				
				$('#listbuilder-step1').hide();
				$('#listbuilder-page2').show();
				$('#listbuilder-step3').hide();
				$('#next-button').hide(); 
				$('#next-button2_top').show(); 
				$('#next-button2_bottom').show(); 
				$('#previous-button2_top').show();  
				$('#previous-button2').hide(); 

			});

		    // Clear button
		    $('#clear-filter-button').click(function() {
		    	
		    	prod_count=0;
		    	
		    	// reset installer select
		    	$("#installer-dialogue-options").val($("#installer-dialogue-options option:first").val());

		    	// Uncheck all filters
		    	$('input:checked').attr('checked', false);
		    	$('#database-select').val($('#database-select option:first').val()); // resets to default
		    	$('span#rec_count').html('N/A');
		    	$('.hidden').val('');

		    	// Restore extra messages
		    	$('.fab_buyin_messages').hide(); 
		    	$('.excludes').attr('checked', false);

		    	// Hide sectors
		    	$('.ifd-filter_s').hide();
		    	$('.ifd-filter').hide();

		    	// Remove text classes
		    	$(".menu-headers").removeClass("selected");
		    	$(".menu-headers").removeClass("active");
		    	$('.pvcu, .alum, .timber, .other').removeClass("selected active");

				// Reset menu selections (remove asterisk)
				$('.menu-headers').each(function() {
					$(this).html($(this).html().replace(/\*/g, ""));
				});

		    	// default display
		    	$('#listbuilder-left').show();
				$('#filter-none').show(); // Default message

		    	set_default_db_regions();

		    	// Restore page 2 to defaults
		    	$('.allregions').attr('checked', false);	
		      	$('#region-all').attr('checked', false);	
	            $('#pc_distance').val('0');
				$('#postcode_box').val('');
				$('#pc_check').html('');
				// sets default sort radio button to selected
	            $('#format-sort1').prop('checked', true);  
				
		    });

			// XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX  PAGE 2 CONTROLS  XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
		   
			//Select all regions checkbox action
		    $('#region-all').click(function() { 
				
				//$('.allregions').attr('checked', !$('.allregions').is(':checked'));
				
				if (this.checked){
					$('.allregions').attr('checked', true);
					//alert('ON!');
				} else {
					$('.allregions').attr('checked', false);
					//alert('OFF!');	 
				}
		    	
		    });

		    // post code or distance ?
		    $('#pc_distance').change(function() { 
			    if($(this).val() > 0){
				   
			    	 $('#postcode-filter').hide();  
			    	 $('#distance-filter').show(); 
			    	 $('#postcode_box').prop('disabled', false);
			    	 $('#postcode_box').focus();		    	       	   	
			    }
			    else{
				    
			    	 $('#postcode-filter').show(); 
			    	 $('#postcode_box').prop('disabled', true); 
			    	 $('#pc_check').html('');
			    	 $('#postcode_box').val('');

			    	 $('.next-button').prop('disabled', false);
			    	 $('.previous-button').prop('disabled', false);
			    	 
			    	// alert( $('#region_ajax').find("input:checkbox").length );
			    } 
		    });

		    $('form input:checkbox').click(function() { 
			   //alert('');
			   $('#pc_distance').val('0');
			   $('#postcode_box').val('');
			   $('#pc_check').html('');
			   
		    });

		    // post code validation
		    $('#postcode_box').keyup(function() { 
			    
		    	 $(this).val().toUpperCase().match(regPostcode) == null ? $('#pc_check').html('&nbsp;Error: invalid post code (eg BS24 9AY)') : $('#pc_check').html('');	    		 
		    	 $(this).val().toUpperCase().match(regPostcode) == null ? $('.next-button').prop('disabled', true) : $('.next-button').prop('disabled', false);
		    	 $(this).val().toUpperCase().match(regPostcode) == null ? $('.previous-button').prop('disabled', true) : $('.previous-button').prop('disabled', false);
		    	 
			});

		    
		   
			// XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX  FILTERS  XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX

            // WINDOWS AND DOORS - PVCu
            $('#maincat-pvcu').click(function() {  // #maincat-alu,#maincat-timber,#maincat-other

				//alert($(this).attr('id'));
            	prod_count++;

            	$('#maincat-pvcu').html('PVCu *');

				// set filter title
				$("#filter_id_wd").text('Filter: PVCu');

            	// get id of current visible filter
            	var cur = $('.ifd-filter:visible').attr('id'); 

                // set currently active class (have any check boxes been selected ?)
            	$("#"+cur+" input:checked").length > 0 ?  $(".active").addClass("selected") : $(".active").removeClass("selected"); 
            	
            	// WINDOWS AND DOORS - Aluminium submenus
            	if($("#"+cur+" input:checked").length == 0 && cur == 'filter-windoors-al'){  $('#ALUM_choices').val(''); $('.alum').removeClass("active selected");}

            	// WINDOWS AND DOORS - Timber submenus
            	if($("#"+cur+" input:checked").length == 0 && cur == 'filter-windoors-tim'){  $('#TIM_choices').val(''); $('.timber').removeClass("active selected");}

            	// WINDOWS AND DOORS - Other materials/hybrids submenus
            	if($("#"+cur+" input:checked").length == 0 && cur == 'filter-windoors-other'){  $('#OTHER_choices').val(''); $('.other').removeClass("active selected");}

	            // force populate	
	            $("#filt1").val('filter-windoors'); 
             		
                $('#PVCu_choices').val('all');	// set hidden field  Windows and Doors;Vertical Sliders;Bi-Fold Doors
            	$('.pvcu').addClass("selected");
            	$(this).addClass("active");	

            	criteria = 'all';  // ALL sub-categories
            	type = 'con_profile';

            	// create / populate profile list - ajax            	
            	$.post('/index.php/ajax/create_profiles_list/'+dsn+'/'+criteria+'/'+type,

			      { 'dsn': dsn },

			      // Web server responds to the request
			      function(result) {
			        		        
			        // if the result is TRUE write data
			        if (result) {	
			          // alert(result);
			   
			          $('#profile_system_ajax_wd').empty().html(result);
			          	    
			        }
			        		        
			      });
            	       	
		    	$('.ifd-filter').hide();
				$('#filter-none').hide(); // Default message
		    	$('#filter-windoors').show();
		    	$('.menu-headers').removeClass("active");
		    	$(this).addClass("active");		
		    			    	
			});

            // PVCu sub-menus
            $('#pvcu_w,#pvcu_v,#pvcu_b').click(function() {  

				//alert($(this).attr('id'));
            	prod_count++;
				
				$('#maincat-pvcu').html('PVCu *');

            	// get id of current visible filter
            	var cur = $('.ifd-filter:visible').attr('id'); 

            	// reset filter title
            	if($(this).attr('id') == 'pvcu_v') { $("#filter_id_wd").text('Filter: PVCu Vertical sliding sash'); }
            	if($(this).attr('id') == 'pvcu_b') { $("#filter_id_wd").text('Filter: PVCu Bi-fold doors'); }
            	if($(this).attr('id') == 'pvcu_w') { $("#filter_id_wd").text('Filter: PVCu Windows & Doors'); }

                // set currently active class (have any check boxes been selected ?)
            	$("#"+cur+" input:checked").length > 0 ?  $(".active").addClass("selected") : $(".active").removeClass("selected"); 

            	// WINDOWS AND DOORS - Aluminium submenus
            	if($("#"+cur+" input:checked").length == 0 && cur == 'filter-windoors-al'){  $('#ALUM_choices').val(''); $('.alum').removeClass("active selected");}

            	// WINDOWS AND DOORS - Timber submenus
            	if($("#"+cur+" input:checked").length == 0 && cur == 'filter-windoors-tim'){  $('#TIM_choices').val(''); $('.timber').removeClass("active selected");}

            	// WINDOWS AND DOORS - Other materials/hybrids submenus
            	if($("#"+cur+" input:checked").length == 0 && cur == 'filter-windoors-other'){  $('#OTHER_choices').val(''); $('.other').removeClass("active selected");}

	            // force populate	
	            $("#filt1").val('filter-windoors');
            	
            	$('.pvcu').removeClass("selected");
            	$(this).toggleClass("selected");	// need to check what is 'selected'           	   
            	$('#PVCu_choices').val($(this).attr('id'));	// set hidden field
            	$('#maincat-pvcu').removeClass("active selected");

            	//alert($('#pvcu_w').hasClass('selected'));
            	
            	if($(this).attr('id') == 'pvcu_w'){
            		criteria = '1-1-1';  // group;product;material (referencing table 'systems_lookup')  wd
            	}
            	else if($(this).attr('id') == 'pvcu_v'){
            		criteria = '1-2-1';  // group;product;material vs
            	}
            	else{
            		criteria = '1-3-1';  // group;product;material  bd
            	}

            	type = 'con_profile';
            	
            	// create / populate profile list - ajax            	
            	$.post('/index.php/ajax/create_profiles_list/'+dsn+'/'+criteria+'/'+type,

			      { 'dsn': dsn },

			      // Web server responds to the request
			      function(result) {
			        		        
			        // if the result is TRUE write data
			        if (result) {	
			          // alert(result);
			          
			          $('#profile_system_ajax_wd').empty().html(result);
			          	    
			        }
			        		        
			      });
             	
		    	$('.ifd-filter').hide();
				$('#filter-none').hide(); // Default message
		    	$('#filter-windoors').show();	
		    	$('.menu-headers').removeClass("active");
		   // 	$(this).addClass("active");			    			
		    	
			});

            // WINDOWS AND DOORS - Aluminium
            $('#maincat-alu').click(function() {  // #maincat-alu,#maincat-timber,#maincat-other

				//alert($(this).attr('id'));
            	prod_count++;

            	$('#maincat-alu').html('Aluminium *');

				// set filter title
				$("#filter_id_wd_al").text('Filter: Aluminium');

            	// get id of current visible filter
            	var cur = $('.ifd-filter:visible').attr('id'); 

                // set currently active class (have any check boxes been selected ?)
            	$("#"+cur+" input:checked").length > 0 ?  $(".active").addClass("selected") : $(".active").removeClass("selected"); 

            	// WINDOWS AND DOORS - PVCu submenus
            	if($("#"+cur+" input:checked").length == 0 && cur == 'filter-windoors'){  $('#PVCu_choices').val(''); $('.pvcu').removeClass("active selected");}

            	// WINDOWS AND DOORS - Timber submenus
            	if($("#"+cur+" input:checked").length == 0 && cur == 'filter-windoors-tim'){  $('#TIM_choices').val(''); $('.timber').removeClass("active selected");}

            	// WINDOWS AND DOORS - Other materials/hybrids submenus
            	if($("#"+cur+" input:checked").length == 0 && cur == 'filter-windoors-other'){  $('#OTHER_choices').val(''); $('.other').removeClass("active selected");}


	            // force populate	
	            $("#filt6").val('filter-windoors-al'); 
             		
                $('#ALUM_choices').val('all');	// set hidden field  Windows and Doors;Bi-Fold Doors;Commercial glazing
            	$('.alum').addClass("selected");

            	criteria = 'all-al';  // ALL sub-categories
            	type = 'con_profile_al';

            	// create / populate profile list - ajax            	
            	$.post('/index.php/ajax/create_profiles_list/'+dsn+'/'+criteria+'/'+type,

			      { 'dsn': dsn },

			      // Web server responds to the request
			      function(result) {
			        		        
			        // if the result is TRUE write data
			        if (result) {	
			          // alert(result);
			   
			          $('#profile_system_ajax_wd_al').empty().html(result);
			          	    
			        }
			        		        
			      });
            	       	
		    	$('.ifd-filter').hide();
				$('#filter-none').hide(); // Default message
		    	$('#filter-windoors-al').show();
		    	$('.menu-headers').removeClass("active");
		    	$(this).addClass("active");		
		    			    	
			});

			
            // Aluminium sub-menus
            $('#alum_w,#alum_b,#alum_c').click(function() {  

				//alert($(this).attr('id'));
            	prod_count++;
				
				$('#maincat-alu').html('Aluminium *');

            	// get id of current visible filter
            	var cur = $('.ifd-filter:visible').attr('id'); 

            	// reset filter title
            	if($(this).attr('id') == 'alum_c') { $("#filter_id_wd_al").text('Filter: Aluminium Commercial glazing'); }
            	if($(this).attr('id') == 'alum_b') { $("#filter_id_wd_al").text('Filter: Aluminium Bi-fold doors'); }
            	if($(this).attr('id') == 'alum_w') { $("#filter_id_wd_al").text('Filter: Aluminium Windows & Doors'); }

                // set currently active class (have any check boxes been selected ?)
            	$("#"+cur+" input:checked").length > 0 ?  $(".active").addClass("selected") : $(".active").removeClass("selected"); 

            	// WINDOWS AND DOORS - PVCu submenus
            	if($("#"+cur+" input:checked").length == 0 && cur == 'filter-windoors'){  $('#PVCu_choices').val(''); $('.pvcu').removeClass("active selected");}

            	// WINDOWS AND DOORS - Timber submenus
            	if($("#"+cur+" input:checked").length == 0 && cur == 'filter-windoors-tim'){  $('#TIM_choices').val(''); $('.timber').removeClass("active selected");}

            	// WINDOWS AND DOORS - Other materials/hybrids submenus
            	if($("#"+cur+" input:checked").length == 0 && cur == 'filter-windoors-other'){  $('#OTHER_choices').val(''); $('.other').removeClass("active selected");}

	            // force populate	
	            $("#filt6").val('filter-windoors-al');
            	
            	$('.alum').removeClass("selected");
            	$(this).toggleClass("selected");	// need to check what is 'selected'           	   
            	$('#ALUM_choices').val($(this).attr('id'));	// set hidden field
            	$('#maincat-alu').removeClass("active selected");

            	//alert($('#pvcu_w').hasClass('selected'));
            	
            	if($(this).attr('id') == 'alum_w'){
            		criteria = '1-1-2';  // group;product;material (referencing table 'systems_lookup')  wd
            	}
            	else if($(this).attr('id') == 'alum_c'){
            		criteria = '1-3-2';  // group;product;material  (Specialist - Commercial glazing); perhaps '1-4-2' ?
            	}
            	else{
            		criteria = '1-2-2';  // group;product;material  bd
            	}

            	type = 'con_profile_al';

            	// create / populate profile list - ajax            	
            	$.post('/index.php/ajax/create_profiles_list/'+dsn+'/'+criteria+'/'+type,

			      { 'dsn': dsn },

			      // Web server responds to the request
			      function(result) {
			        		        
			        // if the result is TRUE write data
			        if (result) {	
			          // alert(result);
			          
			          $('#profile_system_ajax_wd_al').empty().html(result);
			          	    
			        }
			        		        
			      });
             	
		    	$('.ifd-filter').hide();
		    	$('#filter-windoors-al').show();	
				$('#filter-none').hide(); // Default message
		    	$('.menu-headers').removeClass("active");
		   // 	$(this).addClass("active");			    			
		    	
			});
            
            // WINDOWS AND DOORS - Timber
            $('#maincat-timber').click(function() {  // #maincat-alu,#maincat-timber,#maincat-other

				//alert($(this).attr('id'));
            	prod_count++;

            	$('#maincat-timber').html('Timber *');

				// set filter title
				$("#filter_id_wd_tim").text('Filter: Timber');

            	// get id of current visible filter
            	var cur = $('.ifd-filter:visible').attr('id'); 

                // set currently active class (have any check boxes been selected ?)
            	$("#"+cur+" input:checked").length > 0 ?  $(".active").addClass("selected") : $(".active").removeClass("selected"); 
            	
            	// WINDOWS AND DOORS - PVCu submenus
            	if($("#"+cur+" input:checked").length == 0 && cur == 'filter-windoors'){  $('#PVCu_choices').val(''); $('.pvcu').removeClass("active selected");}

            	// WINDOWS AND DOORS - Aluminium submenus
            	if($("#"+cur+" input:checked").length == 0 && cur == 'filter-windoors-al'){  $('#ALUM_choices').val(''); $('.alum').removeClass("active selected");}

            	// WINDOWS AND DOORS - Other materials/hybrids submenus
            	if($("#"+cur+" input:checked").length == 0 && cur == 'filter-windoors-other'){  $('#OTHER_choices').val(''); $('.other').removeClass("active selected");}

	            // force populate	
	            $("#filt7").val('filter-windoors-tim'); 
             		
                $('#TIM_choices').val('all');	// set hidden field  Windows and Doors;Vertical Sliders;Bi-Fold Doors
            	$('.timber').addClass("selected");

            	       	
		    	$('.ifd-filter').hide();
				$('#filter-none').hide(); // Default message
		    	$('#filter-windoors-tim').show();
		    	$('.menu-headers').removeClass("active");
		    	$(this).addClass("active");		
		    			    	
			});

         // WINDOWS AND DOORS - Timber - submenus 
            $('#tim_w,#tim_v').click(function() {  

				//alert($(this).attr('id'));
            	prod_count++;
				
				$('#maincat-timber').html('Timber *');

            	// get id of current visible filter
            	var cur = $('.ifd-filter:visible').attr('id'); 

            	// reset filter title
            	if($(this).attr('id') == 'tim_v') { $("#filter_id_wd_tim").text('Filter: Timber Vertical sliding sash'); }
            	if($(this).attr('id') == 'tim_w') { $("#filter_id_wd_tim").text('Filter: Timber Windows & Doors'); }

                // set currently active class (have any check boxes been selected ?)
            	$("#"+cur+" input:checked").length > 0 ?  $(".active").addClass("selected") : $(".active").removeClass("selected"); 

            	// WINDOWS AND DOORS - PVCu submenus
            	if($("#"+cur+" input:checked").length == 0 && cur == 'filter-windoors'){  $('#PVCu_choices').val(''); $('.pvcu').removeClass("active selected");}

            	// WINDOWS AND DOORS - Aluminium submenus
            	if($("#"+cur+" input:checked").length == 0 && cur == 'filter-windoors-al'){  $('#ALUM_choices').val(''); $('.alum').removeClass("active selected");}

            	// WINDOWS AND DOORS - Other materials/hybrids submenus
            	if($("#"+cur+" input:checked").length == 0 && cur == 'filter-windoors-other'){  $('#OTHER_choices').val(''); $('.other').removeClass("active selected");}

	            // force populate	
	            $("#filt7").val('filter-windoors-tim');
            	
            	$('.timber').removeClass("selected");
            	$(this).toggleClass("selected");	// need to check what is 'selected'           	   
            	$('#TIM_choices').val($(this).attr('id'));	// set hidden field
            	$('#maincat-timber').removeClass("active selected");
             	
		    	$('.ifd-filter').hide();
				$('#filter-none').hide(); // Default message
		    	$('#filter-windoors-tim').show();	
		    	$('.menu-headers').removeClass("active");		    			
		    	
			});

            // WINDOWS AND DOORS - Other materials/hybrids
            $('#maincat-other,#other_w').click(function() {  // #maincat-alu,#maincat-timber,#maincat-other

				//alert($(this).attr('id'));
            	prod_count++;

            	$('#maincat-other').html('Other materials/hybrids *');

				// set filter title
				$("#filter_id_wd_other").text('Filter: Other materials/hybrids (Windows & Doors)');

            	// get id of current visible filter
            	var cur = $('.ifd-filter:visible').attr('id'); 

                // set currently active class (have any check boxes been selected ?)
            	$("#"+cur+" input:checked").length > 0 ?  $(".active").addClass("selected") : $(".active").removeClass("selected"); 
            	
            	// WINDOWS AND DOORS - PVCu submenus
            	if($("#"+cur+" input:checked").length == 0 && cur == 'filter-windoors'){  $('#PVCu_choices').val(''); $('.pvcu').removeClass("active selected");}

            	// WINDOWS AND DOORS - Aluminium submenus
            	if($("#"+cur+" input:checked").length == 0 && cur == 'filter-windoors-al'){  $('#ALUM_choices').val(''); $('.alum').removeClass("active selected");}

            	// WINDOWS AND DOORS - Timber submenus
            	if($("#"+cur+" input:checked").length == 0 && cur == 'filter-windoors-tim'){  $('#TIM_choices').val(''); $('.timber').removeClass("active selected");}

	            // force populate	
	            $("#filt8").val('filter-windoors-other'); 
             		
                $('#OTHER_choices').val('all');	// set hidden field  Windows and Doors;Vertical Sliders;Bi-Fold Doors
            	$('.other').addClass("selected");

            	       	
		    	$('.ifd-filter').hide();
				$('#filter-none').hide(); // Default message
		    	$('#filter-windoors-other').show();
		    	$('.menu-headers').removeClass("active");

		    	if($(this).attr('id') == 'maincat-other') { $(this).addClass("active"); }		
		    			    	
			});
			
             					
		    // CONSERVATORY ROOFS
		    $('#maincat-roofs').click(function() {
		    	
		    	prod_count++;

		    	// get id of current visible filter
            	var cur = $('.ifd-filter:visible').attr('id'); 

            	$('#maincat-roofs').html('Conservatory roofs *');

            //	alert(cur);

                // set currently active class (have any check boxes been selected ?)
            	$("#"+cur+" input:checked").length > 0 ?  $(".active").addClass("selected") : $(".active").removeClass("selected"); 

            	// WINDOWS AND DOORS - PVCu submenus
            	if($("#"+cur+" input:checked").length == 0 && cur == 'filter-windoors'){  $('#PVCu_choices').val(''); $('.pvcu').removeClass("active selected");}

            	// WINDOWS AND DOORS - Aluminium submenus
            	if($("#"+cur+" input:checked").length == 0 && cur == 'filter-windoors-al'){  $('#ALUM_choices').val(''); $('.alum').removeClass("active selected");}

            	// WINDOWS AND DOORS - Timber submenus
            	if($("#"+cur+" input:checked").length == 0 && cur == 'filter-windoors-tim'){  $('#TIM_choices').val(''); $('.timber').removeClass("active selected");}

            	// WINDOWS AND DOORS - Other materials/hybrids submenus
            	if($("#"+cur+" input:checked").length == 0 && cur == 'filter-windoors-other'){  $('#OTHER_choices').val(''); $('.other').removeClass("active selected");}
            	
            	
	            // force populate	
	            $("#filt2").val('filter-cons-roofs');	

            	criteria = '3-1-1'; 
            	type = 'con_alu'; 

            	// create / populate profile list - ajax            	
            	$.post('/index.php/ajax/create_profiles_list/'+dsn+'/'+criteria+'/'+type,

			      { 'dsn': dsn },

			      // Web server responds to the request
			      function(result) {
			        		        
			        // if the result is TRUE write data
			        if (result) {	
			          // alert(result);
			          
			          $('#profile_system_ajax_cr').empty().html(result);
			          	    
			        }
			        		        
			      });
            	  	
		    	$('.ifd-filter').hide();
				$('#filter-none').hide(); // Default message
		    	$('#filter-cons-roofs').show();
		    	$('.menu-headers').removeClass("active");
		    	$(this).addClass("active");		
		    	
		    });	

			// COMPOSITE DOORS
			$('#maincat-comp').click(function() {
				
				prod_count++;

				// get id of current visible filter
            	var cur = $('.ifd-filter:visible').attr('id'); 

            	$('#maincat-comp').html('Composite doors *');

            	// set currently active class (have any check boxes been selected ?)
            	$("#"+cur+" input:checked").length > 0 ?  $(".active").addClass("selected") : $(".active").removeClass("selected"); 

            	// // WINDOWS AND DOORS - PVCu submenus
            	if($("#"+cur+" input:checked").length == 0 && cur == 'filter-windoors'){  $('#PVCu_choices').val(''); $('.pvcu').removeClass("active selected"); }

            	// WINDOWS AND DOORS - Aluminium submenus
            	if($("#"+cur+" input:checked").length == 0 && cur == 'filter-windoors-al'){  $('#ALUM_choices').val(''); $('.alum').removeClass("active selected");}

            	// WINDOWS AND DOORS - Timber submenus
            	if($("#"+cur+" input:checked").length == 0 && cur == 'filter-windoors-tim'){  $('#TIM_choices').val(''); $('.timber').removeClass("active selected");}

            	// WINDOWS AND DOORS - Other materials/hybrids submenus
            	if($("#"+cur+" input:checked").length == 0 && cur == 'filter-windoors-other'){  $('#OTHER_choices').val(''); $('.other').removeClass("active selected");}
            	
            	criteria = 'cd'; 
            	type = 'con_slab'; 

            	// create / populate profile list - ajax            	
            	$.post('/index.php/ajax/create_profiles_list_cd/'+dsn+'/'+criteria+'/'+type,

			      { 'dsn': dsn },

			      // Web server responds to the request
			      function(result) {
			        		        
			        // if the result is TRUE write data
			        if (result) {	
			          // alert(result);
			          
			          $('#profile_system_ajax_cd').empty().html(result);
			          	    
			        }
			        		        
			      });
            	
            	
	            // force populate	
	            $("#filt3").val('filter-com-doors');
            	
		    	$('.ifd-filter').hide();
				$('#filter-none').hide(); // Default message
		    	$('#filter-com-doors').show();
		    	$('.menu-headers').removeClass("active");
		    	$(this).addClass("active");		
		    	
			});
			
			// SEALED UNITS
			$('#maincat-igu').click(function() {
				
				prod_count++;

				// get id of current visible filter
            	var cur = $('.ifd-filter:visible').attr('id'); 

            	$('#maincat-igu').html('Sealed units (IGU\'s) *');

            	// set currently active class (have any check boxes been selected ?)
            	$("#"+cur+" input:checked").length > 0 ?  $(".active").addClass("selected") : $(".active").removeClass("selected"); 

            	// // WINDOWS AND DOORS - PVCu submenus
            	if($("#"+cur+" input:checked").length == 0 && cur == 'filter-windoors'){  $('#PVCu_choices').val(''); $('.pvcu').removeClass("active selected"); }

            	// WINDOWS AND DOORS - Aluminium submenus
            	if($("#"+cur+" input:checked").length == 0 && cur == 'filter-windoors-al'){  $('#ALUM_choices').val(''); $('.alum').removeClass("active selected");}

            	// WINDOWS AND DOORS - Timber submenus
            	if($("#"+cur+" input:checked").length == 0 && cur == 'filter-windoors-tim'){  $('#TIM_choices').val(''); $('.timber').removeClass("active selected");}

            	// WINDOWS AND DOORS - Other materials/hybrids submenus
            	if($("#"+cur+" input:checked").length == 0 && cur == 'filter-windoors-other'){  $('#OTHER_choices').val(''); $('.other').removeClass("active selected");}
            	
            	
	            // force populate
	            $("#filt4").val('filter-sealed-units');	
		    	
		    	$('.ifd-filter').hide();
				$('#filter-none').hide(); // Default message
		    	$('#filter-sealed-units').show();
		    	$('.menu-headers').removeClass("active");
		    	$(this).addClass("active");		
		    	
			});
		    
		    // ROOFLINE
		    $('#maincat-roofline').click(function() {

		    	// get id of current visible filter
            	var cur = $('.ifd-filter:visible').attr('id');

            	$('#maincat-roofline').html('Roofline *'); 

            	prod_count++;

            	// set currently active class (have any check boxes been selected ?)
            	$("#"+cur+" input:checked").length > 0 ?  $(".active").addClass("selected") : $(".active").removeClass("selected"); 

            	// WINDOWS AND DOORS - PVCu submenus
            	if($("#"+cur+" input:checked").length == 0 && cur == 'filter-windoors'){  $('#PVCu_choices').val(''); $('.pvcu').removeClass("active selected"); }

            	// WINDOWS AND DOORS - Aluminium submenus
            	if($("#"+cur+" input:checked").length == 0 && cur == 'filter-windoors-al'){  $('#ALUM_choices').val(''); $('.alum').removeClass("active selected");}

            	// WINDOWS AND DOORS - Timber submenus
            	if($("#"+cur+" input:checked").length == 0 && cur == 'filter-windoors-tim'){  $('#TIM_choices').val(''); $('.timber').removeClass("active selected");}

            	// WINDOWS AND DOORS - Other materials/hybrids submenus
            	if($("#"+cur+" input:checked").length == 0 && cur == 'filter-windoors-other'){  $('#OTHER_choices').val(''); $('.other').removeClass("active selected");}
            	
            	
	            // force populate	
	            $("#filt5").val('filter-roofline');	
          
            	criteria = '5-1-1'; 
                type = 'con_sup_brand';

            	// create / populate profile list - ajax            	
            	$.post('/index.php/ajax/create_profiles_list/'+dsn+'/'+criteria+'/'+type,

			      { 'dsn': dsn },

			      // Web server responds to the request
			      function(result) {
			        		        
			        // if the result is TRUE write data
			        if (result) {	
			          // alert(result);
			          
			          $('#profile_system_ajax_rl').empty().html(result);
			          	    
			        }
			        		        
			      });
		    	
		    	$('.ifd-filter').hide();
				$('#filter-none').hide(); // Default message
		    	$('#filter-roofline').show();
		    	$('.menu-headers').removeClass("active");
		    	$(this).addClass("active");		
		    	
			});

		 // XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX  FABRICATE / BUY-IN MESSAGES  XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
		   
			$('#con_act_2, #con_act_4').change(function() {  // buy-in
				if(this.checked){
				  $('#cr_buyin_message').show();
				}
				else{
				  $('#cr_buyin_message').hide();	
				}

				if($('#cr_buyin_message').is(':visible') && $('#cr_fab_message').is(':visible')) {
					$('.fab_buyin_messages').hide(); 
				}
				
			});

			// XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX  SELECT / DE-SELECT LIST CONTROLS  XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
			
			// XXXXXXX  MULTIPLE LISTS XXXXXXXXXXX

			// activity list
			$(".con_act_list").click(function() {
				//$('.con_act').attr('checked', !$('.con_act').is(':checked'));
				$(this).parent().find(".con_act").attr('checked', !$(".con_act").is(':checked'));
			});
			
			// markets list - ALL
			$('.con_mar_list').click(function() {
				$('.con_mar').attr('checked', !$('.con_mar').is(':checked'));
				//$(this).parent().find('.con_mar').attr('checked', !$('.con_mar').is(':checked'));
			});

			// markets list - one by one
			$('.m1,.m2,.m3,.m4').click(function() {
				var elem = $(this).attr('class').split(' ');
				$(this).is(':checked') ? $('.'+elem[1]).attr('checked', true) : $('.'+elem[1]).attr('checked', false);
				
			});

			// address type list - ALL
			$('.con_add_list').click(function() {
				$('.con_add').attr('checked', !$('.con_add').is(':checked'));
				//$(this).parent().find('.con_add').attr('checked', !$('.con_add').is(':checked'));
			});

			// address type list - one by one
			$('.a1,.a2,.a3,.a4,.a5,.a6').click(function() {
				var elem = $(this).attr('class').split(' ');
				$(this).is(':checked') ? $('.'+elem[1]).attr('checked', true) : $('.'+elem[1]).attr('checked', false);
				
			});


        
		

            // XXXXXXX  SINGLE LISTS XXXXXXXXXXX
			
			// Aluminium/PVCu list
			$('#con_alu_list').click(function() { 
				$('.con_alu').attr('checked', !$('.con_alu').is(':checked'));  // ajax
			});

			// Roofs per month list
			$('#con_rpm_list').click(function() {
				$('.con_rpm').attr('checked', !$('.con_rpm').is(':checked'));
			});

			// slab lists
			$('#con_slab_list').click(function() {
				$('.con_slab').attr('checked', !$('.con_slab').is(':checked')); 
			});
			
			// doors per week list
			$('#con_dpw_list').click(function() {
				$('.con_dpw').attr('checked', !$('.con_dpw').is(':checked')); 
			});

			// units per week
			$('#con_upw_list').click(function() {
				$('.con_upw').attr('checked', !$('.con_upw').is(':checked'));
			});

			// supplier brands
			$('#con_sup_brand_list').click(function() {
				$('.con_sup_brand').attr('checked', !$('.con_sup_brand').is(':checked'));  //ajax
			});

			
			// profile list (Main Products / sub menus)
			$('#con_profile_list').click(function() {				
				$('.con_profile').attr('checked', !$('.con_profile').is(':checked')); // ajax PVCu				
			});
			$('#con_profile_list_al').click(function() {				
				$('.con_profile_al').attr('checked', !$('.con_profile_al').is(':checked')); // ajax	Aluminium			
			});

			
			// frames per week list
			$('#con_fpw_list').click(function() {
				$('.con_fpw').attr('checked', !$('.con_fpw').is(':checked'));
			});

			// sectors SBD
			$('#sectors_sbd').click(function() {
				$('.sbd').attr('checked', !$('.sbd').is(':checked'));
			});

			// sectors NRG
			$('#sectors_nrg').click(function() {
				$('.nrg').attr('checked', !$('.nrg').is(':checked'));
			});

		});	// end of document ready
		
	
			
			
