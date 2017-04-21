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
	<div class="container results-container">
		<div class="row">
			<div class="col-lg-1 col-xl-1 text-xs-center text-lg-right">
				<a href="<?php print base_url();?>index.php/web/index">
					<img src="<?php print base_url();?>assets/img/logo.png" class="results-logo">
				</a>
			</div>
			<!-- .col-lg-2 .col-xl-2 .text-xs-center .text-lg-right -->
			<div class="col-lg-11 col-xl-11">
				<form action="<?php print base_url();?>index.php/web/results/" method="GET">
					<div class="input-group">
						<input type="text" class="form-control results-input-search" name="query" value="<?php print $query; ?>">
						<span class="input-group-btn">
					        <button class="btn btn-primary results-btn-search" type="submit">
					        	<span class="fa fa-search"> </span>
					        </button>
					        <!-- .results-btn-search -->
					    </span>
					    <!-- .input-group-btn -->
					</div>
					<!-- .input-group -->

					<div>
						<?php 
							if($method == "semantic")
							{
						?>
							<a href="<?php print base_url();?>index.php/web/results/?query=<?php print urlencode($query); ?>" class="results-tab">Keyword</a>
							<a href="<?php print base_url();?>index.php/web/results/?query=<?php print urlencode($query); ?>&method=semantic" class="results-tab-active">Semantic</a>
						<?php
							}
							else
							{ 
						?>
							<a href="<?php print base_url();?>index.php/web/results/?query=<?php print urlencode($query); ?>" class="results-tab-active">Keyword</a>
							<a href="<?php print base_url();?>index.php/web/results/?query=<?php print urlencode($query); ?>&method=semantic" class="results-tab">Semantic</a>
						<?php 
							}
						?>
					</div>
				</form>
				<!-- form -->
			</div>
			<!-- .col-lg-10 .col-xl-10 -->
		</div>
		<!-- .row -->
		<hr>
		<div class="row">
			<div class="col-lg-7 col-xl-7 offset-lg-1 offset-xl-1">
				<?php 	
					if($status == 'BAD')
					{
				?>
					<div class="results-not-found">
						<p>
							Your search - <strong><?php print $query; ?></strong> - 
							did not match any workflow.
						</p>
						<p>Suggestions:</p>
						<ul>
							<li>Ensure all words are spelled correctly.</li>
							<li>Try using different words or synonyms.</li>
							<li>Try using more general keywords.</li>
							<li>Make your queries as concise as possible.</li>
						</ul>
					</div>
					<!-- .results-not-found -->
				<?php 	
					}
					else
					{
				?>
					<div class="results-found">
						<p><?php print $total; ?> Resultados</p>
						<div class="results-content">
							<?php
								foreach ($results as $workflow) 
								{
							?>
								<div class="results-workflow">
									<a href="http://www.myexperiment.org/workflows/<?php print $workflow['_id'];?>" class="results-workflow-title" target="_blank" rel="noopener noreferrer">
										<?php print $workflow['_source']['title'];?>
									</a>
									<div class="results-workflow-url">
										http://www.myexperiment.org/workflows/<?php print $workflow['_id'];?>
									</div>
									<div class="results-workflow-description">

										<?php print character_limiter($workflow['_source']['description'], 150);?>
									</div>
									<div class="results-workflow-wfms">
										Workflow Management System: <strong><?php print $workflow['_source']['wfms'];?></strong>
									</div>
									<?php 
										if($method == "semantic")
										{
									?>
									<div class="results-semantic-annotations">
										<strong> Semantic annotations: </strong>
										<span>
											<a target="_blank" href="<?php print base_url().'index.php/web/workflow?id='.$workflow['_id']; ?>">
												READ MORE		
											</a>
											 - 
											<span class="results-span-analytics" onclick="show_analytics(<?php print $workflow['_id'];?>)">ANALYTICS</span>
									</div>
									<?php
										}
									?>
								</div>
								<!-- .results-workflow -->
							<?php
								}
							?>
						</div>
						<!-- .results-content -->
						<nav aria-label="..." class="text-xs-center">
							<ul class="pagination pagination-sm">
								<?php print $pagination->create_links(); ?>
							</ul>
							<!-- ul -->
						</nav>
						<!-- nav -->
					</div>
					<!-- .results-found -->
				<?php 	
					}
				?>
			</div>
			<!-- .col-lg-10 .col-xl-10 .offset-lg-2 .offset-xl-2-->
			<div class="col-lg-4 col-xl-4" id="semantic-augmentation">
				
			</div>
			<!-- .col-lg-4 .col-xl-4-->
		</div>
	</div>

	<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>

    <!-- Custom JavaScript -->
    <script type="text/javascript">
    	var flag = true;

    	function show_analytics(id_workflow)
    	{
    		if(flag)
    		{
    			flag = false;
    			$.ajax({
					method: "POST",
					url: "<?php print base_url(); ?>index.php/workflow/get_semantic_augmentation",
					context: document.body,
					data: { 
						id_workflow: id_workflow
					},
					success: function(result){
						if(result.status == 'OK')
						{
							var content = "";
							if(result.workflows.length > 0)
							{
								content += '<div class="results-analytic-box">';
								content += '<div class="results-analytic-title">Workflows associated</div>';
								content += '<div class="results-analytic-description">';
								content += 'These workflows were listed here because they are subworkflows of the selected workflow or they use the selected workflow as subworkflow.';
								content += '</div>';
								content += '<ul class="results-analytic-list">';
								for (var i = 0; i < result.workflows.length; i++) 
								{
									content += '<li><a target="_blank" href="<?php print base_url().'index.php/web/workflow?id='; ?>'+result.workflows[i].id+'">'+result.workflows[i].title+'</a></li>';
								}
								content += '</ul>';
								content += '</div>';
							}

							if(result.authors.length > 0)
							{
								content += '<div class="results-analytic-box">';
								content += '<div class="results-analytic-title">Authors</div>';
								for (var i = 0; i < result.authors.length; i++) 
								{
									content += '<div class="results-analytic-author">';
									content += '<img class="results-analytic-author-thumbnail" src="'+result.authors[i].photo+'">';
									content += '<div class="results-analytic-author-content">';
									content += '<a href="https://www.myexperiment.org/users/59168">'+result.authors[i].name+'</a>';
									if(result.authors[i].email)
									{
										content += '<div>Email: '+result.authors[i].email+'</div>';
									}
									if(result.authors[i].website)
									{
										content += '<div>Website: <a href="'+result.authors[i].website+'"> Here </a></div>';
									}
									content += '</div>';
									content += '</div>';
								}
								content += '</div>';
							}

							$("#semantic-augmentation").html(content);
						}
						flag = true;
		    		}
		    	});
    		}
    	}
    </script>
</body>
</html>