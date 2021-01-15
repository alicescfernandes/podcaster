<?php
namespace Utils;
use \Aws\S3\S3Client;
use \Aws\S3\MultipartUploader;
use \Aws\Exception\AwsException;
use \Aws\Exception\MultipartUploadException;

class S3Connector{
    private $s3;
    function __construct(){
        //Create a S3Client
        $this->s3 = new S3Client([
            'version' => 'latest',
            'region' => AWS_REGION,
            'credentials' => [
                'key'    => AWS_KEY,
                'secret' => AWS_SECRET,
            ],
            'http'    => [
                'verify' => ROOT_DIR.'/cert.pem'
            ]
        ]);
    }
    
    function upload($folder,$key, $fileContents){
        // Send a PutObject request and get the result object.
        $result = $this->s3->putObject([
            'Bucket' => AWS_S3_BUCKET,
            'Key' => "{$folder}/${key}",
            'Body' => $fileContents,
        ]);
        return $result;
    }

    function multipartUpload($folder,$key, $source){
     
        // Use multipart upload
        $uploader = new MultipartUploader($this->s3, $source, [
            'Bucket' => AWS_S3_BUCKET,
            'key' => "{$folder}/${key}",
        ]);
        
        try {
            $result = $uploader->upload();
            //echo "Upload complete: {$result['ObjectURL']}\n";
        } catch (MultipartUploadException $e) {
            echo $e->getMessage() . "\n";
        }
        return $result;        
    }

    function getObjectURL($folder, $key){
        $cmd = $this->s3->getCommand('GetObject', [
            'Bucket' => AWS_S3_BUCKET,
            'Key' => "{$folder}/${key}"
        ]);
        $request = $this->s3->createPresignedRequest($cmd, '1 hour');
        return (string) $request->getUri();
        // Print the body of the result by indexing into the result object.
        //echo("<img src='".(string) $request->getUri()."'>" );   
    }

    function deleteObject($folder){
        $result = $this->s3->deleteMatchingObjects(AWS_S3_BUCKET,"{$folder}/");
        return $result;
    }
}


?>