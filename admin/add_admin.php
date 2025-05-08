<?php
require_once '../config.php';
require_once 'auth.php';
requireLogin();

header('Content-Type: application/json');

// Vérifier si la requête est de type POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit();
}

// Validation des données requises
$required_fields = ['name', 'email', 'password', 'confirm_password'];
foreach ($required_fields as $field) {
    if (!isset($_POST[$field]) || empty($_POST[$field])) {
        echo json_encode(['success' => false, 'message' => "Le champ $field est requis"]);
        exit();
    }
}

$name = clean_input($_POST['name']);
$email = clean_input($_POST['email']);
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];

// Vérifier si les mots de passe correspondent
if ($password !== $confirm_password) {
    echo json_encode(['success' => false, 'message' => 'Les mots de passe ne correspondent pas']);
    exit();
}

try {
    // Vérifier si l'email existe déjà
    $stmt = $pdo->prepare("SELECT id FROM admins WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Cet email est déjà utilisé']);
        exit();
    }

    // Créer l'administrateur
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO admins (name, email, password, role, created_at) VALUES (?, ?, ?, 'admin', NOW())");
    $stmt->execute([$name, $email, $hashedPassword]);

    echo json_encode(['success' => true, 'message' => 'Administrateur créé avec succès']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur lors de la création de l\'administrateur: ' . $e->getMessage()]);
}
?> 