<?php
require_once '../../includes/config.php';
requireLogin();

$db = getDB();
$error = '';

// Get customers
$customers = $db->query("SELECT id, company_name, contact_name FROM customers WHERE status = 'active' ORDER BY company_name ASC")->fetchAll();

// Get articles
$articles = $db->query("SELECT id, code, name, sale_price, wholesale_price, quantity FROM articles WHERE status = 'active' ORDER BY name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = intval($_POST['customer_id'] ?? 0);
    $order_date = $_POST['order_date'] ?: date('Y-m-d');
    $delivery_date = $_POST['delivery_date'] ?: null;
    $notes = sanitize($_POST['notes'] ?? '');
    $payment_method = $_POST['payment_method'] ?? 'cash';
    
    $article_ids = $_POST['article_id'] ?? [];
    $quantities = $_POST['quantity'] ?? [];
    $prices = $_POST['unit_price'] ?? [];
    $discounts = $_POST['discount'] ?? [];
    
    if (!$customer_id) {
        $error = 'Veuillez sélectionner un client.';
    } elseif (empty($article_ids)) {
        $error = 'Veuillez ajouter au moins un article.';
    } else {
        // Generate order number
        $lastId = $db->query("SELECT MAX(id) FROM orders")->fetchColumn() + 1;
        $order_number = generateCode('CMD', $lastId);
        
        // Calculate totals
        $subtotal = 0;
        $totalDiscount = 0;
        
        for ($i = 0; $i < count($article_ids); $i++) {
            if ($article_ids[$i] && $quantities[$i] > 0) {
                $lineTotal = $quantities[$i] * $prices[$i];
                $lineDiscount = $discounts[$i] ?? 0;
                $subtotal += $lineTotal;
                $totalDiscount += $lineDiscount;
            }
        }
        
        $taxRate = APP_TAX_RATE;
        $taxAmount = ($subtotal - $totalDiscount) * ($taxRate / 100);
        $total = $subtotal - $totalDiscount + $taxAmount;
        
        // Insert order
        $stmt = $db->prepare("INSERT INTO orders (order_number, customer_id, user_id, order_date, delivery_date, subtotal, discount, tax_rate, tax_amount, total, payment_method, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$order_number, $customer_id, $_SESSION['user_id'], $order_date, $delivery_date, $subtotal, $totalDiscount, $taxRate, $taxAmount, $total, $payment_method, $notes])) {
            $orderId = $db->lastInsertId();
            
            // Insert order items
            $itemStmt = $db->prepare("INSERT INTO order_items (order_id, article_id, quantity, unit_price, discount, total) VALUES (?, ?, ?, ?, ?, ?)");
            $updateStockStmt = $db->prepare("UPDATE articles SET quantity = quantity - ? WHERE id = ?");
            
            for ($i = 0; $i < count($article_ids); $i++) {
                if ($article_ids[$i] && $quantities[$i] > 0) {
                    $lineTotal = ($quantities[$i] * $prices[$i]) - ($discounts[$i] ?? 0);
                    $itemStmt->execute([$orderId, $article_ids[$i], $quantities[$i], $prices[$i], $discounts[$i] ?? 0, $lineTotal]);
                    
                    // Update stock
                    $updateStockStmt->execute([$quantities[$i], $article_ids[$i]]);
                }
            }
            
            logActivity('Création commande', 'order', $orderId);
            setFlashMessage('success', 'Commande créée avec succès. N° ' . $order_number);
            redirect('view.php?id=' . $orderId);
        } else {
            $error = 'Erreur lors de la création de la commande.';
        }
    }
}

$pageTitle = 'Nouvelle Commande';
$currentPage = 'orders';
require_once '../../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-shopping-cart"></i> Nouvelle Commande</h1>
        <nav class="breadcrumb">
            <a href="../../index.php">Accueil</a>
            <i class="fas fa-chevron-right"></i>
            <a href="index.php">Commandes</a>
            <i class="fas fa-chevron-right"></i>
            <span>Nouveau</span>
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
        
        <form method="POST" action="" id="orderForm" data-validate>
            <div class="form-row">
                <div class="form-group">
                    <label for="customer_id">Client <span class="text-danger">*</span></label>
                    <select id="customer_id" name="customer_id" class="form-control" required>
                        <option value="">-- Sélectionner un client --</option>
                        <?php foreach ($customers as $cust): ?>
                        <option value="<?php echo $cust['id']; ?>" <?php echo ($_GET['customer_id'] ?? '') == $cust['id'] ? 'selected' : ''; ?>>
                            <?php echo $cust['company_name'] ? $cust['company_name'] . ' (' . $cust['contact_name'] . ')' : $cust['contact_name']; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="order_date">Date de commande</label>
                    <input type="date" id="order_date" name="order_date" class="form-control" 
                           value="<?php echo date('Y-m-d'); ?>">
                </div>
                
                <div class="form-group">
                    <label for="delivery_date">Date de livraison prévue</label>
                    <input type="date" id="delivery_date" name="delivery_date" class="form-control">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="payment_method">Mode de paiement</label>
                    <select id="payment_method" name="payment_method" class="form-control">
                        <option value="cash">Espèces</option>
                        <option value="check">Chèque</option>
                        <option value="transfer">Virement</option>
                        <option value="credit">Crédit</option>
                    </select>
                </div>
            </div>
            
            <!-- Order Items -->
            <h4 style="margin: 30px 0 15px; padding-bottom: 10px; border-bottom: 1px solid var(--gray-200);">
                <i class="fas fa-boxes"></i> Articles
            </h4>
            
            <div class="table-responsive">
                <table class="data-table" id="itemsTable">
                    <thead>
                        <tr>
                            <th>Article</th>
                            <th style="width: 100px;">Quantité</th>
                            <th style="width: 150px;">Prix Unitaire</th>
                            <th style="width: 120px;">Remise</th>
                            <th style="width: 150px;">Total</th>
                            <th style="width: 50px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="calc-row">
                            <td>
                                <select name="article_id[]" class="form-control article-select" required>
                                    <option value="">-- Sélectionner --</option>
                                    <?php foreach ($articles as $art): ?>
                                    <option value="<?php echo $art['id']; ?>" 
                                            data-price="<?php echo $art['sale_price']; ?>"
                                            data-wholesale="<?php echo $art['wholesale_price']; ?>"
                                            data-stock="<?php echo $art['quantity']; ?>">
                                        <?php echo $art['name']; ?> (Stock: <?php echo $art['quantity']; ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td>
                                <input type="number" name="quantity[]" class="form-control quantity" min="1" value="1" data-quantity required>
                            </td>
                            <td>
                                <input type="number" name="unit_price[]" class="form-control unit-price" step="0.001" min="0" data-price required>
                            </td>
                            <td>
                                <input type="number" name="discount[]" class="form-control discount" step="0.001" min="0" value="0" data-discount>
                            </td>
                            <td>
                                <input type="number" class="form-control line-total" step="0.001" readonly data-total>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-danger btn-icon remove-row">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="6">
                                <button type="button" class="btn btn-success" id="addRow">
                                    <i class="fas fa-plus"></i> Ajouter un article
                                </button>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <!-- Totals -->
            <div style="max-width: 400px; margin-left: auto; margin-top: 20px;">
                <div class="form-row" style="margin-bottom: 10px;">
                    <label style="flex: 1;">Sous-total:</label>
                    <input type="number" id="subtotal" name="subtotal" class="form-control" style="width: 150px;" readonly value="0">
                </div>
                <div class="form-row" style="margin-bottom: 10px;">
                    <label style="flex: 1;">Remise totale:</label>
                    <input type="number" id="totalDiscount" name="total_discount" class="form-control" style="width: 150px;" readonly value="0">
                </div>
                <div class="form-row" style="margin-bottom: 10px;">
                    <label style="flex: 1;">TVA (<?php echo APP_TAX_RATE; ?>%):</label>
                    <input type="number" id="taxAmount" name="tax_amount" class="form-control" style="width: 150px;" readonly value="0">
                </div>
                <div class="form-row" style="margin-bottom: 10px; font-size: 18px; font-weight: bold;">
                    <label style="flex: 1;">TOTAL:</label>
                    <input type="number" id="grandTotal" name="total" class="form-control" style="width: 150px; font-weight: bold;" readonly value="0" data-grand-total>
                </div>
            </div>
            
            <div class="form-group" style="margin-top: 20px;">
                <label for="notes">Notes</label>
                <textarea id="notes" name="notes" class="form-control" rows="3" placeholder="Notes additionnelles..."></textarea>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Créer la commande
                </button>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Annuler
                </a>
            </div>
        </form>
    </div>
</div>

<template id="rowTemplate">
    <tr class="calc-row">
        <td>
            <select name="article_id[]" class="form-control article-select" required>
                <option value="">-- Sélectionner --</option>
                <?php foreach ($articles as $art): ?>
                <option value="<?php echo $art['id']; ?>" 
                        data-price="<?php echo $art['sale_price']; ?>"
                        data-wholesale="<?php echo $art['wholesale_price']; ?>"
                        data-stock="<?php echo $art['quantity']; ?>">
                    <?php echo $art['name']; ?> (Stock: <?php echo $art['quantity']; ?>)
                </option>
                <?php endforeach; ?>
            </select>
        </td>
        <td>
            <input type="number" name="quantity[]" class="form-control quantity" min="1" value="1" data-quantity required>
        </td>
        <td>
            <input type="number" name="unit_price[]" class="form-control unit-price" step="0.001" min="0" data-price required>
        </td>
        <td>
            <input type="number" name="discount[]" class="form-control discount" step="0.001" min="0" value="0" data-discount>
        </td>
        <td>
            <input type="number" class="form-control line-total" step="0.001" readonly data-total>
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-danger btn-icon remove-row">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    </tr>
</template>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const itemsTable = document.getElementById('itemsTable');
    const addRowBtn = document.getElementById('addRow');
    const rowTemplate = document.getElementById('rowTemplate');
    
    // Add new row
    addRowBtn.addEventListener('click', function() {
        const tbody = itemsTable.querySelector('tbody');
        const clone = rowTemplate.content.cloneNode(true);
        tbody.appendChild(clone);
        initRowEvents();
        calculateTotals();
    });
    
    // Remove row
    function initRowEvents() {
        document.querySelectorAll('.remove-row').forEach(function(btn) {
            btn.removeEventListener('click', removeRow);
            btn.addEventListener('click', removeRow);
        });
        
        document.querySelectorAll('.article-select').forEach(function(select) {
            select.removeEventListener('change', updatePrice);
            select.addEventListener('change', updatePrice);
        });
        
        document.querySelectorAll('.quantity, .unit-price, .discount').forEach(function(input) {
            input.removeEventListener('input', calculateTotals);
            input.addEventListener('input', calculateTotals);
        });
    }
    
    function removeRow() {
        const rows = itemsTable.querySelectorAll('tbody tr');
        if (rows.length > 1) {
            this.closest('tr').remove();
            calculateTotals();
        }
    }
    
    function updatePrice() {
        const row = this.closest('tr');
        const selectedOption = this.options[this.selectedIndex];
        const price = selectedOption.dataset.price || 0;
        row.querySelector('.unit-price').value = price;
        calculateTotals();
    }
    
    function calculateTotals() {
        let subtotal = 0;
        let totalDiscount = 0;
        
        document.querySelectorAll('.calc-row').forEach(function(row) {
            const qty = parseFloat(row.querySelector('.quantity').value) || 0;
            const price = parseFloat(row.querySelector('.unit-price').value) || 0;
            const discount = parseFloat(row.querySelector('.discount').value) || 0;
            
            const lineTotal = (qty * price) - discount;
            row.querySelector('.line-total').value = lineTotal.toFixed(3);
            
            subtotal += (qty * price);
            totalDiscount += discount;
        });
        
        const taxRate = <?php echo APP_TAX_RATE; ?>;
        const taxableAmount = subtotal - totalDiscount;
        const taxAmount = taxableAmount * (taxRate / 100);
        const grandTotal = taxableAmount + taxAmount;
        
        document.getElementById('subtotal').value = subtotal.toFixed(3);
        document.getElementById('totalDiscount').value = totalDiscount.toFixed(3);
        document.getElementById('taxAmount').value = taxAmount.toFixed(3);
        document.getElementById('grandTotal').value = grandTotal.toFixed(3);
    }
    
    initRowEvents();
    calculateTotals();
});
</script>

<?php require_once '../../includes/footer.php'; ?>
