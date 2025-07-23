// resources/js/components/InteractiveMap.vue
<script setup>
import { onMounted, ref } from 'vue';

const mapContainer = ref(null);

onMounted(() => {
  // هنا، في المستقبل القريب، سنقوم بتهيئة Three.js
  console.log("InteractiveMap component is mounted. Ready for 3D!");
});
</script>

<template>
  <div>
    <h1 class="text-4xl font-bold text-brand-text mb-4">خريطة العيادة التفاعلية</h1>
    <div ref="mapContainer" class="w-full h-[600px] bg-gray-200 rounded-xl flex items-center justify-center">
      <p class="text-gray-500 text-2xl">3D Map Will Be Rendered Here</p>
    </div>
  </div>
</template>