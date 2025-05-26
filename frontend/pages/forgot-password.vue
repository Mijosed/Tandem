<template>
    <AppHeader />
    <div class="min-h-screen flex items-center justify-center bg-gray-100 px-4">
      <Card class="w-full max-w-md">
        <CardHeader>
          <CardTitle>Mot de passe oublié</CardTitle>
          <CardDescription>Entrez votre email pour recevoir un lien de réinitialisation</CardDescription>
        </CardHeader>
        <CardContent>
          <form class="space-y-4">
            <div>
              <Label for="email">Adresse email</Label>
              <Input id="email" type="email" placeholder="exemple@mail.com" class="mt-1" />
            </div>
            <Button type="submit" class="w-full">Envoyer un lien</Button>
          </form>
          <p class="text-center text-sm mt-4">
            Vous vous souvenez de votre mot de passe ?
            <NuxtLink to="/login" class="text-blue-600 hover:underline">Se connecter</NuxtLink>
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