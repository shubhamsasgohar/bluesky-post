<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\UploadedFile;

/**
 * BlueSkyService
 *
 * This service encapsulates all communication with the BlueSky
 * AT Protocol API. It is intentionally stateless and follows
 * the official BlueSky documentation for authentication,
 * image upload, and post creation.
 */
class BlueSkyService
{
    /**
     * Personal Data Server (PDS)
     * Default public BlueSky server
     */
    private string $pds = 'https://bsky.social';

    /* -----------------------------------------------------------------
     | CREATE SESSION (AUTHENTICATION)
     |------------------------------------------------------------------
     | Creates a short-lived session with BlueSky using:
     | - Account handle (identifier)
     | - App Password (not normal password)
     |
     | Returns:
     | - accessToken (JWT, short-lived)
     | - did (Decentralized Identifier of the account)
     |
     | Tokens are NOT stored to keep the flow stateless.
     */
    public function createSession(): array
    {
        $response = Http::asJson()->post(
            "{$this->pds}/xrpc/com.atproto.server.createSession",
            [
                'identifier' => config('services.bluesky.identifier'),
                'password'   => config('services.bluesky.password'),
            ]
        );

        if (!$response->successful()) {
            throw new \Exception('BlueSky authentication failed');
        }

        return [
            'accessToken' => $response['accessJwt'],
            'did'         => $response['did'],
        ];
    }

    /* -----------------------------------------------------------------
     | IMAGE UPLOAD
     |------------------------------------------------------------------
     | Uploads an image to BlueSky as raw bytes.
     |
     | Important:
     | - BlueSky does NOT accept multipart/form-data
     | - Image must be sent as raw bytes
     | - Content-Type must match image/*
     | - Max file size: 1MB
     |
     | Returns:
     | - A "blob" object required for embedding images in posts
     */
    public function uploadImage(string $accessToken, UploadedFile $file): array
    {
        $mime = $file->getMimeType(); // image/png, image/jpeg, etc.

        if (!str_starts_with($mime, 'image/')) {
            throw new \Exception('File must be an image');
        }

        $bytes = file_get_contents($file->getRealPath());

        // BlueSky image size limit (1MB)
        if (strlen($bytes) > 1_000_000) {
            throw new \Exception('Image too large (max 1MB)');
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type'  => $mime,
        ])
            ->withBody($bytes, $mime)
            ->post("{$this->pds}/xrpc/com.atproto.repo.uploadBlob");

        if (!$response->successful()) {
            throw new \Exception('BlueSky image upload failed');
        }

        return $response['blob'];
    }

    /* -----------------------------------------------------------------
     | CREATE POST
     |------------------------------------------------------------------
     | Creates a BlueSky post using the Lexicon schema:
     | app.bsky.feed.post
     |
     | Required fields:
     | - $type
     | - text
     | - createdAt (UTC with trailing Z)
     |
     | Optional:
     | - Image embed (if blob is provided)
     */
    public function createPost(
        string $accessToken,
        string $did,
        string $text,
        ?array $imageBlob = null
    ): void {
        $record = [
            '$type'     => 'app.bsky.feed.post',
            'text'      => $text,
            'createdAt'=> now()->utc()->format('Y-m-d\TH:i:s\Z'),
        ];

        // Attach image embed if provided
        if ($imageBlob) {
            $record['embed'] = [
                '$type'  => 'app.bsky.embed.images',
                'images' => [
                    [
                        'image' => $imageBlob,
                        'alt'   => '',
                    ],
                ],
            ];
        }

        $response = Http::withToken($accessToken)
            ->asJson()
            ->post(
                "{$this->pds}/xrpc/com.atproto.repo.createRecord",
                [
                    'repo'       => $did, // Must be DID, not handle
                    'collection' => 'app.bsky.feed.post',
                    'record'     => $record,
                ]
            );

        if (!$response->successful()) {
            throw new \Exception('BlueSky post creation failed');
        }
    }
}
