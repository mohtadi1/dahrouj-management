<?php
require_once '../../includes/config.php';
requireLogin();

$db = getDB();

// Handle delete action
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    requireManager();
    $id = intval($_GET['delete']);
    
    // Check if customer has orders
    $stmt = $db->prepare("SELECT COUNT(*) FROM orders WHERE customer_id = ?");
    $stmt->execute([$id]);
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        setFlashMessage('error', 'Impossible de supprimer ce client car il a des commandes.');
    } else {
        $stmt = $db->prepare("DELETE FROM customers WHERE id = ?");
        if ($stmt->execute([$id])) {
            logActivity('Suppression client', 'customer', $id);
            setFlashMessage('success', 'Client supprimé avec succès.');
        } else {
            setFlashMessage('error', 'Erreur lors de la suppression.');
        }
    }
    redirect('index.php');
}

// Get filter parameters
$search = $_GET['search'] ?? '';
$statusFilter = $_GET['status'] ?? '';

// Build query
$sql = "SELECT c.*, 
    (SELECT COUNT(*) FROM orders WHERE customer_id = c.id) as order_count,
    (SELECT COALESCE(SUM(total), 0) FROM orders WHERE customer_id = c.id AND status != 'cancelled') as total_orders
    FROM customers c 
    WHERE 1=1";
$params = [];

if ($search) {
    $sql .= " AND (c.company_name LIKE ? OR c.contact_name LIKE ? OR c.email LIKE ? OR c.phone LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($statusFilter) {
    $sql .= " AND c.status = ?";
    $params[] = $statusFilter;
}

$sql .= " ORDER BY c.company_name ASC, c.contact_name ASC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$customers = $stmt->fetchAll();

$pageTitle = 'Gestion des Clients';
$currentPage = 'customers';
require_once '../../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-users"></i> Clients</h1>
        <nav class="breadcrumb">
            <a href="../../index.php">Accueil</a>
            <i class="fas fa-chevron-right"></i>
            <span>Clients</span>
        </nav>
    </div>
    <a href="create.php" class="btn btn-primary">
        <i class="fas fa-user-plus"></i> Nouveau Client
    </a>
</div>

<!-- Filter Bar -->
<div class="card">
    <div class="card-body">
        <form method="GET" action="" class="filter-bar">
            <div class="form-group" style="margin-bottom: 0;">
                <input type="text" name="search" class="form-control" placeholder="Rechercher..." 
                       value="<?php echo $search; ?>">
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <select name="status" class="form-control">
                    <option value="">Tous les statuts</option>
                    <option value="active" <?php echo $statusFilter === 'active' ? 'selected' : ''; ?>>Actif</option>
                    <option value="inactive" <?php echo $statusFilter === 'inactive' ? 'selected' : ''; ?>>Inactif</option>
                </select>
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
        <h3 class="card-title">Liste des Clients</h3>
        <span class="badge badge-primary"><?php echo count($customers); ?> client(s)</span>
    </div>
    <div class="card-body">
        <?php if (count($customers) > 0): ?>
        <div class="table-responsive">
            <table class="data-table" id="customersTable">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Société</th>
                        <th>Contact</th>
                        <th>Téléphone</th>
                        <th>Ville</th>
                        <th>Commandes</th>
                        <th>Total</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customers as $customer): ?>
                    <tr>
                        <td><strong><?php echo $customer['code']; ?></strong></td>
                        <td><?php echo $customer['company_name'] ?: '-'; ?></td>
                        <td><?php echo $customer['contact_name']; ?></td>
                        <td><?php echo $customer['phone']; ?></td>
                        <td><?php echo $customer['city'] ?: '-'; ?></td>
                        <td><span class="badge badge-info"><?php echo $customer['order_count']; ?></span></td>
                        <td><?php echo formatMoney($customer['total_orders']); ?></td>
                        <td><span class="status status-<?php echo $customer['status']; ?>"><?php echo $customer['status'] === 'active' ? 'Actif' : 'Inactif'; ?></span></td>
                        <td class="actions">
                            <a href="view.php?id=<?php echo $customer['id']; ?>" class="btn btn-sm btn-info btn-icon" data-tooltip="Voir">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="edit.php?id=<?php echo $customer['id']; ?>" class="btn btn-sm btn-warning btn-icon" data-tooltip="Modifier">
                                <i class="fas fa-edit"></i>
                            </a>
                            <?php if ($customer['order_count'] == 0): ?>
                            <a href="?delete=<?php echo $customer['id']; ?>" class="btn btn-sm btn-danger btn-icon" data-confirm="Êtes-vous sûr de vouloir supprimer ce client?" data-tooltip="Supprimer">
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
            <i class="fas fa-users"></i>
            <h3>Aucun client trouvé</h3>
            <p>Aucun client ne correspond à vos critères de recherche</p>
            <a href="create.php" class="btn btn-primary">
                <i class="fas fa-user-plus"></i> Nouveau Client
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
