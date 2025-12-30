<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\BlueSkyService;

class BlueSkyController extends Controller
{
    /**
     * Display the BlueSky post creation page
     *
     * GET /post
     * Renders a simple UI where the user can:
     * - Write post text
     * - Upload an optional image
     */
    public function index()
    {
        return view('bluesky.post');
    }

    /**
     * Handle BlueSky post submission
     *
     * POST /post
     * This method follows the official BlueSky AT Protocol flow:
     *
     * 1. Create a short-lived BlueSky session (access token)
     * 2. Upload image as raw bytes (if provided)
     * 3. Create the post using the same access token
     *
     * The flow is intentionally stateless:
     * - No tokens are stored
     * - No credentials are exposed to the frontend
     */
    public function store(Request $request, BlueSkyService $blueSky)
    {
        /**
         * Validate user input
         * - Text is required (BlueSky post limit: 300 chars)
         * - Image is optional and must be a valid image file
         */
        $request->validate([
            'text'  => 'required|string|max:300',
            'image' => 'nullable|image|max:2048',
        ]);

        /**
         * Step 1: Create a BlueSky session
         * Returns:
         * - accessToken (short-lived JWT)
         * - did (Decentralized Identifier of the account)
         */
        $session = $blueSky->createSession();

        /**
         * Step 2: Upload image (optional)
         * BlueSky requires raw image bytes with a proper MIME type.
         */
        $blob = null;
        if ($request->hasFile('image')) {
            $blob = $blueSky->uploadImage(
                $session['accessToken'],
                $request->file('image')
            );
        }

        /**
         * Step 3: Create the BlueSky post
         * - Uses DID as repo
         * - Includes required Lexicon fields ($type, text, createdAt)
         * - Optionally embeds uploaded image
         */
        $blueSky->createPost(
            $session['accessToken'],
            $session['did'],
            $request->text,
            $blob
        );

        /**
         * Redirect back with success message
         */
        return back()->with('success', 'Posted to BlueSky 🎉');
    }
}
