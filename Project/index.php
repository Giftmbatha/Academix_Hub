<?php
session_start();
include_once('./includes/database.php');



// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../php/login-signup.php');
    exit;
}
// Fetch user details
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$role = $_SESSION['role'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/jpg" href="/images/crestview-university-high-resolution-logo-transparent.png" alt="logo">
    <title id="page-title">Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="/css/styles.css"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .sidebar {
            transition: all 0.3s ease-in-out;
            
        }
        .sidebar.collapsed {
            width: 5rem;
        }
        .sidebar.collapsed .sidebar-text {
            display: none;
        }
        .main-content {
            transition: margin-left 0.3s ease-in-out;
        }
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.open {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0 !important;
            }
        }
        .notification-badge {
            @apply absolute -top-1 -right-1 h-4 w-4 bg-red-500 rounded-full flex items-center justify-center text-xs text-white font-bold;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside id="sidebar" class="sidebar bg-gradient-to-b from-purple-600 to-purple-700 text-white w-64 fixed inset-y-0 left-0 z-30 overflow-y-auto">
            <div class="flex items-center justify-center p-4">
                <img src="/images/crestview-university-high-resolution-logo-white-transparent.png" alt="Logo" class="w-32">
            </div>
            <nav class="mt-8">
                <ul class="space-y-2 px-4">
                    <!-- Dynamic menu items will be added here by JavaScript -->
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <div id="main-content" class="main-content flex-1 flex flex-col overflow-hidden ml-64">
            <!-- Header -->
            <header class="bg-white shadow-md p-4">
                <div class="flex items-center justify-between">
                    <!-- Toggle Sidebar Button -->
                    <button id="toggle-sidebar" class="text-purple-600 focus:outline-none">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>

                    <!-- Search Bar -->
                    <div class="relative flex-1 max-w-xl mx-4 hidden sm:block">
                        <input type="text" placeholder="Search..." class="w-full pl-10 pr-4 py-2 rounded-full border border-gray-300 focus:outline-none focus:ring-2 focus:ring-purple-600">
                        <div class="absolute left-3 top-2">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                    </div>

                    <!-- Right Side: Notifications and User Profile -->
                    <div class="flex items-center space-x-4">
                        <!-- Notifications -->
                                <?php
                                $sql = "SELECT COUNT(*) as notification_count FROM communications";
                                $result = $conn->query($sql);

                                if ($result) {
                                    $row = $result->fetch_assoc();
                                    $notification_count = $row['notification_count'];
                                    $notification_count; // Debugging line
                                } else {
                                    echo "Query failed: " . $conn->error; // Debugging line
                                    $notification_count = 0;
                                }

                                ?>
                        <button class="relative p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-600 focus:bg-gray-100 focus:text-gray-600 rounded-full" data-page="communication">
                            <span class="sr-only">Notifications</span>
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118.5 14.5V11a6.5 6.5 0 00-13 0v3.5c0 .53-.21 1.04-.584 1.415L4.5 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                            </svg>
                            <span class="notification-badge"><?php echo $notification_count; ?></span>
                        </button>

                        <!-- Messages -->
                        <button class="relative p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-600 focus:bg-gray-100 focus:text-gray-600 rounded-full" data-page="chats">
                            <span class="sr-only">Messages</span>
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            <span class="notification-badge">99</span>
                        </button>

                        <!-- User Profile -->
                         <?php

                        $user_id = $_SESSION['user_id']; 
                        $query = "SELECT username, profile_photo FROM users WHERE id = ?";
                        $stmt = $conn->prepare($query);
                        $stmt->bind_param("i", $user_id);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $user = $result->fetch_assoc();

                        $username = $user['username'];
                        $profile_photo = $user['profile_photo'];

                        // Fallback if user has not uploaded a profile photo
                        if (empty($profile_photo)) {
                            $profile_photo = 'default_profile.png'; // Path to a default profile image
                        }
                        ?>
                        <div class="relative">
                            <button id="profile-dropdown-toggle" class="flex items-center space-x-2 focus:outline-none">
                                <img src="/uploads/<?php echo $profile_photo; ?>" alt="Profile" class="w-10 h-10 rounded-full">
                                <span class="text-gray-700 font-medium hidden md:inline"><?php echo $username; ?></span>
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            
                            <!-- Profile Dropdown -->
                            <div id="profile-dropdown" class="absolute right-0 mt-2 w-48 bg-white shadow-lg rounded-lg hidden">
                                <form action="../php/profile.php" method="POST" enctype="multipart/form-data" class="px-4 py-3">
                                    <label for="profile_photo" class="block text-sm font-medium text-gray-700">Upload Profile Photo</label>
                                    <input type="file" id="profile_photo" name="profile_photo" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100">
                                    <button type="submit" class="mt-2 w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded-full">Upload</button>
                                </form>
                                <div class="border-t my-2"></div>
                                <a href="#" class="block px-4 py-2 text-gray-700 hover:bg-gray-100" data-page="profile">View Profile</a>
                                <a href="/php/logout.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Logout</a>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Main Content -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto p-4">
                <header class="mb-4">
                    <p class="text-lg">
                        Welcome, <span class="font-bold text-purple-600"><?php echo $username; ?></span>. 
                        You are logged in as a <span class="font-medium"><?php echo $role; ?></span>.
                    </p>
                </header>
                
                <div id="main-content-area">
                    <!-- Dynamic content will be loaded here -->
                </div>
            </main>

            <!-- Footer -->
            <footer class="bg-white text-purple-600 text-center p-4">
                &copy; 2024 AcademixHub. All rights reserved.
            </footer>
        </div>
    </div>

    
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const user = {
            id: '<?php echo $user_id; ?>',
            role: '<?php echo $role; ?>',
            username: '<?php echo $username; ?>'
        };

        const sidebar = document.querySelector('#sidebar ul');
        const mainContentArea = document.getElementById('main-content-area');
        const pageTitle = document.getElementById('page-title');
        const toggleSidebarBtn = document.getElementById('toggle-sidebar');
        const sidebarElement = document.getElementById('sidebar');
        const mainContentElement = document.getElementById('main-content');
        const profileToggle = document.getElementById('profile-dropdown-toggle');
        const profileDropdown = document.getElementById('profile-dropdown');

        async function loadContent(page) {
            try {
                const response = await fetch(`${page}.html`); // Fetch content from page.html
                if (!response.ok) {
                    throw new Error(`Error loading ${page}.html`);
                }
                const content = await response.text();
                document.getElementById("content-area").innerHTML = content;
            } catch (error) {
                console.error(error);
                document.getElementById("content-area").innerHTML = "<p>Failed to load content. Please try again later.</p>";
            }
        }

        function updateDashboard() {
            switch (user.role) {
                case 'student':
                    setupStudentDashboard();
                    break;
                case 'admin':
                    setupAdminDashboard();
                    break;
                case 'lecturer':
                    setupLecturerDashboard();
                    break;
            }
        }

        function createSidebarLink(page, icon, text) {
            return `
                <li>
                    <a href="#" class="flex items-center space-x-3 p-2 rounded-lg hover:bg-purple-700" data-page="${page}">
                        <i class="${icon} w-6"></i>
                        <span class="sidebar-text">${text}</span>
                    </a>
                </li>
            `;
        }

        function setupStudentDashboard() {
            pageTitle.textContent = 'Student Dashboard | CrestView';
            sidebar.innerHTML = `
                ${createSidebarLink("student_dashboard", "fas fa-home", "Dashboard")}
                ${createSidebarLink("my_courses", "fas fa-book", "My Courses")}
                ${createSidebarLink("student_progress", "fas fa-chart-bar", "Grades")}
                ${createSidebarLink("assessments_submission", "fas fa-file", "Assessments")}
                ${createSidebarLink("calendar", "fas fa-calendar", "Calendar")}
                ${createSidebarLink("chats", "fas fa-message", "Chat")}
                ${createSidebarLink("communication", "fas fa-bell", "Announcements")}
                ${createSidebarLink("team", "fas fa-users", "Team")}
            `;
            loadPageContent('student_dashboard');
        }

        function setupAdminDashboard() {
            pageTitle.textContent = 'Admin Dashboard | CrestView';
            sidebar.innerHTML = `
                ${createSidebarLink("admin_dashboard", "fas fa-home", "Dashboard")}
                ${createSidebarLink("manage_users", "fas fa-users", "Users")}
                ${createSidebarLink("manage_courses", "fas fa-book", "Manage Courses")}
                ${createSidebarLink("reports", "fas fa-file", "Reports")}
                ${createSidebarLink("events", "fas fa-calendar", "Events")}
                ${createSidebarLink("class_scheduling", "fas fa-clock", "Classes")}
                ${createSidebarLink("chats", "fas fa-message", "Chat")}
                ${createSidebarLink("communication", "fas fa-bell", "Announcements")}
                ${createSidebarLink("team", "fas fa-users", "Team")}
            `;
            loadPageContent('admin_dashboard');
        }

        function setupLecturerDashboard() {
            pageTitle.textContent = 'Lecturer Dashboard | CrestView';
            sidebar.innerHTML = `
                ${createSidebarLink("lecturer_dashboard", "fas fa-home", "Dashboard")}
                ${createSidebarLink("my_courses", "fas fa-book", "My Courses")}
                ${createSidebarLink("my_students", "fas fa-users", "My Students")}
                ${createSidebarLink("student_progress", "fas fa-users", "Students Progress")}
                ${createSidebarLink("assessments", "fas fa-file", "Assessments")}
                ${createSidebarLink("grading_assessments", "fas fa-chart-bar", "Grade Submissions")}
                ${createSidebarLink("attendance_tracking", "fas fa-list", "Attendance")}
                ${createSidebarLink("calendar", "fas fa-calendar", "Calendar")}
                ${createSidebarLink("chats", "fas fa-message", "Chat")}
                ${createSidebarLink("communication", "fas fa-bell", "Announcements")}
                ${createSidebarLink("team", "fas fa-users", "Team")}
            `;
            loadPageContent('lecturer_dashboard');
        }

        function loadPageContent(page) {
            mainContentArea.innerHTML = `<p>Loading ${page}...</p>`;
            fetch(`/php/${page}.php`)
                .then(response => response.text())
                .then(html => {
                    mainContentArea.innerHTML = html;
                    if (page === 'dashboard') {
                        initializeCalendar();
                    }
                })
                .catch(error => {
                    console.error('Error loading page:', 
                    error);
                    mainContentArea.innerHTML = `<p>Error loading ${page}. Please try again.</p>`;
                });
        }

        function toggleSidebar() {
            const isMobile = window.innerWidth < 768;
            sidebarElement.classList.toggle(isMobile ? 'open' : 'collapsed');
            if (!isMobile) {
                mainContentElement.style.marginLeft = sidebarElement.classList.contains('collapsed') ? '5rem' : '16rem';
            }
        }

        function initializeCalendar() {
            const calendarEl = document.getElementById('calendar');
            if (calendarEl) {
                const calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek,timeGridDay'
                    },
                    events: [
                        // You can add events here or load them from an API
                    ]
                });
                calendar.render();
            }
        }

        // Handle page navigation for sidebar and header items
        document.addEventListener('click', function (e) {
            const target = e.target.closest('a[data-page], button[data-page]');
            if (target && target.dataset.page) {
                e.preventDefault();
                loadPageContent(target.dataset.page);
                if (window.innerWidth < 768) {
                    toggleSidebar();
                }
            }
        });

        // Sidebar toggle functionality
        toggleSidebarBtn.addEventListener('click', toggleSidebar);

        // Toggle profile dropdown
        profileToggle.addEventListener('click', () => {
            profileDropdown.classList.toggle('hidden');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!profileToggle.contains(e.target) && !profileDropdown.contains(e.target)) {
                profileDropdown.classList.add('hidden');
            }
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', (e) => {
            if (window.innerWidth < 768 && sidebarElement.classList.contains('open') && 
                !sidebarElement.contains(e.target) && !toggleSidebarBtn.contains(e.target)) {
                toggleSidebar();
            }
        });

        // Handle window resize
        window.addEventListener('resize', () => {
            const isMobile = window.innerWidth < 768;
            if (isMobile) {
                sidebarElement.classList.remove('collapsed');
                sidebarElement.classList.remove('open');
                mainContentElement.style.marginLeft = '0';
            } else {
                sidebarElement.classList.remove('open');
                if (sidebarElement.classList.contains('collapsed')) {
                    mainContentElement.style.marginLeft = '5rem';
                } else {
                    mainContentElement.style.marginLeft = '16rem';
                }
            }
        });

        updateDashboard();

        

        
    });
    </script>
</body>
</html>