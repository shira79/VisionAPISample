<?php

namespace App\Http\Controllers\File;


use Google\Cloud\Vision\V1\AsyncAnnotateFileRequest;
use Google\Cloud\Vision\V1\Feature;
use Google\Cloud\Vision\V1\Feature\Type;
use Google\Cloud\Vision\V1\GcsDestination;
use Google\Cloud\Vision\V1\GcsSource;
use Google\Cloud\Vision\V1\ImageAnnotatorClient;
use Google\Cloud\Vision\V1\InputConfig;
use Google\Cloud\Vision\V1\OutputConfig;
use Illuminate\Http\Request;

class ConvertController extends FileBaseController
{

    /**
     * pdfファイルをOCRにかけて、データをGCSに配置する
     */
    public function convert(Request $request)
    {
        $fileName = $request->session()->get("name");
        $fileTime = $request->session()->get("time");
        $path = self::GCS_PATH_PREFIX . self::BUCKET_NAME . '/' . $fileName;
        $output = self::GCS_PATH_PREFIX . self::BUCKET_NAME . '/' . $fileTime . '/';

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

        return redirect('/file/convert/check');
    }

    public function check()
    {
        return view('file.check.convert');
    }

    public function result(Request $request)
    {
        if(empty($time = $request->session()->get("time"))){
            return redirect('/');
        }
        $estates = $this->read($time);
        dump($estates);
    }

    public function cancel(Request $request)
    {
        //まずローカルのファイルを消す。
        //GCSにアップしたファイルを削除する。
        //別にわざわざ消さなくていいかな。
        $this->forgetSession($request);
        return view('file.cancel.convert');
    }

}