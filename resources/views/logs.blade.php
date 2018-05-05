@extends('layouts.sidebar')

@if($mode == "")
@section('logs')
            <div class="card">
                <div class="card-header"> About here. </div>
                <div class="card-body">
                    ここではあなたの投稿の履歴を確認できます。<br>
                    左のメニューから対象のコンテンツを選択して下さい。
                </div>
            </div>
@endsection
@elseif($mode == "shortText")
@section('logs')
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">つぶやき（短文投稿）</h5>
                    <ul class="list-group list-group-flush">
                        @foreach($logs["shortTexts"] as $st)
                        <li class="list-group-item"> {{ $st["text"] }} <br>
                            <span class="date">(posted_at: {{ $st["created_at"] }}) </li>
                        @endforeach
                    </ul>
                    @if($noMore)
                    <button class="form-control btn btn-secondary"> もうありません </button>
                    @else
                    <div class="form-group">
                        <form method="GET" action="/logs">
                            <input type="hidden" name="mode"     value="{{ $mode }}">
                            <input type="hidden" name="winSize"  value="{{ $winSize }}">
                            <input type="hidden" name="viewSize" value="{{ $nextSize }}">
                            <button type="submit" class="form-control btn btn-primary">もっと見る</button>
                        </form>
                    </div>
                    @endif
                </div>
            </div>
@endsection
@endif

@section('sidebar')
<sidebar>
    <div class="row">
        <div class="col logmenu">
            Logs Menu
        </div>
    </div>
    <div class="row">
        <div class="col">
            <ul class="list-group">
                <li class="list-group-item"><a href="/logs?mode=shortText"> <span class="triangle">&nbsp;</span> つぶやき </a></li>
                <li class="list-group-item"> <span class="triangle-gray">&nbsp;</span> 日記 </li>
            </ul>
        </div>
    </div>
</sidebar>
@endsection

@section('mainpage')
<main>
    <div class="row">
        <div class="col">
@yield('logs')
        </div>
    </div>
</main>

@endsection