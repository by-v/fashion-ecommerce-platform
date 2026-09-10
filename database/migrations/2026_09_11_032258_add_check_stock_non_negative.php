<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add CHECK constraint to prevent negative stock if supported by database driver
        if (DB::getDriverName() !== 'sqlite') {
            try {
                DB::statement('ALTER TABLE products ADD CONSTRAINT products_stock_check CHECK (stock >= 0)');
            } catch (\Exception $e) {
                // Ignore if constraint already exists or not supported
            }

            try {
                DB::statement('ALTER TABLE product_variants ADD CONSTRAINT product_variants_stock_check CHECK (stock >= 0)');
            } catch (\Exception $e) {
                // Ignore if constraint already exists or not supported
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            try {
                DB::statement('ALTER TABLE products DROP CONSTRAINT products_stock_check');
            } catch (\Exception $e) {
            }

            try {
                DB::statement('ALTER TABLE product_variants DROP CONSTRAINT product_variants_stock_check');
            } catch (\Exception $e) {
            }
        }
    }
};
