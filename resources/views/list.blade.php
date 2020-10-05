<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css" integrity="sha384-GJzZqFGwb1QTTN6wy59ffF1BuGJpLSa9DkKMp0DgiMDm4iYMj70gZWKYbI706tWS" crossorigin="anonymous">

        <title>test</title>
    </head>
    <body>
        <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.6/umd/popper.min.js" integrity="sha384-wHAiFfRlMFy6i5SRaxvfOCifBUQy1xHdJ/yoi7FRNXMRBu5WHdZYu1hA6ZOblgut" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/js/bootstrap.min.js" integrity="sha384-B0UglyR+jN6CkvvICOB2joaf5I4l3gm9GU6Hc1og6Ls7i6U/mkkaduKaBhlAXv9k" crossorigin="anonymous"></script>  

        <div class="container">
            <h2>不動産情報をリスト表示する</h2>

            <form action="/list" method="GET">
                <div class="input-group">
                    <input name="text" type="text" class="form-control" placeholder="検索文字列" aria-label="Recipient's username with two button addons" aria-describedby="button-addon4">
                    <div class="input-group-append" id="button-addon4">
                        <button class="btn btn-outline-secondary" type="submit">Button</button>
                    </div>
                </div>
            </form>

            <div>
                @foreach($conditions as $key=>$val)
                    <span>「{{ $key }} => {{$val}}」</span>
                @endforeach
                の検索結果</div>
            <div>{{$estates->total()}}件中 {{$estates->firstItem()}}~{{$estates->lastItem()}}件目を表示</div>
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th scope="col">id</th>
                        <th scope="col">file_id</th>
                        <th scope="col">info</th>
                        <th scope="col">sent_at</th>
                    </tr>
                </thead>
                @foreach($estates as $estate)
                    <tbody>
                        <tr>
                            <th scope="row">{{$estate->id}}</th>
                            <td>{{$estate->file_id}}</td>
                            <td>{{$estate->info}}</td>
                            <td>{{$estate->sent_at}}</td>
                        </tr>
                    </tbody>
            @endforeach
            </table>
            {{ $estates->appends(request()->input())->links() }}
        </div>

    </body>
</html>
