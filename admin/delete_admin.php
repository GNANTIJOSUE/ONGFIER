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

if (!isset($_POST['admin_id']) || !is_numeric($_POST['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'ID de l\'administrateur invalide']);
    exit();
}

$admin_id = (int)$_POST['admin_id'];

// Empêcher la suppression de son propre compte
if ($admin_id === $_SESSION['admin_id']) {
    echo json_encode(['success' => false, 'message' => 'Vous ne pouvez pas supprimer votre propre compte']);
    exit();
}

try {
    // Vérifier si l'administrateur existe et récupérer ses informations
    $stmt = $pdo->prepare("SELECT id, name, role FROM admins WHERE id = ?");
    $stmt->execute([$admin_id]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$admin) {
        echo json_encode(['success' => false, 'message' => 'Administrateur non trouvé']);
        exit();
    }

    // Empêcher la suppression d'un super admin
    if ($admin['role'] === 'super_admin') {
        echo json_encode(['success' => false, 'message' => 'Impossible de supprimer un super administrateur']);
        exit();
    }

    // Supprimer l'administrateur
    $stmt = $pdo->prepare("DELETE FROM admins WHERE id = ?");
    $stmt->execute([$admin_id]);

    if ($stmt->rowCount() > 0) {
        // Journaliser l'action
        $currentAdminId = $_SESSION['admin_id'];
        $action = "Suppression de l'administrateur " . $admin['name'] . " (ID: {$admin_id})";
        
        // Vérifier si la table admin_logs existe avant d'insérer
        $tableExists = $pdo->query("SHOW TABLES LIKE 'admin_logs'")->rowCount() > 0;
        if ($tableExists) {
            $stmt = $pdo->prepare("INSERT INTO admin_logs (admin_id, action, created_at) VALUES (?, ?, NOW())");
            $stmt->execute([$currentAdminId, $action]);
        }

        echo json_encode(['success' => true, 'message' => 'Administrateur supprimé avec succès']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression de l\'administrateur']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur de base de données: ' . $e->getMessage()]);
}
?> 