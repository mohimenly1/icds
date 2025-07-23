<script setup>
import { ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

// Access user data from Inertia's shared props
const user = usePage().props.auth.user;

// State for mobile sidebar
const sidebarOpen = ref(false);
const showingNavigationDropdown = ref(false);
</script>

<template>
    <div class="flex h-screen bg-slate-100">
        <!-- Sidebar -->
        <aside class="w-64 bg-white shadow-xl h-full fixed rtl:right-0 ltr:left-0 z-20 flex-col hidden sm:flex">
            <!-- Logo -->
            <div class="h-16 flex items-center justify-center border-b border-slate-200 shrink-0">
                <Link :href="route('dashboard')">
                    <div class="flex items-center gap-2 text-xl font-bold text-teal-600">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/></svg>
                        <span>لوحة التحكم</span>
                    </div>
                </Link>
            </div>
            
       <!-- Navigation Links -->
       <nav class="flex-grow p-4 space-y-2">
                <Link :href="route('dashboard')" :class="[route().current('dashboard') ? 'bg-teal-100 text-teal-700' : 'text-slate-600 hover:bg-slate-100', 'flex items-center gap-3 px-3 py-2 rounded-md text-base font-medium transition-colors']">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
                    <span>الرئيسية</span>
                </Link>
                <!-- Note: You will need to create these pages and routes later -->
                <p class="px-3 pt-4 pb-2 text-xs font-semibold text-slate-400 uppercase">الإدارة</p>
                <Link :href="route('doctors.index')" :class="['text-slate-600 hover:bg-slate-100', 'flex items-center gap-3 px-3 py-2 rounded-md text-base font-medium transition-colors']">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    <span>إدارة الأطباء</span>
                </Link>
                <Link :href="route('clinic-structure.index')" :class="[route().current('clinic-structure.index') ? 'bg-teal-100 text-teal-700' : 'text-slate-600 hover:bg-slate-100', 'flex items-center gap-3 px-3 py-2 rounded-md text-base font-medium transition-colors']">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 2 2 4-4"/></svg>
                    <span>إدارة الغرف والطوابق</span>
                </Link>
                 <Link :href="route('schedules.index')":class="['text-slate-600 hover:bg-slate-100', 'flex items-center gap-3 px-3 py-2 rounded-md text-base font-medium transition-colors']">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                    <span>إدارة المواعيد</span>
                </Link>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col rtl:sm:mr-64 ltr:sm:ml-64">
            <!-- Topbar -->
            <header class="bg-white border-b border-slate-200 h-16 flex items-center justify-between px-6 shrink-0">
                <div class="flex items-center">
                    <!-- Mobile Sidebar Toggle -->
                    <button @click="sidebarOpen = !sidebarOpen" class="sm:hidden text-slate-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                    </button>
                </div>
                <!-- User Dropdown -->
                <div class="relative">
                    <div @click="showingNavigationDropdown = !showingNavigationDropdown" class="flex items-center cursor-pointer">
                        <span class="font-medium text-slate-600">{{ user.name }}</span>
                        <svg class="ml-2 h-4 w-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                    </div>
                    <transition enter-active-class="transition ease-out duration-100" enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100" leave-active-class="transition ease-in duration-75" leave-from-class="transform opacity-100 scale-100" leave-to-class="transform opacity-0 scale-95">
                        <div v-show="showingNavigationDropdown" class="absolute z-50 mt-2 w-48 rounded-md shadow-lg ltr:origin-top-right rtl:origin-top-left end-0">
                            <div class="rounded-md ring-1 ring-black ring-opacity-5 py-1 bg-white">
                                <Link :href="route('logout')" method="post" as="button" class="block w-full px-4 py-2 text-sm leading-5 text-slate-700 rtl:text-right ltr:text-left hover:bg-slate-100">تسجيل الخروج</Link>
                            </div>
                        </div>
                    </transition>
                </div>
            </header>
            
            <!-- Page Heading Slot -->
            <div v-if="$slots.header" class="bg-white shadow-sm">
                <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </div>

            <!-- Page Content Slot -->
            <main class="flex-1 overflow-y-auto">
                <slot />
            </main>
        </div>
    </div>
</template>
