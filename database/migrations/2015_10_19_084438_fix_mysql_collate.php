<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FixMysqlCollate extends Migration
{
    /**
     * Drop all tables in the schema.
     *
     * @return void
     */
    private function burnItDown()
    {
        // The order here is specific so that PKs get dropped in the right order.
        Schema::dropIfExists('bans');
        Schema::dropIfExists('ban_reasons');

        Schema::dropIfExists('cache');

        Schema::dropIfExists('reports');

        Schema::dropIfExists('board_assets');
        Schema::dropIfExists('board_settings');
        Schema::dropIfExists('board_tags');
        Schema::dropIfExists('board_tag_assignments');

        Schema::dropIfExists('captcha');

        Schema::dropIfExists('file_attachments');
        Schema::dropIfExists('files');
        Schema::dropIfExists('logs');

        Schema::dropIfExists('site_settings');

        Schema::dropIfExists('option_group_assignments');
        Schema::dropIfExists('options');

        Schema::dropIfExists('permission_group_assignments');
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('permission_groups');

        Schema::dropIfExists('payments');
        Schema::dropIfExists('post_cites');
        Schema::dropIfExists('sessions');

        Schema::dropIfExists('user_roles');

        Schema::dropIfExists('stats_uniques');

        Schema::dropIfExists('password_resets');

        Schema::dropIfExists('option_groups');
        Schema::dropIfExists('stats');
        Schema::dropIfExists('roles');

        Schema::dropIfExists('posts');
        Schema::dropIfExists('board_adventures');
        Schema::dropIfExists('boards');
        Schema::dropIfExists('users');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->burnItDown();

        /**
         * Ban Reasons
         */
        Schema::create('ban_reasons', function(Blueprint $table)
        {
            $table->increments('ban_reason_id');
            $table->timestamps();
            $table->string('board_uri', 32)->nullable()->default(null);
            $table->text('ban_name');
            $table->text('ban_text');
            $table->text('mod_tip')->nullable()->default(null);
            $table->boolean('require_file')->default(false);
            $table->boolean('require_text')->default(false);
            $table->boolean('delete_file')->default(false);
            $table->boolean('delete_text')->default(false);
        });

        /**
         * Bans
         */
        Schema::create('bans', function(Blueprint $table)
        {
            $table->increments('ban_id');
            $table->ipAddress('ban_ip_end');
            $table->ipAddress('ban_ip_start');
            $table->boolean('seen')->default(false);
            $table->timestamps();
            $table->timestamp('expires_at')->nullable()->default(null);

            $table->string('board_uri', 32)->nullable()->default(null);
            $table->integer('mod_id')->unsigned()->nullable()->default(null);
            $table->bigInteger('post_id')->unsigned()->nullable()->default(null);
            $table->integer('ban_reason_id')->unsigned()->nullable()->default(null);
            $table->text('justification')->default(null);
        });

        /**
         * Board Adventures
         */
        Schema::create('board_adventures', function(Blueprint $table)
        {
            $table->increments('adventure_id');
            $table->ipAddress('adventurer_ip');
            $table->string('board_uri', 32);
            $table->integer('post_id')->unsigned()->nullable()->default(NULL);
            $table->timestamps();
            $table->timestamp('expires_at');
            $table->timestamp('expended_at')->nullable()->default(NULL);
        });

        /**
         * Board Assets
         */
        Schema::create('board_assets', function(Blueprint $table)
        {
            $table->increments('board_asset_id');
            $table->string('board_uri', 32);
            $table->bigInteger('file_id')->unsigned();
            $table->string('asset_type', 24);
            $table->timestamps();
        });

        /**
         * Board Settings
         */
        Schema::create('board_settings', function($table)
        {
            $table->increments('board_setting_id');
            $table->string('option_name', 128);
            $table->string('board_uri', 32);
            $table->binary('option_value');
        });

        /**
         * Board Tags
         */
        Schema::create('board_tags', function(Blueprint $table)
        {
            $table->increments('board_tag_id');
            $table->string('tag', 32);
        });

        /**
         * Board Tag Assignments
         */
        Schema::create('board_tag_assignments', function(Blueprint $table)
        {
            $table->increments('board_tag_assignment_id');
            $table->integer('board_tag_id')->unsigned();
            $table->string('board_uri', 32);

        });

        /**
         * Boards
         */
        Schema::create('boards', function(Blueprint $table)
        {
            $table->string('board_uri', 32)->unique();
            $table->text('title');
            $table->text('description')->nullable()->default(null);
            $table->timestamps();
            $table->timestamp('last_post_at')->nullable()->default(null);
            $table->integer('created_by')->unsigned();
            $table->integer('operated_by')->unsigned();
            $table->integer('posts_total')->unsigned()->default(0);
            $table->boolean('is_indexed')->default(true);
            $table->boolean('is_overboard')->default(true);
            $table->boolean('is_worksafe')->default(false);
        });

        /**
         * Cache
         */
        Schema::create('cache', function($table)
        {
            $table->string('key', 191)->unique();
            $table->longtext('value');
            $table->integer('expiration');
        });

        /**
         * Captcha
         */
        Schema::create('captcha', function(Blueprint $table)
        {
            $table->increments('captcha_id');
            $table->binary('hash');
            $table->ipAddress('client_ip');
            $table->string('solution', 16);
            $table->timestamp('created_at');
            $table->timestamp('cracked_at')->nullable()->default(null);
        });

        /**
         * File Attachments
         */
        Schema::create('file_attachments', function(Blueprint $table)
        {
            $table->bigIncrements('attachment_id');
            $table->bigInteger('post_id')->unsigned();
            $table->bigInteger('file_id')->unsigned();

            $table->text('filename');
            $table->smallInteger('position')->unsigned()->default(0);
            $table->boolean('is_spoiler')->default(false);
        });

        /**
         * File Storage
         */
        Schema::create('files', function(Blueprint $table)
        {
            $table->bigIncrements('file_id');
            $table->char('hash', 32);
            $table->boolean('banned')->default(false);
            $table->integer('filesize')->unsigned();
            $table->text('mime');
            $table->mediumText('meta')->nullable();
            $table->dateTime('first_uploaded_at');
            $table->dateTime('last_uploaded_at');
            $table->integer('upload_count')->unsigned();
            $table->boolean('has_thumbnail')->default(false);
        });

        /**
         * Logs
         */
        Schema::create('logs', function(Blueprint $table)
        {
            $table->increments('action_id');
            $table->text('action_name');
            $table->binary('action_details')->nullable()->default(null);
            $table->integer('user_id')->unsigned()->nullable()->default(null);
            $table->ipAddress('user_ip')->nullable();
            $table->string('board_uri', 32)->nullable()->default(null);
            $table->timestamps();
        });

        /**
         * Options
         */
        Schema::create('options', function(Blueprint $table)
        {
            $table->string('option_name', 128)->unique();
            $table->binary('default_value')->nullable();
            $table->string('option_type', 24)->default('string');
            $table->string('format', 24)->default('textbox');
            $table->mediumText('format_parameters');
            $table->string('data_type', 24)->default('board');
            $table->text('validation_parameters');
            $table->text('validation_class')->nullable();
        });

        /**
         * Option Groups
         */
        Schema::create('option_groups', function(Blueprint $table)
        {
            $table->increments('option_group_id');
            $table->string('group_name', 191)->unique();
            $table->boolean('debug_only');
            $table->integer('display_order')->unsigned();
        });

        /**
         * Option Groups Assignments
         */
        Schema::create('option_group_assignments', function(Blueprint $table)
        {
            $table->string('option_name', 128);
            $table->integer('option_group_id')->unsigned();
            $table->integer('display_order')->unsigned();

        });

        /**
         * Password Resets
         */
        Schema::create('password_resets', function(Blueprint $table)
        {
            $table->text('email');
            $table->text('token');
            $table->timestamp('created_at');
        });

        /**
         * Payments
         */
        Schema::create('payments', function(Blueprint $table)
        {
            // Bulk data
            $table->increments('payment_id');
            $table->integer('customer_id')->unsigned()->nullable()->default(NULL);
            $table->text('attribution')->nullable()->default(NULL);
            $table->ipAddress('payment_ip')->nullable();
            $table->timestamp('created_at');
            $table->integer('amount');
            $table->string('currency', 3);
            $table->string('subscription', 64)->nullable();
        });

        /**
         * Permsision Groups
         */
        Schema::create('permission_groups', function(Blueprint $table)
        {
            $table->increments('permission_group_id');
            $table->string('group_name', 191)->unique();
            $table->integer('display_order')->unsigned();
            $table->boolean('is_account_only')->default(false);
            $table->boolean('is_system_only')->default(false);
        });

        /**
         * Permsision Groups Assignments
         */
        Schema::create('permission_group_assignments', function(Blueprint $table)
        {
            $table->string('permission_id', 128);
            $table->integer('permission_group_id')->unsigned();
            $table->integer('display_order')->unsigned();
        });

        /**
         * Post Cites
         */
        Schema::create('post_cites', function(Blueprint $table)
        {
            $table->bigIncrements('post_cite_id');
            $table->bigInteger('post_id')->unsigned();
            $table->string('post_board_uri', 32);
            $table->bigInteger('post_board_id')->unsigned();
            $table->bigInteger('cite_id')->unsigned()->nullable();
            $table->string('cite_board_uri', 32);
            $table->bigInteger('cite_board_id')->unsigned()->nullable();
        });

        /**
         * Permissions
         */
        Schema::create('permissions', function(Blueprint $table)
        {
            $table->string('permission_id', 128);
        });

        /**
         * Posts
         */
        Schema::create('posts', function(Blueprint $table)
        {
            // Identifying information
            $table->bigIncrements('post_id');
            $table->string('board_uri', 32);
            $table->bigInteger('board_id')->unsigned();
            $table->bigInteger('reply_to')->unsigned()->nullable();
            $table->bigInteger('reply_to_board_id')->unsigned()->nullable();
            $table->integer('reply_count')->nullable()->default(0);
            $table->timestamp('reply_last')->nullable();
            $table->timestamp('bumped_last')->nullable()->default(null);

            // Embedded information
            $table->timestamps();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->softDeletes();
            $table->boolean('stickied')->default(false);
            $table->timestamp('stickied_at')->nullable()->default(null);
            $table->timestamp('bumplocked_at')->nullable()->default(null);
            $table->timestamp('locked_at')->nullable()->default(null);

            // Authorship information
            $table->ipAddress('author_ip')->nullable()->default(null);
            $table->string('author_id', 6)->nullable();
            $table->string('author_country', 4)->nullable();
            $table->timestamp('author_ip_nulled_at')->nullable();
            $table->text('author')->nullable();
            $table->string('insecure_tripcode')->nullable();
            $table->integer('capcode_id')->unsigned()->nullable()->default(null);
            $table->integer('adventure_id')->unsigned()->nullable()->default(null);

            // Content information
            $table->text('subject')->nullable();
            $table->text('email')->nullable();
            $table->text('password')->nullable()->default(null);

            // Main Body
            $table->text('body')->nullable();
            $table->text('body_parsed')->nullable();
            $table->timestamp('body_parsed_at')->nullable();
            $table->text('body_html')->nullable();
        });

        /**
         * Reports
         */
        Schema::create('reports', function(Blueprint $table)
        {
            $table->increments('report_id');
            $table->text('reason');
            $table->string('board_uri', 32)->nullable();
            $table->boolean('global')->default(false);
            $table->bigInteger('post_id')->nullable()->unsigned();
            $table->ipAddress('reporter_ip')->nullable()->default(null);
            $table->integer('user_id')->nullable()->unsigned();
            $table->boolean('is_dismissed')->default(false);
            $table->boolean('is_successful')->default(false);
            $table->timestamps();
            $table->timestamp('promoted_at')->nullable();
            $table->integer('promoted_by')->nullable()->unsigned();
        });

        /**
         * Roles
         */
        Schema::create('roles', function(Blueprint $table)
        {
            $table->increments('role_id');
            $table->string('role', 128);
            $table->string('board_uri', 32)->nullable()->default(NULL);
            $table->string('caste', 128)->nullable()->default(NULL);

            $table->integer('inherit_id')->unsigned()->nullable()->default(NULL);

            $table->text('name');
            $table->text('capcode')->nullable();

            $table->boolean('system')->default(false);
            $table->integer('weight')->unsigned()->default(0);
        });

        /**
         * Role Permissions
         */
        Schema::create('role_permissions', function(Blueprint $table)
        {
            $table->integer('role_id')->unsigned();
            $table->string('permission_id', 128);
            $table->boolean('value');
        });

        /**
         * Sessions
         */
        Schema::create('sessions', function($table)
        {
            $table->string('id', 191)->unique();
            $table->text('payload');
            $table->integer('last_activity');
        });

        /**
         * Site Settings
         */
        Schema::create('site_settings', function($table)
        {
            $table->increments('site_setting_id');
            $table->string('option_name', 128);
            $table->binary('option_value');
        });

        /**
         * Stats
         */
        Schema::create('stats', function(Blueprint $table)
        {
            $table->bigIncrements('stats_id');
            $table->string('board_uri', 32);
            $table->timestamp('stats_time');
            $table->string('stats_type', 64);
            $table->bigInteger('counter')->unsigned()->default(0);
        });

        /**
         * Stats Uniques
         */
        Schema::create('stats_uniques', function(Blueprint $table)
        {
            $table->bigIncrements('stats_bit_id');
            $table->bigInteger('stats_id')->unsigned();
            $table->bigInteger('unique');
        });

        /**
         * Users
         */
        Schema::create('users', function(Blueprint $table)
        {
            $table->increments('user_id');
            $table->string('username', 191)->unique();
            $table->text('email')->nullable();
            $table->boolean('email_verified')->default(0);
            $table->text('password')->nullable();
            $table->text('password_legacy')->nullable()->default(null);
            $table->rememberToken();
            $table->timestamps();

            // Stripe
            $table->tinyInteger('stripe_active')->default(0);
            $table->string('stripe_id')->nullable();
            $table->string('stripe_subscription')->nullable();
            $table->string('stripe_plan', 100)->nullable();
            $table->string('last_four', 4)->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('subscription_ends_at')->nullable();
            $table->string('subscription_kill_token', 32)->nullable();

            // Braintree
            $table->tinyInteger('braintree_active')->default(0);
            $table->string('braintree_id')->nullable();
        });

        /**
         * User Roles
         */
        Schema::create('user_roles', function(Blueprint $table)
        {
            $table->integer('user_id')->unsigned();
            $table->integer('role_id')->unsigned();
        });


        /**
         * Ban Reasons Keys
         */
        Schema::table('ban_reasons', function(Blueprint $table)
        {
            // Foreigns and Indexes
            $table->foreign('board_uri')
                ->references('board_uri')->on('boards')
                ->onDelete('cascade')->onUpdate('cascade');
        });

        /**
         * Bans Keys
         */
        Schema::table('bans', function(Blueprint $table)
        {
            // Foreigns and Indexes
            $table->foreign('board_uri')
                ->references('board_uri')->on('boards')
                ->onDelete('cascade')->onUpdate('cascade');

            $table->foreign('mod_id')
                ->references('user_id')->on('users')
                ->onDelete('set null')->onUpdate('cascade');

            $table->foreign('post_id')
                ->references('post_id')->on('posts')
                ->onDelete('set null')->onUpdate('cascade');

            $table->foreign('ban_reason_id')
                ->references('ban_reason_id')->on('ban_reasons')
                ->onDelete('set null')->onUpdate('cascade');

        });

        /**
         * Board Assets Keys
         */
        Schema::table('board_assets', function(Blueprint $table)
        {
            // Foreigns and Indexes
            $table->foreign('file_id')
                ->references('file_id')->on('files')
                ->onDelete('cascade')->onUpdate('cascade');

            $table->foreign('board_uri')
                ->references('board_uri')->on('boards')
                ->onDelete('cascade')->onUpdate('cascade');
        });

        /**
         * Board Adventures Keys
         */
        Schema::table('board_adventures', function(Blueprint $table)
        {
            $table->foreign('board_uri')
                ->references('board_uri')->on('boards')
                ->onDelete('cascade')->onUpdate('cascade');
        });

        /**
         * Board Settings Keys
         */
        Schema::table('board_settings', function($table)
        {
            // Foreigns and Indexes
            $table->index('option_name');
            $table->unique(['option_name', 'board_uri']);
        });
        Schema::table('board_settings', function($table)
        {
            $table->foreign('option_name')
                ->references('option_name')->on('options')
                ->onDelete('cascade')->onUpdate('cascade');

            $table->foreign('board_uri')
                ->references('board_uri')->on('boards')
                ->onDelete('cascade')->onUpdate('cascade');
        });

        /**
         * Board Tags Keys
         */
        Schema::table('board_tag_assignments', function(Blueprint $table)
        {
            $table->foreign('board_uri')
                ->references('board_uri')->on('boards')
                ->onDelete('cascade')->onUpdate('cascade');

            $table->foreign('board_tag_id')
                ->references('board_tag_id')->on('board_tags')
                ->onDelete('cascade')->onUpdate('cascade');
        });

        /**
         * Boards Keys
         */
        Schema::table('boards', function(Blueprint $table)
        {
            // Foreigns and Indexes
            $table->primary('board_uri');

            $table->foreign('created_by')
                ->references('user_id')->on('users')
                ->onDelete('cascade')->onUpdate('cascade');

            $table->foreign('operated_by')
                ->references('user_id')->on('users')
                ->onDelete('cascade')->onUpdate('cascade');
        });

        /**
         * File Attachments Keys
         */
        Schema::table('file_attachments', function(Blueprint $table)
        {
            $table->foreign('file_id')
                ->references('file_id')->on('files')
                ->onDelete('set null')->onUpdate('cascade');

            $table->foreign('post_id')
                ->references('post_id')->on('posts')
                ->onDelete('cascade')->onUpdate('cascade');
        });

        /**
         * Files Keys
         */
        Schema::table('files', function(Blueprint $table)
        {
            // Foreigns and Indexes
            $table->unique('hash');
        });

        /**
         * Logs Keys
         */
        Schema::table('logs', function(Blueprint $table)
        {
            // Foreigns and Indexes
            $table->foreign('user_id')
                ->references('user_id')->on('users')
                ->onDelete('cascade')->onUpdate('cascade');

            $table->foreign('board_uri')
                ->references('board_uri')->on('boards')
                ->onDelete('cascade')->onUpdate('cascade');
        });

        /**
         * Option Groups Assignments Keys
         */
        Schema::table('option_group_assignments', function(Blueprint $table)
        {
            // Foreigns and Indexes
            $table->unique(['option_name', 'option_group_id']);

            $table->foreign('option_name')
                ->references('option_name')->on('options')
                ->onDelete('cascade')->onUpdate('cascade');

            $table->foreign('option_group_id')
                ->references('option_group_id')->on('option_groups')
                ->onDelete('cascade')->onUpdate('cascade');
        });

        /**
         * Payments Keys
         */
        Schema::table('payments', function(Blueprint $table)
        {
            // Foreigns and Indexes
            $table->foreign('customer_id')
                ->references('user_id')->on('users')
                ->onUpdate('cascade');
        });

        /**
         * Permissions Keys
         */
        Schema::table('permissions', function(Blueprint $table)
        {
            // Foreigns and Indexes
            $table->primary('permission_id');
        });

        /**
         * Permsision Groups Assignments Keys
         */
        Schema::table('permission_group_assignments', function(Blueprint $table)
        {
            // Foreigns and Indexes
            $table->unique(['permission_id', 'permission_group_id'], 'permission_group_assignments_unique');

            $table->foreign('permission_id')
                ->references('permission_id')->on('permissions')
                ->onDelete('cascade')->onUpdate('cascade');

            $table->foreign('permission_group_id')
                ->references('permission_group_id')->on('permission_groups')
                ->onDelete('cascade')->onUpdate('cascade');
        });

        /**
         * Post Cites Keys
         */
        Schema::table('post_cites', function(Blueprint $table)
        {
            // Foreigns and Indexes
            $table->foreign('post_id')
                ->references('post_id')->on('posts')
                ->onDelete('cascade')->onUpdate('cascade');

            $table->foreign('post_board_uri')
                ->references('board_uri')->on('boards')
                ->onDelete('cascade')->onUpdate('cascade');

            $table->foreign('cite_id')
                ->references('post_id')->on('posts')
                ->onDelete('cascade')->onUpdate('cascade');

            $table->foreign('cite_board_uri')
                ->references('board_uri')->on('boards')
                ->onDelete('cascade')->onUpdate('cascade');
        });

        /**
         * Posts Keys
         */
        Schema::table('posts', function(Blueprint $table)
        {
            // Foreigns and Indexes
            $table->unique(['board_uri', 'board_id']);

            $table->foreign('board_uri')
                ->references('board_uri')->on('boards')
                ->onDelete('cascade')->onUpdate('cascade');

            $table->foreign('capcode_id')
                ->references('role_id')->on('roles')
                ->onDelete('set null')->onUpdate('cascade');

            $table->foreign('adventure_id')
                ->references('adventure_id')->on('board_adventures')
                ->onDelete('set null')->onUpdate('cascade');

            $table->foreign('reply_to')
                ->references('post_id')->on('posts')
                ->onDelete('cascade')->onUpdate('cascade');
        });

        /**
         * Reports Keys
         */
        Schema::table('reports', function(Blueprint $table)
        {
            // Relationshps
            $table->foreign('user_id')
                ->references('user_id')->on('users')
                ->onDelete('set null')->onUpdate('set null');

            $table->foreign('post_id')
                ->references('post_id')->on('posts')
                ->onDelete('set null')->onUpdate('set null');

            $table->foreign('board_uri')
                ->references('board_uri')->on('boards')
                ->onDelete('set null')->onUpdate('set null');
        });

        /**
         * Role Permissions Keys
         */
        Schema::table('role_permissions', function(Blueprint $table)
        {
            // Foreigns and Indexes
            $table->primary(['role_id', 'permission_id']);
            $table->index('role_id');
            $table->index('permission_id');

            $table->foreign('role_id')
                ->references('role_id')->on('roles')
                ->onDelete('cascade')->onUpdate('cascade');

            $table->foreign('permission_id')
                ->references('permission_id')->on('permissions')
                ->onDelete('cascade')->onUpdate('cascade');
        });

        /**
         * Roles Keys
         */
        Schema::table('roles', function(Blueprint $table)
        {
            // Foreigns and Indexes
            $table->index(['role', 'board_uri', 'caste']);

            $table->foreign('board_uri')
                ->references('board_uri')->on('boards')
                ->onDelete('cascade')->onUpdate('cascade');

            $table->foreign('inherit_id')
                ->references('role_id')->on('roles')
                ->onDelete('cascade')->onUpdate('cascade');
        });

        /**
         * Site Settings Keys
         */
        Schema::table('site_settings', function($table)
        {
            // Foreigns and Indexes
            $table->index('option_name');

            $table->foreign('option_name')
                ->references('option_name')->on('options')
                ->onDelete('cascade')->onUpdate('cascade');
        });

        /**
         * Stats Keys
         */
        Schema::table('stats', function(Blueprint $table)
        {
            // Foreigns and Indexes
            $table->unique(['stats_time', 'board_uri', 'stats_type']);

            $table->foreign('board_uri')
                ->references('board_uri')->on('boards')
                ->onDelete('cascade')->onUpdate('cascade');
        });

        /**
         * Stats Uniques Keys
         */
        Schema::table('stats_uniques', function(Blueprint $table)
        {
            // Foreigns and Indexes
            $table->foreign('stats_id')
                ->references('stats_id')->on('stats')
                ->onDelete('cascade')->onUpdate('cascade');
        });

        /**
         * User Roles Keys
         */
        Schema::table('user_roles', function(Blueprint $table)
        {
            // Foreigns and Indexes
            $table->primary(['user_id', 'role_id']);
            $table->index('user_id');
            $table->index('role_id');

            $table->foreign('user_id')
                ->references('user_id')->on('users')
                ->onDelete('cascade')->onUpdate('cascade');

            $table->foreign('role_id')
                ->references('role_id')->on('roles')
                ->onDelete('cascade')->onUpdate('cascade');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->burnItDown();
    }
}
