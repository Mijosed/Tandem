# Documentation Tracking Matomo

## Vue d'ensemble

Le système de tracking Matomo a été intégré pour suivre les interactions des utilisateurs sur la page d'accueil de Tandem. Il permet de tracker les clics sur les boutons, la navigation et les actions CTA.

## Composable `useMatomo`

Le composable `useMatomo` fournit plusieurs fonctions pour tracker différents types d'événements :

### Fonctions disponibles

#### `trackEvent(category, action, name?, value?)`
Fonction générique pour tracker un événement personnalisé.
- `category`: Catégorie de l'événement (ex: 'Navigation', 'CTA', 'Form')
- `action`: Action effectuée (ex: 'Click', 'Submit', 'View')
- `name`: Nom de l'élément (optionnel)
- `value`: Valeur numérique (optionnel)

#### `trackButtonClick(buttonName, location)`
Pour tracker les clics sur les boutons.
- `buttonName`: Nom du bouton
- `location`: Localisation du bouton (ex: 'Header', 'Hero', 'Footer')

#### `trackNavigation(destination, source)`
Pour tracker la navigation entre les pages.
- `destination`: Page de destination
- `source`: Source de la navigation

#### `trackCTA(ctaName, location)`
Pour tracker les actions CTA (Call To Action).
- `ctaName`: Nom du CTA
- `location`: Localisation du CTA

## Événements trackés actuellement

### Page d'accueil (`/`)

#### Header (AppHeader.vue)
- **Navigation**: 
  - "À propos" → `trackNavigation('About', 'Header')`
  - "Blog" → `trackNavigation('Blog', 'Header')`
  - "Contact" → `trackNavigation('Contact', 'Header')`
  - "Dashboard" → `trackNavigation('Dashboard', 'Header')`

- **Boutons d'authentification**:
  - "Connexion" → `trackButtonClick('Login', 'Header')`
  - "Inscription" → `trackButtonClick('Register', 'Header')`

#### Section Hero (HeroSection.vue)
- **CTA principaux**:
  - "Commencer gratuitement" → `trackCTA('Start Free', 'Hero')`
  - "Se connecter" → `trackCTA('Login', 'Hero')`
  - "Accéder au dashboard" → `trackCTA('Access Dashboard', 'Hero')`

#### Section Pricing (PricingSection.vue)
- **Plans tarifaires**:
  - "Commencer" (plan gratuit) → `trackCTA('Free Plan', 'Pricing')`
  - "S'abonner" (plan premium) → `trackCTA('Premium Plan', 'Pricing')`

#### Section Join (JoinSection.vue)
- **Boutons d'inscription/connexion**:
  - "Créer un compte" → `trackCTA('Create Account', 'Join')`
  - "Se connecter" → `trackCTA('Login Existing', 'Join')`

#### Footer (FooterSection.vue)
- **Liens légaux**:
  - "Mentions légales" → `trackNavigation('Legal Terms', 'Footer')`
  - "Confidentialité" → `trackNavigation('Privacy Policy', 'Footer')`

## Configuration technique

### Plugin Matomo (`plugins/matomo.client.ts`)
Initialise Matomo côté client et configure les paramètres par défaut.

### Middleware global (`middleware/matomo.global.ts`)
Track automatiquement les changements de page.

### Composant de debug (`MatomoDebug.vue`)
Disponible en mode développement pour tester le tracking.

## Utilisation dans un nouveau composant

```vue
<template>
  <button @click="handleClick">Mon bouton</button>
</template>

<script setup>
import { useMatomo } from '@/composables/useMatomo'

const { trackButtonClick } = useMatomo()

const handleClick = () => {
  trackButtonClick('Mon bouton', 'Ma Section')
  // Votre logique métier ici
}
</script>
```

## Configuration côté Matomo

Les événements sont envoyés avec cette structure :
- **Catégorie**: Type d'événement (Button Click, Navigation, CTA)
- **Action**: Action effectuée (généralement "Click")
- **Nom**: Description détaillée avec localisation

### Exemples d'événements dans Matomo
```
Catégorie: "Button Click" | Action: "Click" | Nom: "Login - Header"
Catégorie: "CTA" | Action: "Click" | Nom: "Start Free - Hero"
Catégorie: "Navigation" | Action: "Click" | Nom: "Header to About"
```

## Debug et test

En mode développement, un bouton de debug apparaît en bas à droite permettant de :
- Tester l'envoi d'événements
- Afficher la queue Matomo dans la console
- Voir le dernier événement envoyé

## Notes importantes

1. Le tracking fonctionne uniquement côté client (navigateur)
2. Les événements sont ajoutés à la queue Matomo même si le script n'est pas encore chargé
3. En cas d'absence de Matomo, les fonctions ne génèrent pas d'erreur
4. Le middleware global track automatiquement les changements de page

## Prochaines étapes

Pour étendre le tracking à d'autres pages :
1. Importer `useMatomo` dans le composant
2. Ajouter les handlers d'événements appropriés
3. Utiliser la nomenclature établie pour la cohérence
