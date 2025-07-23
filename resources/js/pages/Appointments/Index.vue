<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '../../layouts/AuthenticatedLayout.vue';

// Props received from the controller
const props = defineProps({
    appointments: Array,
    doctors: Array,
});

const page = usePage();
const showModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);

// Form helper from Inertia
const form = useForm({
    doctor_id: null,
    patient_name: '',
    patient_phone: '',
    appointment_time: '',
    notes: '',
    status: 'scheduled',
});

const successMessage = computed(() => page.props.flash?.success);
const errorMessage = computed(() => page.props.flash?.error);

// Helper to format date for the input field
const formatDateTimeForInput = (dateTimeString) => {
    if (!dateTimeString) return '';
    const date = new Date(dateTimeString);
    // Adjust for timezone offset
    date.setMinutes(date.getMinutes() - date.getTimezoneOffset());
    return date.toISOString().slice(0, 16);
};

const openModal = (mode, appointment = null) => {
    isEditing.value = (mode === 'edit');
    if (appointment) {
        editingId.value = appointment.id;
        form.defaults({
            ...appointment,
            appointment_time: formatDateTimeForInput(appointment.appointment_time),
        }).reset();
    } else {
        form.reset();
    }
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
};

const submitForm = () => {
    const options = { preserveScroll: true, onSuccess: () => closeModal() };
    if (isEditing.value) {
        form.put(route('appointments.update', editingId.value), options);
    } else {
        form.post(route('appointments.store'), options);
    }
};

const deleteAppointment = (appointment) => {
    if (confirm(`هل أنت متأكد من حذف موعد المريض ${appointment.patient_name}؟`)) {
        useForm({}).delete(route('appointments.destroy', appointment.id), {
            preserveScroll: true,
        });
    }
};

const getStatusClass = (status) => {
    if (status === 'completed') return 'bg-green-100 text-green-800';
    if (status === 'cancelled') return 'bg-red-100 text-red-800';
    return 'bg-blue-100 text-blue-800'; // scheduled
};
</script>

<template>
    <Head title="إدارة المواعيد" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">إدارة المواعيد</h2>
                <button @click="openModal('add')" class="px-4 py-2 bg-teal-600 text-white rounded-lg shadow hover:bg-teal-700 transition-colors">
                    إضافة موعد جديد
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Flash Messages -->
                <div v-if="successMessage" class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4" role="alert">{{ successMessage }}</div>
                <div v-if="errorMessage" class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert">{{ errorMessage }}</div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المريض</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الطبيب</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">وقت الموعد</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الحالة</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">إجراءات</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="appointment in appointments" :key="appointment.id">
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <div class="text-sm font-medium text-gray-900">{{ appointment.patient_name }}</div>
                                        <div class="text-sm text-gray-500">{{ appointment.patient_phone }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">{{ appointment.doctor.name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">{{ new Date(appointment.appointment_time).toLocaleString('ar-EG') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="getStatusClass(appointment.status)">
                                            {{ appointment.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center justify-end space-x-2 rtl:space-x-reverse">
                                            <button @click="openModal('edit', appointment)" class="flex items-center justify-center gap-2 px-3 py-2 bg-indigo-100 text-indigo-700 text-xs font-semibold rounded-lg hover:bg-indigo-200"><span>تعديل</span></button>
                                            <button @click="deleteAppointment(appointment)" class="flex items-center justify-center gap-2 px-3 py-2 bg-red-100 text-red-700 text-xs font-semibold rounded-lg hover:bg-red-200"><span>حذف</span></button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add/Edit Modal -->
        <transition enter-active-class="ease-out duration-300" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="ease-in duration-200" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="showModal" class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center z-50" @click.self="closeModal">
                <transition enter-active-class="ease-out duration-300" enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" enter-to-class="opacity-100 translate-y-0 sm:scale-100" leave-active-class="ease-in duration-200" leave-from-class="opacity-100 translate-y-0 sm:scale-100" leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                    <div v-if="showModal" class="bg-white rounded-lg shadow-xl w-full max-w-2xl transform transition-all">
                        <form @submit.prevent="submitForm" class="text-right">
                            <div class="p-6 border-b border-slate-200">
                                <h3 class="text-xl font-semibold text-slate-800">{{ isEditing ? 'تعديل الموعد' : 'إضافة موعد جديد' }}</h3>
                            </div>
                            <div class="p-6 space-y-4 max-h-[60vh] overflow-y-auto">
                                <div><label class="font-medium text-slate-700">اسم المريض</label><input v-model="form.patient_name" class="w-full mt-1 rounded-lg border-slate-300 text-slate-800" /></div>
                                <div><label class="font-medium text-slate-700">رقم هاتف المريض</label><input v-model="form.patient_phone" class="w-full mt-1 rounded-lg border-slate-300 text-slate-800" /></div>
                                <div><label class="font-medium text-slate-700">الطبيب</label><select v-model="form.doctor_id" class="w-full mt-1 rounded-lg border-slate-300 text-slate-800"><option :value="null">-- اختر طبيب --</option><option v-for="doctor in doctors" :key="doctor.id" :value="doctor.id">{{ doctor.name }}</option></select></div>
                                <div><label class="font-medium text-slate-700">تاريخ ووقت الموعد</label><input v-model="form.appointment_time" type="datetime-local" class="w-full mt-1 rounded-lg border-slate-300 text-slate-800" /></div>
                                <div><label class="font-medium text-slate-700">الحالة</label><select v-model="form.status" class="w-full mt-1 rounded-lg border-slate-300 text-slate-800"><option value="scheduled">مجدول</option><option value="completed">مكتمل</option><option value="cancelled">ملغى</option></select></div>
                                <div><label class="font-medium text-slate-700">ملاحظات</label><textarea v-model="form.notes" class="w-full mt-1 rounded-lg border-slate-300 text-slate-800"></textarea></div>
                            </div>
                            <div class="flex justify-end space-x-2 rtl:space-x-reverse p-6 bg-slate-50 rounded-b-lg">
                                <button type="button" @click="closeModal" class="px-5 py-2 bg-slate-200 text-slate-700 rounded-lg">إلغاء</button>
                                <button type="submit" :disabled="form.processing" class="px-5 py-2 bg-teal-600 text-white rounded-lg disabled:bg-teal-300">حفظ</button>
                            </div>
                        </form>
                    </div>
                </transition>
            </div>
        </transition>
    </AuthenticatedLayout>
</template>
