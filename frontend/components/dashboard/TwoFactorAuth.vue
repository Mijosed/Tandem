<template>
  <Card class="w-full">
    <CardHeader>
      <CardTitle class="flex items-center gap-3">
        <Shield class="h-6 w-6 text-blue-600" />
        Authentification à deux facteurs (2FA)
      </CardTitle>
      <CardDescription>
        Ajoutez une couche de sécurité supplémentaire à votre compte avec l'authentification à deux facteurs
      </CardDescription>
    </CardHeader>
    
    <CardContent class="space-y-6">
      <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
        <div class="flex items-center gap-3">
          <div class="flex items-center gap-2">
            <div :class="twoFactorStatus.enabled ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" 
                 class="px-3 py-1 rounded-full text-sm font-medium">
              {{ twoFactorStatus.enabled ? 'Activé' : 'Désactivé' }}
            </div>
          </div>
          <span class="text-sm text-gray-600">
            {{ twoFactorStatus.enabled ? 'Votre compte est sécurisé avec 2FA' : 'Votre compte n\'est pas protégé par 2FA' }}
          </span>
        </div>
        
        <Button 
          @click="twoFactorStatus.enabled ? showDisableModal = true : setupTwoFactor()"
          :variant="twoFactorStatus.enabled ? 'destructive' : 'default'"
          :disabled="loading"
        >
          <Loader2 v-if="loading" class="w-4 h-4 mr-2 animate-spin" />
          {{ twoFactorStatus.enabled ? 'Désactiver' : 'Activer' }} 2FA
        </Button>
      </div>

      <div v-if="twoFactorStatus.enabled" class="p-4 border rounded-lg">
        <div class="flex items-center justify-between mb-3">
          <h4 class="font-medium">Codes de récupération</h4>
          <Badge variant="secondary">{{ twoFactorStatus.backupCodesCount }} codes restants</Badge>
        </div>
        <p class="text-sm text-gray-600 mb-3">
          Utilisez ces codes si vous perdez l'accès à votre application d'authentification
        </p>
        <Button 
          @click="generateBackupCodes" 
          variant="outline" 
          size="sm"
          :disabled="loading"
        >
          <RefreshCw class="w-4 h-4 mr-2" />
          Générer de nouveaux codes
        </Button>
      </div>
    </CardContent>

    <Dialog v-model:open="showSetupModal">
      <DialogContent class="max-w-md">
        <DialogHeader>
          <DialogTitle>Configuration de l'authentification à deux facteurs</DialogTitle>
          <DialogDescription>
            Suivez ces étapes pour sécuriser votre compte
          </DialogDescription>
        </DialogHeader>

        <div class="space-y-6 py-4">
          <div v-if="setupStep === 1">
            <Label for="password">Confirmez votre mot de passe</Label>
            <Input 
              id="password"
              v-model="setupForm.password"
              type="password"
              placeholder="Votre mot de passe actuel"
              class="mt-2"
            />
          </div>

          <div v-if="setupStep === 2" class="text-center">
            <h4 class="font-medium mb-4">Scannez ce QR code</h4>
            <div class="bg-white p-4 rounded-lg border inline-block">
              <img :src="qrCodeUrl" alt="QR Code 2FA" class="w-48 h-48" />
            </div>
            <p class="text-sm text-gray-600 mt-4">
              Scannez ce code avec Google Authenticator ou une application similaire
            </p>
            
            <div class="mt-4 p-3 bg-gray-50 rounded-lg">
              <p class="text-xs text-gray-600 mb-2">Ou entrez manuellement cette clé :</p>
              <code class="text-sm font-mono break-all">{{ secretKey }}</code>
            </div>
          </div>

          <div v-if="setupStep === 3">
            <Label for="code">Code de vérification</Label>
            <Input 
              id="code"
              v-model="setupForm.code"
              placeholder="123456"
              maxlength="6"
              class="mt-2 text-center text-lg tracking-widest"
            />
            <p class="text-sm text-gray-600 mt-2">
              Entrez le code à 6 chiffres de votre application d'authentification
            </p>
          </div>

          <div v-if="setupStep === 4">
            <h4 class="font-medium mb-4">Codes de récupération</h4>
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
              <p class="text-sm text-yellow-800 mb-3">
                <strong>Important :</strong> Sauvegardez ces codes dans un endroit sûr. 
                Ils vous permettront d'accéder à votre compte si vous perdez votre appareil d'authentification.
              </p>
              <div class="grid grid-cols-2 gap-2 font-mono text-sm">
                <div v-for="code in backupCodes" :key="code" class="p-2 bg-white rounded border">
                  {{ code }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <DialogFooter>
          <Button 
            v-if="setupStep > 1" 
            @click="setupStep--" 
            variant="outline"
          >
            Retour
          </Button>
          <Button 
            @click="handleSetupStep" 
            :disabled="loading || !canProceed"
          >
            <Loader2 v-if="loading" class="w-4 h-4 mr-2 animate-spin" />
            {{ setupStep === 4 ? 'Terminé' : 'Continuer' }}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <Dialog v-model:open="showDisableModal">
      <DialogContent class="max-w-md">
        <DialogHeader>
          <DialogTitle>Désactiver l'authentification à deux facteurs</DialogTitle>
          <DialogDescription>
            Êtes-vous sûr de vouloir désactiver le 2FA ? Votre compte sera moins sécurisé.
          </DialogDescription>
        </DialogHeader>

        <div class="py-4">
          <Label for="disable-password">Confirmez votre mot de passe</Label>
          <Input 
            id="disable-password"
            v-model="disableForm.password"
            type="password"
            placeholder="Votre mot de passe actuel"
            class="mt-2"
          />
        </div>

        <DialogFooter>
          <Button @click="showDisableModal = false" variant="outline">
            Annuler
          </Button>
          <Button 
            @click="disableTwoFactor" 
            variant="destructive"
            :disabled="loading || !disableForm.password"
          >
            <Loader2 v-if="loading" class="w-4 h-4 mr-2 animate-spin" />
            Désactiver 2FA
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <Dialog v-model:open="showBackupCodesModal">
      <DialogContent class="max-w-md">
        <DialogHeader>
          <DialogTitle>Nouveaux codes de récupération</DialogTitle>
          <DialogDescription>
            Sauvegardez ces nouveaux codes. Les anciens codes ne fonctionneront plus.
          </DialogDescription>
        </DialogHeader>

        <div class="py-4">
          <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
            <div class="grid grid-cols-2 gap-2 font-mono text-sm">
              <div v-for="code in newBackupCodes" :key="code" class="p-2 bg-white rounded border">
                {{ code }}
              </div>
            </div>
          </div>
        </div>

        <DialogFooter>
          <Button @click="showBackupCodesModal = false">
            J'ai sauvegardé les codes
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </Card>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { 
  Shield, 
  Loader2, 
  RefreshCw 
} from 'lucide-vue-next'
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Badge } from '@/components/ui/badge'
import { useToast } from '@/components/ui/toast/use-toast'

const { toast } = useToast()

// État
const loading = ref(false)
const showSetupModal = ref(false)
const showDisableModal = ref(false)
const showBackupCodesModal = ref(false)
const setupStep = ref(1)
const qrCodeUrl = ref('')
const secretKey = ref('')
const backupCodes = ref<string[]>([])
const newBackupCodes = ref<string[]>([])

// Statut 2FA
const twoFactorStatus = ref({
  enabled: false,
  hasSecret: false,
  backupCodesCount: 0
})

// Formulaires
const setupForm = ref({
  password: '',
  code: ''
})

const disableForm = ref({
  password: ''
})

// Computed
const canProceed = computed(() => {
  switch (setupStep.value) {
    case 1: return setupForm.value.password.length > 0
    case 2: return true
    case 3: return setupForm.value.code.length === 6
    case 4: return true
    default: return false
  }
})

// Méthodes
const fetchTwoFactorStatus = async () => {
  try {
    const userId = getUserId()
    if (!userId) return

    const response = await fetch('/api/auth/2fa/status', {
      headers: {
        'X-User-ID': userId.toString()
      }
    })

    if (response.ok) {
      const data = await response.json()
      twoFactorStatus.value = {
        enabled: data.enabled,
        hasSecret: data.hasSecret,
        backupCodesCount: data.backupCodesCount
      }
    }
  } catch (error) {
    console.error('Erreur lors de la récupération du statut 2FA:', error)
  }
}

const setupTwoFactor = () => {
  setupStep.value = 1
  setupForm.value = { password: '', code: '' }
  showSetupModal.value = true
}

const handleSetupStep = async () => {
  if (setupStep.value === 1) {
    await initiate2FASetup()
  } else if (setupStep.value === 3) {
    await enable2FA()
  } else if (setupStep.value === 4) {
    showSetupModal.value = false
    await fetchTwoFactorStatus()
  } else {
    setupStep.value++
  }
}

const initiate2FASetup = async () => {
  loading.value = true
  try {
    const userId = getUserId()
    const response = await fetch('http://localhost:8888/api/auth/2fa/setup', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-User-ID': userId.toString()
      },
      body: JSON.stringify({
        password: setupForm.value.password
      })
    })

    if (response.ok) {
      const data = await response.json()
      qrCodeUrl.value = data.qrCodeUrl
      secretKey.value = data.secret
      backupCodes.value = data.backupCodes
      setupStep.value = 2
    } else {
      const error = await response.json()
      toast({
        title: "Erreur",
        description: error.error || "Erreur lors de la configuration 2FA",
        variant: "destructive"
      })
    }
  } catch (error) {
    toast({
      title: "Erreur",
      description: "Erreur de connexion",
      variant: "destructive"
    })
  } finally {
    loading.value = false
  }
}

const enable2FA = async () => {
  loading.value = true
  try {
    const userId = getUserId()
    const response = await fetch('http://localhost:8888/api/auth/2fa/enable', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-User-ID': userId.toString()
      },
      body: JSON.stringify({
        code: setupForm.value.code
      })
    })

    if (response.ok) {
      setupStep.value = 4
      toast({
        title: "Succès",
        description: "2FA activé avec succès",
        variant: "default"
      })
    } else {
      const error = await response.json()
      toast({
        title: "Erreur",
        description: error.error || "Code invalide",
        variant: "destructive"
      })
    }
  } catch (error) {
    toast({
      title: "Erreur",
      description: "Erreur de connexion",
      variant: "destructive"
    })
  } finally {
    loading.value = false
  }
}

const disableTwoFactor = async () => {
  loading.value = true
  try {
    const userId = getUserId()
    const response = await fetch('http://localhost:8888/api/auth/2fa/disable', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-User-ID': userId.toString()
      },
      body: JSON.stringify({
        password: disableForm.value.password
      })
    })

    if (response.ok) {
      showDisableModal.value = false
      disableForm.value.password = ''
      await fetchTwoFactorStatus()
      toast({
        title: "Succès",
        description: "2FA désactivé",
        variant: "default"
      })
    } else {
      const error = await response.json()
      toast({
        title: "Erreur",
        description: error.error || "Erreur lors de la désactivation",
        variant: "destructive"
      })
    }
  } catch (error) {
    toast({
      title: "Erreur",
      description: "Erreur de connexion",
      variant: "destructive"
    })
  } finally {
    loading.value = false
  }
}

const generateBackupCodes = async () => {
  loading.value = true
  try {
    const userId = getUserId()
    const response = await fetch('http://localhost:8888/api/auth/2fa/backup-codes', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-User-ID': userId.toString()
      },
      body: JSON.stringify({
        password: prompt('Confirmez votre mot de passe:') || ''
      })
    })

    if (response.ok) {
      const data = await response.json()
      newBackupCodes.value = data.backupCodes
      showBackupCodesModal.value = true
      await fetchTwoFactorStatus()
    } else {
      const error = await response.json()
      toast({
        title: "Erreur",
        description: error.error || "Erreur lors de la génération",
        variant: "destructive"
      })
    }
  } catch (error) {
    toast({
      title: "Erreur",
      description: "Erreur de connexion",
      variant: "destructive"
    })
  } finally {
    loading.value = false
  }
}

const getUserId = () => {
  if (typeof window !== 'undefined') {
    const user = JSON.parse(localStorage.getItem('user') || '{}')
    return user.id
  }
  return null
}

onMounted(() => {
  fetchTwoFactorStatus()
})
</script> 