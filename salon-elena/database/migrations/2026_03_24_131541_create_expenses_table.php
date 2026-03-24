<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_expenses_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->bigIncrements('expense_id');
            $table->string('type', 50); // salary, supplier_order
            $table->decimal('amount', 12, 2); // сумма расхода
            $table->date('date'); // дата расхода
            $table->text('description')->nullable(); // описание
            $table->unsignedBigInteger('reference_id')->nullable(); // ID связанной записи
            $table->string('reference_type')->nullable(); // класс связанной модели
            $table->json('metadata')->nullable(); // дополнительные данные
            $table->boolean('is_confirmed')->default(true);
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
            
            $table->index(['type', 'date']);
            $table->index(['reference_id', 'reference_type']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('expenses');
    }
};