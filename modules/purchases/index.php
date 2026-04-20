<?php
require_once '../../includes/config.php';
requireLogin();

$db = getDB();

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    requireManager();
    $id = intval($_GET['delete']);
    $stmt = $db->prepare("DELETE FROM purchases WHERE id = ? AND status = 'pending'");
    if ($stmt->execute([$id])) {
        logActivity('Suppression achat', 'purchase', $id);
        setFlashMessage('success', 'Achat supprimé avec succès.');
    } else {
        setFlashMessage('error', 'Impossible de supprimer cet achat.');
    }
    redirect('index.php');
}

$purchases = $db->query("SELECT p.*, pr.company_name 
    FROM purchases p 
    LEFT JOIN partners pr ON p.partner_id = pr.id 
    ORDER BY p.created_at DESC")->fetchAll();

$pageTitle = 'Gestion des Achats';
$currentPage = 'purchases';
require_once '../../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-truck-loading"></i> Achats</h1>
        <nav class="breadcrumb">
            <a href="../../index.php">Accueil</a>
            <i class="fas fa-chevron-right"></i>
            <span>Achats</span>
        </nav>
    </div>
    <a href="create.php" class="btn btn-primary"><i class="fas fa-plus"></i> Nouvel Achat</a>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Liste des Achats</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>N° Achat</th>
                        <th>Fournisseur</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Payé</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($purchases as $purchase): ?>
                    <tr>
                        <td><strong>#<?php echo $purchase['purchase_number']; ?></strong></td>
                        <td><?php echo $purchase['company_name']; ?></td>
                        <td><?php echo formatDate($purchase['purchase_date']); ?></td>
                        <td><?php echo formatMoney($purchase['total']); ?></td>
                        <td><?php echo formatMoney($purchase['paid_amount']); ?></td>
                        <td><span class="status status-<?php echo $purchase['status']; ?>"><?php echo ucfirst($purchase['status']); ?></span></td>
                        <td class="actions">
                            <a href="view.php?id=<?php echo $purchase['id']; ?>" class="btn btn-sm btn-info btn-icon"><i class="fas fa-eye"></i></a>
                            <a href="edit.php?id=<?php echo $purchase['id']; ?>" class="btn btn-sm btn-warning btn-icon"><i class="fas fa-edit"></i></a>
                            <?php if ($purchase['status'] === 'pending'): ?>
                            <a href="?delete=<?php echo $purchase['id']; ?>" class="btn btn-sm btn-danger btn-icon" data-confirm="Supprimer cet achat?"><i class="fas fa-trash"></i></a>
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
