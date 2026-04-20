<?php
require_once '../../includes/config.php';
requireManager();

$db = getDB();

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $db->prepare("DELETE FROM expenses WHERE id = ?");
    if ($stmt->execute([$id])) {
        logActivity('Suppression dépense', 'expense', $id);
        setFlashMessage('success', 'Dépense supprimée avec succès.');
    }
    redirect('expenses.php');
}

$expenses = $db->query("SELECT e.*, p.company_name 
    FROM expenses e 
    LEFT JOIN partners p ON e.partner_id = p.id 
    ORDER BY e.expense_date DESC")->fetchAll();

$pageTitle = 'Gestion des Dépenses';
$currentPage = 'expenses';
require_once '../../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-money-bill-wave"></i> Dépenses</h1>
        <nav class="breadcrumb">
            <a href="../../index.php">Accueil</a>
            <i class="fas fa-chevron-right"></i>
            <a href="index.php">Comptabilité</a>
            <i class="fas fa-chevron-right"></i>
            <span>Dépenses</span>
        </nav>
    </div>
    <a href="expense_create.php" class="btn btn-primary"><i class="fas fa-plus"></i> Nouvelle Dépense</a>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Liste des Dépenses</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>N°</th>
                        <th>Date</th>
                        <th>Catégorie</th>
                        <th>Description</th>
                        <th>Montant</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($expenses as $expense): ?>
                    <tr>
                        <td><strong>#<?php echo $expense['expense_number']; ?></strong></td>
                        <td><?php echo formatDate($expense['expense_date']); ?></td>
                        <td><span class="badge badge-info"><?php echo $expense['category']; ?></span></td>
                        <td><?php echo $expense['description']; ?></td>
                        <td><?php echo formatMoney($expense['amount']); ?></td>
                        <td><span class="status status-<?php echo $expense['status']; ?>"><?php echo ucfirst($expense['status']); ?></span></td>
                        <td class="actions">
                            <a href="expense_view.php?id=<?php echo $expense['id']; ?>" class="btn btn-sm btn-info btn-icon"><i class="fas fa-eye"></i></a>
                            <a href="expense_edit.php?id=<?php echo $expense['id']; ?>" class="btn btn-sm btn-warning btn-icon"><i class="fas fa-edit"></i></a>
                            <a href="?delete=<?php echo $expense['id']; ?>" class="btn btn-sm btn-danger btn-icon" data-confirm="Supprimer cette dépense?"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
