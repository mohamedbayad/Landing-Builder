<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unsubscribed</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-6">
    <div class="max-w-lg w-full bg-white rounded-xl border border-gray-200 shadow-sm p-8 text-center">
        <h1 class="text-2xl font-semibold text-gray-900">You Have Been Unsubscribed</h1>
        <p class="mt-3 text-sm text-gray-600">
            {{ $contact->email }} will no longer receive automation emails from this funnel.
        </p>
    </div>
</body>
</html>

