<?php
require_once '../../includes/config.php';
requireLogin();

$db = getDB();

// Handle delete action
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    requireManager();
    $id = intval($_GET['delete']);
    
    $stmt = $db->prepare("DELETE FROM orders WHERE id = ? AND status = 'pending'");
    if ($stmt->execute([$id])) {
        logActivity('Suppression commande', 'order', $id);
        setFlashMessage('success', 'Commande supprimée avec succès.');
    } else {
        setFlashMessage('error', 'Impossible de supprimer cette commande. Seules les commandes en attente peuvent être supprimées.');
    }
    redirect('index.php');
}

// Get filter parameters
$statusFilter = $_GET['status'] ?? '';
$paymentFilter = $_GET['payment'] ?? '';
$customerFilter = $_GET['customer'] ?? '';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';

// Build query
$sql = "SELECT o.*, c.company_name, c.contact_name, c.phone 
    FROM orders o 
    LEFT JOIN customers c ON o.customer_id = c.id 
    WHERE 1=1";
$params = [];

if ($statusFilter) {
    $sql .= " AND o.status = ?";
    $params[] = $statusFilter;
}

if ($paymentFilter) {
    $sql .= " AND o.payment_status = ?";
    $params[] = $paymentFilter;
}

if ($customerFilter) {
    $sql .= " AND o.customer_id = ?";
    $params[] = $customerFilter;
}

if ($dateFrom) {
    $sql .= " AND o.order_date >= ?";
    $params[] = $dateFrom;
}

if ($dateTo) {
    $sql .= " AND o.order_date <= ?";
    $params[] = $dateTo;
}

$sql .= " ORDER BY o.created_at DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

// Get customers for filter
$customers = $db->query("SELECT id, company_name, contact_name FROM customers WHERE status = 'active' ORDER BY company_name ASC")->fetchAll();

$pageTitle = 'Gestion des Commandes';
$currentPage = 'orders';
require_once '../../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-shopping-cart"></i> Commandes</h1>
        <nav class="breadcrumb">
            <a href="../../index.php">Accueil</a>
            <i class="fas fa-chevron-right"></i>
            <span>Commandes</span>
        </nav>
    </div>
    <a href="create.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Nouvelle Commande
    </a>
</div>

<!-- Filter Bar -->
<div class="card">
    <div class="card-body">
        <form method="GET" action="" class="filter-bar">
            <div class="form-group" style="margin-bottom: 0;">
                <select name="customer" class="form-control">
                    <option value="">Tous les clients</option>
                    <?php foreach ($customers as $cust): ?>
                    <option value="<?php echo $cust['id']; ?>" <?php echo $customerFilter == $cust['id'] ? 'selected' : ''; ?>>
                        <?php echo $cust['company_name'] ?: $cust['contact_name']; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <select name="status" class="form-control">
                    <option value="">Tous les statuts</option>
                    <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>En attente</option>
                    <option value="confirmed" <?php echo $statusFilter === 'confirmed' ? 'selected' : ''; ?>>Confirmée</option>
                    <option value="processing" <?php echo $statusFilter === 'processing' ? 'selected' : ''; ?>>En traitement</option>
                    <option value="shipped" <?php echo $statusFilter === 'shipped' ? 'selected' : ''; ?>>Expédiée</option>
                    <option value="delivered" <?php echo $statusFilter === 'delivered' ? 'selected' : ''; ?>>Livrée</option>
                    <option value="cancelled" <?php echo $statusFilter === 'cancelled' ? 'selected' : ''; ?>>Annulée</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <select name="payment" class="form-control">
                    <option value="">Tous les paiements</option>
                    <option value="unpaid" <?php echo $paymentFilter === 'unpaid' ? 'selected' : ''; ?>>Non payé</option>
                    <option value="partial" <?php echo $paymentFilter === 'partial' ? 'selected' : ''; ?>>Partiel</option>
                    <option value="paid" <?php echo $paymentFilter === 'paid' ? 'selected' : ''; ?>>Payé</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <input type="date" name="date_from" class="form-control" placeholder="Du" value="<?php echo $dateFrom; ?>">
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <input type="date" name="date_to" class="form-control" placeholder="Au" value="<?php echo $dateTo; ?>">
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-filter"></i> Filtrer
            </button>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-times"></i> Réinitialiser
            </a>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Liste des Commandes</h3>
        <span class="badge badge-primary"><?php echo count($orders); ?> commande(s)</span>
    </div>
    <div class="card-body">
        <?php if (count($orders) > 0): ?>
        <div class="table-responsive">
            <table class="data-table" id="ordersTable">
                <thead>
                    <tr>
                        <th>N° Commande</th>
                        <th>Client</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Payé</th>
                        <th>Statut</th>
                        <th>Paiement</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><strong>#<?php echo $order['order_number']; ?></strong></td>
                        <td><?php echo $order['company_name'] ?: $order['contact_name']; ?></td>
                        <td><?php echo formatDate($order['order_date']); ?></td>
                        <td><?php echo formatMoney($order['total']); ?></td>
                        <td><?php echo formatMoney($order['paid_amount']); ?></td>
                        <td><span class="status status-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></td>
                        <td><span class="badge badge-<?php echo $order['payment_status'] === 'paid' ? 'success' : ($order['payment_status'] === 'partial' ? 'warning' : 'danger'); ?>"><?php echo $order['payment_status'] === 'paid' ? 'Payé' : ($order['payment_status'] === 'partial' ? 'Partiel' : 'Non payé'); ?></span></td>
                        <td class="actions">
                            <a href="view.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-info btn-icon" data-tooltip="Voir">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="edit.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-warning btn-icon" data-tooltip="Modifier">
                                <i class="fas fa-edit"></i>
                            </a>
                            <?php if ($order['status'] === 'pending'): ?>
                            <a href="?delete=<?php echo $order['id']; ?>" class="btn btn-sm btn-danger btn-icon" data-confirm="Êtes-vous sûr de vouloir supprimer cette commande?" data-tooltip="Supprimer">
                                <i class="fas fa-trash"></i>
                            </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-shopping-cart"></i>
            <h3>Aucune commande trouvée</h3>
            <p>Aucune commande ne correspond à vos critères de recherche</p>
            <a href="create.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nouvelle Commande
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
