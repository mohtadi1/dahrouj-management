<?php
require_once '../../includes/config.php';
requireLogin();

$db = getDB();

$id = intval($_GET['id'] ?? 0);
if (!$id) redirect('index.php');

$stmt = $db->prepare("SELECT p.*, pr.company_name, pr.contact_name, pr.phone, pr.email 
    FROM purchases p 
    LEFT JOIN partners pr ON p.partner_id = pr.id 
    WHERE p.id = ?");
$stmt->execute([$id]);
$purchase = $stmt->fetch();

if (!$purchase) {
    setFlashMessage('error', 'Achat non trouvé.');
    redirect('index.php');
}

$items = $db->prepare("SELECT pi.*, a.code, a.name, a.unit 
    FROM purchase_items pi 
    LEFT JOIN articles a ON pi.article_id = a.id 
    WHERE pi.purchase_id = ?");
$items->execute([$id]);
$items = $items->fetchAll();

$pageTitle = 'Détails Achat #' . $purchase['purchase_number'];
$currentPage = 'purchases';
require_once '../../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-truck-loading"></i> Achat #<?php echo $purchase['purchase_number']; ?></h1>
        <nav class="breadcrumb">
            <a href="../../index.php">Accueil</a>
            <i class="fas fa-chevron-right"></i>
            <a href="index.php">Achats</a>
            <i class="fas fa-chevron-right"></i>
            <span>Détails</span>
        </nav>
    </div>
    <div class="quick-actions">
        <a href="edit.php?id=<?php echo $purchase['id']; ?>" class="btn btn-warning"><i class="fas fa-edit"></i> Modifier</a>
        <button class="btn btn-info" onclick="window.print()"><i class="fas fa-print"></i> Imprimer</button>
        <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Retour</a>
    </div>
</div>

<div class="dashboard-grid">
    <div class="widget">
        <div class="widget-header"><h3 class="widget-title"><i class="fas fa-info-circle"></i> Informations</h3></div>
        <div class="widget-body">
            <table class="data-table">
                <tr><td><strong>N° Achat:</strong></td><td><span style="font-size: 18px; font-weight: bold;">#<?php echo $purchase['purchase_number']; ?></span></td></tr>
                <tr><td><strong>Date:</strong></td><td><?php echo formatDate($purchase['purchase_date']); ?></td></tr>
                <tr><td><strong>Réception prévue:</strong></td><td><?php echo $purchase['expected_date'] ? formatDate($purchase['expected_date']) : '-'; ?></td></tr>
                <tr><td><strong>Statut:</strong></td><td><span class="status status-<?php echo $purchase['status']; ?>"><?php echo ucfirst($purchase['status']); ?></span></td></tr>
                <tr><td><strong>Paiement:</strong></td><td><span class="badge badge-<?php echo $purchase['payment_status'] === 'paid' ? 'success' : ($purchase['payment_status'] === 'partial' ? 'warning' : 'danger'); ?>"><?php echo $purchase['payment_status'] === 'paid' ? 'Payé' : ($purchase['payment_status'] === 'partial' ? 'Partiel' : 'Non payé'); ?></span></td></tr>
            </table>
        </div>
    </div>
    
    <div class="widget">
        <div class="widget-header"><h3 class="widget-title"><i class="fas fa-handshake"></i> Fournisseur</h3></div>
        <div class="widget-body">
            <table class="data-table">
                <tr><td><strong>Société:</strong></td><td><?php echo $purchase['company_name']; ?></td></tr>
                <tr><td><strong>Contact:</strong></td><td><?php echo $purchase['contact_name'] ?: '-'; ?></td></tr>
                <tr><td><strong>Email:</strong></td><td><?php echo $purchase['email'] ?: '-'; ?></td></tr>
                <tr><td><strong>Téléphone:</strong></td><td><?php echo $purchase['phone'] ?: '-'; ?></td></tr>
            </table>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-boxes"></i> Articles</h3></div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr><th>Code</th><th>Article</th><th>Qté</th><th>Prix Unitaire</th><th>Total</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?php echo $item['code']; ?></td>
                        <td><?php echo $item['name']; ?></td>
                        <td><?php echo $item['quantity']; ?> <?php echo $item['unit']; ?></td>
                        <td><?php echo formatMoney($item['unit_price']); ?></td>
                        <td><strong><?php echo formatMoney($item['total']); ?></strong></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot style="background: var(--gray-100); font-weight: bold;">
                    <tr><td colspan="4" style="text-align: right;">Sous-total:</td><td><?php echo formatMoney($purchase['subtotal']); ?></td></tr>
                    <tr><td colspan="4" style="text-align: right;">TVA:</td><td><?php echo formatMoney($purchase['tax_amount']); ?></td></tr>
                    <tr style="font-size: 16px; background: var(--primary-color); color: white;"><td colspan="4" style="text-align: right;">TOTAL:</td><td><?php echo formatMoney($purchase['total']); ?></td></tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<?php if ($purchase['notes']): ?>
<div class="card">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-sticky-note"></i> Notes</h3></div>
    <div class="card-body"><p><?php echo nl2br($purchase['notes']); ?></p></div>
</div>
<?php endif; ?>

<?php require_once '../../includes/footer.php'; ?>
