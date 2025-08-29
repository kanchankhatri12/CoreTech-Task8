<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $user_type = $_POST['user_type'];
    
    // Validate input
    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "Please fill in all fields.";
        header("Location: login.html");
        exit();
    }
    
    // Determine which table to query based on user type
    $table = '';
    $redirect = '';
    
    switch($user_type) {
        case 'jobseeker':
            $table = 'users';
            $redirect = 'job_seeker_dashboard.html';
            break;
        case 'employer':
            $table = 'employers';
            $redirect = 'employer_dashboard.html';
            break;
        case 'admin':
            $table = 'admins';
            $redirect = 'admin_dashboard.html';
            break;
        default:
            $_SESSION['error'] = "Invalid user type.";
            header("Location: login.html");
            exit();
    }
    
    try {
        // Query the database
        $stmt = $pdo->prepare("SELECT * FROM $table WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Login successful
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_type'] = $user_type;
            $_SESSION['email'] = $user['email'];
            
            // Set additional session variables based on user type
            if ($user_type == 'jobseeker') {
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];
            } elseif ($user_type == 'employer') {
                $_SESSION['company_name'] = $user['company_name'];
            }
            
            header("Location: $redirect");
            exit();
        } else {
            // Login failed
            $_SESSION['error'] = "Invalid email or password.";
            header("Location: login.html");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
        header("Location: login.html");
        exit();
    }
} else {
    header("Location: login.html");
    exit();
}
?>