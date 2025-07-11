<?php

namespace App\Services\Payment;

use GeoIp2\Database\Reader;

class AntiFraudService
{
    /**
     * Score a transaction based on basic rules.
     *
     * @param array $data
     * @return float Risk score between 0 and 1
     */
    public function scoreTransaction(array $data): float
    {
        $score = 0.0;
        // Example: Geolocation
        if (!empty($data['ip'])) {
            try {
                $reader = new Reader(storage_path('geoip/GeoLite2-Country.mmdb'));
                $record = $reader->country($data['ip']);
                // If country is blacklisted, increase score
                $blacklist = ['XY']; // example country codes
                if (in_array($record->country->isoCode, $blacklist)) {
                    $score += 0.5;
                }
            } catch (\Exception $e) {
                // ignore geoip failures
            }
        }
        // Example: amount threshold
        if (!empty($data['amount']) && $data['amount'] > 10000) {
            $score += 0.3;
        }
        return min($score, 1.0);
    }
}
