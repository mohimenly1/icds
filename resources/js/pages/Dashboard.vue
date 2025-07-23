<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '../layouts/AuthenticatedLayout.vue';

// تعريف الـ props القادمة من الـ Controller
const props = defineProps({
    doctors: Array,
    floors: Array,
    allRooms: Array,
});

const user = usePage().props.auth.user;
const page = usePage();

// =================================================================
// الإحصائيات
// =================================================================
const totalDoctors = computed(() => props.doctors?.length || 0);
const totalRooms = computed(() => props.allRooms?.length || 0);
const occupiedRooms = computed(() => props.allRooms?.filter(r => r.doctor).length || 0);
const availableDoctors = computed(() => props.doctors?.filter(d => d.status === 'available').length || 0);

const stats = [
    { name: 'إجمالي الأطباء', value: totalDoctors.value, icon: 'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2' },
    { name: 'الأطباء المتوفرون', value: availableDoctors.value, icon: 'M9 12l2 2 4-4' },
    { name: 'إجمالي الغرف', value: totalRooms.value, icon: 'M22 11.08V12a10 10 0 1 1-5.93-9.14' },
    { name: 'الغرف المشغولة', value: occupiedRooms.value, icon: 'M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z' },
];

// =================================================================
// منطق النماذج والنوافذ المنبثقة (Modals)
// =================================================================
const showModal = ref(false);
const modalMode = ref('addDoctor');
const editingId = ref(null);

const successMessage = computed(() => page.props.flash?.success);
const errorMessage = computed(() => page.props.flash?.error);

const doctorForm = useForm({ name: '', specialty: '', status: 'available', room_id: null, photo_url: '', bio: '' });
const floorForm = useForm({ name: '', level: 0 });
const roomForm = useForm({ floor_id: null, room_number: '', name: '' });

const availableRooms = computed(() => {
    if (modalMode.value === 'editDoctor') {
        const currentDoctorRoomId = props.doctors.find(d => d.id === editingId.value)?.room_id;
        return props.allRooms.filter(room => !room.doctor || room.id === currentDoctorRoomId);
    }
    return props.allRooms.filter(room => !room.doctor);
});

const openModal = (mode, item = null) => {
    modalMode.value = mode;
    if (item) {
        editingId.value = item.id;
        if (mode.includes('Doctor')) doctorForm.defaults(item).reset();
        if (mode.includes('Floor')) floorForm.defaults(item).reset();
        if (mode.includes('Room')) roomForm.defaults(item).reset();
    } else {
        doctorForm.reset();
        floorForm.reset();
        roomForm.reset();
    }
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
};

const submitForm = () => {
    const options = { preserveScroll: true, onSuccess: () => closeModal() };
    switch (modalMode.value) {
        case 'addDoctor': doctorForm.post(route('doctors.store'), options); break;
        case 'addFloor': floorForm.post(route('floors.store'), options); break;
        case 'addRoom': roomForm.post(route('rooms.store'), options); break;
        // Note: Edit/Delete functionality will be on their respective management pages
    }
};

</script>

<template>
    <Head title="الرئيسية" />

    <AuthenticatedLayout>
        <!-- Flash Messages -->
        <div v-if="successMessage" class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 flex items-center gap-3" role="alert">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 2 2 4-4"/></svg>
            <span>{{ successMessage }}</span>
        </div>
        <div v-if="errorMessage" class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 flex items-center gap-3" role="alert">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
            <span>{{ errorMessage }}</span>
        </div>

        <div class="space-y-6">
            <!-- Welcome Header -->
            <div class="bg-white shadow rounded-lg p-6">
                <h1 class="text-2xl font-bold text-slate-800">مرحباً بعودتك، {{ user.name }}!</h1>
                <p class="text-slate-500 mt-1">هنا نظرة سريعة على حالة العيادة اليوم.</p>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div v-for="stat in stats" :key="stat.name" class="bg-white p-6 rounded-lg shadow flex items-center justify-between hover:shadow-lg transition-shadow">
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

            <!-- Quick Actions & Recent Activity -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 bg-white p-6 rounded-lg shadow">
                    <h3 class="text-lg font-semibold text-slate-800 mb-4">آخر الأطباء المسجلين</h3>
                    <ul v-if="doctors && doctors.length > 0" class="space-y-3">
                        <li v-for="doctor in doctors.slice(0, 5)" :key="doctor.id" class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <img :src="doctor.photo_url || 'https://placehold.co/40x40/E2E8F0/4A5568?text=Dr'" class="w-10 h-10 rounded-full object-cover">
                                <div>
                                    <p class="font-semibold text-slate-700">{{ doctor.name }}</p>
                                    <p class="text-sm text-slate-500">{{ doctor.specialty }}</p>
                                </div>
                            </div>
                            <span class="text-sm text-slate-400">غرفة {{ doctor.room?.room_number }}</span>
                        </li>
                    </ul>
                     <p v-else class="text-slate-500">لا يوجد أطباء لعرضهم.</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-lg font-semibold text-slate-800 mb-4">إجراءات سريعة</h3>
                    <div class="space-y-3">
                        <button @click="openModal('addDoctor')" class="w-full text-right bg-blue-50 hover:bg-blue-100 text-blue-700 font-medium p-3 rounded-lg transition-colors">إضافة طبيب جديد</button>
                        <button @click="openModal('addRoom')" class="w-full text-right bg-green-50 hover:bg-green-100 text-green-700 font-medium p-3 rounded-lg transition-colors">إضافة غرفة جديدة</button>
                        <button @click="openModal('addFloor')" class="w-full text-right bg-purple-50 hover:bg-purple-100 text-purple-700 font-medium p-3 rounded-lg transition-colors">إضافة طابق جديد</button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>

    <!-- Universal Modal -->
    <transition
        enter-active-class="ease-out duration-300"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="ease-in duration-200"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div v-if="showModal" class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center z-50" @click.self="closeModal">
            <transition
                enter-active-class="ease-out duration-300"
                enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                enter-to-class="opacity-100 translate-y-0 sm:scale-100"
                leave-active-class="ease-in duration-200"
                leave-from-class="opacity-100 translate-y-0 sm:scale-100"
                leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            >
                <div v-if="showModal" class="bg-white rounded-lg shadow-xl w-full max-w-2xl transform transition-all">
                    <form @submit.prevent="submitForm" class="text-right">
                        <!-- Modal Header -->
                        <div class="p-6 border-b border-slate-200">
                            <h3 class="text-xl font-semibold text-slate-800">
                                <span v-if="modalMode.includes('Doctor')">{{ modalMode === 'addDoctor' ? 'إضافة طبيب جديد' : 'تعديل طبيب' }}</span>
                                <span v-if="modalMode.includes('Floor')">{{ modalMode === 'addFloor' ? 'إضافة طابق جديد' : 'تعديل طابق' }}</span>
                                <span v-if="modalMode.includes('Room')">{{ modalMode === 'addRoom' ? 'إضافة غرفة جديدة' : 'تعديل غرفة' }}</span>
                            </h3>
                        </div>

                        <!-- Modal Body -->
                        <div class="p-6 space-y-4 max-h-[60vh] overflow-y-auto">
                            <!-- Doctor Form -->
                            <div v-if="modalMode.includes('Doctor')" class="space-y-4">
                                <div><label class="font-medium text-slate-700">الاسم</label><input v-model="doctorForm.name" class="w-full mt-1 rounded-lg border-slate-300 shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-200 focus:ring-opacity-50" /></div>
                                <div><label class="font-medium text-slate-700">التخصص</label><input v-model="doctorForm.specialty" class="w-full mt-1 rounded-lg border-slate-300 shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-200 focus:ring-opacity-50" /></div>
                                <div><label class="font-medium text-slate-700">الغرفة</label><select v-model="doctorForm.room_id" class="w-full mt-1 rounded-lg border-slate-300 shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-200 focus:ring-opacity-50"><option :value="null">-- اختر غرفة --</option><option v-for="room in availableRooms" :key="room.id" :value="room.id">{{ room.room_number }} ({{ floors.find(f => f.id === room.floor_id)?.name }})</option></select></div>
                                <div><label class="font-medium text-slate-700">الحالة</label><select v-model="doctorForm.status" class="w-full mt-1 rounded-lg border-slate-300 shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-200 focus:ring-opacity-50"><option value="available">متوفر</option><option value="busy">مشغول</option><option value="away">غير موجود</option></select></div>
                                <div><label class="font-medium text-slate-700">رابط الصورة</label><input v-model="doctorForm.photo_url" type="url" class="w-full mt-1 rounded-lg border-slate-300 shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-200 focus:ring-opacity-50" /></div>
                                <div><label class="font-medium text-slate-700">نبذة</label><textarea v-model="doctorForm.bio" class="w-full mt-1 rounded-lg border-slate-300 shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-200 focus:ring-opacity-50"></textarea></div>
                            </div>
                            <!-- Floor Form -->
                            <div v-if="modalMode.includes('Floor')" class="space-y-4">
                                <div><label class="font-medium text-slate-700">اسم الطابق</label><input v-model="floorForm.name" class="w-full mt-1 rounded-lg border-slate-300 shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-200 focus:ring-opacity-50" /></div>
                                <div><label class="font-medium text-slate-700">المستوى (رقم)</label><input v-model="floorForm.level" type="number" class="w-full mt-1 rounded-lg border-slate-300 shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-200 focus:ring-opacity-50" /></div>
                            </div>
                            <!-- Room Form -->
                            <div v-if="modalMode.includes('Room')" class="space-y-4">
                                <div><label class="font-medium text-slate-700">الطابق</label><select v-model="roomForm.floor_id" class="w-full mt-1 rounded-lg border-slate-300 shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-200 focus:ring-opacity-50"><option :value="null">-- اختر طابق --</option><option v-for="floor in floors" :key="floor.id" :value="floor.id">{{ floor.name }}</option></select></div>
                                <div><label class="font-medium text-slate-700">رقم الغرفة</label><input v-model="roomForm.room_number" class="w-full mt-1 rounded-lg border-slate-300 shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-200 focus:ring-opacity-50" /></div>
                                <div><label class="font-medium text-slate-700">اسم الغرفة (اختياري)</label><input v-model="roomForm.name" class="w-full mt-1 rounded-lg border-slate-300 shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-200 focus:ring-opacity-50" /></div>
                            </div>
                        </div>

                        <!-- Modal Footer -->
                        <div class="flex justify-end space-x-2 rtl:space-x-reverse p-6 bg-slate-50 rounded-b-lg">
                            <button type="button" @click="closeModal" class="px-5 py-2 bg-slate-200 text-slate-700 font-medium rounded-lg hover:bg-slate-300 transition-colors">إلغاء</button>
                            <button type="submit" :disabled="doctorForm.processing || floorForm.processing || roomForm.processing" class="px-5 py-2 bg-teal-600 text-white font-medium rounded-lg hover:bg-teal-700 transition-colors disabled:bg-teal-300 disabled:cursor-not-allowed">حفظ</button>
                        </div>
                    </form>
                </div>
            </transition>
        </div>
    </transition>
</template>
