<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '../../layouts/AuthenticatedLayout.vue';

const props = defineProps({
    floors: Array,
    allRooms: Array,
});

const page = usePage();
const activeTab = ref('floors'); // 'floors' or 'rooms'
const showModal = ref(false);
const modalMode = ref('addFloor');
const editingId = ref(null);

const floorForm = useForm({ name: '', level: 0 });
const roomForm = useForm({ floor_id: null, room_number: '', name: '' });

const successMessage = computed(() => page.props.flash?.success);
const errorMessage = computed(() => page.props.flash?.error);

const openModal = (mode, item = null) => {
    modalMode.value = mode;
    if (item) {
        editingId.value = item.id;
        if (mode.includes('Floor')) floorForm.defaults(item).reset();
        if (mode.includes('Room')) roomForm.defaults(item).reset();
    } else {
        floorForm.reset();
        roomForm.reset();
    }
    showModal.value = true;
};

const closeModal = () => showModal.value = false;

const submitForm = () => {
    const options = { preserveScroll: true, onSuccess: () => closeModal() };
    switch (modalMode.value) {
        case 'addFloor': floorForm.post(route('floors.store'), options); break;
        case 'editFloor': floorForm.put(route('floors.update', editingId.value), options); break;
        case 'addRoom': roomForm.post(route('rooms.store'), options); break;
        case 'editRoom': roomForm.put(route('rooms.update', editingId.value), options); break;
    }
};

const deleteItem = (type, item) => {
    const name = type === 'floors' ? item.name : item.room_number;
    if (confirm(`هل أنت متأكد من حذف '${name}'؟`)) {
        useForm({}).delete(route(`${type}.destroy`, item.id), { preserveScroll: true });
    }
};
</script>

<template>
    <Head title="إدارة الغرف والطوابق" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">إدارة هيكل العيادة</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Flash Messages -->
                <div v-if="successMessage" class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4">{{ successMessage }}</div>
                <div v-if="errorMessage" class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4">{{ errorMessage }}</div>
                
                <!-- Tabs -->
                <div class="mb-4 border-b border-gray-200">
                    <nav class="-mb-px flex space-x-4 rtl:space-x-reverse" aria-label="Tabs">
                        <button @click="activeTab = 'floors'" :class="[activeTab === 'floors' ? 'border-teal-500 text-teal-600' : 'border-transparent text-gray-500 hover:text-gray-700', 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm']">إدارة الطوابق</button>
                        <button @click="activeTab = 'rooms'" :class="[activeTab === 'rooms' ? 'border-teal-500 text-teal-600' : 'border-transparent text-gray-500 hover:text-gray-700', 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm']">إدارة الغرف</button>
                    </nav>
                </div>

                <!-- Floors Panel -->
                <div v-show="activeTab === 'floors'">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <button @click="openModal('addFloor')" class="mb-4 px-4 py-2 bg-teal-600 text-white rounded-lg shadow hover:bg-teal-700">إضافة طابق</button>
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">اسم الطابق</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">المستوى</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">عدد الغرف</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">إجراءات</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="floor in floors" :key="floor.id">
                                    <td class="px-6 py-4 text-right">{{ floor.name }}</td>
                                    <td class="px-6 py-4 text-right">{{ floor.level }}</td>
                                    <td class="px-6 py-4 text-right">{{ floor.rooms.length }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center justify-end space-x-2 rtl:space-x-reverse">
                                            <button @click="openModal('editFloor', floor)" class="flex items-center justify-center gap-2 px-3 py-2 bg-indigo-100 text-indigo-700 text-xs font-semibold rounded-lg hover:bg-indigo-200 transition-colors">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.5L15.232 5.232z" /></svg>
                                                <span>تعديل</span>
                                            </button>
                                            <button @click="deleteItem('floors', floor)" class="flex items-center justify-center gap-2 px-3 py-2 bg-red-100 text-red-700 text-xs font-semibold rounded-lg hover:bg-red-200 transition-colors">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                <span>حذف</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Rooms Panel -->
                <div v-show="activeTab === 'rooms'">
                     <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <button @click="openModal('addRoom')" class="mb-4 px-4 py-2 bg-teal-600 text-white rounded-lg shadow hover:bg-teal-700">إضافة غرفة</button>
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">رقم الغرفة</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">اسم الغرفة</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الطابق</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الطبيب</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">إجراءات</th>
                                </tr>
                            </thead>
                             <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="room in allRooms" :key="room.id">
                                    <td class="px-6 py-4 text-right">{{ room.room_number }}</td>
                                    <td class="px-6 py-4 text-right">{{ room.name }}</td>
                                    <td class="px-6 py-4 text-right">{{ room.floor.name }}</td>
                                    <td class="px-6 py-4 text-right">{{ room.doctor?.name || 'شاغرة' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center justify-end space-x-2 rtl:space-x-reverse">
                                            <button @click="openModal('editRoom', room)" class="flex items-center justify-center gap-2 px-3 py-2 bg-indigo-100 text-indigo-700 text-xs font-semibold rounded-lg hover:bg-indigo-200 transition-colors">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.5L15.232 5.232z" /></svg>
                                                <span>تعديل</span>
                                            </button>
                                            <button @click="deleteItem('rooms', room)" class="flex items-center justify-center gap-2 px-3 py-2 bg-red-100 text-red-700 text-xs font-semibold rounded-lg hover:bg-red-200 transition-colors">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                <span>حذف</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Modal -->
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
                            <div class="p-6 border-b border-slate-200">
                                <h3 class="text-xl font-semibold text-slate-800">
                                    <span v-if="modalMode.includes('Floor')">{{ modalMode === 'addFloor' ? 'إضافة طابق جديد' : 'تعديل طابق' }}</span>
                                    <span v-if="modalMode.includes('Room')">{{ modalMode === 'addRoom' ? 'إضافة غرفة جديدة' : 'تعديل غرفة' }}</span>
                                </h3>
                            </div>
                            <div class="p-6 space-y-4 max-h-[60vh] overflow-y-auto">
                                <!-- Floor Form -->
                                <div v-if="modalMode.includes('Floor')" class="space-y-4">
                                    <div><label class="font-medium text-slate-700">اسم الطابق</label><input v-model="floorForm.name" class="w-full mt-1 rounded-lg border-slate-300 shadow-sm text-slate-800 focus:border-teal-500 focus:ring-teal-200" /></div>
                                    <div><label class="font-medium text-slate-700">المستوى (رقم)</label><input v-model="floorForm.level" type="number" class="w-full mt-1 rounded-lg border-slate-300 shadow-sm text-slate-800 focus:border-teal-500 focus:ring-teal-200" /></div>
                                </div>
                                <!-- Room Form -->
                                <div v-if="modalMode.includes('Room')" class="space-y-4">
                                    <div><label class="font-medium text-slate-700">الطابق</label><select v-model="roomForm.floor_id" class="w-full mt-1 rounded-lg border-slate-300 shadow-sm text-slate-800 focus:border-teal-500 focus:ring-teal-200"><option :value="null">-- اختر طابق --</option><option v-for="floor in floors" :key="floor.id" :value="floor.id">{{ floor.name }}</option></select></div>
                                    <div><label class="font-medium text-slate-700">رقم الغرفة</label><input v-model="roomForm.room_number" class="w-full mt-1 rounded-lg border-slate-300 shadow-sm text-slate-800 focus:border-teal-500 focus:ring-teal-200" /></div>
                                    <div><label class="font-medium text-slate-700">اسم الغرفة (اختياري)</label><input v-model="roomForm.name" class="w-full mt-1 rounded-lg border-slate-300 shadow-sm text-slate-800 focus:border-teal-500 focus:ring-teal-200" /></div>
                                </div>
                            </div>
                            <div class="flex justify-end space-x-2 rtl:space-x-reverse p-6 bg-slate-50 rounded-b-lg">
                                <button type="button" @click="closeModal" class="px-5 py-2 bg-slate-200 text-slate-700 font-medium rounded-lg hover:bg-slate-300">إلغاء</button>
                                <button type="submit" :disabled="floorForm.processing || roomForm.processing" class="px-5 py-2 bg-teal-600 text-white font-medium rounded-lg hover:bg-teal-700 disabled:bg-teal-300">حفظ</button>
                            </div>
                        </form>
                    </div>
                </transition>
            </div>
        </transition>
    </AuthenticatedLayout>
</template>

<style>
td{
    color: black;
}
</style>