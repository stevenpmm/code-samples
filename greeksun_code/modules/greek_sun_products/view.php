<?
	class module_greek_sun_products_core_view extends view{
		
		function __construct($parent){
			$this->parent = $parent;
		}
		
		// other functions go here ...
		
		
		// called by controller (ref: get accomodation listing)
		function render_search_results($results) {   // accommodation

			// from modules/greek_sun_products/client_templates/ folder
			$path = $this->client_template_path('search_results.php');

			ob_start();
			include($path);
			$html = ob_get_contents();
			ob_end_clean();

			$html .= "
			<script>
			
		// THIS AJAX FUNCTION CALLS THE MODEL METHOD get_search_results_names()
		// AND POPULATES THE RESORT DROP DOWN (REF: #g_resort) in search_results.php above - line 31  
			
			$(function(){
				   $('#g_island').change(function(){
					api('greek_sun_products', '_model', 'get_search_results_names',  $('#g_island').val(), function(result){
			
					    $('#g_resort').find('option').each(function() {
						     $(this).remove();
						 });
					
					    $('#g_resort').append('<option value=0>Select a Resort</option>'); 
					    if($('#g_island').val() != 0){
						    for(k=0; k<result.length; k++){
							  $('#g_resort').append('<option value=' + result[k].id + '>' + result[k].name + '</option>');
							}
						}
					});
				   });
			});
			
			</script>";
						    		      
			return $html;
			
		} // end of function
	} // end of class
	
?>
