document.addEventListener('DOMContentLoaded', () => {
    const classSelect = document.getElementById('class-select');
    const attendanceList = document.getElementById('attendance-list');

    async function fetchClasses() {
        try {
            const response = await fetch('php/lecturer_classes.php');
            const classes = await response.json();
            displayClasses(classes);
        } catch (error) {
            console.error('Error fetching classes:', error);
            classSelect.innerHTML = '<option>Failed to load classes</option>';
        }
    }

    function displayClasses(classes) {
        classSelect.innerHTML += classes.map(cls => `
            <option value="${cls.id}">${cls.course_name} - ${cls.schedule}</option>
        `).join('');
    }

    async function fetchStudents(classId) {
        try {
            const response = await fetch(`php/lecturer_students.php?class_id=${classId}`);
            const students = await response.json();
            displayAttendanceForm(students);
        } catch (error) {
            console.error('Error fetching students:', error);
            attendanceList.innerHTML = '<p class="text-red-500">Failed to load students. Please try again later.</p>';
        }
    }

    function displayAttendanceForm(students) {
        attendanceList.innerHTML = `
            <form id="attendance-form">
                ${students.map(student => `
                    <div class="mb-4">
                        <label class="block mb-2">
                            <span class="text-gray-700">${student.name}</span>
                            <select name="attendance[${student.id}]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <option value="present">Present</option>
                                <option value="absent">Absent</option>
                                <option value="late">Late</option>
                            </select>
                        </label>
                    </div>
                `).join('')}
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Submit Attendance</button>
            </form>
        `;

        document.getElementById('attendance-form').addEventListener('submit', submitAttendance);
    }

    async function submitAttendance(e) {
        e.preventDefault();
        const formData = new FormData(e.target);
        try {
            const response = await fetch('php/lecturer_submit_attendance.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            if (result.success) {
                alert('Attendance submitted successfully');
            } else {
                alert('Failed to submit attendance');
            }
        } catch (error) {
            console.error('Error submitting attendance:', error);
            alert('An error occurred. Please try again.');
        }
    }

    classSelect.addEventListener('change', (e) => {
        const classId = e.target.value;
        if (classId) {
            fetchStudents(classId);
        } else {
            attendanceList.innerHTML = '';
        }
    });

    fetchClasses();
});