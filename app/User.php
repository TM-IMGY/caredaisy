<?php

namespace App;

use DB;
use Exception;
use Log;

use App\ApiLoginLimitTrait;

use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Laravel\Passport\HasApiTokens;


class User extends Authenticatable
{
    use HasApiTokens, Notifiable, ApiLoginLimitTrait;

    protected $table = 'i_accounts';
    protected $primaryKey = 'account_id';

    protected $maxAttempts = 3;     // ログイン試行回数（回）
    protected $decayMinutes = 10;   // ログインロックタイム（分）

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'employee_number','password','account_name','auth_id','staff_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'account_id','password','remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function findAndValidateForPassport($username, $password)
    {
        // 呼び出している各ロジックはApp\ApiLoginLimitTraitに記載しています
        // ここにベタで書くにはあまりに汚いので。

        // まずロックかかってるか検査する
        if($this->tooManyAttempts($username, $this->maxAttempts)){
            // かかってるなら突っ返す
            return null;
        }
        // かかってないならログイン認証してみる
        if(Auth::attempt(['employee_number' => $username, 'password' => $password])) {
            // OKなら問題なし
            return Auth::user();
        }
        // NGなら試行回数をインクリメント
        $this->hit($username, $this->decayMinutes * 60);
    }

    public function staff()
    {
        return $this->belongsTo('App\Models\Staff');
    }
}
