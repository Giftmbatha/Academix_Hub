document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('login');
    const signupForm = document.getElementById('signup');
    const showSignupLink = document.getElementById('show-signup');
    

    const urlParams = new URLSearchParams(window.location.search);
        const role = urlParams.get('role');

        // Update the page title based on the role
        if (role) {
            document.querySelector('h1').textContent += ` - ${role.charAt(0).toUpperCase() + role.slice(1)}`;
        }

    // Show signup form when the signup link is clicked
    showSignupLink.addEventListener('click', (e) => {
        e.preventDefault();
        document.getElementById('signup-form').style.display = 'block';
        document.getElementById('login-form').style.display = 'none';
    });

    // Handle login form submission
    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const username = document.getElementById('username').value;
        const password = document.getElementById('password').value;
        const role = role.value;

        // Prepare form data
        const formData = new URLSearchParams();
        formData.append('action', 'login');
        formData.append('username', username);
        formData.append('password', password);
        formData.append('role', role);

        try {
            const response = await fetch('/php/register.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: formData.toString(),
            });

            const data = await response.json();

            if (data.success) {
                localStorage.setItem('user', JSON.stringify(data.user));
                window.location.href = data.redirect;
            } else {
                alert(data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        }
    });

    // Handle signup form submission
    signupForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const username = document.getElementById('new-username').value;
        const password = document.getElementById('new-password').value;
        const email = document.getElementById('email').value;
        const role = role.value;

        // Prepare form data
        const formData = new URLSearchParams();
        formData.append('action', 'signup');
        formData.append('username', username);
        formData.append('password', password);
        formData.append('email', email);
        formData.append('role', role);

        try {
            const response = await fetch('/Pages/login-signup.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: formData.toString(),
            });

            const data = await response.json();

            if (data.success) {
                alert('Account created successfully. Please log in.');
                document.getElementById('signup-form').style.display = 'none';
                document.getElementById('login-form').style.display = 'block';
            } else {
                alert(data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        }
    });
});
