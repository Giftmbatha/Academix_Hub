<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Amazing Team</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-purple-100 to-indigo-200 min-h-screen py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <h1 class="text-4xl font-bold text-center text-gray-800 mb-12">Meet Our Team</h1>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <!-- Team Member 1 -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden transform transition duration-500 hover:scale-105">
                <img src="../uploads/profile_photosGift.jpg" alt="Jane Doe" class="w-full h-64 object-cover object-center">
                <div class="p-6">
                    <h2 class="text-2xl font-semibold text-gray-800 mb-2">Gift Mbatha</h2>
                    <p class="text-gray-600 mb-4">Full-stack Developer</p>
                    <div class="flex justify-center space-x-4">
                        <a href="#" class="text-blue-500 hover:text-blue-600"><i class="fab fa-twitter fa-lg"></i></a>
                        <a href="#" class="text-blue-700 hover:text-blue-800"><i class="fab fa-linkedin fa-lg"></i></a>
                        <a href="#" class="text-gray-800 hover:text-gray-900"><i class="fab fa-github fa-lg"></i></a>
                    </div>
                </div>
            </div>

            <!-- Team Member 2 -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden transform transition duration-500 hover:scale-105">
                <img src="../uploads/profile_photosfour.jpg" alt="" class="w-full h-64 object-cover object-center">
                <div class="p-6">
                    <h2 class="text-2xl font-semibold text-gray-800 mb-2">Lehlohonolo Thebe</h2>
                    <p class="text-gray-600 mb-4">Frontend Developer</p>
                    <div class="flex justify-center space-x-4">
                        <a href="#" class="text-blue-500 hover:text-blue-600"><i class="fab fa-twitter fa-lg"></i></a>
                        <a href="#" class="text-blue-700 hover:text-blue-800"><i class="fab fa-linkedin fa-lg"></i></a>
                        <a href="#" class="text-gray-800 hover:text-gray-900"><i class="fab fa-github fa-lg"></i></a>
                    </div>
                </div>
            </div>

            <!-- Team Member 3 -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden transform transition duration-500 hover:scale-105">
                <img src="../uploads/profile_photosRetha.jpg" alt="Emily Johnson" class="w-full h-64 object-cover object-center">
                <div class="p-6">
                    <h2 class="text-2xl font-semibold text-gray-800 mb-2">Rethabile Choche</h2>
                    <p class="text-gray-600 mb-4">UI/UX Designer</p>
                    <div class="flex justify-center space-x-4">
                        <a href="#" class="text-blue-500 hover:text-blue-600"><i class="fab fa-twitter fa-lg"></i></a>
                        <a href="#" class="text-blue-700 hover:text-blue-800"><i class="fab fa-linkedin fa-lg"></i></a>
                        <a href="#" class="text-pink-500 hover:text-pink-600"><i class="fab fa-dribbble fa-lg"></i></a>
                    </div>
                </div>
            </div>

            <!-- Team Member 4 -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden transform transition duration-500 hover:scale-105">
                <img src="../uploads/profile_photosNthabeleng.jpg" alt="Michael Brown" class="w-full h-64 object-cover object-center">
                <div class="p-6">
                    <h2 class="text-2xl font-semibold text-gray-800 mb-2">Nthabeleng Moleko</h2>
                    <p class="text-gray-600 mb-4">Project Manager</p>
                    <div class="flex justify-center space-x-4">
                        <a href="#" class="text-blue-500 hover:text-blue-600"><i class="fab fa-twitter fa-lg"></i></a>
                        <a href="#" class="text-blue-700 hover:text-blue-800"><i class="fab fa-linkedin fa-lg"></i></a>
                        <a href="#" class="text-gray-800 hover:text-gray-900"><i class="fab fa-github fa-lg"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Add a subtle animation when the page loads
        document.addEventListener('DOMContentLoaded', (event) => {
            const teamMembers = document.querySelectorAll('.transform');
            teamMembers.forEach((member, index) => {
                setTimeout(() => {
                    member.classList.add('opacity-100');
                    member.classList.remove('opacity-0', 'translate-y-4');
                }, index * 200);
            });
        });
    </script>
</body>
</html>