<?php
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer les données du formulaire
    $civilite = $_POST['Civilite'] ?? '';
    $nom = $_POST['Nom'] ?? '';
    $prenom = $_POST['Prenom'] ?? '';
    $niveau = $_POST['Niveau'] ?? '';
    $diplome = $_POST['Diplome'] ?? '';
    $specialite = $_POST['Specialite'] ?? '';
    $fonction_actuelle = $_POST['Fonction_actuelle'] ?? '';
    $telephone = $_POST['Telephone'] ?? '';
    $email = $_POST['Email'] ?? '';
    $pays = $_POST['Pays'] ?? '';
    $ville = $_POST['Ville'] ?? '';

    $errors = [];

    // Validation des champs
    if (!$civilite) $errors[] = "La civilité est requise";
    if (!$nom) $errors[] = "Le nom est requis";
    if (!$prenom) $errors[] = "Le prénom est requis";
    if (!$niveau) $errors[] = "Le niveau d'études est requis";
    if (!$diplome) $errors[] = "Le diplôme est requis";
    if (!$specialite) $errors[] = "La spécialité est requise";
    if (!$fonction_actuelle) $errors[] = "La fonction actuelle est requise";
    if (!$telephone) $errors[] = "Le téléphone est requis";
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Un email valide est requis";
    if (!$pays) $errors[] = "Le pays est requis";
    if (!$ville) $errors[] = "La ville est requise";

    // Vérifier l'unicité de l'email
    if (!$errors) {
        $stmt = $conn->prepare("SELECT id FROM membres WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = "Cet email est déjà utilisé";
        }
    }

    if ($errors) {
        echo json_encode(['success' => false, 'errors' => $errors]);
        exit;
    }

    try {
        $stmt = $conn->prepare("INSERT INTO membres 
            (civilite, nom, prenom, niveau, diplome, specialite, fonction_actuelle, telephone, email, pays, ville, date_inscription, statut) 
            VALUES 
            (:civilite, :nom, :prenom, :niveau, :diplome, :specialite, :fonction_actuelle, :telephone, :email, :pays, :ville, NOW(), 'en_attente')");

        $stmt->execute([
            ':civilite' => $civilite,
            ':nom' => $nom,
            ':prenom' => $prenom,
            ':niveau' => $niveau,
            ':diplome' => $diplome,
            ':specialite' => $specialite,
            ':fonction_actuelle' => $fonction_actuelle,
            ':telephone' => $telephone,
            ':email' => $email,
            ':pays' => $pays,
            ':ville' => $ville
        ]);

        echo json_encode(['success' => true, 'message' => 'Inscription réussie !']);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'errors' => ["Erreur : " . $e->getMessage()]]);
    }
} else {
    echo json_encode(['success' => false, 'errors' => ['Méthode non autorisée']]);
}
?> 