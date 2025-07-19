<template>
  <div class="container max-w-4xl mx-auto p-6 space-y-8">
    <div class="text-center space-y-4">
      <h1 class="text-3xl font-bold">Guide des filtres de recherche</h1>
      <p class="text-lg text-muted-foreground">
        Apprenez à utiliser efficacement la recherche d'offres France Travail
      </p>
    </div>

    <!-- Vue d'ensemble -->
    <Card>
      <CardHeader>
        <CardTitle class="flex items-center gap-2">
          <Info class="h-5 w-5" />
          Vue d'ensemble
        </CardTitle>
      </CardHeader>
      <CardContent class="space-y-4">
        <p>
          Notre interface de recherche vous permet d'explorer les offres d'emploi officielles 
          de France Travail (ex-Pôle Emploi) avec des critères précis.
        </p>
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
          <div class="flex items-center gap-2 mb-2">
            <Target class="h-4 w-4 text-blue-600" />
            <span class="font-semibold text-blue-800">Conseil</span>
          </div>
          <p class="text-blue-700 text-sm">
            Combinez plusieurs filtres pour affiner votre recherche et trouver 
            les offres qui correspondent exactement à vos critères.
          </p>
        </div>
      </CardContent>
    </Card>

    <!-- Guide des filtres -->
    <div class="grid gap-6 md:grid-cols-2">
      <!-- Mots-clés -->
      <Card>
        <CardHeader>
          <CardTitle class="text-lg">🔍 Mots-clés</CardTitle>
        </CardHeader>
        <CardContent class="space-y-3">
          <p class="text-sm text-muted-foreground">
            Recherchez par métier, compétence ou secteur d'activité.
          </p>
          
          <div class="space-y-2">
            <h4 class="font-medium text-sm">Exemples :</h4>
            <div class="flex flex-wrap gap-2">
              <Badge variant="secondary">développeur web</Badge>
              <Badge variant="secondary">commercial</Badge>
              <Badge variant="secondary">infirmier</Badge>
              <Badge variant="secondary">comptable</Badge>
            </div>
          </div>

          <div class="bg-yellow-50 border border-yellow-200 rounded p-3">
            <div class="flex items-center gap-2 mb-1">
              <Lightbulb class="h-4 w-4 text-yellow-600" />
              <span class="font-medium text-yellow-800 text-sm">Astuce</span>
            </div>
            <p class="text-yellow-700 text-xs">
              Utilisez des virgules pour séparer plusieurs mots-clés : 
              "développeur, programmeur, web"
            </p>
          </div>
        </CardContent>
      </Card>

      <!-- Localisation -->
      <Card>
        <CardHeader>
          <CardTitle class="text-lg">📍 Localisation</CardTitle>
        </CardHeader>
        <CardContent class="space-y-3">
          <p class="text-sm text-muted-foreground">
            Spécifiez une commune par son code INSEE.
          </p>
          
          <div class="space-y-2">
            <h4 class="font-medium text-sm">Exemples de codes :</h4>
            <div class="grid grid-cols-2 gap-2 text-xs">
              <div><code class="bg-gray-100 px-1 rounded">75001</code> Paris 1er</div>
              <div><code class="bg-gray-100 px-1 rounded">69001</code> Lyon 1er</div>
              <div><code class="bg-gray-100 px-1 rounded">13001</code> Marseille 1er</div>
              <div><code class="bg-gray-100 px-1 rounded">33063</code> Bordeaux</div>
            </div>
          </div>

          <div class="bg-blue-50 border border-blue-200 rounded p-3">
            <div class="flex items-center gap-2 mb-1">
              <MapPin class="h-4 w-4 text-blue-600" />
              <span class="font-medium text-blue-800 text-sm">Info</span>
            </div>
            <p class="text-blue-700 text-xs">
              Trouvez les codes INSEE sur le site officiel de l'INSEE ou 
              des services publics français.
            </p>
          </div>
        </CardContent>
      </Card>

      <!-- Type de contrat -->
      <Card>
        <CardHeader>
          <CardTitle class="text-lg">📄 Type de contrat</CardTitle>
        </CardHeader>
        <CardContent class="space-y-3">
          <p class="text-sm text-muted-foreground">
            Filtrez par type de contrat de travail.
          </p>
          
          <div class="space-y-2">
            <h4 class="font-medium text-sm">Types disponibles :</h4>
            <div class="space-y-1 text-xs">
              <div class="flex justify-between">
                <span>CDI</span>
                <span class="text-muted-foreground">Contrat à durée indéterminée</span>
              </div>
              <div class="flex justify-between">
                <span>CDD</span>
                <span class="text-muted-foreground">Contrat à durée déterminée</span>
              </div>
              <div class="flex justify-between">
                <span>MIS</span>
                <span class="text-muted-foreground">Intérim</span>
              </div>
              <div class="flex justify-between">
                <span>SAI</span>
                <span class="text-muted-foreground">Saisonnier</span>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Expérience -->
      <Card>
        <CardHeader>
          <CardTitle class="text-lg">🎓 Expérience</CardTitle>
        </CardHeader>
        <CardContent class="space-y-3">
          <p class="text-sm text-muted-foreground">
            Filtrez selon le niveau d'expérience requis.
          </p>
          
          <div class="space-y-2">
            <h4 class="font-medium text-sm">Niveaux :</h4>
            <div class="space-y-1">
              <Badge variant="outline">Débutant accepté</Badge>
              <Badge variant="outline">1 à 3 ans d'expérience</Badge>
              <Badge variant="outline">Plus de 3 ans d'expérience</Badge>
            </div>
          </div>

          <div class="bg-green-50 border border-green-200 rounded p-3">
            <div class="flex items-center gap-2 mb-1">
              <GraduationCap class="h-4 w-4 text-green-600" />
              <span class="font-medium text-green-800 text-sm">Conseil</span>
            </div>
            <p class="text-green-700 text-xs">
              Si vous êtes débutant, recherchez spécifiquement les offres 
              "Débutant accepté" pour maximiser vos chances.
            </p>
          </div>
        </CardContent>
      </Card>

      <!-- Secteur d'activité -->
      <Card>
        <CardHeader>
          <CardTitle class="text-lg">🏭 Secteur d'activité</CardTitle>
        </CardHeader>
        <CardContent class="space-y-3">
          <p class="text-sm text-muted-foreground">
            Recherchez dans un secteur spécifique.
          </p>
          
          <div class="space-y-2">
            <h4 class="font-medium text-sm">Exemples populaires :</h4>
            <div class="flex flex-wrap gap-2">
              <Badge variant="secondary">Informatique</Badge>
              <Badge variant="secondary">Commerce</Badge>
              <Badge variant="secondary">Santé</Badge>
              <Badge variant="secondary">Éducation</Badge>
            </div>
          </div>

          <div class="bg-purple-50 border border-purple-200 rounded p-3">
            <div class="flex items-center gap-2 mb-1">
              <Building class="h-4 w-4 text-purple-600" />
              <span class="font-medium text-purple-800 text-sm">Note</span>
            </div>
            <p class="text-purple-700 text-xs">
              La liste des secteurs est chargée automatiquement depuis 
              l'API France Travail et mise à jour régulièrement.
            </p>
          </div>
        </CardContent>
      </Card>

      <!-- Pagination -->
      <Card>
        <CardHeader>
          <CardTitle class="text-lg">📄 Résultats par page</CardTitle>
        </CardHeader>
        <CardContent class="space-y-3">
          <p class="text-sm text-muted-foreground">
            Contrôlez le nombre de résultats affichés.
          </p>
          
          <div class="space-y-2">
            <h4 class="font-medium text-sm">Options :</h4>
            <div class="flex gap-2">
              <Badge variant="outline">10 résultats</Badge>
              <Badge variant="outline">20 résultats</Badge>
              <Badge variant="outline">50 résultats</Badge>
            </div>
          </div>

          <div class="bg-orange-50 border border-orange-200 rounded p-3">
            <div class="flex items-center gap-2 mb-1">
              <Zap class="h-4 w-4 text-orange-600" />
              <span class="font-medium text-orange-800 text-sm">Performance</span>
            </div>
            <p class="text-orange-700 text-xs">
              20 résultats par page est le réglage optimal pour un bon 
              équilibre entre vitesse et exhaustivité.
            </p>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Conseils d'utilisation -->
    <Card>
      <CardHeader>
        <CardTitle class="flex items-center gap-2">
          <BookOpen class="h-5 w-5" />
          Conseils d'utilisation
        </CardTitle>
      </CardHeader>
      <CardContent class="space-y-4">
        <div class="grid gap-4 md:grid-cols-2">
          <div class="space-y-2">
            <h4 class="font-medium flex items-center gap-2">
              <CheckCircle class="h-4 w-4 text-green-600" />
              Bonnes pratiques
            </h4>
            <ul class="text-sm space-y-1 text-muted-foreground">
              <li>• Commencez par une recherche large puis affinez</li>
              <li>• Utilisez des mots-clés spécifiques à votre domaine</li>
              <li>• Combinez localisation et type de contrat</li>
              <li>• Explorez différents secteurs d'activité</li>
            </ul>
          </div>
          
          <div class="space-y-2">
            <h4 class="font-medium flex items-center gap-2">
              <AlertTriangle class="h-4 w-4 text-yellow-600" />
              À éviter
            </h4>
            <ul class="text-sm space-y-1 text-muted-foreground">
              <li>• Trop de filtres en même temps au début</li>
              <li>• Codes de commune incorrects</li>
              <li>• Mots-clés trop génériques ("travail")</li>
              <li>• Recherches sans aucun critère</li>
            </ul>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Retour à la recherche -->
    <div class="text-center">
      <Button @click="$router.push('/dashboard/jobs')" size="lg">
        <Search class="h-4 w-4 mr-2" />
        Commencer ma recherche
      </Button>
    </div>
  </div>
</template>

<script setup>
import { 
  Info, Target, Lightbulb, MapPin, GraduationCap, Building, 
  BookOpen, CheckCircle, AlertTriangle, Search, Zap
} from 'lucide-vue-next'
import {
  Card,
  CardContent,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'

// Métadonnées de la page
definePageMeta({
  layout: 'dashboard',
  title: 'Guide des filtres de recherche'
})
</script>
