<?php

// Get the JSON data from the request body
$inputData = json_decode(file_get_contents("php://input"), true);

if ($inputData) {
    $startTime = $inputData['start_time']; // Get start time
    $endTime = $inputData['end_time']; // Get end time
    $workoutData = $inputData['workout_data']; // Get workout data
    
    $userId = $inputData['user_id']; // Assuming the user ID is passed in the request


    try {
        $db = new SQLite3('workout.db');

        // Insert the workout data into the Workout table (no conversion, just insert as strings)
        $stmt = $db->prepare("INSERT INTO Workout (UserID, StartTime, EndTime) VALUES (:user_id, :start_time, :end_time)");
        if ($stmt === false) {
            throw new Exception("Failed to prepare statement: " . $db->lastErrorMsg());
        }

        $stmt->bindValue(':user_id', $userId, SQLITE3_INTEGER);
        $stmt->bindValue(':start_time', $startTime, SQLITE3_TEXT);
        $stmt->bindValue(':end_time', $endTime, SQLITE3_TEXT);
        $stmt->execute();

        // Get the last inserted workout ID
        $workoutId = $db->lastInsertRowID();

        // Insert exercises
        foreach ($workoutData as $exercise) {
            // Insert the exercise into the Exercise table
            $stmt = $db->prepare("INSERT INTO Exercise (ExerciseTypeID, WorkoutID) VALUES (:exercise_type_id, :workout_id)");
            if ($stmt === false) {
                throw new Exception("Failed to prepare statement: " . $db->lastErrorMsg());
            }

            $exercise_type_id = $exercise['exercise_type_id'];
            $stmt->bindValue(':exercise_type_id', $exercise_type_id , SQLITE3_INTEGER);
            $stmt->bindValue(':workout_id', $workoutId, SQLITE3_INTEGER);
            $stmt->execute();


            // Get the last inserted exercise ID
            $exerciseId = $db->lastInsertRowID();

            // Insert the sets for the exercise
            foreach ($exercise['sets'] as $set) {
                error_log(json_encode($set));

                $stmt = $db->prepare("INSERT INTO 'Set' (ExerciseID, 'Index', 'Weight', Reps) VALUES (:exercise_id, :index, :weight, :reps)");
                if ($stmt === false) {
                    throw new Exception("Failed to prepare statement: " . $db->lastErrorMsg());
                }

                $stmt->bindValue(':exercise_id', $exerciseId, SQLITE3_INTEGER);
                $stmt->bindValue(':index', $set['index'], SQLITE3_INTEGER);
                $stmt->bindValue(':weight', $set['weight'], SQLITE3_FLOAT);
                $stmt->bindValue(':reps', $set['reps'], SQLITE3_INTEGER);
                $stmt->execute();
            }
        }

        // Return success response
        echo json_encode(["status" => "success"]);
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid data"]);
}
?>
