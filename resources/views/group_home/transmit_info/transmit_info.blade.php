@extends('layouts.application')

@section('title','伝送情報')

@section('style')
  <link rel='stylesheet' href="{{ mix('/css/group_home/user_info/user_info.css') }}">
  <link rel='stylesheet' href="{{ mix('/css/group_home/transmit_info/transmit_info.css') }}">
  <link rel='stylesheet' href="{{ mix('/css/calendar.css') }}">
@endsection

@section('contents')
  <div>
    <div id="transmit_menu">
      <div id="fum_pulldown_list">
        {{-- 事業所プルダウン --}}
        <select class="fum_pulldown" id="facility_pulldown"></select>
      </div>
      <div>事業所番号 :<span id="transmit_facility_number"></span></div>
    </div>

    <div id="transmit_contents">
      {{-- カテゴリータブ(サブ) --}}
      <div id="tm_sub_tab">
        <a class="tm_subtab_btn tm_subtab_inactive" data-contents-id="tm_contents_transmit" dusk="tm-contents-transmit-tab">伝送請求</a>
        <a class="tm_subtab_btn tm_subtab_inactive" data-contents-id="tm_contents_document" dusk="tm-contents-document-tab">通知文書</a>
      </div>

      <div id="tm_contents">
        @component('components.group_home.transmit_info.transmit')@endcomponent
        @component('components.group_home.transmit_info.document')@endcomponent
      </div>
    </div>
  </div>
@endsection

@section('script')
  <script type="module" src="{{ mix('/js/group_home/transmit_info/transmit_info.js') }}" defer></script>
  <script src="https://code.jquery.com/jquery-3.5.0.min.js"></script>
  <script src="/js/jquery-ui.min.js"></script>
  <script src="/js/datepicker-ja.js"></script>
@endsection
