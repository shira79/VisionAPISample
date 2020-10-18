<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    

     /**
      * 回転する処理
      */
      public function rotate($request)
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
