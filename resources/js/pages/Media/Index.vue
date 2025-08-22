<script setup>
import { computed } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '../../layouts/AuthenticatedLayout.vue';

const props = defineProps({
    media: Array,
});

const page = usePage();

const form = useForm({
    files: [], // تم التغيير للتعامل مع مصفوفة من الملفات
});

const successMessage = computed(() => page.props.flash?.success);
const errorMessage = computed(() => page.props.flash?.error);

const submit = () => {
    form.post(route('media.store'), {
        onSuccess: () => form.reset(),
    });
};

const deleteMedia = (mediaItem) => {
    if (confirm(`هل أنت متأكد من حذف ملف '${mediaItem.name}'؟`)) {
        useForm({}).delete(route('media.destroy', mediaItem.id), {
            preserveScroll: true,
        });
    }
};

// Helper to get the full URL for a media file
const getMediaUrl = (path) => {
    return `/storage/${path}`;
};
</script>

<template>
    <Head title="مكتبة الوسائط" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">مكتبة الوسائط</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div v-if="successMessage" class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4" role="alert">{{ successMessage }}</div>
                <div v-if="errorMessage" class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert">{{ errorMessage }}</div>

                <!-- Upload Form -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-semibold text-slate-800 mb-4">رفع ملفات جديدة</h3>
                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <label for="file" class="block text-sm font-medium text-gray-700">اختر ملفات (صور أو فيديوهات)</label>
                            <!-- إضافة 'multiple' للسماح باختيار عدة ملفات -->
                            <input type="file" @input="form.files = $event.target.files" id="file" class="mt-1 block w-full text-slate-800" multiple>
                            <progress v-if="form.progress" :value="form.progress.percentage" max="100" class="w-full mt-2">
                                {{ form.progress.percentage }}%
                            </progress>
                            <div v-if="form.errors.files" class="text-red-500 text-xs mt-1">{{ form.errors.files }}</div>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" :disabled="form.processing || form.files.length === 0" class="px-4 py-2 bg-teal-600 text-white rounded-lg shadow hover:bg-teal-700 disabled:bg-teal-300">
                                رفع الملفات
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Media Library Grid -->
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    <div v-for="item in media" :key="item.id" class="relative group bg-white rounded-lg shadow-sm overflow-hidden">
                        <template v-if="item.type === 'image'">
                            <img :src="getMediaUrl(item.path)" :alt="item.name" class="h-40 w-full object-cover">
                        </template>
                        <template v-else>
                            <div class="h-40 w-full bg-slate-800 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="2.18" ry="2.18"></rect><line x1="7" y1="2" x2="7" y2="22"></line><line x1="17" y1="2" x2="17" y2="22"></line><line x1="2" y1="12" x2="22" y2="12"></line><line x1="2" y1="7" x2="7" y2="7"></line><line x1="2" y1="17" x2="7" y2="17"></line><line x1="17" y1="17" x2="22" y2="17"></line><line x1="17" y1="7" x2="22" y2="7"></line></svg>
                            </div>
                        </template>
                        <div class="absolute bottom-0 left-0 w-full p-2 bg-gradient-to-t from-black/60 to-transparent">
                            <p class="text-white text-sm truncate">{{ item.name }}</p>
                        </div>
                        <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button @click="deleteMedia(item)" class="p-1.5 bg-red-500 text-white rounded-full shadow-lg hover:bg-red-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
