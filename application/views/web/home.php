<!DOCTYPE html>
<html>
<head>
	<title>Workflow Hunt</title>

	<!-- Custom Font -->
	<link href="https://fonts.googleapis.com/css?family=Lato" rel="stylesheet"> 

	<!-- Font Awesome -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css">

	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.4/css/bootstrap.min.css" integrity="sha384-2hfp1SzUoho7/TsGGGDaFdsuuDL0LX2hnUp6VkX3CUQ2K4K+xjboZdsXyp4oUHZj" crossorigin="anonymous">

	<!-- Custom CSS-->
	<link rel="stylesheet" href="<?php print base_url();?>assets/css/main.css">
</head>
<body>
	<div class="container text-xs-center home-container">
		<img src="<?php print base_url();?>assets/img/logo.png" class="home-logo">
		<p class="home-wh-title">WorkflowHunt</p>
		<div class="col-lg-8 col-xl-8 offset-lg-2 offset-xl-2">
			<form action="<?php print base_url();?>index.php/web/results/" method="GET">
				<div class="input-group">
					<input type="text" class="form-control home-input-search" name="query">
					<span class="input-group-btn">
				        <button class="btn btn-primary home-btn-search" type="submit">
				        	<span class="fa fa-search"> </span>
				        </button>
				        <!-- .home-btn-search -->
				    </span>
				    <!-- .input-group-btn -->
				</div>
				<!-- .input-group -->
			</form>
			<!-- form -->
		</div>
		<!-- .col-lg-8 .col-xl-8 .offset-lg-2 .offset-xl-2 -->
		<div class="col-md-12 home-text">
			WorkflowHunt is a search engine for scientific workflow repositories.
		</div>
		<!-- .home-text -->
	</div>
	<!-- .home-container -->

	<!-- jQuery 3.1.1 -->
	<script src="https://code.jquery.com/jquery-3.1.1.min.js" type="text/javascript"></script>

	<!-- Latest compiled and minified JavaScript -->
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.4/js/bootstrap.min.js" integrity="sha384-VjEeINv9OSwtWFLAtmc4JCtEJXXBub00gtSnszmspDLCtC0I4z4nqz7rEFbIZLLU" crossorigin="anonymous"></script>

	<!-- Custom Script -->
	<script type="text/javascript">
		
	</script>
</body>
</html>