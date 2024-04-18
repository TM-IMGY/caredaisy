@extends('layouts.application')
@section('title', 'サービスコード選択')

@section('script')
@endsection

@section('content')

<style>
  .pagination{
      display: inline-block;
      }
</style>

<div class="col-sm-12" style="text-align:center;">

  <div class="form-inline row" style="padding-left:0px;">
    <!--↓↓ 検索フォーム ↓↓-->
    <div class="col-md-10">
      <form action="{{ route('claim_users.index') }}">
        @csrf
        <div class="form-group">
          <input type="text" name="name" value="" class="form-control" placeholder="事業者名"><!--テーブル名不明-->
          <input type="text" name="insurer_no" value="{{ $request->insurer_no }}" class="form-control" placeholder="保険者番号">
          <input type="text" name="insured_no" value="{{ $request->insured_no }}" class="form-control" placeholder="被保険者番号">
          <input type="text" name="name" value="" class="form-control" placeholder="氏名"><!--利用者姓名-->
          <input type="submit" value="検索" class="btn btn-primary">
          <input type="reset" value="クリア" class="btn btn-primary">
          <!--↓↓ ページネーション ↓↓-->
          <div class="pagination">
            {{ $claim_userss->links() }}
          </div>
        <!--↑↑ ページネーション ↑↑-->
        </div>
      </form>
    </div>
    </div>
  </div>

  <table class="table table-condensed table-striped table-hover">
    <thead>
      <tr>
        <th>利用者名</th>
        <th>性別</th>
        <th>保険者番号</th>
        <th>被保険者番号</th>
        <th>要介護度</th>
        <th>国保連請求額</th>
        <th>利用者自己負担額</th>
        <th>実費</th>
        <th>単位数合計</th>
        <th>合計</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      　@foreach($claim_userss as $claim_users)
      <tr>
        <td>{{$claim_users->facility_user_id}}</td>
        <td>{{$claim_users->gender}}</td>
        <td>{{$claim_users->insurer_no}}</td>
        <td>{{$claim_users->insured_no}}</td>
        <td>{{$claim_users->insured_no}}</td><!--要介護度--><!--テーブル名不明-->
        <td>{{$claim_users->insured_no}}</td><!--国保連請求額-->
        <td>{{$claim_users->insured_no}}</td><!--利用者自己負担額-->
        <td>{{$claim_users->insured_no}}</td><!--実費-->
        <td>{{$claim_users->insured_no}}</td><!--単位数合計-->
        <td>{{$claim_users->insured_no}}</td><!--合計-->
        <td size="10">
          <div style="display:flex;">
	 </div>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
</div>


@endsection
