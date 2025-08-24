<?php

namespace App\Utils\Payments;

use App\Utils\Library\Templates\Gateway;

class PaymentManager
{
    public static function getSettingsFormData(): array
    {
        return cache()->rememberForever('payment_forms', function () {
            $formData = [];
            foreach (self::discover() as $key => $gateway) {
                if (isset($gateway['metadata']['settings'])) {
                    $formData[$key]['settings'] = $gateway['metadata']['settings'];

                    if (isset($gateway['metadata']['button'])) {
                        $formData[$key]['button'] = $gateway['metadata']['button'];
                    }
                }
            }

            return $formData;
        });
    }

    public static function discover(): array
    {
        return cache()->rememberForever('discovered_payments', function () {
            $gateways = [];
            foreach (glob(app_path('Utils/Payments/*.php')) as $file) {
                $className = 'App\\Utils\\Payments\\'.basename($file, '.php');
                if (class_exists($className)) {
                    $interfaces = class_implements($className);
                    if ($interfaces && in_array(Gateway::class, $interfaces, true)) {
                        $metadata = $className::metadata();
                        $gateways[$metadata['key']] = [
                            'class' => $className,
                            'metadata' => $metadata,
                        ];
                    }
                }
            }

            return $gateways;
        });
    }

    public static function getAvailable(): array
    {
        return cache()->rememberForever('available_payments', function () {
            foreach (self::discover() as $key => $gateway) {
                if (isset($gateway['metadata']['settings'])) {
                    $allConfigsExist = true;
                    foreach ($gateway['metadata']['settings'] as $setting => $config) {
                        if (! sysConfig($setting)) {
                            $allConfigsExist = false;
                            break;
                        }
                    }
                    if ($allConfigsExist) {
                        $available[] = $key;
                    }
                }
            }

            return $available ?? [];
        });
    }

    public static function getLabels(bool $history = false): array
    {
        return cache()->rememberForever('payment_labels'.app()->getLocale(), function () use ($history) {
            if ($history) {
                $labels = [
                    'bitpayx' => trans('admin.system.payment.channel.bitpayx'),
                    'youzan' => trans('admin.system.payment.channel.youzan'),
                ];
            }

            foreach (self::discover() as $key => $gateway) {
                $labels[$key] = trans("admin.system.payment.channel.$key");
            }

            return $labels ?? [];
        });
    }

    public static function getPaymentsByMethod(string $method): array
    {
        return cache()->rememberForever("{$method}_payments", function () use ($method) {
            foreach (self::discover() as $key => $gateway) {
                if (isset($gateway['metadata']['method']) && in_array($method, $gateway['metadata']['method'], true)) {
                    $lists[trans("admin.system.payment.channel.$key")] = $key;
                }
            }

            return $lists ?? [];
        });
    }
}
