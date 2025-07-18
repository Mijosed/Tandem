// Test simple pour vérifier l'API des candidatures
async function testCandidatureAPI() {
    try {
        // Test de vérification des candidatures multiples
        const jobIds = ['195MJXW', '195MJYH', 'inexistant123'];
        
        const response = await fetch('http://localhost:8888/api/candidatures/check-multiple', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ jobIds })
        });
        
        console.log('Status:', response.status);
        
        if (response.ok) {
            const results = await response.json();
            console.log('Résultats candidatures:', results);
        } else {
            const error = await response.text();
            console.log('Erreur:', error);
        }
    } catch (err) {
        console.error('Erreur réseau:', err);
    }
}

testCandidatureAPI();
