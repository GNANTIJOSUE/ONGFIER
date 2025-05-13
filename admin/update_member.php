<?php
require_once '../config.php';
require_once 'auth.php';
requireLogin();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['admin_id'])) {
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
$required_fields = [
    'id', 'Civilite', 'Nom', 'Prenom', 'Niveau', 'Diplome', 
    'Specialite', 'Fonction_actuelle', 'Telephone', 'Email', 
    'Pays', 'Ville', 'date_de_naissance', 'situation_matrimoniale'
];

foreach ($required_fields as $field) {
    if (!isset($_POST[$field]) || empty($_POST[$field])) {
        echo json_encode(['success' => false, 'message' => "Le champ $field est requis"]);
        exit();
    }
}

$id = (int)$_POST['id'];
$civilite = clean_input($_POST['Civilite']);
$nom = clean_input($_POST['Nom']);
$prenom = clean_input($_POST['Prenom']);
$niveau = clean_input($_POST['Niveau']);
$diplome = clean_input($_POST['Diplome']);
$specialite = clean_input($_POST['Specialite']);
$fonction_actuelle = clean_input($_POST['Fonction_actuelle']);
$telephone = clean_input($_POST['Telephone']);
$email = clean_input($_POST['Email']);
$pays = clean_input($_POST['Pays']);
$ville = clean_input($_POST['Ville']);
$date_de_naissance = clean_input($_POST['date_de_naissance']);
$situation_matrimoniale = clean_input($_POST['situation_matrimoniale']);

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
        SET civilite = ?,
            nom = ?, 
            prenom = ?, 
            niveau = ?,
            diplome = ?,
            specialite = ?,
            fonction_actuelle = ?,
            telephone = ?, 
            email = ?, 
            pays = ?, 
            ville = ?,
            date_de_naissance = ?,
            situation_matrimoniale = ?,
            date_modification = NOW()
        WHERE id = ?
    ");

    $stmt->execute([
        $civilite,
        $nom,
        $prenom,
        $niveau,
        $diplome,
        $specialite,
        $fonction_actuelle,
        $telephone,
        $email,
        $pays,
        $ville,
        $date_de_naissance,
        $situation_matrimoniale,
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