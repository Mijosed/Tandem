<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { Button } from '@/components/ui/button'
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'

const email = ref('')
const password = ref('')
const errorMsg = ref('')
const successMsg = ref('')
const router = useRouter()

const client = useSupabaseClient()

const signup = async () => {
  const { data, error } = await client.auth.signUp({
    email: email.value,
    password: password.value
  })

  if (error) {
    errorMsg.value = error.message
    successMsg.value = ''
  } else {
    successMsg.value = 'Un e-mail de confirmation vous a été envoyé.'
    errorMsg.value = ''
  }
}
</script>

<template>
  <Card class="mx-auto max-w-sm">
    <CardHeader>
      <CardTitle class="text-2xl">Créer un compte</CardTitle>
      <CardDescription>
        Entrez votre email ci-dessous pour créer votre compte
      </CardDescription>
    </CardHeader>
    <CardContent>
      <form @submit.prevent="signup" class="grid gap-4">
        <div class="grid gap-2">
          <Label for="email">Email</Label>
          <Input
            id="email"
            type="email"
            placeholder="m@example.com"
            v-model="email"
            required
          />
        </div>
        <div class="grid gap-2">
          <Label for="password">Mot de passe</Label>
          <Input
            id="password"
            type="password"
            v-model="password"
            required
          />
        </div>
        <Button type="submit" class="w-full bg-[#457891]">S'inscrire</Button>
        <p v-if="errorMsg" class="text-sm text-red-500">{{ errorMsg }}</p>
        <p v-if="successMsg" class="text-sm text-green-600">{{ successMsg }}</p>
      </form>
      <div class="mt-4 text-center text-sm">
        Vous avez déjà un compte ?
        <RouterLink to="/login" class="underline">Connectez-vous</RouterLink>
      </div>
    </CardContent>
  </Card>
</template>
