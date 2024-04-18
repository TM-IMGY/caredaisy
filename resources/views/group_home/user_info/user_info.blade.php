@extends('layouts.application')

@section('title','利用者情報')

@section('style')
  <link rel='stylesheet' href="{{ mix('/css/group_home/user_info/user_info.css') }}">
  <link rel='stylesheet' href="{{ mix('/css/calendar.css') }}">
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
        <tbody id="user_info_fu_tbody" dusk="user_info_fu_table"></tbody>
      </table>
    </div>

    <div id="user_info_contents">
      {{-- カテゴリータブ(サブ) --}}
      <div id="tm_sub_tab">
        <a class="tm_subtab_btn tm_subtab_inactive" data-contents-id="tm_contents_basic" dusk="facility-user-basic-button" id="tm_tabs_basic">基本情報</a>
        <a class="tm_subtab_btn tm_subtab_inactive" data-contents-id="tm_contents_service" dusk="facility-user-service-button" id="tm_tabs_service">サービス</a>
        <a class="tm_subtab_btn tm_subtab_inactive" data-contents-id="tm_contents_approval" dusk="facility-user-care-button" id="tm_tabs_approval">認定情報</a>
        <a class="tm_subtab_btn tm_subtab_inactive" data-contents-id="tm_contents_independence" dusk="facility-user-independence-button" id="tm_tabs_independence">自立度</a>
        @can('readFacilityUser2')
        <a class="tm_subtab_btn tm_subtab_inactive" data-contents-id="tm_contents_public_expenditure" dusk="facility-user-public-expenditure-button" id="tm_tabs_expenditure">公費情報</a>
        <a class="tm_subtab_btn tm_subtab_inactive" data-contents-id="tm_contents_benefit" dusk="facility-user-benefit-button" id="tm_tabs_benefit">給付率</a>
        <a class="tm_subtab_btn tm_subtab_inactive" data-contents-id="tm_contents_billing_address" dusk="facility-user-billing-address-button" id="tm_tabs_billing_address">請求先情報</a>
        @endcan
        <a class="tm_subtab_btn tm_subtab_inactive medical_clinic_only" data-contents-id="tm_contents_injury_and_illness" dusk="facility-user-injury-and-illness-button" id="tm_tabs_injury_and_illness" style="visibility: hidden">傷病名</a>
        <a class="tm_subtab_btn tm_subtab_inactive medical_clinic_only" data-contents-id="tm_contents_basic_abstract" dusk="facility-user-basic-abstract-button" id="tm_tabs_basic_abstract" style="visibility: hidden">基本摘要</a>
        <a class="tm_subtab_btn tm_subtab_inactive medical_clinic_only" data-contents-id="tm_contents_burden_limit" dusk="facility-user-burden-limit-button" id="tm_tabs_burden_limit" style="visibility: hidden">負担限度額</a>
      </div>

      <div id="tm_contents">
        @component('components.group_home.user_info.basic')@endcomponent
        @component('components.group_home.user_info.service')@endcomponent
        @component('components.group_home.user_info.approval')@endcomponent
        @component('components.group_home.user_info.independence')@endcomponent
        @component('components.group_home.user_info.public_expenditure')@endcomponent
        @component('components.group_home.user_info.benefit')@endcomponent
        @component('components.group_home.user_info.billing_address')@endcomponent
        @component('components.group_home.user_info.injury_and_illness_name')@endcomponent
        @component('components.group_home.user_info.basic_abstract')@endcomponent
        @component('components.group_home.user_info.burden_limit')@endcomponent
      </div>
    </div>
  </div>
@endsection

@section('script')
  <script src="https://code.jquery.com/jquery-3.5.0.min.js"></script>
  <script type="module" src="{{ mix('/js/group_home/user_info/user_info.js') }}" defer></script>
  <script src="/js/jquery-ui.min.js"></script>
  <script src="/js/datepicker-ja.js"></script>
@endsection
