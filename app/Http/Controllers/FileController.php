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
use \setasign\Fpdi\Tcpdf\Fpdi;



class FileController extends Controller
{

    /** 拡張子*/
    public $extention =  '.pdf';
    /** オリジナルのファイルの保存パス*/
    public $originalStorePath = '/original';
    /** 回転後ファイルの保存パス**/
    public $rotatedStorePath = '/rotated';

    /** バケット名**/
    public $bucketName = 'realestate-info';

    public $bucketPrefix =  'rotateOutput4/';

    public function __construct(){
        //repositoryでazureとgcpを切り替えたい
    }

    /**
     * バケットの中身を一覧で見るみたいな機能 + ファイル
     */
    public function form()
    {
        return view('file.form');
    }

    /**
     * pfdファイルをアップロード
     */
    public function upload(Request $request)
    {
        $filePath =  $this->rotate($request);
        $bucket = $this->getBucket();
        $bucket->upload(fopen($filePath, 'r'));
        return 'ファイルをGCSに送信しました';
    }

    /**
     * 回転する処理
    */
    private function rotate($request)
    {
        $data = $request->all();
        $fileName = time().$this->extention;
        //todo 時間とユーザーによって与えられた文字列によってで名前を作る

        $request->file->storeAs($this->originalStorePath, $fileName);
        //オリジナルのファイルを保存

        $originalFilePath = storage_path() . '/app' . $this->originalStorePath .'/' . $fileName;
        //オリジナルのファイルを保存
        //ファイルの回転が終わったら削除してもいいかも。

        $pdf = new Fpdi();
        $pageNum = $pdf->setSourceFile($originalFilePath);

        for($i = 1; $i < $pageNum+1; $i++){
            $importPage = $pdf->importPage($i);
            $pdf->addPage();
            $pdf->Rotate($data['angle']);
            $pdf->useTemplate($importPage, 0, 0);
        }

        $rotatedFilePath = storage_path() . '/app' . $this->rotatedStorePath .'/' . $fileName;
        $pdf->Output($rotatedFilePath,'F');
        //Fでディレクトリに保存。その後gcsアップロードにつなげる。
        return $rotatedFilePath;
    }

    /**
     * pdfファイルをOCRにかけて、データをGCSに配置する
     */
    public function convert()
    {
        $path = 'gs://realestate-info//doc7.pdf';
        $output = 'gs://realestate-info/rotateOutput4/';
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
    public function read()
    {
        $objects = $this->getObjects();

        foreach($objects as $object){
            $this->storeInfo($object);
            // $this->getPlaneText($object);
        }

    }

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

        // dump($this->bucketPrefix);
        foreach ($firstBatch->getResponses() as $response) {
            $annotation = $response->getFullTextAnnotation();
            $planText = $annotation->getText();
            $pieces = $this->explode($planText);

            foreach($pieces as $piece){
                $insertData = [
                    'info' => $piece,
                    'file_id' => 1,
                ];

                // dump($piece);
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