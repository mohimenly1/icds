<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, usePage, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '../../layouts/AuthenticatedLayout.vue';

const props = defineProps({
    screens: Array,
    media: Array,
    unifiedContentSets: Array, // استقبال الـ prop الجديد
});

const page = usePage();
const showModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);

const form = useForm({
    name: '',
    location: '',
    is_active: true,
    is_unified_content: false,
    unified_content_set_id: null, // إضافة الحقل الجديد
    media_ids: [],
});

const successMessage = computed(() => page.props.flash?.success);
const errorMessage = computed(() => page.props.flash?.error);

const openModal = (mode, screen = null) => {
    isEditing.value = (mode === 'edit');
    if (screen) {
        editingId.value = screen.id;
        form.defaults({
            ...screen,
            media_ids: screen.media.map(m => m.id),
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
        form.put(route('screens.update', editingId.value), options);
    } else {
        form.post(route('screens.store'), options);
    }
};

const deleteScreen = (screen) => {
    if (confirm(`هل أنت متأكد من حذف شاشة '${screen.name}'؟`)) {
        useForm({}).delete(route('screens.destroy', screen.id), { preserveScroll: true });
    }
};

const toggleMediaSelection = (mediaId) => {
    const index = form.media_ids.indexOf(mediaId);
    if (index > -1) {
        form.media_ids.splice(index, 1);
    } else {
        form.media_ids.push(mediaId);
    }
};

const isMediaSelected = (mediaId) => {
    return form.media_ids.includes(mediaId);
};

const getMediaUrl = (path) => `/storage/${path}`;

</script>

<template>
    <Head title="إدارة الشاشات" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">إدارة الشاشات الإعلانية</h2>
                <button @click="openModal('add')" class="px-4 py-2 bg-teal-600 text-white rounded-lg shadow hover:bg-teal-700 transition-colors">
                    إضافة شاشة جديدة
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div v-if="successMessage" class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4" role="alert">{{ successMessage }}</div>
                <div v-if="errorMessage" class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert">{{ errorMessage }}</div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">اسم الشاشة</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الموقع</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الحالة</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">المحتوى</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">إجراءات</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="screen in screens" :key="screen.id">
                                    <td class="px-6 py-4 text-right font-medium text-gray-900">{{ screen.name }}</td>
                                    <td class="px-6 py-4 text-right text-gray-500">{{ screen.location }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="screen.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'">
                                            {{ screen.is_active ? 'نشطة' : 'غير نشطة' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="screen.is_unified_content ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800'">
                                            {{ screen.is_unified_content ? 'موحد' : 'مخصص' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end space-x-2 rtl:space-x-reverse">
                                            <Link :href="route('screen.display', screen.unique_key)" target="_blank" class="flex items-center gap-2 px-3 py-2 bg-slate-100 text-slate-700 text-xs font-semibold rounded-lg hover:bg-slate-200"><span>معاينة</span></Link>
                                            <button @click="openModal('edit', screen)" class="flex items-center gap-2 px-3 py-2 bg-indigo-100 text-indigo-700 text-xs font-semibold rounded-lg hover:bg-indigo-200"><span>تعديل</span></button>
                                            <button @click="deleteScreen(screen)" class="flex items-center gap-2 px-3 py-2 bg-red-100 text-red-700 text-xs font-semibold rounded-lg hover:bg-red-200"><span>حذف</span></button>
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
                <transition enter-active-class="ease-out duration-300" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100" leave-active-class="ease-in duration-200" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
                    <div v-if="showModal" class="bg-white rounded-lg shadow-xl w-full max-w-3xl">
                        <form @submit.prevent="submitForm">
                            <div class="p-6 border-b">
                                <h3 class="text-xl font-semibold text-slate-800">{{ isEditing ? `تعديل شاشة: ${form.name}` : 'إضافة شاشة جديدة' }}</h3>
                            </div>
                            <div class="p-6 space-y-4 max-h-[60vh] overflow-y-auto text-right">
                                <div><label class="font-medium text-slate-700">اسم الشاشة</label><input v-model="form.name" class="w-full mt-1 rounded-lg border-slate-300 text-slate-800" /></div>
                                <div><label class="font-medium text-slate-700">موقع الشاشة</label><input v-model="form.location" class="w-full mt-1 rounded-lg border-slate-300 text-slate-800" /></div>
                                <div class="grid grid-cols-2 gap-4 pt-2">
                                    <label class="flex items-center p-3 border rounded-lg hover:bg-slate-50"><input type="checkbox" v-model="form.is_active" class="rounded h-5 w-5 text-teal-600"><span class="mr-2 text-slate-800">الشاشة نشطة</span></label>
                                    <label class="flex items-center p-3 border rounded-lg hover:bg-slate-50"><input type="checkbox" v-model="form.is_unified_content" class="rounded h-5 w-5 text-teal-600"><span class="mr-2 text-slate-800">استخدام المحتوى الموحد</span></label>
                                </div>
                                
                                <div v-if="form.is_unified_content">
                                    <label class="font-medium text-slate-700">اختر مجموعة المحتوى الموحد</label>
                                    <select v-model="form.unified_content_set_id" class="w-full mt-1 rounded-lg border-slate-300 text-slate-800">
                                        <option :value="null">-- اختر مجموعة --</option>
                                        <option v-for="set in unifiedContentSets" :key="set.id" :value="set.id">{{ set.name }}</option>
                                    </select>
                                </div>

                                <div v-if="!form.is_unified_content">
                                    <label class="font-medium text-slate-700">الوسائط المخصصة لهذه الشاشة</label>
                                    <div class="mt-2 grid grid-cols-4 gap-4 p-4 border rounded-lg h-64 overflow-y-auto">
                                        <div v-for="m in media" :key="m.id" @click="toggleMediaSelection(m.id)" 
                                             class="relative rounded-lg overflow-hidden cursor-pointer border-4 transition-all"
                                             :class="isMediaSelected(m.id) ? 'border-teal-500' : 'border-transparent'">
                                            
                                            <img v-if="m.type === 'image'" :src="getMediaUrl(m.path)" class="h-full w-full object-cover">
                                            <div v-else class="h-full w-full bg-slate-800 flex items-center justify-center">
                                                 <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="2.18" ry="2.18"></rect><line x1="7" y1="2" x2="7" y2="22"></line><line x1="17" y1="2" x2="17" y2="22"></line><line x1="2" y1="12" x2="22" y2="12"></line></svg>
                                            </div>
                                            <p class="absolute bottom-0 left-0 w-full p-1 text-center text-white text-xs bg-black/50 truncate">{{ m.name }}</p>
                                            
                                            <div v-if="isMediaSelected(m.id)" class="absolute top-2 right-2 bg-teal-500 text-white rounded-full p-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                                            </div>
                                        </div>
                                    </div>
                                </div>
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
