<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
// 1. استيراد المكون بالطريقة الصحيحة من 'vue3-lottie'
import { Vue3Lottie } from 'vue3-lottie';
import DoctorAnimationData from '../../../public/assets/Doctor.json';

const isVisible = ref(false);
let timer = null;

const showMascot = () => {
  isVisible.value = true;
  setTimeout(() => {
    isVisible.value = false;
  }, 7000); 
};

onMounted(() => {
  setTimeout(showMascot, 5000);
  timer = setInterval(showMascot, 30000);
});

onUnmounted(() => {
  if (timer) {
    clearInterval(timer);
  }
});
</script>

<template>
  <div class="mascot-container" :class="{ 'is-visible': isVisible }">
    <Vue3Lottie
      :animationData="DoctorAnimationData"
      :loop="true"
      :autoPlay="true"
      :speed="1"
      width="100%"
      height="100%"
    />
  </div>
</template>

<style scoped>
.mascot-container {
  position: fixed;
  bottom: 20px;
  right: -300px; 
  width: 280px;
  height: auto;
  z-index: 1000;
  transition: transform 1s cubic-bezier(0.68, -0.55, 0.27, 1.55);
}

.mascot-container.is-visible {
  transform: translateX(-320px);
}
</style>