<?php 
// Calculate base path if not already set
if (!isset($basePath)) {
    $currentDir = dirname($_SERVER['SCRIPT_NAME']);
    $basePath = '';
    if (strpos($currentDir, '/modules/') !== false) {
        $basePath = '../../';
    } elseif (strpos($currentDir, '/modules') !== false) {
        $basePath = '../';
    }
}
?>
<?php if (isLoggedIn()): ?>
        </main>
        
        <!-- Footer -->
        <footer class="main-footer">
            <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?> - Tous droits réservés</p>
            <p>Version <?php echo APP_VERSION; ?></p>
        </footer>
    </div>
<?php endif; ?>

    <script src="<?php echo $basePath; ?>assets/js/main.js"></script>
    <?php if (isset($extraScripts)): ?>
        <?php echo $extraScripts; ?>
    <?php endif; ?>
</body>
</html>
