@extends('layouts.app')
@section('content')

    <div>
        <div>
            以下のOCR結果を確認してください
            <div>
                <a href="/file/convert/result" target="_blank">OCR結果を確認する</a>
                <a href="/file/upload/result" target="_blank">PDFと比較してくださいね</a>
            </div>
        </div>

        <div>
            OCR結果をデータベースに入れてもいいですか？？？？
            <div>
                <button>
                    <a href="/file/insert">はい</a>
                </button>
                <button>
                    <a href="/file/convert/cancel">いいえ</a>
                </button>
            </div>
        </div>
    </div>

@endsection
