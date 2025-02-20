<?php
$server = "localhost";
$username = "root";
$password = "";
$dbname = "ss3";

$con = mysqli_connect($server, $username, $password, $dbname);

if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $name = mysqli_real_escape_string($con, $_POST['name'] ?? '');
    $email = mysqli_real_escape_string($con, $_POST['email'] ?? '');
    $message = mysqli_real_escape_string($con, $_POST['message'] ?? '');
    
   
    $sql = "INSERT INTO ss4 (name, email, message) 
            VALUES (?, ?, ?)";
            
 
    $stmt = mysqli_prepare($con, $sql);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "sss", $name, $email, $message);
 
        if (mysqli_stmt_execute($stmt)) {
         

            header("refresh:3;url=home.html");
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
                          <p>Redirecting to main page in 3 seconds...</p>
                      </div>
                  </body>
                  </html>';
        } else {
            echo "Error: " . mysqli_error($con);
        }
        
        mysqli_stmt_close($stmt);
    } else {
        echo "Error in preparing statement: " . mysqli_error($con);
    }
}


mysqli_close($con);
?>