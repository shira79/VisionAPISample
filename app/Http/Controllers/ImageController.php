<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
// use TCPDF;
// use \setasign\Fpdi\Tcpdf\Fpdi;
use App\Http\Extentions\Myfpdi;

class ImageController extends Controller
{
    public $extention =  '.pdf';
    public $storePath = '/pdfs';

    public function upload()
    {
        return view('file.upload');
    }
    //画像を回転
    public function rotate(Request $request)
    {
        $data = $request->all();
        $fileName = time().$this->extention;
        //todo 時間とユーザーによって与えられた文字列によってで名前を作る

        $request->file->storeAs($this->storePath, $fileName);
        $filePath = storage_path() . '/app' . $this->storePath .'/' . $fileName;

        $this->rotatePdf($filePath,$data['angle']);

    }

    private function rotatePdf($filePath,$angle)
    {

        $pdf = new Myfpdi();
        // ページを追加
        // $pageNum = $pdf->setSourceFile(base_path() .$basePdf);
        $pageNum = $pdf->setSourceFile($filePath);

        for($i = 1; $i < $pageNum+1; $i++){
            $importPage = $pdf->importPage($i);
            $pdf->addPage();
            $pdf->Rotate($angle);
            $pdf->useTemplate($importPage, 0, 0);
        }

        return $pdf->Output(null,'I');

    }

}