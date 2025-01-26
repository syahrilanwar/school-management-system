<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <section class="min-h-screen bg-gray-50 dark:bg-gray-900">
        <div
            class="mx-auto flex flex-col items-center justify-center px-6 py-8 md:h-screen lg:py-0"
        >
            <a
                href="#"
                class="mb-6 flex items-center text-2xl font-semibold text-gray-900 dark:text-white"
            >
                <Link href="/">
                    <ApplicationLogo class="h-20" />
                </Link>
            </a>
            <div
                class="w-full rounded-lg bg-white shadow dark:border dark:border-gray-700 dark:bg-gray-800 sm:max-w-md md:mt-0 xl:p-0"
            >
                <slot />
            </div>
        </div>
    </section>
</template>
