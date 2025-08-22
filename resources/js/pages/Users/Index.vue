<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '../../layouts/AuthenticatedLayout.vue';

const props = defineProps({
    users: Array,
    roles: Array,
});

const page = usePage();
const showModal = ref(false);
const isEditing = ref(false);
const editingUser = ref(null);

// Updated form to include fields for adding a new user
const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    roles: [],
});

const successMessage = computed(() => page.props.flash?.success);

const openModal = (mode, user = null) => {
    isEditing.value = (mode === 'edit');
    if (user) {
        editingUser.value = user;
        form.defaults({
            name: user.name,
            email: user.email,
            password: '',
            password_confirmation: '',
            roles: user.roles.map(role => role.name),
        }).reset();
    } else {
        form.reset();
    }
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    editingUser.value = null;
    form.reset();
};

const submit = () => {
    const options = {
        preserveScroll: true,
        onSuccess: () => closeModal(),
    };
    if (isEditing.value) {
        form.put(route('users.update', editingUser.value.id), options);
    } else {
        form.post(route('users.store'), options);
    }
};
</script>

<template>
    <Head title="إدارة المستخدمين" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">إدارة المستخدمين والصلاحيات</h2>
                <button @click="openModal('add')" class="px-4 py-2 bg-teal-600 text-white rounded-lg shadow hover:bg-teal-700 transition-colors">
                    إضافة مستخدم جديد
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div v-if="successMessage" class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4" role="alert">{{ successMessage }}</div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">المستخدم</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">البريد الإلكتروني</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الأدوار</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">إجراءات</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="user in users" :key="user.id">
                                    <td class="px-6 py-4 text-right">{{ user.name }}</td>
                                    <td class="px-6 py-4 text-right">{{ user.email }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex flex-wrap gap-1 justify-end">
                                            <span v-for="role in user.roles" :key="role.id" class="px-2 py-1 text-xs rounded-full"
                                                  :class="{ 'bg-yellow-200 text-yellow-800': role.name === 'Super-Admin', 'bg-blue-200 text-blue-800': role.name === 'Screen-Manager' }">
                                                {{ role.name }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <button @click="openModal('edit', user)" class="px-3 py-2 bg-indigo-100 text-indigo-700 text-xs font-semibold rounded-lg hover:bg-indigo-200">تعديل الصلاحيات</button>
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
                    <div v-if="showModal" class="bg-white rounded-lg shadow-xl w-full max-w-lg">
                        <form @submit.prevent="submit">
                            <div class="p-6 border-b">
                                <h3 class="text-xl font-semibold text-slate-800">
                                    {{ isEditing ? `تعديل صلاحيات: ${editingUser.name}` : 'إضافة مستخدم جديد' }}
                                </h3>
                            </div>
                            <div class="p-6 space-y-4 text-right">
                                <div v-if="!isEditing">
                                    <div><label class="font-medium text-slate-700">الاسم</label><input v-model="form.name" class="w-full mt-1 rounded-md border-gray-300 text-slate-800" /></div>
                                    <div><label class="font-medium text-slate-700">البريد الإلكتروني</label><input v-model="form.email" type="email" class="w-full mt-1 rounded-md border-gray-300 text-slate-800" /></div>
                                    <div><label class="font-medium text-slate-700">كلمة المرور</label><input v-model="form.password" type="password" class="w-full mt-1 rounded-md border-gray-300 text-slate-800" /></div>
                                    <div><label class="font-medium text-slate-700">تأكيد كلمة المرور</label><input v-model="form.password_confirmation" type="password" class="w-full mt-1 rounded-md border-gray-300 text-slate-800" /></div>
                                </div>
                                
                                <p class="font-medium text-slate-700 pt-4">الأدوار المتاحة:</p>
                                <div class="grid grid-cols-2 gap-4">
                                    <label v-for="role in roles" :key="role" class="flex items-center p-3 border rounded-lg hover:bg-slate-50">
                                        <input type="checkbox" v-model="form.roles" :value="role" class="rounded h-5 w-5 text-teal-600 border-gray-300 focus:ring-teal-500">
                                        <span class="ml-2 mr-2 text-slate-800">{{ role }}</span>
                                    </label>
                                </div>
                            </div>
                            <div class="flex justify-end space-x-2 rtl:space-x-reverse p-6 bg-slate-50 rounded-b-lg">
                                <button type="button" @click="closeModal" class="px-5 py-2 bg-slate-200 text-slate-700 rounded-lg">إلغاء</button>
                                <button type="submit" :disabled="form.processing" class="px-5 py-2 bg-teal-600 text-white rounded-lg disabled:bg-teal-300">حفظ التغييرات</button>
                            </div>
                        </form>
                    </div>
                </transition>
            </div>
        </transition>
    </AuthenticatedLayout>
</template>
