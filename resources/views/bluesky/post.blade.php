<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create BlueSky Post</title>

    <!--
        Tailwind CSS CDN
        Used for quick UI styling for the assessment.
        Keeps frontend simple and focused on functionality.
    -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 p-10">
<!--
    Main container
    - Centered layout
    - Clean card-style UI
    - Focused on post creation only
-->
<div class="max-w-3xl mx-auto bg-white p-6 rounded shadow">
    <!-- Page heading -->
    <h1 class="text-xl font-semibold mb-4">
        Create Post (BlueSky)
    </h1>

    <!--
        Success message
        Displayed after a successful post submission
    -->
    @if(session('success'))
        <div class="bg-green-100 text-green-800 p-2 rounded mb-3">
            {{ session('success') }}
        </div>
    @endif

    <!--
            Post creation form
            - Submits text and optional image
            - Uses POST method
            - Protected with CSRF token
        -->
    <form
        method="POST"
        action="{{ route('bluesky.post') }}"
        enctype="multipart/form-data"
    >
        @csrf

        <!--
                Text input for BlueSky post content
                - Required field
                - Max length enforced server-side (300 chars)
            -->
        <textarea
            name="text"
            class="w-full border p-3 rounded mb-4"
            rows="4"
            placeholder="What's happening?"
            required
        ></textarea>

        <!--
            Optional image upload
            - Supported formats: PNG / JPG / WEBP
            - Image validation handled server-side
        -->
        <input
            type="file"
            name="image"
            class="mb-4"
        >

        <!--
            Submit button
            Triggers:
            - BlueSky session creation
            - Optional image upload
            - Post creation via AT Protocol
        -->
        <button
            type="submit"
            class="bg-blue-600 text-white px-4 py-2 rounded"
        >
            Post to BlueSky
        </button>
    </form>
</div>
</body>
</html>
