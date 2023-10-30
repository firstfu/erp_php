<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;



/**
 * @ Author: firstfu
 * @ Create Time: 2023-10-29 01:47:30
 * @ Description: 物料Sku表
 */


class MaterialSku extends Model
{
    use HasFactory;

    protected $table = 'materialSku';


    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];


    protected $fillable = [
        'name',
        'amount',
        'json',
        'skuAttr',
        'spuId',
    ];


    public function groups()
    {
        return $this->belongsToMany(MaterialGroup::class, 'materialAndGroup', 'skuId', 'groupId');
    }

    public function materialAndGroups()
    {
        return $this->hasMany(MaterialAndGroup::class, 'skuId');
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }

}
