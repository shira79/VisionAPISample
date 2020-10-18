<?php

namespace App\Http\Controllers\File;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use \setasign\Fpdi\Tcpdf\Fpdi;

//ここらの処理はコントローラーにいっぱい書くんじゃなくて、それ専用のクラスを作る

class RotateController extends Controller
{
    /** 拡張子*/
    public $extention =  '.pdf';
    /** オリジナルのファイルの保存パス*/
    public $originalStorePath = '/original';
    /** 回転後ファイルの保存パス**/
    public $rotatedStorePath = '/rotated';

    /**
     * 回転するファイルと角度のフォーム
     */
    public function edit()
    {
        return view('file.edit');
    }

     /**
      * 回転する処理
      */
    public function rotate(Request $request)
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

    }

}