<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<!DOCTYPE html>
<html lang="en">
<head>
  
  <meta charset="utf-8">
  <meta name="description" content="Miminium Admin Template v.1">
  <meta name="author" content="Isna Nur Azis">
  <meta name="keyword" content="">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>CLEAR System | Clayton Euro Risk | Dashboard</title>

  <!-- start: Css -->
  <link rel="stylesheet" type="text/css" href="/public/asset/css/bootstrap.min.css">
  
   <link href="/public/bootstrap-colorpicker-master/bootstrap-colorpicker-master/dist/css/bootstrap-colorpicker.css" rel="stylesheet">  
 
  <link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/base/jquery-ui.css">

  <!-- plugins -->
  <link rel="stylesheet" type="text/css" href="/public/asset/css/plugins/font-awesome.min.css"/>
  <link rel="stylesheet" type="text/css" href="/public/asset/css/plugins/animate.min.css"/>
  <link rel="stylesheet" type="text/css" href="/public/asset/css/plugins/simple-line-icons.css"/>
  <link rel="stylesheet" type="text/css" href="/public/asset/css/plugins/jquery.steps.css"/>
  <link rel="stylesheet" type="text/css" href="/public/asset/css/plugins/jquery.gridster.min.css"/>
  <link rel="stylesheet" type="text/css" href="/public/asset/css/plugins/bootstrap2-toggle.min.css"/>
  <link href="/public/asset/css/style.css" rel="stylesheet">
  
   <link href="/public/asset/css/designer.css" rel="stylesheet">
   
   <link href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css" rel="stylesheet">
  
  <!-- end: Css -->

  <link rel="shortcut icon" href="/public/asset/img/logomi.png">
  <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
      <![endif]-->
    
</head>

<body id="mimin" class="dashboard">

      <!-- start: Header -->
        <nav class="navbar navbar-default header navbar-fixed-top">
          <div class="col-md-12 nav-wrapper">
            <div class="navbar-header" style="width:100%;">
              <div class="opener-left-menu is-open">
                <span class="top"></span>
                <span class="middle"></span>
                <span class="bottom"></span>
              </div>
                 <a href="#"><img src="/public/asset/img/cer-transparent-circle-48x48.png" class="cer-logo"/><span class="navbar-brand"><strong><em>CLEAR</em></strong></span></a>
              <ul class="nav navbar-nav navbar-right user-nav">
                <li class="user-name"><span>Steven Matthews</span></li>
                  <li class="dropdown avatar-dropdown">
                   <img src="/public/asset/img/your-avatar.jpg" class="img-circle avatar" alt="Your company" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true"/>
                   <ul class="dropdown-menu user-dropdown">
				   						<li class="projectButton"><button type="button" class="btn btn-info" onclick="switchProject(4);">Arjen Servicing Nov 16<button></li>
											<hr />
                    <li><a href="#" data-toggle="modal" data-target="#changePassword"><span class="fa fa-lock"></span> Change Password</a></li>
                    <li><a href="/public/logout.php"><span class="fa-2x fa fa-power-off"></span> Log out</a></li>
                  </ul>
                </li>
              </ul>
            </div>
          </div>
        </nav>
      <!-- end: Header -->

       <div class="container-fluid mimin-wrapper">
  
          <!-- start:Left Menu -->
    <div id="left-menu">
        <div class="sub-left-menu scroll">
            <ul class="nav nav-list">
                <li><div class="left-bg"></div></li>
                <li class="time">
                    <h1 class="animated fadeInLeft"></h1>
                    <p class="animated fadeInRight"></p>
                </li>
                
                <li class="ripple">
		    <a href="<?php base_url()?>/Clear/"><span class="fa-home fa"></span>Dashboard</a>
	       </li>
                
                <li class="ripple">
		    <a href="<?php base_url()?>/Clear/designer"><span class="fa-home fa"></span> Designer</a>
	       </li>
               
                <li class="ripple">
		    <a href="<?php base_url()?>/Keypunch"><span class="fa-home fa"></span> Keypunch</a>
	       </li>
               
                <li class="ripple">
		    <a href="<?php base_url()?>/Exceptions"><span class="fa-home fa"></span> Exceptions</a>
	       </li>
               
                <li class="ripple">
		    <a href="<?php base_url()?>/Grade"><span class="fa-home fa"></span>Grades</a>
	       </li>
               
		<li class="ripple">
                    <a class="tree-toggle nav-header">
                        <span class="fa fa-cloud-download"></span> Export
                        <span class="fa-angle-right fa right-arrow text-right"></span>
                    </a>
					<ul class="nav nav-list tree">
                        <li><a href="/public/export.php?data=all">All</a></li>
						<hr />
                        <li><a href="/public/export.php?data=pro">Project Data</a></li>
						<li><a href="/public/export.php?data=ref">Referral list</a></li>
						<li><a href="/public/export.php?data=int">Data Integrity</a></li>
                    </ul>
                </li>
				<li class="ripple">
                    <a href="/public/assetList.php"><span class="fa fa-database"></span> Loan level data</a>
                </li>
								<li class="ripple">
                    <a href="/public/admin.php"><span class="fa fa-cogs"></span> Administration</a>
                </li>
				             </ul>
        </div>
    </div>
<!-- end: Left Menu -->

