// resources/js/components/DoctorsDirectory.vue
<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const doctors = ref([]);
const isLoading = ref(true);

onMounted(async () => {
  try {
    const response = await axios.get('/api/doctors');
    doctors.value = response.data;
  } catch (error) {
    console.error("Failed to fetch doctors:", error);
  } finally {
    isLoading.value = false;
  }
});

const statusClasses = {
  available: 'bg-green-100 text-green-800',
  busy: 'bg-red-100 text-red-800',
  away: 'bg-yellow-100 text-yellow-800',
};

const statusText = {
  available: 'متوفر',
  busy: 'مشغول',
  away: 'خارج المكتب',
}
</script>

<template>
  <div>
    <h1 class="text-4xl font-bold text-brand-text mb-8">دليل الأطباء</h1>
    
    <div v-if="isLoading" class="text-center">
      <p>جاري تحميل البيانات...</p>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
      <div v-for="doctor in doctors" :key="doctor.id" class="bg-white rounded-xl shadow-md p-6 flex flex-col items-center text-center transition-transform duration-300 hover:scale-105">
        <img class="w-32 h-32 rounded-full object-cover mb-4 ring-4 ring-brand-primary/20" :src="doctor.photo_url || `https://ui-avatars.com/api/?name=${doctor.name}&background=3B82F6&color=fff&size=128`" :alt="doctor.name">
        <h2 class="text-2xl font-bold text-brand-text">{{ doctor.name }}</h2>
        <p class="text-brand-primary font-semibold mb-2">{{ doctor.specialty }}</p>
        <p class="text-gray-600 mb-4 flex-grow">{{ doctor.bio }}</p>
        <span class="px-3 py-1 text-sm font-medium rounded-full" :class="statusClasses[doctor.status]">
          {{ statusText[doctor.status] }}
        </span>
      </div>
    </div>
  </div>
</template>