<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class AccountGoogle extends Model {
    protected $table = 'account_google';
    protected $primaryKey = 'id_user_google';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];
}