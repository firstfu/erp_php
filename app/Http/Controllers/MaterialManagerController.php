<?php

namespace App\Http\Controllers;

use App\Models\MaterialGroup;
use App\Models\MaterialSku;
use App\Models\MGroupAndM;
use DB;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


/**
 * @ Author: firstfu
 * @ Create Time: 2023-10-29 02:34:43
 * @ Description: 物料管理
 */


class MaterialManagerController extends Controller
{

    /**
     * 新增物料群組
     */
    public function groupCreate(Request $request)
    {
        $data = $request->all();
        $rs   = MaterialGroup::create($data);
        return response()->json($rs);
    }


    /**
     * 新增SKU
     */
    public function skuCreate(Request $request)
    {
        $data = $request->all();
        $rs   = MaterialSku::create($data);
        return response()->json($rs);
    }



    /**
     * 新增群組與物料關聯
     */
    public function groupAndSkuCreate(Request $request)
    {

        $body = \Validator::make($request->all(), [
            'groupId' => 'required|integer|exists:materialGroup,id',
            'skuId'   => 'required|integer|exists:materialSku,id',
            'amount'  => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    // dump($value);
                    if ($value <= 1) {
                        $fail('The ' . $attribute . ' must be greater than 1.');
                    }
                }
            ],
        ], [
            'groupId.required' => 'groupId 字段是必填的。',
            'groupId.integer'  => 'groupId 字段必须是整数。',
            'groupId.exists'   => '指定的 groupId 不存在。',
            'skuId.required'   => 'skuId 字段是必填的。',
            'skuId.integer'    => 'skuId 字段必须是整数。',
            'skuId.exists'     => '指定的 skuId 不存在。',
            'amount.required'  => 'amount 字段是必填的。',
        ]);
        if ($body->fails()) {
            return response()->json($body->errors());
        }

        // 建立關聯
        $rs = MaterialGroup::query()
            ->find($request->groupId)
            ->skus()
            ->attach($request->input('skuId'), [
                'amount'     => $request->input('amount'),
                'created_at' => now(),
                'updated_at' => now()
            ]);

        // dump($request->all());

        return response()->json('ok');
    }





    /**
     * 查詢列表(群組與物料)
     */
    public function groupAndSkuList(Request $request)
    {

        // $rs = MaterialGroup::query()
        //     ->with(['children', 'skus', 'children.skus'])
        //     ->where('parentId', 0)
        //     ->union(
        //         // DB::table('materialSku')
        //         //     ->select('id', 'name', DB::raw('0 AS parentId'), 'created_at', 'updated_at')

        //         MaterialSku::query()
        //             ->select('id', 'name', DB::raw('0 AS parentId'), 'created_at', 'updated_at')
        //     )
        //     ->get();

        // $rs2 = DB::table('materialSku')
        //     ->select('id', 'name', DB::raw('0 AS parentId'), 'created_at', 'updated_at')
        //     ->get();



        // ============================


        $groupArr = MaterialGroup::query()
            ->with([
                'children',
                // 'skus',
                // 'children.skus'
            ])
            ->where('parentId', 0)
            ->get();


        // $skuArr = DB::table('materialSku')
        //     ->select('id', 'name', DB::raw('0 AS parentId'), 'created_at', 'updated_at')
        //     ->get();


        // $rs = array_merge($groupArr->toArray(), $skuArr->toArray());


        $rs = $groupArr;

        // Log::info("測試用", ["rs" => $rs]);
        return response()->json($rs);
    }



    /**
     * 測試用
     */
    public function test001(Request $request)
    {


        // $rs = MaterialSku::query()->where(function (Builder $query) {

        //     // $query->selectSub(function ($query) {
        //     //     $query->from('mGroupAndM')
        //     //         ->where('skuId', 3)
        //     //         ->sum('amount');
        //     // }, 'amount')
        //     //     ->where('id', 3);


        //     $query->from('mGroupAndM')
        //         ->where('skuId', 3)
        //         ->sum('amount');

        //     dump($query->toRawSql());
        //     // $rs = MGroupAndM::query()->where('skuId', 3)->sum('amount');
        //     // dump($query->id);
        //     // $query->whereIn('id', [3, 4, 5]);

        // })->get();



        $rs = MaterialSku::query()->where(function (Builder $query) {
            $query->select(DB::raw("SUM(amount)"))
                ->from('mGroupAndM')
                ->whereColumn('skuId', 'materialSku.id')
                ->limit(1);
        }, '<', DB::raw('id'))
            ->get();


        return $rs;

    }




}
