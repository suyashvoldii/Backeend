<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = [
        'name',
        'contact',
        'country',
        'state',
        'city',
        'pincode',
        'department',
        'branch',
        'address'
    ];
    protected $primaryKey = 'company_id';

    protected $dates =['deleted_at','created_at','updated_at'];

}
