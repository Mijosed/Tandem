<script setup lang="ts">
import { SidebarTrigger } from '~/components/ui/sidebar'
import { ref, onMounted, computed } from 'vue'
import { useMediaQuery } from '@vueuse/core'
import { 
  BriefcaseIcon, 
  CalendarDaysIcon, 
  BellIcon, 
  TrendingUpIcon,
  ClockIcon,
  CheckCircleIcon,
  XCircleIcon,
  MessageSquareIcon,
  LoaderIcon
} from 'lucide-vue-next'
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'

definePageMeta({
  layout: 'dashboard'
})

// Utiliser le composable Dashboard
const { 
  loading, 
  error, 
  user, 
  stats, 
  recentApplications, 
  recentNotifications,
  fetchDashboardData 
} = useDashboard()

// Citations motivantes
const quotes = [
  "Le succès n'est pas final, l'échec n'est pas fatal. C'est le courage de continuer qui compte.",
  "Le meilleur moyen de prédire l'avenir est de le créer.",
  "Chaque expert était d'abord un débutant.",
  "L'éducation est l'arme la plus puissante pour changer le monde."
]

// Utiliser une citation basée sur la date pour éviter l'erreur d'hydratation
const todaysQuote = ref('')

// Nom d'affichage de l'utilisateur
const displayName = computed(() => {
  if (user.value) {
    return user.value.fullName || `${user.value.firstName} ${user.value.lastName}` || user.value.email
  }
  return 'Utilisateur'
})

onMounted(async () => {
  // Générer un index basé sur le jour de l'année pour avoir une citation constante par jour
  const now = new Date()
  const start = new Date(now.getFullYear(), 0, 0)
  const diff = now.getTime() - start.getTime()
  const dayOfYear = Math.floor(diff / (1000 * 60 * 60 * 24))
  todaysQuote.value = quotes[dayOfYear % quotes.length]
  
  // Charger les données du dashboard
  await fetchDashboardData()
})

// Format de la date en français
const today = new Date().toLocaleDateString('fr-FR', {
  weekday: 'long',
  year: 'numeric',
  month: 'long',
  day: 'numeric'
})

// Détection mobile pour afficher conditionnellement le SidebarTrigger
const isMobile = useMediaQuery('(max-width: 768px)')

</script>

<template>
  <div>
    <header class="flex h-16 shrink-0 items-center gap-2 border-b transition-[width,height] ease-linear">
      <div class="flex items-center gap-2 px-4">
        <SidebarTrigger v-if="isMobile" class="-ml-1" />
        <h1 class="text-2xl font-bold">Tableau de bord</h1>
      </div>
    </header>

    <div class="container py-6 px-4 space-y-6">
      <!-- Section d'accueil -->
      <Card class="bg-gradient-to-br from-purple-50 to-blue-50">
        <CardContent class="pt-6">
          <!-- Affichage du loader pendant le chargement -->
          <div v-if="loading" class="flex items-center space-x-2">
            <LoaderIcon class="h-5 w-5 animate-spin" />
            <span>Chargement des données...</span>
          </div>
          
          <!-- Affichage des erreurs -->
          <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-md p-4 mb-4">
            <p class="text-red-800">{{ error }}</p>
          </div>
          
          <!-- Contenu normal -->
          <div v-else>
            <h2 class="text-3xl font-bold">
              Bonjour, {{ displayName }} 👋
            </h2>
            <p class="text-muted-foreground mt-1">{{ today }}</p>
            <p v-if="user?.subscription?.isPremium" class="text-xs bg-gradient-to-r from-purple-500 to-pink-500 text-white px-2 py-1 rounded-full inline-block mt-2">
              Premium
            </p>
            <p class="mt-4 text-lg italic text-muted-foreground">
              "{{ todaysQuote }}"
            </p>
          </div>
        </CardContent>
      </Card>

      <!-- Statistiques principales -->
      <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <NuxtLink to="/dashboard/candidatures" class="block">
          <Card class="cursor-pointer transition-shadow hover:shadow-md">
            <CardHeader class="flex flex-row items-center justify-between pb-2">
              <CardTitle class="text-sm font-medium text-muted-foreground">
                Candidatures totales
              </CardTitle>
              <BriefcaseIcon class="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div class="text-2xl font-bold">{{ stats.applications }}</div>
              <p class="text-xs text-muted-foreground">
                {{ stats.activeApplications }} en cours
              </p>
            </CardContent>
          </Card>
        </NuxtLink>

        <NuxtLink to="/dashboard/schedule" class="block">
          <Card class="cursor-pointer transition-shadow hover:shadow-md">
            <CardHeader class="flex flex-row items-center justify-between pb-2">
              <CardTitle class="text-sm font-medium text-muted-foreground">
                Entretiens à venir
              </CardTitle>
              <CalendarDaysIcon class="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div class="text-2xl font-bold">{{ stats.upcomingInterviews }}</div>
              <p class="text-xs text-emerald-500">
                Prochain dans 2 jours
              </p>
            </CardContent>
          </Card>
        </NuxtLink>

        <NuxtLink to="/dashboard/notifications" class="block">
          <Card class="cursor-pointer transition-shadow hover:shadow-md">
            <CardHeader class="flex flex-row items-center justify-between pb-2">
              <CardTitle class="text-sm font-medium text-muted-foreground">
                Messages non lus
              </CardTitle>
              <MessageSquareIcon class="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div class="text-2xl font-bold">{{ stats.messages }}</div>
              <p class="text-xs text-muted-foreground">
                3 nouveaux aujourd'hui
              </p>
            </CardContent>
          </Card>
        </NuxtLink>

        <NuxtLink to="/dashboard/candidatures" class="block">
          <Card class="cursor-pointer transition-shadow hover:shadow-md">
            <CardHeader class="flex flex-row items-center justify-between pb-2">
              <CardTitle class="text-sm font-medium text-muted-foreground">
                Taux de réponse
              </CardTitle>
              <TrendingUpIcon class="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div class="text-2xl font-bold">{{ stats.responseRate }}%</div>
              <p class="text-xs text-emerald-500">
                +12% ce mois-ci
              </p>
            </CardContent>
          </Card>
        </NuxtLink>
      </div>

      <!-- Section détaillée -->
      <div class="grid gap-4 md:grid-cols-2">
        <!-- État des candidatures -->
        <NuxtLink to="/dashboard/candidatures" class="block">
          <Card class="cursor-pointer transition-shadow hover:shadow-md h-full">
            <CardHeader>
              <CardTitle>État des candidatures</CardTitle>
              <CardDescription>Vue d'ensemble de vos démarches</CardDescription>
            </CardHeader>
            <CardContent>
              <div class="space-y-4">
                <div class="flex items-center">
                  <ClockIcon class="h-4 w-4 text-amber-500 mr-2" />
                  <div class="flex-1">
                    <div class="text-sm font-medium">En attente</div>
                    <div class="text-xs text-muted-foreground">{{ stats.pendingResponses }} réponses attendues</div>
                  </div>
                </div>
                <div class="flex items-center">
                  <CheckCircleIcon class="h-4 w-4 text-emerald-500 mr-2" />
                  <div class="flex-1">
                    <div class="text-sm font-medium">Actives</div>
                    <div class="text-xs text-muted-foreground">{{ stats.activeApplications }} processus en cours</div>
                  </div>
                </div>
                <div class="flex items-center">
                  <XCircleIcon class="h-4 w-4 text-red-500 mr-2" />
                  <div class="flex-1">
                    <div class="text-sm font-medium">Refusées</div>
                    <div class="text-xs text-muted-foreground">{{ stats.rejectedApplications }} candidatures</div>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>
        </NuxtLink>

        <!-- Prochains événements -->
        <NuxtLink to="/dashboard/schedule" class="block">
          <Card class="cursor-pointer transition-shadow hover:shadow-md h-full">
            <CardHeader>
              <CardTitle>Candidatures récentes</CardTitle>
              <CardDescription>Vos dernières démarches</CardDescription>
            </CardHeader>
            <CardContent>
              <div v-if="loading" class="flex items-center justify-center py-4">
                <LoaderIcon class="h-5 w-5 animate-spin" />
              </div>
              <div v-else-if="recentApplications.length === 0" class="text-center py-4 text-muted-foreground">
                Aucune candidature récente
              </div>
              <div v-else class="space-y-4">
                <div 
                  v-for="application in recentApplications" 
                  :key="application.id"
                  class="flex items-start space-x-4"
                >
                  <div class="min-w-[56px] text-center">
                    <div class="text-xl font-bold">{{ new Date(application.createdAt).getDate() }}</div>
                    <div class="text-xs text-muted-foreground">
                      {{ new Date(application.createdAt).toLocaleDateString('fr-FR', { month: 'short' }) }}
                    </div>
                  </div>
                  <div class="flex-1">
                    <p class="text-sm font-medium">{{ application.position || 'Position non spécifiée' }}</p>
                    <p class="text-xs text-muted-foreground">
                      {{ application.company || 'Entreprise' }} - 
                      <span :class="{
                        'text-amber-500': application.status === 'pending',
                        'text-emerald-500': application.status === 'active',
                        'text-red-500': application.status === 'rejected'
                      }">
                        {{ application.status === 'pending' ? 'En attente' : 
                           application.status === 'active' ? 'Active' : 
                           application.status === 'rejected' ? 'Refusée' : application.status }}
                      </span>
                    </p>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>
        </NuxtLink>
      </div>
    </div>
  </div>
</template>
