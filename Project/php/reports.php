<?php
// Database connection settings
require_once '../includes/database.php';


// Fetch performance data (average grades per course)
$performanceQuery = "SELECT courses.course_name, AVG(CAST(grades.grade AS DECIMAL(5,2))) as avg_grade 
                    FROM grades 
                    JOIN classes ON grades.class_id = classes.id 
                    JOIN courses ON classes.course_id = courses.id 
                    GROUP BY courses.course_name";
$performanceResult = $conn->query($performanceQuery);

$performanceData = [
    'labels' => [],
    'grades' => []
];
while ($row = $performanceResult->fetch_assoc()) {
    $performanceData['labels'][] = $row['course_name'];
    $performanceData['grades'][] = $row['avg_grade'];
}

// Fetch attendance data (attendance count per day)
$attendanceQuery = "SELECT DATE(attendance_date) as date, COUNT(*) as attendance_count 
                    FROM attendance 
                    GROUP BY DATE(attendance_date)";
$attendanceResult = $conn->query($attendanceQuery);

$attendanceData = [
    'labels' => [],
    'attendance' => []
];
while ($row = $attendanceResult->fetch_assoc()) {
    $attendanceData['labels'][] = $row['date'];
    $attendanceData['attendance'][] = $row['attendance_count'];
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>University Reports</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 text-gray-800">
    <div class="container mx-auto p-6">
        <h1 class="text-4xl font-bold text-center mb-8 text-purple-700">University Reports</h1>

        <!-- Performance Chart -->
        <div class="bg-white shadow-lg rounded-lg p-6 mb-8">
            <h2 class="text-2xl font-semibold text-purple-600 mb-4 flex items-center">
                <span class="material-icons text-purple-600 mr-2">insights</span> Student Performance
            </h2>
            <div class="text-gray-400 mb-4">Average grades by course.</div>
            <div class="chart-icon text-blue-300 text-9xl text-center mb-4">📊</div>
            <canvas id="performanceChart" style="display: none;"></canvas>
        </div>

        <!-- Attendance Chart -->
        <div class="bg-white shadow-lg rounded-lg p-6 mb-8">
            <h2 class="text-2xl font-semibold text-purple-600 mb-4 flex items-center">
                <span class="material-icons text-purple-600 mr-2">date_range</span> Student Attendance
            </h2>
            <div class="text-gray-400 mb-4">Attendance trends over time.</div>
            <div class="chart-icon text-blue-300 text-9xl text-center mb-4">📈</div>
            <canvas id="attendanceChart" style="display: none;"></canvas>
        </div>

        <!-- PDF Button -->
        <div class="text-center">
            <button onclick="downloadPDF()" class="bg-purple-700 hover:bg-purple-600 text-white font-semibold py-2 px-4 rounded-lg shadow-md">
                Download Report as PDF
            </button>
        </div>
    </div>

    <script>
        const performanceData = <?php echo json_encode($performanceData); ?>;
        const attendanceData = <?php echo json_encode($attendanceData); ?>;

        function initPerformanceChart() {
            const ctx = document.getElementById('performanceChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: performanceData.labels,
                    datasets: [{
                        label: 'Average Grade',
                        data: performanceData.grades,
                        backgroundColor: 'rgba(54, 162, 235, 0.6)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1,
                    }]
                },
                options: { scales: { y: { beginAtZero: true } } }
            });
        }

        function initAttendanceChart() {
            const ctx = document.getElementById('attendanceChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: attendanceData.labels,
                    datasets: [{
                        label: 'Attendance Count',
                        data: attendanceData.attendance,
                        fill: false,
                        borderColor: 'rgba(255, 99, 132, 1)',
                    }]
                },
                options: { scales: { y: { beginAtZero: true } } }
            });
        }

        function downloadPDF() {
            const canvas1 = document.getElementById('performanceChart');
            const canvas2 = document.getElementById('attendanceChart');

            canvas1.style.display = 'block';
            canvas2.style.display = 'block';

            const pdf = new jsPDF();

            pdf.text("University Reports", 10, 10);
            pdf.text("Student Performance", 10, 30);
            pdf.addImage(canvas1.toDataURL("image/png"), 'PNG', 10, 40, 180, 80);
            pdf.text("Student Attendance", 10, 130);
            pdf.addImage(canvas2.toDataURL("image/png"), 'PNG', 10, 140, 180, 80);

            pdf.save("University_Reports.pdf");

            canvas1.style.display = 'none';
            canvas2.style.display = 'none';
        }

        document.addEventListener('DOMContentLoaded', () => {
            initPerformanceChart();
            initAttendanceChart();
        });
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
</body>
</html>
