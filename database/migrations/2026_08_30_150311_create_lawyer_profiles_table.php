<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lawyer_profiles', function (Blueprint $table) {
            $table->id();

            /*
             * User account.
             *
             * One user can have only one lawyer profile.
             */
            $table->foreignId('user_id')
                ->unique()
                ->constrained('users')
                ->cascadeOnDelete();

            /*
             * Lawyer's professional information.
             */
            $table->string('professional_name', 255)->nullable();

            $table->string('bar_registration_number', 100)
                ->nullable();

            $table->string('bar_council_name', 255)
                ->nullable();

            $table->string('law_firm_name', 255)
                ->nullable();

            $table->text('professional_bio')->nullable();

            /*
             * Primary country and region where the lawyer
             * is registered / operates.
             */
            $table->foreignId('country_id')
                ->nullable()
                ->constrained('countries')
                ->nullOnDelete();

            $table->foreignId('region_id')
                ->nullable()
                ->constrained('regions')
                ->nullOnDelete();

            /*
             * Contact / office information.
             */
            $table->string('address_line_1', 255)->nullable();
            $table->string('address_line_2', 255)->nullable();
            $table->string('city', 150)->nullable();
            $table->string('postal_code', 30)->nullable();

            /*
             * Professional experience.
             */
            $table->unsignedSmallInteger('years_of_experience')
                ->nullable();

            /*
             * Website / professional profile.
             */
            $table->string('website')->nullable();

            /*
             * Profile photograph.
             */
            $table->string('profile_photo')->nullable();

            /*
             * Lawyer account/profile approval workflow.
             *
             * pending      = newly registered
             * under_review = admin is checking documents
             * approved     = lawyer can provide services
             * rejected     = application rejected
             */
            $table->enum('approval_status', [
                'pending',
                'under_review',
                'approved',
                'rejected',
            ])->default('pending');

            /*
             * Admin/Super Admin who approved/rejected
             * the lawyer profile.
             */
            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('reviewed_at')->nullable();

            /*
             * Required when profile is rejected.
             */
            $table->text('rejection_reason')->nullable();

            /*
             * When the lawyer was approved.
             */
            $table->timestamp('approved_at')->nullable();

            /*
             * Whether this lawyer is currently available
             * to receive new orders.
             */
            $table->boolean('is_available')->default(false);

            /*
             * Profile visibility.
             */
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            /*
             * Indexes.
             */
            $table->index('approval_status');
            $table->index('is_available');
            $table->index('is_active');
            $table->index([
                'country_id',
                'region_id',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lawyer_profiles');
    }
};
