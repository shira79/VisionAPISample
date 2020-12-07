@extends('layouts.app')
@section('content')

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
                        <th scope="col">status_name</th>
                    </tr>
                </thead>
                @foreach($estates as $estate)
                    <tbody>
                        <tr>
                            <th scope="row">{{$estate->id}}</th>
                            <td>{{$estate->file_id}}</td>
                            <td>{{$estate->info}}</td>
                            <td>{{$estate->status_name}}</td>
                        </tr>
                    </tbody>
            @endforeach
            </table>
            {{ $estates->appends(request()->input())->links() }}
        </div>

@endsection