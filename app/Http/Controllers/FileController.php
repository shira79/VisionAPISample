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

    //todo ここら辺全部定数だから大文字にする
    /** 拡張子*/
    public $extention =  '.pdf';
    /** オリジナルのファイルの保存パス*/
    public $originalStorePath = '/original';
    /** 回転後ファイルの保存パス**/
    public $rotatedStorePath = '/rotated';
    /** GCSのpathのプレフィックス*/
    public $gcsPathPrefix = 'gs://';
    /** バケット名**/
    public $bucketName = 'realestate-info';

    public function __construct(){
        //repositoryでazureとgcpを切り替えたい
    }

    private function getBucket()
    {
        $storage = new StorageClient();
        return $storage->bucket($this->bucketName);
    }

    /**
     * ファイルアップロードのためのフォーム
     */
    public function form()
    {
        //ファイル名をユーザーが決めれるようにしたい
        //今は現unixのtimestampでやってる
        //あと何年何月のデータか
        return view('file.form');
    }

    /**
     * pfdファイルをもっといい感じの名前にする
     */
    public function upload(Request $request)
    {
        $this->rotate($request);
        $path = $request->session()->get("path");
        $bucket = $this->getBucket();
        $bucket->upload(fopen($path, 'r'));

        return redirect('/file/upload/check');
        // $this->read($fileData);
    }

    /**
     * 回転する処理
    */
    private function rotate($request)
    {
        $data = $request->all();
        $unixTime = time();
        $fileName = $unixTime.$this->extention;
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
        //strageに保存

        $request->session()->put("path", $rotatedFilePath);
        $request->session()->put("name", $fileName);
        $request->session()->put("time", $unixTime);
        //セッションに書き込む
    }

    public function uploadCheck(Request $request)
    {
        return view('file.check.upload');
    }

    public function uploadCancel(Request $request)
    {
        //まずローカルのファイルを消す。
        //GCSにアップしたファイルを削除する
        $this->forgetSession($request);
        return view('file.cancel.upload');
    }

    public function uploadResult(Request $request){
        if(empty($path = $request->session()->get("path"))){
            return redirect('/');
        }

        $headers = ['Content-disposition' => 'inline;'];
        return response()->file($path, $headers);
    }

    /**
     * pdfファイルをOCRにかけて、データをGCSに配置する
     */
    public function convert(Request $request)
    {
        $fileName = $request->session()->get("name");
        $fileTime = $request->session()->get("time");
        $path = $this->gcsPathPrefix . $this->bucketName . '/' . $fileName;
        $output = $this->gcsPathPrefix . $this->bucketName . '/' . $fileTime . '/';

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

        //todo ここでファイル情報をDBに挿入する。

        return redirect('/file/convert/check');
    }

    public function convertCheck()
    {
        return view('file.check.convert');
    }

    public function convertResult(Request $request)
    {
        $fileTime = $request->session()->get("time");
        $this->read($fileTime,'check');

    }

    public function convertCancel(Request $request)
    {
        //まずローカルのファイルを消す。
        //GCSにアップしたファイルを削除する。
        //別にわざわざ消さなくていいかな。
        $this->forgetSession($request);
        return view('file.cancel.convert');
    }

        public function insert(Request $request)
    {
        //todo 同じファイルが読み込まれないようにする
        $fileTime = $request->session()->get("time");
        $this->read($fileTime,'insert');
        return redirect('/list');
    }

    /**
     * GCSに置いてあるファイルを読み込む
     */
    public function read($fileTime, $dest)
    {
        $bucket =  $this->getBucket();
        $options = ['prefix' => $fileTime.'/'];
        $objects = $bucket->objects($options);

        foreach($objects as $object){
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

                    if($dest === 'check'){
                         dump($piece);
                    }elseif ($dest === 'insert'){
                        Estate::create($insertData);
                    }

                }
            }
        }

    }

    /**
     * カッコで区切るよ
     */
    private function explode($text)
    {
        $pattern = '/(【|\(|\n|\[|「|1)第/u';
        //最終的にここをDBにいれる
        $pieces =  preg_split($pattern ,$text);
        return $pieces;
    }

}