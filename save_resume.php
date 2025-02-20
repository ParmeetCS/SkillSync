<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);


header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config.php';


function sanitizeInput($data) {
    return htmlspecialchars(strip_tags($data));
}


function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}


function validateURL($url) {
    if (empty($url)) return true; 
    return filter_var($url, FILTER_VALIDATE_URL);
}

try {
  
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

  
    $database = new Database();
    $db = $database->getConnection();
    

    $db->beginTransaction();

   
    if (empty($_POST['full_name']) || empty($_POST['email'])) {
        throw new Exception('Name and email are required');
    }

 
    if (!validateEmail($_POST['email'])) {
        throw new Exception('Invalid email format');
    }


    if (!validateURL($_POST['portfolio_link'])) {
        throw new Exception('Invalid portfolio URL format');
    }

    
    $stmt = $db->prepare("
        INSERT INTO users (full_name, email, phone, portfolio_link)
        VALUES (:full_name, :email, :phone, :portfolio_link)
    ");

    $stmt->execute([
        ':full_name' => sanitizeInput($_POST['full_name']),
        ':email' => sanitizeInput($_POST['email']),
        ':phone' => sanitizeInput($_POST['phone'] ?? ''),
        ':portfolio_link' => sanitizeInput($_POST['portfolio_link'] ?? '')
    ]);

    $user_id = $db->lastInsertId();

    
    if (!empty($_POST['institution_name']) && !empty($_POST['degree'])) {
        $stmt = $db->prepare("
            INSERT INTO education (user_id, institution_name, degree, field_of_study)
            VALUES (:user_id, :institution_name, :degree, :field_of_study)
        ");

        $stmt->execute([
            ':user_id' => $user_id,
            ':institution_name' => sanitizeInput($_POST['institution_name']),
            ':degree' => sanitizeInput($_POST['degree']),
            ':field_of_study' => sanitizeInput($_POST['field_of_study'] ?? '')
        ]);
    }

    if (!empty($_POST['skill_name'])) {
        $stmt = $db->prepare("
            INSERT INTO skills (user_id, skill_name, proficiency_level)
            VALUES (:user_id, :skill_name, :proficiency_level)
        ");

        $stmt->execute([
            ':user_id' => $user_id,
            ':skill_name' => sanitizeInput($_POST['skill_name']),
            ':proficiency_level' => intval($_POST['proficiency_level'] ?? 3)
        ]);
    }

  
    if (!empty($_POST['project_title'])) {
        $stmt = $db->prepare("
            INSERT INTO projects (user_id, project_title, project_description, project_link)
            VALUES (:user_id, :project_title, :project_description, :project_link)
        ");

        $stmt->execute([
            ':user_id' => $user_id,
            ':project_title' => sanitizeInput($_POST['project_title']),
            ':project_description' => sanitizeInput($_POST['project_description'] ?? ''),
            ':project_link' => sanitizeInput($_POST['project_link'] ?? '')
        ]);
    }

   
    $db->commit();

   
    echo json_encode([
        'success' => true,
        'message' => 'Resume saved successfully!',
        'user_id' => $user_id
    ]);

} catch (Exception $e) {
   
    if (isset($db)) {
        $db->rollBack();
    }


    error_log("Error in save_resume.php: " . $e->getMessage());


    echo json_encode([
        'success' => false,
        'message' => 'Error saving resume: ' . $e->getMessage()
    ]);
}
?>