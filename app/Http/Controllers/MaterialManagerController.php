<?php

namespace App\Http\Controllers;

use App\Models\MaterialAndGroup;
use App\Models\MaterialGroup;
use App\Models\MaterialSku;
use App\Models\MGroupAndM;
use DB;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Psy\Exception\ThrowUpException;


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

        $body = \Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ], [
            'name.required' => 'name 字段是必填的。',
            'name.string'   => 'name 字段必须是字符串。',
            'name.max'      => 'name 字段最大长度是 255。',
        ]);
        if ($body->fails()) {
            return response()->json($body->errors());
        }

        $data = $request->all();
        $rs   = MaterialGroup::create($data);
        return response()->json($rs);
    }


    /**
     * 新增SKU
     */
    public function skuCreate(Request $request)
    {
        $body = \Validator::make($request->all(), [
            "groupId" => "integer",
            'name'    => 'required|string|max:255',
            'amount'  => 'required|integer',
        ], [
            'name.required'   => 'name 字段是必填的。',
            'name.string'     => 'name 字段必须是字符串。',
            'name.max'        => 'name 字段最大长度是 255。',
            'amount.required' => 'amount 字段是必填的。',
            'amount.integer'  => 'amount 字段必须是整数。',
            'groupId:integer' => 'groupId 字段必须是整数。',
        ]);
        if ($body->fails()) {
            return response()->json($body->errors());
        }

        $data = $request->all();

        if (!$request->input('groupId')) {
            $request->merge(['groupId' => 0]);
        }

        $rs  = MaterialSku::create($request->all());
        $rs2 = MaterialAndGroup::create([
            'groupId' => $request->input('groupId'),
            'skuId'   => $rs->id,
            'amount'  => $rs->amount,
        ]);

        $rs3 = [$rs, $rs2];

        return response()->json($rs3);
    }



    /**
     * 新增群組與物料關聯
     */
    public function groupAndSkuCreate(Request $request)
    {

        $body = \Validator::make($request->all(), [
            'groupId' => 'integer',
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
            'groupId.integer' => 'groupId 字段必须是整数。',
            'skuId.required'  => 'skuId 字段是必填的。',
            'skuId.integer'   => 'skuId 字段必须是整数。',
            'skuId.exists'    => '指定的 skuId 不存在。',
            'amount.required' => 'amount 字段是必填的。',
        ]);
        if ($body->fails()) {
            return response()->json($body->errors());
        }


        if (!$request->input('groupId')) {
            $request->merge(['groupId' => 0]);
            MaterialAndGroup::create($request->all());
        } else {
            MaterialGroup::query()
                    ?->find($request->input('groupId'))
                    ?->skus()
                    ?->attach($request->input('skuId'), [
                    'amount'     => $request->input('amount'),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
        }

        // dump($request->all());

        return response()->json('ok');
    }


    // 轉移物料與群組關聯
    public function groupAndSkuTransfer(Request $request)
    {

        $body = \Validator::make($request->all(), [
            // 物料與群組id
            'id'      => 'required|integer|exists:materialAndGroup,id',
            // 轉移sku id
            'skuId'   => 'required|integer|exists:materialSku,id',
            // 轉移去的group id
            'groupId' => 'required|integer',
            'amount'  => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    if ($value < 1) {
                        $fail($attribute . '轉移數量必須大於等於1');
                    }
                }
            ],
        ], [
            'id.required'      => 'id 字段是必填的。',
            'id.integer'       => 'id 字段必须是整数。',
            'id.exists'        => '指定的 id 不存在。',
            'skuId.required'   => 'skuId 字段是必填的。',
            'skuId.integer'    => 'skuId 字段必须是整数。',
            'skuId.exists'     => '指定的 skuId 不存在。',
            'groupId.required' => 'groupId 字段是必填的。',
            'groupId.integer'  => 'groupId 字段必须是整数。',
            'amount.required'  => 'amount 字段是必填的。',
        ]);
        if ($body->fails()) {
            return response()->json($body->errors());
        }


        DB::transaction(function () use ($request): void {

            // 轉移物料與群組關聯
            $findRs = MaterialAndGroup::query()->find($request->input('id'));

            if ($findRs->amount < $request->input('amount')) {
                throw new \Exception('轉移數量不足');
            }


            $findRs->update([
                'skuId'  => $request->input('skuId'),
                'amount' => DB::raw('amount - ' . $request->input('amount')),
            ]);

            // 判斷轉移去的群組&& sku 是否存在
            $transferRs = MaterialAndGroup::query()
                ->where('groupId', $request->input('groupId'))
                ->where('skuId', $request->input('skuId'))
                ->first();

            if ($transferRs) {
                // 更新物料與群組關聯
                $transferRs->update([
                    'groupId' => $request->input('groupId'),
                    'skuId'   => $request->input('skuId'),
                    'amount'  => DB::raw('amount + ' . $request->input('amount')),
                ]);
            } else {
                // 新增物料與群組關聯
                MaterialAndGroup::query()->create([
                    'groupId' => $request->input('groupId'),
                    'skuId'   => $request->input('skuId'),
                    'amount'  => $request->input('amount'),
                ]);
            }

        });

        return 'ok';
    }



    /**
     * 查詢列表(群組與物料)
     */
    public function groupAndSkuList(Request $request)
    {

        $body = \Validator::make($request->all(), [
            'parentId' => 'required|integer|exists:materialGroup,parentId',
        ], [
            'parentId.integer' => 'parentId 字段必须是整数。',
            'parentId.exists'  => '指定的 parentId 不存在。',
        ]);
        if ($body->fails()) {
            return response()->json($body->errors());
        }


        $rs = MaterialGroup::query()
            ->with([
                'children',
                'skus',
            ])
            ->where('parentId', $request->input('parentId'))
            ->get();

        // dump($rs);

        $rs2 = MaterialSku::query()
            ->with(['materialAndGroups'])
            ->whereHas('materialAndGroups', function (Builder $query) use ($request) {
                $query->where('amount', '>', 0);
                $query->where('groupId', '=', $request->input('parentId'));
            })->get();

        // dump($rs2->toArray());


        $rs3 = array_merge($rs->toArray(), $rs2->toArray());


        // Log::info("測試用", ["rs" => $rs]);
        return response()->json($rs3);
    }



    /**
     * 測試用
     */
    public function test001(Request $request)
    {

        $rs = MaterialSku::query()
            ->whereRaw('materialSku.amount != coalesce((select sum(amount) from materialAndGroup where skuId = materialSku.id), 0)')
            ->get();

        // dump($rs->toRawSql());

        return $rs;

    }




}
