<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWorkflowStatusToOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {            
            if (!Schema::hasColumn('orders', 'workflow_status')) {
                $table->json('workflow_status')->nullable()->after('system_order_no');
            }

            if (Schema::hasColumn('orders', 'order_file')) {
                $table->dropColumn('order_file');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'workflow_status')) {
                $table->dropColumn('workflow_status');
            }
        });
    }
}
