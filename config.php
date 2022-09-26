<?php

// load composer generated files
require_once '../vendor/autoload.php';  // relative path may no longer be correct

// load GCS library
use Google\Cloud\Storage\StorageClient;
// load PHPSpreadsheet xlsx and csv tools
use \PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use \PhpOffice\PhpSpreadsheet\Writer\Csv;

// Path to private API key stored on GCP VM (not publicly)
// TODO: encrypt file?
$privateKeyFilePath = '../keys/silken-reducer-359320-b3cecc9b17ca.json';

/**
 *  Upload file to Google Cloud Storage Bucket
 * 
 *  @return bool - true if file is uploaded, false if not
 */
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

/**
 *  Get File info for specific file in Google Cloud Storage Bucket
 */
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

/**
 *  List all items in Google Cloud storage bucket
 */
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
/**
 *  From Google Cloud Storage API Documentation
 * 
 * @param $bucketName - name of bucket (xlsx-uploads)
 * @param $cloudPath - path of file on GCS
 * 
 * @return bool or storage object containing file info
 */
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

/**
 *  Download file from GCS storage to local disk (on VM)
 * 
 *  @return true if object is downloaded correctly
 *  @return false if StorageClient fails to be created (Google Cloud Storage API call)
 */
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
        echo $e;
        return false;
    }
    $bucket = $storage->bucket($bucketName);
    $object = $bucket->object($cloudPath);
    $object->downloadToFile($localpath);
    return true; // return object (Psr\Http\Message\StreamInterface)
}

/**
 * Use mysqli extension to connect to MySQL database
 * 
 * @return null if json_decode() fails
 * @return null if mysqli object isn't created (connection to MySQL DB failed)
 * @return mysqli object if MySQL database connection was successful
 */
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
