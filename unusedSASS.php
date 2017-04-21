#!/usr/bin/php
<?php

/**
 * Please adjust
 */


$PATH = "./";
$outputFileName = 'unusedSass.txt';


// Default pattern
$pattern = '/\$-?[_a-zA-Z0-9-]+[_a-zA-Z0-9-]\:/';
$replace = [':'];
$searchFor = 'SASS variables';
$searchIn = 'css';

if($argc > 0){

    // Check for path
    foreach($argv as $v){
        $len = strlen($v);
        if($len >= 3) {
            if(substr( $v, 0, 3 ) === "-p="){
                $PATH = substr( $v, 3, $len );
                break;
            }
        }
    }
}


/**
 * SCRIPT START
 */
ini_set('max_execution_time', 0);

function getFilesRecursive($dir, $results = ['css'=> [], 'others'=>[]]){
    $files = scandir($dir);

    foreach($files as $key => $value){
        $path = realpath($dir.DIRECTORY_SEPARATOR.$value);
        if(!is_dir($path)) {
            if(strpos($path, '.scss') || strpos($path, '.css')){
                $results['css'][] = $path;
            }
        } else if($value !== "." && $value !== "..") {
            $results = getFilesRecursive($path, $results);
        }
    }

    return $results;
}

// Get list of all files
$listOfAllFiles = getFilesRecursive($PATH);

// Get all searched strings
$sassVars = [];
foreach($listOfAllFiles['css'] as $cssFile){
    $content = file_get_contents($cssFile);
    preg_match_all($pattern, $content, $listOfAllCssClassesInFile);
    $sassVars = array_merge($sassVars, $listOfAllCssClassesInFile[0]);
}

// Get clean strings
foreach($sassVars as $key => $value){
    $sassVars[$key] = str_replace($replace, '', $value);
}

// Backup
$sassVarsUncounted = $sassVars;


// Create list of variable usages
$sassVarUsages = [];
foreach($listOfAllFiles[$searchIn] as $file){
    $fileContent = file_get_contents($file);
    foreach($sassVarsUncounted as $var_key => $var_name){
        $amount = substr_count($fileContent, $var_name);
        if(isset($sassVarUsages[$var_name])){
            $sassVarUsages[$var_name] += $amount;
        }else{
            $sassVarUsages[$var_name] = $amount;
        }
    }
}

foreach($sassVarUsages as $var_name => $var_count){
    if($var_count > 1){
        unset($sassVarUsages[$var_name]);
    }
}

$unusedSassVars = $sassVarUsages;

// Unused CSS classes
$unusedSassVarssAsText = '';
foreach($unusedSassVars as $unusedSassVarName => $unusedSassVarValue){
    $unusedSassVarssAsText .= $unusedSassVarName.PHP_EOL;
}

file_put_contents($outputFileName, $unusedSassVarssAsText);

echo "Amount S(CSS) files:".count($listOfAllFiles['css']).PHP_EOL;
echo "Amount other files:".count($listOfAllFiles['others']).PHP_EOL;
echo "Amount ".$searchFor.":".count($sassVars).PHP_EOL;
echo "Amount unused ".$searchFor.":".count($unusedSassVars).PHP_EOL;
echo $searchFor." written to: ".$outputFileName.PHP_EOL;