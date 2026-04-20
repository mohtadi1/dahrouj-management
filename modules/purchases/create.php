<?php
require_once '../../includes/config.php';
requireManager();

$db = getDB();
$error = '';

$partners = $db->query("SELECT id, company_name FROM partners WHERE status = 'active' ORDER BY company_name ASC")->fetchAll();
$articles = $db->query("SELECT id, code, name, purchase_price FROM articles WHERE status = 'active' ORDER BY name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $partner_id = intval($_POST['partner_id'] ?? 0);
    $purchase_date = $_POST['purchase_date'] ?: date('Y-m-d');
    $expected_date = $_POST['expected_date'] ?: null;
    $notes = sanitize($_POST['notes'] ?? '');
    
    $article_ids = $_POST['article_id'] ?? [];
    $quantities = $_POST['quantity'] ?? [];
    $prices = $_POST['unit_price'] ?? [];
    
    if (!$partner_id) {
        $error = 'Veuillez sélectionner un fournisseur.';
    } elseif (empty($article_ids)) {
        $error = 'Veuillez ajouter au moins un article.';
    } else {
        $lastId = $db->query("SELECT MAX(id) FROM purchases")->fetchColumn() + 1;
        $purchase_number = generateCode('ACH', $lastId);
        
        $subtotal = 0;
        for ($i = 0; $i < count($article_ids); $i++) {
            if ($article_ids[$i] && $quantities[$i] > 0) {
                $subtotal += $quantities[$i] * $prices[$i];
            }
        }
        
        $taxAmount = $subtotal * (APP_TAX_RATE / 100);
        $total = $subtotal + $taxAmount;
        
        $stmt = $db->prepare("INSERT INTO purchases (purchase_number, partner_id, user_id, purchase_date, expected_date, subtotal, tax_amount, total, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$purchase_number, $partner_id, $_SESSION['user_id'], $purchase_date, $expected_date, $subtotal, $taxAmount, $total, $notes])) {
            $purchaseId = $db->lastInsertId();
            
            $itemStmt = $db->prepare("INSERT INTO purchase_items (purchase_id, article_id, quantity, unit_price, total) VALUES (?, ?, ?, ?, ?)");
            $updateStockStmt = $db->prepare("UPDATE articles SET quantity = quantity + ? WHERE id = ?");
            
            for ($i = 0; $i < count($article_ids); $i++) {
                if ($article_ids[$i] && $quantities[$i] > 0) {
                    $lineTotal = $quantities[$i] * $prices[$i];
                    $itemStmt->execute([$purchaseId, $article_ids[$i], $quantities[$i], $prices[$i], $lineTotal]);
                    $updateStockStmt->execute([$quantities[$i], $article_ids[$i]]);
                }
            }
            
            logActivity('Création achat', 'purchase', $purchaseId);
            setFlashMessage('success', 'Achat créé avec succès. N° ' . $purchase_number);
            redirect('view.php?id=' . $purchaseId);
        } else {
            $error = 'Erreur lors de la création de l\'achat.';
        }
    }
}

$pageTitle = 'Nouvel Achat';
$currentPage = 'purchases';
require_once '../../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-truck-loading"></i> Nouvel Achat</h1>
        <nav class="breadcrumb">
            <a href="../../index.php">Accueil</a>
            <i class="fas fa-chevron-right"></i>
            <a href="index.php">Achats</a>
            <i class="fas fa-chevron-right"></i>
            <span>Nouveau</span>
        </nav>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3 class="card-title">Informations</h3></div>
    <div class="card-body">
        <?php if ($error): ?>
        <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="" id="purchaseForm" data-validate>
            <div class="form-row">
                <div class="form-group">
                    <label for="partner_id">Fournisseur <span class="text-danger">*</span></label>
                    <select id="partner_id" name="partner_id" class="form-control" required>
                        <option value="">-- Sélectionner --</option>
                        <?php foreach ($partners as $p): ?>
                        <option value="<?php echo $p['id']; ?>"><?php echo $p['company_name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="purchase_date">Date d'achat</label>
                    <input type="date" id="purchase_date" name="purchase_date" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="form-group">
                    <label for="expected_date">Date prévue de réception</label>
                    <input type="date" id="expected_date" name="expected_date" class="form-control">
                </div>
            </div>
            
            <h4 style="margin: 30px 0 15px; border-bottom: 1px solid var(--gray-200); padding-bottom: 10px;">
                <i class="fas fa-boxes"></i> Articles
            </h4>
            
            <div class="table-responsive">
                <table class="data-table" id="itemsTable">
                    <thead>
                        <tr>
                            <th>Article</th>
                            <th style="width: 100px;">Qté</th>
                            <th style="width: 150px;">Prix Unitaire</th>
                            <th style="width: 150px;">Total</th>
                            <th style="width: 50px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="calc-row">
                            <td>
                                <select name="article_id[]" class="form-control article-select">
                                    <option value="">-- Sélectionner --</option>
                                    <?php foreach ($articles as $a): ?>
                                    <option value="<?php echo $a['id']; ?>" data-price="<?php echo $a['purchase_price']; ?>"><?php echo $a['name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td><input type="number" name="quantity[]" class="form-control quantity" min="1" value="1" data-quantity></td>
                            <td><input type="number" name="unit_price[]" class="form-control unit-price" step="0.001" min="0" data-price></td>
                            <td><input type="number" class="form-control line-total" readonly data-total></td>
                            <td><button type="button" class="btn btn-sm btn-danger btn-icon remove-row"><i class="fas fa-trash"></i></button></td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr><td colspan="5"><button type="button" class="btn btn-success" id="addRow"><i class="fas fa-plus"></i> Ajouter</button></td></tr>
                    </tfoot>
                </table>
            </div>
            
            <div style="max-width: 400px; margin-left: auto; margin-top: 20px;">
                <div class="form-row" style="margin-bottom: 10px;">
                    <label style="flex: 1;">Sous-total:</label>
                    <input type="number" id="subtotal" class="form-control" style="width: 150px;" readonly value="0">
                </div>
                <div class="form-row" style="margin-bottom: 10px;">
                    <label style="flex: 1;">TVA (<?php echo APP_TAX_RATE; ?>%):</label>
                    <input type="number" id="taxAmount" class="form-control" style="width: 150px;" readonly value="0">
                </div>
                <div class="form-row" style="font-size: 18px; font-weight: bold;">
                    <label style="flex: 1;">TOTAL:</label>
                    <input type="number" id="grandTotal" name="total" class="form-control" style="width: 150px; font-weight: bold;" readonly value="0" data-grand-total>
                </div>
            </div>
            
            <div class="form-group" style="margin-top: 20px;">
                <label for="notes">Notes</label>
                <textarea id="notes" name="notes" class="form-control" rows="3"></textarea>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Enregistrer</button>
                <a href="index.php" class="btn btn-secondary"><i class="fas fa-times"></i> Annuler</a>
            </div>
        </form>
    </div>
</div>

<template id="rowTemplate">
    <tr class="calc-row">
        <td>
            <select name="article_id[]" class="form-control article-select">
                <option value="">-- Sélectionner --</option>
                <?php foreach ($articles as $a): ?>
                <option value="<?php echo $a['id']; ?>" data-price="<?php echo $a['purchase_price']; ?>"><?php echo $a['name']; ?></option>
                <?php endforeach; ?>
            </select>
        </td>
        <td><input type="number" name="quantity[]" class="form-control quantity" min="1" value="1" data-quantity></td>
        <td><input type="number" name="unit_price[]" class="form-control unit-price" step="0.001" min="0" data-price></td>
        <td><input type="number" class="form-control line-total" readonly data-total></td>
        <td><button type="button" class="btn btn-sm btn-danger btn-icon remove-row"><i class="fas fa-trash"></i></button></td>
    </tr>
</template>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const itemsTable = document.getElementById('itemsTable');
    const addRowBtn = document.getElementById('addRow');
    const rowTemplate = document.getElementById('rowTemplate');
    
    addRowBtn.addEventListener('click', function() {
        const tbody = itemsTable.querySelector('tbody');
        const clone = rowTemplate.content.cloneNode(true);
        tbody.appendChild(clone);
        initRowEvents();
        calculateTotals();
    });
    
    function initRowEvents() {
        document.querySelectorAll('.remove-row').forEach(btn => {
            btn.removeEventListener('click', removeRow);
            btn.addEventListener('click', removeRow);
        });
        document.querySelectorAll('.article-select').forEach(select => {
            select.removeEventListener('change', updatePrice);
            select.addEventListener('change', updatePrice);
        });
        document.querySelectorAll('.quantity, .unit-price').forEach(input => {
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
        row.querySelector('.unit-price').value = selectedOption.dataset.price || 0;
        calculateTotals();
    }
    
    function calculateTotals() {
        let subtotal = 0;
        document.querySelectorAll('.calc-row').forEach(row => {
            const qty = parseFloat(row.querySelector('.quantity').value) || 0;
            const price = parseFloat(row.querySelector('.unit-price').value) || 0;
            const lineTotal = qty * price;
            row.querySelector('.line-total').value = lineTotal.toFixed(3);
            subtotal += lineTotal;
        });
        
        const taxRate = <?php echo APP_TAX_RATE; ?>;
        const taxAmount = subtotal * (taxRate / 100);
        const grandTotal = subtotal + taxAmount;
        
        document.getElementById('subtotal').value = subtotal.toFixed(3);
        document.getElementById('taxAmount').value = taxAmount.toFixed(3);
        document.getElementById('grandTotal').value = grandTotal.toFixed(3);
    }
    
    initRowEvents();
    calculateTotals();
});
</script>

<?php require_once '../../includes/footer.php'; ?>
