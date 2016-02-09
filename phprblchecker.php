#!/usr/local/bin/php
<?php

/**
 *
 * This script checks if IPs are blacklisted on main RBL providers
 *
 * @author     Yann Rapenne <yann.rapenne@cepv.ch>
 *
 */

//Force PHP CLI usage
if (substr(php_sapi_name(), 0, 3) != 'cli') {
    echo "This script must be executed in command line!\n";
    exit();
}



//Set default timezone
date_default_timezone_set('Europe/Zurich');

/*
$ips = array that contans the IPs that you want to test for
example: array('8.8.8.8' => 'Human readable description');

$rbls = array that contains the RBLs urls that you want to test for. The values are stored on TXT file, one url by line
            
Note: RBLs must be queried by DNS !
*/

$ips = array('8.8.8.8' => 'Google DNS');

$rbls = file('rbls-addresses.txt');
$rbls = array_combine($rbls, $rbls); 

foreach($ips as $ip => $value){
    //Reverse IP
    $ipReverse = explode('.',$ip);
    $ipReverse = "$ipReverse[3].$ipReverse[2].$ipReverse[1].$ipReverse[0]";
    
    echo "*** Search for $value ($ip) in black list: *** \n\n";
    
    foreach($rbls as $rbl){
        //Concatenate strings to obtain the final test chain
        $testChain = "$ipReverse.$rbl";
        
        $execOutput = '';
        
        exec("dig +short -t a $testChain",$execOutput);
        
        if(is_array($execOutput)){
            if(count($execOutput) > 0){
                foreach($execOutput as $value){
                    if(preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\z/',$value)){
                        echo date('d/m/Y H\hm').": /!\ listed on $rbl"; 
                    }
                    else{
                        echo date('d/m/Y H\hm').": (OK) NOT listed on $rbl"; 
                    }
                }
            }
            else{
                echo date('d/m/Y H\hm').": (OK) NOT listed on $rbl"; 
            }
        }
    }
    echo "\n\n";
}

//Print some spacers
echo "\n\n";
?>