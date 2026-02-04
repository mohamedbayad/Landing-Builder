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
        $leads = DB::table('leads')->where('type', 'checkout')->get();

        foreach ($leads as $lead) {
            $data = json_decode($lead->data, true);
            
            if (is_array($data)) {
                $updates = [];
                
                // Map JSON keys to DB columns
                if (empty($lead->first_name) && isset($data['billing_first_name'])) $updates['first_name'] = $data['billing_first_name'];
                if (empty($lead->last_name) && isset($data['billing_last_name'])) $updates['last_name'] = $data['billing_last_name'];
                if (empty($lead->phone) && isset($data['billing_phone'])) $updates['phone'] = $data['billing_phone'];
                if (empty($lead->address) && isset($data['billing_address'])) $updates['address'] = $data['billing_address'];
                if (empty($lead->city) && isset($data['billing_city'])) $updates['city'] = $data['billing_city'];
                if (empty($lead->zip) && isset($data['billing_zip'])) $updates['zip'] = $data['billing_zip'];
                if (empty($lead->country) && isset($data['billing_country'])) $updates['country'] = $data['billing_country'];

                if (!empty($updates)) {
                    DB::table('leads')->where('id', $lead->id)->update($updates);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse operation needed for backfilling
    }
};
