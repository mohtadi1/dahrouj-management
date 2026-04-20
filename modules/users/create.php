<?php
require_once '../../includes/config.php';
requireAdmin();

$db = getDB();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize($_POST['full_name'] ?? '');
    $username = sanitize($_POST['username'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'employee';
    $status = $_POST['status'] ?? 'active';
    
    // Check if username exists
    $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetchColumn() > 0) {
        $error = 'Ce nom d\'utilisateur existe déjà.';
    } elseif (empty($full_name) || empty($username) || empty($password)) {
        $error = 'Veuillez remplir tous les champs obligatoires.';
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO users (full_name, username, email, password, role, status) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$full_name, $username, $email, $hashedPassword, $role, $status])) {
            logActivity('Création utilisateur', 'user', $db->lastInsertId());
            setFlashMessage('success', 'Utilisateur créé avec succès.');
            redirect('index.php');
        } else {
            $error = 'Erreur lors de la création.';
        }
    }
}

$pageTitle = 'Nouvel Utilisateur';
$currentPage = 'users';
require_once '../../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-user-plus"></i> Nouvel Utilisateur</h1>
        <nav class="breadcrumb">
            <a href="../../index.php">Accueil</a>
            <i class="fas fa-chevron-right"></i>
            <a href="index.php">Utilisateurs</a>
            <i class="fas fa-chevron-right"></i>
            <span>Nouveau</span>
        </nav>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3 class="card-title">Informations</h3></div>
    <div class="card-body">
        <?php if ($error): ?>
        <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="" data-validate>
            <div class="form-row">
                <div class="form-group">
                    <label for="full_name">Nom complet <span class="text-danger">*</span></label>
                    <input type="text" id="full_name" name="full_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="username">Nom d'utilisateur <span class="text-danger">*</span></label>
                    <input type="text" id="username" name="username" class="form-control" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control">
                </div>
                <div class="form-group">
                    <label for="password">Mot de passe <span class="text-danger">*</span></label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="role">Rôle</label>
                    <select id="role" name="role" class="form-control">
                        <option value="employee">Employé</option>
                        <option value="manager">Manager</option>
                        <option value="admin">Administrateur</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="status">Statut</label>
                    <select id="status" name="status" class="form-control">
                        <option value="active">Actif</option>
                        <option value="inactive">Inactif</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Enregistrer</button>
                <a href="index.php" class="btn btn-secondary"><i class="fas fa-times"></i> Annuler</a>
            </div>
        </form>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
