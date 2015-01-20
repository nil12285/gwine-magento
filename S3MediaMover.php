<?php
require 'aws-autoloader.php';

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;




// Upload a publicly accessible file. The file size, file type, and MD5 hash
// are automatically calculated by the SDK.
$mediaDir = dirname(__FILE__) . '/media/catalog/product';
$objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($mediaDir), RecursiveIteratorIterator::SELF_FIRST);
$mys3 = new MyS3();
foreach($objects as $name => $object){
    if(is_file($name)) {
        $mys3->pushToS3($name);
    }
}

class MyS3
{
    private $s3 = null;
    function __construct() {
        $xml_string = file_get_contents(dirname(__FILE__).'/app/etc/config.xml');
        $xml = simplexml_load_string($xml_string);
        $json = json_encode($xml);
        $config = json_decode($json,TRUE);
        
        
        // Instantiate an S3 client
        $this->s3 = S3Client::factory(array(
            'key'    => $config['aws']['s3']['AccessKey'],
            'secret' => $config['aws']['s3']['SecretKey']
        ));
    }


    function pushToS3($fileName) {
        try {
            $this->s3->putObject(array(
                'Bucket' => 'gwine',
                'Key'    => str_replace("/var/www/gwine-magento/media", "product-media", $fileName),
                'Body'   => fopen($fileName, 'r'),
                'ACL'    => 'public-read',
            ));
            echo $fileName . "\n";
        } catch (S3Exception $e) {
            print_r($e);
            echo "There was an error uploading the file.\n";
        }
        
    }

}