
<!-- 

THE main TEMPLATE for http://www.greeksun.co.uk/resort-view/ 

 -->


<!-- TEMPLATE_NAME:homepage -->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
        <link href="http://yui.yahooapis.com/2.9.0/build/reset/reset-min.css" rel="stylesheet" type="text/css" />
        <link rel="stylesheet" type="text/css" href="/css/site.css" />
        <!--[if IE 7]> <link rel="stylesheet" type="text/css" href="/css/browsers/ie7.css" /> <![endif]-->
        <!--[if IE 8]> <link rel="stylesheet" type="text/css" href="/css/browsers/ie8.css" /> <![endif]-->
        <!--[if IE 9]> <link rel="stylesheet" type="text/css" href="/css/browsers/ie9.css" /> <![endif]-->
        <title>Greek Sun - Search Results</title>
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js" type="text/javascript"></script>	
				<script src="/scripts/common.js"></script>
		<script src="/scripts/api.js"></script>
		
        <script type="text/javascript">
			$(function(){
				$('.input_text').focus(function(){
					$(this).addClass('white_bg'); 
				})
				
				$('.input_text').blur(function(){
					if($(this).val() == ""){
						$(this).removeClass('white_bg');
					}
				})
			})
		</script>
    </head>
    <body>
        <div id="site_wrapper" class="clearfix">			
            <div id="main_content" class="clearfix">
                <div id="top_content" class="clearfix">
                    <div id="left_column">                        
                        <?php include("inc/left_column.php") ?> 
                        <?php include("inc/protection.php"); ?>                       
                    </div>
                    <div id="right_column">                    
                        <?php include("inc/header.php"); ?>                        
                        <div id="page_content">
                        
                        // another template included here ... 
							<?=template::read('body');?>
                        </div>                            
                    </div>
                </div>
            </div>
            <?php include("inc/footer.php"); ?>
        </div>
        </div>
    </body>
</html>
