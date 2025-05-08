<?php
// Démarrer la session et la mise en tampon de sortie
ob_start();
require_once '../config.php';
require_once 'auth.php';
require_once '../vendor/autoload.php';

// Vérification explicite de l'existence de TCPDF
if (!class_exists('TCPDF')) {
    // Essayer de charger TCPDF directement
    require_once '../vendor/tecnickcom/tcpdf/tcpdf.php';
    
    if (!class_exists('TCPDF')) {
        die('Erreur : La bibliothèque TCPDF n\'est pas installée correctement. Veuillez exécuter "composer require tecnickcom/tcpdf" dans le terminal.');
    }
}

requireLogin();

// Nombre de membres par page
$membersPerPage = 20;

// Récupérer le terme de recherche
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';

// Récupérer le numéro de page actuel
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($currentPage - 1) * $membersPerPage;

// Préparer la requête de base
$baseQuery = "FROM membres";
$whereClause = "";
$params = [];

// Ajouter la condition de recherche si un terme est fourni
if (!empty($searchTerm)) {
    $whereClause = "WHERE nom LIKE :search OR prenom LIKE :search OR ville LIKE :search";
    $params[':search'] = "%$searchTerm%";
}

// Requête pour le nombre total de membres
$countQuery = "SELECT COUNT(*) as total " . $baseQuery . " " . $whereClause;
$stmt = $pdo->prepare($countQuery);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$totalMembers = $stmt->fetch()['total'];

// Calculer le nombre total de pages
$totalPages = ceil($totalMembers / $membersPerPage);

// Requête pour récupérer les membres
$query = "SELECT * " . $baseQuery . " " . $whereClause . " ORDER BY date_inscription DESC LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($query);

// Bind les paramètres de recherche
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}

// Bind les paramètres de pagination
$stmt->bindValue(':limit', $membersPerPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$members = $stmt->fetchAll();
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
        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 2rem;
            margin-bottom: 2rem;
        }

        .pagination a {
            padding: 0.5rem 1rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-decoration: none;
            color: #333;
            transition: all 0.3s ease;
        }

        .pagination a:hover {
            background-color: #f5f5f5;
        }

        .pagination .active {
            background-color: #3498db;
            color: white;
            border-color: #3498db;
        }

        .pagination .disabled {
            color: #999;
            pointer-events: none;
        }

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
        }

        .search-btn {
            padding: 0.8rem 1.5rem;
            background: linear-gradient(45deg, #3498db, #2ecc71);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .table-container {
            overflow-x: auto;
            margin-top: 1rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background-color: #f8f9fa;
            font-weight: 600;
        }

        .status-badge {
            padding: 0.3rem 0.6rem;
            border-radius: 20px;
            font-size: 0.8rem;
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

        .action-btn {
            padding: 0.5rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin: 0 0.2rem;
        }

        .edit-btn {
            color: #3498db;
        }

        .delete-btn {
            color: #e74c3c;
        }

        .action-btn:hover {
            background-color: rgba(0, 0, 0, 0.1);
        }

        /* Styles pour la sidebar */
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

        .print-btn {
            padding: 0.8rem 1.5rem;
            background: linear-gradient(45deg, #e74c3c, #c0392b);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .print-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(231, 76, 60, 0.3);
        }

        .print-btn i {
            font-size: 1.2rem;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="../img/logo.jpg" alt="Logo FIER">
                <h2>Admin</h2>
            </div>
            <ul class="sidebar-menu">
                <li><a href="dashboard.php"><i class="fas fa-home"></i> Tableau de bord</a></li>
                <li><a href="members.php" class="active"><i class="fas fa-users"></i> Membres</a></li>
                <li><a href="#"><i class="fas fa-newspaper"></i> Actualités</a></li>
                <li><a href="#"><i class="fas fa-calendar-alt"></i> Événements</a></li>
                <li><a href="#"><i class="fas fa-image"></i> Galerie</a></li>
                <li><a href="#"><i class="fas fa-cog"></i> Paramètres</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <div class="top-bar">
                <h1>Gestion des Membres</h1>
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo substr($_SESSION['admin_name'], 0, 2); ?>
                    </div>
                    <span><?php echo $_SESSION['admin_name']; ?></span>
                    <a href="#" onclick="confirmLogout()" class="logout-btn">Déconnexion</a>
                </div>
            </div>

            <div class="content-section">
                <div class="search-container">
                    <form method="GET" class="search-input-group">
                        <input type="text" class="search-input" name="search" id="memberSearch" 
                               placeholder="Rechercher par nom, prénom ou ville..."
                               value="<?php echo htmlspecialchars($searchTerm); ?>">
                        <button type="submit" class="search-btn">
                            <i class="fas fa-search"></i>
                            Rechercher
                        </button>
                    </form>
                    <?php if (!empty($searchTerm)): ?>
                        <a href="pdf_generator.php?search=<?php echo urlencode($searchTerm); ?>" class="print-btn" target="_blank">
                            <i class="fas fa-file-pdf"></i>
                            Imprimer en PDF
                        </a>
                    <?php endif; ?>
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
                                <th>Date d'inscription</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="membersTableBody">
                            <?php if (empty($members)): ?>
                                <tr>
                                    <td colspan="10" style="text-align: center; padding: 2rem; color: #7f8c8d;">
                                        <i class="fas fa-search" style="font-size: 2rem; margin-bottom: 1rem;"></i>
                                        <p>Aucun membre trouvé pour cette recherche</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($members as $member): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($member['id']); ?></td>
                                    <td><?php echo htmlspecialchars($member['nom']); ?></td>
                                    <td><?php echo htmlspecialchars($member['prenom']); ?></td>
                                    <td><?php echo htmlspecialchars($member['email']); ?></td>
                                    <td><?php echo htmlspecialchars($member['telephone']); ?></td>
                                    <td><?php echo htmlspecialchars($member['fonction_actuelle'] ?? 'Non spécifié'); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo htmlspecialchars($member['statut']); ?>">
                                            <?php echo htmlspecialchars($member['statut']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($member['ville']); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($member['date_inscription'])); ?></td>
                                    <td>
                                        <button class="action-btn edit-btn" onclick="openEditMemberModal(<?php echo $member['id']; ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="action-btn delete-btn" data-member-id="<?php echo $member['id']; ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="pagination">
                    <?php if ($currentPage > 1): ?>
                        <a href="?page=<?php echo $currentPage - 1; ?><?php echo !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : ''; ?>">
                            &laquo; Précédent
                        </a>
                    <?php else: ?>
                        <a class="disabled">&laquo; Précédent</a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?page=<?php echo $i; ?><?php echo !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : ''; ?>" 
                           class="<?php echo $i === $currentPage ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($currentPage < $totalPages): ?>
                        <a href="?page=<?php echo $currentPage + 1; ?><?php echo !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : ''; ?>">
                            Suivant &raquo;
                        </a>
                    <?php else: ?>
                        <a class="disabled">Suivant &raquo;</a>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Fonction de confirmation de déconnexion
        function confirmLogout() {
            if (confirm('Êtes-vous sûr de vouloir vous déconnecter ?')) {
                window.location.href = 'auth.php?action=logout';
            }
        }
    </script>
</body>
</html> 