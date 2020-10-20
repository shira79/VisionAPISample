<?php

namespace App\Http\Controllers\File;

use Illuminate\Http\Request;
use setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException;
use setasign\Fpdi\Tcpdf\Fpdi;

class UploadController extends FileBaseController
{

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
        //todo storage下のディレクトリがなかったら作る
//        $this->initDir();

        $this->rotate($request);
        $path = $request->session()->get("path");
        $bucket = $this->getBucket();
        $bucket->upload(fopen($path, 'r'));

        return redirect('/file/upload/check');
    }

    /**
     * 回転する処理
     */
    private function rotate($request)
    {
        $unixTime = time();
        $fileName = $unixTime.self::PDF_EXTENSION;

        $request->file->storeAs(self::ORIGINAL_FILE_DIR_NAME, $fileName);
        //storageに保存

        $pdf = new Fpdi();

        try {
            $pageNum = $pdf->setSourceFile($this->getOriginalFilePath($fileName));
        } catch (CrossReferenceException $e) {
            //エラーを吐いたら圧縮処理をかける
            $this->uncompress($fileName);
            $pageNum = $pdf->setSourceFile($this->getUncompressedFilePath($fileName));
        }

        for($i = 1; $i < $pageNum+1; $i++){
            $importPage = $pdf->importPage($i);
            $pdf->addPage();
            $pdf->Rotate($request->get('angle'));
            $pdf->useTemplate($importPage, 0, 0);
        }

        $pdf->Output($this->getRotatedFilePath($fileName),'F');
        //storageに保存

        $request->session()->put("path", $this->getRotatedFilePath($fileName));
        $request->session()->put("name", $fileName);
        $request->session()->put("time", $unixTime);
        //セッションに書き込む
    }

    /**
     * @param $originalFilePath
     * @param $fileName
     * @return string
     */
    private function uncompress($fileName)
    {
        exec('qpdf --force-version=1.4 '. $this->getOriginalFilePath($fileName) .' ' . $this->getUncompressedFilePath($fileName));
    }

    public function check(Request $request)
    {
        return view('file.check.upload');
    }

    public function cancel(Request $request)
    {
        //まずローカルのファイルを消す。
        //GCSにアップしたファイルを削除する
        $this->forgetSession($request);
        return view('file.cancel.upload');
    }

    public function result(Request $request)
    {
        if(empty($path = $request->session()->get("path"))){
            return redirect('/');
        }

        $headers = ['Content-disposition' => 'inline;'];
        return response()->file($path, $headers);
    }

}