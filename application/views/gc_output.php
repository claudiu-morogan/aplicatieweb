<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php 
foreach($css_files as $file): ?>
	<link type="text/css" rel="stylesheet" href="<?php echo $file; ?>" />
<?php endforeach; ?>
<link type="text/css" rel="stylesheet" href="<?php echo site_url(); ?>assets/css/style.css" />
<?php foreach($js_files as $file): ?>
	<script src="<?php echo $file; ?>"></script>
<?php endforeach; ?>
<style type='text/css'>
body
{
	font-family: Arial;
	font-size: 14px;
}
a {
    color: blue;
    text-decoration: none;
    font-size: 14px;
}
a:hover
{
	text-decoration: underline;
}
</style>
</head>
<body>
<ul class="menu cf">
    <li><a href="<?=site_url();?>masaje">Masaje</a></li>
    <li><a href="<?=site_url();?>masaje/clienti">Clienti</a></li>
	<!-- <li>
		<a href="">Taskuri</a>
		<ul class="submenu">
			<li><a href="<?=site_url();?>taskmanager/nefinalizateLunarUseri">Echipa</a></li>
			<li><a href="<?=site_url();?>taskmanager/">Personale</a></li>
		</ul>			
	</li> -->
	<!-- <li><a href="<?=site_url();?>taskmanager/logout">Logout</a></li> -->
</ul>
	<div style='height:20px;'></div>  
    <div>
		<?php echo $output; ?>
    </div>
</body>
</html>
