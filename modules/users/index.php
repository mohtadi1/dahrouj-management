<?php
require_once '../../includes/config.php';
requireAdmin();

$db = getDB();

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = intval($_GET['delete']);
    if ($id != $_SESSION['user_id']) {
        $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
        if ($stmt->execute([$id])) {
            logActivity('Suppression utilisateur', 'user', $id);
            setFlashMessage('success', 'Utilisateur supprimé avec succès.');
        }
    } else {
        setFlashMessage('error', 'Vous ne pouvez pas supprimer votre propre compte.');
    }
    redirect('index.php');
}

$users = $db->query("SELECT * FROM users ORDER BY full_name ASC")->fetchAll();

$pageTitle = 'Gestion des Utilisateurs';
$currentPage = 'users';
require_once '../../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-user-cog"></i> Utilisateurs</h1>
        <nav class="breadcrumb">
            <a href="../../index.php">Accueil</a>
            <i class="fas fa-chevron-right"></i>
            <span>Utilisateurs</span>
        </nav>
    </div>
    <a href="create.php" class="btn btn-primary"><i class="fas fa-plus"></i> Nouvel Utilisateur</a>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Liste des Utilisateurs</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Nom d'utilisateur</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>Statut</th>
                        <th>Dernière connexion</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><strong><?php echo $user['full_name']; ?></strong></td>
                        <td><?php echo $user['username']; ?></td>
                        <td><?php echo $user['email'] ?: '-'; ?></td>
                        <td><span class="badge badge-<?php echo $user['role'] === 'admin' ? 'danger' : ($user['role'] === 'manager' ? 'warning' : 'info'); ?>"><?php echo ucfirst($user['role']); ?></span></td>
                        <td><span class="status status-<?php echo $user['status']; ?>"><?php echo $user['status'] === 'active' ? 'Actif' : 'Inactif'; ?></span></td>
                        <td><?php echo $user['last_login'] ? formatDateTime($user['last_login']) : 'Jamais'; ?></td>
                        <td class="actions">
                            <a href="edit.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-warning btn-icon"><i class="fas fa-edit"></i></a>
                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                            <a href="?delete=<?php echo $user['id']; ?>" class="btn btn-sm btn-danger btn-icon" data-confirm="Supprimer cet utilisateur?"><i class="fas fa-trash"></i></a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
