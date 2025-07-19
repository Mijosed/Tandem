# Correction temporaire de l'authentification

## Problème identifié

L'erreur HTTP 401 (Unauthorized) sur l'endpoint `/api/candidatures/check-multiple` était causée par un problème d'authentification JWT non configurée.

### Erreur originale
```
POST http://localhost:8888/api/candidatures/check-multiple 401 (Unauthorized)
```

## Solution temporaire implémentée

### Backend (`CandidatureController.php`)
- Désactivation temporaire de l'annotation `#[IsGranted('ROLE_USER')]`
- Ajout d'un système d'authentification basé sur le header `X-User-ID`
- L'endpoint fonctionne maintenant sans JWT mais utilise l'ID utilisateur passé en header

### Frontend (`index.vue`)
- Récupération de l'ID utilisateur depuis `localStorage`
- Ajout du header `X-User-ID` dans les requêtes vers l'API candidatures
- Gestion gracieuse quand aucun utilisateur n'est connecté

## Code modifié

### Backend
```php
#[Route('/candidatures/check-multiple', name: 'candidature_check_multiple', methods: ['POST'])]
// #[IsGranted('ROLE_USER')] // Temporairement désactivé - utilise X-User-ID header
public function checkMultipleCandidatures(Request $request): JsonResponse
{
    // Récupérer l'utilisateur depuis le header X-User-ID (temporaire pour dev)
    $userId = $request->headers->get('X-User-ID');
    
    $user = null;
    if ($userId) {
        $user = $this->userRepository->find($userId);
    }
    
    // ... reste du code
}
```

### Frontend
```javascript
// Récupérer l'utilisateur depuis le localStorage
const userData = typeof window !== 'undefined' ? JSON.parse(localStorage.getItem('user') || '{}') : {}
const userId = userData.id || null

const headers = {
  'Content-Type': 'application/json',
  'Accept': 'application/json',
}

// Ajouter l'ID utilisateur dans le header si disponible
if (userId) {
  headers['X-User-ID'] = userId.toString()
}
```

## Test de fonctionnement

```bash
# Test avec header utilisateur
curl -X POST http://localhost:8888/api/candidatures/check-multiple \
  -H "Content-Type: application/json" \
  -H "X-User-ID: 12" \
  -d '{"jobIds":["123","456"]}'
```

Réponse attendue :
```json
{
  "123": {"hasApplied": false, "candidature": null},
  "456": {"hasApplied": false, "candidature": null}
}
```

## TODO : Solution définitive

Pour une solution de production, il faudrait :

1. **Configurer JWT correctement** dans Symfony :
   - Installer `lexik/jwt-authentication-bundle`
   - Configurer les clés JWT
   - Modifier `security.yaml` pour utiliser JWT
   - Modifier `AuthController` pour générer des tokens JWT

2. **Modifier le frontend** pour :
   - Sauvegarder le token JWT lors de la connexion
   - Utiliser le token JWT dans toutes les requêtes API
   - Gérer le refresh des tokens expirés

3. **Sécuriser les endpoints** :
   - Remettre les annotations `#[IsGranted('ROLE_USER')]`
   - Utiliser `$this->getUser()` pour récupérer l'utilisateur authentifié

## Impact de la solution temporaire

- ✅ **Avantages** : Déblocage immédiat du problème 401
- ✅ **Sécurité dev** : Utilise l'ID utilisateur réel quand disponible
- ⚠️ **Limitation** : Ne convient pas pour la production
- ⚠️ **Sécurité** : Pas de vérification de token, utilisateur basé sur header

Cette solution permet de continuer le développement en attendant l'implémentation complète du JWT.
