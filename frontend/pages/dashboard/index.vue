<script setup lang="ts">
import { SidebarTrigger } from '~/components/ui/sidebar'
import { ref, onMounted } from 'vue'
import { 
  BriefcaseIcon, 
  CalendarDaysIcon, 
  BellIcon, 
  TrendingUpIcon,
  ClockIcon,
  CheckCircleIcon,
  XCircleIcon,
  MessageSquareIcon
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

// Données utilisateur (à connecter avec l'état de l'application)
const user = ref({
  name: 'John Doe',
  role: 'Étudiant en alternance'
})

// Citations motivantes
const quotes = [
  "Le succès n'est pas final, l'échec n'est pas fatal. C'est le courage de continuer qui compte.",
  "Le meilleur moyen de prédire l'avenir est de le créer.",
  "Chaque expert était d'abord un débutant.",
  "L'éducation est l'arme la plus puissante pour changer le monde."
]
const todaysQuote = ref(quotes[Math.floor(Math.random() * quotes.length)])

// Statistiques
const stats = ref({
  applications: 12,
  upcomingInterviews: 3,
  pendingResponses: 5,
  responseRate: 75,
  activeApplications: 8,
  rejectedApplications: 4,
  messages: 7
})

// Format de la date en français
const today = new Date().toLocaleDateString('fr-FR', {
  weekday: 'long',
  year: 'numeric',
  month: 'long',
  day: 'numeric'
})

</script>

<template>
  <div>
    <header class="flex h-16 shrink-0 items-center gap-2 border-b transition-[width,height] ease-linear">
      <div class="flex items-center gap-2 px-4">
        <SidebarTrigger class="-ml-1" />
        <h1 class="text-2xl font-bold">Tableau de bord</h1>
      </div>
    </header>

    <div class="container py-6 px-4 space-y-6">
      <!-- Section d'accueil -->
      <Card class="bg-gradient-to-br from-purple-50 to-blue-50">
        <CardContent class="pt-6">
          <h2 class="text-3xl font-bold">
            Bonjour, {{ user.name }} 👋
          </h2>
          <p class="text-muted-foreground mt-1">{{ today }}</p>
          <p class="mt-4 text-lg italic text-muted-foreground">
            "{{ todaysQuote }}"
          </p>
        </CardContent>
      </Card>

      <!-- Statistiques principales -->
      <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <NuxtLink to="/dashboard/applications" class="block">
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

        <NuxtLink to="/dashboard/applications" class="block">
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
        <NuxtLink to="/dashboard/applications" class="block">
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
              <CardTitle>Prochains événements</CardTitle>
              <CardDescription>Vos rendez-vous à venir</CardDescription>
            </CardHeader>
            <CardContent>
              <div class="space-y-4">
                <div class="flex items-start space-x-4">
                  <div class="min-w-[56px] text-center">
                    <div class="text-xl font-bold">29</div>
                    <div class="text-xs text-muted-foreground">Mai</div>
                  </div>
                  <div>
                    <p class="text-sm font-medium">Entretien technique</p>
                    <p class="text-xs text-muted-foreground">Innovative Tech Solutions - 14h30</p>
                  </div>
                </div>
                <div class="flex items-start space-x-4">
                  <div class="min-w-[56px] text-center">
                    <div class="text-xl font-bold">02</div>
                    <div class="text-xs text-muted-foreground">Juin</div>
                  </div>
                  <div>
                    <p class="text-sm font-medium">Second entretien RH</p>
                    <p class="text-xs text-muted-foreground">DataCorp Solutions - 10h00</p>
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
