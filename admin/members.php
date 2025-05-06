<?php
require_once '../config.php';
require_once '../db_functions.php';

// Vérification de l'authentification (à implémenter selon vos besoins)
session_start();
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: ../index.html");
    exit();
}

// Récupération des membres
try {
    $members = get_all_members();
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Membres - FIER</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .admin-container {
            padding: 8rem 2rem 4rem;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .members-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2rem;
            background: white;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        }
        
        .members-table th,
        .members-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        .members-table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        
        .members-table tr:hover {
            background-color: #f5f5f5;
        }
        
        .status-badge {
            padding: 0.3rem 0.6rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .status-en_attente {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-approuve {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-rejete {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <div class="logo-container">
                <div class="logo">FIER</div>
                <div class="logo-acronym">Fraternité Ivoirienne et Républicaine</div>
                <div class="founder-name">Fondée par Monsieur JEAN BONIN</div>
            </div>
            <ul class="nav-links">
                <li><a href="../index.html">Accueil</a></li>
                <li><a href="../about.html">À Propos</a></li>
                <li><a href="../actions.html">Nos Actions</a></li>
                <li><a href="../join.html">Nous Rejoindre</a></li>
            </ul>
        </nav>
    </header>

    <main class="admin-container">
        <h1>Gestion des Membres</h1>
        
        <?php if (isset($error)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <table class="members-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th>Type</th>
                    <th>Statut</th>
                    <th>Date d'inscription</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($members as $member): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($member['id']); ?></td>
                        <td><?php echo htmlspecialchars($member['nom']); ?></td>
                        <td><?php echo htmlspecialchars($member['prenom']); ?></td>
                        <td><?php echo htmlspecialchars($member['email']); ?></td>
                        <td><?php echo htmlspecialchars($member['telephone']); ?></td>
                        <td><?php echo htmlspecialchars($member['type_membre']); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo htmlspecialchars($member['statut']); ?>">
                                <?php echo htmlspecialchars($member['statut']); ?>
                            </span>
                        </td>
                        <td><?php echo date('d/m/Y H:i', strtotime($member['date_inscription'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>

    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h3>Contact</h3>
                <p>Email: contact@fier.org</p>
                <p>Téléphone: +33 1 23 45 67 89</p>
                <p>Adresse: COCODY ANGRE (Cité ZINSOU)</p>
            </div>
            <div class="footer-section">
                <h3>Suivez-Nous</h3>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-linkedin"></i></a>
                    <a href="#"><i class="fab fa-tiktok"></i></a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 FIER. Tous droits réservés.</p>
        </div>
    </footer>
</body>
</html> 