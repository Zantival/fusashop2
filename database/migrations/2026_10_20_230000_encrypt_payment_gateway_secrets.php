<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $companySecrets = ['wompi_integrity_secret', 'wompi_events_secret'];
    private array $bannerSecrets = ['custom_wompi_integrity_secret', 'custom_wompi_events_secret'];

    public function up(): void
    {
        if (Schema::hasTable('company_profiles')) {
            $columns = array_values(array_filter($this->companySecrets, fn ($column) => Schema::hasColumn('company_profiles', $column)));
            if ($columns) {
                Schema::table('company_profiles', function (Blueprint $table) use ($columns) {
                    foreach ($columns as $column) {
                        $table->text($column)->nullable()->change();
                    }
                });

                DB::table('company_profiles')->select(array_merge(['id'], $columns))->orderBy('id')->chunkById(100, function ($rows) use ($columns) {
                    foreach ($rows as $row) {
                        $updates = [];
                        foreach ($columns as $column) {
                            if (!empty($row->{$column})) {
                                $updates[$column] = Crypt::encryptString($row->{$column});
                            }
                        }
                        if ($updates) {
                            DB::table('company_profiles')->where('id', $row->id)->update($updates);
                        }
                    }
                });
            }
        }

        $this->encryptBannerSecrets();
    }

    public function down(): void
    {
        $this->decryptBannerSecrets();

        if (Schema::hasTable('company_profiles')) {
            $columns = array_values(array_filter($this->companySecrets, fn ($column) => Schema::hasColumn('company_profiles', $column)));
            if ($columns) {
                DB::table('company_profiles')->select(array_merge(['id'], $columns))->orderBy('id')->chunkById(100, function ($rows) use ($columns) {
                    foreach ($rows as $row) {
                        $updates = [];
                        foreach ($columns as $column) {
                            if (!empty($row->{$column})) {
                                try {
                                    $updates[$column] = Crypt::decryptString($row->{$column});
                                } catch (\Throwable) {
                                    $updates[$column] = $row->{$column};
                                }
                            }
                        }
                        if ($updates) {
                            DB::table('company_profiles')->where('id', $row->id)->update($updates);
                        }
                    }
                });
                Schema::table('company_profiles', function (Blueprint $table) use ($columns) {
                    foreach ($columns as $column) {
                        $table->string($column, 120)->nullable()->change();
                    }
                });
            }
        }
    }

    private function encryptBannerSecrets(): void
    {
        if (!Schema::hasTable('settings')) {
            return;
        }

        $setting = DB::table('settings')->where('key', 'banner_payout_config')->first();
        $config = $setting ? json_decode($setting->value, true) : null;
        if (!is_array($config)) {
            return;
        }

        foreach ($this->bannerSecrets as $key) {
            $secret = (string) ($config[$key] ?? '');
            if ($secret !== '' && !str_starts_with($secret, 'enc:v1:')) {
                $config[$key] = 'enc:v1:' . Crypt::encryptString($secret);
            }
        }

        DB::table('settings')->where('id', $setting->id)->update([
            'value' => json_encode($config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
    }

    private function decryptBannerSecrets(): void
    {
        if (!Schema::hasTable('settings')) {
            return;
        }

        $setting = DB::table('settings')->where('key', 'banner_payout_config')->first();
        $config = $setting ? json_decode($setting->value, true) : null;
        if (!is_array($config)) {
            return;
        }

        foreach ($this->bannerSecrets as $key) {
            $secret = (string) ($config[$key] ?? '');
            if (str_starts_with($secret, 'enc:v1:')) {
                try {
                    $config[$key] = Crypt::decryptString(substr($secret, 7));
                } catch (\Throwable) {
                    // Preserve an unreadable encrypted value instead of discarding it.
                }
            }
        }

        DB::table('settings')->where('id', $setting->id)->update([
            'value' => json_encode($config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
    }
};
