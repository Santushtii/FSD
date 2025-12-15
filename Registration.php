<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration System</title>
    <style>
        body {
            font-family: Palatino, URW Palladio L, serif;
            max-width: 500px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: white;
            padding: 24px;
            border-radius: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #333;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .error {
            color: #d9534f;
            font-size: 14px;
            margin-top: 5px;
        }
        .success {
            color: #5cb85c;
            background-color: #dff0d8;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: center;
        }
        .error-message {
            color: #d9534f;
            background-color: #f2dede;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: center;
        }
        button {
            text-align:justify;
            background-color: white;
            color: seagreen;
            padding: 11px 19px;
            border: 1 px seagreen;
            border-radius: 4px;
            cursor: pointer;
            width: 100px;
            font-size: 16px;
            float:center;
        }
        button:hover {
            text-align: justify;
            background-color: #4cae4c;
            color:white;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>User Registration</h2>
        
        <?php
        $name = $email = $password = $confirm_password = "";
        $errors = [];
        $success_message = "";
        $error_message = "";
        
        // JSON file path
        $json_file = 'users.json';
        
        // Define that an empty form cannot be submitted
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Get and sanitize form data
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            
            // Validation
            // Name validation: cannot have numbers
            if (empty($name)) {
                $errors['name'] = "Name is required";
            } elseif (!preg_match("/^[a-zA-Z ]*$/", $name)) {
                $errors['name'] = "Only letters and spaces allowed";
            }
            
            // Email validation: should have @ and '.com'
            if (empty($email)) {
                $errors['email'] = "Email is required";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = "Invalid email format";
            }
            
            // Password validation
            if (empty($password)) {
                $errors['password'] = "Password is required";
            } elseif (strlen($password) < 6) {
                $errors['password'] = "Password must be at least 6 characters long";
            }

            
            // Confirm password validation
            if (empty($confirm_password)) {
                $errors['confirm_password'] = "Please confirm your password";
            } elseif ($password !== $confirm_password) {
                $errors['confirm_password'] = "Passwords do not match";
            }
            
            // If no
            if (empty($errors)) {
                try {
                    // Read existing users from JSON file
                    if (file_exists($json_file)) {
                        $json_data = file_get_contents($json_file);
                        $users = json_decode($json_data, true) ?? [];
                    } else {
                        $users = [];
                        // Create file if it doesn't exist
                        file_put_contents($json_file, json_encode($users));
                    }
                    
                    // Check if email already exists
                    $email_exists = false;
                    foreach ($users as $user) {
                        if ($user['email'] === $email) {
                            $email_exists = true;
                            break;
                        }
                    }
                    
                    if ($email_exists) {
                        $errors['email'] = "This email is already registered";
                    } else {
                        // Hash the password
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        
                        // Create new user array
                        $new_user = [
                            'id' => uniqid(),
                            'name' => $name,
                            'email' => $email,
                            'password' => $hashed_password,
                            'registration_date' => date('Y-m-d H:i:s')
                        ];
                        
                        // Add new user to users array
                        $users[] = $new_user;
                        
                        // Write updated array back to JSON file
                        $json_result = json_encode($users, JSON_PRETTY_PRINT);
                        
                        if (file_put_contents($json_file, $json_result)) {
                            $success_message = "Registration successful! Your account has been created.";
                            
                            // Clear form fields
                            $name = $email = $password = $confirm_password = "";
                        } else {
                            $error_message = "Error saving user data. Please try again.";
                        }
                    }
                    
                } catch (Exception $e) {
                    $error_message = "An error occurred: " . $e->getMessage();
                }
            }
        }
        
        // Display success message
        if (!empty($success_message)) {
            echo "<div class='success'>$success_message</div>";
        }
        
        // Display general error message
        if (!empty($error_message)) {
            echo "<div class='error-message'>$error_message</div>";
        }
        ?>
        
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="form-group">
                <label for="name">Full Name:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
                <?php if (!empty($errors['name'])): ?>
                    <div class="error"><?php echo $errors['name']; ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="email">Email Address:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                <?php if (!empty($errors['email'])): ?>
                    <div class="error"><?php echo $errors['email']; ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
                <?php if (!empty($errors['password'])): ?>
                    <div class="error"><?php echo $errors['password']; ?></div>
                <?php endif; ?>
                <small style="color: #666;">Must be at least 6 characters long</small>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
                <?php if (!empty($errors['confirm_password'])): ?>
                    <div class="error"><?php echo $errors['confirm_password']; ?></div>
                <?php endif; ?>
            </div>
            
            <button type="submit">Register</button>
        </form>
        
        <?php
        // Display registered users (for testing purposes)
        if (file_exists($json_file)) {
            $json_data = file_get_contents($json_file);
            $users = json_decode($json_data, true);
            
            if (!empty($users)) {
                echo "<hr><h3>Registered Users (Testing View):</h3>";
                echo "<p>Total users: " . count($users) . "</p>";
                echo "<div style='max-height: 200px; overflow-y: auto; background: #f9f9f9; padding: 10px; border-radius: 4px;'>";
                echo "<pre>" . htmlspecialchars(json_encode($users, JSON_PRETTY_PRINT)) . "</pre>";
                echo "</div>";
            }
        }
        ?>
    </div>
</body>
</html>