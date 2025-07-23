// resources/js/pages/Display.vue
<script setup>
import { ref } from 'vue';
import DoctorsDirectory from '../components/DoctorsDirectory.vue'; // سننشئه بعد قليل

const activeView = ref('map'); // 'map' or 'doctors'
</script>

<template>
  <main class="w-full h-screen bg-brand-bg text-brand-text font-sans flex overflow-hidden">
    
    <nav class="w-24 bg-white/50 backdrop-blur-sm shadow-lg p-4 flex flex-col items-center justify-center space-y-8">
      <button @click="activeView = 'map'" class="w-16 h-16 flex items-center justify-center rounded-lg transition-all duration-300" :class="activeView === 'map' ? 'bg-brand-primary text-white scale-110' : 'bg-white'">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
      </button>
      <button @click="activeView = 'doctors'" class="w-16 h-16 flex items-center justify-center rounded-lg transition-all duration-300" :class="activeView === 'doctors' ? 'bg-brand-primary text-white scale-110' : 'bg-white'">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M15 21a6 6 0 00-9-5.197" /></svg>
      </button>
    </nav>

    <section class="flex-1 p-8">
      <div v-if="activeView === 'map'">
        <h1 class="text-4xl font-bold text-brand-text">Interactive Clinic Map</h1>
        <p class="mt-2 text-lg text-gray-500">The 3D map will live here.</p>
      </div>
      
      <div v-if="activeView === 'doctors'">
        <DoctorsDirectory />
      </div>
    </section>

  </main>
</template>