<?php
    require 'gfl.php';

    header('Content-Type: text/html; charset=UTF-8');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache');
	
    echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
    include_once 'include/config.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
    <title><?php echo _TITLE; ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="-1" />
    <link rel="stylesheet" type="text/css" href="<?php echo _SKIN; ?>/css/xams.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo _SKIN; ?>/css/form.css" />
    <link rel="SHORTCUT ICON" href="favicon.ico" />
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.8/jquery.min.js"></script>
    <script type="text/javascript" src="iframe.js"></script>
	<?php
        if (isset($CSS_ADD))
            echo $CSS_ADD;
    ?>
	<style type="text/css">
		#container{
			width:984px;
			margin:0 auto;
		}
		
		#col_left{
			float:left;
			width:230px;
		}
		
		#col_right{
			width:742px;
			margin-left:10px;
			margin-top:0px;
			float:left;
		}

		#headband{
			margin-left:-12px;
			margin-right:-3px;
			height:80px;
			margin-bottom:5px;
			border-bottom:1px;
			border-color:white;
			background-color:#fff;
		}
		
		#framemenu{
			height:900px;	
			width:230px;
			overflow:hidden;
		}
		
		#framecontenu{
			height:800px;	
			width:100%;
			overflow:auto;
		}
	</style>
</head>
<body>
	<div id="container">
	     <div id="col_left">
             	  <iframe frameborder="0" name="framemenu" id="framemenu"  scrolling="no" src="menu.php">
        	  </iframe>
	     </div>
	     <div id="col_right">
	     	  <div id="headband"></div>
	     	  <iframe frameborder="0" name="framecontenu" id="framecontenu" src="startup.php" onload="window.parent.parent.scrollTo(0,0)">
		  </iframe>
	     </div>
        </div>
</body>
</html>
