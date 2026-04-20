<?php
require_once '../../includes/config.php';
requireLogin();

$db = getDB();
$error = '';

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    redirect('index.php');
}

// Get order
$stmt = $db->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$id]);
$order = $stmt->fetch();

if (!$order) {
    setFlashMessage('error', 'Commande non trouvée.');
    redirect('index.php');
}

// Get customers
$customers = $db->query("SELECT id, company_name, contact_name FROM customers WHERE status = 'active' ORDER BY company_name ASC")->fetchAll();

// Get order items
$items = $db->prepare("SELECT oi.*, a.code, a.name, a.quantity as stock, a.sale_price, a.wholesale_price 
    FROM order_items oi 
    LEFT JOIN articles a ON oi.article_id = a.id 
    WHERE oi.order_id = ?");
$items->execute([$id]);
$orderItems = $items->fetchAll();

// Get all articles for adding new items
$articles = $db->query("SELECT id, code, name, sale_price, wholesale_price, quantity FROM articles WHERE status = 'active' ORDER BY name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = intval($_POST['customer_id'] ?? 0);
    $order_date = $_POST['order_date'] ?: date('Y-m-d');
    $delivery_date = $_POST['delivery_date'] ?: null;
    $status = $_POST['status'] ?? 'pending';
    $payment_status = $_POST['payment_status'] ?? 'unpaid';
    $notes = sanitize($_POST['notes'] ?? '');
    
    if (!$customer_id) {
        $error = 'Veuillez sélectionner un client.';
    } else {
        $stmt = $db->prepare("UPDATE orders SET customer_id = ?, order_date = ?, delivery_date = ?, status = ?, payment_status = ?, notes = ? WHERE id = ?");
        if ($stmt->execute([$customer_id, $order_date, $delivery_date, $status, $payment_status, $notes, $id])) {
            logActivity('Modification commande', 'order', $id);
            setFlashMessage('success', 'Commande mise à jour avec succès.');
            redirect('view.php?id=' . $id);
        } else {
            $error = 'Erreur lors de la mise à jour de la commande.';
        }
    }
}

$pageTitle = 'Modifier Commande #' . $order['order_number'];
$currentPage = 'orders';
require_once '../../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-shopping-cart"></i> Modifier Commande #<?php echo $order['order_number']; ?></h1>
        <nav class="breadcrumb">
            <a href="../../index.php">Accueil</a>
            <i class="fas fa-chevron-right"></i>
            <a href="index.php">Commandes</a>
            <i class="fas fa-chevron-right"></i>
            <span>Modifier</span>
        </nav>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Informations de la Commande</h3>
    </div>
    <div class="card-body">
        <?php if ($error): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
        </div>
        <?php endif; ?>
        
        <form method="POST" action="" data-validate>
            <div class="form-row">
                <div class="form-group">
                    <label for="customer_id">Client <span class="text-danger">*</span></label>
                    <select id="customer_id" name="customer_id" class="form-control" required>
                        <option value="">-- Sélectionner un client --</option>
                        <?php foreach ($customers as $cust): ?>
                        <option value="<?php echo $cust['id']; ?>" <?php echo $order['customer_id'] == $cust['id'] ? 'selected' : ''; ?>>
                            <?php echo $cust['company_name'] ? $cust['company_name'] . ' (' . $cust['contact_name'] . ')' : $cust['contact_name']; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="order_date">Date de commande</label>
                    <input type="date" id="order_date" name="order_date" class="form-control" 
                           value="<?php echo $order['order_date']; ?>">
                </div>
                
                <div class="form-group">
                    <label for="delivery_date">Date de livraison prévue</label>
                    <input type="date" id="delivery_date" name="delivery_date" class="form-control"
                           value="<?php echo $order['delivery_date']; ?>">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="status">Statut</label>
                    <select id="status" name="status" class="form-control">
                        <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>En attente</option>
                        <option value="confirmed" <?php echo $order['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmée</option>
                        <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>En traitement</option>
                        <option value="shipped" <?php echo $order['status'] === 'shipped' ? 'selected' : ''; ?>>Expédiée</option>
                        <option value="delivered" <?php echo $order['status'] === 'delivered' ? 'selected' : ''; ?>>Livrée</option>
                        <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Annulée</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="payment_status">Statut de paiement</label>
                    <select id="payment_status" name="payment_status" class="form-control">
                        <option value="unpaid" <?php echo $order['payment_status'] === 'unpaid' ? 'selected' : ''; ?>>Non payé</option>
                        <option value="partial" <?php echo $order['payment_status'] === 'partial' ? 'selected' : ''; ?>>Partiel</option>
                        <option value="paid" <?php echo $order['payment_status'] === 'paid' ? 'selected' : ''; ?>>Payé</option>
                    </select>
                </div>
            </div>
            
            <!-- Order Items Display (Read-only) -->
            <h4 style="margin: 30px 0 15px; padding-bottom: 10px; border-bottom: 1px solid var(--gray-200);">
                <i class="fas fa-boxes"></i> Articles Commandés
            </h4>
            
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Article</th>
                            <th>Quantité</th>
                            <th>Prix Unitaire</th>
                            <th>Remise</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orderItems as $item): ?>
                        <tr>
                            <td><?php echo $item['name']; ?> (<?php echo $item['code']; ?>)</td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td><?php echo formatMoney($item['unit_price']); ?></td>
                            <td><?php echo formatMoney($item['discount']); ?></td>
                            <td><?php echo formatMoney($item['total']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot style="background: var(--gray-100); font-weight: bold;">
                        <tr>
                            <td colspan="4" style="text-align: right;">Sous-total:</td>
                            <td><?php echo formatMoney($order['subtotal']); ?></td>
                        </tr>
                        <tr>
                            <td colspan="4" style="text-align: right;">Remise:</td>
                            <td><?php echo formatMoney($order['discount']); ?></td>
                        </tr>
                        <tr>
                            <td colspan="4" style="text-align: right;">TVA:</td>
                            <td><?php echo formatMoney($order['tax_amount']); ?></td>
                        </tr>
                        <tr style="font-size: 16px;">
                            <td colspan="4" style="text-align: right;">TOTAL:</td>
                            <td><?php echo formatMoney($order['total']); ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <div class="form-group" style="margin-top: 20px;">
                <label for="notes">Notes</label>
                <textarea id="notes" name="notes" class="form-control" rows="3"><?php echo $order['notes']; ?></textarea>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Mettre à jour
                </button>
                <a href="view.php?id=<?php echo $order['id']; ?>" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Annuler
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
