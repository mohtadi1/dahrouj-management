<?php
require_once '../../includes/config.php';
requireManager();

$db = getDB();

// Vérifier l'ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    setFlashMessage('danger', 'ID de dépense invalide.');
    redirect('expenses.php');
}

$id = intval($_GET['id']);

// Récupérer la dépense avec les infos du partenaire et de l'utilisateur
$stmt = $db->prepare("SELECT e.*, p.company_name as partner_name, u.username as user_name 
                      FROM expenses e 
                      LEFT JOIN partners p ON e.partner_id = p.id 
                      LEFT JOIN users u ON e.user_id = u.id 
                      WHERE e.id = ?");
$stmt->execute([$id]);
$expense = $stmt->fetch();

if (!$expense) {
    setFlashMessage('danger', 'Dépense introuvable.');
    redirect('expenses.php');
}

$pageTitle = 'Détail de la dépense #' . $expense['expense_number'];
$currentPage = 'expenses';
require_once '../../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-receipt"></i> Dépense #<?php echo $expense['expense_number']; ?></h1>
        <nav class="breadcrumb">
            <a href="../../index.php">Accueil</a>
            <i class="fas fa-chevron-right"></i>
            <a href="expenses.php">Dépenses</a>
            <i class="fas fa-chevron-right"></i>
            <span>Détail</span>
        </nav>
    </div>
    <div class="quick-actions">
        <a href="expense_edit.php?id=<?php echo $expense['id']; ?>" class="btn btn-warning"><i class="fas fa-edit"></i> Modifier</a>
        <a href="expenses.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Retour</a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Informations détaillées</h3>
    </div>
    <div class="card-body">
        <div class="detail-grid">
            <div class="detail-row">
                <div class="detail-label">Numéro :</div>
                <div class="detail-value"><strong><?php echo $expense['expense_number']; ?></strong></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Date :</div>
                <div class="detail-value"><?php echo formatDate($expense['expense_date']); ?></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Catégorie :</div>
                <div class="detail-value"><span class="badge badge-info"><?php echo $expense['category']; ?></span></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Montant :</div>
                <div class="detail-value"><?php echo formatMoney($expense['amount']); ?></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Mode de paiement :</div>
                <div class="detail-value"><?php echo ucfirst($expense['payment_method']); ?></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Statut :</div>
                <div class="detail-value">
                    <span class="status status-<?php echo $expense['status']; ?>"><?php echo ucfirst($expense['status']); ?></span>
                </div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Fournisseur :</div>
                <div class="detail-value"><?php echo $expense['partner_name'] ?: '-'; ?></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Créé par :</div>
                <div class="detail-value"><?php echo $expense['user_name'] ?: 'Système'; ?></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Description :</div>
                <div class="detail-value"><?php echo nl2br(htmlspecialchars($expense['description'])); ?></div>
            </div>
        </div>
    </div>
</div>

<style>
.detail-grid {
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.detail-row {
    display: flex;
    padding: 8px 0;
    border-bottom: 1px solid #e9ecef;
}
.detail-label {
    width: 160px;
    font-weight: 600;
    color: #495057;
}
.detail-value {
    flex: 1;
    color: #212529;
}
@media (max-width: 768px) {
    .detail-row {
        flex-direction: column;
    }
    .detail-label {
        width: auto;
        margin-bottom: 5px;
    }
}
</style>

<?php require_once '../../includes/footer.php'; ?>