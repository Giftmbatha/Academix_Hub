<?php
session_start();
require_once '../includes/database.php';

// Check if user is logged in and has appropriate role
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'lecturer' && $_SESSION['role'] !== 'student')) {
    header("Location: login-signup.php");
    exit();
}

// Function to calculate GPA
function calculateGPA($grades) {
    if (empty($grades)) return 0;
    $total_points = 0;
    $total_credits = 0;
    foreach ($grades as $grade) {
        $total_points += $grade['score'] * $grade['credits'];
        $total_credits += $grade['credits'];
    }
    return $total_credits > 0 ? $total_points / $total_credits : 0;
}

// Initialize variables
$student = null;
$courses = [];
$overall_gpa = 0;

// Fetch student data
if ($_SESSION['role'] === 'student') {
    $student_id = $_SESSION['user_id'];
} elseif (isset($_GET['student_id'])) {
    $student_id = $_GET['student_id'];
} else {
    $student_id = null;
}

if ($student_id) {
    // Fetch student details
    $stmt = $conn->prepare("SELECT id, username FROM users WHERE id = ?");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $student = $stmt->get_result()->fetch_assoc();
    
    // Fetch enrolled courses and grades
    $stmt = $conn->prepare("
        SELECT c.id, c.course_name, c.course_code, a.title AS assessment_name, g.grade AS score, c.credits
        FROM enrollments e
        JOIN courses c ON e.course_id = c.id
        LEFT JOIN assessments a ON c.id = a.course_id
        LEFT JOIN grades g ON e.student_id = g.student_id AND a.id = g.assessment_id
        WHERE e.student_id = ?
        ORDER BY c.course_name, a.title
    ");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $course_id = $row['id'];
        if (!isset($courses[$course_id])) {
            $courses[$course_id] = [
                'course_name' => $row['course_name'],
                'course_code' => $row['course_code'],
                'credits' => $row['credits'],
                'assessments' => []
            ];
        }
        if ($row['assessment_name']) {
            $courses[$course_id]['assessments'][] = [
                'assessment_name' => $row['assessment_name'],
                'score' => $row['score']
            ];
        }
    }

    // Calculate overall GPA
    $overall_grades = [];
    foreach ($courses as $course) {
        $course_total = 0;
        $assessment_count = count($course['assessments']);
        foreach ($course['assessments'] as $assessment) {
            $course_total += $assessment['score'] ?? 0;
        }
        $course_average = $assessment_count > 0 ? $course_total / $assessment_count : 0;
        $overall_grades[] = [
            'score' => $course_average,
            'credits' => $course['credits']
        ];
    }
    $overall_gpa = calculateGPA($overall_grades);
}

// Fetch all students for admin and lecturer views
if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'lecturer') {
    $stmt = $conn->prepare("SELECT id, username FROM users WHERE role = 'student' ORDER BY username");
    $stmt->execute();
    $all_students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

$conn->close();
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Progress Monitoring</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.13/jspdf.plugin.autotable.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto mt-10 p-4">
        <h1 class="text-3xl font-bold mb-6">Student Progress Monitoring</h1>

        <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'lecturer'): ?>
            <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
                <h2 class="text-xl font-semibold mb-4">Select Student</h2>
                <form method="GET" class="space-y-4" action="../php/student_progress.php">
                    <div>
                        <label for="student_id" class="block text-gray-700 text-sm font-bold mb-2">Student</label>
                        <select id="student_id" name="student_id" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            <option value="">Select a student</option>
                            <?php foreach ($all_students as $s): ?>
                                <option value="<?php echo $s['id']; ?>" <?php echo $student_id == $s['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($s['username']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <button type="submit" class="bg-purple-600 hover:bg-purple-400 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            View Progress
                        </button>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <?php if ($student_id && $student): ?>
            <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
                <h2 class="text-xl font-semibold mb-4">Progress Report: <?php echo htmlspecialchars($student['username']); ?></h2>
                <table id="report-table" class="min-w-full bg-white">
                    <thead>
                        <tr>
                            <th class="py-2 px-4 bg-gray-200 text-left text-sm font-semibold text-gray-700">Course Code</th>
                            <th class="py-2 px-4 bg-gray-200 text-left text-sm font-semibold text-gray-700">Course Name</th>
                            <th class="py-2 px-4 bg-gray-200 text-left text-sm font-semibold text-gray-700">Assessment</th>
                            <th class="py-2 px-4 bg-gray-200 text-left text-sm font-semibold text-gray-700">Score</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($courses as $course): ?>
                            <?php foreach ($course['assessments'] as $assessment): ?>
                                <tr>
                                    <td class="py-2 px-4 border-b border-gray-200"><?php echo htmlspecialchars($course['course_code']); ?></td>
                                    <td class="py-2 px-4 border-b border-gray-200"><?php echo htmlspecialchars($course['course_name']); ?></td>
                                    <td class="py-2 px-4 border-b border-gray-200"><?php echo htmlspecialchars($assessment['assessment_name']); ?></td>
                                    <td class="py-2 px-4 border-b border-gray-200"><?php echo htmlspecialchars($assessment['score']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="mt-6">
                    <h3 class="text-lg font-bold">Overall GPA: <?php echo number_format($overall_gpa, 2); ?></h3>
                    <button onclick="downloadPDF()" class="mt-4 bg-purple-700 hover:bg-purple-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Download Report as PDF</button>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!$student_id): ?>
            <p class="text-gray-700">Please select a student to view progress.</p>
        <?php endif; ?>
    </div>

    <script>
        function downloadPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();

    // Add University Logo
    const logoImg = new Image();
    logoImg.src = "../images/crestview-university-high-resolution-logo-transparent.png"; // Replace with your logo path or base64 encoded image

    logoImg.onload = function() {
        doc.addImage(logoImg, "PNG", 15, 10, 30, 30); // Adjust positioning and size

        // University Name and Timestamp
        doc.setFontSize(18);
        doc.text("CrestView University", 55, 20); // Replace with the actual university name
        doc.setFontSize(10);
        const currentDate = new Date().toLocaleString();
        doc.text(`Generated on: ${currentDate}`, 55, 28); // Generated date and time

        // Report Title
        doc.setFontSize(16);
        doc.text("Progress Report", 15, 50);
        
        // Student Name and GPA
        doc.setFontSize(12);
        doc.text("Student Name: <?php echo htmlspecialchars($student['username']); ?>", 15, 60);
        doc.text("Overall GPA: <?php echo number_format($overall_gpa, 2); ?>", 15, 70);

        // Table for Course Data
        if (doc.autoTable) {
            doc.autoTable({
                html: '#report-table',
                startY: 80,
                styles: { cellPadding: 2, fontSize: 10 }
            });
            doc.save("progress_report.pdf");
        } else {
            console.error("autoTable plugin not loaded.");
            alert("Error: autoTable plugin not loaded.");
        }
    };

    // If logo fails to load
    logoImg.onerror = function() {
        alert("Error loading the logo image.");
    };
}


    </script>
</body>
