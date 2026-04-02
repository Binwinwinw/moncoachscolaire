/**
 * TEST: Vérifier que la détection du niveau fonctionne correctement
 */

console.log('🧪 TEST: Détection du niveau du système d\'exercices dynamique');

// Simuler les URLs de chaque page
const testCases = [
    { url: 'http://localhost/moncoachscolaire/college/6eme/exercices', expected: '6ème', expected_container: '6ème' },
    { url: 'http://localhost/moncoachscolaire/college/5eme/exercices', expected: '5ème', expected_container: '5ème' },
    { url: 'http://localhost/moncoachscolaire/college/4eme/exercices', expected: '4ème', expected_container: '4ème' },
    { url: 'http://localhost/moncoachscolaire/college/3eme/exercices', expected: '3ème', expected_container: '3ème' },
    { url: 'http://localhost/moncoachscolaire/lycee/seconde/exercices', expected: 'Seconde', expected_container: 'Seconde' },
    { url: 'http://localhost/moncoachscolaire/lycee/premiere/exercices', expected: 'Première', expected_container: 'Première' },
    { url: 'http://localhost/moncoachscolaire/lycee/terminale/exercices', expected: 'Terminale', expected_container: 'Terminale' },
];

testCases.forEach(tc => {
    // Simuler detectLevel()
    const url = tc.url;
    const levelPatterns = {
        '6ème': /6[èe]me|6eme/i,
        '5ème': /5[èe]me|5eme/i,
        '4ème': /4[èe]me|4eme/i,
        '3ème': /3[èe]me|3eme/i,
        'Seconde': /seconde|2nde/i,
        'Première': /premi[èe]re|1[èe]re/i,
        'Terminale': /terminale/i,
        'BAC': /bac/i
    };
    
    let detected = null;
    for (const [level, pattern] of Object.entries(levelPatterns)) {
        if (pattern.test(url)) {
            detected = level;
            break;
        }
    }
    
    const status = detected === tc.expected ? '✅' : '❌';
    console.log(`${status} URL: ${tc.url}`);
    console.log(`   Détecté: ${detected}, Attendu: ${tc.expected}`);
    console.log(`   data-level du container: ${tc.expected_container}`);
});
