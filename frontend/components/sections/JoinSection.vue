<template>
    <section class="py-20 bg-gray-50 px-4">
      <h2 class="text-3xl font-bold text-center mb-10" data-aos="fade-up">Rejoignez Tandem dès aujourd'hui</h2>
      <div class="max-w-4xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-8">
        <Card data-aos="zoom-in-right" class="flex flex-col h-full">
          <CardHeader class="flex-grow">
            <CardTitle class="flex items-center gap-2">
              <UserPlus class="w-5 h-5 text-blue-600" /> Je suis nouveau
            </CardTitle>
            <CardDescription data-aos="fade">
              Créez un compte gratuit pour commencer à organiser vos candidatures et suivre vos démarches.
            </CardDescription>
          </CardHeader>
          <CardFooter class="mt-auto">
            <NuxtLink to="/register" class="w-full" @click="trackCTA('Create Account', 'Join')">
              <Button class="bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-700 w-full">Créer un compte</Button>
            </NuxtLink>
          </CardFooter>
        </Card>
  
        <Card data-aos="zoom-in-left" class="flex flex-col h-full">
          <CardHeader class="flex-grow">
            <CardTitle class="flex items-center gap-2">
              <LogIn class="w-5 h-5 text-green-600" /> J'ai déjà un compte
            </CardTitle>
            <CardDescription data-aos="fade">
              Connectez-vous pour reprendre là où vous vous êtes arrêté et accéder à votre tableau de bord.
            </CardDescription>
          </CardHeader>
          <CardFooter class="mt-auto">
            <NuxtLink to="/login" class="w-full" @click="trackCTA('Login Existing', 'Join')">
              <Button variant="outline" class="w-full">Se connecter</Button>
            </NuxtLink>
          </CardFooter>
        </Card>
      </div>
    </section>
  </template>
  
  <script setup>
  import {
    Card,
    CardHeader,
    CardTitle,
    CardDescription,
    CardFooter
  } from '@/components/ui/card'
  import { Button } from '@/components/ui/button'
  import { UserPlus, LogIn } from 'lucide-vue-next'
  import { useMatomo } from '@/composables/useMatomo'

  const { trackCTA } = useMatomo()
  </script>