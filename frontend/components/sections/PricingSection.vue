<template>
    <section class="py-20 bg-white text-center px-4">
      <h2 class="text-3xl font-bold mb-10">Tarification</h2>
      <div class="flex flex-col md:flex-row gap-6 justify-center">
        <Card data-aos="flip-up">
          <CardHeader>
            <CardTitle class="flex items-center justify-center gap-2">
              <component :is="FreeIcon" class="w-5 h-5 text-green-500" /> Gratuit
            </CardTitle>
          </CardHeader>
          <CardContent>
            <p>Suivi des candidatures, emploi du temps, alertes de base.</p>
          </CardContent>
          <CardFooter>
            <NuxtLink to="/register">
              <Button variant="outline" class="mt-4">Commencer</Button>
            </NuxtLink>
          </CardFooter>
        </Card>
        <Card data-aos="flip-up">
          <CardHeader>
            <CardTitle class="flex items-center justify-center gap-2">
              <component :is="PremiumIcon" class="w-5 h-5 text-yellow-500" /> Premium
            </CardTitle>
          </CardHeader>
          <CardContent>
            <p>Offres exclusives via Indeed et rappels intelligents.</p>
          </CardContent>
          <CardFooter>
            <NuxtLink to="/register">
              <Button class="bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-700">S’abonner</Button>
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
    CardContent,
    CardFooter
  } from '@/components/ui/card'
  import { Button } from '@/components/ui/button'
  import { BadgeCheck as FreeIcon, Crown as PremiumIcon } from 'lucide-vue-next'
  </script>