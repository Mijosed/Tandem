<template>
  <div>
          <header class="flex h-16 shrink-0 items-center gap-2 border-b transition-[width,height] ease-linear">
        <div class="flex items-center gap-2 px-4">
          <SidebarTrigger v-if="isMobile" class="-ml-1" />
          <h1 class="text-2xl font-bold">Abonnement</h1>
        </div>
      </header>

    <div class="container py-6 px-4">
      <!-- État de l'abonnement -->
      <Card class="mb-8">
        <CardHeader>
          <CardTitle>État de votre abonnement</CardTitle>
          <CardDescription>
            Votre abonnement actuel et son statut
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xl font-semibold mb-2 flex items-center">
                <Crown class="mr-2 h-5 w-5 text-yellow-500" />
                {{ subscription.plan === 'premium' ? 'Premium' : 'Gratuit' }}
              </p>
              <p class="text-muted-foreground">
                {{ subscription.plan === 'premium' 
                  ? 'Accès à toutes les fonctionnalités premium' 
                  : 'Accès aux fonctionnalités de base' }}
              </p>
            </div>
            <Badge 
              :variant="subscription.status === 'active' ? 'default' : 'destructive'"
            >
              {{ subscription.status === 'active' ? 'Actif' : 'Inactif' }}
            </Badge>
          </div>
          <div v-if="subscription.plan === 'premium'" class="mt-4 text-sm text-muted-foreground">
            Prochain paiement le {{ formatDate(subscription.nextBilling) }}
          </div>
        </CardContent>
      </Card>

      <!-- Plans d'abonnement -->
      <div class="space-y-6">
        <h2 class="text-2xl font-semibold tracking-tight">Plans disponibles</h2>
        <div class="grid gap-6 lg:grid-cols-2">
          <!-- Plan Gratuit -->
          <Card>
            <CardHeader>
              <CardTitle class="flex items-center gap-2">
                <BadgeCheck class="h-5 w-5 text-green-500" />
                Gratuit
              </CardTitle>
              <CardDescription>
                Pour démarrer votre recherche d'alternance
              </CardDescription>
            </CardHeader>
            <CardContent class="grid gap-4">
              <div class="text-2xl font-bold">0 €/mois</div>
              <ul class="grid gap-2 text-sm">
                <li class="flex items-center gap-2">
                  <Check class="h-4 w-4" /> Gestion des candidatures
                </li>
                <li class="flex items-center gap-2">
                  <Check class="h-4 w-4" /> Emploi du temps
                </li>
                <li class="flex items-center gap-2">
                  <Check class="h-4 w-4" /> Notifications de base
                </li>
              </ul>
            </CardContent>
            <CardFooter>
              <Button 
                class="w-full" 
                variant="outline" 
                :disabled="subscription.plan === 'free'"
              >
                {{ subscription.plan === 'free' ? 'Plan actuel' : 'Choisir ce plan' }}
              </Button>
            </CardFooter>
          </Card>

          <!-- Plan Premium -->
          <Card>
            <CardHeader>
              <CardTitle class="flex items-center gap-2">
                <Crown class="h-5 w-5 text-yellow-500" />
                Premium
              </CardTitle>
              <CardDescription>
                Pour maximiser vos chances
              </CardDescription>
            </CardHeader>
            <CardContent class="grid gap-4">
              <div class="text-2xl font-bold">9,99 €/mois</div>
              <ul class="grid gap-2 text-sm">
                <li class="flex items-center gap-2">
                  <Check class="h-4 w-4" /> Toutes les fonctionnalités gratuites
                </li>
                <li class="flex items-center gap-2">
                  <Check class="h-4 w-4" /> Offres exclusives via Indeed
                </li>
                <li class="flex items-center gap-2">
                  <Check class="h-4 w-4" /> Rappels intelligents
                </li>
                <li class="flex items-center gap-2">
                  <Check class="h-4 w-4" /> Statistiques avancées
                </li>
                <li class="flex items-center gap-2">
                  <Check class="h-4 w-4" /> Support prioritaire
                </li>
              </ul>
            </CardContent>            <CardFooter>
              <Button 
                class="w-full" 
                :disabled="subscription.plan === 'premium'"
                :variant="subscription.plan === 'premium' ? 'outline' : 'default'"
                @click="showPaymentDialog"
              >
                {{ subscription.plan === 'premium' ? 'Plan actuel' : 'Passer au Premium' }}
              </Button>
            </CardFooter>
          </Card>
        </div>
      </div>

      <!-- Modal de paiement -->
      <Dialog v-model:open="isPaymentDialogOpen">
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Passer au plan Premium</DialogTitle>
            <DialogDescription>
              Vous serez facturé 9,99 € par mois
            </DialogDescription>
          </DialogHeader>
          
          <div v-if="clientSecret">
            <PaymentForm
              :client-secret="clientSecret"
              :success-url="successUrl"
              :amount="999"
              @success="handlePaymentSuccess"
              @error="handlePaymentError"
            />
          </div>
          <div v-else class="flex justify-center p-4">
            <Loader2 class="h-6 w-6 animate-spin" />
          </div>
        </DialogContent>
      </Dialog>

      <!-- Historique des paiements -->
      <div v-if="subscription.plan === 'premium'" class="mt-8 space-y-6">
        <h2 class="text-2xl font-semibold tracking-tight">Historique des paiements</h2>
        <Card>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Date</TableHead>
                <TableHead>Montant</TableHead>
                <TableHead>Statut</TableHead>
                <TableHead class="text-right">Facture</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="payment in payments" :key="payment.id">
                <TableCell>{{ formatDate(payment.date) }}</TableCell>
                <TableCell>{{ payment.amount }} €</TableCell>
                <TableCell>
                  <Badge variant="outline">{{ payment.status }}</Badge>
                </TableCell>
                <TableCell class="text-right">
                  <Button variant="ghost" size="sm">
                    <Download class="h-4 w-4" />
                  </Button>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </Card>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { formatDate } from '~/lib/utils'
import { Crown, BadgeCheck, Check, Download, Loader2 } from 'lucide-vue-next'
import { SidebarTrigger } from '~/components/ui/sidebar'
import { useMediaQuery } from '@vueuse/core'
import { Button } from '~/components/ui/button'
import { Badge } from '~/components/ui/badge'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
} from '~/components/ui/dialog'
import PaymentForm from '~/components/ui/stripe/PaymentForm.vue'
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from '~/components/ui/card'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '~/components/ui/table'

definePageMeta({
  layout: 'dashboard'
})

// Détection mobile pour afficher conditionnellement le SidebarTrigger
const isMobile = useMediaQuery('(max-width: 768px)')

// Mock data
const subscription = ref({
  plan: 'premium',
  status: 'active',
  nextBilling: '2025-06-27',
})

const payments = ref([
  {
    id: 1,
    date: '2025-05-27',
    amount: 9.99,
    status: 'Payé',
  },
  {
    id: 2,
    date: '2025-04-27',
    amount: 9.99,
    status: 'Payé',
  },
  {
    id: 3,
    date: '2025-03-27',
    amount: 9.99,
    status: 'Payé',
  },
])

const isPaymentDialogOpen = ref(false)
const clientSecret = ref('')
const successUrl = ref(window?.location?.origin + '/dashboard/subscription/success')

const showPaymentDialog = async () => {
  isPaymentDialogOpen.value = true
  try {
    // TODO: Appel à l'API pour créer une intention de paiement
    // Cette partie devra être implémentée côté backend
    const response = await fetch('/api/create-payment-intent', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        amount: 999, // 9.99 €
        currency: 'eur'
      }),
    })
    const data = await response.json()
    clientSecret.value = data.clientSecret
  } catch (error) {
    console.error('Erreur lors de la création de l\'intention de paiement:', error)
    // TODO: Afficher une notification d'erreur
  }
}

const handlePaymentSuccess = () => {
  subscription.value.plan = 'premium'
  subscription.value.status = 'active'
  subscription.value.nextBilling = new Date(
    Date.now() + 30 * 24 * 60 * 60 * 1000
  ).toISOString().split('T')[0]
  isPaymentDialogOpen.value = false
  // TODO: Afficher une notification de succès
}

const handlePaymentError = (error: string) => {
  console.error('Erreur de paiement:', error)
  // TODO: Afficher une notification d'erreur
}
</script>
