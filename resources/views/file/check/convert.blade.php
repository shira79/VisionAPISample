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
            formにデータを入れてくださいね。
            <div>
                <div class="input-group">
                    <form action="/file/insert" method="POST">
                        @csrf
                        <div>
                            <input type="month" name="month">何年何月のデータですか
                        </div>
                        <div>
                            <input type="text" name="name" placeholder="ファイルの識別名">
                        </div>

                        <div>

                            <div>
                                <button type="submit">submit</button>
                            </div>
                            <button>
                                <a href="/file/convert/cancel">いいえ</a>
                            </button>
                        </div>
                    </form>
                </div>
            </div>



        </div>
    </div>

@endsection
