<?php
header('Content-Type: application/javascript');

session_start();

if (!isset($_SESSION['user_id'])) {
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

    function formatDate(timestamp) {
        const date = new Date(timestamp);
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0'); // Month is 0-indexed
        const day = String(date.getDate()).padStart(2, '0');
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');
        const seconds = String(date.getSeconds()).padStart(2, '0');

        return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
    }

    // Store the start time (in milliseconds)
    let startTime = Date.now();
    let timerInterval;

    // Function to start the timer
    function startTimer() {
        // Update the timer every second
        timerInterval = setInterval(() => {
            updateTimeDisplay(); // Update the display
        }, 1000);
    }

    // Function to update the timer display
    function updateTimeDisplay() {
        const currentTime = Date.now(); // Get the current time in milliseconds
        const elapsedTime = currentTime - startTime; // Calculate the difference from start time

        const hours = Math.floor(elapsedTime / 3600000); // Convert milliseconds to hours
        const minutes = Math.floor((elapsedTime % 3600000) / 60000); // Convert milliseconds to minutes
        const seconds = Math.floor((elapsedTime % 60000) / 1000); // Convert milliseconds to seconds

        const formattedTime = `${pad(hours)}:${pad(minutes)}:${pad(seconds)}`;
        document.getElementById('time').textContent = formattedTime; // Update the display
    }

    // Function to add leading zero for single-digit numbers
    function pad(num) {
        return num < 10 ? '0' + num : num;
    }



    // Start the timer when the page is loaded
    window.onload = function () {
        startTimer(); // Start the timer
        // Stop the timer when finish workout button is clicked
        finishBtn.addEventListener('click', () => {
            clearInterval(timerInterval); // Stop the timer
            saveWorkout(); // Save the workout data
        });
    };

    function saveWorkout() {
        const endTime = Date.now(); // Capture the end time
        const ecc = document.getElementById('exerciseContainerContainer');
        const exercises = ecc.querySelectorAll(':scope > div'); // All exercise containers
        const formattedStartTime = formatDate(startTime); // Format start time
        const formattedEndTime = formatDate(endTime);
        const workoutData = [];

        exercises.forEach(exerciseContainer => {

            const title = exerciseContainer.querySelector('.exerciseTitle').innerText;
            const setsHTML = exerciseContainer.querySelectorAll('.set');

            const sets = Array.from(setsHTML).map((setHTML, index) => {
                return {
                    "index": index,
                    "weight": setHTML.querySelector('.weight').value,
                    "reps": setHTML.querySelector('.reps').value,
                    "user_id": <?php echo $_SESSION['user_id'] ?>
                };
            });

            workoutData.push({
                "exercise": title,
                "exercise_type_id": get_exercise_type_id(title),
                "sets": sets
            });
        });

        // Get the base URL of the current website (without the path)
        const baseUrl = window.location.origin;

        // Concatenate it with the relative path to your PHP script
        const url = `${baseUrl}/save_workout.php`;

        // Send the workout data, including start and end times
        fetch(url, {
            method: "POST",
            headers: {
                "Content-Type": "application/json", // Indicate JSON data
            },
            body: JSON.stringify({
                start_time: formattedStartTime,  // Send the start time
                end_time: formattedEndTime,      // Send the end time
                workout_data: workoutData,  // Send the workout data
                user_id: 0
            }), // Send the nested JSON structure
        })
            .then(response => response.json())  // Parse the JSON response
            .then(data => console.log("Success:", data))  // Handle success
            .catch(error => console.error("Error:", error));  // Handle errors
    }


    <?php
    $query = "SELECT * FROM ExerciseType";
    $results = $db->query($query);

    // Fetch all results and store them in an array of objects
    $workouts = [];
    while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
        $workouts[] = [
            'name' => $row['Name'],
            'bodypart' => $row['BodyPart'],
            'exercise_type_id' => $row['ExerciseTypeID']
        ];
    }

    echo 'const workouts = ' . json_encode($workouts) . ';';
    ?>

    function get_exercise_type_id(title) {
        id = -1;
        workouts.forEach(workout => {
            if (workout.name == title) {
                id = workout.exercise_type_id;
            }
        });
        return id;
    }

    // Modal elements
    const modal = document.getElementById('exerciseModal');
    const workoutList = document.getElementById('workoutList');
    const addExerciseBtn = document.getElementById('addExerciseBtn');
    const closeModal = document.getElementById('closeModal');

    const finishBtn = document.getElementById('finishBtn');
    const cancelBtn = document.getElementById('cancelBtn');



    cancelBtn.addEventListener('click', () => {
        location.reload();
    });

    // Open modal
    addExerciseBtn.addEventListener('click', () => {
        populateWorkoutList();
        modal.classList.remove('hidden');
    });

    // Close modal
    closeModal.addEventListener('click', () => {
        modal.classList.add('hidden');
    });

    // Populate modal with workout list
    function populateWorkoutList() {
        workoutList.innerHTML = ''; // Clear existing items
        workouts.forEach((workout, index) => {
            const li = document.createElement('li');
            li.className = 'bg-blue-900 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded cursor-pointer';
            li.textContent = `${workout.name} (${workout.bodypart})`;
            li.addEventListener('click', () => selectWorkout(workout));
            workoutList.appendChild(li);
        });
    }

    // Handle workout selection
    function selectWorkout(workout) {
        // Close modal after selecting
        modal.classList.add('hidden');

        const ecc = document.getElementById('exerciseContainerContainer');


        // Create the exercise container with the title of the selected workout
        const exerciseContainer = document.createElement('div');
        exerciseContainer.className = 'bg-white dark:bg-slate-700 text-white font-bold px-4 py-2 rounded w-full max-w-4xl mx-auto my-4';

        // Create and append the exercise title (selected workout name)
        const exerciseTitleContainer = document.createElement('div');
        exerciseTitleContainer.className = 'flex justify-between items-center';

        const exerciseTitle = document.createElement('h2');
        exerciseTitle.className = 'text-xl font-bold dark:text-white mb-4 exerciseTitle';
        exerciseTitle.textContent = workout.name;
        exerciseTitleContainer.appendChild(exerciseTitle);

        // Add the "X" button to remove the entire exercise
        const removeExerciseBtn = document.createElement('button');
        removeExerciseBtn.className = 'text-red-600 hover:text-red-800 font-bold ml-auto'; // Align the "X" button to the right
        removeExerciseBtn.textContent = 'X';
        removeExerciseBtn.addEventListener('click', () => {
            exerciseContainer.remove();
        });
        exerciseTitleContainer.appendChild(removeExerciseBtn);

        exerciseContainer.appendChild(exerciseTitleContainer);

        // Create the sets container
        const setsContainer = document.createElement('div');
        setsContainer.className = 'mx-4 w-full';
        exerciseContainer.appendChild(setsContainer);

        // Add the labels for the set table
        const labelRow = document.createElement('div');
        labelRow.className = 'flex items-center space-x-4 font-bold pb-2';
        labelRow.innerHTML = `
        <span class="w-1/5">Set</span>
        <span class="w-1/5">Previous</span>
        <span class="w-1/5">Kg</span>
        <span class="w-1/5">Reps</span>
    `;
        setsContainer.appendChild(labelRow);

        // Add the Add Set button
        const addSetBtn = document.createElement('button');
        addSetBtn.className = 'bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 rounded w-full';
        addSetBtn.textContent = 'Add Set';
        addSetBtn.addEventListener('click', () => { addSet(setsContainer) });
        exerciseContainer.appendChild(addSetBtn);

        ecc.appendChild(exerciseContainer);
        addSetBtn.click();
    }


    // Function to check if the device is mobile based on width and height comparison
    function isMobile() {
        return window.innerWidth < window.innerHeight; // Check if width is smaller than height
    }

    // Add Set function for each workout
    function addSet(setsContainer) {
        const setCount = setsContainer.querySelectorAll('.set').length + 1;

        const setElement = document.createElement('div');
        setElement.className = 'flex items-center space-x-4 py-2 set w-full bg-white dark:bg-slate-700'; // Ensure the full width for the set element
        setElement.style.transition = 'transform 0.3s ease';  // Smooth sliding transition

        // Set number
        const setNumber = document.createElement('span');
        setNumber.className = 'w-1/5';
        setNumber.textContent = `${setCount}`;
        setElement.appendChild(setNumber);

        // Previous set value (static)
        const previousSetValue = document.createElement('span');
        previousSetValue.className = 'w-1/5';
        previousSetValue.textContent = '-';
        setElement.appendChild(previousSetValue);

        // Input for weight (kg)
        const weightInput = document.createElement('input');
        weightInput.type = 'number';
        weightInput.placeholder = 'Kg';
        weightInput.className = 'px-4 py-2 border rounded-lg dark:bg-slate-600 dark:text-white w-1/5 weight';
        setElement.appendChild(weightInput);

        // Input for reps
        const repsInput = document.createElement('input');
        repsInput.type = 'number';
        repsInput.placeholder = 'Reps';
        repsInput.className = 'px-4 py-2 border rounded-lg dark:bg-slate-600 dark:text-white w-1/5 reps';
        setElement.appendChild(repsInput);

        // Add remove button (cross) to be aligned right further
        const removeBtn = document.createElement('button');
        removeBtn.className = 'text-red-600 hover:text-red-800 font-bold ml-[calc(1rem+1px)]'; // Shift the "X" button a tiny bit left
        removeBtn.textContent = 'X';

        // Hide the "X" button on mobile
        if (isMobile()) {
            removeBtn.style.display = 'none';
        } else {
            removeBtn.addEventListener('click', () => {
                setElement.remove();
                adjustSetNumbers(setsContainer);
            });
        }

        setElement.appendChild(removeBtn);

        // If it's a mobile device (portrait), enable sliding
        if (isMobile()) {
            // Add touch/mouse drag behavior for sliding
            let startX = 0;
            let isDragging = false;

            setElement.addEventListener('mousedown', (e) => {
                startX = e.clientX;
                isDragging = true;
            });

            setElement.addEventListener('touchstart', (e) => {
                startX = e.touches[0].clientX;
                isDragging = true;
            });

            // When dragging moves
            setElement.addEventListener('mousemove', (e) => {
                if (isDragging) {
                    const moveX = e.clientX - startX;
                    if (moveX < 0) { // Allow sliding to the left only
                        setElement.style.transform = `translateX(${moveX}px)`;
                    }
                }
            });

            setElement.addEventListener('touchmove', (e) => {
                if (isDragging) {
                    const moveX = e.touches[0].clientX - startX;
                    if (moveX < 0) { // Allow sliding to the left only
                        setElement.style.transform = `translateX(${moveX}px)`;
                    }
                }
            });

            // End dragging
            setElement.addEventListener('mouseup', () => {
                if (isDragging) {
                    finishDrag();
                }
            });

            setElement.addEventListener('touchend', () => {
                if (isDragging) {
                    finishDrag();
                }
            });

            function finishDrag() {
                isDragging = false;
                const slideDistance = parseInt(setElement.style.transform.replace('translateX(', '').replace('px)', ''));

                if (slideDistance < -150) { // If the set is dragged sufficiently far to the left
                    setElement.style.transform = 'translateX(-100%)';
                    setTimeout(() => {
                        setElement.remove(); // Remove the set after the slide
                        adjustSetNumbers(setsContainer); // Re-adjust the set numbers
                    }, 300); // Match the transition duration
                } else {
                    // Reset position if not dragged far enough
                    setElement.style.transform = 'translateX(0)';
                }
            }
        } else {
            // Desktop version: prevent sliding behavior and just add the set without the drag functionality
            setElement.style.transform = 'translateX(0)';
        }

        // Add the set element to the container
        setsContainer.appendChild(setElement);
    }

    // Function to adjust set numbers after a set is removed
    function adjustSetNumbers(setsContainer) {
        const setElements = setsContainer.querySelectorAll('.set');
        setElements.forEach((setElement, index) => {
            const setNumber = setElement.querySelector('span'); // Get the set number
            setNumber.textContent = `${index + 1}`; // Reassign the number
        });
    }
