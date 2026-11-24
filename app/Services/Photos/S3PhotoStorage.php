<?php

namespace App\Services\Photos;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/**
 * Minimal client for Supabase Storage's S3-compatible API: signed PUT and DELETE of single objects.
 * Hand-rolled SigV4 keeps the AWS SDK (tens of MB) out of the container for two calls.
 */
final class S3PhotoStorage
{
    private const SERVICE = 's3';

    private const TIMEOUT_SECONDS = 30;

    public function put(string $key, string $body, string $contentType): void
    {
        $this->send('PUT', $key, $body, ['Content-Type' => $contentType, 'Cache-Control' => 'public, max-age=31536000, immutable']);
    }

    public function delete(string $key): void
    {
        $this->send('DELETE', $key, '', []);
    }

    /**
     * @param  array<string, string>  $headers
     */
    private function send(string $method, string $key, string $body, array $headers): void
    {
        $config = $this->config();
        $path = parse_url($config['endpoint'], PHP_URL_PATH).'/'.$config['bucket'].'/'.$key;
        $uri = implode('/', array_map('rawurlencode', explode('/', $path)));
        $host = (string) parse_url($config['endpoint'], PHP_URL_HOST);
        $now = now('UTC');
        $amzDate = $now->format('Ymd\THis\Z');
        $date = $now->format('Ymd');
        $payloadHash = hash('sha256', $body);

        $signed = ['host' => $host, 'x-amz-content-sha256' => $payloadHash, 'x-amz-date' => $amzDate];
        ksort($signed);
        $canonicalHeaders = implode('', array_map(fn ($k, $v) => "{$k}:{$v}\n", array_keys($signed), $signed));
        $signedHeaders = implode(';', array_keys($signed));
        $canonicalRequest = implode("\n", [$method, $uri, '', $canonicalHeaders, $signedHeaders, $payloadHash]);

        $scope = "{$date}/{$config['region']}/".self::SERVICE.'/aws4_request';
        $stringToSign = implode("\n", ['AWS4-HMAC-SHA256', $amzDate, $scope, hash('sha256', $canonicalRequest)]);
        $signingKey = array_reduce(
            [$date, $config['region'], self::SERVICE, 'aws4_request'],
            fn (string $key, string $part) => hash_hmac('sha256', $part, $key, true),
            'AWS4'.$config['secret'],
        );
        $signature = hash_hmac('sha256', $stringToSign, $signingKey);

        $headers += [
            'Authorization' => "AWS4-HMAC-SHA256 Credential={$config['key']}/{$scope}, SignedHeaders={$signedHeaders}, Signature={$signature}",
            'x-amz-content-sha256' => $payloadHash,
            'x-amz-date' => $amzDate,
        ];

        try {
            $response = Http::timeout(self::TIMEOUT_SECONDS)
                ->withHeaders($headers)
                ->withBody($body, $headers['Content-Type'] ?? 'application/octet-stream')
                ->send($method, 'https://'.$host.$uri);
        } catch (ConnectionException $e) {
            throw new PhotoStorageException(__('Penyimpanan foto tidak bisa dihubungi. Coba lagi sebentar lagi.'), previous: $e);
        }

        if ($response->failed()) {
            report(new PhotoStorageException("Storage {$method} {$key} failed with HTTP {$response->status()}: ".substr($response->body(), 0, 300)));

            throw new PhotoStorageException(__('Foto gagal disimpan (kode :status). Coba lagi atau hubungi pengelola.', ['status' => $response->status()]));
        }
    }

    /**
     * @return array{endpoint: string, region: string, bucket: string, key: string, secret: string}
     */
    private function config(): array
    {
        $config = config('patung.photo_storage');

        foreach (['endpoint', 'region', 'bucket', 'key', 'secret'] as $name) {
            if (blank($config[$name] ?? null)) {
                throw new PhotoStorageException(__('Penyimpanan foto belum dikonfigurasi.'));
            }
        }

        return $config;
    }
}
