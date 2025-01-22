<?php

session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Check if the login failed flag is set in the GET request
$login_failed = isset($_GET['login_failed']) && $_GET['login_failed'] == 'true';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Workout Tracker - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        let hasher; // Declare the hasher variable globally to use in hashPassword function

        // Function to create the WASM hasher
        async function createWASMHasher(wasmPath) {
            // Load and initialize the WASM module
            const wasmResponse = await fetch(wasmPath);
            const wasmArrayBuffer = await wasmResponse.arrayBuffer();
            const { instance } = await WebAssembly.instantiate(wasmArrayBuffer);

            const shaPassword = instance.exports.sha_password;
            const memory = instance.exports.memory || new WebAssembly.Memory({ initial: 1 });

            if (!shaPassword) {
                throw new Error("sha_password function not found in WASM exports.");
            }

            // Function to compute the SHA-256 hash
            return async function hashString(input) {
                const encoder = new TextEncoder();
                const passwordBuffer = encoder.encode(input + "\0"); // Null-terminated string
                const requiredMemory = passwordBuffer.length + 32; // Password + 32 bytes for hash

                // Grow memory if necessary
                if (memory.buffer.byteLength < requiredMemory) {
                    const pagesNeeded = Math.ceil(requiredMemory / (64 * 1024)); // 1 page = 64 KiB
                    memory.grow(pagesNeeded);
                }

                // Recreate the memory view after growth
                const wasmMemory = new Uint8Array(memory.buffer);

                // Write the password into WASM memory
                const passwordPtr = 0; // Start at the beginning of the memory
                wasmMemory.set(passwordBuffer, passwordPtr);

                // Call the sha_password function
                const hashPtr = shaPassword(passwordPtr);

                // Extract the hash (32 bytes for SHA-256)
                const hashArray = new Uint8Array(memory.buffer, hashPtr, 32);

                // Convert the hash to a hexadecimal string
                return Array.from(hashArray)
                    .map(byte => byte.toString(16).padStart(2, '0'))
                    .join('');
            };
        }

        // Initialize the hasher globally
        (async () => {
            const wasmPath = 'main.wasm'; // Path to your WASM file
            hasher = await createWASMHasher(wasmPath);
        })();

        // Function to hash the password using SHA-256 before form submission
        async function hashPassword() {
            const passwordField = document.getElementById("password");

            if (hasher) {
                // Hash the password and set it back to the field
                passwordField.value = await hasher("password");
            } else {
                console.error("Hasher is not initialized yet.");
            }
        }

    </script>
</head>

<body class="dark:bg-gray-900 h-screen flex items-center justify-center">

    <!-- Login failed banner -->
    <?php if ($login_failed): ?>
        <div class="absolute top-0 left-0 right-0 bg-red-600 text-white text-center py-2">
            <p>Login failed. Please check your username and password.</p>
        </div>
    <?php endif; ?>

    <div class="bg-gray-800 p-8 rounded-lg shadow-lg w-96">
        <h2 class="text-2xl font-bold text-white text-center mb-6">Login</h2>
        <form action="login_action.php" method="post" onsubmit="hashPassword()">
            <div class="mb-4">
                <label for="username" class="block text-gray-300">Username</label>
                <input type="text" id="username" name="username"
                    class="w-full p-3 border border-gray-600 bg-gray-700 text-white rounded-lg" required>
            </div>
            <div class="mb-6">
                <label for="password" class="block text-gray-300">Password</label>
                <input type="password" id="password" name="password"
                    class="w-full p-3 border border-gray-600 bg-gray-700 text-white rounded-lg" required>
            </div>
            <div class="text-center">
                <button type="submit"
                    class="w-full bg-blue-600 text-white p-3 rounded-lg hover:bg-blue-500">Login</button>
            </div>
        </form>
    </div>

</body>

</html>