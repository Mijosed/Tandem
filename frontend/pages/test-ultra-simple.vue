<template>
  <div>
    <h1>Test Ultra Simple</h1>
    <p>Si vous voyez ceci, Nuxt fonctionne.</p>
    <button @click="test">Clic = {{ clicks }}</button>
  </div>
</template>

<script setup>
const clicks = ref(0)
const test = () => clicks.value++
</script>
