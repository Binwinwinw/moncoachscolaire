/**
 * Debug - Test Maintenance Toggle
 * À exécuter dans la console du navigateur (F12) sur le dashboard admin
 * 
 * Copier-coller dans la console:
 * fetch('/api/admin/maintenance.php').then(r => r.json()).then(d => console.log('GET Maintenance:', d))
 * 
 * Pour activer le mode maintenance:
 * fetch('/api/admin/maintenance.php', { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({enabled: true, message: 'Test'}) }).then(r => r.json()).then(d => console.log('POST Result:', d))
 * 
 * Pour désactiver:
 * fetch('/api/admin/maintenance.php', { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({enabled: false, message: 'Test'}) }).then(r => r.json()).then(d => console.log('POST Result:', d))
 */

// Script de debug pour tester l'API maintenance depuis la console
window.testMaintenanceAPI = {
    // Récupérer l'état
    getStatus: async function() {
        const response = await fetch('/api/admin/maintenance.php', {
            method: 'GET',
            credentials: 'same-origin'
        });
        const data = await response.json();
        console.log('✅ État actuel maintenance:', data);
        return data;
    },
    
    // Activer la maintenance
    enable: async function() {
        const response = await fetch('/api/admin/maintenance.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                enabled: true,
                message: 'L\'application est actuellement en maintenance. Nous serons de retour très bientôt !'
            })
        });
        const data = await response.json();
        console.log('✅ Maintenance ACTIVÉE:', data);
        return data;
    },
    
    // Désactiver la maintenance
    disable: async function() {
        const response = await fetch('/api/admin/maintenance.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                enabled: false,
                message: 'L\'application est maintenant disponible'
            })
        });
        const data = await response.json();
        console.log('✅ Maintenance DÉSACTIVÉE:', data);
        return data;
    },
    
    // Tester le toggle
    test: async function() {
        console.log('🔄 Test de l\'API maintenance...');
        console.log('1. État actuel:');
        await this.getStatus();
        
        console.log('\n2. Activation:');
        await this.enable();
        
        console.log('\n3. Récupération pour vérifier:');
        await this.getStatus();
        
        console.log('\n4. Désactivation:');
        await this.disable();
        
        console.log('\n5. Récupération finale:');
        await this.getStatus();
    }
};

console.log('✅ Debug Maintenance API chargé');
console.log('Utiliser: window.testMaintenanceAPI.getStatus() - voir l\'état');
console.log('Utiliser: window.testMaintenanceAPI.enable() - activer');
console.log('Utiliser: window.testMaintenanceAPI.disable() - désactiver');
console.log('Utiliser: window.testMaintenanceAPI.test() - test complet');
