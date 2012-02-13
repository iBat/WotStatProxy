<?php
    if (preg_match ("/show.php/", $_SERVER['PHP_SELF']))
    {
        header ("Location: /index.php");
        exit;
    }
    header("Content-type: text/plain; charset=windows-1251");
    $n = 0;

    foreach($names as $name){
        if($n == 0){
            echo $data[$name]['eff'].'-'.$data[$name]['win'];
            $n++;
        }else{
            echo ','.$data[$name]['eff'].'-'.$data[$name]['win'];    
        }
    }
?>