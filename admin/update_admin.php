<?php
require_once 'auth.php';
requireLogin();

// Vérifier si l'utilisateur est super admin
if (!isSuperAdmin()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Accès non autorisé']);
    exit();
}

// Vérifier si la requête est de type POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit();
}

// Récupérer et valider les données
$adminId = isset($_POST['id']) ? intval($_POST['id']) : 0;
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';

if ($adminId <= 0 || empty($name) || empty($email)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Données invalides']);
    exit();
}

try {
    // Vérifier si l'administrateur existe
    $stmt = $pdo->prepare("SELECT id, role FROM admins WHERE id = ?");
    $stmt->execute([$adminId]);
    $admin = $stmt->fetch();

    if (!$admin) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Administrateur non trouvé']);
        exit();
    }

    // Vérifier si l'email est déjà utilisé par un autre administrateur
    $stmt = $pdo->prepare("SELECT id FROM admins WHERE email = ? AND id != ?");
    $stmt->execute([$email, $adminId]);
    if ($stmt->fetch()) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Cet email est déjà utilisé']);
        exit();
    }

    // Commencer une transaction
    $pdo->beginTransaction();

    // Préparer la requête de mise à jour
    if (!empty($password)) {
        // Si un nouveau mot de passe est fourni
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE admins SET name = ?, email = ?, password = ? WHERE id = ?");
        $stmt->execute([$name, $email, $hashedPassword, $adminId]);
    } else {
        // Sinon, ne pas modifier le mot de passe
        $stmt = $pdo->prepare("UPDATE admins SET name = ?, email = ? WHERE id = ?");
        $stmt->execute([$name, $email, $adminId]);
    }

    // Valider la transaction
    $pdo->commit();

    // Journaliser l'action
    $currentAdminId = $_SESSION['admin_id'];
    $action = "Modification des informations de l'administrateur {$name} (ID: {$adminId})";
    $stmt = $pdo->prepare("INSERT INTO admin_logs (admin_id, action, created_at) VALUES (?, ?, NOW())");
    $stmt->execute([$currentAdminId, $action]);

    // Si l'administrateur modifié est celui qui est connecté, mettre à jour la session
    if ($adminId == $currentAdminId) {
        $_SESSION['admin_name'] = $name;
        $_SESSION['admin_email'] = $email;
    }

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Informations mises à jour avec succès']);

} catch (Exception $e) {
    // En cas d'erreur, annuler la transaction
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour : ' . $e->getMessage()]);
}
?> 