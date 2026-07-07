<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LegacyCollectionSeeder extends Seeder
{
    /**
     * Migrate staged fs_* import tables into production MySQL tables.
     */
    public function run(): void
    {
        $registry = config('kweek_entities', []);

        try {
            DB::statement('SET SESSION max_allowed_packet = 16777216');
        } catch (\Throwable $e) {
            // Ignore if session level setting is not supported
        }

        Schema::disableForeignKeyConstraints();

        $settingsMap = [
            'settings_0' => 'AppHomeBanners',
            'settings_2' => 'rental_booking',
            'settings_3' => 'ContactUs',
            'settings_4' => 'DeliveryCharge',
            'settings_5' => 'DriverNearBy',
            'settings_6' => 'mercadopago',
            'settings_7' => 'stripeSettings',
            'settings_8' => 'Version',
            'settings_9' => 'razorpay',
            'settings_11' => 'arropay',
            'settings_12' => 'arropay_instapay',
            'settings_13' => 'arropay_maya_qr',
            'settings_14' => 'arropay_maya_prefer',
            'settings_15' => 'cab_landing_page',
            'settings_16' => 'digitalProduct',
            'settings_17' => 'driver_verification',
            'settings_18' => 'emailSetting',
            'settings_19' => 'payfast',
            'settings_20' => 'footer_template',
            'settings_21' => 'globalSettings',
            'settings_22' => 'googleMapKey',
            'settings_23' => 'homepage_template',
            'settings_24' => 'languages',
            'settings_25' => 'maintenance_mode',
            'settings_26' => 'paystack',
            'settings_27' => 'notification_setting',
            'settings_28' => 'paytm',
            'settings_30' => 'paypal',
            'settings_31' => 'flutterWave',
            'settings_32' => 'paypalSettings',
            'settings_33' => 'placeHolderImage',
            'settings_34' => 'privacy_policy',
            'settings_35' => 'provider',
            'settings_36' => 'razorpaySettings',
            'settings_37' => 'wallet',
            'settings_38' => 'notification_time',
            'settings_40' => 'social_login',
            'settings_41' => 'story',
            'settings_42' => 'story_video',
            'settings_43' => 'walletSettings',
            'settings_44' => 'termsAndConditions',
            'settings_45' => 'permission_check',
            'settings_46' => 'vendor',
            'settings_49' => 'rental',
            'settings_50' => 'payfastSettings',
        ];

        foreach ($registry as $key => $meta) {
            $sourceTable = 'fs_' . $key;
            $targetTable = $meta['table'] ?? null;

            if (! $targetTable) {
                continue;
            }

            if (! Schema::hasTable($sourceTable)) {
                $this->command->warn("Skipping '{$key}': Source table '{$sourceTable}' does not exist.");

                continue;
            }

            if (! Schema::hasTable($targetTable)) {
                $this->command->warn("Skipping '{$key}': Target table '{$targetTable}' does not exist.");

                continue;
            }

            $this->command->info("Migrating data from '{$sourceTable}' to '{$targetTable}'...");

            $columnDetails = DB::select("SHOW COLUMNS FROM `{$targetTable}`");
            $columnLengths = [];
            $columnNullability = [];
            $columnDefaults = [];
            $columnTypes = [];
            foreach ($columnDetails as $col) {
                if (preg_match('/varchar\((\d+)\)/i', $col->Type, $matches)) {
                    $columnLengths[$col->Field] = (int) $matches[1];
                }
                $columnNullability[$col->Field] = ($col->Null === 'YES');
                $columnDefaults[$col->Field] = $col->Default;
                $columnTypes[$col->Field] = $col->Type;
            }

            $targetColumns = Schema::getColumnListing($targetTable);
            $targetColumnsMap = array_combine(
                array_map('strtolower', $targetColumns),
                $targetColumns
            );

            $records = DB::table($sourceTable)->get();
            DB::table($targetTable)->truncate();

            $chunk = [];
            foreach ($records as $record) {
                $recordArray = (array) $record;
                $attributes = [];
                $payload = [];

                $targetId = $recordArray['document_id'] ?? $recordArray['id'] ?? null;
                if ($targetId !== null) {
                    $targetIdStr = (string) $targetId;
                    if ($targetTable === 'settings') {
                        $targetIdStr = $settingsMap[$targetIdStr] ?? $targetIdStr;
                    }
                    if (isset($columnLengths['id']) && strlen($targetIdStr) > $columnLengths['id']) {
                        $targetIdStr = substr($targetIdStr, 0, $columnLengths['id']);
                    }
                    $attributes['id'] = $targetIdStr;
                }

                $createdAtVal = $recordArray['created_at'] ?? null;
                $updatedAtVal = $recordArray['updated_at'] ?? null;

                unset(
                    $recordArray['id'],
                    $recordArray['document_id'],
                    $recordArray['created_at'],
                    $recordArray['updated_at']
                );

                foreach ($recordArray as $colName => $value) {
                    $cleanColName = strtolower(str_replace('_', '', $colName));

                    $actualTargetCol = null;
                    if ($cleanColName === 'locationlatitude' && isset($targetColumnsMap['latitude'])) {
                        $actualTargetCol = $targetColumnsMap['latitude'];
                    } elseif ($cleanColName === 'locationlongitude' && isset($targetColumnsMap['longitude'])) {
                        $actualTargetCol = $targetColumnsMap['longitude'];
                    } elseif (isset($targetColumnsMap[$cleanColName])) {
                        $actualTargetCol = $targetColumnsMap[$cleanColName];
                    }

                    if ($actualTargetCol !== null) {
                        if (is_string($value) && isset($columnLengths[$actualTargetCol])) {
                            if (strlen($value) > $columnLengths[$actualTargetCol]) {
                                $value = substr($value, 0, $columnLengths[$actualTargetCol]);
                            }
                        }

                        if ($value === null && isset($columnNullability[$actualTargetCol]) && ! $columnNullability[$actualTargetCol]) {
                            if ($columnDefaults[$actualTargetCol] !== null) {
                                $value = $columnDefaults[$actualTargetCol];
                            } else {
                                $type = strtolower($columnTypes[$actualTargetCol] ?? '');
                                if (str_contains($type, 'int') || str_contains($type, 'decimal') || str_contains($type, 'float') || str_contains($type, 'double')) {
                                    $value = 0;
                                } else {
                                    $value = '';
                                }
                            }
                        }

                        $attributes[$actualTargetCol] = $value;
                    } else {
                        $payload[$colName] = $value;
                    }
                }

                if ($targetTable === 'settings') {
                    foreach ($payload as $pKey => $pVal) {
                        if (is_string($pVal) && isset($pVal[0]) && ($pVal[0] === '[' || $pVal[0] === '{')) {
                            $decoded = json_decode($pVal, true);
                            if (json_last_error() === JSON_ERROR_NONE) {
                                $payload[$pKey] = $decoded;
                            }
                        }
                    }
                    $attributes = [
                        'id' => $targetIdStr,
                        'value' => json_encode($payload),
                    ];
                } elseif (in_array('payload', $targetColumns, true)) {
                    $attributes['payload'] = json_encode($payload);
                }

                if (in_array('created_at', $targetColumns, true)) {
                    $attributes['created_at'] = $createdAtVal ?? now();
                }
                if (in_array('updated_at', $targetColumns, true)) {
                    $attributes['updated_at'] = $updatedAtVal ?? now();
                }

                $chunk[] = $attributes;

                if (count($chunk) >= 50) {
                    DB::table($targetTable)->insert($chunk);
                    $chunk = [];
                }
            }

            if (count($chunk) > 0) {
                DB::table($targetTable)->insert($chunk);
            }

            $count = DB::table($targetTable)->count();
            $this->command->info("  ✓ Migrated {$count} records into '{$targetTable}'.");
        }

        Schema::enableForeignKeyConstraints();
    }
}
