document.addEventListener('DOMContentLoaded', () => {
    const coursesList = document.getElementById('courses-list');

    async function fetchCourses() {
        try {
            const response = await fetch('/php/student_courses.php');
            const courses = await response.json();
            displayCourses(courses);
        } catch (error) {
            console.error('Error fetching courses:', error);
            coursesList.innerHTML = '<p class="text-red-500">Failed to load courses. Please try again later.</p>';
        }
    }

    function displayCourses(courses) {
        coursesList.innerHTML = courses.map(course => `
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold mb-2">${course.course_name}</h2>
                <p class="text-gray-600 mb-2">Code: ${course.course_code}</p>
                <p class="text-gray-600 mb-4">${course.description}</p>
                <p class="text-sm text-gray-500">Credits: ${course.credits}</p>
            </div>
        `).join('');
    }

    fetchCourses();
});