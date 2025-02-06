<?php
session_start();
require '../includes/database.php';

$message = '';
$message_type = '';
$assessments = [];

// Handle form submission (grading)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_POST['student_id'];
    $course_id = $_POST['course_id'];
    $grade = $_POST['grade'];
    $assessment_id = $_POST['assessment_id'];

    // Fetch the class_id based on the assessment_id
    $class_stmt = $conn->prepare("SELECT class_id FROM assessments WHERE id = ?");
    $class_stmt->bind_param("i", $assessment_id);
    $class_stmt->execute();
    $class_stmt->bind_result($class_id);
    $class_stmt->fetch();
    $class_stmt->close();

    if ($student_id && $course_id && $grade && $assessment_id && $class_id) {
        // Insert or update the grade along with class_id
        $stmt = $conn->prepare("INSERT INTO grades (student_id, course_id, class_id, grade, assessment_id) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE grade = VALUES(grade)");
        $stmt->bind_param("iiisi", $student_id, $course_id, $class_id, $grade, $assessment_id);

        if ($stmt->execute()) {
            $message = "Grade recorded successfully!";
            $message_type = "success";
        } else {
            $message = "Failed to record grade: " . $stmt->error;
            $message_type = "error";
        }
        $stmt->close();
    } else {
        $message = "Please fill all the fields!";
        $message_type = "error";
    }
}

// Fetch students
$query = "SELECT * FROM users WHERE role = 'student'";
$students_result = $conn->query($query);

// Fetch courses, assessments, and their relationships
$lecturer_id = $_SESSION['user_id']; // Assuming lecturer ID is stored in session

$query = "SELECT co.id AS course_id, co.course_name, a.id AS assessment_id, a.title
          FROM courses co
          JOIN classes c ON co.id = c.course_id
          JOIN assessments a ON c.id = a.class_id
          WHERE c.lecturer_id = ?
          ORDER BY a.due_date DESC";
$classes_stmt = $conn->prepare($query);
$classes_stmt->bind_param("i", $lecturer_id);
$classes_stmt->execute();
$classes_result = $classes_stmt->get_result();

// Organize assessments by course
$courses_with_assessments = [];
while ($row = $classes_result->fetch_assoc()) {
    $courses_with_assessments[$row['course_id']]['course_name'] = $row['course_name'];
    $courses_with_assessments[$row['course_id']]['assessments'][] = [
        'id' => $row['assessment_id'],
        'title' => $row['title']
    ];
}

// Fetch submitted assessments with grades for grading
$grades_query = "SELECT u.username AS student_name, co.course_name, a.title AS assessment_title, g.grade, s.submission_date, s.file_path
                 FROM assessment_submissions s
                 LEFT JOIN grades g ON s.assessment_id = g.assessment_id AND s.student_id = g.student_id
                 JOIN users u ON s.student_id = u.id
                 JOIN assessments a ON s.assessment_id = a.id
                 JOIN classes c ON a.class_id = c.id
                 JOIN courses co ON c.course_id = co.id
                 WHERE c.lecturer_id = ?
                 ORDER BY s.submission_date DESC";
$grades_stmt = $conn->prepare($grades_query);
$grades_stmt->bind_param("i", $lecturer_id);
$grades_stmt->execute();
$grades_result = $grades_stmt->get_result();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grading & Assessments</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
    <script>
        function toggleAssessments(courseId) {
            // Hide all assessments
            const allAssessments = document.querySelectorAll('.assessment-options');
            allAssessments.forEach(assessment => {
                assessment.style.display = 'none';
            });

            // Show the assessments for the selected course
            const selectedAssessments = document.getElementById('assessments_' + courseId);
            if (selectedAssessments) {
                selectedAssessments.style.display = 'block';
            }
        }
    </script>
</head>
<body>
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-8">Grading & Assessments</h1>

        <?php if ($message): ?>
            <div class="mb-6 p-4 rounded-md <?php echo $message_type === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Grade Recording Form -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Record Grade</h2>
                <form action="../php/grading_assessments.php" method="POST" class="space-y-4">
                    <div>
                        <label for="student_id" class="block text-sm font-medium text-gray-700">Student</label>
                        <select id="student_id" name="student_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                            <option value="">Select a student</option>
                            <?php while ($student = $students_result->fetch_assoc()): ?>
                                <option value="<?php echo $student['id']; ?>"><?php echo htmlspecialchars($student['username']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div>
                        <label for="course_id" class="block text-sm font-medium text-gray-700">Course</label>
                        <select id="course_id" name="course_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required onchange="toggleAssessments(this.value)">
                            <option value="">Select a course</option>
                            <?php foreach ($courses_with_assessments as $course_id => $course_data): ?>
                                <option value="<?php echo $course_id; ?>"><?php echo htmlspecialchars($course_data['course_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                   <div>
                    <label for="assessment_id" class="block text-sm font-medium text-gray-700">Assessment</label>
                    <?php foreach ($courses_with_assessments as $course_id => $course_data): ?>
                        <select id="assessments_<?php echo $course_id; ?>" name="assessment_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 assessment-options" style="display:none;">
                            <option value="">Select an assessment</option>
                            <?php foreach ($course_data['assessments'] as $assessment): ?>
                                <option value="<?php echo $assessment['id']; ?>"><?php echo htmlspecialchars($assessment['title']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php endforeach; ?>
                </div>
                    <div>
                        <label for="grade" class="block text-sm font-medium text-gray-700">Grade</label>
                        <input type="text" id="grade" name="grade" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                    </div>
                    <div>
                        <button type="submit" class="w-full bg-purple-600 text-white font-bold py-2 px-4 rounded-md hover:bg-purple-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            Record Grade
                        </button>
                    </div>
                </form>
            </div>

            <!-- Graded Assessments Table -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Submitted Assessments & Grades</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3">Student</th>
                                <th class="px-6 py-3">Course</th>
                                <th class="px-6 py-3">Assessment</th>
                                <th class="px-6 py-3">Grade</th>
                                <th class="px-6 py-3">Submission Date</th>
                                <th class="px-6 py-3">Download</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php while ($grade = $grades_result->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($grade['student_name']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($grade['course_name']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($grade['assessment_title']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($grade['grade'] ?? 'Not graded'); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($grade['submission_date']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if ($grade['file_path']): ?>
                                            <a href="<?php echo htmlspecialchars($grade['file_path']); ?>" target="_blank" class="text-blue-500 hover:underline">Download</a>
                                        <?php else: ?>
                                            <span class="text-gray-500">No file</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
<script>
    function toggleAssessments(courseId) {
        // Hide all assessments
        const allAssessments = document.querySelectorAll('.assessment-options');
        allAssessments.forEach(assessment => {
            assessment.style.display = 'none';
        });

        // Show the assessments for the selected course if the courseId is valid
        if (courseId) {
            const selectedAssessments = document.getElementById('assessments_' + courseId);
            if (selectedAssessments) {
                selectedAssessments.style.display = 'block';
            }
        }
    }
</script>

</html>
