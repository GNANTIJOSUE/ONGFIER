<?php
require_once 'config.php';
require_once 'db_functions.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupération et nettoyage des données du formulaire
    $data = [
        'nom' => clean_input($_POST['nom'] ?? ''),
        'prenom' => clean_input($_POST['prenom'] ?? ''),
        'email' => clean_input($_POST['email'] ?? ''),
        'telephone' => clean_input($_POST['telephone'] ?? ''),
        'whatsapp' => clean_input($_POST['whatsapp'] ?? ''),
        'pays' => clean_input($_POST['pays'] ?? ''),
        'ville' => clean_input($_POST['ville'] ?? ''),
        'quartier' => clean_input($_POST['quartier'] ?? ''),
        'adresse' => clean_input($_POST['adresse'] ?? ''),
        'type_membre' => clean_input($_POST['type_membre'] ?? ''),
        'message' => clean_input($_POST['message'] ?? '')
    ];

    // Validation des données
    $errors = [];

    if (empty($data['nom'])) {
        $errors[] = "Le nom est requis";
    }

    if (empty($data['prenom'])) {
        $errors[] = "Le prénom est requis";
    }

    if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Une adresse email valide est requise";
    } elseif (email_exists($data['email'])) {
        $errors[] = "Cette adresse email est déjà utilisée";
    }

    if (empty($data['telephone'])) {
        $errors[] = "Le numéro de téléphone est requis";
    }

    if (empty($data['pays'])) {
        $errors[] = "Le pays de résidence est requis";
    }

    if (empty($data['ville'])) {
        $errors[] = "La ville de résidence est requise";
    }

    if (empty($data['quartier'])) {
        $errors[] = "Le quartier/commune est requis";
    }

    if (empty($data['adresse'])) {
        $errors[] = "L'adresse complète est requise";
    }

    if (empty($data['type_membre'])) {
        $errors[] = "Le type de membre est requis";
    }

    // Si aucune erreur, procéder à l'insertion dans la base de données
    if (empty($errors)) {
        try {
            $member_id = add_member($data);
            
            // Envoi d'un email de confirmation
            $to = $data['email'];
            $subject = "Confirmation d'inscription - FIER";
            $message = "Bonjour {$data['prenom']} {$data['nom']},\n\n";
            $message .= "Merci de votre inscription à FIER. Nous avons bien reçu votre demande d'adhésion.\n";
            $message .= "Type de membre : {$data['type_membre']}\n";
            $message .= "Pays de résidence : {$data['pays']}\n";
            $message .= "Ville : {$data['ville']}\n\n";
            $message .= "Notre équipe vous contactera dans les plus brefs délais pour la suite du processus.\n\n";
            $message .= "Cordialement,\nL'équipe FIER";
            
            $headers = "From: contact@fier.org\r\n";
            $headers .= "Reply-To: contact@fier.org\r\n";
            $headers .= "X-Mailer: PHP/" . phpversion();
            
            mail($to, $subject, $message, $headers);
            
            // Redirection vers la page de succès
            header("Location: success.html");
            exit();
        } catch (Exception $e) {
            $errors[] = "Une erreur est survenue : " . $e->getMessage();
        }
    }
}

// Si nous arrivons ici, c'est qu'il y a eu une erreur
// Redirection vers la page d'inscription avec les erreurs
session_start();
$_SESSION['errors'] = $errors;
$_SESSION['form_data'] = $_POST;
header("Location: join.html");
exit();
?> 