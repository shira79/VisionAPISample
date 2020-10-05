<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Estate;

use Illuminate\Support\Str;
use Google\Cloud\Storage\StorageClient;
use Google\Cloud\Vision\V1\AnnotateFileResponse;
use Google\Cloud\Vision\V1\AsyncAnnotateFileRequest;
use Google\Cloud\Vision\V1\Feature;
use Google\Cloud\Vision\V1\Feature\Type;
use Google\Cloud\Vision\V1\GcsDestination;
use Google\Cloud\Vision\V1\GcsSource;
use Google\Cloud\Vision\V1\ImageAnnotatorClient;
use Google\Cloud\Vision\V1\InputConfig;
use Google\Cloud\Vision\V1\OutputConfig;



class FileController extends Controller
{
    public $bucketName = 'realestate-info';
    public $bucketPrefix =  'outputs2/';

    /**
     * pdfファイルをOCRにかけて、データをGCSに配置する
     */
    public function convert()
    {
        // dd('gs:/'.base_path().'/File/doc.pdf');
        $path = 'gs://realestate-info//doc.pdf';
        $output = 'gs://realestate-info/outputs3/';
          # select ocr feature
        $feature = (new Feature())
        ->setType(Type::DOCUMENT_TEXT_DETECTION);

        # set $path (file to OCR) as source
        $gcsSource = (new GcsSource())
            ->setUri($path);
        # supported mime_types are: 'application/pdf' and 'image/tiff'
        $mimeType = 'application/pdf';
        $inputConfig = (new InputConfig())
            ->setGcsSource($gcsSource)
            ->setMimeType($mimeType);

        # set $output as destination
        $gcsDestination = (new GcsDestination())
            ->setUri($output);
        # how many pages should be grouped into each json output file.
        $batchSize = 2;
        $outputConfig = (new OutputConfig())
            ->setGcsDestination($gcsDestination)
            ->setBatchSize($batchSize);

        # prepare request using configs set above
        $request = (new AsyncAnnotateFileRequest())
            ->setFeatures([$feature])
            ->setInputConfig($inputConfig)
            ->setOutputConfig($outputConfig);
        $requests = [$request];

        # make request
        $imageAnnotator = new ImageAnnotatorClient();
        $operation = $imageAnnotator->asyncBatchAnnotateFiles($requests);
        print('Waiting for operation to finish.' . PHP_EOL);
        $operation->pollUntilComplete();

        $imageAnnotator->close();
    }


    /**
     * GCSに置いてあるファイルを読み込む
     */
    // public function read()
    // {
    //     $objects = $this->getObjects();

    //     foreach($objects as $object){
    //         $this->getPlaneText($object);
    //     }

    // }

    public function insert()
    {
        $objects = $this->getObjects();

        foreach ($objects as $obj){
            // $object->name();
            $this->storeInfo($obj);
        }

    }

    private function getBucket()
    {
        $storage = new StorageClient();
        return $storage->bucket($this->bucketName);
    }

    private function getObjects()
    {
        $bucket =  $this->getBucket();
        $options = ['prefix' => $this->bucketPrefix];
        return $bucket->objects($options);
    }

    private function storeInfo($object)
    {
        $jsonString = $object->downloadAsString();
        $firstBatch = new AnnotateFileResponse();
        $firstBatch->mergeFromJsonString($jsonString);

        foreach ($firstBatch->getResponses() as $response) {
            $annotation = $response->getFullTextAnnotation();
            $planText = $annotation->getText();
            $pieces = $this->explode($planText);

            foreach($pieces as $piece){
                $insertData = [
                    'info' => $piece,
                    'file_id' => 1,
                ];

                Estate::create($insertData);

            }
        }
    }

    /**
     * カッコで区切るよ
     */
    private function explode($text)
    {
        $pattern = '/(【|\(|\n|\[|「)第/u';
        $pieces =  preg_split($pattern ,$text);
        return $pieces;
    }

}