<?php
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $country = $_POST['country'];
    $membership_type = $_POST['membership_type'];
    $message = $_POST['message'];
    
    try {
        // Prepare SQL statement
        $stmt = $conn->prepare("INSERT INTO members (name, email, phone, address, country, membership_type, message, join_date) 
                              VALUES (:name, :email, :phone, :address, :country, :membership_type, :message, NOW())");
        
        // Bind parameters
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':country', $country);
        $stmt->bindParam(':membership_type', $membership_type);
        $stmt->bindParam(':message', $message);
        
        // Execute the statement
        $stmt->execute();
        
        // Send confirmation email
        $to = $email;
        $subject = "Welcome to Global Hope Foundation";
        $message = "Dear $name,\n\nThank you for joining Global Hope Foundation! We have received your application and will review it shortly.\n\nBest regards,\nGlobal Hope Foundation Team";
        $headers = "From: info@globalhopefoundation.org";
        
        mail($to, $subject, $message, $headers);
        
        // Redirect to success page
        header("Location: success.html");
        exit();
        
    } catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?> 