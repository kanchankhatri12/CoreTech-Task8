<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_type = $_POST['user_type'];
    
    // Validate user type
    if (!in_array($user_type, ['jobseeker', 'employer'])) {
        $_SESSION['error'] = "Invalid user type.";
        header("Location: register.html");
        exit();
    }
    
    if ($user_type == 'jobseeker') {
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Validate input
        if (empty($first_name) || empty($last_name) || empty($email) || empty($password) || empty($confirm_password)) {
            $_SESSION['error'] = "Please fill in all fields.";
            header("Location: register.html");
            exit();
        }
        
        if ($password !== $confirm_password) {
            $_SESSION['error'] = "Passwords do not match.";
            header("Location: register.html");
            exit();
        }
        
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            $_SESSION['error'] = "Email already registered.";
            header("Location: register.html");
            exit();
        }
        
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert into database
        try {
            $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password) VALUES (?, ?, ?, ?)");
            $stmt->execute([$first_name, $last_name, $email, $hashed_password]);
            
            $_SESSION['success'] = "Registration successful! You can now login.";
            header("Location: login.html");
            exit();
        } catch (PDOException $e) {
            $_SESSION['error'] = "Database error: " . $e->getMessage();
            header("Location: register.html");
            exit();
        }
        
    } elseif ($user_type == 'employer') {
        $company_name = $_POST['company_name'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $website = $_POST['website'] ?? '';
        
        // Validate input
        if (empty($company_name) || empty($email) || empty($password) || empty($confirm_password)) {
            $_SESSION['error'] = "Please fill in all required fields.";
            header("Location: register.html");
            exit();
        }
        
        if ($password !== $confirm_password) {
            $_SESSION['error'] = "Passwords do not match.";
            header("Location: register.html");
            exit();
        }
        
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM employers WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            $_SESSION['error'] = "Email already registered.";
            header("Location: register.html");
            exit();
        }
        
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert into database
        try {
            $stmt = $pdo->prepare("INSERT INTO employers (company_name, email, password, website) VALUES (?, ?, ?, ?)");
            $stmt->execute([$company_name, $email, $hashed_password, $website]);
            
            $_SESSION['success'] = "Registration successful! Your account is pending admin approval.";
            header("Location: login.html");
            exit();
        } catch (PDOException $e) {
            $_SESSION['error'] = "Database error: " . $e->getMessage();
            header("Location: register.html");
            exit();
        }
    }
} else {
    header("Location: register.html");
    exit();
}
?>