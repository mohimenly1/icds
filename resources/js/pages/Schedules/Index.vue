<script setup>
import { ref, watch,computed } from 'vue';
import { Head, useForm,usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '../../layouts/AuthenticatedLayout.vue';

const props = defineProps({
    doctors: Array,
    schedules: Array,
});

const page = usePage();
const viewMode = ref('view'); // 'view' or 'edit'

// --- Edit Mode Logic ---
const selectedDoctorId = ref(props.doctors[0]?.id || null);
const form = useForm({
    doctor_id: selectedDoctorId.value,
    days: [],
});

const daysOfWeek = [
    { id: 0, name: 'الأحد' }, { id: 1, name: 'الإثنين' }, { id: 2, name: 'الثلاثاء' },
    { id: 3, name: 'الأربعاء' }, { id: 4, name: 'الخميس' }, { id: 5, name: 'الجمعة' },
    { id: 6, name: 'السبت' },
];

const initializeFormDays = (doctorId) => {
    const doctorSchedules = props.schedules.filter(s => s.doctor_id === doctorId);
    form.days = daysOfWeek.map(day => {
        const schedule = doctorSchedules.find(s => s.day_of_week === day.id);
        return {
            day_of_week: day.id,
            name: day.name,
            active: !!schedule,
            start_time: schedule ? schedule.start_time.slice(0, 5) : '09:00',
            end_time: schedule ? schedule.end_time.slice(0, 5) : '17:00',
        };
    });
};

watch(selectedDoctorId, (newDoctorId) => {
    form.doctor_id = newDoctorId;
    initializeFormDays(newDoctorId);
}, { immediate: true });

const submit = () => {
    form.post(route('schedules.store'), {
        preserveScroll: true,
    });
};

// --- View Mode Logic ---
const doctorsWithSchedules = computed(() => {
    return props.doctors.map(doctor => {
        const schedule = {};
        const doctorSchedules = props.schedules.filter(s => s.doctor_id === doctor.id);
        doctorSchedules.forEach(s => {
            const startTime = s.start_time.slice(0, 5);
            const endTime = s.end_time.slice(0, 5);
            schedule[s.day_of_week] = `${startTime} - ${endTime}`;
        });
        return {
            ...doctor,
            schedule,
        };
    });
});

const successMessage = computed(() => page.props.flash?.success);

</script>

<template>
    <Head title="جداول الأطباء" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">إدارة جداول الأطباء الأسبوعية</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Flash Message -->
                <div v-if="successMessage" class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4" role="alert">{{ successMessage }}</div>

                <!-- Tabs -->
                <div class="mb-4 border-b border-gray-200">
                    <nav class="-mb-px flex space-x-4 rtl:space-x-reverse" aria-label="Tabs">
                        <button @click="viewMode = 'view'" :class="[viewMode === 'view' ? 'border-teal-500 text-teal-600' : 'border-transparent text-gray-500 hover:text-gray-700', 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm']">عرض الجداول</button>
                        <button @click="viewMode = 'edit'" :class="[viewMode === 'edit' ? 'border-teal-500 text-teal-600' : 'border-transparent text-gray-500 hover:text-gray-700', 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm']">تعديل الجداول</button>
                    </nav>
                </div>

                <!-- View Mode: Display Table -->
                <div v-if="viewMode === 'view'">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                             <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">الطبيب</th>
                                        <th v-for="day in daysOfWeek" :key="day.id" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">{{ day.name }}</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="doctor in doctorsWithSchedules" :key="doctor.id">
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ doctor.name }}</div>
                                            <div class="text-sm text-gray-500">{{ doctor.specialty }}</div>
                                        </td>
                                        <td v-for="day in daysOfWeek" :key="day.id" class="px-4 py-4 whitespace-nowrap text-center text-sm">
                                            <span v-if="doctor.schedule[day.id]" class="px-2 py-1 bg-teal-100 text-teal-800 rounded-full font-mono">
                                                {{ doctor.schedule[day.id] }}
                                            </span>
                                            <span v-else class="text-gray-400">راحة</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Edit Mode: Form -->
                <div v-if="viewMode === 'edit'">
                     <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="mb-6">
                                <label for="doctor" class="block text-lg font-medium text-slate-700">اختر طبيبًا لتعديل جدوله:</label>
                                <select id="doctor" v-model="selectedDoctorId" class="w-full mt-1 rounded-lg border-slate-300 shadow-sm text-slate-800 focus:border-teal-500 focus:ring focus:ring-teal-200 focus:ring-opacity-50">
                                    <option v-for="doctor in doctors" :key="doctor.id" :value="doctor.id">
                                        {{ doctor.name }}
                                    </option>
                                </select>
                            </div>

                            <form v-if="selectedDoctorId" @submit.prevent="submit">
                                <div class="space-y-4">
                                    <div v-for="(day, index) in form.days" :key="day.day_of_week" class="grid grid-cols-12 items-center gap-4 p-3 rounded-lg" :class="day.active ? 'bg-teal-50' : 'bg-slate-50'">
                                        <div class="col-span-3">
                                            <label :for="'active-' + index" class="flex items-center cursor-pointer">
                                                <input type="checkbox" v-model="day.active" :id="'active-' + index" class="h-5 w-5 rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                                                <span class="ml-3 mr-3 font-bold text-slate-800">{{ day.name }}</span>
                                            </label>
                                        </div>
                                        <div class="col-span-9 grid grid-cols-2 gap-4" :class="{ 'opacity-50': !day.active }">
                                            <div>
                                                <label :for="'start-' + index" class="text-sm text-slate-600">وقت البدء</label>
                                                <input type="time" v-model="day.start_time" :id="'start-' + index" :disabled="!day.active" class="mt-1 w-full rounded-md border-slate-300 text-slate-800">
                                            </div>
                                            <div>
                                                <label :for="'end-' + index" class="text-sm text-slate-600">وقت الانتهاء</label>
                                                <input type="time" v-model="day.end_time" :id="'end-' + index" :disabled="!day.active" class="mt-1 w-full rounded-md border-slate-300 text-slate-800">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-8 flex justify-end">
                                    <button type="submit" :disabled="form.processing" class="px-6 py-2 bg-teal-600 text-white rounded-lg shadow hover:bg-teal-700 disabled:bg-teal-300">
                                        حفظ التغييرات
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
