<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '../../layouts/AuthenticatedLayout.vue';

const props = defineProps({
    doctors: Array,
    floors: Array,
    allRooms: Array,
});

const page = usePage();
const showModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);

const form = useForm({
    name: '',
    specialty: '',
    status: 'available',
    room_ids: [], // تم التغيير إلى مصفوفة
    photo_url: '',
    bio: '',
});

const successMessage = computed(() => page.props.flash?.success);
const errorMessage = computed(() => page.props.flash?.error);

const openModal = (mode, doctor = null) => {
    isEditing.value = (mode === 'edit');
    if (doctor) {
        editingId.value = doctor.id;
        form.defaults({
            ...doctor,
            // تحويل علاقة الغرف إلى مصفوفة من الـ IDs
            room_ids: doctor.rooms.map(room => room.id),
        }).reset();
    } else {
        form.reset();
    }
    showModal.value = true;
};

const closeModal = () => showModal.value = false;

const submitForm = () => {
    const options = { preserveScroll: true, onSuccess: () => closeModal() };
    if (isEditing.value) {
        form.put(route('doctors.update', editingId.value), options);
    } else {
        form.post(route('doctors.store'), options);
    }
};

const deleteDoctor = (doctor) => {
    if (confirm(`هل أنت متأكد من حذف الطبيب ${doctor.name}؟`)) {
        useForm({}).delete(route('doctors.destroy', doctor.id), { preserveScroll: true });
    }
};
</script>

<template>
    <Head title="إدارة الأطباء" />

    <AuthenticatedLayout>
        <template #header>
             <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">إدارة الأطباء</h2>
                <button @click="openModal('add')" class="px-4 py-2 bg-teal-600 text-white rounded-lg shadow hover:bg-teal-700">إضافة طبيب</button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                 <!-- Flash Messages -->
                <div v-if="successMessage" class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4" role="alert">{{ successMessage }}</div>
                <div v-if="errorMessage" class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert">{{ errorMessage }}</div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الطبيب</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الغرف</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الحالة</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">إجراءات</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="doctor in doctors" :key="doctor.id">
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <img class="h-10 w-10 rounded-full object-cover" :src="doctor.photo_url || 'https://placehold.co/40x40/E2E8F0/4A5568?text=Dr'" alt="">
                                        </div>
                                        <div class="mr-4">
                                            <div class="text-sm font-medium text-gray-900">{{ doctor.name }}</div>
                                            <div class="text-sm text-gray-500">{{ doctor.specialty }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 text-right">
                                    <div v-if="doctor.rooms.length > 0" class="flex flex-wrap gap-1 justify-end">
                                        <span v-for="room in doctor.rooms" :key="room.id" class="px-2 py-1 bg-slate-200 text-slate-700 rounded-full text-xs">
                                            {{ room.room_number }}
                                        </span>
                                    </div>
                                    <span v-else class="text-xs text-gray-400">-- لم تسند غرفة --</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="doctor.status === 'available' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                                        {{ doctor.status === 'available' ? 'متوفر' : 'غير متوفر' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-right">
                                     <div class="flex items-center justify-end space-x-2 rtl:space-x-reverse">
                                        <button @click="openModal('edit', doctor)" class="flex items-center justify-center gap-2 px-3 py-2 bg-indigo-100 text-indigo-700 text-xs font-semibold rounded-lg hover:bg-indigo-200 transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.5L15.232 5.232z" /></svg>
                                            <span>تعديل</span>
                                        </button>
                                        <button @click="deleteDoctor(doctor)" class="flex items-center justify-center gap-2 px-3 py-2 bg-red-100 text-red-700 text-xs font-semibold rounded-lg hover:bg-red-200 transition-colors">
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
                                    {{ isEditing ? 'تعديل بيانات الطبيب' : 'إضافة طبيب جديد' }}
                                </h3>
                            </div>
                            <div class="p-6 space-y-4 max-h-[60vh] overflow-y-auto">
                                <div><label class="font-medium text-slate-700">الاسم</label><input v-model="form.name" class="w-full mt-1 rounded-lg border-slate-300 shadow-sm text-slate-800 focus:border-teal-500 focus:ring-teal-200" /></div>
                                <div><label class="font-medium text-slate-700">التخصص</label><input v-model="form.specialty" class="w-full mt-1 rounded-lg border-slate-300 shadow-sm text-slate-800 focus:border-teal-500 focus:ring-teal-200" /></div>
                                <div>
                                    <label class="font-medium text-slate-700">الغرف</label>
                                    <select v-model="form.room_ids" multiple class="w-full mt-1 rounded-lg border-slate-300 shadow-sm text-slate-800 focus:border-teal-500 focus:ring-teal-200 h-40">
                                        <option v-for="room in allRooms" :key="room.id" :value="room.id">
                                            {{ room.room_number }} ({{ floors.find(f => f.id === room.floor_id)?.name }})
                                        </option>
                                    </select>
                                    <p class="text-xs text-slate-500 mt-1">يمكنك اختيار أكثر من غرفة بالضغط على Ctrl (أو Cmd) والنقر.</p>
                                </div>
                                <div><label class="font-medium text-slate-700">الحالة</label><select v-model="form.status" class="w-full mt-1 rounded-lg border-slate-300 shadow-sm text-slate-800 focus:border-teal-500 focus:ring-teal-200"><option value="available">متوفر</option><option value="busy">مشغول</option><option value="away">غير موجود</option></select></div>
                                <div><label class="font-medium text-slate-700">رابط الصورة</label><input v-model="form.photo_url" type="url" class="w-full mt-1 rounded-lg border-slate-300 shadow-sm text-slate-800 focus:border-teal-500 focus:ring-teal-200" /></div>
                                <div><label class="font-medium text-slate-700">نبذة</label><textarea v-model="form.bio" class="w-full mt-1 rounded-lg border-slate-300 shadow-sm text-slate-800 focus:border-teal-500 focus:ring-teal-200"></textarea></div>
                            </div>
                            <div class="flex justify-end space-x-2 rtl:space-x-reverse p-6 bg-slate-50 rounded-b-lg">
                                <button type="button" @click="closeModal" class="px-5 py-2 bg-slate-200 text-slate-700 font-medium rounded-lg hover:bg-slate-300">إلغاء</button>
                                <button type="submit" :disabled="form.processing" class="px-5 py-2 bg-teal-600 text-white font-medium rounded-lg hover:bg-teal-700 disabled:bg-teal-300">حفظ</button>
                            </div>
                        </form>
                    </div>
                </transition>
            </div>
        </transition>
    </AuthenticatedLayout>
</template>