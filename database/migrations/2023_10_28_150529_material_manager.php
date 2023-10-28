<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {


        /**
         * 物料群組表
         */
        Schema::create('materialGroup', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->comment('物料群組名稱');
            $table->integer('parentId')->default(0)->comment('父群組id');
            $table->timestamps();
        });


        /**
         * 物料群組與物料表
         */
        Schema::create('mGroupAndM', function (Blueprint $table) {
            $table->id();
            $table->integer('materialGroupId')->comment('物料群組id');
            $table->integer('skuId')->comment('物料sku表id');
            $table->integer('amount')->default(0)->comment('數量');
            // 聯合id
            $table->unique(['materialGroupId', 'skuId']);
            $table->timestamps();
        });

        /**
         * 物料Sku表
         */
        Schema::create('materialSku', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('物料名稱');
            $table->string('json')->nullable()->comment('sku圖片');
            $table->json('skuAttr')->nullable()->comment('sku屬性值');
            $table->integer('spuId')->nullable()->comment('spu id');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materialGroup');
        Schema::dropIfExists('mGroupAndM');
        Schema::dropIfExists('materialSku');

    }
};
