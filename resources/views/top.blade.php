{{-- author ttakenaka --}}
@extends('layouts.application')

@section('title','caredaisy')

@section('style')
{{-- プレースホルダ --}}
<style>
    #top_background_image{
        width: 100%;
        height: 100%;
    }
    #top_caredaisy_image{
        height: 50%;
        background: url(../../../sozai/logo1.png) no-repeat center bottom;
    }
    .top_comment{
        width: 840px;
        margin: 60px auto auto 450px;
    }
</style>
@endsection

@section('contents')
{{-- プレースホルダ --}}
<!-- 画像 -->
<div id="top_background_image">
    <div id="top_caredaisy_image"></div>
    <div class="top_comment">
        @foreach($messageList as $message)
            <div>{!! $message !!}</div>
        @endforeach
    </div>
</div>
@endsection

@section('script')
{{-- プレースホルダ --}}
@endsection
