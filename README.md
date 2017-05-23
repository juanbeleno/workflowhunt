# WorkflowHunt

WorkflowHunt is a search engine for scientific workflow repositories. See [WorkflowHunt](http://workflowhunt.com/)

# Initial Setup

This project was built in PHP using CodeIgniter as back-end framework and Bootstrap as front-end framework. The steps to setup the project are explained as follows:

1. Download [Codeigniter](https://www.codeigniter.com/) 3.1.4 
2. Clone this project using git (`git clone https://github.com/jbeleno/workflowhunt.git`) or download the [ZIP](https://github.com/jbeleno/workflowhunt/archive/master.zip)
3. Replace the files and directories in CodeIgniter using the files and directories in this project.
4. Setup your favourite web environment for PHP. I personally use AWS (EC2, RDS, and Route 53). Nevertheless, for testing your can download XAMPP (for Windows) or LAMPP (for Linux).
5. Create a database with name `workflowhunt`. For LAMPP/XAMPP the database is MySQL.
6. Setup CodeIgnite. Usually, I just configure 4 files:

6.1. `application/config/autoload.php`: This file setup the packages, libraries, drivers, helpers, languages, models, etc that will be loaded when the project run for first time. 
Replace 

```
$autoload['libraries'] = array();
```

By 

```
$autoload['libraries'] = array('database', 'session');
```

6.2. `application/config/config.php`: This file is for general settings.

Add the timezone after `defined('BASEPATH') OR exit('No direct script access allowed');`

```
// Timezone setting
date_default_timezone_set('America/Sao_Paulo');
```

Replace

```
$config['base_url'] = '';
```

By

```
$config['base_url'] = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "https" : "http");
$config['base_url'] .= "://".$_SERVER['HTTP_HOST'];
$config['base_url'] .= str_replace(basename($_SERVER['SCRIPT_NAME']),"",$_SERVER['SCRIPT_NAME']);
```

6.3. `application/config/database.php`: Settings for database.

6.4. `application/config/routes.php`: This file manages the URL settings and URL defaults in the application.

Replace 
```
$route['default_controller'] = 'welcome';
```

By
```
$route['default_controller'] = 'web';
```

7. Setup ElasticSearch. Follow (Part II - Installing Elasticsearch) of this [tutorial](https://www.elastic.co/blog/running-elasticsearch-on-aws)

8. Run Apache/Nginx: `sudo service httpd start`

# Project Setup

Consider the project running in `localhost`.

1. Run `curl http://localhost/index.php/workflow/insert_workflow_metadata`
2. Run `curl http://localhost/index.php/workflow/update_workflow_metadata`
3. Run `curl http://localhost/index.php/ontology/download_terms`
4. Run `curl http://localhost/index.php/wordnet/store_synonyms`
5. Run `curl http://localhost/index.php/semantic/annotate`
6. Run `curl http://localhost/index.php/semantic/expand`
7. Run `curl http://localhost/index.php/elasticsearch/index_metadata`
8. Run `curl http://localhost/index.php/elasticsearch/index_semantics`


	