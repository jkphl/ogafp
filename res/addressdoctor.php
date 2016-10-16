<?php

define ('COUNTRY', trim($_GET['country']));

?><!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Address format editor</title>
        <link rel="stylesheet" type="text/css" href="../css/common.css" media="all" />
    </head>
    <body class="white pad">
    	<?php echo strtr(file_get_contents('http://www.addressdoctor.com/index.php?eID=ad_worldMAP&ISO3='.COUNTRY.'&lang=0'), array(
    		'fileadmin' => 'http://www.addressdoctor.com/fileadmin'		
    	)); ?>
    </body>
</html>