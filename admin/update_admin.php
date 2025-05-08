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

// Vérifier si l'ID est fourni
if (!isset($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID non fourni']);
    exit();
}

$id = (int)$_POST['id'];

try {
    // Récupérer les informations de l'administrateur
    $stmt = $pdo->prepare("SELECT id, name, email FROM admins WHERE id = ?");
    $stmt->execute([$id]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin) {
        echo json_encode([
            'success' => true,
            'id' => $admin['id'],
            'name' => $admin['name'],
            'email' => $admin['email']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Administrateur non trouvé']);
    }
} catch (PDOException $e) {
    error_log("Erreur lors de la récupération de l'administrateur: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erreur lors de la récupération des données']);
}
?> 