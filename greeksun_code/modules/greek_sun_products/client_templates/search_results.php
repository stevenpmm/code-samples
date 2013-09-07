
<!-- 

THIS TEMPLATE IS CALLED INSIDE from profiles/2248_Greek_Sun/templates/search_results - line 49

 -->

<div id="left_hopping" class="main">
	<div id="island_hopping_top">
		<div id="refine_title">
			<h3>Refine Results</h3>
			<p>Click on the options to narrow your results</p>
		</div>
		<div id="refiner">
		
		<!--
			<form action="form">
	    --> 
			
			<form id="resort_form" name="resort_form" method="post" action="/resort-view" onsubmit="" >
			
			    <input type = "hidden" name = "submit_type" value = "resorts" />
			
				<select name="stay">
					<option value="0" <?=($_POST['stay'] == '0' ? 'selected="selected"' : '')?>>Length of Stay</option>
					<option value="7_days" <?=($_POST['stay'] == '7_days' ? 'selected="selected"' : '')?>>7 days</option>
					<option value="14_days" <?=($_POST['stay'] == '14_days' ? 'selected="selected"' : '')?>>14 days</option>                                     
				</select>
				
				
				<?php 
				        // get resort name
				      	$cmd="SELECT `name` FROM `grk_islands_resort` WHERE `id` = ".$_POST['resort_id']; 
				      	$values = db::select($cmd);		      	
				      	$id = $_POST['resort_id'];
				      	$value = $values[0]['name'];		
          		?>
				<select  name="resort_id" id="g_resort">
				    <option value="0">Select a Resort</option> 
				    <?php if(isset($_POST['resort_id']) && $_POST['resort_id']>0){?>			   
					<option value="<?=$id?>" selected="selected"><?=$value?></option>
					<?php }?>   
				</select>
			   
			   
				<select name="year">
					<option value="0">Select Time of Year</option>
					<option value="0">Jul & Aug</option> 
					<option value="0">Jun & Sep</option> 
					<option value="0">Rest of Year</option>                                     
				</select>
				
				<select  name="island_id" id="g_island">
				    <option value="0">Select an Island</option>
					<?php foreach($results['islands'] as $island){?>
                       <option value="<?=$island['island_id']?>" <?=($_POST['island_id'] == $island['island_id'] ? 'selected="selected"' : '')?> ><?=$island['name']?></option> 
					<?php } ?>                                 
				</select>
				
				 <select name="board">
					<option value="0" <?=($_POST['board'] == '0' ? 'selected="selected"' : '')?>>Board Type</option>
					<option value="BB" <?=($_POST['board'] == 'BB' ? 'selected="selected"' : '')?>>Bed & Breakfast</option>
					<option value="SC" <?=($_POST['board'] == 'SC' ? 'selected="selected"' : '')?>>Self Catering</option>   
					<option value="RO" <?=($_POST['board'] == 'RO' ? 'selected="selected"' : '')?>>Room Only</option>                                   
				</select>
					
				<div id="party">
                <select name="party">
                    <option value="0" <?=($_POST['party'] == '0' ? 'selected="selected"' : '')?>>Party Size</option>
                    <option value="2" <?=($_POST['party'] == '2' ? 'selected="selected"' : '')?>>2</option>
                    <option value="3" <?=($_POST['party'] == '3' ? 'selected="selected"' : '')?>>3</option>
                    <option value="4" <?=($_POST['party'] == '4' ? 'selected="selected"' : '')?>>4</option>
                    <option value="5" <?=($_POST['party'] == '5' ? 'selected="selected"' : '')?>>5</option>    
                    <option value="6" <?=($_POST['party'] == '6' ? 'selected="selected"' : '')?>>6</option>                                   
                </select>
                </div> 
				
				<div id="check-boxes">									
                <input type="checkbox" name="beach" value="Beach" <?=(isset($_POST['beach']) ? 'checked="checked"' : '')?> /> Beach
                <input type="checkbox" name="pool" value="Pool" <?=(isset($_POST['pool']) ? 'checked="checked"' : '')?> /> Pool 
                </div>                                          
														 
				<div id="refine_button">
				
				<!--  
				<a href="#">Refine</a>  
				-->
				
				<input name="btnSubmit" type="submit" id="btnSubmit" value="Refine" />
				                            	
				</div>
			</form>
		</div>
		<h2>Your Search Results</h2>
		<div class="pagination">
			<span class="page_1 first_pag curr_pagination_page">1</span> | 
			<a href="#" class="page_2">2</a> | 
			<a href="#" class="page_3">3</a> | 
			<a href="#" class="page_4">4</a> | 
			<a href="#" class="page_5">5</a> | 
			<span>...</span> | 
			<a href="#" class="last_pag">36</a> | 
			<a href="#" class="next_pag">Next »</a>
		</div>
	</div>
	<div id="content3">
		
		<ul>
		<?	
		   if(!$results['data']){
             echo '<h4>NO DATA AVAILABLE</h4>'; 
		   }
           else{  
		   // loop goes here - found in template: search_result_item.php	
			$path = $this->client_template_path('search_result_item.php');
			ob_start();
			include($path);
			$html = ob_get_contents();
			ob_end_clean();
			echo $html;
           }
		?>
		</ul>
																		  
	</div>
	<? if($results['data']){ ?>
	<div class="pagination">
		<span class="page_1 first_pag curr_pagination_page">1</span> | 
		<a href="#" class="page_2">2</a> | 
		<a href="#" class="page_3">3</a> | 
		<a href="#" class="page_4">4</a> | 
		<a href="#" class="page_5">5</a> | 
		<span>...</span> | 
		<a href="#" class="last_pag">36</a> | 
		<a href="#" class="next_pag">Next »</a>
	</div>
	<?}?>
	
