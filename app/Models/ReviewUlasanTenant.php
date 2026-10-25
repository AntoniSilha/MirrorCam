<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ReviewUlasanTenant extends Model {
    protected $table = 'review_ulasan_tenants';
    protected $primaryKey = 'id_review';
    protected $guarded = [];
}