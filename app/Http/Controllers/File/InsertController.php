<?php

namespace App\Http\Controllers\File;

use Illuminate\Http\Request;
use App\File;
use App\Estate;
use App\Defs\DefStatus;

class InsertController extends FileBaseController
{

    public function insert(Request $request)
    {
        //todo 同じファイルが読み込まれないようにする
        //pdfのpageもいれたい
        $fileTime = $request->session()->get("time");

        $fileData = [
            'name' => $request->get('name'),
            'month' => $request->get('month'),
            'unix_time' => $fileTime,
        ];

        $file = File::create($fileData);

        $estates = $this->read($fileTime);
        foreach ($estates as $estate){
            $estateData = [
                'info' => $estate,
                'file_id' => $file->id,
                'status_code' => DefStatus::UNPROCESSED_STATUS_CODE
            ];
            Estate::create($estateData);
        }

        return redirect('/list');
    }


}