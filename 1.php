<?php
    define('ROOT_DIR', dirname(__FILE__));
    include_once(ROOT_DIR.'/config.php');
    include_once(ROOT_DIR.'/func.php');
    include_once(ROOT_DIR.'/curl.php');
    include_once(ROOT_DIR.'/dbm.class.php');

    if(isset($_GET['string'])){
        $db = new DBM();
        $db->DBManager('localhost', '', '', 'effect');
        $ids = explode(',', $_GET['string']);
        $tmp = checkIds($ids);
        $urls = linkCreater($tmp['names']);
        $result = batchGet($urls) ;
        $data = processData($result);
        if(count($data) > 0) {
            $data = arraySpecialMerge($tmp['str'], $data);
            insertData($data);
        } else {
            $data = &$tmp['str'];
        }
    }
    if(count($data) > 0){
        include('show.php');
    }
?>
