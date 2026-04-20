<?php
require_once '../../includes/config.php';
requireLogin();

$db = getDB();

$id = intval($_GET['id'] ?? 0);
if (!$id) redirect('index.php');

// Récupérer la commande avec le client
$stmt = $db->prepare("SELECT o.*, c.company_name, c.contact_name, c.email, c.phone, c.address, c.city, c.tax_number 
    FROM orders o 
    LEFT JOIN customers c ON o.customer_id = c.id 
    WHERE o.id = ?");
$stmt->execute([$id]);
$order = $stmt->fetch();

if (!$order) {
    setFlashMessage('error', 'Commande non trouvée.');
    redirect('index.php');
}

// Récupérer les articles
$items = $db->prepare("SELECT oi.*, a.code, a.name, a.unit 
    FROM order_items oi 
    LEFT JOIN articles a ON oi.article_id = a.id 
    WHERE oi.order_id = ?");
$items->execute([$id]);
$items = $items->fetchAll();

// Valeurs par défaut pour éviter les warnings
$order['tax_rate'] = $order['tax_rate'] ?? 0;
$order['discount'] = $order['discount'] ?? 0;
$order['paid_amount'] = $order['paid_amount'] ?? 0;
$order['subtotal'] = $order['subtotal'] ?? 0;
$order['tax_amount'] = $order['tax_amount'] ?? 0;

/**
 * Convertit un nombre en toutes lettres (français) – dinars/millimes
 */
function convertNumberToFrenchWords($number, $currency_unit = 'dinar', $subunit = 'millime') {
    if (!is_numeric($number)) return 'zéro ' . $currency_unit;
    
    $parts = explode('.', number_format($number, 2, '.', ''));
    $integer_part = intval($parts[0]);
    $decimal_part = intval($parts[1]);
    
    $units = ['', 'un', 'deux', 'trois', 'quatre', 'cinq', 'six', 'sept', 'huit', 'neuf', 'dix', 'onze', 'douze', 'treize', 'quatorze', 'quinze', 'seize', 'dix-sept', 'dix-huit', 'dix-neuf'];
    $tens = ['', 'dix', 'vingt', 'trente', 'quarante', 'cinquante', 'soixante', 'soixante-dix', 'quatre-vingt', 'quatre-vingt-dix'];
    
    function convertHundreds($n, $units, $tens) {
        $hundred = floor($n / 100);
        $rest = $n % 100;
        $result = '';
        if ($hundred > 0) {
            if ($hundred == 1) $result .= 'cent ';
            else $result .= $units[$hundred] . ' cents ';
        }
        if ($rest > 0) {
            if ($rest < 20) $result .= $units[$rest];
            else {
                $ten = floor($rest / 10);
                $unit = $rest % 10;
                if ($ten == 7 || $ten == 9) {
                    $result .= $tens[$ten - 1] . '-' . $units[$unit + 10];
                } else {
                    $result .= $tens[$ten];
                    if ($unit > 0) $result .= '-' . $units[$unit];
                }
            }
        }
        return trim($result);
    }
    
    function convertBlock($n, $units, $tens, $scale) {
        if ($n == 0) return '';
        $result = convertHundreds($n, $units, $tens);
        if ($scale) {
            if ($n == 1) $result .= ' ' . rtrim($scale, 's');
            else $result .= ' ' . $scale;
        }
        return $result;
    }
    
    $words = '';
    
    $billions = floor($integer_part / 1000000000);
    $integer_part %= 1000000000;
    if ($billions > 0) {
        $words .= convertBlock($billions, $units, $tens, 'milliard') . ' ';
    }
    
    $millions = floor($integer_part / 1000000);
    $integer_part %= 1000000;
    if ($millions > 0) {
        $words .= convertBlock($millions, $units, $tens, 'million') . ' ';
    }
    
    $thousands = floor($integer_part / 1000);
    $integer_part %= 1000;
    if ($thousands > 0) {
        $thousands_word = convertHundreds($thousands, $units, $tens);
        if ($thousands == 1) $words .= 'mille ';
        else $words .= $thousands_word . ' mille ';
    }
    
    if ($integer_part > 0) {
        $words .= convertHundreds($integer_part, $units, $tens);
    } elseif ($thousands == 0 && $millions == 0 && $billions == 0) {
        $words = 'zéro';
    }
    
    $words = trim($words);
    $words = str_replace('cent  ', 'cent ', $words);
    $words = str_replace('  ', ' ', $words);
    
    if (empty($words)) $words = 'zéro';
    if ($words == 'un') $words .= ' ' . $currency_unit;
    else $words .= ' ' . $currency_unit . 's';
    
    if ($decimal_part > 0) {
        $sub_words = '';
        if ($decimal_part < 20) $sub_words = $units[$decimal_part];
        else {
            $ten = floor($decimal_part / 10);
            $unit = $decimal_part % 10;
            if ($ten == 7 || $ten == 9) {
                $sub_words = $tens[$ten - 1] . '-' . $units[$unit + 10];
            } else {
                $sub_words = $tens[$ten];
                if ($unit > 0) $sub_words .= '-' . $units[$unit];
            }
        }
        if ($decimal_part == 1) $sub_words .= ' ' . $subunit;
        else $sub_words .= ' ' . $subunit . 's';
        $words .= ' et ' . $sub_words;
    }
    
    return ucfirst($words);
}

$pageTitle = 'Facture #' . $order['order_number'];
$currentPage = 'orders';
require_once '../../includes/header.php';
?>

<style>
    .invoice-container {
        max-width: 1100px;
        margin: 0 auto;
        background: white;
        box-shadow: 0 0 20px rgba(0,0,0,0.1);
        padding: 30px;
    }
    .invoice-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 2px solid var(--primary-color);
        flex-wrap: wrap;
        gap: 20px;
    }
    .company-logo img {
        height: 120px;
        width: auto;
    }
    .company-info {
        flex: 1;
        margin-left: 20px;
    }
    .company-info h2 {
        color: var(--primary-color);
        margin-bottom: 5px;
    }
    .company-info p {
        margin: 3px 0;
        font-size: 13px;
    }
    .invoice-title {
        text-align: right;
    }
    .invoice-title h1 {
        color: var(--primary-color);
        font-size: 28px;
        margin-bottom: 10px;
    }
    .client-info {
        background: var(--gray-100);
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 30px;
    }
    .montant-fixe {
        background: var(--gray-100);
        padding: 12px;
        border-radius: 5px;
        margin-top: 20px;
        font-weight: 500;
        text-align: center;
    }
    @media print {
        /* Masquer les éléments de navigation */
        .top-header, .sidebar, .sidebar-overlay, .btn, .page-header, .main-footer {
            display: none !important;
        }
        
        /* Supprimer les marges et en-têtes/pieds de page du navigateur */
        @page {
            margin: 0;
            size: auto;
        }
        
        /* Supprimer les marges du body et wrapper */
        body {
            margin: 0;
            padding: 0;
            background: white;
        }
        
        .main-wrapper {
            margin: 0;
            padding: 0;
        }
        
        .invoice-container {
            box-shadow: none;
            padding: 15mm;
            margin: 0;
        }
        
        /* Éviter les coupures de page intempestives */
        .invoice-container, .client-info, .data-table, .montant-fixe {
            page-break-inside: avoid;
        }
    }
    .btn-print {
        margin-bottom: 20px;
    }
    .data-table th, .data-table td {
        padding: 10px;
        vertical-align: top;
    }
    .total-row {
        font-weight: bold;
        background: var(--primary-color);
        color: white;
    }
</style>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-file-invoice"></i> Facture</h1>
        <nav class="breadcrumb">
            <a href="../../index.php">Accueil</a>
            <i class="fas fa-chevron-right"></i>
            <a href="index.php">Commandes</a>
            <i class="fas fa-chevron-right"></i>
            <span>Facture #<?php echo $order['order_number']; ?></span>
        </nav>
    </div>
    <div class="quick-actions">
        <button class="btn btn-info btn-print" onclick="window.print()">
            <i class="fas fa-print"></i> Imprimer
        </button>
        <a href="index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
    </div>
</div>

<div class="invoice-container">
    <!-- En-tête société avec logo -->
    <div class="invoice-header">
        <div class="company-logo">
            <img src="<?php echo $basePath; ?>assets/images/logo1.png" alt="Logo Dahrouj Import Textile">
        </div>
        <div class="company-info">
            <h2>DAHROUJ IMPORT TEXTILE</h2>
            <p>SUARL</p>
            <p>Rue Mahmoud El Matri, Sidi Ameur - 5061 Monastir, Tunisie</p>
            <p>📞 28 853 280 | ✉ attig.mohtadi@icloud.com</p>
            <p>Matricule fiscal/RNE : 196721R</p>
        </div>
        <div class="invoice-title">
            <h1>FACTURE</h1>
            <p>N° : <?php echo $order['order_number']; ?></p>
            <p>Date : <?php echo formatDate($order['order_date']); ?></p>
            <?php if ($order['delivery_date']): ?>
            <p>Livraison prévue : <?php echo formatDate($order['delivery_date']); ?></p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Informations client -->
    <div class="client-info">
        <strong>CLIENT :</strong><br>
        <?php echo $order['company_name'] ?: $order['contact_name']; ?><br>
        <?php if ($order['address']): ?>
        Adresse : <?php echo $order['address']; ?>, <?php echo $order['city']; ?><br>
        <?php endif; ?>
        <?php if ($order['email']): ?>Email : <?php echo $order['email']; ?><br><?php endif; ?>
        Tél : <?php echo $order['phone'] ?: '-'; ?><br>
        Matricule fiscal : <?php echo $order['tax_number'] ?: '-'; ?>
    </div>

    <!-- Tableau des articles -->
    <div class="table-responsive">
        <table class="data-table" style="width:100%">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Désignation</th>
                    <th>Quantité</th>
                    <th>Prix unitaire (HT)</th>
                    <th>Remise</th>
                    <th>Total HT</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['code']); ?></td>
                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                    <td><?php echo $item['quantity'] . ' ' . $item['unit']; ?></td>
                    <td><?php echo formatMoney($item['unit_price']); ?></td>
                    <td><?php echo formatMoney($item['discount']); ?></td>
                    <td><?php echo formatMoney($item['total']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" style="text-align:right">Sous-total HT :</td>
                    <td><?php echo formatMoney($order['subtotal']); ?></td>
                </tr>
                <tr>
                    <td colspan="5" style="text-align:right">Remise globale :</td>
                    <td><?php echo formatMoney($order['discount']); ?></td>
                </tr>
                <tr>
                    <td colspan="5" style="text-align:right">TVA (<?php echo $order['tax_rate']; ?>%) :</td>
                    <td><?php echo formatMoney($order['tax_amount']); ?></td>
                </tr>
                <tr class="total-row">
                    <td colspan="5" style="text-align:right"><strong>TOTAL TTC :</strong></td>
                    <td><strong><?php echo formatMoney($order['total']); ?></strong></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Signature Service commercial alignée à droite -->
    <div style="margin: 30px 0 20px 0; display: flex; justify-content: flex-end;">
        <div style="text-align: center; width: 250px;">
            <div style="border-top: 1px solid var(--gray-400); padding-top: 8px; margin-top: 20px;">
                Service commercial
            </div>
            <div style="height: 40px;"></div>
        </div>
    </div>

    <!-- Montant en toutes lettres dynamique -->
    <div class="montant-fixe">
        <strong>Arrêtée la présente facture à la somme de : <?php echo convertNumberToFrenchWords($order['total'], 'dinar', 'millime'); ?></strong>
    </div>

    <!-- Notes si existantes -->
    <?php if ($order['notes']): ?>
    <div style="margin-top: 20px;">
        <strong>Notes :</strong>
        <p><?php echo nl2br(htmlspecialchars($order['notes'])); ?></p>
    </div>
    <?php endif; ?>
</div>

<?php require_once '../../includes/footer.php'; ?>