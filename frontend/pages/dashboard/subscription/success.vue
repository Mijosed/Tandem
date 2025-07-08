<template>
  <div>
          <header class="flex h-16 shrink-0 items-center gap-2 border-b transition-[width,height] ease-linear">
        <div class="flex items-center gap-2 px-4">
          <SidebarTrigger v-if="isMobile" class="-ml-1" />
          <h1 class="text-2xl font-bold">Paiement réussi</h1>
        </div>
      </header>

    <div class="container py-6 px-4">
      <Card>
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <CheckCircle class="h-6 w-6 text-green-500" />
            Merci pour votre abonnement !
          </CardTitle>
          <CardDescription>
            Votre paiement a été traité avec succès
          </CardDescription>
        </CardHeader>
        <CardContent class="space-y-4">
          <p>
            Vous avez maintenant accès à toutes les fonctionnalités premium de Tandem.
            Votre abonnement sera automatiquement renouvelé chaque mois.
          </p>
          <div class="flex flex-col gap-2">
            <div class="flex items-center gap-2">
              <Check class="h-4 w-4 text-green-500" />
              <span>Offres exclusives via Indeed</span>
            </div>
            <div class="flex items-center gap-2">
              <Check class="h-4 w-4 text-green-500" />
              <span>Rappels intelligents</span>
            </div>
            <div class="flex items-center gap-2">
              <Check class="h-4 w-4 text-green-500" />
              <span>Statistiques avancées</span>
            </div>
            <div class="flex items-center gap-2">
              <Check class="h-4 w-4 text-green-500" />
              <span>Support prioritaire</span>
            </div>
          </div>
        </CardContent>
        <CardFooter>
          <Button asChild>
            <NuxtLink to="/dashboard">
              Retour au tableau de bord
            </NuxtLink>
          </Button>
        </CardFooter>
      </Card>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { CheckCircle, Check } from 'lucide-vue-next'
import { SidebarTrigger } from '~/components/ui/sidebar'
import { useMediaQuery } from '@vueuse/core'
import { Button } from '~/components/ui/button'
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from '~/components/ui/card'

definePageMeta({
  layout: 'dashboard'
})

// Détection mobile pour afficher conditionnellement le SidebarTrigger
const isMobile = useMediaQuery('(max-width: 768px)')

onMounted(() => {
  // TODO: Vérifier le statut du paiement avec l'API
  // et mettre à jour le statut de l'abonnement si nécessaire
})
</script>
