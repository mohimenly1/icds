<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '../../layouts/AuthenticatedLayout.vue';

const props = defineProps({
    contentSets: Array,
    media: Array,
});

const page = usePage();
const showModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);

const form = useForm({
    name: '',
    media_ids: [],
});

const successMessage = computed(() => page.props.flash?.success);
const errorMessage = computed(() => page.props.flash?.error);

const openModal = (mode, set = null) => {
    isEditing.value = (mode === 'edit');
    if (set) {
        editingId.value = set.id;
        form.defaults({
            name: set.name,
            media_ids: set.media.map(m => m.id),
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
        form.put(route('unified-content.update', editingId.value), options);
    } else {
        form.post(route('unified-content.store'), options);
    }
};

const deleteSet = (set) => {
    if (confirm(`هل أنت متأكد من حذف مجموعة '${set.name}'؟`)) {
        useForm({}).delete(route('unified-content.destroy', set.id), { preserveScroll: true });
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

const isMediaSelected = (mediaId) => form.media_ids.includes(mediaId);
const getMediaUrl = (path) => `/storage/${path}`;

</script>

<template>
    <Head title="المحتوى الموحد" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">إدارة مجموعات المحتوى الموحد</h2>
                <button @click="openModal('add')" class="px-4 py-2 bg-teal-600 text-white rounded-lg shadow hover:bg-teal-700">
                    إنشاء مجموعة جديدة
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div v-if="successMessage" class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4">{{ successMessage }}</div>
                <div v-if="errorMessage" class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4">{{ errorMessage }}</div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="set in contentSets" :key="set.id" class="bg-white rounded-lg shadow-sm overflow-hidden">
                        <div class="p-4 border-b">
                            <h3 class="font-bold text-lg text-slate-800">{{ set.name }}</h3>
                            <p class="text-sm text-slate-500">{{ set.media.length }} ملفات</p>
                        </div>
                        <div class="p-4">
                            <div class="flex -space-x-2 rtl:space-x-reverse justify-end">
                                <img v-for="m in set.media.slice(0, 5)" :key="m.id" class="inline-block h-10 w-10 rounded-full ring-2 ring-white object-cover" :src="getMediaUrl(m.path)" :alt="m.name">
                                <span v-if="set.media.length > 5" class="flex items-center justify-center h-10 w-10 rounded-full ring-2 ring-white bg-slate-200 text-slate-600 text-xs font-bold">
                                    +{{ set.media.length - 5 }}
                                </span>
                            </div>
                        </div>
                        <div class="p-4 bg-slate-50 flex justify-end space-x-2 rtl:space-x-reverse">
                            <button @click="openModal('edit', set)" class="text-sm text-indigo-600 hover:text-indigo-800">تعديل</button>
                            <button @click="deleteSet(set)" class="text-sm text-red-600 hover:text-red-800">حذف</button>
                        </div>
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
                                <h3 class="text-xl font-semibold text-slate-800">{{ isEditing ? `تعديل مجموعة: ${form.name}` : 'إنشاء مجموعة محتوى جديدة' }}</h3>
                            </div>
                            <div class="p-6 space-y-4 max-h-[60vh] overflow-y-auto text-right">
                                <div><label class="font-medium text-slate-700">اسم المجموعة</label><input v-model="form.name" class="w-full mt-1 rounded-lg border-slate-300 text-slate-800" /></div>
                                <div>
                                    <label class="font-medium text-slate-700">اختر الوسائط لهذه المجموعة</label>
                                    <div class="mt-2 grid grid-cols-4 gap-4 p-4 border rounded-lg h-64 overflow-y-auto">
                                        <div v-for="m in media" :key="m.id" @click="toggleMediaSelection(m.id)" 
                                             class="relative rounded-lg overflow-hidden cursor-pointer border-4 transition-all"
                                             :class="isMediaSelected(m.id) ? 'border-teal-500' : 'border-transparent'">
                                            <img v-if="m.type === 'image'" :src="getMediaUrl(m.path)" class="h-full w-full object-cover">
                                            <div v-else class="h-full w-full bg-slate-800 flex items-center justify-center">
                                                 <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="2.18" ry="2.18"></rect><line x1="7" y1="2" x2="7" y2="22"></line></svg>
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
