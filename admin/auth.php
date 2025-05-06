<?php
session_start();

// Configuration de la base de données
$host = 'localhost';
$dbname = 'ngo_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erreur de connexion: " . $e->getMessage());
}

// Fonction pour hacher le mot de passe
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Fonction pour vérifier le mot de passe
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Fonction pour vérifier si l'utilisateur est connecté
function isLoggedIn() {
    return isset($_SESSION['admin_id']);
}

// Fonction pour vérifier si l'utilisateur est super admin
function isSuperAdmin() {
    return isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'super_admin';
}

// Fonction pour rediriger si non connecté
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: admin.html');
        exit();
    }
}

// Fonction pour vérifier s'il y a déjà des administrateurs
function hasAdmins() {
    global $pdo;
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM admins");
    return $stmt->fetch()['count'] > 0;
}

// Traitement de l'inscription
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    // Vérifier si l'inscription est autorisée
    if (hasAdmins() && !isSuperAdmin()) {
        echo json_encode(['success' => false, 'message' => 'Seul le super administrateur peut créer de nouveaux administrateurs']);
        exit();
    }

    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    // Validation
    if ($password !== $confirmPassword) {
        echo json_encode(['success' => false, 'message' => 'Les mots de passe ne correspondent pas']);
        exit();
    }

    // Vérifier si l'email existe déjà
    $stmt = $pdo->prepare("SELECT id FROM admins WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Cet email est déjà utilisé']);
        exit();
    }

    // Déterminer le rôle
    $role = hasAdmins() ? 'admin' : 'super_admin';

    // Créer l'administrateur
    $hashedPassword = hashPassword($password);
    $stmt = $pdo->prepare("INSERT INTO admins (name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $email, $hashedPassword, $role]);

    echo json_encode(['success' => true, 'message' => 'Inscription réussie']);
    exit();
}

// Traitement de la connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Récupérer l'administrateur
    $stmt = $pdo->prepare("SELECT id, name, password, role FROM admins WHERE email = ?");
    $stmt->execute([$email]);
    $admin = $stmt->fetch();

    if ($admin && verifyPassword($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_name'] = $admin['name'];
        $_SESSION['admin_role'] = $admin['role'];
        echo json_encode(['success' => true, 'message' => 'Connexion réussie']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Email ou mot de passe incorrect']);
    }
    exit();
}

// Traitement de la déconnexion
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header('Location: admin.html');
    exit();
}

// Traitement de la suppression d'un administrateur
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'delete_admin') {
        // Vérifier si l'utilisateur est super admin
        if (!isSuperAdmin()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Accès non autorisé']);
            exit();
        }

        // Récupérer l'ID de l'administrateur à supprimer
        $adminId = isset($_POST['admin_id']) ? intval($_POST['admin_id']) : 0;

        if ($adminId <= 0) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'ID d\'administrateur invalide']);
            exit();
        }

        try {
            // Vérifier si l'administrateur existe et n'est pas un super admin
            $stmt = $pdo->prepare("SELECT id, name, role FROM admins WHERE id = ?");
            $stmt->execute([$adminId]);
            $admin = $stmt->fetch();

            if (!$admin) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Administrateur non trouvé']);
                exit();
            }

            if ($admin['role'] === 'super_admin') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Impossible de supprimer un super administrateur']);
                exit();
            }

            // Commencer une transaction
            $pdo->beginTransaction();

            // Supprimer l'administrateur
            $stmt = $pdo->prepare("DELETE FROM admins WHERE id = ?");
            $stmt->execute([$adminId]);

            // Valider la transaction
            $pdo->commit();

            // Journaliser l'action
            $currentAdminId = $_SESSION['admin_id'];
            $action = "Suppression de l'administrateur {$admin['name']} (ID: {$adminId})";
            $stmt = $pdo->prepare("INSERT INTO admin_logs (admin_id, action, created_at) VALUES (?, ?, NOW())");
            $stmt->execute([$currentAdminId, $action]);

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Administrateur supprimé avec succès']);

        } catch (Exception $e) {
            // En cas d'erreur, annuler la transaction
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression : ' . $e->getMessage()]);
        }
    }
}
?> 