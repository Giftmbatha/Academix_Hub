<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logged Out - CrestView University</title>
    <link rel="icon" type="image/jpg" href="/images/crestview-university-high-resolution-logo-transparent.png" alt="logo">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl overflow-hidden">
        <div class="p-8">
            <div class="text-center mb-8">
                <div class="inline-block p-4 bg-indigo-100 rounded-full mb-4">
                    <svg class="w-16 h-16 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h2 class="text-3xl font-bold text-gray-800 mb-2">You've Been Logged Out</h2>
                <p class="text-gray-600">
                    Thank you for using CrestView University portal. We hope to see you again soon!
                </p>
            </div>
            <div class="space-y-4">
                <a href="role.php" class="block w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-4 rounded-lg transition duration-300 text-center">
                    <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                    </svg>
                    Log In Again
                </a>
                <a href="/home.php" class="block w-full border border-indigo-600 text-indigo-600 font-semibold py-3 px-4 rounded-lg transition duration-300 text-center hover:bg-indigo-50">
                    <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    Go to Homepage
                </a>
            </div>
        </div>
        <div class="px-8 py-6 bg-gray-50 border-t border-gray-100">
            <p class="text-center text-gray-600 text-sm">
                © 2024 CrestView University. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>