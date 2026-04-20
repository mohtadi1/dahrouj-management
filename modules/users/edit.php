<?php
require_once '../../includes/config.php';
requireAdmin();

$db = getDB();
$error = '';

$id = intval($_GET['id'] ?? 0);
if (!$id) redirect('index.php');

$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    setFlashMessage('error', 'Utilisateur non trouvé.');
    redirect('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize($_POST['full_name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $role = $_POST['role'] ?? 'employee';
    $status = $_POST['status'] ?? 'active';
    $password = $_POST['password'] ?? '';
    
    if (empty($full_name)) {
        $error = 'Le nom complet est obligatoire.';
    } else {
        if (!empty($password)) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE users SET full_name = ?, email = ?, role = ?, status = ?, password = ? WHERE id = ?");
            $stmt->execute([$full_name, $email, $role, $status, $hashedPassword, $id]);
        } else {
            $stmt = $db->prepare("UPDATE users SET full_name = ?, email = ?, role = ?, status = ? WHERE id = ?");
            $stmt->execute([$full_name, $email, $role, $status, $id]);
        }
        logActivity('Modification utilisateur', 'user', $id);
        setFlashMessage('success', 'Utilisateur mis à jour avec succès.');
        redirect('index.php');
    }
}

$pageTitle = 'Modifier Utilisateur';
$currentPage = 'users';
require_once '../../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-user-cog"></i> Modifier Utilisateur</h1>
        <nav class="breadcrumb">
            <a href="../../index.php">Accueil</a>
            <i class="fas fa-chevron-right"></i>
            <a href="index.php">Utilisateurs</a>
            <i class="fas fa-chevron-right"></i>
            <span>Modifier</span>
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
                    <input type="text" id="full_name" name="full_name" class="form-control" value="<?php echo $user['full_name']; ?>" required>
                </div>
                <div class="form-group">
                    <label for="username">Nom d'utilisateur</label>
                    <input type="text" class="form-control" value="<?php echo $user['username']; ?>" disabled>
                    <small class="text-muted">Le nom d'utilisateur ne peut pas être modifié</small>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" value="<?php echo $user['email']; ?>">
                </div>
                <div class="form-group">
                    <label for="password">Nouveau mot de passe</label>
                    <input type="password" id="password" name="password" class="form-control">
                    <small class="text-muted">Laissez vide pour conserver l'actuel</small>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="role">Rôle</label>
                    <select id="role" name="role" class="form-control">
                        <option value="employee" <?php echo $user['role'] === 'employee' ? 'selected' : ''; ?>>Employé</option>
                        <option value="manager" <?php echo $user['role'] === 'manager' ? 'selected' : ''; ?>>Manager</option>
                        <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Administrateur</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="status">Statut</label>
                    <select id="status" name="status" class="form-control">
                        <option value="active" <?php echo $user['status'] === 'active' ? 'selected' : ''; ?>>Actif</option>
                        <option value="inactive" <?php echo $user['status'] === 'inactive' ? 'selected' : ''; ?>>Inactif</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Mettre à jour</button>
                <a href="index.php" class="btn btn-secondary"><i class="fas fa-times"></i> Annuler</a>
            </div>
        </form>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
