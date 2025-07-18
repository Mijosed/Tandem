<template>
    <section class="py-20 bg-gray-50 px-4">
      <div class="max-w-4xl mx-auto">
      <h2 class="text-3xl font-bold text-center mb-8">FAQ</h2>
      <Accordion type="single" collapsible class="w-full" data-aos="fade-up">
        <AccordionItem value="q1">
          <AccordionTrigger>Comment fonctionne Tandem ?</AccordionTrigger>
          <AccordionContent>Vous créez un compte et commencez à gérer vos candidatures facilement.</AccordionContent>
        </AccordionItem>
        <AccordionItem value="q2" data-aos="fade-up">
          <AccordionTrigger>Qu’inclut l’offre Premium ?</AccordionTrigger>
          <AccordionContent>Des offres d'emploi exclusives via Indeed et des rappels intelligents.</AccordionContent>
        </AccordionItem>
      </Accordion>
      <Accordion type="single" collapsible class="w-full mt-6" data-aos="fade-up">
        <AccordionItem value="q3">
          <AccordionTrigger>Est-ce que Tandem est gratuit ?</AccordionTrigger>
          <AccordionContent>Oui, l'inscription de base est gratuite. L'offre Premium est payante.</AccordionContent>
        </AccordionItem>
        <AccordionItem value="q4" data-aos="fade-up">
          <AccordionTrigger>Comment puis-je m'inscrire ?</AccordionTrigger>
          <AccordionContent>Vous pouvez vous inscrire directement sur notre site web.</AccordionContent>
        </AccordionItem>
      </Accordion>
      </div>
    </section>
  </template>
  
  <script setup>
  import {
    Accordion,
    AccordionItem,
    AccordionTrigger,
    AccordionContent
  } from '@/components/ui/accordion'
  </script>
  