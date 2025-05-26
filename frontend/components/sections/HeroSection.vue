<template>
    <section class="text-center py-24 px-4 bg-white relative overflow-hidden">
      <img src="/assets/images/hero-bg.jpg" alt="Tandem" class="absolute inset-0 w-full h-full object-cover opacity-10" />
      <div class="relative z-10">
        <h1 class="text-4xl md:text-6xl font-bold mb-4" data-aos="fade-down">Trouvez votre alternance facilement</h1>
        <p class="text-lg text-gray-600 mb-6" data-aos="fade-up">Avec Tandem, organisez vos candidatures sans stress.</p>
        <div class="flex justify-center gap-4" data-aos="zoom-in">
          <Button variant="default">Commencer gratuitement</Button>
          <Button variant="outline">Se connecter</Button>
        </div>
      </div>
    </section>
  </template>
  
  <script setup>
  import { Button } from '@/components/ui/button'
  </script>
  