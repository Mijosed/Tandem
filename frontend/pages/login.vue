<template>
  <AppHeader />
  <div class="min-h-screen flex items-center justify-center bg-gray-100 px-4">
    <Card class="w-full max-w-md">
      <CardHeader>
        <CardTitle>Connexion</CardTitle>
        <CardDescription>Connectez-vous à votre compte Tandem</CardDescription>
      </CardHeader>
      <CardContent>
        <form class="space-y-4">
          <div>
            <Label for="email">Adresse email</Label>
            <Input id="email" type="email" placeholder="exemple@mail.com" class="mt-1" />
          </div>
          <div>
            <Label for="password">Mot de passe</Label>
            <Input id="password" type="password" placeholder="••••••••" class="mt-1" />
          </div>
          <Button type="submit" class="w-full">Se connecter</Button>
        </form>
        <p class="text-center text-sm mt-4">Pas encore de compte ?
          <NuxtLink to="/register" class="text-blue-600 hover:underline">S'inscrire</NuxtLink>
        </p>
        <p class="text-center text-sm mt-2">
          Mot de passe oublié ?
          <NuxtLink to="/forgot-password" class="text-blue-600 hover:underline">Réinitialiser</NuxtLink>
        </p>
      </CardContent>
    </Card>
  </div>
</template>

<script setup lang="ts">
import {
  Card,
  CardHeader,
  CardTitle,
  CardDescription,
  CardContent
} from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { Label } from '@/components/ui/label'
import { NuxtLink } from '#components'
import AppHeader from '@/components/sections/AppHeader.vue'
</script>