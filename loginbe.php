<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);


$db_host = "localhost";     
$db_user = "root";         
$db_pass = "";             
$db_name = "skillsync_nexus";


try {

    $conn = new mysqli($db_host, $db_user, $db_pass);
    
   
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    
    $db_check = $conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$db_name'");
    if ($db_check->num_rows == 0) {
        $conn->query("CREATE DATABASE $db_name");
    }

    $conn->select_db($db_name);

    
    $table_check = $conn->query("SHOW TABLES LIKE 'talent_profiles'");
    if ($table_check->num_rows == 0) {
        $create_table = "CREATE TABLE talent_profiles (
            profile_id INT AUTO_INCREMENT PRIMARY KEY,
            full_name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            institute VARCHAR(150) NOT NULL,
            profession ENUM('student', 'Industry Expert') NOT NULL,
            registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            last_login TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            profile_status ENUM('active', 'inactive', 'pending') DEFAULT 'pending'
        )";
        $conn->query($create_table);
    }

} catch (Exception $e) {
   
    error_log("Database Error: " . $e->getMessage() . " in " . __FILE__ . " on line " . __LINE__, 0);
    die("Connection error. Please check your database configuration.");
}


function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
      
        $full_name = sanitizeInput($_POST['full_name']);
        $email = sanitizeInput($_POST['email']);
        $institute = sanitizeInput($_POST['institute']);
        $profession = sanitizeInput($_POST['profession']);

    
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format");
        }

      
        if (!in_array($profession, ['student', 'Industry Expert'])) {
            throw new Exception("Invalid profession selected");
        }

   
        $check_email = $conn->prepare("SELECT email FROM talent_profiles WHERE email = ?");
        if (!$check_email) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $check_email->bind_param("s", $email);
        $check_email->execute();
        $result = $check_email->get_result();

        if ($result->num_rows > 0) {
            $check_email->close();
            throw new Exception("Email already registered. Please use a different email or try logging in.");
        }
        $check_email->close();

        
        $stmt = $conn->prepare("INSERT INTO talent_profiles (full_name, email, institute, profession) VALUES (?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("ssss", $full_name, $email, $institute, $profession);
        
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

    
        session_start();
        $_SESSION['user_email'] = $email;
        $_SESSION['user_name'] = $full_name;
        
        echo '<!DOCTYPE html>
              <html>
              <head>
                  <title>Success</title>
                  <style>
                      .success-message {
                          text-align: center;
                          margin-top: 50px;
                          font-family: Arial, sans-serif;
                          color: green;
                      }
                  </style>
              </head>
              <body>
                  <div class="success-message">
                      <h2>Data submitted successfully!</h2>
                      <p>Redirecting to login page in 3 seconds...</p>
                  </div>
              </body>
              </html>';
        
   
        header("refresh:3;url=home.html");
        exit();

    } catch (Exception $e) {
        error_log("Registration error: " . $e->getMessage());
        echo "Error: " . $e->getMessage();
    }
}

$conn->close();
?>