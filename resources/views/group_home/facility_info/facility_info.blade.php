
@extends('layouts.application')

@section('title','事業所情報')

@section('style')
  <link rel='stylesheet' href="{{ mix('/css/group_home/facility_info/facility_info.css') }}">
  <link rel='stylesheet' href="{{ mix('/css/calendar.css') }}">
@endsection

@section('contents')
  <div>
    <div id="billing_menu">
      
      <div id="corporate_list">
        <!--リスト挿入箇所-->
      </div>
    </div>
    <div id="facility_info_contents">
      {{-- カテゴリーサブタブ --}}
      <div id="tm_sub_tab">
        <a class="tm_subtab_btn tm_subtab_inactive" data-contents-id="tm_contents_corporation" dusk="corporation-button" id="tm_tabs_corporation">法人</a>
        <a class="tm_subtab_btn tm_subtab_inactive" data-contents-id="tm_contents_facility" dusk="institution-button" id="tm_tabs_facility">施設</a>
        <a class="tm_subtab_btn tm_subtab_inactive" data-contents-id="tm_contents_office" dusk="facility-button" id="tm_tabs_office">事業所</a>
        <a class="tm_subtab_btn tm_subtab_inactive" data-contents-id="tm_contents_service_type" dusk="service-type-button" id="tm_tabs_service_type">サービス種別</a>
        <a class="tm_subtab_btn tm_subtab_inactive" data-contents-id="tm_contents_addition_status" dusk="addition-status-button" id="tm_tabs_addition_status">加算状況</a>
        <a class="tm_subtab_btn tm_subtab_inactive" data-contents-id="tm_contents_uninsured_service" dusk="uninsured-button" id="tm_tabs_uninsured_service">保険外費用</a>
	{{-- <a class="tm_subtab_btn tm_subtab_inactive" data-contents-id="tm_contents_living_room">居室</a> --}}
      </div>

      <div id="tm_contents">
        <div class="sub_tab_contents_left">
          @component('components.group_home.facility_info.corporation')@endcomponent
          @component('components.group_home.facility_info.facility')@endcomponent
          @component('components.group_home.facility_info.office')@endcomponent
          @component('components.group_home.facility_info.service_type')@endcomponent
          @component('components.group_home.facility_info.addition_status')@endcomponent
          @component('components.group_home.facility_info.uninsured_service')@endcomponent
	  {{-- @component('components.group_home.facility_info.living_room')@endcomponent --}}
        </div>
      </div>
    </div>
  </div>
@endsection

@section('script')
  {{-- eikeda --}}
  <script src="https://code.jquery.com/jquery-3.5.0.min.js"></script>
  {{-- eikeda --}}

  <script type="module" src="{{ mix('/js/group_home/facility_info/facility_info.js') }}" defer></script>
  <script src="/js/jquery-ui.min.js"></script>
  <script src="/js/datepicker-ja.js"></script>
@endsection
