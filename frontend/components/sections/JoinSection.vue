<template>
    <section class="py-20 bg-gray-50 px-4">
      <h2 class="text-3xl font-bold text-center mb-10" data-aos="fade-up">Rejoignez Tandem dès aujourd’hui</h2>
      <div class="max-w-4xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-6">
        <Card data-aos="zoom-in-right">
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <UserPlus class="w-5 h-5 text-blue-600" /> Je suis nouveau
            </CardTitle>
            <CardDescription data-aos="fade">Créez un compte gratuit pour commencer à organiser vos candidatures.</CardDescription>
          </CardHeader>
          <CardFooter>
            <Button variant="default" class="w-full">Créer un compte</Button>
          </CardFooter>
        </Card>
  
        <Card data-aos="zoom-in-left">
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <LogIn class="w-5 h-5 text-gray-600" /> J’ai déjà un compte
            </CardTitle>
            <CardDescription data-aos="fade">Connectez-vous pour reprendre là où vous vous êtes arrêté.</CardDescription>
          </CardHeader>
          <CardFooter>
            <Button variant="outline" class="w-full">Se connecter</Button>
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
  </script>