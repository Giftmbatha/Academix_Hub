<?php
session_start();
require_once '../includes/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login-signup.php");
    exit();
}

// Fetch upcoming events and due dates for assessments
$query = "
    SELECT title, description, start_date, end_date
    FROM events
    ORDER BY start_date ASC
    LIMIT 10
";
$result = $conn->query($query);

if (!$result) {
    die("Query failed: " . $conn->error);
}

$events = $result->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendar</title>
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
       
    </style>
</head>
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-8">Calendar & Upcoming Events</h1>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Calendar Section -->
            <div class="lg:col-span-2 bg-white rounded-lg shadow-md p-6">
                <div id="calendar"></div>
            </div>

            <!-- Events Section -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Upcoming Events</h2>
                <div class="space-y-4">
                    <?php if (empty($events)): ?>
                        <p class="text-gray-600">No upcoming events.</p>
                    <?php else: ?>
                        <?php foreach ($events as $event): ?>
                            <div class="border-l-4 border-purple-600 pl-4 py-2">
                                <h3 class="text-lg font-semibold text-gray-800"><?php echo htmlspecialchars($event['title']); ?></h3>
                                <p class="text-sm text-gray-600 mt-1"><?php echo htmlspecialchars($event['description']); ?></p>
                                <div class="flex items-center mt-2 text-sm text-gray-500">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <span><?php echo htmlspecialchars($event['start_date']); ?></span>
                                    <?php if (!empty($event['end_date'])): ?>
                                        <span class="mx-1">-</span>
                                        <span><?php echo htmlspecialchars($event['end_date']); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                        events: [
                <?php foreach ($events as $event): ?>
                {
                    title: '<?php echo addslashes($event['title']); ?>',
                    start: '<?php echo $event['start_date']; ?>',
                    end: '<?php echo $event['end_date']; ?>',
                    description: '<?php echo addslashes($event['description']); ?>'
                },
                <?php endforeach; ?>
            ],

                eventClick: function(info) {
                    alert('Event: ' + info.event.title + '\nDescription: ' + info.event.extendedProps.description);
                }
            });
            calendar.render();
        });
    </script>

