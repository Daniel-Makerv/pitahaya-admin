<script setup lang="ts">
import AppLayout from "@/layouts/AppLayout.vue";
import { forms as formsRoute } from "@/routes";
import type { BreadcrumbItem } from "@/types";
import { Head, Link, router, useForm } from "@inertiajs/vue3";
import { ref, computed, nextTick, onMounted, onBeforeUnmount } from "vue";

interface PreguntaPrincipal {
    id: number;
    title: string;
    description: string | null;
    total_preguntas: number;
    created_at: string | null;
}

const props = defineProps<{
    preguntasPrincipales: PreguntaPrincipal[];
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: "Formularios", href: formsRoute().url }];

// ---------- Buscador ----------
const searchQuery = ref("");

const filteredFormularios = computed(() => {
    const q = searchQuery.value.trim().toLowerCase();
    if (!q) return props.preguntasPrincipales;

    return props.preguntasPrincipales.filter(item =>
        item.title.toLowerCase().includes(q) ||
        (item.description ?? "").toLowerCase().includes(q)
    );
});

// ---------- Modal: crear / editar pregunta principal ----------
const showCreateModal = ref(false);
const editingId = ref<number | null>(null);
const titleInput = ref<HTMLInputElement | null>(null);

const form = useForm({
    title: "",
    description: "",
});

const openCreateModal = async () => {
    editingId.value = null;
    form.reset();
    form.clearErrors();
    showCreateModal.value = true;
    await nextTick();
    titleInput.value?.focus();
};

const openEditModal = async (base: PreguntaPrincipal) => {
    editingId.value = base.id;
    form.clearErrors();
    form.title = base.title;
    form.description = base.description ?? "";
    showCreateModal.value = true;
    await nextTick();
    titleInput.value?.focus();
    titleInput.value?.select();
};

const closeCreateModal = () => {
    if (form.processing) return;
    showCreateModal.value = false;
};

const submitCreate = () => {
    const options = {
        preserveScroll: true,
        onSuccess: () => {
            showCreateModal.value = false;
            editingId.value = null;
            form.reset();
        },
    };

    if (editingId.value) {
        form.put(`/questions/${editingId.value}`, options);
    } else {
        form.post("/questions", options);
    }
};

const onKeydown = (e: KeyboardEvent) => {
    if (e.key === "Escape" && showCreateModal.value) closeCreateModal();
};

onMounted(() => window.addEventListener("keydown", onKeydown));
onBeforeUnmount(() => window.removeEventListener("keydown", onKeydown));

// ---------- Eliminar ----------
const deletingId = ref<number | null>(null);

const deleteFormulario = (id: number) => {
    if (!confirm("¿Estás seguro de eliminar esta pregunta principal? Se borrarán todas las preguntas relacionadas.")) return;

    deletingId.value = id;
    router.delete(`/questions/${id}`, {
        preserveScroll: true,
        onFinish: () => (deletingId.value = null),
    });
};
</script>

<template>

    <Head title="Formularios Base" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-6 min-h-screen">

            <!-- Encabezado -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">Administración de Cuestionarios</h1>
                    <p class="text-sm text-slate-500 mt-1">Monitorea y edita las secciones principales de tus
                        cuestionarios</p>
                </div>

                <div class="flex flex-col sm:flex-row gap-3">
                    <button type="button" disabled title="Próximamente"
                        class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-400 shadow-sm cursor-not-allowed">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Nueva Pregunta Secundaria
                    </button>
                    <button type="button" @click="openCreateModal"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Nueva Pregunta Principal
                    </button>
                </div>
            </div>

            <!-- Buscador -->
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
                        class="w-full pl-10 pr-4 py-2 text-sm text-slate-900 placeholder-slate-400 border border-slate-200 rounded-lg shadow-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors" />
                </div>
                <div class="text-xs text-slate-500 font-medium">
                    Mostrando <span class="text-slate-800 font-bold">{{ filteredFormularios.length }}</span>
                    cuestionarios base
                </div>
            </div>

            <!-- Tabla -->
            <div class="overflow-x-auto rounded-xl border border-slate-200 shadow-sm">
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
                            class="hover:bg-slate-50/50 transition-colors group"
                            :class="{ 'opacity-50': deletingId === base.id }">
                            <td class="px-6 py-4 font-semibold text-slate-900">
                                {{ base.title }}
                            </td>

                            <td class="px-6 py-4 text-slate-500 font-normal">
                                <span class="line-clamp-2">{{ base.description || 'Sin descripción' }}</span>
                            </td>

                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <span
                                    class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700 ring-1 ring-inset ring-blue-700/10">
                                    {{ base.total_preguntas }} {{ base.total_preguntas === 1 ? 'ítem' : 'ítems' }}
                                </span>
                            </td>

                            <td class="px-6 py-4 text-slate-500 whitespace-nowrap">
                                {{ base.created_at ?? '—' }}
                            </td>

                            <td class="px-6 py-4 text-end whitespace-nowrap">
                                <div class="inline-flex items-center justify-end gap-3 w-full">
                                    <button type="button" @click="openEditModal(base)"
                                        class="text-slate-400 hover:text-blue-600 p-1 rounded transition-colors"
                                        title="Editar pregunta">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
                                        </svg>
                                    </button>

                                    <Link :href="`/questions/${base.id}/edit`"
                                        class="inline-flex items-center gap-1 rounded-md bg-slate-100 px-2.5 py-1.5 text-xs font-semibold text-blue-600 hover:bg-slate-200 transition-colors">
                                        <span>Ver más</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </Link>

                                    <button type="button" @click="deleteFormulario(base.id)"
                                        :disabled="deletingId === base.id"
                                        class="text-slate-400 hover:text-red-600 p-1 rounded transition-colors disabled:cursor-wait"
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
                                    <p class="text-sm font-medium">
                                        {{ searchQuery ? 'No se encontraron cuestionarios con esa búsqueda.' : 'Aún no hay preguntas principales. Crea la primera.' }}
                                    </p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal: Nueva Pregunta Principal -->
        <Teleport to="body">
            <Transition enter-active-class="transition duration-150 ease-out" enter-from-class="opacity-0"
                enter-to-class="opacity-100" leave-active-class="transition duration-100 ease-in"
                leave-from-class="opacity-100" leave-to-class="opacity-0">
                <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-[2px]" @click="closeCreateModal" />

                    <div role="dialog" aria-modal="true" aria-labelledby="create-title"
                        class="relative w-full max-w-lg rounded-xl bg-white shadow-xl ring-1 ring-slate-200">
                        <form @submit.prevent="submitCreate">
                            <div class="px-6 pt-6">
                                <h2 id="create-title" class="text-lg font-semibold text-slate-900">
                                    {{ editingId ? 'Editar pregunta principal' : 'Nueva pregunta principal' }}
                                </h2>
                                <p class="mt-1 text-sm text-slate-500">
                                    {{ editingId
                                        ? 'El cambio se verá en todos los lugares donde aparece esta pregunta.'
                                        : 'Se mostrará al inicio, antes de sus preguntas secundarias.' }}
                                </p>
                            </div>

                            <div class="space-y-4 px-6 py-5">
                                <div>
                                    <label for="title" class="block text-sm font-medium text-slate-700">Título</label>
                                    <input id="title" ref="titleInput" v-model="form.title" type="text"
                                        maxlength="255" placeholder="Ej. Asesoría técnica"
                                        class="mt-1.5 w-full rounded-lg border px-3 py-2 text-sm text-slate-900 placeholder-slate-400 shadow-sm focus:outline-none focus:ring-1 transition-colors"
                                        :class="form.errors.title
                                            ? 'border-red-300 focus:border-red-500 focus:ring-red-500'
                                            : 'border-slate-200 focus:border-blue-500 focus:ring-blue-500'" />
                                    <p v-if="form.errors.title" class="mt-1.5 text-xs text-red-600">{{
                                        form.errors.title }}</p>
                                </div>

                                <div>
                                    <label for="description" class="block text-sm font-medium text-slate-700">
                                        Descripción <span class="font-normal text-slate-400">(opcional)</span>
                                    </label>
                                    <textarea id="description" v-model="form.description" rows="3" maxlength="255"
                                        placeholder="¿Para qué sirve este cuestionario?"
                                        class="mt-1.5 w-full resize-none rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-900 placeholder-slate-400 shadow-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors" />
                                    <p v-if="form.errors.description" class="mt-1.5 text-xs text-red-600">{{
                                        form.errors.description }}</p>
                                </div>
                            </div>

                            <div
                                class="flex items-center justify-end gap-3 rounded-b-xl border-t border-slate-100 bg-slate-50/60 px-6 py-4">
                                <button type="button" @click="closeCreateModal" :disabled="form.processing"
                                    class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100 transition-colors">
                                    Cancelar
                                </button>
                                <button type="submit" :disabled="form.processing || !form.title.trim()"
                                    class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                    <svg v-if="form.processing" class="h-4 w-4 animate-spin" viewBox="0 0 24 24"
                                        fill="none">
                                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"
                                            class="opacity-25" />
                                        <path d="M22 12a10 10 0 0 0-10-10" stroke="currentColor" stroke-width="3"
                                            stroke-linecap="round" />
                                    </svg>
                                    {{ form.processing ? 'Guardando...' : (editingId ? 'Guardar cambios' : 'Crear pregunta') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </AppLayout>
</template>
