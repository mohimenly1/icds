<template>
    <div class="w-full max-w-4xl h-[500px] bg-slate-100 rounded-2xl p-6 border-2 border-slate-200 flex flex-col">
      <div class="flex justify-between items-center mb-4">
        <div class="flex items-center gap-3 bg-white p-3 rounded-lg shadow-sm">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-emerald-500"><path d="M16 20V4a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/><path d="M12 20h4"/><path d="M12 20H8"/><path d="M10 4v4h4V4"/></svg>
          <span class="font-bold text-slate-700">الاستقبال</span>
        </div>
        <div class="flex items-center gap-3 bg-white p-3 rounded-lg shadow-sm">
           <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-amber-500"><path d="M4.5 12.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z"/><path d="M7 15v-5h10v5"/><path d="M10.5 12.5a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0Z"/><path d="M17.5 12.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z"/></svg>
          <span class="font-bold text-slate-700">دورات المياه</span>
        </div>
        <div class="flex items-center gap-3 bg-white p-3 rounded-lg shadow-sm">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-sky-500"><path d="M8 2v4"/><path d="M16 2v4"/><path d="M21 17V6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v11a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2Z"/><path d="M3 10h18"/><path d="m10 16 2 2 4-4"/></svg>
          <span class="font-bold text-slate-700">الصيدلية</span>
        </div>
      </div>
      
      <div class="flex-grow bg-slate-200/70 h-full rounded-lg flex items-center justify-center p-4">
        <!-- Hallway -->
        <div class="w-full h-full grid grid-cols-10 gap-4">
          <!-- Rooms on the left -->
          <div class="col-span-4 flex flex-col gap-4">
            <template v-for="(doctor, index) in doctors.slice(0, Math.ceil(doctors.length / 2))" :key="doctor.id">
              <div @click="emit('select-room', doctor.id)"
                   :class="getRoomClass(doctor)">
                <span class="font-bold text-sm md:text-base">{{ doctor.name }}</span>
                <span class="text-xs md:text-sm text-slate-500">غرفة {{ doctor.room_number || index + 1 }}</span>
              </div>
            </template>
          </div>
          
          <!-- Hallway space -->
          <div class="col-span-2"></div>
          
          <!-- Rooms on the right -->
          <div class="col-span-4 flex flex-col gap-4">
             <template v-for="(doctor, index) in doctors.slice(Math.ceil(doctors.length / 2))" :key="doctor.id">
              <div @click="emit('select-room', doctor.id)"
                   :class="getRoomClass(doctor)">
                <span class="font-bold text-sm md:text-base">{{ doctor.name }}</span>
                <span class="text-xs md:text-sm text-slate-500">غرفة {{ doctor.room_number || index + Math.ceil(doctors.length / 2) + 1 }}</span>
              </div>
            </template>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script setup>
  const props = defineProps({
    doctors: Array,
    selectedDoctor: Object
  });
  
  const emit = defineEmits(['select-room']);
  
  const getRoomClass = (doctor) => {
    const baseClass = 'bg-white rounded-lg p-3 flex flex-col items-center justify-center text-center h-full cursor-pointer transition-all duration-300 border-2';
    const isSelected = props.selectedDoctor && props.selectedDoctor.id === doctor.id;
    
    if (isSelected) {
      return `${baseClass} border-teal-500 bg-teal-50 scale-105 shadow-lg`;
    }
    
    return `${baseClass} border-slate-300 hover:border-teal-400 hover:shadow-md`;
  };
  </script>
  