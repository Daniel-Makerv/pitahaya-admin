<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Formularios WhatsApp',
        href: '/whatsapp/forms',
    },
    {
        title: 'Nuevo formulario',
        href: '/whatsapp/forms/create',
    },
];

const form = useForm({
    name: '',
    description: '',
});

const submit = () => {
    form.post('/whatsapp/forms');
};
</script>

<template>
    <Head title="Nuevo formulario WhatsApp" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    Nuevo formulario
                </h1>

                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Crea un nuevo flujo para el agente de WhatsApp.
                </p>
            </div>

            <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-800">
                <form @submit.prevent="submit">
                    <div class="mb-5">
                        <label
                            class="mb-2 block text-sm font-medium text-gray-900 dark:text-white"
                        >
                            Nombre
                        </label>

                        <input
                            v-model="form.name"
                            type="text"
                            class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            placeholder="Ej. Prospectos Pitamex"
                        />

                        <p
                            v-if="form.errors.name"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <div class="mb-6">
                        <label
                            class="mb-2 block text-sm font-medium text-gray-900 dark:text-white"
                        >
                            Descripción
                        </label>

                        <textarea
                            v-model="form.description"
                            rows="4"
                            class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            placeholder="Formulario para nuevos prospectos..."
                        />
                    </div>

                    <div class="flex justify-end gap-2">
                        <Link
                            href="/whatsapp/forms"
                            class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium hover:bg-gray-100 dark:border-gray-600 dark:text-white"
                        >
                            Cancelar
                        </Link>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
                        >
                            {{
                                form.processing
                                    ? 'Creando...'
                                    : 'Crear formulario'
                            }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
