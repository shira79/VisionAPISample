<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

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
    public function read(){

        $bucketName =  'realestate-info';
        $prefix =  'outputs2/';
        $storage = new StorageClient();
        $bucket = $storage->bucket($bucketName);
        $options = ['prefix' => $prefix];
        $objects = $bucket->objects($options);

        # list objects with the given prefix.
        // print('Output files:' . PHP_EOL);
        // foreach ($objects as $object) {
            // print($object->name() . PHP_EOL);
        // }

        foreach($objects as $object){

            $jsonString = $object->downloadAsString();
            $batch = new AnnotateFileResponse();
            $batch->mergeFromJsonString($jsonString);

            foreach ($batch->getResponses() as $response) {
                $annotation = $response->getFullTextAnnotation();
                dump($annotation->getText());
            }
        }


    }
}