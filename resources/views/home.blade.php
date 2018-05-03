@extends('layouts.app')

@section('css')
    @loadLocalCSS(/css/home.css)
@endsection


@section('content')
<div class="container">

@if($post_succeed == true)
    <div class="row justify-content-center margin-bottom">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">投稿に成功しました！</div>
            </div>
        </div>
    </div>

@endif
    <div class="row justify-content-center margin-bottom">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header"> {{ $username }} さんの Dashboard</div>
                <div class="card-body">
                    <h5 class="card-title">ようこそ！</h5>
                    <p class="card-text">今日もいっぱい Log しましょう！</p>
                    <div class="row">
                        <div class="col-6">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item list-group-item-head">本日の投稿数</li>
                                <li class="list-group-item list-group-item-head">本日の単語数</li>
                                <li class="list-group-item list-group-item-head">今日の気分</li>
                            </ul>
                        </div>
                        <div class="col-6">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item"> {{ $todays_posts }} 件 </li>
                                <li class="list-group-item"> XXXXX 語 </li>
                                <li class="list-group-item"> 普通 </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row justify-content-center margin-bottom">
        <div class="col-md-10">
        <div class="card">
            <div class="card-header"> つぶやき（短文投稿） </div>
                <div class="card-body">
                    <form method="POST" action="/log">
                        {{ csrf_field() }}
                        <div class="form-group">
                            <div class="row margin-bottom">
                                <div class="col-12">
                                    <input type="text" name="short_text" class="form-control">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-4"></div>
                                <div class="col-4">
                                    <button type="submit" class="form-control btn btn-primary ">投稿</button>
                                </div>
                                <div class="col-4"></div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header"> 直近の投稿 </div>
                    <div class="card-body">
                        <h5 class="card-title">つぶやき（短文投稿）</h5>
                        <ul class="list-group list-group-flush">
                            @foreach($logs["shortTexts"] as $st)
                            <li class="list-group-item"> {{ $st["text"] }} <br>
                                <span class="date">(posted_at: {{ $st["created_at"] }}) </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
