@extends('layouts.application')

@section('title','ケアプラン')

@section('style')
  <link rel='stylesheet' href="{{ mix('/css/group_home/care_plan_info/care_plan_info.css') }}">
@endsection

@section('contents')
  <div>
    {{-- 利用者メニュー --}}
    <div id="facility_user_menu">
      <div id="fum_pulldown_list">
        {{-- 事業所プルダウン --}}
        <select class="fum_pulldown" id="facility_pulldown"></select>
      </div>
      {{-- 利用者テーブル --}}
      <table class="caredaisy_table" id="user_info_fu_table">
        <tbody id="user_info_fu_tbody"></tbody>
      </table>
    </div>

    <div id="user_info_contents">
      {{-- カテゴリータブ(サブ) --}}
      <div id="tm_sub_tab">
        <a class="tm_subtab_btn tm_subtab_inactive" data-contents-id="tm_contents_service_plan1" dusk="care-plan-1-button" id="tm_tabs_service_plan1">介護計画書1</a>
        <a class="tm_subtab_btn tm_subtab_inactive" data-contents-id="tm_contents_service_plan2" dusk="care-plan-2-button" id="tm_tabs_service_plan2">介護計画書2</a>
        <a class="tm_subtab_btn tm_subtab_inactive" data-contents-id="tm_contents_service_plan3" dusk="care-plan-3-button" id="tm_tabs_service_plan3">週間サービス計画表</a>
      </div>

      <div id="tm_contents">
        @component('components.group_home.care_plan_info.service_plan1')@endcomponent
        @component('components.group_home.care_plan_info.service_plan2')@endcomponent
        @component('components.group_home.care_plan_info.service_plan3')@endcomponent
      </div>
    </div>
  </div>
@endsection

@section('script')
  <script src="https://code.jquery.com/jquery-3.5.0.min.js"></script>
  <script type="module" src="{{ mix('/js/group_home/care_plan_info/care_plan_info.js') }}" defer></script>
@endsection
