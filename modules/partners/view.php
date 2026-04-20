<?php
require_once '../../includes/config.php';
requireLogin();

$db = getDB();

$id = intval($_GET['id'] ?? 0);
if (!$id) redirect('index.php');

$stmt = $db->prepare("SELECT p.*, 
    (SELECT COUNT(*) FROM purchases WHERE partner_id = p.id) as purchase_count,
    (SELECT COALESCE(SUM(total), 0) FROM purchases WHERE partner_id = p.id AND status != 'cancelled') as total_purchases
    FROM partners p 
    WHERE p.id = ?");
$stmt->execute([$id]);
$partner = $stmt->fetch();

if (!$partner) {
    setFlashMessage('error', 'Fournisseur non trouvé.');
    redirect('index.php');
}

$purchases = $db->prepare("SELECT * FROM purchases WHERE partner_id = ? ORDER BY purchase_date DESC LIMIT 10");
$purchases->execute([$id]);
$purchases = $purchases->fetchAll();

$pageTitle = 'Détails Fournisseur';
$currentPage = 'partners';
require_once '../../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-handshake"></i> <?php echo $partner['company_name']; ?></h1>
        <nav class="breadcrumb">
            <a href="../../index.php">Accueil</a>
            <i class="fas fa-chevron-right"></i>
            <a href="index.php">Fournisseurs</a>
            <i class="fas fa-chevron-right"></i>
            <span>Détails</span>
        </nav>
    </div>
    <div class="quick-actions">
        <?php if (isManager()): ?>
        <a href="edit.php?id=<?php echo $partner['id']; ?>" class="btn btn-warning"><i class="fas fa-edit"></i> Modifier</a>
        <?php endif; ?>
        <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Retour</a>
    </div>
</div>

<div class="dashboard-grid">
    <div class="widget">
        <div class="widget-header"><h3 class="widget-title"><i class="fas fa-info-circle"></i> Informations</h3></div>
        <div class="widget-body">
            <table class="data-table">
                <tr><td><strong>Code:</strong></td><td><?php echo $partner['code']; ?></td></tr>
                <tr><td><strong>Société:</strong></td><td><?php echo $partner['company_name']; ?></td></tr>
                <tr><td><strong>Contact:</strong></td><td><?php echo $partner['contact_name'] ?: '-'; ?></td></tr>
                <tr><td><strong>Email:</strong></td><td><?php echo $partner['email'] ? '<a href="mailto:' . $partner['email'] . '">' . $partner['email'] . '</a>' : '-'; ?></td></tr>
                <tr><td><strong>Téléphone:</strong></td><td><?php echo $partner['phone'] ? '<a href="tel:' . $partner['phone'] . '">' . $partner['phone'] . '</a>' : '-'; ?></td></tr>
                <tr><td><strong>Adresse:</strong></td><td><?php echo $partner['address'] ? $partner['address'] . ', ' . $partner['city'] : '-'; ?></td></tr>
                <tr><td><strong>Type:</strong></td><td><span class="badge badge-info"><?php echo ucfirst($partner['partner_type']); ?></span></td></tr>
                <tr><td><strong>Statut:</strong></td><td><span class="status status-<?php echo $partner['status']; ?>"><?php echo $partner['status'] === 'active' ? 'Actif' : 'Inactif'; ?></span></td></tr>
            </table>
        </div>
    </div>
    
    <div class="widget">
        <div class="widget-header"><h3 class="widget-title"><i class="fas fa-chart-pie"></i> Statistiques</h3></div>
        <div class="widget-body">
            <div class="stats-grid" style="grid-template-columns: 1fr;">
                <div class="stat-card">
                    <div class="stat-icon primary"><i class="fas fa-shopping-cart"></i></div>
                    <div class="stat-content"><h3><?php echo $partner['purchase_count']; ?></h3><p>Achats</p></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon success"><i class="fas fa-money-bill-wave"></i></div>
                    <div class="stat-content"><h3><?php echo formatMoney($partner['total_purchases']); ?></h3><p>Total Achats</p></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($partner['notes']): ?>
<div class="card">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-sticky-note"></i> Notes</h3></div>
    <div class="card-body"><p><?php echo nl2br($partner['notes']); ?></p></div>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-history"></i> Achats Récents</h3>
        <a href="../purchases/index.php?partner=<?php echo $partner['id']; ?>" class="btn btn-sm btn-outline-primary">Voir tout</a>
    </div>
    <div class="card-body">
        <?php if (count($purchases) > 0): ?>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr><th>N° Achat</th><th>Date</th><th>Total</th><th>Statut</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($purchases as $p): ?>
                    <tr>
                        <td><strong>#<?php echo $p['purchase_number']; ?></strong></td>
                        <td><?php echo formatDate($p['purchase_date']); ?></td>
                        <td><?php echo formatMoney($p['total']); ?></td>
                        <td><span class="status status-<?php echo $p['status']; ?>"><?php echo ucfirst($p['status']); ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="empty-state" style="padding: 30px;">
            <i class="fas fa-shopping-cart"></i>
            <h3>Aucun achat</h3>
            <p>Aucun achat enregistré avec ce fournisseur</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
