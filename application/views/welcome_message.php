<!doctype html>  
<!--[if lt IE 7 ]> <html lang="en" class="no-js ie6"> <![endif]-->
<!--[if IE 7 ]>    <html lang="en" class="no-js ie7"> <![endif]-->
<!--[if IE 8 ]>    <html lang="en" class="no-js ie8"> <![endif]-->
<!--[if IE 9 ]>    <html lang="en" class="no-js ie9"> <![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--> <html lang="en" class="no-js"> <!--<![endif]-->
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	
	<title>Welcome to CodeIgniter</title>
	<meta name="description" content="Assets demo">
	<meta name="author" content="Boris Strahija, http://creolab.hr">
	
	<?php //$this->assets->clear_cache(); ?>
	<?php display_css(array('init.css', 'style.css')); ?>
	<?php display_js(array('libs/modernizr-1.6.js', 'libs/jquery-1.4.4.js', 'plugins.js', 'script.js')); ?>
</head>
<body>
<div id="layout">
	<header>
		<h1>Welcome to CodeIgniter!</h1>
	</header>
    
    <div id="main">
		<p>The page you are looking at is being generated dynamically by CodeIgniter.</p>
		
		<p>If you would like to edit this page you'll find it located at:</p>
		<code class="hot">application/views/welcome_message.php</code>
		
		<p>The corresponding controller for this page is found at:</p>
		<code>application/controllers/welcome.php</code>
		
		<p>If you are exploring CodeIgniter for the very first time, you should start by reading the <a href="user_guide/">User Guide</a>.</p>
	</div>
    
	<footer>
		<p>Page rendered in <strong>{elapsed_time}</strong> seconds</p>
	</footer>
</div>
<!-- /#layout -->
  
</body>
</html>