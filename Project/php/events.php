<?php
session_start();
require_once '../includes/database.php';

// Check if the user is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login-signup.php");
    exit();
}

// Handle form submission to add a new event
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_event'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $event_type = $_POST['event_type'];

    $stmt = $conn->prepare("INSERT INTO events (title, description, start_date, end_date, event_type) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $title, $description, $start_date, $end_date, $event_type);

    if ($stmt->execute()) {
        $success_message = "Event created successfully.";
    } else {
        $error_message = "Error creating event.";
    }
}

// Fetch all events
$events_result = $conn->query("SELECT * FROM events ORDER BY start_date DESC");
$events = $events_result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
        <h1 class="text-3xl font-bold text-gray-800 mb-8">Event Management</h1>

        <?php if (isset($success_message)): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                <p class="font-bold">Success</p>
                <p><?php echo $success_message; ?></p>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                <p class="font-bold">Error</p>
                <p><?php echo $error_message; ?></p>
            </div>
        <?php endif; ?>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Calendar -->
            <div class="lg:col-span-2 bg-white rounded-lg shadow-md p-6">
                <div id="calendar"></div>
            </div>

            <!-- Event Management Form -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Create Event</h2>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" id="eventForm" class="space-y-4">
                    <div>
                        <label for="event_type" class="block text-sm font-medium text-gray-700">Event Type</label>
                        <select id="event_type" name="event_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="class">Class</option>
                            <option value="assignment">Assignment</option>
                            <option value="exam">Exam</option>
                            <option value="exam">Holiday</option>
                            <option value="exam">Exam</option>
                        </select>
                    </div>
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                        <input type="text" id="title" name="title" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                        <input type="datetime-local" id="start_date" name="start_date" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                        <input type="datetime-local" id="end_date" name="end_date" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea id="description" name="description" rows="3" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"></textarea>
                    </div>
                    <div>
                        <button type="submit" name="create_event" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Create Event
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Upcoming Events Table -->
        <div class="mt-12 bg-white rounded-lg shadow-md overflow-hidden">
            <h2 class="text-2xl font-semibold text-gray-800 p-6 bg-gray-50 border-b">Upcoming Events</h2>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            <th class="px-6 py-3">Title</th>
                            <th class="px-6 py-3">Type</th>
                            <th class="px-6 py-3">Description</th>
                            <th class="px-6 py-3">Start Date</th>
                            <th class="px-6 py-3">End Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($events as $event): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($event['title']); ?></td>
                               <td class="px-6 py-4 whitespace-nowrap">
                                    <?php
                                    $eventType = isset($event['event_type']) ? $event['event_type'] : 'unknown';
                                    $typeClass = '';

                                    switch ($eventType) {
                                        case 'class':
                                            $typeClass = 'bg-green-100 text-green-800';
                                            break;
                                        case 'assignment':
                                            $typeClass = 'bg-yellow-100 text-yellow-800';
                                            break;
                                        case 'exam':
                                            $typeClass = 'bg-red-100 text-red-800';
                                            break;
                                        default:
                                            $typeClass = 'bg-gray-100 text-gray-800';
                                    }
                                    ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $typeClass; ?>">
                                        <?php echo htmlspecialchars(ucfirst($eventType)); ?>
                                    </span>
                                </td>

                                <td class="px-6 py-4"><?php echo htmlspecialchars($event['description']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($event['start_date']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($event['end_date']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
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
            ,{
                title: '<?php echo addslashes($event['title']); ?>',
                start: '<?php echo $event['start_date']; ?>',
                end: '<?php echo $event['end_date']; ?>',
                description: '<?php echo addslashes($event['description']); ?>',
                color: '<?php 
                    switch($event['event_type']) {
                        case 'class':
                            echo '#10B981';
                            break;
                        case 'assignment':
                            echo '#FBBF24';
                            break;
                        case 'exam':
                            echo '#EF4444';
                            break;
                        default:
                            echo '#6B7280';
                    }
                ?>'
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
