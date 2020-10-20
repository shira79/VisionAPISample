<?php

namespace App\Http\Controllers\File;

use Illuminate\Http\Request;
use App\Estate;

class InsertController extends FileBaseController
{

    public function insert(Request $request)
    {
        //todo 同じファイルが読み込まれないようにする
        $fileTime = $request->session()->get("time");
        $estates = $this->read($fileTime);

        foreach ($estates as $estate){
            $insertData = [
                'info' => $estate,
                'file_id' => 1,
            ];

            Estate::create($insertData);
        }

        return redirect('/list');
    }


}