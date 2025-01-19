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

    <script>

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
            const ecc = document.getElementById('exerciseContainerContainer');
            const exercises = ecc.querySelectorAll(':scope > div'); // All exercise containers

            const workoutData = [];

            exercises.forEach(exerciseContainer => {

                const title = exerciseContainer.querySelector('.exerciseTitle').innerText;
                const setsHTML = exerciseContainer.querySelectorAll('.set');

                const sets = Array.from(setsHTML).map((setHTML, index) => {
                    return {
                        "index": index,
                        "weight": setHTML.querySelector('.weight').value,
                        "reps": setHTML.querySelector('.reps').value,
                    };
                });

                workoutData.push({
                    "exercise": title,
                    "sets": sets
                });
            });

            console.log(JSON.stringify(workoutData));
        }

        function generateRandomWorkouts(count) {
            const bodyParts = ['Chest', 'Legs', 'Back', 'Arms', 'Shoulders', 'Core'];
            const workoutActions = ['Press', 'Curl', 'Raise', 'Squat', 'Pull', 'Push'];
            const equipment = ['Barbell', 'Dumbbell', 'Kettlebell', 'Machine', 'Bodyweight'];
            const workouts = [];

            for (let i = 0; i < count; i++) {
                const randomAction = workoutActions[Math.floor(Math.random() * workoutActions.length)];
                const randomEquipment = equipment[Math.floor(Math.random() * equipment.length)];
                const randomBodyPart = bodyParts[Math.floor(Math.random() * bodyParts.length)];
                const workoutName = `${randomAction} ${randomEquipment}`;

                workouts.push({ name: workoutName, bodypart: randomBodyPart });
            }

            return workouts;
        }

        // Generate 100 random workouts
        const randomWorkouts = generateRandomWorkouts(100);

        // Example usage: update the workouts array in your code
        const workouts = randomWorkouts;

        // Modal elements
        const modal = document.getElementById('exerciseModal');
        const workoutList = document.getElementById('workoutList');
        const addExerciseBtn = document.getElementById('addExerciseBtn');
        const closeModal = document.getElementById('closeModal');

        const finishBtn = document.getElementById('finishBtn');
        const cancelBtn = document.getElementById('cancelBtn');

        finishBtn.addEventListener('click', saveWorkout);
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


    </script>
</body>

</html>