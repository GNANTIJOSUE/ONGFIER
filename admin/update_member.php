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

// Validation des données requises
$required_fields = ['id', 'nom', 'prenom', 'email', 'telephone', 'type_membre'];
foreach ($required_fields as $field) {
    if (!isset($_POST[$field]) || empty($_POST[$field])) {
        echo json_encode(['success' => false, 'message' => "Le champ $field est requis"]);
        exit();
    }
}

$id = (int)$_POST['id'];
$nom = clean_input($_POST['nom']);
$prenom = clean_input($_POST['prenom']);
$email = clean_input($_POST['email']);
$telephone = clean_input($_POST['telephone']);
$type_membre = clean_input($_POST['type_membre']);
$whatsapp = isset($_POST['whatsapp']) ? clean_input($_POST['whatsapp']) : null;
$pays = clean_input($_POST['pays']);
$ville = clean_input($_POST['ville']);
$quartier = clean_input($_POST['quartier']);
$adresse = clean_input($_POST['adresse']);
$message = isset($_POST['message']) ? clean_input($_POST['message']) : null;

try {
    // Vérifier si le membre existe
    $stmt = $pdo->prepare("SELECT id FROM membres WHERE id = ?");
    $stmt->execute([$id]);
    
    if ($stmt->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'Membre non trouvé']);
        exit();
    }

    // Vérifier si l'email est déjà utilisé par un autre membre
    $stmt = $pdo->prepare("SELECT id FROM membres WHERE email = ? AND id != ?");
    $stmt->execute([$email, $id]);
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => false, 'message' => 'Cet email est déjà utilisé par un autre membre']);
        exit();
    }

    // Mettre à jour les informations du membre
    $stmt = $pdo->prepare("
        UPDATE membres 
        SET nom = ?, 
            prenom = ?, 
            email = ?, 
            telephone = ?, 
            whatsapp = ?, 
            pays = ?, 
            ville = ?, 
            quartier = ?, 
            adresse = ?, 
            type_membre = ?, 
            message = ?,
            date_modification = NOW()
        WHERE id = ?
    ");

    $stmt->execute([
        $nom,
        $prenom,
        $email,
        $telephone,
        $whatsapp,
        $pays,
        $ville,
        $quartier,
        $adresse,
        $type_membre,
        $message,
        $id
    ]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Membre modifié avec succès']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Aucune modification effectuée']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur de base de données: ' . $e->getMessage()]);
}
?> 