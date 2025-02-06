document.addEventListener('DOMContentLoaded', function () {
  const addUserBtn = document.getElementById('addUserBtn');
        const userForm = document.getElementById('userForm');
        const cancelBtn = document.getElementById('cancelBtn');
        const formTitle = document.getElementById('formTitle');

        // Show the form when 'Add New User' is clicked
        addUserBtn.addEventListener('click', () => {
            userForm.classList.remove('hidden');
            formTitle.textContent = 'Add New User';  // Set title for adding a new user
            document.getElementById('user_id').value = '';  // Reset user_id
            document.getElementById('username').value = '';
            document.getElementById('email').value = '';
            document.getElementById('password').value = '';
            document.getElementById('role').value = 'student';  // Default role
        });

        // Cancel button to hide the form
        cancelBtn.addEventListener('click', () => {
            userForm.classList.add('hidden');
        });

        // Function to populate the form for editing a user
        function editUser(id, username, email, role) {
            userForm.classList.remove('hidden');
            formTitle.textContent = 'Edit User';  // Set title for editing a user
            document.getElementById('user_id').value = id;
            document.getElementById('username').value = username;
            document.getElementById('email').value = email;
            document.getElementById('role').value = role;
        }
});
