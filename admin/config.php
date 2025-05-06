<?php
// Configuration de la base de données
$host = 'localhost';
$dbname = 'ngo_db';
$username = 'root';
$password = '';

try {
    // Création de la connexion PDO
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// Fonction pour nettoyer les entrées
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Messages d'erreur
$error_messages = [
    'db_connection' => "Erreur de connexion à la base de données",
    'db_query' => "Erreur lors de l'exécution de la requête",
    'required_field' => "Ce champ est requis",
    'invalid_email' => "Adresse email invalide",
    'invalid_phone' => "Numéro de téléphone invalide",
    'form_submission' => "Erreur lors de la soumission du formulaire"
];

// Fonction pour afficher les messages d'erreur
function display_errors($errors) {
    if (!empty($errors)) {
        echo '<div class="error-message">';
        echo '<ul>';
        foreach ($errors as $error) {
            echo '<li>' . htmlspecialchars($error) . '</li>';
        }
        echo '</ul>';
        echo '</div>';
    }
}

// Fonction pour récupérer les données du formulaire en cas d'erreur
function get_form_data($field) {
    return isset($_SESSION['form_data'][$field]) ? clean_input($_SESSION['form_data'][$field]) : '';
}
?> 