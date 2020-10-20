<?php

namespace App\Http\Controllers\File;


use App\Http\Controllers\Controller;
use Google\Cloud\Storage\StorageClient;
use Google\Cloud\Vision\V1\AnnotateFileResponse;

class FileBaseController extends Controller
{
    /** 拡張子*/
    const PDF_EXTENSION =  '.pdf';
    /** オリジナルのファイルの保存パス*/
    const ORIGINAL_FILE_DIR_NAME = '/original';
    /** 回転後ファイルの保存パス**/
    const ROTATED_FILE_DIR_NAME = '/rotated';
    /** 圧縮解除ファイルの保存パス**/
    const UNCPMPRESSED_FILE_DIR_NAME = '/uncompressed';
    /** GCSのpathのプレフィックス*/
    const GCS_PATH_PREFIX  = 'gs://';
    /** バケット名**/ //todo .envで管理する
    const BUCKET_NAME  = 'realestate-info';

    protected function getBucket()
    {
        $storage = new StorageClient();
        return $storage->bucket(self::BUCKET_NAME);
    }

    protected function getFilePath($fileDirName,$fileName)
    {
        return storage_path() . '/app' . $fileDirName .'/' . $fileName;
    }

    protected function getUncompressedFilePath($fileName)
    {
        return $this->getFilePath(self::UNCPMPRESSED_FILE_DIR_NAME,$fileName);
    }

    protected function getRotatedFilePath($fileName)
    {
        return $this->getFilePath(self::ROTATED_FILE_DIR_NAME,$fileName);
    }

    protected function getOriginalFilePath($fileName)
    {
        return $this->getFilePath(self::ORIGINAL_FILE_DIR_NAME,$fileName);
    }

    protected function forgetSession($request)
    {
        $request->session()->forget("path");
        $request->session()->forget("name");
        $request->session()->forget("time");
    }


    /**
     * GCSに置いてあるファイルを読み込む
     */
    //todo リファクタリングする
    protected function read($fileTime)
    {
        $bucket =  $this->getBucket();
        $options = ['prefix' => $fileTime.'/'];
        $objects = $bucket->objects($options);

        $estates = [];
        foreach($objects as $object){
            $jsonString = $object->downloadAsString();
            $firstBatch = new AnnotateFileResponse();
            $firstBatch->mergeFromJsonString($jsonString);

            foreach ($firstBatch->getResponses() as $response) {
                $annotation = $response->getFullTextAnnotation();
                $planText = $annotation->getText();
                $pieces = $this->explode($planText);

                foreach($pieces as $piece){
                    $estates[] = $piece;
                }
            }
        }

        return $estates;

    }

    /**
     * カッコで区切るよ
     */
    //todo これvisionAPIのblockで分割すればこんなことする必要ない。
    private function explode($text)
    {
        $pattern = '/(【|\(|\n|\[|「|1)第/u';
        //最終的にここをDBにいれる
        $pieces =  preg_split($pattern ,$text);
        return $pieces;
    }

}
