<template>
  <div style="background: lime; padding: 50px; border: 5px solid black;">
    <h1 style="color: black; font-size: 60px;">🔥 EMERGENCY TEST 🔥</h1>
    <p style="font-size: 30px;">URL: /dashboard/emergency-test</p>
    <button @click="count++" style="font-size: 25px; background: black; color: lime; padding: 15px;">
      COMPTEUR: {{ count }}
    </button>
  </div>
</template>

<script setup>
import { ref } from 'vue'
const count = ref(0)
</script>
