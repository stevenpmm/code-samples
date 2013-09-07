      
           
	<script language="javascript" type="text/javascript">

	     function suppress_ifd(){
	    	    $('#listbuilder-left').hide();
				$('#listbuilder-right').hide();
				$('.ifd-filter_s').hide();
	     }

	    $(document).ready(function() {

	    	// CHECK USER PERMISSIONS (Display options) FOR ALL SUBSCRIBED DATABASES 
	    	<?php $fenflag = false; ?>	
	    	
			// ajax checks
			<?php foreach ($db_list as $db): ?>	
			<?php $fields = explode(':',$db); ?>				

			<?php if($fields[1] == 'nrg'){ ?>
			    set_default_db_products('nrg');
			<?php } ?>

			<?php if($fields[1] == 'asd'){ ?>
			    set_default_db_products('asd');
			<?php } ?>

			<?php if($fields[1] == 'mcd'){ ?>
			    set_default_db_products('mcd');
			<?php } ?>	

			<?php if($fields[1] == 'sbd'){ ?>
			    set_default_db_products('sbd');
			<?php } ?>
			// END ajax checks			
				 
			<?php if($fields[1] == 'ifd' || $fields[1] == 'roi'){ $this->Search2->_get_database_permissions($fields[1]); ?>
			<?php $fenflag = true; ?>	

			    //  IFD / ROI start here ....
			      
				// IFD Main Products - any windows / doors
				<?php if(!strstr($this->session->userdata('products'), 'wd:')){  ?>
				    $('#maincat-pvcu,#maincat-alu,#maincat-timber,#maincat-other,.pvcu,.alum,.timber,.other').unbind('click');
					$('#maincat-pvcu,#maincat-alu,#maincat-timber,#maincat-other').addClass('unavailable');
					$('.pvcu, .alum, .timber, .other').addClass('unavailable');		
				<?php } ?>

				// IFD Main Products - PVCu
				<?php if(!strstr($this->session->userdata('products'), 'wd:pvc')){  ?>
				    $('#maincat-pvcu,.pvcu').unbind('click');
					$('#maincat-pvcu').addClass('unavailable');
					$('.pvcu').addClass('unavailable');		
				<?php } ?>

				// IFD Main Products - Aluminium
				<?php if(!strstr($this->session->userdata('products'), 'wd:alu')){  ?>
				    $('#maincat-alu,.alum').unbind('click');
					$('#maincat-alu').addClass('unavailable');
					$('.alum').addClass('unavailable');		
				<?php } ?>

				// IFD Main Products - Timber
				<?php if(!strstr($this->session->userdata('products'), 'wd:tim')){  ?>
				    $('#maincat-timber,.timber').unbind('click');
					$('#maincat-timber').addClass('unavailable');
					$('.timber').addClass('unavailable');		
				<?php } ?>

				// IFD Main Products - Other
				<?php if(!strstr($this->session->userdata('products'), 'wd:oth')){  ?>
				    $('#maincat-other,.other').unbind('click');
					$('#maincat-other').addClass('unavailable');
					$('.other').addClass('unavailable');		
				<?php } ?> 
				
				// Specialist Products
				<?php if(!strstr($this->session->userdata('products'), 'cr')){  ?> // Conservatory roofs
			        $('#maincat-roofs').unbind('click');
			        //$('#maincat-roofs').css('color','#999999').css('font-style','italic').css('cursor','default');
					$('#maincat-roofs').addClass('unavailable');
			    <?php } ?>
			    <?php if(!strstr($this->session->userdata('products'), 'cd')){  ?> // Composite doors
			        $('#maincat-comp').unbind('click');
			        //$('#maincat-comp').css('color','#999999').css('font-style','italic').css('cursor','default');
					$('#maincat-comp').addClass('unavailable');
			    <?php } ?>
			    <?php if(!strstr($this->session->userdata('products'), 'su')){  ?> // Sealed units
			        $('#maincat-igu').unbind('click');
			        //$('#maincat-igu').css('color','#999999').css('font-style','italic').css('cursor','default');
					$('#maincat-igu').addClass('unavailable');
			    <?php } ?>
			    <?php if(!strstr($this->session->userdata('products'), 'rl')){  ?> // Roofline
			        $('#maincat-roofline').unbind('click');
			        //$('#maincat-roofline').css('color','#999999').css('font-style','italic').css('cursor','default');     
					$('#maincat-roofline').addClass('unavailable');				
			    <?php } ?>	

		    <?php } ?>

		    <?php endforeach;?> 

            <?php if(!$fenflag){?> 
                suppress_ifd();
                $('#filter-'+'<?=$fields[1];?>').show(); // display sector view
            <?php }?>  
                

        });	// end of document ready

	</script>		   
        
                   
     <div id="content">  
     
       <!-- Array ( [0] => Architect & Specifier [1] => asd ) -->
   
         <form id="listbuilder_form" name="listbuilder_form" action="#" method="POST">  
         
                    <div id="listbuilder-header">
							<h1>List Builder</h1>
							
							<select class="" id="database-select" name="">
							    <?php if(count($db_list)>1){?>
								  <!--  <option value="all">All Databases</option> -->
								<?php }?>   
														
								<?php foreach ($db_list as $db): ?>	
								<?php $fields = explode(':',$db); 							
								  if($fields[1] == 'prd'){continue;}
								?>				
								   <option  <?=($this->input->post('database-select') == $fields[1] ? 'selected="selected"' : '')?> value="<?=$fields[1];?>"><?=$fields[0];?></option>					
								<?php endforeach;?> 					
							</select>
							
							
					</div>
					
<div id="record-count">
	<div id="record-count-spacer">
		Current number of records in this list: 
		<div id="record-count-box">
			<img id="ajax-anim_1" height="15px" height="15px" class="ajax-anim" src="/media/images/ajax-loader.gif">
			<span id="rec_count" style="width:100px;"></span>
		</div>
	</div>
	<input id="btn_submit" class="updaterecordcount-button" type="button" value="Update record count" />
</div>
					
					<div id="listbuilder-header-buttons">	
					   
					    <input id="previous-button2_top" class="previous-button" type="button" value="Previous" /> 
					    <input id="previous-button2" class="previous-button" type="button" value="Previous" /> 					    
						<input id="clear-filter-button" class="clear-filter-button" type="button" value="Clear Filters" /> 
						<input id="next-button" class="next-button next-button_top" type="button" value="Next" />					
						<input id="next-button2_top" class="next-button next-button_top" type="button" value="Next" />
						
					</div>
					
					<img style="float:right" class="ajax-anim"  id="ajax-anim_33" src="/media/images/ajax-loader.gif">
     
  <!-- Start listbuilder-step1 -->
     	<div class="clear" style="clear:both">&nbsp;</div>
				<div id="listbuilder-step1">	
				
				    <input type = "hidden" class = "hidden" id="filt1" name = "filters[]" value = "" />
				    <input type = "hidden" class = "hidden" id="filt2" name = "filters[]" value = "" />
				    <input type = "hidden" class = "hidden" id="filt3" name = "filters[]" value = "" />
				    <input type = "hidden" class = "hidden" id="filt4" name = "filters[]" value = "" />
				    <input type = "hidden" class = "hidden" id="filt5" name = "filters[]" value = "" />	
				    <input type = "hidden" class = "hidden" id="filt6" name = "filters[]" value = "" />	
				    <input type = "hidden" class = "hidden" id="filt7" name = "filters[]" value = "" />	
				    <input type = "hidden" class = "hidden" id="filt8" name = "filters[]" value = "" />	
											
						<div class="clear">&nbsp;</div>
						<div id="listbuilder-left">
						
							<div class="box" id="main-products">
								<h2 class="boxhead">Main Products</h2>
								
								<h3 class="menu-headers" id="maincat-pvcu">PVCu</h3>
								<ul>
									<!-- <li class="selected">Windows &amp; doors</li> -->
									<li class="pvcu" id="pvcu_w">Windows &amp; doors</li>
									<li class="pvcu" id="pvcu_v">Vertical sliding sash</li>
									<li class="pvcu" id="pvcu_b">Bi-fold doors</li>
								</ul>
								
								<h3 class="menu-headers" id="maincat-alu">Aluminium</h3>
								<ul>
									<li class="alum" id="alum_w">Windows &amp; doors</li>
									<li class="alum" id="alum_b">Bi-fold doors</li>
									<li class="alum" id="alum_c">Commercial glazing</li>
								</ul>
								
								<h3 class="menu-headers" id="maincat-timber">Timber</h3>
								<ul>
									<li class="timber" id="tim_w">Windows &amp; doors</li>
									<li class="timber" id="tim_v">Vertical sliding sash</li>
								</ul>
								
								<h3 class="menu-headers" id="maincat-other">Other materials/hybrids</h3>
								<ul>
									<li class="other" id="other_w">Windows &amp; doors</li>
								</ul>
							</div>
							
							<div class="box" id="specialist-products">
								<h2 class="boxhead">Specialist Products</h2>								
								<h3 class="menu-headers" id="maincat-roofs">Conservatory roofs</h3>
								<h3 class="menu-headers" id="maincat-comp">Composite doors</h3>
								<h3 class="menu-headers" id="maincat-igu">Sealed units (IGU's)</h3>
								<h3 class="menu-headers" id="maincat-roofline">Roofline</h3>
							</div>
							
						</div>
						
						<div id="listbuilder-right">
							
							<!-- Fabricate popup -->
							<div id="cr_buyin_message" class="fab_buyin_messages box">
									  <h3 class="boxhead">Important!</h3>
									  <p>You have selected "Buy-in" as criteria. Some companies who buy-in PVCu OR Conservatory Roofs also fabricate. Please tick the following option if you only want companies who buy-in but do not fabricate:</p>
									  <p><input type="checkbox" class="excludes" name="exclude_fabricate" id="exclude_fabricate" value="exclude_fabricate" /> Exclude companies who fabricate</p>
							</div>		    
							<div class="clear">&nbsp;</div>
							<!-- End fabricate popup -->
							
							<!-- INSTALLER OPTIONS -->
						    <div id="popup-installer-dialogue" class="box">
							<h2 style="text-align:left;" class="boxhead">Installer Options (please select) &nbsp;</h2>
							<p class="center" style="margin-bottom:0px;">
								<select style="width:130px;" name="installer-dialogue-options" id="installer-dialogue-options">
									<option selected value="0">No preference</option>
									<option value="1">Only include</option>
									<option value="2">Do not include</option>
								</select>
							</p>
							<div class="clear">&nbsp;</div>
							</div>
							<!-- END INSTALLER OPTIONS -->
						
<!-- START OF <div id="filter-cons-roofs" class="ifd-filter"> -->
						
						<div id="filter-cons-roofs" class="ifd-filter">
							<div class="box">
								<h2 id="filter_id" class="boxhead">Filter: Conservatory Roofs</h2>
								<div class="listbuilder-column">
								
								
				
				<!-- ACTIVITY -->					
									<h3 class="con_act_list">Activity</h3>
									
									<ul id="activity">	
									
										<?php if(strstr($this->session->userdata('products'),'cr:f')){ ?>							
										   <li><input type="checkbox" id="con_act_1" name="con_act[]" class="con_act" value="fabricate"/> Fabricate roofs<li>
										<?php }?>
										
										<?php if(strstr($this->session->userdata('products'),'cr:b')){ ?>
										   <li><input type="checkbox" id="con_act_2" name="con_act[]" class="con_act" value="buy-In"/> Buy-in roofs<li>
										<?php }?>
										
										   <!--  
										   <li><input type="checkbox" name="con_act[]" class="con_act" value="installer"/> Install roofs<li>
										   -->
										  
									</ul>
						
			
				<!-- MARKETS -->
									
									<h3 class="con_mar_list">Markets Served</h3>
									<ul id="markets_served">
										<li><input type="checkbox" name="con_mar[]" class="con_mar m1" value="trade" /> Trade<li>
										<li><input type="checkbox" name="con_mar[]" class="con_mar m2" value="domestic" /> Domestic<li>
										<li><input type="checkbox" name="con_mar[]" class="con_mar m3" value="commercial" /> Commercial<li>
										<li><input type="checkbox" name="con_mar[]" class="con_mar m4" value="new build" /> New-build<li>
									</ul>
									
									
				<!-- ADDRESS TYPES -->
									<h3 class="con_add_list">Address Type</h3>
									<ul id="address_type">  <!-- NOTE SYSTEM CONTAINS ONLY HeadOffice;Showroom;BranchOffice;Factory; -->
										<li><input type="checkbox" name="con_add[]" class="con_add a1" value="headoffice" /> Head Office<li>
										<li><input type="checkbox" name="con_add[]" class="con_add a2" value="showroom" /> Showroom<li>
										<li><input type="checkbox" name="con_add[]" class="con_add a3" value="factory" /> Factory<li>
										<li><input type="checkbox" name="con_add[]" class="con_add a4" value="trade" /> Trade Counter<li>
										<li><input type="checkbox" name="con_add[]" class="con_add a5" value="depot" /> Depot/Warehouse<li>
										<li><input type="checkbox" name="con_add[]" class="con_add a6" value="branchoffice" /> Branch Office<li>
									</ul>
								</div>
								
			 <!-- Aluminium/PVCu - ajax -->
								
								<div class="listbuilder-column">
								
									<h3 id="con_alu_list">Aluminium/PVCu</h3>
									<input type="radio" class="profile_radio_select"  id="con_alu_list_s" name="con_alu_list_radio" checked value="0" />Select
							        <input type="radio" id="con_alu_list_d" name="con_alu_list_radio" value="1" />De-select
							        <p></p>
									
									<span id="profile_system_ajax_cr"></span>
									
								</div>
								
			<!-- Roofs per month -->
								
								<div class="listbuilder-column">
									<h3 id="con_rpm_list">Roofs per month</h3>
									<ul>
									    
									    <li><input type="checkbox" name="con_rpm[]" class="con_rpm" value="1 to 10" />1 to 10<li>
									    <li><input type="checkbox" name="con_rpm[]" class="con_rpm" value="1 to 25" />1 to 25<li>
									    <li><input type="checkbox" name="con_rpm[]" class="con_rpm" value="11 to 25" />11 to 25<li>
									    <li><input type="checkbox" name="con_rpm[]" class="con_rpm" value="26 to 50" />26 to 50<li>
 								        <li><input type="checkbox" name="con_rpm[]" class="con_rpm" value="51 to 100" />51 to 100<li>
									    <li><input type="checkbox" name="con_rpm[]" class="con_rpm" value="101 to 200" />101 to 200<li>		   
									    <li><input type="checkbox" name="con_rpm[]" class="con_rpm" value="201 to 500" />201 to 500<li>
									    <li><input type="checkbox" name="con_rpm[]" class="con_rpm" value="501 to 1000" />501 to 1000<li>
									    <li><input type="checkbox" name="con_rpm[]" class="con_rpm" value="1000+" />1000+<li>
									</ul>
								</div>
								<div class="clear">&nbsp;</div>
								
							</div>
						
						</div> 
						
<!-- END OF <div id="filter-cons-roofs" class="ifd-filter"> -->

<!-- START OF <div id="filter-com-doors" class="ifd-filter"> -->
						
						<div id="filter-com-doors" class="ifd-filter">
							<div class="box">
								<h2 id="filter_id" class="boxhead">Filter: Composite Doors</h2>
								<div class="listbuilder-column">
																
								
				
				<!-- ACTIVITY -->					
									<h3 class="con_act_list">Activity</h3>
									
									<ul id="activity">
									    <?php if(strstr($this->session->userdata('products'),'cd:f')){ ?>									
										  <li><input type="checkbox" name="com_act[]" class="con_act" value="fabricate"/> Fabricate<li>
										<?php }?>
										
										<?php if(strstr($this->session->userdata('products'),'cd:b')){ ?>									
										  <li><input type="checkbox" name="com_act[]" class="con_act" value="buy-In"/> Buy in complete doors<li>
										<?php }?>
										
										<!-- 
										   <li><input type="checkbox" name="com_act[]" class="con_act" value="installer"/> Install<li>
										    -->
										
									</ul>
					
			
				<!-- MARKETS -->
									
									<h3 class="con_mar_list">Markets Served</h3>
									<ul id="markets_served">
										<li><input type="checkbox" name="con_mar[]" class="con_mar m1" value="trade" /> Trade<li>
										<li><input type="checkbox" name="con_mar[]" class="con_mar m2" value="domestic" /> Domestic<li>
										<li><input type="checkbox" name="con_mar[]" class="con_mar m3" value="commercial" /> Commercial<li>
										<li><input type="checkbox" name="con_mar[]" class="con_mar m4" value="new build" /> New-build<li>
									</ul>
									
									
				<!-- ADDRESS TYPES -->
									<h3 class="con_add_list">Address Type</h3>
									<ul id="address_type">  <!-- NOTE SYSTEM CONTAINS ONLY HeadOffice;Showroom;BranchOffice;Factory; -->
										<li><input type="checkbox" name="con_add[]" class="con_add a1" value="headoffice" /> Head Office<li>
										<li><input type="checkbox" name="con_add[]" class="con_add a2" value="showroom" /> Showroom<li>
										<li><input type="checkbox" name="con_add[]" class="con_add a3" value="factory" /> Factory<li>
										<li><input type="checkbox" name="con_add[]" class="con_add a4" value="trade" /> Trade Counter<li>
										<li><input type="checkbox" name="con_add[]" class="con_add a5" value="depot" /> Depot/Warehouse<li>
										<li><input type="checkbox" name="con_add[]" class="con_add a6" value="branchoffice" /> Branch Office<li>
									</ul>
								</div>
								
			 <!-- Slab Supplier 
								
								<div class="listbuilder-column">
									<h3 id="con_slab_list">Slab Supplier</h3>
									<ul id="">  
										<li><input type="checkbox" name="con_slab[]" class="con_slab" value="fabricate" /> Full fabricate (blank slabs)<li>									
									</ul>
								</div>	
								
			-->	
			
			<!--Slab Supplier - ajax -->
								
								<div class="listbuilder-column">
								
									<h3 id="con_slab_list">Slab Supplier</h3>
									<input type="radio" class="profile_radio_select"  id="con_slab_list_s" name="con_slab_list_radio" checked value="0" />Select
							        <input type="radio" id="con_slab_list_d" name="con_slab_list_radio" value="1" />De-select
							        <p></p>
									
									<span id="profile_system_ajax_cd"></span>
									
								</div>				
									
			<!-- Doors per week -->
								<div class="listbuilder-column">						
									<h3 id="con_dpw_list">Doors per week</h3>
									<ul>									    
									    <li><input type="checkbox" name="com_dpw[]" class="con_dpw" value="1 to 10" />1 to 10<li>							    
									    <li><input type="checkbox" name="com_dpw[]" class="con_dpw" value="11 to 25" />11 to 25<li>
									    <li><input type="checkbox" name="com_dpw[]" class="con_dpw" value="26 to 50" />26 to 50<li>
 								        <li><input type="checkbox" name="com_dpw[]" class="con_dpw" value="51 to 100" />51 to 100<li>
									    <li><input type="checkbox" name="com_dpw[]" class="con_dpw" value="101 to 200" />101 to 200<li>		   
								        <li><input type="checkbox" name="com_dpw[]" class="con_dpw" value="201 to 500" />201 to 500<li>
									    <li><input type="checkbox" name="com_dpw[]" class="con_dpw" value="501 to 1000" />501 to 1000<li>
									    <li><input type="checkbox" name="com_dpw[]" class="con_dpw" value="1000+" />1000+<li>
									</ul>								
								</div>
								
								<div class="clear">&nbsp;</div>
								
							</div>
						
						</div> 
						
<!-- END OF <div id="filter-com-doors" class="ifd-filter"> -->

<!-- START OF <div id="filter-sealed-units" class="ifd-filter"> -->
						
						<div id="filter-sealed-units" class="ifd-filter">
							<div class="box">
								<h2 id="filter_id" class="boxhead">Filter: Sealed Units (IGU's)</h2>
								<div class="listbuilder-column">
								
								
				
				<!-- ACTIVITY -->					
									<h3 class="con_act_list">Activity</h3>
									
									<ul id="activity">	
									    <?php if(strstr($this->session->userdata('products'),'su:m')){ ?>								
										  <li><input type="checkbox" name="su_act[]" class="con_act" value="manufacture"/> Manufacture IGU's<li>
										<?php }?>
										
										<?php if(strstr($this->session->userdata('products'),'su:b')){ ?>
										  <li><input type="checkbox" name="su_act[]" class="con_act" value="buy-In"/> Buy in IGU's<li>
										<?php }?>							
									</ul>
					
			
				<!-- MARKETS -->
									
									<h3 class="con_mar_list">Markets Served</h3>
									<ul id="markets_served">
										<li><input type="checkbox" name="con_mar[]" class="con_mar m1" value="trade" /> Trade<li>
										<li><input type="checkbox" name="con_mar[]" class="con_mar m2" value="domestic" /> Domestic<li>
										<li><input type="checkbox" name="con_mar[]" class="con_mar m3" value="commercial" /> Commercial<li>
										<li><input type="checkbox" name="con_mar[]" class="con_mar m4" value="new build" /> New-build<li>
									</ul>
									
									
				<!-- ADDRESS TYPES -->
									<h3 class="con_add_list">Address Type</h3>
									<ul id="address_type">  <!-- NOTE SYSTEM CONTAINS ONLY HeadOffice;Showroom;BranchOffice;Factory; -->
										<li><input type="checkbox" name="con_add[]" class="con_add a1" value="headoffice" /> Head Office<li>
										<li><input type="checkbox" name="con_add[]" class="con_add a2" value="showroom" /> Showroom<li>
										<li><input type="checkbox" name="con_add[]" class="con_add a3" value="factory" /> Factory<li>
										<li><input type="checkbox" name="con_add[]" class="con_add a4" value="trade" /> Trade Counter<li>
										<li><input type="checkbox" name="con_add[]" class="con_add a5" value="depot" /> Depot/Warehouse<li>
										<li><input type="checkbox" name="con_add[]" class="con_add a6" value="branchoffice" /> Branch Office<li>
									</ul>
								</div>
								
									
			<!-- Units per week -->
								<div class="listbuilder-column">						
									<h3 id="con_upw_list">Units per week</h3>
									<ul>									    
									    <li><input type="checkbox" name="su_upw[]" class="con_upw" value="1 to 50" />1 to 50<li>							    									  
 								        <li><input type="checkbox" name="su_upw[]" class="con_upw" value="51 to 100" />51 to 100<li>
									    <li><input type="checkbox" name="su_upw[]" class="con_upw" value="101 to 500" />101 to 200<li>		   					    
									    <li><input type="checkbox" name="su_upw[]" class="con_upw" value="501 to 1000" />501 to 1000<li>
									    <li><input type="checkbox" name="su_upw[]" class="con_upw" value="1001 to 2000" />1001 to 2000<li>
									    <li><input type="checkbox" name="su_upw[]" class="con_upw" value="2001 to 5000" />2001 to 5000<li>
									    <li><input type="checkbox" name="su_upw[]" class="con_upw" value="5001 to 10000" />5001 to 10000<li>
									    <li><input type="checkbox" name="su_upw[]" class="con_upw" value="10001 to 20000" />10001 to 20000<li>								    
									    <li><input type="checkbox" name="su_upw[]" class="con_upw" value="20000+" />20000+<li>
									</ul>								
								</div>
								
								<div class="clear">&nbsp;</div>
								
							</div>
						
						</div> 
						
<!-- END OF <div id="filter-sealed-units" class="ifd-filter"> -->

<!-- START OF <div id="filter-roofline" class="ifd-filter"> -->
						
						<div id="filter-roofline" class="ifd-filter">
							<div class="box">
								<h2 id="filter_id" class="boxhead">Filter: Roofline</h2>
								<div class="listbuilder-column">
								
								
								
				
				<!-- ACTIVITY -->					
									<h3 class="con_act_list">Activity</h3>
									
									<ul id="activity">	
									      <?php if(strstr($this->session->userdata('products'),'rl:i')){ ?>									
										     <li><input type="checkbox" name="rl_act[]" class="con_act" value="installer"/> Installers of roofline<li>
										  <?php }?>						
									</ul>
					
			
				<!-- MARKETS -->
									
									<h3 class="con_mar_list">Markets Served</h3>
									<ul id="markets_served">
										<li><input type="checkbox" name="con_mar[]" class="con_mar m1" value="trade" /> Trade<li>
										<li><input type="checkbox" name="con_mar[]" class="con_mar m2" value="domestic" /> Domestic<li>
										<li><input type="checkbox" name="con_mar[]" class="con_mar m3" value="commercial" /> Commercial<li>
										<li><input type="checkbox" name="con_mar[]" class="con_mar m4" value="new build" /> New-build<li>
									</ul>
									
									
				<!-- ADDRESS TYPES -->
									<h3 class="con_add_list">Address Type</h3>
									<ul id="address_type">  <!-- NOTE SYSTEM CONTAINS ONLY HeadOffice;Showroom;BranchOffice;Factory; -->
										<li><input type="checkbox" name="con_add[]" class="con_add a1" value="headoffice" /> Head Office<li>
										<li><input type="checkbox" name="con_add[]" class="con_add a2" value="showroom" /> Showroom<li>
										<li><input type="checkbox" name="con_add[]" class="con_add a3" value="factory" /> Factory<li>
										<li><input type="checkbox" name="con_add[]" class="con_add a4" value="trade" /> Trade Counter<li>
										<li><input type="checkbox" name="con_add[]" class="con_add a5" value="depot" /> Depot/Warehouse<li>
										<li><input type="checkbox" name="con_add[]" class="con_add a6" value="branchoffice" /> Branch Office<li>
									</ul>
								</div>
								
									
			<!-- Supplier brands -->
								<div class="listbuilder-column">
														
									<h3 id="con_sup_brand_list">Supplier brands</h3>
									<input type="radio" class="profile_radio_select"  id="con_sup_brand_list_s" name="con_sup_brand_list_radio" checked value="0" />Select
							        <input type="radio" id="con_sup_brand_list_d" name="con_sup_brand_list_radio" value="1" />De-select
							        <p></p>
									
									<span id="profile_system_ajax_rl"></span>
																
								</div>
								
								<div class="clear">&nbsp;</div>
								
							</div>
						
						</div> 
						
<!-- END OF <div id="filter-roofline" class="ifd-filter"> -->

<!-- START OF <div id="filter-windoors" class="ifd-filter"> PVCu -->

             <input type = "hidden" class = "hidden" name = "PVCu_choices" id = "PVCu_choices" value = "" />
						
						<div id="filter-windoors" class="ifd-filter">
							<div class="box">
								<h2 id="filter_id_wd" class="boxhead">Filter: PVCu</h2>
								<div class="listbuilder-column">
								
								
								
				
				<!-- ACTIVITY -->					
									<h3 class="con_act_list">Activity</h3>
									
									<ul id="activity">
									    <?php if(strstr($this->session->userdata('products'),'wd:f') && strstr($this->session->userdata('products'),'wd:pvc')){ ?>									
											<li><input type="checkbox" id="con_act_3" name="wd_act[]" class="con_act" value="fabricate"/> Fabricate<li>
										<?php }?>
										
										<?php if(strstr($this->session->userdata('products'),'wd:b') && strstr($this->session->userdata('products'),'wd:pvc')){ ?>
											<li><input type="checkbox" id="con_act_4" name="wd_act[]" class="con_act" value="buy-In"/> Buy in<li>
										<?php }?>
											
											<!-- 								
											<li><input type="checkbox" id="" name="wd_act[]" class="con_act" value="installer"/> Install<li>
											 -->					
									</ul>
					
			
				<!-- MARKETS -->
									
									<h3 class="con_mar_list">Markets Served</h3>
									<ul id="markets_served">
									    <li><input type="checkbox" name="con_mar[]" class="con_mar m1" value="trade" /> Trade<li>
										<li><input type="checkbox" name="con_mar[]" class="con_mar m2" value="domestic" /> Domestic<li>
										<li><input type="checkbox" name="con_mar[]" class="con_mar m3" value="commercial" /> Commercial<li>
										<li><input type="checkbox" name="con_mar[]" class="con_mar m4" value="new build" /> New-build<li>
									</ul>
									
									
				<!-- ADDRESS TYPES -->
									<h3 class="con_add_list">Address Type</h3>
									<ul id="address_type">  <!-- NOTE SYSTEM CONTAINS ONLY HeadOffice;Showroom;BranchOffice;Factory; -->
										<li><input type="checkbox" name="con_add[]" class="con_add a1" value="headoffice" /> Head Office<li>
										<li><input type="checkbox" name="con_add[]" class="con_add a2" value="showroom" /> Showroom<li>
										<li><input type="checkbox" name="con_add[]" class="con_add a3" value="factory" /> Factory<li>
										<li><input type="checkbox" name="con_add[]" class="con_add a4" value="trade" /> Trade Counter<li>
										<li><input type="checkbox" name="con_add[]" class="con_add a5" value="depot" /> Depot/Warehouse<li>
										<li><input type="checkbox" name="con_add[]" class="con_add a6" value="branchoffice" /> Branch Office<li>
									</ul>
								</div>
								
									
			<!-- Profile System - ajax -->
								<div class="listbuilder-column">	
													
									<h3 id="con_profile_list">Profile System</h3>
									<input type="radio" class="profile_radio_select"  id="con_profile_list_s" name="con_profile_list_radio" checked value="0" />Select
							        <input type="radio" id="con_profile_list_d" name="con_profile_list_radio" value="1" />De-select
							        <p></p>
									
									<span id="profile_system_ajax_wd"></span>
																	
								</div>
								
			<!-- Frames per week -->					
								<div class="listbuilder-column">						
									<h3 id="con_fpw_list">Frames per week</h3>
									<ul>									    
									    <li><input type="checkbox" name="wd_fpw[]" class="con_fpw" value="1 to 25" />1 to 25<li>							    									    
									    <li><input type="checkbox" name="wd_fpw[]" class="con_fpw" value="26 to 50" />26 to 50<li>
 								        <li><input type="checkbox" name="wd_fpw[]" class="con_fpw" value="51 to 100" />51 to 100<li>
									    <li><input type="checkbox" name="wd_fpw[]" class="con_fpw" value="101 to 250" />101 to 250<li>		   
									    <li><input type="checkbox" name="wd_fpw[]" class="con_fpw" value="251 to 500" />251 to 500<li>
									    <li><input type="checkbox" name="wd_fpw[]" class="con_fpw" value="501 to 1000" />501 to 1000<li>
									    
									    <!--  
									    <li><input type="checkbox" name="wd_fpw[]" class="con_fpw" value="1001 to 2000" />1001 to 2000<li>
									    <li><input type="checkbox" name="wd_fpw[]" class="con_fpw" value="2001 to 3000" />2001 to 3000<li>
									    -->
									    
									    <li><input type="checkbox" name="wd_fpw[]" class="con_fpw" value="1001 to 3000" />1001 to 3000<li>
									    
									    <li><input type="checkbox" name="wd_fpw[]" class="con_fpw" value="3000+" />3000+<li>
									</ul>								
								</div>
								
								<div class="clear">&nbsp;</div>
								
							</div>
						
						</div> 
						
<!-- END OF <div id="filter-windoors" class="ifd-filter"> PVCu-->

<!-- START OF <div id="filter-windoors-al" class="ifd-filter"> Aluminium -->

             <input type = "hidden" class = "hidden" name = "ALUM_choices" id = "ALUM_choices" value = "" />		
						
						<div id="filter-windoors-al" class="ifd-filter">
							<div class="box">
								<h2 id="filter_id_wd_al" class="boxhead">Filter: Aluminium</h2>
								<div class="listbuilder-column">
								
								
								
				
				<!-- ACTIVITY -->					
									<h3 class="con_act_list">Activity</h3>
									
									<ul id="activity">
									<?php if(strstr($this->session->userdata('products'),'wd:f') && strstr($this->session->userdata('products'),'wd:alu')){ ?>																			
										<li><input type="checkbox" id="" name="wd_act_al[]" class="con_act" value="fabricate"/> Fabricate<li>
									<?php }?>	
										
									<?php if(strstr($this->session->userdata('products'),'wd:b') && strstr($this->session->userdata('products'),'wd:alu')){ ?>		
										<li><input type="checkbox" id="" name="wd_act_al[]" class="con_act" value="buy-In"/> Buy in<li>
									<?php }?>
										
										<!--  
										<li><input type="checkbox" id="" name="wd_act_al[]" class="con_act" value="installer"/> Install<li>	
										-->				
									</ul>
					
			
				<!-- MARKETS -->
									
									<h3 class="con_mar_list">Markets Served</h3>
									<ul id="markets_served">
									    <li><input type="checkbox" name="con_mar[]" class="con_mar m1" value="trade" /> Trade<li>
										<li><input type="checkbox" name="con_mar[]" class="con_mar m2" value="domestic" /> Domestic<li>
										<li><input type="checkbox" name="con_mar[]" class="con_mar m3" value="commercial" /> Commercial<li>
										<li><input type="checkbox" name="con_mar[]" class="con_mar m4" value="new build" /> New-build<li>
									</ul>
									
									
				<!-- ADDRESS TYPES -->
									<h3 class="con_add_list">Address Type</h3>
									<ul id="address_type">  <!-- NOTE SYSTEM CONTAINS ONLY HeadOffice;Showroom;BranchOffice;Factory; -->
										<li><input type="checkbox" name="con_add[]" class="con_add a1" value="headoffice" /> Head Office<li>
										<li><input type="checkbox" name="con_add[]" class="con_add a2" value="showroom" /> Showroom<li>
										<li><input type="checkbox" name="con_add[]" class="con_add a3" value="factory" /> Factory<li>
										<li><input type="checkbox" name="con_add[]" class="con_add a4" value="trade" /> Trade Counter<li>
										<li><input type="checkbox" name="con_add[]" class="con_add a5" value="depot" /> Depot/Warehouse<li>
										<li><input type="checkbox" name="con_add[]" class="con_add a6" value="branchoffice" /> Branch Office<li>
									</ul>
								</div>
								
									
			<!-- Profile System - ajax -->
								<div class="listbuilder-column">	
													
									<h3 id="con_profile_list_al">Profile System</h3>
									<input type="radio" class="profile_radio_select" id="con_profile_list_al_s" name="con_profile_list_radio_al" checked value="0" />Select
							        <input type="radio" id="con_profile_list_al_d" name="con_profile_list_radio_al" value="1" />De-select
							        <p></p>
									
									<span id="profile_system_ajax_wd_al"></span>
																	
								</div>
								
			<!-- Frames per week -->					
								<div class="listbuilder-column">						
									<h3 id="con_fpw_list">Frames per week</h3>
									<ul>									    
									    <li><input type="checkbox" name="wd_fpw_al[]" class="con_fpw" value="1 to 25" />1 to 25<li>							    									    
									    <li><input type="checkbox" name="wd_fpw_al[]" class="con_fpw" value="26 to 50" />26 to 50<li>
 								        <li><input type="checkbox" name="wd_fpw_al[]" class="con_fpw" value="51 to 100" />51 to 100<li>
									    <li><input type="checkbox" name="wd_fpw_al[]" class="con_fpw" value="101 to 250" />101 to 250<li>		   
									    <li><input type="checkbox" name="wd_fpw_al[]" class="con_fpw" value="251 to 500" />251 to 500<li>
									    <li><input type="checkbox" name="wd_fpw_al[]" class="con_fpw" value="501 to 1000" />501 to 1000<li>
									    
									    <!--  
									    <li><input type="checkbox" name="wd_fpw_al[]" class="con_fpw" value="1001 to 2000" />1001 to 2000<li>
									    <li><input type="checkbox" name="wd_fpw_al[]" class="con_fpw" value="2001 to 3000" />2001 to 3000<li>
									    -->
									    
									    <li><input type="checkbox" name="wd_fpw_al[]" class="con_fpw" value="1001 to 3000" />1001 to 3000<li>
									    <li><input type="checkbox" name="wd_fpw_al[]" class="con_fpw" value="3000+" />3000+<li>
									</ul>								
								</div>
								
								<div class="clear">&nbsp;</div>
								
							</div>
						
						</div> 
						
<!-- END OF <div id="filter-windoors-al" class="ifd-filter"> Aluminium-->
			
			
<!-- START OF <div id="filter-windoors-tim" class="ifd-filter"> Timber -->

             <input type = "hidden" class = "hidden" name = "TIM_choices" id = "TIM_choices" value = "" />		
						
						<div id="filter-windoors-tim" class="ifd-filter">
							<div class="box">
								<h2 id="filter_id_wd_tim" class="boxhead">Filter: Timber</h2>
								<div class="listbuilder-column">
								
								
								
				
				<!-- ACTIVITY -->					
									<h3 class="con_act_list">Activity</h3>
									
									<ul id="activity">	
									    <?php if(strstr($this->session->userdata('products'),'wd:f') && strstr($this->session->userdata('products'),'wd:tim')){ ?>											
											<li><input type="checkbox" id="" name="wd_act_tim[]" class="con_act" value="fabricate"/> Fabricate<li>
										<?php }?>	
										
										<?php if(strstr($this->session->userdata('products'),'wd:b') && strstr($this->session->userdata('products'),'wd:tim')){ ?>			
											<li><input type="checkbox" id="" name="wd_act_tim[]" class="con_act" value="buy-In"/> Buy in<li>
										<?php }?>	
										
										<!--
										<li><input type="checkbox" id="" name="wd_act_tim[]" class="con_act" value="installer"/> Install<li>
										-->					
									</ul>
					
			
				<!-- MARKETS -->
									
									<h3 class="con_mar_list">Markets Served</h3>
									<ul id="markets_served">
									    <li><input type="checkbox" name="con_mar[]" class="con_mar m1" value="trade" /> Trade<li>
										<li><input type="checkbox" name="con_mar[]" class="con_mar m2" value="domestic" /> Domestic<li>
										<li><input type="checkbox" name="con_mar[]" class="con_mar m3" value="commercial" /> Commercial<li>
										<li><input type="checkbox" name="con_mar[]" class="con_mar m4" value="new build" /> New-build<li>
									</ul>
									
									
				<!-- ADDRESS TYPES -->
									<h3 class="con_add_list">Address Type</h3>
									<ul id="address_type">  <!-- NOTE SYSTEM CONTAINS ONLY HeadOffice;Showroom;BranchOffice;Factory; -->
										<li><input type="checkbox" name="con_add[]" class="con_add a1" value="headoffice" /> Head Office<li>
										<li><input type="checkbox" name="con_add[]" class="con_add a2" value="showroom" /> Showroom<li>
										<li><input type="checkbox" name="con_add[]" class="con_add a3" value="factory" /> Factory<li>
										<li><input type="checkbox" name="con_add[]" class="con_add a4" value="trade" /> Trade Counter<li>
										<li><input type="checkbox" name="con_add[]" class="con_add a5" value="depot" /> Depot/Warehouse<li>
										<li><input type="checkbox" name="con_add[]" class="con_add a6" value="branchoffice" /> Branch Office<li>
									</ul>
								</div>
								
								
			<!-- Frames per week -->					
								<div class="listbuilder-column">						
									<h3 id="con_fpw_list">Frames per week</h3>
									<ul>									    
									    <li><input type="checkbox" name="wd_fpw_tim[]" class="con_fpw" value="1 to 25" />1 to 25<li>							    									    
									    <li><input type="checkbox" name="wd_fpw_tim[]" class="con_fpw" value="26 to 50" />26 to 50<li>
 								        <li><input type="checkbox" name="wd_fpw_tim[]" class="con_fpw" value="51 to 100" />51 to 100<li>
									    <li><input type="checkbox" name="wd_fpw_tim[]" class="con_fpw" value="101 to 250" />101 to 250<li>		   
									    <li><input type="checkbox" name="wd_fpw_tim[]" class="con_fpw" value="251 to 500" />251 to 500<li>
									    <li><input type="checkbox" name="wd_fpw_tim[]" class="con_fpw" value="501 to 1000" />501 to 1000<li>
									    
									    <!--  
									    <li><input type="checkbox" name="wd_fpw_tim[]" class="con_fpw" value="1001 to 2000" />1001 to 2000<li>
									    <li><input type="checkbox" name="wd_fpw_tim[]" class="con_fpw" value="2001 to 3000" />2001 to 3000<li>
									    -->
									    
									    <li><input type="checkbox" name="wd_fpw_tim[]" class="con_fpw" value="1001 to 3000" />1001 to 3000<li>
									    <li><input type="checkbox" name="wd_fpw_tim[]" class="con_fpw" value="3000+" />3000+<li>
									</ul>								
								</div>
								
								<div class="clear">&nbsp;</div>
								
							</div>
						
						</div> 
						
<!-- END OF <div id="filter-windoors-tim" class="ifd-filter"> Timber -->

<!-- START OF <div id="filter-windoors-other" class="ifd-filter"> Other materials/hybrids -->

				<input type = "hidden" class = "hidden" name = "OTHER_choices" id = "OTHER_choices" value = "" />	
						
						<div id="filter-windoors-other" class="ifd-filter">
							<div class="box">
								<h2 id="filter_id_wd_other" class="boxhead">Filter: Other materials/hybrids</h2>
								<div class="listbuilder-column">						
								
				
				<!-- ACTIVITY -->					
									<h3 class="con_act_list">Activity</h3>
									
									<ul id="activity">	
									    <?php if(strstr($this->session->userdata('products'),'wd:f') && strstr($this->session->userdata('products'),'wd:oth')){ ?>									
											<li><input type="checkbox" id="" name="wd_act_other[]" class="con_act" value="fabricate"/> Fabricate<li>
										<?php }?>
										
										<?php if(strstr($this->session->userdata('products'),'wd:b') && strstr($this->session->userdata('products'),'wd:oth')){ ?>	
											<li><input type="checkbox" id="" name="wd_act_other[]" class="con_act" value="buy-In"/> Buy in<li>
										<?php }?>									
										<!--
										<li><input type="checkbox" id="" name="wd_act_other[]" class="con_act" value="installer"/> Install<li>	
										-->				
									</ul>
					
			
				<!-- MARKETS -->
									
									<h3 class="con_mar_list">Markets Served</h3>
									<ul id="markets_served">
									    <li><input type="checkbox" name="con_mar[]" class="con_mar m1" value="trade" /> Trade<li>
										<li><input type="checkbox" name="con_mar[]" class="con_mar m2" value="domestic" /> Domestic<li>
										<li><input type="checkbox" name="con_mar[]" class="con_mar m3" value="commercial" /> Commercial<li>
										<li><input type="checkbox" name="con_mar[]" class="con_mar m4" value="new build" /> New-build<li>
									</ul>
									
									
				<!-- ADDRESS TYPES -->
									<h3 class="con_add_list">Address Type</h3>
									<ul id="address_type">  <!-- NOTE SYSTEM CONTAINS ONLY HeadOffice;Showroom;BranchOffice;Factory; -->
										<li><input type="checkbox" name="con_add[]" class="con_add a1" value="headoffice" /> Head Office<li>
										<li><input type="checkbox" name="con_add[]" class="con_add a2" value="showroom" /> Showroom<li>
										<li><input type="checkbox" name="con_add[]" class="con_add a3" value="factory" /> Factory<li>
										<li><input type="checkbox" name="con_add[]" class="con_add a4" value="trade" /> Trade Counter<li>
										<li><input type="checkbox" name="con_add[]" class="con_add a5" value="depot" /> Depot/Warehouse<li>
										<li><input type="checkbox" name="con_add[]" class="con_add a6" value="branchoffice" /> Branch Office<li>
									</ul>
								</div>
								
								
			<!-- Frames per week -->					
								<div class="listbuilder-column">						
									<h3 id="con_fpw_list">Frames per week</h3>
									<ul>									    
									    <li><input type="checkbox" name="wd_fpw_other[]" class="con_fpw" value="1 to 25" />1 to 25<li>							    									    
									    <li><input type="checkbox" name="wd_fpw_other[]" class="con_fpw" value="26 to 50" />26 to 50<li>
 								        <li><input type="checkbox" name="wd_fpw_other[]" class="con_fpw" value="51 to 100" />51 to 100<li>
									    <li><input type="checkbox" name="wd_fpw_other[]" class="con_fpw" value="101 to 250" />101 to 250<li>		   
									    <li><input type="checkbox" name="wd_fpw_other[]" class="con_fpw" value="251 to 500" />251 to 500<li>
									    <li><input type="checkbox" name="wd_fpw_other[]" class="con_fpw" value="501 to 1000" />501 to 1000<li>
									    
									    <!--  
									    <li><input type="checkbox" name="wd_fpw_other[]" class="con_fpw" value="1001 to 2000" />1001 to 2000<li>
									    <li><input type="checkbox" name="wd_fpw_other[]" class="con_fpw" value="2001 to 3000" />2001 to 3000<li>
									    -->
									    
									    <li><input type="checkbox" name="wd_fpw_other[]" class="con_fpw" value="1001 to 3000" />1001 to 3000<li>
									    <li><input type="checkbox" name="wd_fpw_other[]" class="con_fpw" value="3000+" />3000+<li>
									</ul>								
								</div>
								
								<div class="clear">&nbsp;</div>
								
							</div>
						
						</div> 
						
<!-- END OF <div id="filter-windoors-other" class="ifd-filter"> Other materials/hybrids -->

<!-- START OF <div id="filter-none" class="ifd-filter"> Select a filter -->
					
					<div id="filter-none">
						<div class="box">
							<h2 id="filter_id_wd" class="boxhead">Build a List</h2>
			
							<p>Click a product in the sidebar on the left to begin creating your list.</p>
							
						</div>
					</div>

<!-- END OF <div id="filter-none" class="ifd-filter"> Select a filter -->
						<!-- EXTRA MESSAGES FOR WINDOORS AND CONSERVATORY ROOFS FILTERS  -->
						
						<!--  
				        <div id="cr_fab_message" class="fab_buyin_messages">
						<h3 style="color:#ff0000">Important</h3>
						<p>You have selected "Fabricate" from the above criteria. Some companies who fabricate also buy-in. Please tick the following option if you only want fabricators who do not buy-in:</p>
						<p><input type="checkbox" class="excludes" name="exclude_buyin" id="exclude_buyin" value="exclude_buyin" /> Exclude companies who buy-in</p>
						</div>
						-->
						
					
					</div> <!-- END OF  listbuilder-right -->
					
<!-- SECTORS START -->		
              <div id="sectors_all" class="sectors_all">

              
<!-- START OF LOCAL BUILDERS TEMPLATE-->	

					<div id="filter-sbd" class="ifd-filter_s">
					
					        <div class="box">
								<h2 class="boxhead">Filter: Local Builders</h2>
								
								<div class="listbuilder-column">
								
									<h3 id="sectors_sbd">Sectors</h3>
									<ul id="activity_sbd">
																    
										<li><input id="" class="sbd" name="sbd[]" type="checkbox" value="1" />Home Improvements<li>
										<li><input id="" class="sbd" name="sbd[]" type="checkbox" value="2" />Extensions<li>
										<li><input id="" class="sbd" name="sbd[]" type="checkbox" value="3" />Roofing<li>
										<li><input id="" class="sbd" name="sbd[]" type="checkbox" value="4" />Driveways<li>
										<li><input id="" class="sbd" name="sbd[]" type="checkbox" value="5" />Renovation<li>
										<li><input id="" class="sbd" name="sbd[]" type="checkbox" value="6" />New Build<li>
										<li><input id="" class="sbd" name="sbd[]" type="checkbox" value="7" />Specialist Services<li>
										<li><input id="" class="sbd" name="sbd[]" type="checkbox" value="8" />Solar Panels<li>
										
									</ul>
									
								</div>
								
								<!--<div class="listbuilder-column">
								<h3  class="con_mar_list">Markets Served</h3>
								<ul id="markets_served">
								
									<li><input type="checkbox" name="con_mar[]" class="con_mar m1" value="trade" /> Trade<li>
									<li><input type="checkbox" name="con_mar[]" class="con_mar m2" value="domestic" /> Domestic<li>
									<li><input type="checkbox" name="con_mar[]" class="con_mar m3" value="commercial" /> Commercial<li>
									<li><input type="checkbox" name="con_mar[]" class="con_mar m4" value="new build" /> New-build<li>
									
								</ul>
							   </div>-->
								
								<div class="clear">&nbsp;</div>
								
							</div>
					
					</div>


<!-- END OF LOCAL BUILDERS TEMPLATE-->	

<!-- START OF RENEWABLE ENERGY TEMPLATE-->	

					<div id="filter-nrg" class="ifd-filter_s">
					
					        <div class="box">
								<h2 class="boxhead">Filter: Renewable Energy</h2>
								
								<div class="listbuilder-column">
								
									<h3 id="sectors_nrg">Sectors</h3>
									<ul id="activity_nrg">
																    
										<li><input id="" class="nrg" name="nrg[]" type="checkbox" value="1" />Solar Thermal<li>
										<li><input id="" class="nrg" name="nrg[]" type="checkbox" value="2" />Solar PV<li>
										<li><input id="" class="nrg" name="nrg[]" type="checkbox" value="3" />Wind Turbines<li>
										<li><input id="" class="nrg" name="nrg[]" type="checkbox" value="4" />Air Source Heat Pumps<li>
										<li><input id="" class="nrg" name="nrg[]" type="checkbox" value="5" />Ground Source Heat Pumps<li>
										<li><input id="" class="nrg" name="nrg[]" type="checkbox" value="6" />Hydro Power<li>
										<li><input id="" class="nrg" name="nrg[]" type="checkbox" value="7" />Combined Heat and Power<li>
										<li><input id="" class="nrg" name="nrg[]" type="checkbox" value="8" />Biomass<li>
										<li><input id="" class="nrg" name="nrg[]" type="checkbox" value="9" />Anaerobic Digestion<li>
										<li><input id="" class="nrg" name="nrg[]" type="checkbox" value="10" />Heat Recovery Units<li>
											
									</ul>
									
								</div>
								
								<!--<div class="listbuilder-column">
								<h3  class="con_mar_list">Markets Served</h3>
								<ul id="markets_served">
								
									<li><input type="checkbox" name="con_mar[]" class="con_mar m1" value="trade" /> Trade<li>
									<li><input type="checkbox" name="con_mar[]" class="con_mar m2" value="domestic" /> Domestic<li>
									<li><input type="checkbox" name="con_mar[]" class="con_mar m3" value="commercial" /> Commercial<li>
									<li><input type="checkbox" name="con_mar[]" class="con_mar m4" value="new build" /> New-build<li>
									
								</ul>
							   </div>-->
								
								<div class="clear">&nbsp;</div>
								
							</div>
					
					</div>


<!-- END OF RENEWABLE ENERGY TEMPLATE-->	

<!-- START OF ASD TEMPLATE-->	

					<div id="filter-asd" class="ifd-filter_s">
					
					        <div class="box">
								
							<div id="asd-filter">
							<h2 class="boxhead">Filter: Architects &amp; Specifiers</h2>
							
							<div class="listbuilder-column">
								<h3>Public Sector</h3>
								<ul id="asd_activity">
									<li><input id="asd_gov_loc_aut" class="asd" name="asd[]" type="checkbox" value="11" /> Government / Local Authority</li>
									<li><input id="asd_def" class="asd" name="asd[]" type="checkbox" value="12" /> Defence</li>
									<li><input id="asd_civ_com" class="asd" name="asd[]" type="checkbox" value="13" /> Civic / Community</li>
								</ul>
							</div>
						
							<div class="listbuilder-column">
								<h3>Commercial</h3>
								<ul id="asd_activity">
									<li><input id="asd_off" class="asd" name="asd[]" type="checkbox" value="21" /> Offices</li>
									<li><input id="asd_ind_uni_bui" class="asd" name="asd[]" type="checkbox" value="22" /> Industrial Units / Buildings</li>
								</ul>
							</div>
							
							<div class="listbuilder-column">
								<h3>Education</h3>
								<ul id="asd_activity">
									<li><input id="asd_sch_and_col" class="asd" name="asd[]" type="checkbox" value="31" /> Schools &amp; Colleges</li>
									<li><input id="asd_stu_acc" class="asd" name="asd[]" type="checkbox" value="32" /> Student Accomodation</li>
									<li><input id="asd_uni" class="asd" name="asd[]" type="checkbox" value="33" /> Universities</li>
								</ul>
							</div>
							
							<div class="listbuilder-column">
								<h3>Retail</h3>
								<ul id="asd_activity">
									<li><input id="asd_sho_sho" class="asd" name="asd[]" type="checkbox" value="41" /> Shops / Shopfronts</li>
									<li><input id="asd_sup_sup" class="asd" name="asd[]" type="checkbox" value="42" /> Supermarkets / Superstores</li>
									<li><input id="asd_sho_cen_ret_par" class="asd" name="asd[]" type="checkbox" value="43" /> Shopping Centres / Retail Parks</li>
								</ul>
							</div>
							
							<div class="listbuilder-column">
								<h3>Leisure</h3>
								<ul id="asd_activity">
									<li><input id="asd_hot" class="asd" name="asd[]" type="checkbox" value="51" /> Hotels</li>
									<li><input id="asd_con_cen" class="asd" name="asd[]" type="checkbox" value="52" /> Conference Centres</li>
									<li><input id="asd_res_bar" class="asd" name="asd[]" type="checkbox" value="53" /> Restaurants / Bars</li>
									<li><input id="asd_cul_ent" class="asd" name="asd[]" type="checkbox" value="54" /> Culture / Entertainment</li>
								</ul>
							</div>
							
							<div class="listbuilder-column">
								<h3>Health</h3>
								<ul id="asd_activity">
									<li><input id="asd_hos" class="asd" name="asd[]" type="checkbox" value="61" /> Hospitals</li>
									<li><input id="asd_car_hom_hos" class="asd" name="asd[]" type="checkbox" value="62" /> Care Homes / Hospices</li>
									<li><input id="asd_hea_car_sur" class="asd" name="asd[]" type="checkbox" value="63" /> Health Care / Surgeries</li>
								</ul>
							</div>
							
							<div class="listbuilder-column">
								<h3>Housing</h3>
								<ul id="asd_activity">
									<li><input id="asd_pri_hou" class="asd" name="asd[]" type="checkbox" value="71" /> Private Housing</li>
									<li><input id="asd_sec_hou" class="asd" name="asd[]" type="checkbox" value="72" /> Social Housing</li>
									<li><input id="asd_com_hou" class="asd" name="asd[]" type="checkbox" value="73" /> Commercial Housing</li>
								</ul>
							</div>
							
							<div class="listbuilder-column">
								<h3>Category</h3>
								<select name="asd_category" id="asd_category">
									<option id="asd_cat_a" value="a">International or UK</option>
									<option id="asd_cat_b" value="b">UK with multiple locations</option>
									<option id="asd_cat_c" value="c">Medium  UK practice</option>
									<option id="asd_cat_d" value="d">Small UK practice</option>
									<option id="asd_cat_e" value="e">Sole architect</option>
								</select>
							</div>
							
							<div class="clear">&nbsp;</div>
						</div>
								
						</div>
					
					</div>


<!-- END OF ASD TEMPLATE-->	

<!-- START OF MCD TEMPLATE-->	

					<div id="filter-mcd" class="ifd-filter_s">
					
					        <div class="box">
								
							<div id="mcd-filter">
							<h2 class="boxhead">Filter: Construction File</h2>
							
							<div class="listbuilder-column">
								<h3>Public Sector</h3>
								<ul id="mcd_activity">
									<li><input id="mcd_gov_loc_aut" class="mcd" name="mcd[]" type="checkbox" value="11" /> Government / Local Authority</li>
									<li><input id="mcd_def" class="mcd" name="mcd[]" type="checkbox" value="12" /> Defence</li>
									<li><input id="mcd_civ_com" class="mcd" name="mcd[]" type="checkbox" value="13" /> Civic / Community</li>
								</ul>
							</div>
							
							<div class="listbuilder-column">
								<h3>Commercial</h3>
								<ul id="mcd_activity">
									<li><input id="mcd_off" class="mcd" name="mcd[]" type="checkbox" value="21" /> Offices</li>
									<li><input id="mcd_ind_uni_bui" class="mcd" name="mcd[]" type="checkbox" value="22" /> Industrial Units / Buildings</li>
								</ul>
							</div>
							
							<div class="listbuilder-column">
								<h3>Education</h3>
								<ul id="mcd_activity">
									<li><input id="mcd_sch_and_col" class="mcd" name="mcd[]" type="checkbox" value="31" /> Schools &amp; Colleges</li>
									<li><input id="mcd_stu_acc" class="mcd" name="mcd[]" type="checkbox" value="32" /> Student Accomodation</li>
									<li><input id="mcd_uni" class="mcd" name="mcd[]" type="checkbox" value="33" /> Universities</li>
								</ul>
							</div>
							
							<div class="listbuilder-column">
								<h3>Health</h3>
								<ul id="mcd_activity">
									<li><input id="mcd_hos" class="mcd" name="mcd[]" type="checkbox" value="61" /> Hospitals</li>
									<li><input id="mcd_car_hom_hos" class="mcd" name="mcd[]" type="checkbox" value="62" /> Care Homes / Hospices</li>
									<li><input id="mcd_hea_car_sur" class="mcd" name="mcd[]" type="checkbox" value="63" /> Health Care / Surgeries</li>
								</ul>
							</div>
							
							<div class="listbuilder-column">
								<h3>Retail</h3>
								<ul id="mcd_activity">
									<li><input id="mcd_sho_sho" class="mcd" name="mcd[]" type="checkbox" value="41" /> Shops / Shopfronts</li>
									<li><input id="mcd_sup_sup" class="mcd" name="mcd[]" type="checkbox" value="42" /> Supermarkets / Superstores</li>
									<li><input id="mcd_sho_cen_ret_par" class="mcd" name="mcd[]" type="checkbox" value="43" /> Shopping Centres / Retail Parks</li>
								</ul>
							</div>
							
							<div class="listbuilder-column">
								<h3>Housing</h3>
								<ul id="mcd_activity">
									<li><input id="mcd_pri_hou" class="mcd" name="mcd[]" type="checkbox" value="71" /> Private Housing</li>
									<li><input id="mcd_sec_hou" class="mcd" name="mcd[]" type="checkbox" value="72" /> Social Housing</li>
									<li><input id="mcd_com_hou" class="mcd" name="mcd[]" type="checkbox" value="73" /> Commercial Housing</li>
								</ul>
							</div>
							
							<div class="listbuilder-column">
								<h3>Leisure</h3>
								<ul id="mcd_activity">
									<li><input id="mcd_hot" class="mcd" name="mcd[]" type="checkbox" value="51" /> Hotels</li>
									<li><input id="mcd_con_cen" class="mcd" name="mcd[]" type="checkbox" value="52" /> Conference Centres</li>
									<li><input id="mcd_res_bar" class="mcd" name="mcd[]" type="checkbox" value="53" /> Restaurants / Bars</li>
									<li><input id="mcd_cul_ent" class="mcd" name="mcd[]" type="checkbox" value="54" /> Culture / Entertainment</li>
								</ul>
							</div>
							
							<div class="listbuilder-column">
								<h3>Civil</h3>
								<ul id="mcd_activity">
									<li><input id="mcd_gro_wor_civ_pro" class="mcd" name="mcd[]" type="checkbox" value="81" /> Ground Works / Civil Projects</li>
									<li><input id="mcd_uti_inf" class="mcd" name="mcd[]" type="checkbox" value="82" /> Utilities / Infrastructure</li>
								</ul>
							</div>
							
							<div class="clear">&nbsp;</div>
							<div class="listbuilder-column">
								<h3>Restoration / Refurbishment</h3>
								<ul id="mcd_activity">
									<li><input id="mcd_res_ref" class="mcd" name="mcd[]" type="checkbox" value="91" /> Restoration / Refurbishment</li>
								</ul>
							</div>
												
							<!--<div class="listbuilder-column">
								<h3  class="con_mar_list">Markets Served</h3>
								<ul id="markets_served">
								
									<li><input type="checkbox" name="con_mar[]" class="con_mar m1" value="trade" /> Trade<li>
									<li><input type="checkbox" name="con_mar[]" class="con_mar m2" value="domestic" /> Domestic<li>
									<li><input type="checkbox" name="con_mar[]" class="con_mar m3" value="commercial" /> Commercial<li>
									<li><input type="checkbox" name="con_mar[]" class="con_mar m4" value="new build" /> New-build<li>
									
								</ul>
							</div>-->
							
							<div class="clear">&nbsp;</div>
						</div>
								
						</div>
					
					</div>


<!-- END OF MCD TEMPLATE-->	

            </div>
<!-- SECTORS END -->	
					
			</div>				
			<!-- End listbuilder-step1 -->	
					
					<!-- PAGE 2 STARTS -->
					
					<div class="clear" style="clear:both">&nbsp;</div>
					
					<div id="listbuilder-page2">
					
						<div class="clear">&nbsp;</div>
						
						<div class="box" id="sort-filter">
							<h2 class="boxhead">Select sort criterion</h2>
							<p>Sort records by:</p>
							<p><input type="radio" id="format-sort1" name="sortc" value="companyname" checked />Company Name</p>
							
							<!-- 
							<p><input type="radio" id="format-sort2" name="sortc" value="companyid" />Company ID</p>
							<p><input type="radio" id="format-sort4" name="sortc" value="telephoneno" />Telephone number</p>
							 -->
							
							<p><input type="radio" id="format-sort3" name="sortc" value="postcode" />Postcode</p>
						</div>
						
						<!-- display only if region subscription -->
						<div class="box" id="distance-filter">
							<h2 class="boxhead">Filter by distance</h2>
							<p>Distance:</p>
							<select name="distance" id="pc_distance">
								<option value="0">Select...</option>
								<option value="1">1 mile</option>
								<option value="5">5 miles</option>
								<option value="10">10 miles</option>
								<option value="25">25 miles</option>
								<option value="50">50 miles</option>
								<option value="100">100 miles</option>
							</select>
							
							<p>From postcode:</p>
							<input type="text" maxlength="10" id="postcode_box" name="postcode" /><span style="color: #ff0000" id="pc_check"></span>
							<!--  
							<input type="submit" value="GO" />
							-->
						</div>
						
						<div class="box" id="postcode-filter">
							<h2 class="boxhead">Or filter by region/postcode</h2>
							<input type="checkbox" id="region-all" class="" name="all" />
							<p>Select all regions in your subscription</p>
							<div id="region_ajax" class="region">
							
							<!-- 
								AJAX RESULTS DUMPED HERE .....	
						    -->		
								
							</div>
						</div>
						<!--  
						<div id="listbuilder-footer-buttons">
							<input id="btn_submit2" class="help-button" type="button" value="Help" onClick="javascript:alert('work in progress ...')" /> 
							<input  id="previous-button" class="previous-button" type="button" value="Previous" /> 
							<input id="next-button2_bottom" class="next-button" type="button" value="Next" />
						</div>
						<div class="clear">&nbsp;</div>
						-->
					
					 </div>  <!-- END OF listbuilder-page2 -->
					 
					 <!-- PAGE 2 ENDS -->
					 
					 <!-- PAGE 3 STARTS -->
					
						<!-- CONTAINS LIST BUILDER PAGE 3 CODE THAT IS ALSO USED IN SAVED LISTS-->
						<?php $this->load->view($include);?>
					 
			<!-- view list -->	
			
			<script type="text/javascript">
				
					//Handles button clicks
					$(document).ready(function(){

						// simulates a click on the saved list button
						$('#listname').keypress(function(event){
							 if(event.keyCode == 13){
							$('#listname_button').click();
							 }
						});
					
						$('#exclude-button').click(function() {
							$.fancybox.open([{
								href : '#popup-exclude'
							}]);
						});
						
						$('#display-button').click(function() {
							$.fancybox.open([{
								href : '#popup-fields'
							}]);
						});
						
						$('#csv-btn').click(function() {
							$('#csv_submit').attr('value','csv_export'); 
							$('#popup-export h2').text('Export list to a standard CSV file');
							$('p#csv_export_message').text('');	
							$('#csv_submit').show();						     
							$.fancybox.open([{
								href : '#popup-export'
							}]);  		   
						});
						
						$('#excel-btn').click(function() {
							$('#csv_submit').attr('value','xls_export'); 
							$('#popup-export h2').text('Export list to a standard XLS file');
							$('p#csv_export_message').text('');	 
							$('#csv_submit').show();      		   	
							$.fancybox.open([{
								href : '#popup-export'
							}]);  		   
						});
						
						$('#save-button2').click(function() {
							$.fancybox.open([{
								href : '#popup-listname'
							}]);
						});
						
						$('#globalnote-button').click(function() {
							$.fancybox.open([{
								href : '#popup-globalnote'
							}]);
						});
						
					});
				</script>
				
				<input type = "hidden" class = "hidden" name = "view_title" id = "view_title" value = "" /> 
				<div id="records-wrap" class="page3-floating-divs">
					<div id="records-buttons">
						<input title="Go back to the previous page" id="previous-button" class="previous-button" type="button" value="Previous" />
						<?php if(!$this->session->userdata('demouser')){ // demo user?>	
						   <input title="Alter the display of this list" id="display-button" class="display-button" type="button" value="Display" /> 
						<?php }?>
						<input title="Exclude companies by relationship" class="exclude-button" id="exclude-button" type="button" value="Exclude" />
						<input title="Create a global note on all records in this list" class="globalnote-button" id="globalnote-button" type="button" value="Add a global note" />
					    
					    <?php if($this->session->userdata('export_flag')){?>
					       <input title="Export this list to CSV" id="csv-btn" class="csv-button" type="button" value="CSV" />
					       <input title="Export this list to MS Excel" id="excel-btn" class="excel-button" type="button" value="Excel" />
					    <?php }?>
					    
						<input title="Save this list" id="save-button2" class="save-button" type="button" value="Save" /> 
						<!--
						<input id="action-button"class="action-button" type="button" value="Back" />
						-->
					</div>
					<h2>My List (not yet saved)</h2>					
					<h3 style="color:#ff0000;">Please save the list to view the records</h3>
					<div class="clear">&nbsp;</div>
					<div id="records-box" >
						<table id="mytable">
							<thead>
								<tr class="table-head">
								    <th style="width:10px;">Tag</th>
									<th>Company</th>
									<th>Town / City</th>
									<th>Postcode</th>
									<th>Contact</th>
									<th>Position</th>
									<th>Telephone</th>
								</tr>
							</thead>
							<tbody id="records-wrap-results">
							
							<!--   
							<tr class='listrow'><td colspan="7"><img class="ajax-anim"  id="ajax-anim_33" src="/media/images/ajax-loader.gif"></td></tr>    
							 
								RECORDS GO HERE (ajax)
							-->
							
							</tbody>
						</table>
						<div class="clear">&nbsp;</div>
					</div>
				</div>
							
				<!-- END view list -->				
					 
					 <!-- PAGE 3 ENDS -->
					
					</form>		
				
				<!-- CONTAINS LIST BUILDER PAGE 3 FUNCTIONS THAT IS ALSO USED IN SAVED LISTS-->
				<?php $this->load->view($include_func);?>		
			 		
			 </div><!-- End Content -->				
			
