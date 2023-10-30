<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


/**
 * @ Author: firstfu
 * @ Create Time: 2023-10-29 01:47:30
 * @ Description: 物料群組與物料表
 */


class MaterialAndGroup extends Model
{
    use HasFactory;


    protected $table = 'materialAndGroup';


    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];


    protected $fillable = [
        'groupId',
        'skuId',
        'amount',
    ];


    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }

}
