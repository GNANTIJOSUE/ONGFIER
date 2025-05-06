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
    $nom = filter_input(INPUT_POST, 'nom', FILTER_SANITIZE_STRING);
    $prenom = filter_input(INPUT_POST, 'prenom', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $telephone = filter_input(INPUT_POST, 'telephone', FILTER_SANITIZE_STRING);
    $whatsapp = filter_input(INPUT_POST, 'whatsapp', FILTER_SANITIZE_STRING);
    $pays = filter_input(INPUT_POST, 'pays', FILTER_SANITIZE_STRING);
    $ville = filter_input(INPUT_POST, 'ville', FILTER_SANITIZE_STRING);
    $quartier = filter_input(INPUT_POST, 'quartier', FILTER_SANITIZE_STRING);
    $adresse = filter_input(INPUT_POST, 'adresse', FILTER_SANITIZE_STRING);
    $type_membre = filter_input(INPUT_POST, 'type_membre', FILTER_SANITIZE_STRING);
    $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);

    // Log des données reçues
    error_log('Données reçues: ' . print_r($_POST, true));

    // Validation des champs requis
    if (!$nom || !$prenom || !$email || !$telephone || !$pays || !$ville || !$quartier || !$adresse || !$type_membre) {
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
                nom, prenom, email, telephone, whatsapp, 
                pays, ville, quartier, adresse, type_membre, message, 
                date_inscription, statut
            ) VALUES (
                :nom, :prenom, :email, :telephone, :whatsapp, 
                :pays, :ville, :quartier, :adresse, :type_membre, :message, 
                NOW(), 'en_attente'
            )
        ";

        $stmt = $conn->prepare($sql);
        
        // Exécuter la requête avec les paramètres nommés
        $result = $stmt->execute([
            ':nom' => $nom,
            ':prenom' => $prenom,
            ':email' => $email,
            ':telephone' => $telephone,
            ':whatsapp' => $whatsapp,
            ':pays' => $pays,
            ':ville' => $ville,
            ':quartier' => $quartier,
            ':adresse' => $adresse,
            ':type_membre' => $type_membre,
            ':message' => $message
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