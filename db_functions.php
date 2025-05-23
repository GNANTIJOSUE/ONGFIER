<?php
require_once 'config.php';

// Fonction pour ajouter un nouveau membre
function add_member($data) {
    global $conn;
    try {
        $stmt = $conn->prepare("INSERT INTO membres (
            civilite, nom, prenom, niveau, diplome, specialite, 
            fonction_actuelle, telephone, email, pays, ville, 
            date_inscription, statut
        ) VALUES (
            :civilite, :nom, :prenom, :niveau, :diplome, :specialite,
            :fonction_actuelle, :telephone, :email, :pays, :ville,
            NOW(), 'en_attente'
        )");
        
        $stmt->execute([
            ':civilite' => $data['civilite'],
            ':nom' => $data['nom'],
            ':prenom' => $data['prenom'],
            ':niveau' => $data['niveau'],
            ':diplome' => $data['diplome'],
            ':specialite' => $data['specialite'],
            ':fonction_actuelle' => $data['fonction_actuelle'],
            ':telephone' => $data['telephone'],
            ':email' => $data['email'],
            ':pays' => $data['pays'],
            ':ville' => $data['ville']
        ]);
        
        return $conn->lastInsertId();
    } catch(PDOException $e) {
        throw new Exception("Erreur lors de l'ajout du membre : " . $e->getMessage());
    }
}

// Fonction pour récupérer tous les membres
function get_all_members() {
    global $conn;
    try {
        $stmt = $conn->query("SELECT * FROM membres ORDER BY date_inscription DESC");
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        throw new Exception("Erreur lors de la récupération des membres : " . $e->getMessage());
    }
}

// Fonction pour récupérer les actions
function get_actions() {
    global $conn;
    try {
        $stmt = $conn->query("SELECT * FROM actions ORDER BY date_debut DESC");
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        throw new Exception("Erreur lors de la récupération des actions : " . $e->getMessage());
    }
}

// Fonction pour ajouter un participant à une action
function add_participant($membre_id, $action_id, $role) {
    global $conn;
    try {
        $stmt = $conn->prepare("INSERT INTO participants_actions (membre_id, action_id, role) VALUES (:membre_id, :action_id, :role)");
        $stmt->execute([
            ':membre_id' => $membre_id,
            ':action_id' => $action_id,
            ':role' => $role
        ]);
        return true;
    } catch(PDOException $e) {
        throw new Exception("Erreur lors de l'ajout du participant : " . $e->getMessage());
    }
}

// Fonction pour vérifier si un email existe déjà
function email_exists($email) {
    global $conn;
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM membres WHERE email = :email");
        $stmt->execute([':email' => $email]);
        return $stmt->fetchColumn() > 0;
    } catch(PDOException $e) {
        throw new Exception("Erreur lors de la vérification de l'email : " . $e->getMessage());
    }
}
?> 