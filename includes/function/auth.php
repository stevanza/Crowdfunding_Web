<?php
// includes/functions/auth.php
session_start();
require_once '../config/database.php';

function registerUser($username, $email, $password, $fullname) {
    $conn = connectDB();
    
    // Validasi input
    if (empty($username) || empty($email) || empty($password) || empty($fullname)) {
        return ["success" => false, "message" => "Semua field harus diisi"];
    }
    
    // Check email sudah terdaftar
    $email = escapeString($conn, $email);
    $checkEmail = $conn->query("SELECT * FROM users WHERE email = '$email'");
    if ($checkEmail->num_rows > 0) {
        closeDB($conn);
        return ["success" => false, "message" => "Email sudah terdaftar"];
    }
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Simpan user baru
    $username = escapeString($conn, $username);
    $fullname = escapeString($conn, $fullname);
    
    $sql = "INSERT INTO users (username, email, password, full_name) 
            VALUES ('$username', '$email', '$hashedPassword', '$fullname')";
    
    if ($conn->query($sql)) {
        closeDB($conn);
        return ["success" => true, "message" => "Registrasi berhasil"];
    } else {
        closeDB($conn);
        return ["success" => false, "message" => "Error: " . $sql . "<br>" . $conn->error];
    }
}

function loginUser($email, $password) {
    $conn = connectDB();
    
    // Validasi input
    if (empty($email) || empty($password)) {
        return ["success" => false, "message" => "Email dan password harus diisi"];
    }
    
    $email = escapeString($conn, $email);
    
    // Check user exists
    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = $conn->query($sql);
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            // Set session
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            
            closeDB($conn);
            return ["success" => true, "message" => "Login berhasil"];
        }
    }
    
    closeDB($conn);
    return ["success" => false, "message" => "Email atau password salah"];
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function logout() {
    session_destroy();
    header("Location: login.php");
    exit();
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] == 'admin';
}

function requireAdmin() {
    if (!isAdmin()) {
        header("Location: index.php");
        exit();
    }
}