<?php

session_start();

if(!isset( $_SESSION['user_id'] )) {
    header("Location: login.php");
    exit();
}

$db_file = 'workout.db';

try {
    // Create a new SQLite3 database object
    $db = new SQLite3($db_file);

} catch (Exception $e) {
    // Handle connection errors
    echo "Connection failed: " . $e->getMessage();
}



?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Workout Tracker</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="dark:bg-gray-900">
    <div class="bg-white dark:bg-slate-700 px-6 py-8 ring-1 ring-slate-900/5 shadow-xl">
        <div class="flex items-center justify-between">
            <button id="cancelBtn" class="bg-gray-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Cancel Workout
            </button>
            <h1 id="time" class="text-3xl dark:text-white text-center px-6">
                00:00:00
            </h1>
            <button id="finishBtn" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Finish Workout
            </button>
        </div>
    </div>

    <div class="py-6"></div>

    <div id="exerciseContainerContainer"></div>

    <div class="px-2 py-2 rounded w-full mx-auto">
        <button id="addExerciseBtn" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded w-full">
            Add Exercise
        </button>
    </div>

    <!-- Modal -->
    <div id="exerciseModal" class="fixed inset-0 hidden bg-gray-900 bg-opacity-75 flex items-center justify-center">
        <div class="bg-white dark:bg-slate-700 p-6 rounded shadow-xl w-11/12 max-w-lg max-h-[80%] overflow-y-auto">
            <h2 class="text-2xl font-bold mb-4 dark:text-white">Select an Exercise</h2>

            <!-- Search and Body Part Filter Row -->
            <div class="flex space-x-4 mb-4">
                <!-- Search Bar -->
                <input type="text" id="searchBar" placeholder="Search..."
                    class="w-full px-4 py-2 border rounded-lg dark:bg-slate-600 dark:text-white" />

                <!-- Body Part Dropdown -->
                <select id="bodyPartSelect" class="px-4 py-2 border rounded-lg dark:bg-slate-600 dark:text-white">
                    <option value="">All Body Parts</option>
                    <?php


                    $query = "SELECT DISTINCT BodyPart FROM ExerciseType;";

                    // Execute the query and store the result
                    $result = $db->query($query);

                    // Check if any results are returned
                    if ($result) {
                        // Loop through the results and print each distinct BodyPart
                        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                            error_log($row['BodyPart']);
                            echo "<option value=\"" . $row['BodyPart'] . "\">" . $row['BodyPart'] . "</option>";
                        }
                    }

                    ?>
                </select>
            </div>

            <!-- Workout List -->
            <ul id="workoutList" class="space-y-2">
                <!-- Workouts will be dynamically inserted here -->
            </ul>

            <!-- Close Button -->
            <button id="closeModal"
                class="mt-4 bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded w-full">
                Close
            </button>
        </div>
    </div>

    <script src="/script.php" type="text/javascript"></script>
</body>

</html>