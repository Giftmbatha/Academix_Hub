
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pick Your Role | CrestView</title>
    <link rel="icon" type="image/jpg" href="/images/crestview-university-high-resolution-logo-transparent.png" alt="logo">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex flex-col items-center justify-center h-screen">
    <!-- Role Selection Section -->
    <div class="text-center mb-10">
        <h2 class="text-3xl font-bold">What Is Your Role?</h2>
    </div>
    <div id = "roleSelect" class="flex space-x-10">
        <!-- Role Card -->
        <div class="bg-gradient-to-br from-purple-500 to-indigo-600 text-white p-10 rounded-lg shadow-lg hover:shadow-2xl cursor-pointer">
            <a href="/php/login-signup.php?role=admin">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-xl font-semibold">Admin</p>

            </a>
            
        </div>
        <div class="bg-gradient-to-br from-purple-500 to-indigo-600 text-white p-10 rounded-lg shadow-lg hover:shadow-2xl cursor-pointer">
            <a href="/php/login-signup.php?role=student">
                   <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path d="M12 14l9-5-9-5-9 5 9 5z" />
                    <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
                </svg>
                <p class="text-xl font-semibold">Student</p>

            </a>
            
        </div>
        <div class="bg-gradient-to-br from-purple-500 to-indigo-600 text-white p-10 rounded-lg shadow-lg hover:shadow-2xl cursor-pointer">
            <a href="/php/login-signup.php?role=lecturer">
                   <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
                </svg>
                <p class="text-xl font-semibold">Lecturer</p>

            </a>
            
        
        </div>
    </div>
    <script src="/js/auth.js"></script>
     <script>
        function selectRole(role) {
            // Redirect to the login-signup.php page with the selected role as a query parameter
            window.location.href = '/php/login-signup.php?role=' + role;
        }
    </script>
   
</body>
</html>
