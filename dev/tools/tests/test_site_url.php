<?php
// Test ce que retourne site_url()
require_once __DIR__ . '/../src/config/config.php';
require_once __DIR__ . '/../src/config/site_boot.php';

echo "<h1>Test site_url()</h1>";
echo "<pre>";
echo "site_url('login') = " . site_url('login') . "\n";
echo "site_url('register') = " . site_url('register') . "\n";
echo "site_url('dashboard_admin') = " . site_url('dashboard_admin') . "\n";
echo "</pre>";

echo "<h2>Formulaire de test</h2>";
?>
<form method="POST" action="<?php echo site_url('login'); ?>">
    <input type="text" name="username" value="test">
    <input type="password" name="password" value="test123">
    <input type="hidden" name="csrf_token" value="abc">
    <button type="submit">Submit</button>
</form>

<p>Cliquez sur Submit et regardez ce qui se passe dans la barre d'adresse.</p>
<?php
