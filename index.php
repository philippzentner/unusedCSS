<?php

/**
 * Please adjust
 */
$PATH = ‘/path/to/your/application’;
$outputFileName = 'unusedCss.txt';


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

// Get all CSS classes
$cssClasses = [];
foreach($listOfAllFiles['css'] as $cssFile){
    $content = file_get_contents($cssFile);
    preg_match_all("/\.-?[_a-zA-Z]+[_a-zA-Z0-9-]*\s*\{/", $content, $listOfAllCssClassesInFile);
    $cssClasses = array_merge($cssClasses, $listOfAllCssClassesInFile[0]);
}

// Clean classnames
foreach($cssClasses as $key => $value){
    $cssClasses[$key] = str_replace([' {', '.'], '', $value);
}

// Backup
$unusedCssClasses = $cssClasses;

// Create final list
// The goal ist to go through each file and check if the css class is used. If not, delete.
foreach($listOfAllFiles['others'] as $file){
    $fileContent = file_get_contents($file);
    foreach($unusedCssClasses as $class_key => $class_name){
        if(strpos($fileContent, $class_name) !== false){
            unset($unusedCssClasses[$class_key]);
        }
    }
}

echo "Amount SCSS files:".count($listOfAllFiles['css']).PHP_EOL;
echo "Amount other files:".count($listOfAllFiles['others']).PHP_EOL;
echo "Amount CSS classes:".count($cssClasses).PHP_EOL;
echo "Amount unused CSS classes:".count($unusedCssClasses).PHP_EOL;

$unusedCssClassesAsText = '';
foreach($unusedCssClasses as $unusedCssClass){
    $unusedCssClassesAsText .= $unusedCssClass.PHP_EOL;
}

file_put_contents($outputFileName, $unusedCssClassesAsText);

echo "Unused CSS written to: ".$outputFileName.PHP_EOL;