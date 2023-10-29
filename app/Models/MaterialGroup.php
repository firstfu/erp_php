<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


/**
 * @ Author: firstfu
 * @ Create Time: 2023-10-29 01:47:30
 * @ Description: 物料群組表
 */


class MaterialGroup extends Model
{
    use HasFactory;


    protected $table = 'materialGroup';

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];


    protected $fillable = [
        'name',
        'parentId',
    ];



    public function skus()
    {
        return $this->belongsToMany(MaterialSku::class, 'materialAndGroup', 'groupId', 'skuId');
    }

    // 父群組
    public function parent()
    {
        return $this->belongsTo(self::class, 'parentId');
    }


    // 子群組
    public function children()
    {
        return $this->hasMany(self::class, 'parentId');
    }



    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }






}
