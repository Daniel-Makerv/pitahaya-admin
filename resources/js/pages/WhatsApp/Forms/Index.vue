<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';

interface WhatsAppForm {
    id: number;
    name: string;
    description: string | null;
    active: boolean;
    blocks_count: number;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

const props = defineProps<{
    forms: {
        data: WhatsAppForm[];
        links: PaginationLink[];
        current_page: number;
        last_page: number;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Formularios WhatsApp',
        href: '/whatsapp/forms',
    },
];

const destroyForm = (form: WhatsAppForm) => {
    if (!confirm(`¿Eliminar el formulario "${form.name}"?`)) {
        return;
    }

    router.delete(`/whatsapp/forms/${form.id}`);
};
</script>

<template>
    <Head title="Formularios WhatsApp" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <!-- Encabezado -->
            <div
                class="flex flex-col gap-4 rounded-xl border border-gray-200 bg-white p-6 md:flex-row md:items-center md:justify-between dark:border-gray-700 dark:bg-gray-900"
            >
                <div>
                    <h1
                        class="text-2xl font-bold text-gray-900 dark:text-white"
                    >
                        Formularios WhatsApp
                    </h1>

                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Crea y administra los flujos de conversación del agente
                        de Pitamex.
                    </p>
                </div>

                <Link
                    href="/whatsapp/forms/create"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 focus:outline-none dark:focus:ring-blue-800"
                >
                    <span class="text-lg leading-none">+</span>
                    Nuevo formulario
                </Link>
            </div>

            <!-- Tabla -->
            <div
                class="relative overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800"
            >
                <table
                    class="w-full text-left text-sm text-gray-500 dark:text-gray-400"
                >
                    <thead
                        class="bg-gray-50 text-xs text-gray-700 uppercase dark:bg-gray-700 dark:text-gray-400"
                    >
                        <tr>
                            <th scope="col" class="px-6 py-4">Nombre</th>

                            <th scope="col" class="px-6 py-4">Bloques</th>

                            <th scope="col" class="px-6 py-4">Estado</th>

                            <th scope="col" class="px-6 py-4 text-right">
                                Acciones
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr
                            v-for="form in props.forms.data"
                            :key="form.id"
                            class="border-b border-gray-200 bg-white transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700"
                        >
                            <!-- Nombre -->
                            <td class="px-6 py-4">
                                <div
                                    class="font-medium text-gray-900 dark:text-white"
                                >
                                    {{ form.name }}
                                </div>

                                <div
                                    v-if="form.description"
                                    class="mt-1 max-w-xl text-sm text-gray-500 dark:text-gray-400"
                                >
                                    {{ form.description }}
                                </div>
                            </td>

                            <!-- Bloques -->
                            <td class="px-6 py-4">
                                <span
                                    class="inline-flex items-center rounded-md bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700 dark:bg-gray-700 dark:text-gray-300"
                                >
                                    {{ form.blocks_count }}
                                    {{
                                        form.blocks_count === 1
                                            ? 'bloque'
                                            : 'bloques'
                                    }}
                                </span>
                            </td>

                            <!-- Estado -->
                            <td class="px-6 py-4">
                                <span
                                    v-if="form.active"
                                    class="inline-flex items-center gap-1.5 rounded-full bg-green-100 px-2.5 py-1 text-xs font-medium text-green-700 dark:bg-green-900/30 dark:text-green-400"
                                >
                                    <span
                                        class="h-1.5 w-1.5 rounded-full bg-green-500"
                                    ></span>
                                    Activo
                                </span>

                                <span
                                    v-else
                                    class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                >
                                    <span
                                        class="h-1.5 w-1.5 rounded-full bg-gray-400"
                                    ></span>
                                    Inactivo
                                </span>
                            </td>

                            <!-- Acciones -->
                            <td class="px-6 py-4 text-right">
                                <div
                                    class="flex items-center justify-end gap-4"
                                >
                                    <Link
                                        :href="`/whatsapp/forms/${form.id}/edit`"
                                        class="font-medium text-blue-600 hover:underline dark:text-blue-500"
                                    >
                                        Editar
                                    </Link>

                                    <button
                                        type="button"
                                        class="font-medium text-red-600 hover:underline dark:text-red-500"
                                        @click="destroyForm(form)"
                                    >
                                        Eliminar
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- Vacío -->
                        <tr v-if="props.forms.data.length === 0">
                            <td colspan="4">
                                <div
                                    class="flex min-h-[280px] flex-col items-center justify-center px-6 py-12 text-center"
                                >
                                    <div
                                        class="mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-xl dark:bg-gray-700"
                                    >
                                        💬
                                    </div>

                                    <h3
                                        class="text-base font-semibold text-gray-900 dark:text-white"
                                    >
                                        Aún no hay formularios
                                    </h3>

                                    <p
                                        class="mt-1 max-w-md text-sm text-gray-500 dark:text-gray-400"
                                    >
                                        Crea tu primer formulario para comenzar
                                        a definir el flujo de conversación de
                                        WhatsApp.
                                    </p>

                                    <Link
                                        href="/whatsapp/forms/create"
                                        class="mt-5 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
                                    >
                                        Crear primer formulario
                                    </Link>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Paginación -->
                <div
                    v-if="props.forms.links?.length > 3"
                    class="flex justify-center gap-2 border-t border-gray-200 px-6 py-4 dark:border-gray-700"
                >
                    <Link
                        v-for="(link, index) in props.forms.links"
                        :key="index"
                        :href="link.url ?? '#'"
                        v-html="link.label"
                        class="rounded-lg px-3 py-1.5 text-sm"
                        :class="[
                            link.active
                                ? 'bg-blue-600 text-white'
                                : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600',
                            !link.url && 'pointer-events-none opacity-40',
                        ]"
                    />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
