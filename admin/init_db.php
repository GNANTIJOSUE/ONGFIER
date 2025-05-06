<?php
// Configuration de la base de données
$host = 'localhost';
$dbname = 'ngo_db';
$username = 'root';
$password = '';

try {
    // Création de la connexion PDO
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Vérifier si la table est vide
    $stmt = $conn->query("SELECT COUNT(*) as count FROM membres");
    $count = $stmt->fetch()['count'];

    if ($count == 0) {
        // Ajout de quelques données de test
        $sql = "INSERT INTO membres (nom, prenom, email, telephone, pays, ville, quartier, adresse, type_membre, date_inscription, statut) VALUES 
            ('Dupont', 'Jean', 'jean.dupont@example.com', '0123456789', 'France', 'Paris', 'Centre', '123 rue de Paris', 'membre_actif', NOW(), 'approuve'),
            ('Martin', 'Marie', 'marie.martin@example.com', '0987654321', 'France', 'Lyon', 'Nord', '456 avenue de Lyon', 'volontaire', NOW(), 'en_attente'),
            ('Dubois', 'Pierre', 'pierre.dubois@example.com', '0123456789', 'France', 'Marseille', 'Sud', '789 boulevard de Marseille', 'donateur', NOW(), 'approuve')";

        $conn->exec($sql);
        echo "Données de test ajoutées avec succès<br>";
    } else {
        echo "La table contient déjà " . $count . " membres<br>";
    }

} catch(PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}

$conn = null;
?> 