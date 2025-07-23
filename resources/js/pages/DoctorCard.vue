<template>
    <div :class="[
      'p-4 rounded-xl flex items-center gap-4 transition-all duration-300',
      isSelected ? 'bg-white shadow-xl border-l-8 border-teal-500' : 'bg-white shadow-md hover:shadow-lg'
    ]">
      <img 
        :src="doctor.photo_url" 
        @error="onImageError" 
        class="w-24 h-24 rounded-full object-cover border-4"
        :class="isSelected ? 'border-teal-200' : 'border-slate-200'"
      >
      <div class="flex-1">
        <h3 class="text-xl font-bold text-slate-800">{{ doctor.name }}</h3>
        <p class="text-teal-600 font-semibold">{{ doctor.specialty }}</p>
        <div class="mt-2 px-3 py-1 text-sm rounded-full inline-block"
             :class="doctor.status === 'available' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
          {{ doctor.status === 'available' ? 'متوفر' : 'غير متوفر' }}
        </div>
      </div>
    </div>
  </template>
  
  <script setup>
  defineProps({
    doctor: Object,
    isSelected: Boolean
  });
  
  const onImageError = (event) => {
    event.target.src = 'https://placehold.co/100x100/E2E8F0/4A5568?text=Dr';
  };
  </script>
  