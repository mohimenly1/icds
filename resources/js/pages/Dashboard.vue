<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '../layouts/AuthenticatedLayout.vue';

// تعريف الـ props القادمة من الـ Controller
const props = defineProps({
    stats: Object,
    latestDoctors: Array,
});

const page = usePage();
const user = page.props.auth.user;
const roles = page.props.auth.roles;

const successMessage = computed(() => page.props.flash?.success);
const errorMessage = computed(() => page.props.flash?.error);

// =================================================================
// الإحصائيات
// =================================================================
const generalStats = [
    { name: 'إجمالي الأطباء', value: props.stats.totalDoctors, icon: 'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2' },
    { name: 'الأطباء المتوفرون', value: props.stats.availableDoctors, icon: 'M9 12l2 2 4-4' },
    { name: 'إجمالي الغرف', value: props.stats.totalRooms, icon: 'M22 11.08V12a10 10 0 1 1-5.93-9.14' },
    { name: 'الغرف المشغولة', value: props.stats.occupiedRooms, icon: 'M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z' },
];

// إحصائيات مبدئية لمدير الشاشات
const screenStats = [
    { name: 'إجمالي الشاشات', value: 0, icon: 'M12 18v-6M12 6h.01' }, // Placeholder
    { name: 'الشاشات النشطة', value: 0, icon: 'M21 12.79a9 9 0 1 1-11.21-1.21C7.52 10.16 10.16 7.52 12.79 6H21Z' }, // Placeholder
    { name: 'إعلانات موحدة', value: 'لا', icon: 'M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9' }, // Placeholder
    { name: 'آخر تحديث', value: 'الآن', icon: 'M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z' }, // Placeholder
];

</script>

<template>
    <Head title="الرئيسية" />

    <AuthenticatedLayout>
        <!-- Flash Messages -->
        <div v-if="successMessage" class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4" role="alert">{{ successMessage }}</div>
        <div v-if="errorMessage" class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert">{{ errorMessage }}</div>

        <!-- Super Admin & General View -->
        <div v-if="!roles.includes('Screen-Manager') || roles.includes('Super-Admin')" class="space-y-6">
            <div class="bg-white shadow rounded-lg p-6">
                <h1 class="text-2xl font-bold text-slate-800">مرحباً بعودتك، {{ user.name }}!</h1>
                <p class="text-slate-500 mt-1">هنا نظرة سريعة على حالة العيادة اليوم.</p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div v-for="stat in generalStats" :key="stat.name" class="bg-white p-6 rounded-lg shadow flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">{{ stat.name }}</p>
                        <p class="text-3xl font-bold text-slate-900">{{ stat.value }}</p>
                    </div>
                     <div class="bg-teal-100 text-teal-600 p-3 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                           <path :d="stat.icon" />
                        </svg>
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 bg-white p-6 rounded-lg shadow">
                    <h3 class="text-lg font-semibold text-slate-800 mb-4">آخر الأطباء المسجلين</h3>
                    <ul v-if="latestDoctors && latestDoctors.length > 0" class="space-y-3">
                        <li v-for="doctor in latestDoctors" :key="doctor.id" class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <img :src="doctor.photo_url || 'https://placehold.co/40x40/E2E8F0/4A5568?text=Dr'" class="w-10 h-10 rounded-full object-cover">
                                <div>
                                    <p class="font-semibold text-slate-700">{{ doctor.name }}</p>
                                    <p class="text-sm text-slate-500">{{ doctor.specialty }}</p>
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-1 justify-end">
                                <span v-for="room in doctor.rooms" :key="room.id" class="px-2 py-1 bg-slate-200 text-slate-700 rounded-full text-xs">
                                    {{ room.room_number }}
                                </span>
                            </div>
                        </li>
                    </ul>
                     <p v-else class="text-slate-500">لا يوجد أطباء لعرضهم.</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-lg font-semibold text-slate-800 mb-4">إجراءات سريعة</h3>
                    <div class="space-y-3">
                        <Link :href="route('doctors.index')" class="block w-full text-right bg-blue-50 hover:bg-blue-100 text-blue-700 font-medium p-3 rounded-lg">إدارة الأطباء</Link>
                        <Link :href="route('clinic-structure.index')" class="block w-full text-right bg-green-50 hover:bg-green-100 text-green-700 font-medium p-3 rounded-lg">إدارة الغرف والطوابق</Link>
                        <Link :href="route('schedules.index')" class="block w-full text-right bg-purple-50 hover:bg-purple-100 text-purple-700 font-medium p-3 rounded-lg">إدارة جداول الأطباء</Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Screen Manager View -->
        <div v-else class="space-y-6">
             <div class="bg-white shadow rounded-lg p-6">
                <h1 class="text-2xl font-bold text-slate-800">مرحباً بك، {{ user.name }}!</h1>
                <p class="text-slate-500 mt-1">هنا لوحة التحكم الخاصة بإدارة الشاشات الإعلانية.</p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div v-for="stat in screenStats" :key="stat.name" class="bg-white p-6 rounded-lg shadow">
                    <div>
                        <p class="text-sm font-medium text-slate-500">{{ stat.name }}</p>
                        <p class="text-3xl font-bold text-slate-900">{{ stat.value }}</p>
                    </div>
                </div>
            </div>
             <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold text-slate-800 mb-4">إجراءات سريعة</h3>
                <div class="space-y-3">
                    <Link :href="route('screens.index')" class="block w-full text-right bg-blue-50 hover:bg-blue-100 text-blue-700 font-medium p-3 rounded-lg">الانتقال إلى إدارة الشاشات</Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
