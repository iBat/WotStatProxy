<?php  
    if (preg_match ("/func.php/", $_SERVER['PHP_SELF'])) {
        header ("Location: /index.php");
        exit;
    }

    function now() {
        return strtotime(date("Y-m-d H:i:s"));
    }

    function arraySpecialMerge($array1, $array2) {
        foreach($array2 as $key2 => $val2) {
            if(!array_key_exists($key2,$array1)) {
                $array1[$key2] = $val2;
            } else {
                $array1[] = $val2;
            }
        }
        return $array1;
    }

    function getHeadersCurl($url) {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL,            $url);
        curl_setopt($ch, CURLOPT_HEADER,         true);
        curl_setopt($ch, CURLOPT_NOBODY,         true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT,        10);

        $r = @curl_exec($ch);
        $r = @explode("\n", $r);
        return $r;
    } 

    function isValidUrl($url) {
        $url = @parse_url($url);

        if(!$url) {
            return false;
        }

        $url = array_map('trim', $url);
        $url['port'] = (!isset($url['port'])) ? 80 : (int)$url['port'];
        $path = (isset($url['path'])) ? $url['path'] : '';

        if($path == '')
            $path = '/';

        $path .= (isset ($url['query'])) ? "?$url[query]" : '';

        if(isset ($url['host']) AND $url['host'] != gethostbyname ($url['host'])) {
            if(PHP_VERSION >= 5)
                $headers = getHeadersCurl("$url[scheme]://$url[host]:$url[port]$path");
            
            $headers = (is_array($headers)) ? implode ("\n", $headers) : $headers;
            preg_match_all('#^HTTP/.*\s+[0-9]+\s#i', $headers,$match);
            $tmp = explode(' ', $match[0][0]);
            
            $code = array(200,301,302);
            if(in_array($tmp[1],$code))
                return true ;
        }
        return false;
    }

    function insertData($data) {
        global $db;
        foreach($data as $name => $ins) {
            $sql = "SELECT COUNT(*) FROM players WHERE name = '".$name."';";
            $db->executeQuery($sql); 
            $str = $db->loadResult();
            if($str[0]['COUNT(*)'] == 0) {
                $sql = "INSERT INTO players (name,time,eff,win) VALUES ('".$name."','".now()."','".$ins['eff']."','".$ins['win']."');";
                echo $sql;
                $db->executeQuery($sql);
            } else {
                $sql = "UPDATE players SET time = '".now()."', eff = '".$ins['eff']."', win = '".$ins['win']."'  WHERE name = '".$name."';";
                $db->executeQuery($sql);
            } 
        }
    }

    function checkNames($names) {
        global $db;
        $str = array();
        foreach($names as $name) {
            $sql = "SELECT COUNT(*) FROM players WHERE time > ".(now() - CACHE * 3600)." AND name = '".$name."';";
            $db->executeQuery($sql);
            $str = $db->loadResult();
            if($str[0]['COUNT(*)'] != 0) {
                $sql = "SELECT * FROM players WHERE name = '".$name."';";
                $db->executeQuery($sql);
                $str = $db->loadResult();
                $new_str[$name]['win'] = $str[0]['win'];
                $new_str[$name]['eff'] = $str[0]['eff'];
            } else {
                $new_name[] = $name;
            }
        }
        $new['names'] = &$new_name;
        $new['str'] = &$new_str;
        return $new;
    }

    function checkIds($ids) {
        global $db;
        $str = array();
        foreach($ids as $id) {
            $sql = "SELECT COUNT(*) FROM players WHERE time > ".(now() - CACHE * 3600)." AND name = '".$id."';";
            $db->executeQuery($sql);
            $str = $db->loadResult();
            if($str[0]['COUNT(*)'] != 0) {
                $sql = "SELECT * FROM players WHERE name = '".$id."';";
                $db->executeQuery($sql);
                $str = $db->loadResult();
                $new_str[$id]['win'] = $str[0]['win'];
                $new_str[$id]['eff'] = $str[0]['eff'];
            } else {
                $new_name[] = $id;
            }
        }
        $new['names'] = &$new_name;
        $new['str'] = &$new_str;
        return $new;
    }

    function batchGet($urls) {
        $curl = new CURL();
        $opts = array(CURLOPT_RETURNTRANSFER => true);
        foreach($urls as $key => $link)
            $curl->addSession( $link, $key, $opts );
        $result = $curl->exec();  
        $curl->clear();
        return $result;

    }

    function getMemId($search) {
        $data = array();
        $error = 0;
        $special = ' /uc/accounts/';
        $request = "GET ".$special."?type=table&search=".(trim($search))." HTTP/1.0\r\n";
        $request.= "Accept: text/html, */*\r\n";
        $request.= "User-Agent: Mozilla/3.0 (compatible; easyhttp)\r\n";
        $request.= "X-Requested-With: XMLHttpRequest\r\n";
        $request.= "Host: worldoftanks.ru\r\n";
        $request.= "Connection: Keep-Alive\r\n";
        $request.= "\r\n";

        $n = 0;
        while(!isset($fp)) {
            $fp = fsockopen('worldoftanks.ru', 80, $errno, $errstr, 20);
            if($n == 5) {
                break;
            }
            $n++;
        }
        if(!$fp) {
            $error == 2; // Not seterd
        } else {
            stream_set_timeout($fp,20);
            $info = stream_get_meta_data($fp);

            fwrite($fp, $request);
            $page = '';

            while(!feof($fp) && (!$info['timed_out'])) {
                $page .= fgets($fp, 4096);
                $info = stream_get_meta_data($fp);
            }
            fclose($fp);
            if($info['timed_out']) {
                $error = 1; //Connection Timed Out
            }
        }
        if($error == 0) {
            preg_match_all("/{\"request_data\":(.*?),\"result\":\"success\"}/", $page, $matches);
            $data = (json_decode($matches[0][0], true));
        }
        return $data;
    }

    function urlCreate($srch, $name) {
        foreach($srch as $val) {
            if(strtolower($val['name']) == strtolower($name)) {
                $data = 'http://worldoftanks.ru/uc/accounts/'.$val['id'].'/api/1.2/?source_token=Intellect_Soft-WoT_Mobile-unofficial_stats'; 
            }
        }
        return $data;
    }

    function linkCreater($names) {
        $chk_url = 'http://worldoftanks.ru/uc/accounts/1000/api/1.2/?source_token=Intellect_Soft-WoT_Mobile-unofficial_stats'; 
        if(isValidUrl($chk_url) === true && count($names) < 40) {
            foreach($names as $name) {
                $srch = getMemId($name);
                if(isset($srch['request_data']['items']['0'])) {
                    $data[$name] = urlCreate($srch['request_data']['items'],$name);
                } else {
                    $srch = getMemId($name);
                    if(isset($srch['request_data']['items']['0'])) {
                        $data[$name] = urlCreate($srch['request_data']['items'],$name);
                    } else {
                        $data[$name] = 'http://worldoftanks.ru/uc/accounts/1/api/1.2/?source_token=Intellect_Soft-WoT_Mobile-unofficial_stats';    
                    }
                }
            }
        }
        return $data;
    }

    function processData($stats) {
        foreach($stats as $name => $stat) {
            $per_stat = (json_decode(trim($stat), true));
            if($per_stat['status'] == 'ok' && $per_stat['status_code'] == 'NO_ERROR') {
                if($per_stat['data']['summary']['battles_count'] != 0) {
                    $array[$name]['win'] = round($per_stat['data']['summary']['wins']*100/$per_stat['data']['summary']['battles_count'],1);
                } else {
                    $array[$name]['win'] = '0';
                }
                $tank_lvl['battle_count'] = 0;
                for($nl = 1; $nl <= 10; $nl++) {
                    $tank_lvl[$nl]['battle_count'] = 0;
                }
                foreach($per_stat['data']['vehicles'] as $tanks) {
                    $tank_lvl[$tanks['level']]['battle_count'] += $tanks['battle_count'];
                    $tank_lvl['battle_count'] += $tanks['battle_count'];
                }
                $mid = 0;
                foreach($tank_lvl as $lvl => $tanks) {
                    $mid +=  $lvl*$tanks['battle_count']/$tank_lvl['battle_count'];
                }
                $effect = array();
                if($per_stat['data']['summary']['battles_count'] != 0) {
                    $effect['dmg'] = $per_stat['data']['battles']['damage_dealt']/$per_stat['data']['summary']['battles_count'];
                    $effect['des'] = $per_stat['data']['battles']['frags']/$per_stat['data']['summary']['battles_count'];
                    $effect['det'] = $per_stat['data']['battles']['spotted']/$per_stat['data']['summary']['battles_count'];
                    $effect['cap'] = $per_stat['data']['battles']['capture_points']/$per_stat['data']['summary']['battles_count'];
                    $effect['def'] = $per_stat['data']['battles']['dropped_capture_points']/$per_stat['data']['summary']['battles_count'];
                    $array[$name]['eff'] = round(($effect['dmg']*(10/$mid)*(0.15+$mid*2/100) + $effect['des']*(0.35-$mid*2/100)*1000 + $effect['det']*0.2*1000 + $effect['cap']*0.15*1000 + $effect['def']*0.15*1000)/10,0)*10;
                } else {
                    $array[$name]['eff'] = 0;
                }

            } else {
                $array[$name]['eff'] = 'X';
                $array[$name]['win'] = 'X';
            }
        }
        return $array;   
    }
?>
