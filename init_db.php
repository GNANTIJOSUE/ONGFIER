<?php
// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';

try {
    // Create connection without database
    $conn = new PDO("mysql:host=$host", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create database if not exists
    $sql = "CREATE DATABASE IF NOT EXISTS ngo_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $conn->exec($sql);
    echo "Database created successfully<br>";

    // Select the database
    $conn->exec("USE ngo_db");

    // Create members table
    $sql = "CREATE TABLE IF NOT EXISTS membres (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nom VARCHAR(50) NOT NULL,
        prenom VARCHAR(50) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        telephone VARCHAR(20) NOT NULL,
        whatsapp VARCHAR(20),
        pays VARCHAR(50) NOT NULL,
        ville VARCHAR(50) NOT NULL,
        quartier VARCHAR(100) NOT NULL,
        adresse TEXT NOT NULL,
        type_membre ENUM('volontaire', 'donateur', 'membre_actif') NOT NULL,
        message TEXT,
        date_inscription DATETIME NOT NULL,
        statut ENUM('en_attente', 'approuve', 'rejete') DEFAULT 'en_attente',
        date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $conn->exec($sql);
    echo "Table 'membres' created successfully<br>";

    // Create actions table
    $sql = "CREATE TABLE IF NOT EXISTS actions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        titre VARCHAR(100) NOT NULL,
        description TEXT NOT NULL,
        date_debut DATE NOT NULL,
        date_fin DATE,
        lieu VARCHAR(100) NOT NULL,
        statut ENUM('planifie', 'en_cours', 'termine') DEFAULT 'planifie',
        date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $conn->exec($sql);
    echo "Table 'actions' created successfully<br>";

    // Create participants_actions table
    $sql = "CREATE TABLE IF NOT EXISTS participants_actions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        membre_id INT NOT NULL,
        action_id INT NOT NULL,
        role VARCHAR(50) NOT NULL,
        date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (membre_id) REFERENCES membres(id),
        FOREIGN KEY (action_id) REFERENCES actions(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $conn->exec($sql);
    echo "Table 'participants_actions' created successfully<br>";

    // Insert sample data into actions table
    $sql = "INSERT INTO actions (titre, description, date_debut, lieu, statut) VALUES
        ('Plantation d\'arbres', 'Campagne de reforestation dans la région parisienne', '2024-04-15', 'Paris', 'planifie'),
        ('Collecte de fonds', 'Événement caritatif pour financer nos projets', '2024-05-20', 'Lyon', 'planifie'),
        ('Formation bénévoles', 'Session de formation pour les nouveaux bénévoles', '2024-03-10', 'Marseille', 'en_cours')";
    
    $conn->exec($sql);
    echo "Sample data inserted successfully<br>";

    echo "Database setup completed successfully!";

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}

$conn = null;
?> 