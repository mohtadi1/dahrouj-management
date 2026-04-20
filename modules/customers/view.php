<?php
require_once '../../includes/config.php';
requireLogin();

$db = getDB();

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    redirect('index.php');
}

// Get customer
$stmt = $db->prepare("SELECT c.*, 
    (SELECT COUNT(*) FROM orders WHERE customer_id = c.id) as order_count,
    (SELECT COALESCE(SUM(total), 0) FROM orders WHERE customer_id = c.id AND status != 'cancelled') as total_orders,
    (SELECT COALESCE(SUM(total - paid_amount), 0) FROM orders WHERE customer_id = c.id AND payment_status != 'paid' AND status != 'cancelled') as balance_due
    FROM customers c 
    WHERE c.id = ?");
$stmt->execute([$id]);
$customer = $stmt->fetch();

if (!$customer) {
    setFlashMessage('error', 'Client non trouvé.');
    redirect('index.php');
}

// Get recent orders
$recentOrders = $db->prepare("SELECT * FROM orders WHERE customer_id = ? ORDER BY created_at DESC LIMIT 10");
$recentOrders->execute([$id]);
$recentOrders = $recentOrders->fetchAll();

$pageTitle = 'Détails Client';
$currentPage = 'customers';
require_once '../../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-users"></i> <?php echo $customer['company_name'] ?: $customer['contact_name']; ?></h1>
        <nav class="breadcrumb">
            <a href="../../index.php">Accueil</a>
            <i class="fas fa-chevron-right"></i>
            <a href="index.php">Clients</a>
            <i class="fas fa-chevron-right"></i>
            <span>Détails</span>
        </nav>
    </div>
    <div class="quick-actions">
        <a href="edit.php?id=<?php echo $customer['id']; ?>" class="btn btn-warning">
            <i class="fas fa-edit"></i> Modifier
        </a>
        <a href="../orders/create.php?customer_id=<?php echo $customer['id']; ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nouvelle Commande
        </a>
        <a href="index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
    </div>
</div>

<div class="dashboard-grid">
    <!-- Customer Info -->
    <div class="widget">
        <div class="widget-header">
            <h3 class="widget-title"><i class="fas fa-info-circle"></i> Informations</h3>
        </div>
        <div class="widget-body">
            <table class="data-table">
                <tr>
                    <td><strong>Code:</strong></td>
                    <td><?php echo $customer['code']; ?></td>
                </tr>
                <tr>
                    <td><strong>Société:</strong></td>
                    <td><?php echo $customer['company_name'] ?: '-'; ?></td>
                </tr>
                <tr>
                    <td><strong>Contact:</strong></td>
                    <td><?php echo $customer['contact_name'] ?: '-'; ?></td>
                </tr>
                <tr>
                    <td><strong>Email:</strong></td>
                    <td><?php echo $customer['email'] ? '<a href="mailto:' . $customer['email'] . '">' . $customer['email'] . '</a>' : '-'; ?></td>
                </tr>
                <tr>
                    <td><strong>Téléphone:</strong></td>
                    <td><?php echo $customer['phone'] ? '<a href="tel:' . $customer['phone'] . '">' . $customer['phone'] . '</a>' : '-'; ?></td>
                </tr>
                <?php if ($customer['phone2']): ?>
                <tr>
                    <td><strong>Téléphone 2:</strong></td>
                    <td><a href="tel:<?php echo $customer['phone2']; ?>"><?php echo $customer['phone2']; ?></a></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td><strong>Adresse:</strong></td>
                    <td><?php echo $customer['address'] ?: '-'; ?></td>
                </tr>
                <tr>
                    <td><strong>Ville:</strong></td>
                    <td><?php echo $customer['city'] ?: '-'; ?></td>
                </tr>
                <tr>
                    <td><strong>Pays:</strong></td>
                    <td><?php echo $customer['country']; ?></td>
                </tr>
                <tr>
                    <td><strong>Matricule fiscal:</strong></td>
                    <td><?php echo $customer['tax_number'] ?: '-'; ?></td>
                </tr>
                <tr>
                    <td><strong>Registre:</strong></td>
                    <td><?php echo $customer['registration_number'] ?: '-'; ?></td>
                </tr>
                <tr>
                    <td><strong>Statut:</strong></td>
                    <td><span class="status status-<?php echo $customer['status']; ?>"><?php echo $customer['status'] === 'active' ? 'Actif' : 'Inactif'; ?></span></td>
                </tr>
            </table>
        </div>
    </div>
    
    <!-- Statistics -->
    <div class="widget">
        <div class="widget-header">
            <h3 class="widget-title"><i class="fas fa-chart-pie"></i> Statistiques</h3>
        </div>
        <div class="widget-body">
            <div class="stats-grid" style="grid-template-columns: 1fr;">
                <div class="stat-card">
                    <div class="stat-icon primary">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $customer['order_count']; ?></h3>
                        <p>Commandes</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon success">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo formatMoney($customer['total_orders']); ?></h3>
                        <p>Total des achats</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon <?php echo $customer['balance_due'] > 0 ? 'danger' : 'success'; ?>">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo formatMoney($customer['balance_due']); ?></h3>
                        <p>Solde dû</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Notes -->
<?php if ($customer['notes']): ?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-sticky-note"></i> Notes</h3>
    </div>
    <div class="card-body">
        <p><?php echo nl2br($customer['notes']); ?></p>
    </div>
</div>
<?php endif; ?>

<!-- Recent Orders -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-history"></i> Commandes Récentes</h3>
        <a href="../orders/index.php?customer=<?php echo $customer['id']; ?>" class="btn btn-sm btn-outline-primary">Voir tout</a>
    </div>
    <div class="card-body">
        <?php if (count($recentOrders) > 0): ?>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>N° Commande</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Payé</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentOrders as $order): ?>
                    <tr>
                        <td><strong>#<?php echo $order['order_number']; ?></strong></td>
                        <td><?php echo formatDate($order['order_date']); ?></td>
                        <td><?php echo formatMoney($order['total']); ?></td>
                        <td><?php echo formatMoney($order['paid_amount']); ?></td>
                        <td><span class="status status-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></td>
                        <td class="actions">
                            <a href="../orders/view.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-info btn-icon">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="empty-state" style="padding: 30px;">
            <i class="fas fa-shopping-cart"></i>
            <h3>Aucune commande</h3>
            <p>Ce client n'a pas encore passé de commande</p>
            <a href="../orders/create.php?customer_id=<?php echo $customer['id']; ?>" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nouvelle Commande
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
