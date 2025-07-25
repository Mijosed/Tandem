<template>
  <div>
    <AppHeader />
    <HeroSection />
    <FeaturesSection />
    <PricingSection />
    <JoinSection />
    <TestimonialsSection />
    <FAQSection />
    <FooterSection />
    <!-- Debug component for development -->
    <MatomoDebug v-if="isDev" />
  </div>
</template>

<script setup>
  import HeroSection from '@/components/sections/HeroSection.vue';
  import FeaturesSection from '~/components/sections/FeaturesSection.vue';
  import PricingSection from '~/components/sections/PricingSection.vue';
  import TestimonialsSection from '~/components/sections/TestimonialsSection.vue';
  import FAQSection from '~/components/sections/FAQSection.vue';
  import FooterSection from '~/components/sections/FooterSection.vue';
  import JoinSection from '~/components/sections/JoinSection.vue';
  import AppHeader from '~/components/sections/AppHeader.vue';
  import MatomoDebug from '~/components/MatomoDebug.vue';

  const isDev = process.dev;
</script>

