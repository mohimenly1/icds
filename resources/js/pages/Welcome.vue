<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
// استيراد المكونات كملفات مستقلة
import Screensaver from './Screensaver.vue';
import Heartbeat from './Heartbeat.vue'; // <-- 1. استيراد المكون الجديد
import AIMascot from '@/components/AIMascot.vue'; // <-- 1. استيراد شخصية الأنيميشن


// =================================================================
// Main Component Logic
// =================================================================

const loading = ref(true);
const error = ref(null);
const allData = ref({
    doctors: [],
    floors: [],
    departments: [],
});

const selectedDoctor = ref(null);
const selectedDepartment = ref(null);

const daysOfWeek = [
    { id: 0, name: 'الأحد' }, { id: 1, name: 'الإثنين' }, { id: 2, name: 'الثلاثاء' },
    { id: 3, name: 'الأربعاء' }, { id: 4, name: 'الخميس' }, { id: 5, name: 'الجمعة' },
    { id: 6, name: 'السبت' },
];

// Icons for departments
const departmentIcons = {
  'أسنان': '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 21a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h1a2 2 0 0 0 0-4H8a2 2 0 0 0-2 2v14a4 4 0 0 0 4 4h4a2 2 0 0 0 2-2v-5a2 2 0 0 0-2-2h-1a2 2 0 0 1 0-4h1a2 2 0 0 1 2 2v5a4 4 0 0 1-4 4H7Z"/></svg>',
  'أطفال': '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 12h.01"/><path d="M10 12h.01"/><path d="M12 18h.01"/><path d="M12 6h.01"/><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 12Z"/></svg>',
  'عظام': '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.5 6.5a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0Z"/><path d="M6.5 17.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z"/><path d="m14 14-2.5 2.5"/><path d="m10 10-2.5 2.5"/><path d="m14 10-4 4"/><path d="M9.5 6.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z"/></svg>',
  'جلدية': '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 12.5a4.5 4.5 0 1 1 4.5-4.5 4.5 4.5 0 0 1-4.5 4.5Z"/><path d="M19.5 19.5 16 16"/><path d="M22 12c0 4.5-4 8-8 8s-8-3.5-8-8 4-8 8-8"/></svg>',
  'باطنية': '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10Z"/><path d="m9 12 2 2 4-4"/></svg>',
  'default': '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/></svg>'
};

const departmentsWithIcons = computed(() => {
    return allData.value.departments.map(dept => ({
        ...dept,
        icon: departmentIcons[dept.name] || departmentIcons['default']
    }));
});


function handleAiAction(action) {
  console.log("Action received from AI Mascot:", action);
  
  if (action && action.type === 'SELECT_DOCTOR' && action.payload.id) {
    // ابحث عن الطبيب المطلوب في قائمة الأطباء
    const doctorToSelect = allData.value.doctors.find(d => d.id === action.payload.id);
    
    if (doctorToSelect) {
      console.log("Found doctor to select:", doctorToSelect.name);
      // استدعاء دالة اختيار الطبيب الموجودة بالفعل
      selectDoctor(doctorToSelect);
    } else {
      console.warn(`Doctor with ID ${action.payload.id} not found.`);
    }
  }
}

const filteredDoctors = computed(() => {
    if (!selectedDepartment.value) {
        return allData.value.doctors;
    }
    return allData.value.doctors.filter(d => d.specialty === selectedDepartment.value.name);
});

const doctorSchedule = computed(() => {
    if (!selectedDoctor.value) return [];
    return daysOfWeek.map(day => {
        const schedule = selectedDoctor.value.schedules.find(s => s.day_of_week === day.id);
        return {
            name: day.name,
            time: schedule ? `${schedule.start_time.slice(0, 5)} - ${schedule.end_time.slice(0, 5)}` : 'راحة',
            isWorkDay: !!schedule,
        };
    });
});

async function fetchData() {
    loading.value = true;
    error.value = null;
    try {
        const response = await fetch('/api/clinic-data');
        if (!response.ok) throw new Error('فشل تحميل البيانات');
        allData.value = await response.json();
    } catch (e) {
        error.value = e.message;
        console.error(e);
    } finally {
        loading.value = false;
    }
}

function selectDoctor(doctor) {
    selectedDoctor.value = doctor;
}

function selectDepartment(department) {
    selectedDepartment.value = department;
    selectedDoctor.value = null; // Deselect doctor when changing department
}

function clearSelection() {
    selectedDoctor.value = null;
    selectedDepartment.value = null;
}

// --- Idle Timer Logic ---
const isIdle = ref(false);
let idleTimeout = null;

const promoImages = ref([
  '/assets/1.jpg',
  '/assets/2.jpg',
  '/assets/3.jpg',
  '/assets/4.jpg',
  '/assets/5.jpg',
  '/assets/6.jpg',
  '/assets/7.jpg',
]);

function resetIdleTimer() {
  if (isIdle.value) isIdle.value = false;
  clearTimeout(idleTimeout);
  idleTimeout = setTimeout(() => {
    isIdle.value = true;
    clearSelection();
  }, 60000); // 60 seconds
}

onMounted(() => {
  fetchData();
  resetIdleTimer();
  ['mousemove', 'mousedown', 'keypress', 'touchmove'].forEach(evt => window.addEventListener(evt, resetIdleTimer));
});

onUnmounted(() => {
  clearTimeout(idleTimeout);
  ['mousemove', 'mousedown', 'keypress', 'touchmove'].forEach(evt => window.removeEventListener(evt, resetIdleTimer));
});
</script>

<template>
    <div class="w-screen h-screen bg-slate-200 font-tajwal overflow-hidden" dir="rtl">
        <AIMascot v-if="!loading" :clinicData="allData" @perform-action="handleAiAction" />
        <!-- Screensaver -->
        <div v-if="isIdle" @click="resetIdleTimer" class="w-full h-full cursor-pointer">
            <Screensaver :images="promoImages" />
        </div>

        <!-- Main Interface -->
        <div v-else class="flex h-full w-full">
            <!-- Right Sidebar: Navigation & Lists -->
            <aside class="w-1/3 bg-white h-full flex flex-col shadow-2xl">
                <header class="p-6 border-b border-slate-200 text-right">
                    <h1 class="text-3xl font-extrabold text-teal-600">دليل العيادة</h1>
                    <p class="text-slate-500 mt-1">الأقسام والأطباء</p>
                </header>

                <!-- Departments -->
                <div class="p-6 text-right">
                    <h2 class="text-sm font-bold uppercase text-slate-400 mb-4 text-center">الأقسام</h2>
                    <div class="grid grid-cols-3 gap-4">
                        <!-- All Departments button -->
                        <div @click="selectDepartment(null)" class="flex flex-col items-center gap-2 cursor-pointer group">
                            <div :class="[selectedDepartment === null ? 'bg-teal-600 text-white ring-4 ring-teal-200' : 'bg-slate-100 text-slate-700 group-hover:bg-teal-100', 'w-20 h-20 rounded-full flex items-center justify-center transition-all duration-300 transform group-hover:scale-110']">
                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
                            </div>
                            <p :class="[selectedDepartment === null ? 'text-teal-600 font-bold' : 'text-slate-600', 'text-sm transition-colors text-center']">كل الأقسام</p>
                        </div>
                        <!-- Department buttons -->
                        <div v-for="dept in departmentsWithIcons" :key="dept.name" @click="selectDepartment(dept)" class="flex flex-col items-center gap-2 cursor-pointer group">
                             <div :class="[selectedDepartment?.name === dept.name ? 'bg-teal-600 text-white ring-4 ring-teal-200' : 'bg-slate-100 text-slate-700 group-hover:bg-teal-100', 'w-20 h-20 rounded-full flex items-center justify-center transition-all duration-300 transform group-hover:scale-110']" v-html="dept.icon">
                            </div>
                            <p :class="[selectedDepartment?.name === dept.name ? 'text-teal-600 font-bold' : 'text-slate-600', 'text-sm transition-colors text-center']">{{ dept.name }}</p>
                        </div>
                    </div>
                </div>

                <!-- Doctors List -->
                <div class="flex-grow overflow-y-auto px-6 pb-6 border-t border-slate-200 pt-4">
                     <div v-if="loading" class="text-center pt-10 text-slate-500">جاري التحميل...</div>
                     <div v-else-if="error" class="text-center pt-10 text-red-500">{{ error }}</div>
                     <ul v-else class="space-y-3">
                        <li v-for="doctor in filteredDoctors" :key="doctor.id" @click="selectDoctor(doctor)"
                            :class="[selectedDoctor?.id === doctor.id ? 'bg-teal-50 border-teal-500' : 'bg-white hover:bg-slate-50 border-transparent', 'p-4 rounded-lg border-2 cursor-pointer transition-all duration-200 flex items-center gap-4 text-right']">
                            <img :src="doctor.photo_url || 'https://placehold.co/64x64/E2E8F0/4A5568?text=Dr'" class="w-16 h-16 rounded-full object-cover">
                            <div>
                                <h3 class="font-bold text-lg text-slate-800">{{ doctor.name }}</h3>
                                <p class="text-slate-500">{{ doctor.specialty }}</p>
                            </div>
                        </li>
                     </ul>
                </div>
            </aside>

            <!-- Left Panel: Content Display -->
            <main class="w-2/3 h-full p-10 flex items-center justify-center">
                <transition name="fade" mode="out-in">
                    <!-- Welcome Message -->
                    <div v-if="!selectedDoctor" class="text-center">
                        <Heartbeat />
                    </div>
                    
                    <!-- Doctor Profile -->
                    <div v-else class="w-full max-w-4xl bg-white rounded-2xl shadow-2xl p-8 animate-fade-in text-right">
                        <header class="flex items-center gap-6 border-b border-slate-200 pb-6 mb-6">
                            <img :src="selectedDoctor.photo_url || 'https://placehold.co/128x128/E2E8F0/4A5568?text=Dr'" class="w-32 h-32 rounded-full object-cover border-4 border-white shadow-lg">
                            <div>
                                <h2 class="text-4xl font-extrabold text-slate-800">{{ selectedDoctor.name }}</h2>
                                <p class="text-2xl text-teal-600 font-semibold">{{ selectedDoctor.specialty }}</p>
                                <span class="mt-2 px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full" :class="selectedDoctor.status === 'available' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                                    {{ selectedDoctor.status === 'available' ? 'متوفر الآن' : 'غير متوفر' }}
                                </span>
                            </div>
                        </header>
                        
                        <!-- Rooms -->
                        <div class="mb-6">
                             <h3 class="text-lg font-bold text-slate-600 mb-3">الغرف</h3>
                             <div v-if="selectedDoctor.rooms.length > 0" class="flex flex-wrap gap-3 justify-end">
                                <div v-for="room in selectedDoctor.rooms" :key="room.id" class="bg-slate-100 p-3 rounded-lg">
                                    <p class="font-bold text-slate-800">غرفة {{ room.room_number }}</p>
                                    <p class="text-sm text-slate-500">{{ room.floor.name }}</p>
                                </div>
                             </div>
                             <p v-else class="text-slate-500">لم يتم تحديد غرفة</p>
                        </div>

                        <!-- Weekly Schedule -->
                        <div>
                            <h3 class="text-lg font-bold text-slate-600 mb-3">جدول التواجد الأسبوعي</h3>
                            <div class="grid grid-cols-7 gap-2 text-center">
                                <div v-for="day in doctorSchedule" :key="day.name" class="p-3 rounded-lg" :class="day.isWorkDay ? 'bg-teal-50' : 'bg-slate-100'">
                                    <p class="font-bold text-slate-800">{{ day.name }}</p>
                                    <p class="mt-1 font-mono text-sm" :class="day.isWorkDay ? 'text-teal-700' : 'text-slate-400'">{{ day.time }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </transition>
            </main>
        </div>
    </div>

</template>
<style>
@import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@200;300;400;500;700;800;900&display=swap');
.font-tajwal {
    font-family: 'Tajawal', sans-serif;
}
.fade-enter-active, .fade-leave-active { transition: opacity 0.5s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
.animate-fade-in { animation: fadeIn 0.5s ease-in-out; }
@keyframes fadeIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
</style>