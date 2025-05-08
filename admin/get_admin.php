<?php
require_once '../config.php';
require_once 'auth.php';
requireLogin();

// Vérifier si l'utilisateur est super admin
if (!isSuperAdmin()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Accès non autorisé']);
    exit();
}

// Vérifier si la requête est de type POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit();
}

// Récupérer l'ID de l'administrateur
$adminId = isset($_POST['id']) ? intval($_POST['id']) : 0;

if ($adminId <= 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'ID d\'administrateur invalide']);
    exit();
}

try {
    // Récupérer les informations de l'administrateur
    $stmt = $pdo->prepare("SELECT id, name, email, role FROM admins WHERE id = ?");
    $stmt->execute([$adminId]);
    $admin = $stmt->fetch();

    if (!$admin) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Administrateur non trouvé']);
        exit();
    }

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'id' => $admin['id'],
        'name' => $admin['name'],
        'email' => $admin['email'],
        'role' => $admin['role']
    ]);

} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Erreur lors de la récupération des informations : ' . $e->getMessage()]);
}
?> 