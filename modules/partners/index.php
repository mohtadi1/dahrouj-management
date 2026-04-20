<?php
require_once '../../includes/config.php';
requireLogin();

$db = getDB();

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    requireManager();
    $id = intval($_GET['delete']);
    $stmt = $db->prepare("DELETE FROM partners WHERE id = ?");
    if ($stmt->execute([$id])) {
        logActivity('Suppression fournisseur', 'partner', $id);
        setFlashMessage('success', 'Fournisseur supprimé avec succès.');
    }
    redirect('index.php');
}

$partners = $db->query("SELECT p.*, 
    (SELECT COUNT(*) FROM purchases WHERE partner_id = p.id) as purchase_count,
    (SELECT COALESCE(SUM(total), 0) FROM purchases WHERE partner_id = p.id AND status != 'cancelled') as total_purchases
    FROM partners p 
    ORDER BY p.company_name ASC")->fetchAll();

$pageTitle = 'Gestion des Fournisseurs';
$currentPage = 'partners';
require_once '../../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-handshake"></i> Fournisseurs</h1>
        <nav class="breadcrumb">
            <a href="../../index.php">Accueil</a>
            <i class="fas fa-chevron-right"></i>
            <span>Fournisseurs</span>
        </nav>
    </div>
    <a href="create.php" class="btn btn-primary"><i class="fas fa-plus"></i> Nouveau Fournisseur</a>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Liste des Fournisseurs</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Société</th>
                        <th>Contact</th>
                        <th>Téléphone</th>
                        <th>Type</th>
                        <th>Achats</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($partners as $partner): ?>
                    <tr>
                        <td><strong><?php echo $partner['code']; ?></strong></td>
                        <td><?php echo $partner['company_name']; ?></td>
                        <td><?php echo $partner['contact_name'] ?: '-'; ?></td>
                        <td><?php echo $partner['phone']; ?></td>
                        <td><span class="badge badge-info"><?php echo ucfirst($partner['partner_type']); ?></span></td>
                        <td><span class="badge badge-primary"><?php echo $partner['purchase_count']; ?></span></td>
                        <td><span class="status status-<?php echo $partner['status']; ?>"><?php echo $partner['status'] === 'active' ? 'Actif' : 'Inactif'; ?></span></td>
                        <td class="actions">
                            <a href="view.php?id=<?php echo $partner['id']; ?>" class="btn btn-sm btn-info btn-icon"><i class="fas fa-eye"></i></a>
                            <?php if (isManager()): ?>
                            <a href="edit.php?id=<?php echo $partner['id']; ?>" class="btn btn-sm btn-warning btn-icon"><i class="fas fa-edit"></i></a>
                            <a href="?delete=<?php echo $partner['id']; ?>" class="btn btn-sm btn-danger btn-icon" data-confirm="Supprimer ce fournisseur?"><i class="fas fa-trash"></i></a>
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
