<?php
require_once '../config.php';
require_once 'auth.php';
requireLogin();

// Ajouter cette fonction helper au début du fichier, après les includes
function safe_htmlspecialchars($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// Récupérer les statistiques
// Nombre total de membres
$stmt = $pdo->query("SELECT COUNT(*) as total FROM membres");
$totalMembers = $stmt->fetch()['total'];

// Nombre de messages non lus (à implémenter plus tard)
$unreadMessages = 0;

// Récupérer la liste des administrateurs si super admin
$admins = [];
if (isSuperAdmin()) {
    $stmt = $pdo->query("SELECT id, name, email, role, created_at FROM admins ORDER BY created_at DESC");
    $admins = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord - FIER</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .dashboard {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 250px;
            background: #2c3e50;
            color: white;
            padding: 2rem 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }
        
        .sidebar-header {
            padding: 0 1.5rem;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .sidebar-header img {
            height: 40px;
        }
        
        .sidebar-header h2 {
            font-size: 1.2rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 0;
        }
        
        .sidebar-menu li {
            margin-bottom: 0.5rem;
        }
        
        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 1rem 1.5rem;
            color: #ecf0f1;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: #34495e;
            color: #3498db;
        }
        
        .sidebar-menu i {
            margin-right: 1rem;
            width: 20px;
            text-align: center;
        }
        
        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 2rem;
            background: #f5f7fa;
        }
        
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding: 1rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #3498db;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
        
        .logout-btn {
            padding: 0.5rem 1rem;
            background: #e74c3c;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .logout-btn:hover {
            background: #c0392b;
        }
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, #3498db, #2ecc71);
        }
        
        .stat-card h3 {
            color: #2c3e50;
            margin-bottom: 1rem;
            font-size: 1.2rem;
        }
        
        .stat-card .number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #3498db;
            margin-bottom: 0.5rem;
        }
        
        .stat-card .label {
            color: #7f8c8d;
            font-size: 0.9rem;
        }
        
        .content-section {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }
        
        .content-section h2 {
            color: #2c3e50;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #eee;
        }
        
        .table-container {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th,
        td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .action-btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-right: 0.5rem;
        }
        
        .edit-btn {
            background: #3498db;
            color: white;
        }
        
        .delete-btn {
            background: #e74c3c;
            color: white;
        }
        
        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        
        .add-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.8rem 1.5rem;
            background: linear-gradient(45deg, #3498db, #2ecc71);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 1.5rem;
        }
        
        .add-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
        }
        
        .role-badge {
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .role-badge.super-admin {
            background: linear-gradient(45deg, #3498db, #2ecc71);
            color: white;
        }
        
        .role-badge.admin {
            background: #f1f1f1;
            color: #666;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            overflow-y: auto;
        }
        
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 2rem;
            border-radius: 10px;
            width: 80%;
            max-width: 800px;
            position: relative;
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .close {
            position: absolute;
            right: 1rem;
            top: 1rem;
            font-size: 1.5rem;
            cursor: pointer;
            color: #666;
            z-index: 1;
        }
        
        .close:hover {
            color: #000;
        }
        /* Style pour le formulaire dans le modal */
        
        .modal-content form {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-group.full-width {
            grid-column: 1 / -1;
        }
        
        .form-actions {
            grid-column: 1 / -1;
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }
        
        .form-actions button {
            flex: 1;
        }
        
        @media (max-width: 768px) {
            .modal-content {
                width: 95%;
                margin: 2% auto;
            }
            .modal-content form {
                grid-template-columns: 1fr;
            }
        }
        /* Ajout de styles pour les nouvelles fonctionnalités */
        
        .search-container {
            margin-bottom: 1.5rem;
            display: flex;
            gap: 1rem;
        }
        
        .search-input-group {
            display: flex;
            gap: 0.5rem;
            flex: 1;
        }
        
        .search-input {
            flex: 1;
            padding: 0.8rem;
            border: 2px solid #eee;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .search-btn {
            padding: 0.8rem 1.5rem;
            background: linear-gradient(45deg, #3498db, #2ecc71);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }
        
        .search-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
        }
        
        .search-btn i {
            font-size: 1rem;
        }
        
        .filter-container {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .filter-btn {
            padding: 0.5rem 1rem;
            background: #f8f9fa;
            border: 2px solid #eee;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .filter-btn.active {
            background: #3498db;
            color: white;
            border-color: #3498db;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 1.5rem;
        }
        
        .pagination-btn {
            padding: 0.5rem 1rem;
            background: #f8f9fa;
            border: 2px solid #eee;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .pagination-btn.active {
            background: #3498db;
            color: white;
            border-color: #3498db;
        }
        
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            display: none;
        }
        
        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(360deg);
            }
        }
        
        .success-message {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 1rem;
            background: #2ecc71;
            color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            animation: slideIn 0.3s ease-out;
        }
        
        @keyframes slideIn {
            from {
                transform: translateX(100%);
            }
            to {
                transform: translateX(0);
            }
        }
        /* Styles pour le formulaire d'ajout de membre */
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #2c3e50;
            font-weight: 600;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid #eee;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }
        
        .submit-btn {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(45deg, #3498db, #2ecc71);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
        }
        
        .password-strength {
            margin-top: 0.5rem;
            height: 5px;
            border-radius: 3px;
            transition: all 0.3s ease;
        }
        
        .password-strength.weak {
            background: #e74c3c;
            width: 33%;
        }
        
        .password-strength.medium {
            background: #f39c12;
            width: 66%;
        }
        
        .password-strength.strong {
            background: #2ecc71;
            width: 100%;
        }
        
        .password-requirements {
            margin-top: 0.5rem;
            font-size: 0.9rem;
            color: #7f8c8d;
        }
        
        .password-requirements ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .password-requirements li {
            margin-bottom: 0.3rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .password-requirements li i {
            font-size: 0.8rem;
        }
        
        .password-requirements li.valid {
            color: #2ecc71;
        }
        
        .password-requirements li.invalid {
            color: #e74c3c;
        }
        /* Styles supplémentaires pour le formulaire d'inscription */
        
        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }
        
        .cancel-btn {
            flex: 1;
            padding: 1rem;
            background: #f8f9fa;
            color: #2c3e50;
            border: 2px solid #eee;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .cancel-btn:hover {
            background: #eee;
        }
        
        .submit-btn {
            flex: 2;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        .modal-content {
            max-width: 600px;
            width: 90%;
        }
        /* Style pour le titre de la section */
        
        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 1.5rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 2px solid #3498db;
            padding-bottom: 0.5rem;
            display: inline-block;
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
        
        .edit-btn,
        .delete-btn {
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.5rem;
            margin: 0 0.2rem;
            border-radius: 4px;
            transition: all 0.3s ease;
        }
        
        .edit-btn {
            color: #3498db;
        }
        
        .edit-btn:hover {
            background-color: rgba(52, 152, 219, 0.1);
        }
        
        .delete-btn {
            color: #e74c3c;
        }
        
        .delete-btn:hover {
            background-color: rgba(231, 76, 60, 0.1);
        }
        
        .error-message {
            color: #e74c3c;
            text-align: center;
            padding: 1rem;
            background-color: #fde8e8;
            border-radius: 4px;
        }
    </style>
</head>

<body>
    <div class="loading-overlay">
        <div class="loading-spinner"></div>
    </div>

    <div class="dashboard">
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="../img/logo.jpg" alt="Logo FIER">
                <h2>Admin</h2>
            </div>
            <ul class="sidebar-menu">
                <li><a href="#" class="active"><i class="fas fa-home"></i> Tableau de bord</a></li>
                <li><a href="members.php"><i class="fas fa-users"></i> Membres</a></li>
                <li><a href="#"><i class="fas fa-newspaper"></i> Actualités</a></li>
                <li><a href="#"><i class="fas fa-calendar-alt"></i> Événements</a></li>
                <li><a href="#"><i class="fas fa-image"></i> Galerie</a></li>
                <li><a href="#"><i class="fas fa-cog"></i> Paramètres</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <div class="top-bar">
                <h1>Tableau de Bord</h1>
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo substr($_SESSION['admin_name'], 0, 2); ?>
                    </div>
                    <span><?php echo $_SESSION['admin_name']; ?></span>
                    <a href="#" onclick="confirmLogout()" class="logout-btn">Déconnexion</a>
                </div>
            </div>

            <div class="dashboard-grid">
                <div class="stat-card">
                    <h3>Membres Totaux</h3>
                    <div class="number">
                        <?php echo $totalMembers; ?>
                    </div>
                    <div class="label">Membres actifs</div>
                </div>
                <div class="stat-card">
                    <h3>Messages non lus</h3>
                    <div class="number">
                        <?php echo $unreadMessages; ?>
                    </div>
                    <div class="label">À traiter</div>
                </div>
            </div>

            <div class="content-section">
                <h2 class="section-title">Liste des membres</h2>
                <button class="add-btn" onclick="openAddMemberModal()">
                    <i class="fas fa-user-plus"></i>
                    Inscrire un nouveau membre
                </button>
                <div class="search-container">
                    <div class="search-input-group">
                        <input type="text" class="search-input" id="memberSearch" 
                               placeholder="Rechercher par nom, prénom ou ville...">
                        <button class="search-btn" onclick="searchMembers()">
                            <i class="fas fa-search"></i>
                            Rechercher
                        </button>
                    </div>
                    <div class="filter-container">
                        <button class="filter-btn active" data-filter="all">Tous</button>
                        <button class="filter-btn" data-filter="active">Actifs</button>
                        <button class="filter-btn" data-filter="inactive">Inactifs</button>
                    </div>
                </div>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nom</th>
                                <th>Prénom</th>
                                <th>Email</th>
                                <th>Téléphone</th>
                                <th>Fonction actuelle</th>
                                <th>Statut</th>
                                <th>Ville</th>
                                <th>Date de naissance</th>
                                <th>Situation matrimoniale</th>
                                <th>Date d'inscription</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="membersTableBody">
                            <?php
                            error_reporting(E_ALL);
                            ini_set('display_errors', 1);
                            
                            try {
                                // Vérifier la connexion
                                if (!$conn) {
                                    throw new Exception("La connexion à la base de données n'est pas établie");
                                }
                                
                                // Vérifier si la table existe
                                $tableExists = $conn->query("SHOW TABLES LIKE 'membres'")->rowCount() > 0;
                                if (!$tableExists) {
                                    throw new Exception("La table 'membres' n'existe pas dans la base de données");
                                }
                                
                                // Compter le nombre total de membres
                                $countStmt = $conn->query("SELECT COUNT(*) as total FROM membres");
                                $totalMembers = $countStmt->fetch()['total'];
                                echo "<!-- Nombre total de membres : " . $totalMembers . " -->";
                                
                                // Exécuter la requête avec un LIMIT pour déboguer
                                $stmt = $conn->query("SELECT * FROM membres ORDER BY date_inscription DESC LIMIT 5");
                                
                                // Vérifier si des résultats sont retournés
                                if ($stmt->rowCount() === 0) {
                                    echo '<tr><td colspan="9" class="error-message">Aucun membre trouvé dans la base de données</td></tr>';
                                } else {
                                    while ($member = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        echo '<tr>';
                                        echo '<td>' . safe_htmlspecialchars($member['id']) . '</td>';
                                        echo '<td>' . safe_htmlspecialchars($member['nom']) . '</td>';
                                        echo '<td>' . safe_htmlspecialchars($member['prenom']) . '</td>';
                                        echo '<td>' . safe_htmlspecialchars($member['email']) . '</td>';
                                        echo '<td>' . safe_htmlspecialchars($member['telephone']) . '</td>';
                                        echo '<td>' . safe_htmlspecialchars($member['fonction_actuelle'] ?? 'Non spécifié') . '</td>';
                                        echo '<td><span class="status-badge status-' . safe_htmlspecialchars($member['statut']) . '">' . safe_htmlspecialchars($member['statut']) . '</span></td>';
                                        echo '<td>' . safe_htmlspecialchars($member['ville']) . '</td>';
                                        echo '<td>' . safe_htmlspecialchars($member['date_de_naissance']) . '</td>';
                                        echo '<td>' . safe_htmlspecialchars($member['situation_matrimoniale']) . '</td>';
                                        echo '<td>' . date('d/m/Y H:i', strtotime($member['date_inscription'])) . '</td>';
                                        echo '<td>
                                            <button class="edit-btn" onclick="openEditMemberModal(' . safe_htmlspecialchars($member['id']) . ')">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="delete-btn" data-member-id="' . safe_htmlspecialchars($member['id']) . '">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>';
                                        echo '</tr>';
                                    }
                                }
                            } catch (PDOException $e) {
                                echo '<tr><td colspan="9" class="error-message">Erreur PDO: ' . safe_htmlspecialchars($e->getMessage()) . '</td></tr>';
                            } catch (Exception $e) {
                                echo '<tr><td colspan="9" class="error-message">Erreur: ' . safe_htmlspecialchars($e->getMessage()) . '</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Section des administrateurs -->
            <div class="content-section">
                <h2 class="section-title">Gestion des Administrateurs</h2>
                <button class="add-btn" onclick="openAddAdminModal()">
                    <i class="fas fa-user-plus"></i> Ajouter un administrateur
                </button>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nom</th>
                                <th>Email</th>
                                <th>Rôle</th>
                                <th>Date de création</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            try {
                                $stmt = $pdo->query("SELECT * FROM admins ORDER BY created_at DESC");
                                while ($admin = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo '<tr>';
                                    echo '<td>' . safe_htmlspecialchars($admin['id']) . '</td>';
                                    echo '<td>' . safe_htmlspecialchars($admin['name']) . '</td>';
                                    echo '<td>' . safe_htmlspecialchars($admin['email']) . '</td>';
                                    echo '<td><span class="role-badge ' . safe_htmlspecialchars($admin['role'] === 'super_admin' ? 'super-admin' : 'admin') . '">';
                                    echo safe_htmlspecialchars($admin['role'] === 'super_admin' ? 'Super Admin' : 'Admin');
                                    echo '</span></td>';
                                    echo '<td>' . date('d/m/Y H:i', strtotime($admin['created_at'])) . '</td>';
                                    echo '<td>';
                                    if (safe_htmlspecialchars($admin['role']) !== 'super_admin') {
                                        echo '<button class="edit-btn" onclick="openEditAdminModal(' . safe_htmlspecialchars($admin['id']) . ')">
                                                <i class="fas fa-edit"></i>
                                              </button>';
                                        echo '<button class="delete-btn" onclick="deleteAdmin(' . safe_htmlspecialchars($admin['id']) . ', \'' . safe_htmlspecialchars($admin['name']) . '\')">
                                                <i class="fas fa-trash"></i>
                                              </button>';
                                    }
                                    echo '</td>';
                                    echo '</tr>';
                                }
                            } catch (PDOException $e) {
                                echo '<tr><td colspan="6" class="error-message">Erreur lors de la récupération des administrateurs</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Formulaire de modification (caché par défaut) -->
    <div id="editModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Modifier les informations</h2>
            <form id="editForm" method="POST">
                <input type="hidden" id="editId" name="id">
                <input type="hidden" id="editType" name="type">

                <div class="form-group">
                    <label for="editName">Nom complet</label>
                    <input type="text" id="editName" name="name" required>
                </div>

                <div class="form-group">
                    <label for="editEmail">Email</label>
                    <input type="email" id="editEmail" name="email" required>
                </div>

                <div class="form-group">
                    <label for="editPassword">Nouveau mot de passe (laisser vide pour ne pas changer)</label>
                    <input type="password" id="editPassword" name="password">
                </div>

                <button type="submit" class="submit-btn">Enregistrer les modifications</button>
            </form>
        </div>
    </div>

    <!-- Formulaire d'ajout de membre -->
    <div id="addMemberModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeAddMemberModal()">&times;</span>
            <h2>Inscription d'un nouveau membre</h2>
            <form id="addMemberForm" method="POST">
            <div class="form-group full-width">
                <label for="Civilite" class="required-field">Civilité</label>
                <select id="Civilite" name="Civilite" required>
                    <option value="">CHOISIR</option>
                    <option value="M">M</option>
                    <option value="Mme">Mme</option>
                    <option value="Mmelle">Mmelle</option>
                </select>
            </div>
            <div class="form-group">
                <label for="Nom" class="required-field">Nom</label>
                <input type="text" id="Nom" name="Nom" required placeholder="Entrez votre nom">
            </div>
            <div class="form-group">
                <label for="Prenom" class="required-field">Prénoms</label>
                <input type="text" id="Prenom" name="Prenom" required placeholder="Entrez vos prénoms">
            </div>
            <div class="form-group">
                <label for="Niveau" class="required-field">Niveau</label>
                <select id="Niveau" name="Niveau" required>
                    <option value="">CHOISIR</option>
                    <option value="Aucun">Aucun</option>
                    <option value="Primaire">Primaire</option>
                    <option value="Secondaire">Secondaire</option>
                    <option value="Bac+1">Bac+1</option>
                    <option value="Bac+2">Bac+2</option>
                    <option value="Bac+3">Bac+3</option>
                    <option value="Bac+4">Bac+4</option>
                    <option value="Bac+5">Bac+5</option>
                    <option value="Bac+6">Bac+5+</option>
                    <option value="Bac+8">Bac+8</option>
                    <option value="Bac+8+">Bac+8+</option>
                </select>
            </div>
            <div class="form-group">
                <label for="Diplome" class="required-field">Diplôme</label>
                <select id="Diplome" name="Diplome" required>
                    <option value="">CHOISIR</option>
                    <option value="Aucun">Aucun</option>
                    <option value="CEPE">CEPE</option>
                    <option value="BEPC">BEPC</option>
                    <option value="CAP">CAP</option>
                    <option value="BEP">BEP</option>
                    <option value="BT">BT</option>
                    <option value="BAC">BAC</option>
                    <option value="DUT">DUT</option>
                    <option value="BTS">BTS</option>
                    <option value="Licence">Licence</option>
                    <option value="Maîtrise">Maîtrise</option>
                    <option value="Master">Master</option>
                    <option value="Ingénieur">Ingénieur</option>
                    <option value="MBA">MBA</option>
                    <option value="DEA/Equivalent">DEA/Equivalent</option>
                    <option value="Doctorat">Doctorat</option>
                    <option value="HDR(Post-doctorat)">HDR(Post-doctorat)</option>
                </select>
            </div>
            <div class="form-group">
                <label for="Specialite" class="required-field">Spécialité</label>
                <input type="text" id="Specialite" name="Specialite" required placeholder="Entrez votre spécialité">
            </div>
            <div class="form-group">
                <label for="Fonction_actuelle" class="required-field">Fonction actuelle</label>
                <input type="text" id="Fonction_actuelle" name="Fonction_actuelle" required placeholder="Fonction actuelle">
            </div>
            <div class="form-group">
                <label for="Telephone" class="required-field">Téléphone</label>
                <input type="text" id="Telephone" name="Telephone" required placeholder="Entrez votre téléphone">
            </div>
            <div class="form-group">
                <label for="Email" class="required-field">Email</label>
                <input type="email" id="Email" name="Email" required placeholder="Entrez votre email">
            </div>
            <div class="form-group">
                <label for="Pays" class="required-field">Pays habité</label>
                <select id="Pays" name="Pays" required>
                    <option value="">CHOISIR</option>
                    <option value="Côte d'Ivoire">Côte d'Ivoire</option>
                    <option value="France">France</option>
                    <option value="Burkina Faso">Burkina Faso</option>
                    <option value="Sénégal">Sénégal</option>
                    <option value="Mali">Mali</option>
                    <option value="Bénin">Bénin</option>
                    <option value="Togo">Togo</option>
                    <option value="Autre">Autre</option>
                </select>
            </div>
            <div class="form-group">
                <label for="Ville" class="required-field">Ville/Commune habitée</label>
                <input type="text" id="Ville" name="Ville" required placeholder="Entrez votre ville ou commune habitée">
            </div>
            <div class="form-group">
                <label for="date_de_naissance" class="required-field">Date de naissance</label>
                <input type="date" id="date_de_naissance" name="date_de_naissance" required>
            </div>
            <div class="form-group">
                <label for="situation_matrimoniale" class="required-field">Situation matrimoniale</label>
                <select id="situation_matrimoniale" name="situation_matrimoniale" required>
                    <option value="">CHOISIR</option>
                    <option value="Célibataire">Célibataire</option>
                    <option value="Marié(e)">Marié(e)</option>
                    <option value="Divorcé(e)">Divorcé(e)</option>
                    <option value="Veuf/Veuve">Veuf/Veuve</option>
                    <option value="En concubinage">En concubinage</option>
                </select>
            </div>
            <div class="form-actions">
                <button type="button" class="cancel-btn" onclick="closeAddMemberModal()">
                    Annuler
                </button>
                <button type="submit" class="submit-btn">
                    <i class="fas fa-user-plus"></i>
                    Inscrire le membre
                </button>
            </div>
        </form>
        </div>
    </div>

    <!-- Modal de modification de membre -->
    <div id="editMemberModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditMemberModal()">&times;</span>
            <h2>Modifier les informations du membre</h2>
            <form id="editMemberForm" method="POST">
                <input type="hidden" id="edit_id" name="id">
                
                <div class="form-group full-width">
                    <label for="edit_Civilite" class="required-field">Civilité</label>
                    <select id="edit_Civilite" name="Civilite" required>
                        <option value="">CHOISIR</option>
                        <option value="M">M</option>
                        <option value="Mme">Mme</option>
                        <option value="Mmelle">Mmelle</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="edit_Nom" class="required-field">Nom</label>
                    <input type="text" id="edit_Nom" name="Nom" required placeholder="Entrez votre nom">
                </div>
                
                <div class="form-group">
                    <label for="edit_Prenom" class="required-field">Prénoms</label>
                    <input type="text" id="edit_Prenom" name="Prenom" required placeholder="Entrez vos prénoms">
                </div>
                
                <div class="form-group">
                    <label for="edit_Niveau" class="required-field">Niveau</label>
                    <select id="edit_Niveau" name="Niveau" required>
                        <option value="">CHOISIR</option>
                        <option value="Aucun">Aucun</option>
                        <option value="Primaire">Primaire</option>
                        <option value="Secondaire">Secondaire</option>
                        <option value="Bac+1">Bac+1</option>
                        <option value="Bac+2">Bac+2</option>
                        <option value="Bac+3">Bac+3</option>
                        <option value="Bac+4">Bac+4</option>
                        <option value="Bac+5">Bac+5</option>
                        <option value="Bac+6">Bac+5+</option>
                        <option value="Bac+8">Bac+8</option>
                        <option value="Bac+8+">Bac+8+</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="edit_Diplome" class="required-field">Diplôme</label>
                    <select id="edit_Diplome" name="Diplome" required>
                        <option value="">CHOISIR</option>
                        <option value="Aucun">Aucun</option>
                        <option value="CEPE">CEPE</option>
                        <option value="BEPC">BEPC</option>
                        <option value="CAP">CAP</option>
                        <option value="BEP">BEP</option>
                        <option value="BT">BT</option>
                        <option value="BAC">BAC</option>
                        <option value="DUT">DUT</option>
                        <option value="BTS">BTS</option>
                        <option value="Licence">Licence</option>
                        <option value="Maîtrise">Maîtrise</option>
                        <option value="Master">Master</option>
                        <option value="Ingénieur">Ingénieur</option>
                        <option value="MBA">MBA</option>
                        <option value="DEA/Equivalent">DEA/Equivalent</option>
                        <option value="Doctorat">Doctorat</option>
                        <option value="HDR(Post-doctorat)">HDR(Post-doctorat)</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="edit_Specialite" class="required-field">Spécialité</label>
                    <input type="text" id="edit_Specialite" name="Specialite" required placeholder="Entrez votre spécialité">
                </div>
                
                <div class="form-group">
                    <label for="edit_Fonction_actuelle" class="required-field">Fonction actuelle</label>
                    <input type="text" id="edit_Fonction_actuelle" name="Fonction_actuelle" required placeholder="Fonction actuelle">
                </div>
                
                <div class="form-group">
                    <label for="edit_Telephone" class="required-field">Téléphone</label>
                    <input type="text" id="edit_Telephone" name="Telephone" required placeholder="Entrez votre téléphone">
                </div>
                
                <div class="form-group">
                    <label for="edit_Email" class="required-field">Email</label>
                    <input type="email" id="edit_Email" name="Email" required placeholder="Entrez votre email">
                </div>
                
                <div class="form-group">
                    <label for="edit_Pays" class="required-field">Pays habité</label>
                    <select id="edit_Pays" name="Pays" required>
                        <option value="">CHOISIR</option>
                        <option value="Côte d'Ivoire">Côte d'Ivoire</option>
                        <option value="France">France</option>
                        <option value="Burkina Faso">Burkina Faso</option>
                        <option value="Sénégal">Sénégal</option>
                        <option value="Mali">Mali</option>
                        <option value="Bénin">Bénin</option>
                        <option value="Togo">Togo</option>
                        <option value="Autre">Autre</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="edit_Ville" class="required-field">Ville/Commune habitée</label>
                    <input type="text" id="edit_Ville" name="Ville" required placeholder="Entrez votre ville ou commune habitée">
                </div>
                
                <div class="form-group">
                    <label for="edit_date_de_naissance" class="required-field">Date de naissance</label>
                    <input type="date" id="edit_date_de_naissance" name="date_de_naissance" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_situation_matrimoniale" class="required-field">Situation matrimoniale</label>
                    <select id="edit_situation_matrimoniale" name="situation_matrimoniale" required>
                        <option value="">CHOISIR</option>
                        <option value="Célibataire">Célibataire</option>
                        <option value="Marié(e)">Marié(e)</option>
                        <option value="Divorcé(e)">Divorcé(e)</option>
                        <option value="Veuf/Veuve">Veuf/Veuve</option>
                        <option value="En concubinage">En concubinage</option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="cancel-btn" onclick="closeEditMemberModal()">
                        Annuler
                    </button>
                    <button type="submit" class="submit-btn">
                        <i class="fas fa-save"></i>
                        Enregistrer les modifications
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal d'ajout d'administrateur -->
    <div id="addAdminModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeAddAdminModal()">&times;</span>
            <h2>Ajouter un nouvel administrateur</h2>
            <form id="addAdminForm" method="POST">
                <div class="form-group">
                    <label for="admin_name">Nom complet</label>
                    <input type="text" id="admin_name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="admin_email">Email</label>
                    <input type="email" id="admin_email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="admin_password">Mot de passe</label>
                    <input type="password" id="admin_password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="admin_confirm_password">Confirmer le mot de passe</label>
                    <input type="password" id="admin_confirm_password" name="confirm_password" required>
                </div>
                <div class="form-actions">
                    <button type="button" class="cancel-btn" onclick="closeAddAdminModal()">Annuler</button>
                    <button type="submit" class="submit-btn">Ajouter l'administrateur</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal de modification d'administrateur -->
    <div id="editAdminModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditAdminModal()">&times;</span>
            <h2>Modifier l'administrateur</h2>
            <form id="editAdminForm" method="POST">
                <input type="hidden" id="edit_admin_id" name="id">
                <div class="form-group">
                    <label for="edit_admin_name">Nom complet</label>
                    <input type="text" id="edit_admin_name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="edit_admin_email">Email</label>
                    <input type="email" id="edit_admin_email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="edit_admin_password">Nouveau mot de passe (laisser vide pour ne pas changer)</label>
                    <input type="password" id="edit_admin_password" name="password">
                </div>
                <div class="form-group">
                    <label for="edit_admin_confirm_password">Confirmer le nouveau mot de passe</label>
                    <input type="password" id="edit_admin_confirm_password" name="confirm_password">
                </div>
                <div class="form-actions">
                    <button type="button" class="cancel-btn" onclick="closeEditAdminModal()">Annuler</button>
                    <button type="submit" class="submit-btn">Enregistrer les modifications</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Fonction de confirmation de déconnexion
        function confirmLogout() {
            if (confirm('Êtes-vous sûr de vouloir vous déconnecter ?')) {
                window.location.href = 'auth.php?action=logout';
            }
        }

        // Fermer le modal
        document.querySelector('.close').addEventListener('click', function() {
            document.getElementById('editModal').style.display = 'none';
        });

        // Gestion de la soumission du formulaire
        document.getElementById('editForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const type = formData.get('type');
            const endpoint = type === 'member' ? 'update_member.php' : 'update_admin.php';

            fetch(endpoint, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Modifications enregistrées avec succès');
                        location.reload();
                    } else {
                        console.log('Erreur lors de la modification : ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Une erreur est survenue');
                });
        });

        // Fonction pour supprimer un membre
        function deleteMember(memberId, row) {
            const memberName = row.querySelector('td:nth-child(2)').textContent + ' ' + row.querySelector('td:nth-child(3)').textContent;

            if (confirm(`Êtes-vous sûr de vouloir supprimer le membre "${memberName}" ? Cette action est irréversible.`)) {
                showLoader();
                fetch('delete_member.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `member_id=${memberId}`
                })
                .then(response => response.json())
                .then(data => {
                    hideLoader();
                    if (data.success) {
                        // Supprimer la ligne du tableau
                        row.remove();
                        
                        // Mettre à jour le compteur de membres
                        const totalMembersElement = document.querySelector('.stat-card .number');
                        if (totalMembersElement) {
                            const currentCount = parseInt(totalMembersElement.textContent);
                            totalMembersElement.textContent = currentCount - 1;
                        }

                        // Vérifier s'il reste des membres dans le tableau
                        const tableBody = document.querySelector('#membersTableBody');
                        if (tableBody.children.length === 0) {
                            tableBody.innerHTML = '<tr><td colspan="9" class="error-message">Aucun membre trouvé dans la base de données</td></tr>';
                        }

                        showSuccessMessage('Membre supprimé avec succès');
                    } else {
                        showErrorMessage(data.message || 'Erreur lors de la suppression du membre');
                    }
                })
                .catch(error => {
                    hideLoader();
                    showErrorMessage('Une erreur est survenue lors de la suppression');
                    console.error('Erreur:', error);
                });
            }
        }

        // Gestion de la suppression des membres
        document.addEventListener('click', function(e) {
            if (e.target.closest('.delete-btn[data-member-id]')) {
                const btn = e.target.closest('.delete-btn[data-member-id]');
                const memberId = btn.getAttribute('data-member-id');
                const row = btn.closest('tr');
                deleteMember(memberId, row);
            }
        });

        // Gestion de la suppression des administrateurs
        document.querySelectorAll('.delete-btn[data-admin-id]').forEach(btn => {
            btn.addEventListener('click', function() {
                const adminId = this.getAttribute('data-admin-id');
                const adminName = this.getAttribute('data-admin-name');
                const row = this.closest('tr');

                if (confirm(`Êtes-vous sûr de vouloir supprimer l'administrateur "${adminName}" ? Cette action est irréversible.`)) {
                    showLoader();
                    fetch('delete_admin.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `admin_id=${adminId}`
                        })
                        .then(response => response.json())
                        .then(data => {
                            hideLoader();
                            if (data.success) {
                                row.remove();
                                showSuccessMessage('Administrateur supprimé avec succès');
                            } else {
                                alert('Erreur lors de la suppression : ' + data.message);
                            }
                        })
                        .catch(error => {
                            hideLoader();
                            alert('Une erreur est survenue');
                        });
                }
            });
        });

        // Fonction de recherche des membres
        function searchMembers() {
            const searchTerm = document.getElementById('memberSearch').value.toLowerCase();
            const tableBody = document.querySelector('#membersTableBody');
            const rows = Array.from(tableBody.querySelectorAll('tr'));
            let found = false;

            // Réinitialiser l'affichage de tous les membres
            rows.forEach(row => {
                row.style.display = '';
            });

            if (!searchTerm) {
                // Si le terme de recherche est vide, afficher tous les membres
                return;
            }

            // Rechercher les membres correspondants
            const matchingRows = rows.filter(row => {
                // Les indices commencent à 1, donc :
                // - Nom est dans la colonne 2
                // - Prénom est dans la colonne 3
                // - Ville est dans la colonne 8
                const nom = row.cells[1].textContent.toLowerCase();
                const prenom = row.cells[2].textContent.toLowerCase();
                const ville = row.cells[7].textContent.toLowerCase();
                
                return nom.includes(searchTerm) || 
                       prenom.includes(searchTerm) || 
                       ville.includes(searchTerm);
            });

            if (matchingRows.length > 0) {
                // Supprimer le message "Aucun résultat" s'il existe
                const existingMessage = document.querySelector('.no-results-message');
                if (existingMessage) {
                    existingMessage.remove();
                }

                // Cacher tous les membres
                rows.forEach(row => {
                    row.style.display = 'none';
                });

                // Afficher et mettre en évidence les membres trouvés
                matchingRows.forEach(row => {
                    row.style.display = '';
                    row.style.backgroundColor = '#f8f9fa';
                    row.style.borderLeft = '4px solid #3498db';
                });

                found = true;
            } else {
                showNoResultsMessage();
            }

            // Réinitialiser le style après 3 secondes
            if (matchingRows.length > 0) {
                setTimeout(() => {
                    matchingRows.forEach(row => {
                        row.style.backgroundColor = '';
                        row.style.borderLeft = '';
                    });
                }, 3000);
            }
        }

        // Fonction pour afficher un message quand aucun résultat n'est trouvé
        function showNoResultsMessage() {
            const tableBody = document.querySelector('table tbody');
            const existingMessage = document.querySelector('.no-results-message');

            if (existingMessage) {
                existingMessage.remove();
            }

            const message = document.createElement('tr');
            message.className = 'no-results-message';
            message.innerHTML = `
                <td colspan="5" style="text-align: center; padding: 2rem; color: #7f8c8d;">
                    <i class="fas fa-search" style="font-size: 2rem; margin-bottom: 1rem;"></i>
                    <p>Aucun membre trouvé pour cette recherche</p>
                </td>
            `;

            tableBody.appendChild(message);
        }

        // Recherche lors de la pression sur Entrée
        document.getElementById('memberSearch').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchMembers();
            }
        });

        // Réinitialiser la recherche quand le champ est vidé
        document.getElementById('memberSearch').addEventListener('input', function(e) {
            if (!this.value) {
                const tableBody = document.querySelector('table tbody');
                const rows = tableBody.querySelectorAll('tr');
                const existingMessage = document.querySelector('.no-results-message');

                if (existingMessage) {
                    existingMessage.remove();
                }

                rows.forEach(row => {
                    row.style.display = '';
                    row.style.backgroundColor = '';
                    row.style.borderLeft = '';
                });
            }
        });

        // Gestion des filtres
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                // Retirer la classe active de tous les boutons
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                // Ajouter la classe active au bouton cliqué
                this.classList.add('active');

                const filter = this.getAttribute('data-filter');
                const rows = document.querySelectorAll('table tbody tr');

                rows.forEach(row => {
                    if (filter === 'all') {
                        row.style.display = '';
                    } else {
                        const status = row.querySelector('td:nth-child(4)').textContent.toLowerCase();
                        row.style.display = status.includes(filter) ? '' : 'none';
                    }
                });
            });
        });

        // Fonctions pour gérer le modal d'ajout de membre
        function openAddMemberModal() {
            const modal = document.getElementById('addMemberModal');
            modal.style.display = 'block';

            // Réinitialiser le formulaire
            const form = document.getElementById('addMemberForm');
            form.reset();

            // Focus sur le premier champ (Civilité)
            document.getElementById('Civilite').focus();

            // Empêcher la propagation du clic
            event.stopPropagation();
        }

        function closeAddMemberModal() {
            document.getElementById('addMemberModal').style.display = 'none';
        }

        // Fermer le modal en cliquant en dehors
        window.addEventListener('click', function(event) {
            const modal = document.getElementById('addMemberModal');
            if (event.target === modal) {
                closeAddMemberModal();
            }
        });

        // Empêcher la fermeture du modal lors du clic sur le contenu
        document.querySelector('#addMemberModal .modal-content').addEventListener('click', function(event) {
            event.stopPropagation();
        });

        // Gestion de la soumission du formulaire d'ajout
        document.getElementById('addMemberForm').addEventListener('submit', function(e) {
            e.preventDefault();
            showLoader();

            const formData = new FormData(this);
            fetch('add_member.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                hideLoader();
                if (data.success) {
                    showSuccessMessage('Membre ajouté avec succès');
                    closeAddMemberModal();
                    // Rafraîchir la page après 1.5 secondes
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    showErrorMessage(data.message || 'Erreur lors de l\'ajout du membre');
                }
            })
            .catch(error => {
                hideLoader();
                showErrorMessage('Une erreur est survenue lors de l\'ajout du membre');
            });
        });

        // Fonction pour afficher un message d'erreur
        function showErrorMessage(message) {
            console.log('Affichage du message d\'erreur:', message);
            const errorMessage = document.createElement('div');
            errorMessage.className = 'error-message';
            errorMessage.textContent = message;
            document.body.appendChild(errorMessage);

            setTimeout(() => {
                errorMessage.remove();
            }, 3000);
        }

        // Fonction pour afficher un message de succès
        function showSuccessMessage(message) {
            console.log('Affichage du message de succès:', message);
            const successMessage = document.createElement('div');
            successMessage.className = 'success-message';
            successMessage.textContent = message;
            document.body.appendChild(successMessage);

            setTimeout(() => {
                successMessage.remove();
            }, 3000);
        }

        // Fonction pour afficher le loader
        function showLoader() {
            console.log('Affichage du loader');
            const loader = document.querySelector('.loading-overlay');
            if (loader) {
                loader.style.display = 'flex';
            } else {
                console.error('Loader non trouvé');
            }
        }

        // Fonction pour cacher le loader
        function hideLoader() {
            console.log('Masquage du loader');
            const loader = document.querySelector('.loading-overlay');
            if (loader) {
                loader.style.display = 'none';
            } else {
                console.error('Loader non trouvé');
            }
        }

        // Fonction pour ouvrir le modal de modification
        function openEditMemberModal(memberId) {
            showLoader();
            fetch('get_member.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `id=${memberId}`
            })
            .then(response => response.json())
            .then(data => {
                hideLoader();
                if (data.success) {
                    const member = data.data;
                    document.getElementById('edit_id').value = member.id;
                    document.getElementById('edit_Civilite').value = member.Civilite;
                    document.getElementById('edit_Nom').value = member.Nom;
                    document.getElementById('edit_Prenom').value = member.Prenom;
                    document.getElementById('edit_Niveau').value = member.Niveau;
                    document.getElementById('edit_Diplome').value = member.Diplome;
                    document.getElementById('edit_Specialite').value = member.Specialite;
                    document.getElementById('edit_Fonction_actuelle').value = member.Fonction_actuelle;
                    document.getElementById('edit_Telephone').value = member.Telephone;
                    document.getElementById('edit_Email').value = member.Email;
                    document.getElementById('edit_Pays').value = member.Pays;
                    document.getElementById('edit_Ville').value = member.Ville;
                    document.getElementById('edit_date_de_naissance').value = member.date_de_naissance;
                    document.getElementById('edit_situation_matrimoniale').value = member.situation_matrimoniale;
                    
                    document.getElementById('editMemberModal').style.display = 'block';
                } else {
                    showErrorMessage(data.message || 'Erreur lors de la récupération des données du membre');
                }
            })
            .catch(error => {
                hideLoader();
                showErrorMessage('Une erreur est survenue lors de la récupération des données');
            });
        }

        // Fonction pour fermer le modal de modification
        function closeEditMemberModal() {
            document.getElementById('editMemberModal').style.display = 'none';
        }

        // Gestion de la soumission du formulaire de modification
        document.getElementById('editMemberForm').addEventListener('submit', function(e) {
            e.preventDefault();
            showLoader();

            const formData = new FormData(this);
            fetch('update_member.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                hideLoader();
                if (data.success) {
                    showSuccessMessage('Membre modifié avec succès');
                    closeEditMemberModal();
                    // Rafraîchir la page après 1.5 secondes
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    showErrorMessage(data.message || 'Erreur lors de la modification du membre');
                }
            })
            .catch(error => {
                hideLoader();
                showErrorMessage('Une erreur est survenue lors de la modification');
            });
        });

        // Fermer le modal en cliquant en dehors
        window.addEventListener('click', function(event) {
            const modal = document.getElementById('editMemberModal');
            if (event.target === modal) {
                closeEditMemberModal();
            }
        });

        // Empêcher la fermeture du modal lors du clic sur le contenu
        document.querySelector('#editMemberModal .modal-content').addEventListener('click', function(event) {
            event.stopPropagation();
        });

        // Fonctions pour gérer les administrateurs
        function openAddAdminModal() {
            document.getElementById('addAdminModal').style.display = 'block';
            document.getElementById('addAdminForm').reset();
        }

        function closeAddAdminModal() {
            document.getElementById('addAdminModal').style.display = 'none';
        }

        function openEditAdminModal(adminId) {
            console.log('Opening edit modal for admin ID:', adminId); // Debug log
            showLoader();
            
            // Créer les données à envoyer
            const formData = new FormData();
            formData.append('id', adminId);
            
            fetch('get_admin.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Response received:', response); // Debug log
                return response.json();
            })
            .then(data => {
                console.log('Data received:', data); // Debug log
                hideLoader();
                if (data.success) {
                    // Remplir le formulaire avec les données
                    document.getElementById('edit_admin_id').value = data.id;
                    document.getElementById('edit_admin_name').value = data.name;
                    document.getElementById('edit_admin_email').value = data.email;
                    
                    // Afficher le modal
                    const modal = document.getElementById('editAdminModal');
                    modal.style.display = 'block';
                    
                    // Réinitialiser les champs de mot de passe
                    document.getElementById('edit_admin_password').value = '';
                    document.getElementById('edit_admin_confirm_password').value = '';
                } else {
                    showErrorMessage(data.message || 'Erreur lors de la récupération des données');
                }
            })
            .catch(error => {
                console.error('Error:', error); // Debug log
                hideLoader();
                showErrorMessage('Une erreur est survenue lors de la récupération des données');
            });
        }

        function closeEditAdminModal() {
            document.getElementById('editAdminModal').style.display = 'none';
        }

        // Gestion de la soumission du formulaire d'ajout d'administrateur
        document.getElementById('addAdminForm').addEventListener('submit', function(e) {
            e.preventDefault();
            showLoader();

            const formData = new FormData(this);
            fetch('add_admin.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                hideLoader();
                if (data.success) {
                    showSuccessMessage('Administrateur ajouté avec succès');
                    closeAddAdminModal();
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    showErrorMessage(data.message || 'Erreur lors de l\'ajout de l\'administrateur');
                }
            })
            .catch(error => {
                hideLoader();
                showErrorMessage('Une erreur est survenue');
            });
        });

        // Gestion de la soumission du formulaire de modification d'administrateur
        document.getElementById('editAdminForm').addEventListener('submit', function(e) {
            e.preventDefault();
            showLoader();

            const formData = new FormData(this);
            fetch('update_admin.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                hideLoader();
                if (data.success) {
                    showSuccessMessage('Administrateur modifié avec succès');
                    closeEditAdminModal();
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    showErrorMessage(data.message || 'Erreur lors de la modification de l\'administrateur');
                }
            })
            .catch(error => {
                hideLoader();
                showErrorMessage('Une erreur est survenue');
            });
        });

        // Gestion de la suppression d'administrateur
        function deleteAdmin(adminId, adminName) {
            if (confirm(`Êtes-vous sûr de vouloir supprimer l'administrateur "${adminName}" ? Cette action est irréversible.`)) {
                showLoader();
                fetch('delete_admin.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `admin_id=${adminId}`
                })
                .then(response => response.json())
                .then(data => {
                    hideLoader();
                    if (data.success) {
                        showSuccessMessage('Administrateur supprimé avec succès');
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        showErrorMessage(data.message || 'Erreur lors de la suppression de l\'administrateur');
                    }
                })
                .catch(error => {
                    hideLoader();
                    showErrorMessage('Une erreur est survenue');
                });
            }
        }

        // Fermer les modals en cliquant en dehors
        window.addEventListener('click', function(event) {
            const addAdminModal = document.getElementById('addAdminModal');
            const editAdminModal = document.getElementById('editAdminModal');
            
            if (event.target === addAdminModal) {
                closeAddAdminModal();
            }
            if (event.target === editAdminModal) {
                closeEditAdminModal();
            }
        });

        // Empêcher la fermeture des modals lors du clic sur le contenu
        document.querySelectorAll('.modal-content').forEach(content => {
            content.addEventListener('click', function(event) {
                event.stopPropagation();
            });
        });
    </script>
</body>

</html>