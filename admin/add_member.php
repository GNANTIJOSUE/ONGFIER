<?php
// Désactiver l'affichage des erreurs HTML
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Définir le type de contenu comme JSON
header('Content-Type: application/json');

try {
    session_start();
    require_once '../config.php';

    // Vérifier si l'utilisateur est connecté et est un admin
    if (!isset($_SESSION['admin_id'])) {
        throw new Exception('Accès non autorisé');
    }

    // Vérifier si la requête est de type POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Méthode non autorisée');
    }

    // Vérifier la connexion à la base de données
    if (!$conn) {
        throw new Exception('Erreur de connexion à la base de données');
    }

    // Récupérer et valider les données du formulaire
    $civilite = filter_input(INPUT_POST, 'Civilite', FILTER_SANITIZE_STRING);
    $nom = filter_input(INPUT_POST, 'Nom', FILTER_SANITIZE_STRING);
    $prenom = filter_input(INPUT_POST, 'Prenom', FILTER_SANITIZE_STRING);
    $niveau = filter_input(INPUT_POST, 'Niveau', FILTER_SANITIZE_STRING);
    $diplome = filter_input(INPUT_POST, 'Diplome', FILTER_SANITIZE_STRING);
    $specialite = filter_input(INPUT_POST, 'Specialite', FILTER_SANITIZE_STRING);
    $fonction_actuelle = filter_input(INPUT_POST, 'Fonction_actuelle', FILTER_SANITIZE_STRING);
    $telephone = filter_input(INPUT_POST, 'Telephone', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'Email', FILTER_SANITIZE_EMAIL);
    $pays = filter_input(INPUT_POST, 'Pays', FILTER_SANITIZE_STRING);
    $ville = filter_input(INPUT_POST, 'Ville', FILTER_SANITIZE_STRING);
    $date_de_naissance = filter_input(INPUT_POST, 'date_de_naissance', FILTER_SANITIZE_STRING);
    $situation_matrimoniale = filter_input(INPUT_POST, 'situation_matrimoniale', FILTER_SANITIZE_STRING);

    // Log des données reçues
    error_log('Données reçues: ' . print_r($_POST, true));

    // Validation des champs requis
    if (!$civilite || !$nom || !$prenom || !$niveau || !$diplome || !$specialite || !$fonction_actuelle || !$telephone || !$email || !$pays || !$ville || !$date_de_naissance || !$situation_matrimoniale) {
        throw new Exception('Tous les champs obligatoires doivent être remplis');
    }

    // Vérifier si l'email existe déjà
    $stmt = $conn->prepare("SELECT id FROM membres WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        throw new Exception('Cet email est déjà utilisé');
    }

    // Commencer une transaction
    $conn->beginTransaction();

    try {
        // Préparer la requête d'insertion
        $sql = "
            INSERT INTO membres (
                civilite, nom, prenom, niveau, diplome, specialite, 
                fonction_actuelle, telephone, email, pays, ville,
                date_de_naissance, situation_matrimoniale,
                date_inscription, statut
            ) VALUES (
                :civilite, :nom, :prenom, :niveau, :diplome, :specialite,
                :fonction_actuelle, :telephone, :email, :pays, :ville,
                :date_de_naissance, :situation_matrimoniale,
                NOW(), 'en_attente'
            )
        ";

        $stmt = $conn->prepare($sql);
        
        // Exécuter la requête avec les paramètres nommés
        $result = $stmt->execute([
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
            ':ville' => $ville,
            ':date_de_naissance' => $date_de_naissance,
            ':situation_matrimoniale' => $situation_matrimoniale
        ]);

        if (!$result) {
            throw new Exception('Erreur lors de l\'insertion des données');
        }

        // Valider la transaction
        $conn->commit();

        // Retourner une réponse de succès
        echo json_encode([
            'success' => true,
            'message' => 'Membre ajouté avec succès'
        ]);

    } catch (PDOException $e) {
        // Annuler la transaction en cas d'erreur
        $conn->rollBack();
        error_log('Erreur PDO dans add_member.php: ' . $e->getMessage());
        throw new Exception('Erreur lors de l\'insertion dans la base de données: ' . $e->getMessage());
    }

} catch (Exception $e) {
    // En cas d'erreur, retourner un message d'erreur
    error_log('Erreur dans add_member.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 