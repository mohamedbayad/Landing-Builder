<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('email_automations', function (Blueprint $table) {
            $table->boolean('builder_mode')->default(false)->after('settings')->index();
            $table->json('visual_nodes')->nullable()->after('builder_mode');
            $table->json('visual_edges')->nullable()->after('visual_nodes');
            $table->unsignedInteger('builder_version')->default(1)->after('visual_edges');
        });

        Schema::table('email_messages', function (Blueprint $table) {
            $table->string('channel')->default('email')->after('landing_page_id')->index(); // email|whatsapp|instagram|sms
            $table->string('recipient_phone')->nullable()->after('recipient_email');
            $table->text('body_text')->nullable()->after('body_html');
        });

        Schema::create('email_contact_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->constrained('email_contacts')->cascadeOnDelete();
            $table->string('tag')->index();
            $table->timestamps();

            $table->unique(['contact_id', 'tag']);
        });

        Schema::create('automation_executions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('automation_id')->constrained('email_automations')->cascadeOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained('email_contacts')->nullOnDelete();
            $table->string('status')->default('active')->index(); // active|completed|failed|paused|exited
            $table->string('current_node_id')->nullable()->index();
            $table->timestamp('scheduled_for')->nullable()->index();
            $table->json('context')->nullable();
            $table->string('last_error_code')->nullable();
            $table->text('last_error_message')->nullable();
            $table->timestamp('completed_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('automation_execution_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('execution_id')->constrained('automation_executions')->cascadeOnDelete();
            $table->string('node_id')->nullable()->index();
            $table->string('event_type')->index(); // entered|processed|failed|skipped|completed
            $table->json('result')->nullable();
            $table->timestamp('occurred_at')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('automation_execution_histories');
        Schema::dropIfExists('automation_executions');
        Schema::dropIfExists('email_contact_tags');

        Schema::table('email_messages', function (Blueprint $table) {
            $table->dropColumn(['channel', 'recipient_phone', 'body_text']);
        });

        Schema::table('email_automations', function (Blueprint $table) {
            $table->dropColumn(['builder_mode', 'visual_nodes', 'visual_edges', 'builder_version']);
        });
    }
};

