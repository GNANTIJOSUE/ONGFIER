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

if (!isset($_POST['member_id']) || !is_numeric($_POST['member_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'ID du membre invalide']);
    exit();
}

$member_id = (int)$_POST['member_id'];

try {
    // Vérifier si le membre existe
    $stmt = $pdo->prepare("SELECT id FROM membres WHERE id = ?");
    $stmt->execute([$member_id]);
    
    if ($stmt->rowCount() === 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Membre non trouvé']);
        exit();
    }

    // Supprimer le membre
    $stmt = $pdo->prepare("DELETE FROM membres WHERE id = ?");
    $stmt->execute([$member_id]);

    if ($stmt->rowCount() > 0) {
        // Journaliser l'action
        $adminId = $_SESSION['admin_id'];
        $adminName = $_SESSION['admin_name'];
        $action = "Suppression du membre {$member['name']} (ID: {$member_id})";
        $stmt = $pdo->prepare("INSERT INTO admin_logs (admin_id, action, created_at) VALUES (?, ?, NOW())");
        $stmt->execute([$adminId, $action]);

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Membre supprimé avec succès']);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression du membre']);
    }
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Erreur de base de données: ' . $e->getMessage()]);
}
?> 