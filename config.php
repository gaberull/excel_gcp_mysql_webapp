<?php

// load composer generated files
require_once '../vendor/autoload.php';  // relative path may no longer be correct

// load GCS library
use Google\Cloud\Storage\StorageClient;
use \PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use \PhpOffice\PhpSpreadsheet\Writer\Csv;

$privateKeyFilePath = '../keys/silken-reducer-359320-b3cecc9b17ca.json';

function uploadFile($bucketName, $fileContent, $cloudPath) 
{
    $privateKeyFilePath = $GLOBALS['privateKeyFilePath'];
    // connect to Google Cloud Storage using private key as authentication
    try 
    {
        $storage = new StorageClient([
            'keyFile' => json_decode(file_get_contents($privateKeyFilePath), true)
        ]);
    } 
    catch (Exception $e) 
    {
        print $e;
        return false;
    }

    // set which bucket to work in
    $bucket = $storage->bucket($bucketName);

    // upload/replace file 
    $storageObject = $bucket->upload(
            $fileContent,
            ['name' => $cloudPath]
            // if $cloudPath is existed then will be overwrite without confirmation
            // NOTE: 
            // a. do not put prefix '/', '/' is a separate folder name  !!
            // b. private key MUST have 'storage.objects.delete' permission if want to replace file !
    );
    return $storageObject != null;  // return if successful or not
}

function getFileInfo($bucketName, $cloudPath) {
    $privateKeyFilePath = $GLOBALS['privateKeyFilePath'];
    // connect to Google Cloud Storage using private key as authentication
    try {
        $storage = new StorageClient([
            'keyFile' => json_decode(file_get_contents($privateKeyFilePath), true)
        ]);
    } catch (Exception $e) {
        // maybe invalid private key ?
        print $e;
        return false;
    }
    // set which bucket to work in
    $bucket = $storage->bucket($bucketName);
    $object = $bucket->object($cloudPath);
    return $object->info();
}

//list files in Google storage bucket
/*
function listFiles($bucket, $directory = null) {

    if ($directory == null) {
        // list all files
        $objects = $bucket->objects();
    } else {
        // list all files within a directory (sub-directory)
        $options = array('prefix' => $directory);
        $objects = $bucket->objects($options);
    }
    foreach ($objects as $object) {
        print $object->name() . PHP_EOL;
        // NOTE: if $object->name() ends with '/' then it is a 'folder'
    }
}
*/
            /*
function downloadFileToMemory($bucketName, $cloudPath) {
    $privateKeyFileContent = $GLOBALS['privateKeyFileContent'];
    // connect to Google Cloud Storage using private key as authentication
    try {
        $storage = new StorageClient([
            'keyFile' => json_decode(file_get_contents($privateKeyFilePath), true)
        ]);
    } catch (Exception $e) {
        // maybe invalid private key ?
        print $e;
        return false;
    }
    // set which bucket to work in
    $bucket = $storage->bucket($bucketName);
    $object->$bucket->object($cloudPath);
    $contents = $object->downloadAsString();
    printf(
        'Downloaded gs://%s/%s to %s' . PHP_EOL,
        $contents,
        $bucketName,
        $contents
    );
    return $contents;
}
        */

// Download file from GCS storage to local disk (on VM)
function downloadLocally($bucketName, $cloudPath, $localpath)
{
    //$bucketName = 'xlsx-uploads'; $temp = downloadLocally($bucketName, $cloudPath, $localpath);
    // $objectName = 'my-object';
    // $destination = '/path/to/your/file';

    $privateKeyFilePath = $GLOBALS['privateKeyFilePath'];
    // connect to Google Cloud Storage using private key as authentication
    try {
        $storage = new StorageClient([
            'keyFile' => json_decode(file_get_contents($privateKeyFilePath), true)
        ]);
    } catch (Exception $e) {
        // maybe invalid private key ?
        print $e;
        return false;
    }
    $bucket = $storage->bucket($bucketName);
    $object = $bucket->object($cloudPath);
    $object->downloadToFile($localpath);
    return true; // return object (Psr\Http\Message\StreamInterface)
}

// use mysqli extension to connect to MySQL DB
function connectToDB()
{
    //mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $json_credentials = file_get_contents('../keys/db_credentials.json');
    $json_data = json_decode($json_credentials, true);
    if($json_data == null)
    {
        return null;
    }
    // connect to database
    $mysqli = new mysqli(
        $json_data['host'],
        $json_data['user'],
        $json_data['password'],
        $json_data['database']
    );
    if($mysqli != null)
    {
        $mysqli->set_charset('utf8mb4');
    }
    return $mysqli;
}
