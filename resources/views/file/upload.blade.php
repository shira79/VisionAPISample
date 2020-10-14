@extends('layouts.app')
@section('content')

    <div class="input-group">
    <form action="/file/rotate" method="POST" enctype="multipart/form-data">
    @csrf
        <div>
            <input type="file" name="file" id="file"  class="form-control">
        </div>
        <div>
            <input type="number" value=-1 name="angle" step="0.1">回転する角度(初期値：半時計周りに1度)
        </div>
        <div>
            <button type="submit">submit</button>
            </form>
        </div>
    </div>

@endsection

