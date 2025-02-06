 document.addEventListener('DOMContentLoaded', () => {
        const user = {
            id: '<?php echo $user_id; ?>',
            role: '<?php echo $role; ?>',
            username: '<?php echo $username; ?>'
        };

        const sidebar = document.querySelector('aside ul');
        const mainContent = document.getElementById('main-content');
        const pageTitle = document.getElementById('page-title');

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

        function setupStudentDashboard() {
            pageTitle.textContent = 'Student Dashboard | CrestView';
            sidebar.innerHTML += `
                <li><a href="#" class="block py-2 px-5 rounded hover:bg-gray-700" data-page="my_courses"><i class="fas fa-book px-1"></i>My Courses</a></li>
                <li><a href="#" class="block py-2 px-5 rounded hover:bg-gray-700" data-page="student_progress"><i class="fas fa-chart-bar px-1"></i>Grades</a></li>
                <li><a href="#" class="block py-2 px-5 rounded hover:bg-gray-700" data-page="assessments"><i class="fas fa-file px-1"></i>Assessments</a></li>
            `;
            loadPageContent('dashboard');
        }

        function setupAdminDashboard() {
            pageTitle.textContent = 'Admin Dashboard | CrestView';
            sidebar.innerHTML += `
                <li><a href="#" class="block py-2 px-5 rounded hover:bg-gray-700" data-page="manage_students"><i class="fas fa-users px-1"></i>Students</a></li>
                <li><a href="#" class="block py-2 px-5 rounded hover:bg-gray-700" data-page="manage_courses"><i class="fas fa-book px-1"></i>Manage Courses</a></li>
                <li><a href="#" class="block py-2 px-5 rounded hover:bg-gray-700" data-page="reports"><i class="fas fa-file px-1"></i>Reports</a></li>
                <li><a href="#" class="block py-2 px-5 rounded hover:bg-gray-700" data-page="events"><i class="fas fa-calendar px-1"></i>Events</a></li>
            `;
            loadPageContent('dashboard');
        }

        function setupLecturerDashboard() {
            pageTitle.textContent = 'Lecturer Dashboard | CrestView';
            sidebar.innerHTML += `
                <li><a href="#" class="block py-2 px-5 rounded hover:bg-gray-700" data-page="my-courses"><i class="fas fa-book px-1"></i>My Courses</a></li>
                <li><a href="#" class="block py-2 px-5 rounded hover:bg-gray-700" data-page="my-students"><i class="fas fa-users px-1"></i>My Students</a></li>
                <li><a href="#" class="block py-2 px-5 rounded hover:bg-gray-700" data-page="assignments"><i class="fas fa-file px-1"></i>Assignments</a></li>
                <li><a href="#" class="block py-2 px-5 rounded hover:bg-gray-700" data-page="grades"><i class="fas fa-chart-bar px-1"></i>Grade Submissions</a></li>
                <li><a href="#" class="block py-2 px-5 rounded hover:bg-gray-700" data-page="attendance"><i class="fas fa-list px-1"></i>Attendance</a></li>
            `;
            loadPageContent('dashboard');
        }

        function loadPageContent(page) {
            mainContent.innerHTML = `<p>Loading ${page}...</p>`;
            fetch(`/pages/${page}.php`)
                .then(response => response.text())
                .then(html => {
                    mainContent.innerHTML = html;
                    if (page === 'dashboard') {
                        initializeCalendar();
                    }
                })
                .catch(error => {
                    console.error('Error loading page:', error);
                    mainContent.innerHTML = `<p>Error loading ${page}. Please try again.</p>`;
                });
        }

    
        // Handle page navigation
        sidebar.addEventListener('click', function (e) {
            if (e.target.tagName === 'A') {
                e.preventDefault();
                const page = e.target.dataset.page;
                loadPageContent(page);
            }
        });

        // Sidebar toggle functionality
        const toggleSidebarBtn = document.getElementById('toggle-sidebar');
        const sidebarElement = document.getElementById('sidebar');
        const mainElement = document.querySelector('main');

        toggleSidebarBtn.addEventListener('click', () => {
            sidebarElement.classList.toggle('collapsed');
            toggleSidebarBtn.classList.toggle('sidebar-collapsed');
            mainElement.classList.toggle('ml-0');
            mainElement.classList.toggle('ml-64');
        });

        // Toggle profile dropdown
        const profileToggle = document.getElementById('profile-dropdown-toggle');
        const profileDropdown = document.getElementById('profile-dropdown');

        profileToggle.addEventListener('click', () => {
            profileDropdown.classList.toggle('hidden');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!profileToggle.contains(e.target) && !profileDropdown.contains(e.target)) {
                profileDropdown.classList.add('hidden');
            }
        });

        updateDashboard();
    });