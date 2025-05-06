<?php
require_once '../config.php';
require_once 'auth.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID du membre invalide']);
    exit;
}

$id = (int)$_POST['id'];

try {
    $stmt = $pdo->prepare("
        SELECT id, nom, prenom, email, telephone, whatsapp, pays, ville, quartier, adresse, type_membre, message, statut
        FROM membres 
        WHERE id = ?
    ");
    $stmt->execute([$id]);
    $member = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($member) {
        echo json_encode(['success' => true, 'data' => $member]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Membre non trouvé']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur de base de données: ' . $e->getMessage()]);
} 