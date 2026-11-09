<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { forms as formsRoute } from '@/routes';
import type { BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface Answer {
    question: string;
    answer: string;
}

interface Registro {
    id: number;
    name: string;
    type: string;
    answered_at: string | null;
    received_at: string | null;
    answers: Answer[];
}

const props = defineProps<{
    registro: Registro;
}>();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { title: 'Formularios', href: formsRoute().url },
    { title: props.registro.name, href: `/forms/${props.registro.id}/view` },
]);

const pdfUrl = computed(() => `/forms/${props.registro.id}/pdf`);

// Evita doble clic mientras se genera el PDF
const downloading = ref(false);
const downloadPdf = () => {
    downloading.value = true;
    window.location.href = pdfUrl.value;
    setTimeout(() => (downloading.value = false), 2500);
};

const isEmpty = (answer: string) => answer === 'Sin respuesta';

const deleting = ref(false);
const deleteForm = () => {
    if (
        !confirm(
            `¿Eliminar la encuesta de "${props.registro.name}"? Esta acción no se puede deshacer.`,
        )
    )
        return;

    router.delete(`/forms/${props.registro.id}`, {
        onStart: () => (deleting.value = true),
        onFinish: () => (deleting.value = false),
    });
};
</script>

<template>
    <Head :title="`Encuesta - ${registro.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex min-h-screen flex-1 flex-col gap-6 bg-[#F8FAFC] p-6">
            <Link
                :href="formsRoute().url"
                class="inline-flex w-fit items-center gap-1.5 text-sm font-medium text-slate-500 transition-colors hover:text-slate-900"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="14"
                    height="14"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="2.5"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M15 19l-7-7 7-7"
                    />
                </svg>
                Volver a formularios
            </Link>

            <!-- Encabezado -->
            <div
                class="rounded-xl border border-l-4 border-slate-200 border-l-blue-600 bg-white p-6 shadow-sm"
            >
                <div
                    class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
                >
                    <div class="min-w-0">
                        <span
                            class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                        >
                            {{ registro.type }}
                        </span>
                        <h1
                            class="mt-0.5 truncate text-xl font-bold text-slate-900"
                        >
                            {{ registro.name }}
                        </h1>
                        <p class="mt-1 text-sm text-slate-500">
                            <template v-if="registro.answered_at"
                                >Contestada el {{ registro.answered_at }} ·
                            </template>
                            {{ registro.answers.length }}
                            {{
                                registro.answers.length === 1
                                    ? 'respuesta'
                                    : 'respuestas'
                            }}
                        </p>
                    </div>

                    <div class="flex shrink-0 flex-col gap-2 sm:flex-row">
                        <button
                            type="button"
                            @click="deleteForm"
                            :disabled="deleting"
                            class="inline-flex items-center justify-center gap-2 rounded-lg border border-red-200 bg-white px-4 py-2 text-sm font-semibold text-red-600 shadow-sm transition-colors hover:bg-red-50 disabled:cursor-wait disabled:opacity-60"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="16"
                                height="16"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                />
                            </svg>
                            {{ deleting ? 'Eliminando...' : 'Eliminar' }}
                        </button>
                        <button
                            type="button"
                            @click="downloadPdf"
                            :disabled="downloading"
                            class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-blue-700 disabled:cursor-wait disabled:opacity-60"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="16"
                                height="16"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M12 10v6m0 0l-3-3m3 3l3-3M6 20h12a2 2 0 002-2V8.414a1 1 0 00-.293-.707l-4.414-4.414A1 1 0 0014.586 3H6a2 2 0 00-2 2v13a2 2 0 002 2z"
                                />
                            </svg>
                            {{
                                downloading
                                    ? 'Generando PDF...'
                                    : 'Exportar PDF'
                            }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Preguntas y respuestas -->
            <div
                class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
            >
                <ol
                    v-if="registro.answers.length"
                    class="divide-y divide-slate-200"
                >
                    <li
                        v-for="(item, index) in registro.answers"
                        :key="index"
                        class="flex gap-4 px-6 py-4"
                    >
                        <span
                            class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-slate-100 text-xs font-semibold text-slate-500"
                        >
                            {{ index + 1 }}
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-medium text-slate-500">
                                {{ item.question }}
                            </p>
                            <p
                                class="mt-1 text-sm break-words"
                                :class="
                                    isEmpty(item.answer)
                                        ? 'text-slate-400 italic'
                                        : 'font-semibold text-slate-900'
                                "
                            >
                                {{ item.answer }}
                            </p>
                        </div>
                    </li>
                </ol>

                <p v-else class="px-6 py-12 text-center text-sm text-slate-500">
                    Este registro no tiene respuestas.
                </p>
            </div>
        </div>
    </AppLayout>
</template>
