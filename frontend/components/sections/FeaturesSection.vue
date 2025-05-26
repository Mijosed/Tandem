<template>
    <section class="py-20 bg-gray-100">
      <div class="max-w-6xl mx-auto grid md:grid-cols-2 lg:grid-cols-4 gap-6 px-4">
        <Card v-for="feature in features" :key="feature.title" data-aos="flip-up">
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <component :is="feature.icon" class="w-5 h-5 text-blue-600" />
              {{ feature.title }}
            </CardTitle>
            <CardDescription>{{ feature.desc }}</CardDescription>
          </CardHeader>
        </Card>
      </div>
    </section>
  </template>
  
  <script setup>
  import {
    Card,
    CardHeader,
    CardTitle,
    CardDescription
  } from '@/components/ui/card'
  import { Briefcase, CalendarDays, Bell, Star } from 'lucide-vue-next'
  
  const features = [
    { title: 'Candidatures', desc: 'Gérez le suivi de vos candidatures.', icon: Briefcase },
    { title: 'Événements', desc: 'Ajoutez vos entretiens à votre emploi du temps.', icon: CalendarDays },
    { title: 'Notifications', desc: 'Recevez des rappels personnalisés.', icon: Bell },
    { title: 'Premium', desc: 'Accès à des offres exclusives via Indeed.', icon: Star },
  ]
  </script>