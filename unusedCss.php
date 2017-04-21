#!/usr/bin/php
<?php

/**
 * Please adjust
 */


$PATH = "./";
$outputFileName = 'unusedCss.txt';


// Default pattern
$pattern = '/\.-?[_a-zA-Z]+[_a-zA-Z0-9-]*\s*\{/';
$replace = [' {', '.'];
$searchFor = 'CSS classes';
$searchIn = 'others';


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
            } else {
                $results['others'][] = $path;
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
$cssClasses = [];
foreach($listOfAllFiles['css'] as $cssFile){
    $content = file_get_contents($cssFile);
    preg_match_all($pattern, $content, $listOfAllCssClassesInFile);
    if(count($listOfAllCssClassesInFile[0]) > 1){
        $cssClasses = array_merge($cssClasses, $listOfAllCssClassesInFile[0]);
    }
}

// Get clean strings
foreach($cssClasses as $key => $value){
    $cssClasses[$key] = str_replace($replace, '', $value);
}

// Backup
$unusedCssClasses = $cssClasses;

// Create final list
// The goal ist to go through each file and check if the string is used. If not, delete.
foreach($listOfAllFiles[$searchIn] as $file){
    $fileContent = file_get_contents($file);
    foreach($unusedCssClasses as $class_key => $class_name){
        if(strpos($fileContent, $class_name) !== false){
            unset($unusedCssClasses[$class_key]);
        }
    }
}

// Unused CSS classes
$unusedCssClassesAsText = '';
foreach($unusedCssClasses as $unusedCssClass){
    $unusedCssClassesAsText .= $unusedCssClass.PHP_EOL;
}

file_put_contents($outputFileName, $unusedCssClassesAsText);

echo "Amount S(CSS) files:".count($listOfAllFiles['css']).PHP_EOL;
echo "Amount other files:".count($listOfAllFiles['others']).PHP_EOL;
echo "Amount ".$searchFor.":".count($cssClasses).PHP_EOL;
echo "Amount unused ".$searchFor.":".count($unusedCssClasses).PHP_EOL;
echo $searchFor." written to: ".$outputFileName.PHP_EOL;