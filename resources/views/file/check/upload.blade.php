@extends('layouts.app')
@section('content')

<div>
    <div>
        以下のPDFを確認してください
        <div>
            <a href="/file/upload/result" target="_blank">PDF確認する</a>
        </div>
    </div>

    <div>
        このPDFをOCRにかけてもいいですか？？？？
        <div>
            <button>
                <a href="/file/convert">はい</a>
            </button>
            <button>
                <a href="/file/upload/cancel">いいえ</a>
            </button>
        </div>
    </div>
</div>

@endsection
