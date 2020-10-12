<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use TCPDF;
use \setasign\Fpdi\Tcpdf\Fpdi;

class ImageController extends Controller
{

    public function __construct()
   {
   }

    //画像をアップ
    public function upload()
    {
    }

    //画像を変換
    public function transform()
    {
        $this->getPdf();
    }

    //変換した画像をダウンロード
    public function download()
    {
    }


    private function getPdf()
    {
        $basePdf = '/File/doc2.pdf';

        $fpdi = new Fpdi();
        // ページを追加
        $pageNum = $fpdi->setSourceFile(base_path() .$basePdf);

        for($i = 1; $i < $pageNum+1; $i++){
            $importPage = $fpdi->importPage($i);
            $fpdi->addPage();
            $fpdi->useTemplate($importPage, 0, 0);
        }

        $fpdi->Output(sprintf("test_%s.pdf", time()));

    }

    public function downloadPdf()
   {
   }

}