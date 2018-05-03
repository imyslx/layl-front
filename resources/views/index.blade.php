<!doctype html>

@extends('layouts.app')

@section('css')
    @loadLocalCSS(/css/index.css)
    @loadLocalCSS(/css/index_mobile.css)
@endsection

@section('content')
<content-head>
  <div class="flex-center position-ref">
    <div class="content title m-t">
       [ LAYL ] <br /> Log All of Your Life.
    </div>
  </div>
</content-head>
<hr />
<main>
  <svcdesc class="clearfix">
    <h2 id=desc>
      -- あなたの「人生のすべて」を記録しましょう。
    </h2>
    <div id="svc-detail" class="flex-center">
      LAYLは非公開なタイプのライフログツールです。<br />
      オープンなコミュニティへは流せないようなモノまで含めて、<br />
      あなたの「人生のすべて」を記録します。<br />
    </div>
  </svcdesc>

  <svccontents class="clearfix">
    <div class="item clearfix">
      <p class=i-title>つぶやき</p>
      <p>その日の気分や天気。<br />良かったこと悪かったこと。<br />小さな日常を残しましょう。</p>
    </div>
  </svccontents>


</main>
@endsection

