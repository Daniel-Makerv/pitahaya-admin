<script setup lang="ts">
import AppLayout from "@/layouts/AppLayout.vue";
import { forms as formsRoute } from "@/routes";
import type { BreadcrumbItem } from "@/types";
import { Head, Link, router } from "@inertiajs/vue3";
import { ref, computed } from "vue";

// Props opcionales en caso de que pases los datos desde el Controlador de Laravel
// const props = defineProps<{ formulariosBase: any[] }>();

const breadcrumbs: BreadcrumbItem[] = [{ title: "Formularios", href: formsRoute().url }];

const searchQuery = ref("");

// Mock data reactivo (reemplazar por props.formulariosBase si viene de Laravel)
const formulariosBase = ref([
    { id: 1, title: "Planta", description: "", total_preguntas: 12, created_at: "15/06/2026" },
    { id: 2, title: "Alianzas productivas", description: "", total_preguntas: 8, created_at: "18/06/2026" },
    { id: 3, title: "Canales comerciales", description: "", total_preguntas: 5, created_at: "19/06/2026" },
    { id: 4, title: "Asesoría técnica", description: "", total_preguntas: 0, created_at: "19/06/2026" },
    { id: 5, title: "Proceso/Congelado/Deshidratado", description: "", total_preguntas: 0, created_at: "19/06/2026" },
    { id: 6, title: "Colaboraciones", description: "", total_preguntas: 0, created_at: "19/06/2026" },
    { id: 7, title: "Solo Información", description: "", total_preguntas: 0, created_at: "19/06/2026" }
]);

// Buscador reactivo mediante computed
const filteredFormularios = computed(() => {
    return formulariosBase.value.filter(item =>
        item.title.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        item.description.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
});

const openCreateModal = () => {
    // Tu lógica para abrir modal o redirigir a la creación
    console.log("Abrir modal de creación");
};

const deleteFormulario = (id: number) => {
    if (confirm("¿Estás seguro de eliminar esta pregunta principal? Se borrarán todas las preguntas relacionadas.")) {
        // Ejemplo de eliminación con Inertia:
        // router.delete(route('forms.destroy', id));
        formulariosBase.value = formulariosBase.value.filter(item => item.id !== id);
    }
};
</script>

<template>

    <Head title="Formularios Base" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-6 min-h-screen">

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">Administración de Cuestionarios</h1>
                    <p class="text-sm text-slate-500 mt-1">Monitorea y edita las secciones principales de tus
                        cuestionarios</p>
                </div>
                <button @click="openCreateModal"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Nueva Pregunta Secundaria
                </button>
                <button @click="openCreateModal"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Nueva Pregunta Principal
                </button>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="relative w-full sm:max-w-md">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24"
                            stroke="#64748B" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <input v-model="searchQuery" type="text" placeholder="Buscar por título o descripción..."
                        class="w-full pl-10 pr-4 py-2  text-sm text-slate-900 placeholder-slate-400 border border-slate-200 rounded-lg shadow-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors" />
                </div>
                <div class="text-xs text-slate-500 font-medium">
                    Mostrando <span class="text-slate-800 font-bold">{{ filteredFormularios.length }}</span>
                    cuestionarios base
                </div>
            </div>

            <div class="overflow-x-auto rounded-xl border border-slate-200  shadow-sm">
                <table class="w-full table-auto border-collapse text-left">
                    <thead>
                        <tr
                            class="border-b border-slate-200 bg-slate-50/70 text-xs font-semibold uppercase tracking-wider text-slate-500">
                            <th class="px-6 py-3.5 w-[35%]">Pregunta Principal / Base</th>
                            <th class="px-6 py-3.5 w-[30%]">Descripción</th>
                            <th class="px-6 py-3.5 w-[15%] text-center">Preguntas asociadas</th>
                            <th class="px-6 py-3.5 w-[10%]">F. Creación</th>
                            <th class="px-6 py-3.5 w-[10%] text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 text-sm">
                        <tr v-for="base in filteredFormularios" :key="base.id"
                            class="hover:bg-slate-50/50 transition-colors group">
                            <td class="px-6 py-4 font-semibold text-slate-900">
                                {{ base.title }}
                            </td>

                            <td class="px-6 py-4 text-slate-500 font-normal line-clamp-2 mt-2 sm:mt-0">
                                {{ base.description || 'Sin descripción' }}
                            </td>

                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <span
                                    class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700 ring-1 ring-inset ring-blue-700/10">
                                    {{ base.total_preguntas }} ítems
                                </span>
                            </td>

                            <td class="px-6 py-4 text-slate-500 whitespace-nowrap">
                                {{ base.created_at }}
                            </td>

                            <!-- Acciones -->
                            <td class="px-6 py-4 text-end whitespace-nowrap">
                                <div class="inline-flex items-center justify-end gap-3 w-full">

                                    <!-- Link de Inertia dinámico con el ID del cuestionario -->
                                    <Link :href="`/questions/${base.id}/edit`"
                                        class="inline-flex items-center gap-1 rounded-md bg-slate-100 px-2.5 py-1.5 text-xs font-semibold text-blue-600 hover:bg-slate-200 transition-colors">
                                        <span>Ver más</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </Link>

                                    <!-- Botón Eliminar Rápido -->
                                    <button @click="deleteFormulario(base.id)"
                                        class="text-slate-400 hover:text-red-600 p-1 rounded transition-colors"
                                        title="Eliminar cuestionario">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr v-if="filteredFormularios.length === 0">
                            <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <svg class="text-slate-300" xmlns="http://www.w3.org/2000/svg" width="28"
                                        height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                        stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <p class="text-sm font-medium">No se encontraron cuestionarios base.</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>
    </AppLayout>
</template>
