<?php
require_once '../config.php';
require_once 'auth.php';
requireLogin();

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
                <li><a href="#"><i class="fas fa-users"></i> Membres</a></li>
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
                        <input type="text" class="search-input" id="memberSearch" placeholder="Rechercher un membre par nom...">
                        <button class="search-btn" onclick="searchMembers()">
                            <i class="fas fa-search"></i>
                            Exécuter
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
                                <th>Type</th>
                                <th>Statut</th>
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
                                        echo "<!-- Membre trouvé : " . print_r($member, true) . " -->";
                                        echo '<tr>';
                                        echo '<td>' . htmlspecialchars($member['id']) . '</td>';
                                        echo '<td>' . htmlspecialchars($member['nom']) . '</td>';
                                        echo '<td>' . htmlspecialchars($member['prenom']) . '</td>';
                                        echo '<td>' . htmlspecialchars($member['email']) . '</td>';
                                        echo '<td>' . htmlspecialchars($member['telephone']) . '</td>';
                                        echo '<td>' . htmlspecialchars($member['type_membre']) . '</td>';
                                        echo '<td><span class="status-badge status-' . htmlspecialchars($member['statut']) . '">' . htmlspecialchars($member['statut']) . '</span></td>';
                                        echo '<td>' . date('d/m/Y H:i', strtotime($member['date_inscription'])) . '</td>';
                                        echo '<td>
                                            <button class="edit-btn" onclick="openEditMemberModal(' . $member['id'] . ')">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="delete-btn" data-member-id="' . $member['id'] . '">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>';
                                        echo '</tr>';
                                    }
                                }
                            } catch (PDOException $e) {
                                echo '<tr><td colspan="9" class="error-message">Erreur PDO: ' . htmlspecialchars($e->getMessage()) . '</td></tr>';
                            } catch (Exception $e) {
                                echo '<tr><td colspan="9" class="error-message">Erreur: ' . htmlspecialchars($e->getMessage()) . '</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php if (isSuperAdmin()): ?>
            <div class="content-section">
                <h2>Gestion des Administrateurs</h2>
                <a href="create_admin.php" class="add-btn">
                    <i class="fas fa-user-plus"></i> Créer un nouvel administrateur
                </a>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Email</th>
                                <th>Rôle</th>
                                <th>Date de création</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($admins as $admin): ?>
                            <tr>
                                <td>
                                    <?php echo htmlspecialchars($admin['name']); ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($admin['email']); ?>
                                </td>
                                <td>
                                    <span class="role-badge <?php echo $admin['role'] === 'super_admin' ? 'super-admin' : 'admin'; ?>">
                                    <?php echo $admin['role'] === 'super_admin' ? 'Super Admin' : 'Admin'; ?>
                                </span>
                                </td>
                                <td>
                                    <?php echo date('d/m/Y', strtotime($admin['created_at'])); ?>
                                </td>
                                <td>
                                    <?php if ($admin['role'] !== 'super_admin'): ?>
                                    <button class="action-btn delete-btn" data-admin-id="<?php echo $admin['id']; ?>" data-admin-name="<?php echo htmlspecialchars($admin['name']); ?>">
                                        Supprimer
                                    </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
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
                <div class="form-group">
                    <label for="nom">Nom</label>
                    <input type="text" id="nom" name="nom" required maxlength="50">
                </div>
                <div class="form-group">
                    <label for="prenom">Prénom</label>
                    <input type="text" id="prenom" name="prenom" required maxlength="50">
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required maxlength="100">
                </div>
                <div class="form-group">
                    <label for="telephone">Téléphone</label>
                    <input type="tel" id="telephone" name="telephone" required maxlength="20">
                </div>
                <div class="form-group">
                    <label for="whatsapp">WhatsApp (Optionnel)</label>
                    <input type="tel" id="whatsapp" name="whatsapp" maxlength="20">
                </div>
                <div class="form-group">
                    <label for="pays">Pays de Résidence</label>
                    <input type="text" id="pays" name="pays" required maxlength="50">
                </div>
                <div class="form-group">
                    <label for="ville">Ville de Résidence</label>
                    <input type="text" id="ville" name="ville" required maxlength="50">
                </div>
                <div class="form-group">
                    <label for="quartier">Quartier/Commune</label>
                    <input type="text" id="quartier" name="quartier" required maxlength="100">
                </div>
                <div class="form-group full-width">
                    <label for="adresse">Adresse Complète</label>
                    <textarea id="adresse" name="adresse" required></textarea>
                </div>
                <div class="form-group">
                    <label for="type_membre">Type de Membre</label>
                    <select id="type_membre" name="type_membre" required>
                        <option value="">Sélectionnez un type</option>
                        <option value="volontaire">Volontaire</option>
                        <option value="donateur">Donateur</option>
                        <option value="membre_actif">Membre Actif</option>
                    </select>
                </div>
                <div class="form-group full-width">
                    <label for="message">Message (Optionnel)</label>
                    <textarea id="message" name="message"></textarea>
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
                
                <div class="form-group">
                    <label for="edit_nom">Nom</label>
                    <input type="text" id="edit_nom" name="nom" required maxlength="50">
                </div>
                
                <div class="form-group">
                    <label for="edit_prenom">Prénom</label>
                    <input type="text" id="edit_prenom" name="prenom" required maxlength="50">
                </div>
                
                <div class="form-group">
                    <label for="edit_email">Email</label>
                    <input type="email" id="edit_email" name="email" required maxlength="100">
                </div>
                
                <div class="form-group">
                    <label for="edit_telephone">Téléphone</label>
                    <input type="tel" id="edit_telephone" name="telephone" required maxlength="20">
                </div>
                
                <div class="form-group">
                    <label for="edit_whatsapp">WhatsApp (Optionnel)</label>
                    <input type="tel" id="edit_whatsapp" name="whatsapp" maxlength="20">
                </div>
                
                <div class="form-group">
                    <label for="edit_pays">Pays de Résidence</label>
                    <input type="text" id="edit_pays" name="pays" required maxlength="50">
                </div>
                
                <div class="form-group">
                    <label for="edit_ville">Ville de Résidence</label>
                    <input type="text" id="edit_ville" name="ville" required maxlength="50">
                </div>
                
                <div class="form-group">
                    <label for="edit_quartier">Quartier/Commune</label>
                    <input type="text" id="edit_quartier" name="quartier" required maxlength="100">
                </div>
                
                <div class="form-group full-width">
                    <label for="edit_adresse">Adresse Complète</label>
                    <textarea id="edit_adresse" name="adresse" required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="edit_type_membre">Type de Membre</label>
                    <select id="edit_type_membre" name="type_membre" required>
                        <option value="">Sélectionnez un type</option>
                        <option value="volontaire">Volontaire</option>
                        <option value="donateur">Donateur</option>
                        <option value="membre_actif">Membre Actif</option>
                    </select>
                </div>
                
                <div class="form-group full-width">
                    <label for="edit_message">Message (Optionnel)</label>
                    <textarea id="edit_message" name="message"></textarea>
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
                    fetch('auth.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `action=delete_admin&admin_id=${adminId}`
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
            const tableBody = document.querySelector('table tbody');
            const rows = Array.from(tableBody.querySelectorAll('tr'));
            let found = false;

            // Réinitialiser l'affichage de tous les membres
            rows.forEach(row => {
                row.style.display = '';
            });

            // Rechercher le membre correspondant
            const matchingRow = rows.find(row => {
                const name = row.querySelector('td:first-child').textContent.toLowerCase();
                return name.includes(searchTerm);
            });

            if (matchingRow && searchTerm) {
                // Supprimer le message "Aucun résultat" s'il existe
                const existingMessage = document.querySelector('.no-results-message');
                if (existingMessage) {
                    existingMessage.remove();
                }

                // Mettre le membre trouvé en première position
                tableBody.insertBefore(matchingRow, tableBody.firstChild);

                // Mettre en évidence le membre trouvé
                matchingRow.style.backgroundColor = '#f8f9fa';
                matchingRow.style.borderLeft = '4px solid #3498db';

                // Cacher les autres membres
                rows.forEach(row => {
                    if (row !== matchingRow) {
                        row.style.display = 'none';
                    }
                });

                found = true;
            } else if (searchTerm) {
                showNoResultsMessage();
            }

            // Réinitialiser le style après 3 secondes
            if (matchingRow) {
                setTimeout(() => {
                    matchingRow.style.backgroundColor = '';
                    matchingRow.style.borderLeft = '';
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

            // Focus sur le premier champ
            document.getElementById('nom').focus();

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
        document.querySelector('.modal-content').addEventListener('click', function(event) {
            event.stopPropagation();
        });

        // Gestion de la soumission du formulaire
        const addMemberForm = document.getElementById('addMemberForm');
        if (addMemberForm) {
            addMemberForm.addEventListener('submit', function(e) {
                e.preventDefault();
                console.log('Formulaire soumis');
                showLoader();

                // Récupérer les données du formulaire
                const formData = new FormData(this);
                console.log('Données du formulaire:', Object.fromEntries(formData));

                // Envoyer la requête
                fetch('add_member.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => {
                        console.log('Réponse reçue:', response);
                        if (!response.ok) {
                            throw new Error('Erreur réseau: ' + response.status);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Données reçues:', data);
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
                        console.error('Erreur:', error);
                        hideLoader();
                        showErrorMessage('Une erreur est survenue lors de l\'ajout du membre: ' + error.message);
                    });
            });
        } else {
            console.error('Formulaire non trouvé');
        }

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
                    document.getElementById('edit_nom').value = member.nom;
                    document.getElementById('edit_prenom').value = member.prenom;
                    document.getElementById('edit_email').value = member.email;
                    document.getElementById('edit_telephone').value = member.telephone;
                    document.getElementById('edit_whatsapp').value = member.whatsapp || '';
                    document.getElementById('edit_pays').value = member.pays;
                    document.getElementById('edit_ville').value = member.ville;
                    document.getElementById('edit_quartier').value = member.quartier;
                    document.getElementById('edit_adresse').value = member.adresse;
                    document.getElementById('edit_type_membre').value = member.type_membre;
                    document.getElementById('edit_message').value = member.message || '';
                    
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
    </script>
</body>

</html>