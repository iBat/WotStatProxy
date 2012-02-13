<?php
    if (preg_match ("/mysql.php/", $_SERVER['PHP_SELF']))
    {
        header ("Location: /index.php");
        exit;
    }
    $servername='localhost';
    // username and password to log onto db server
    $dbusername='root';
    $dbpassword='Kndr:34.';
    // name of database
    $dbname='effect';
    connecttodb($servername,$dbname,$dbusername,$dbpassword);
    function connecttodb($servername,$dbname,$dbuser,$dbpassword)
    {
        global $link;
        $link=mysql_connect ("$servername","$dbuser","$dbpassword");
        if(!$link){die("Could not connect to MySQL");}
        mysql_select_db("$dbname",$link) or die ("could not open db".mysql_error());
    }
?>
