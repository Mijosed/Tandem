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
const router = useRouter()

const client = useSupabaseClient()

const login = async () => {
  const { data, error } = await client.auth.signInWithPassword({
    email: email.value,
    password: password.value
  })

  if (error) {
    errorMsg.value = error.message
  } else {
    router.push('/offers')
  }
}
</script>

<template>
  <Card class="mx-auto max-w-sm">
    <CardHeader>
      <CardTitle class="text-2xl">Connexion</CardTitle>
      <CardDescription>
        Entrez votre email ci-dessous pour vous connecter à votre compte
      </CardDescription>
    </CardHeader>
    <CardContent>
      <form @submit.prevent="login" class="grid gap-4">
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
          <div class="flex items-center">
            <Label for="password">Mot de passe</Label>
            <RouterLink
              to="/forgotpassword"
              class="ml-auto inline-block text-sm underline"
            >
              Mot de passe oublié ?
            </RouterLink>
          </div>
          <Input
            id="password"
            type="password"
            v-model="password"
            required
          />
        </div>
        <Button type="submit" class="w-full bg-[#457891]">Se connecter</Button>
        <p v-if="errorMsg" class="text-sm text-red-500">{{ errorMsg }}</p>
      </form>
      <div class="mt-4 text-center text-sm">
        Vous n'avez pas de compte ?
        <RouterLink to="/register" class="underline">Inscrivez-vous</RouterLink>
      </div>
    </CardContent>
  </Card>
</template>
