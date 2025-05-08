<?php
require_once '../config.php';
require_once 'auth.php';
requireLogin();

header('Content-Type: application/json');

if (!isset($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID du membre non spécifié']);
    exit;
}

try {
    $id = intval($_POST['id']);
    
    $stmt = $pdo->prepare("SELECT 
        id,
        Civilite,
        Nom,
        Prenom,
        Niveau,
        Diplome,
        Specialite,
        Fonction_actuelle,
        Telephone,
        Email,
        Pays,
        Ville
        FROM membres 
        WHERE id = ?");
    
    $stmt->execute([$id]);
    $member = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($member) {
        echo json_encode([
            'success' => true,
            'data' => $member
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Membre non trouvé'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la récupération des données: ' . $e->getMessage()
    ]);
} 