@extends('layouts.application')

@section('title','スタッフ情報')

@section('style')
  <link rel='stylesheet' href="{{ mix('/css/group_home/user_info/user_info.css') }}">
  <link rel='stylesheet' href="{{ mix('/css/group_home/staff_info/staff_info.css') }}">
@endsection

@section('contents')
  <div>
    {{-- スタッフメニュー --}}
    <div id="facility_user_menu">
      <div id="fum_pulldown_list">
        {{-- 法人プルダウン --}}
        {{-- 施設プルダウン --}}
        {{-- 事業所プルダウン --}}
        <select class="fum_pulldown" id="facility_pulldown"></select>
      </div>
      {{-- スタッフテーブル --}}
      <table class="staff_list_table" id="staff_table">
        <tbody id="staff_tbody" class="staff_list_tbody"></tbody>
      </table>
    </div>

    <div id="user_info_contents">
      {{-- カテゴリータブ(サブ) --}}
      <div id="tm_sub_tab">
        <a class="tm_subtab_btn tm_subtab_inactive" data-contents-id="tm_contents_staff" dusk="staff-basic-button" id="tm_tabs_staff">基本情報</a>
        <a class="tm_subtab_btn tm_subtab_inactive" data-contents-id="tm_contents_auths" dusk="staff-auth-button" id="tm_tabs_auths">権限設定</a>
      </div>

      <div id="tm_contents">
        @component('components.group_home.staff_info.basic')@endcomponent
        @component('components.group_home.staff_info.auths')@endcomponent
      </div>
    </div>
  </div>
@endsection

@section('script')
  <script type="module" src="{{ mix('/js/group_home/staff_info/staff_info.js') }}" defer></script>
@endsection
