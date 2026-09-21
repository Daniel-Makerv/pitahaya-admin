<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

interface WhatsAppForm {
    id: number;
    name: string;
    description: string | null;
    active: boolean;
    blocks: any[];
}

const props = defineProps<{
    form: WhatsAppForm;
}>();

const showBlockModal = ref(false);

const blockForm = useForm({
    name: '',
    is_start: false,
    is_final: false,
});

const openBlockModal = () => {
    blockForm.reset();

    // Si todavía no existen bloques,
    // el primero será automáticamente el inicial.
    blockForm.is_start = props.form.blocks.length === 0;

    showBlockModal.value = true;
};

const closeBlockModal = () => {
    showBlockModal.value = false;
    blockForm.reset();
    blockForm.clearErrors();
};

const createBlock = () => {
    blockForm.post(`/whatsapp/forms/${props.form.id}/blocks`, {
        preserveScroll: true,

        onSuccess: () => {
            closeBlockModal();
        },
    });
};

const showQuestionModal = ref(false);
const selectedBlock = ref<any>(null);

const questionForm = useForm({
    text: '',
    type: 'text',
    required: true,
});

const openQuestionModal = (block: any) => {
    selectedBlock.value = block;

    questionForm.reset();
    questionForm.type = 'text';
    questionForm.required = true;

    showQuestionModal.value = true;
};

const closeQuestionModal = () => {
    showQuestionModal.value = false;
    selectedBlock.value = null;

    questionForm.reset();
    questionForm.clearErrors();
};

const createQuestion = () => {
    if (!selectedBlock.value) {
        return;
    }

    questionForm.post(
        `/whatsapp/forms/${props.form.id}/blocks/${selectedBlock.value.id}/questions`,
        {
            preserveScroll: true,

            onSuccess: () => {
                closeQuestionModal();
            },
        },
    );
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'WhatsApp',
        href: '/whatsapp/forms',
    },
    {
        title: props.form.name,
        href: `/whatsapp/forms/${props.form.id}/edit`,
    },
];
</script>

<template>
    <Head :title="form.name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div
                class="flex items-center justify-between rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-900"
            >
                <div>
                    <h1
                        class="text-2xl font-bold text-gray-900 dark:text-white"
                    >
                        {{ form.name }}
                    </h1>

                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {{ form.description || 'Sin descripción' }}
                    </p>
                </div>

                <Link
                    href="/whatsapp/forms"
                    class="rounded-lg border border-gray-300 px-4 py-2 text-sm dark:border-gray-600 dark:text-white"
                >
                    Volver
                </Link>
            </div>

            <div
                class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <h2
                            class="text-lg font-semibold text-gray-900 dark:text-white"
                        >
                            Bloques del formulario
                        </h2>

                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Aquí construiremos el flujo de conversación.
                        </p>
                    </div>

                    <button
                        type="button"
                        @click="openBlockModal"
                        class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
                    >
                        + Agregar bloque
                    </button>
                </div>

                <div
                    v-if="!form.blocks?.length"
                    class="mt-6 flex min-h-64 items-center justify-center rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600"
                >
                    <div class="text-center">
                        <p class="font-medium text-gray-900 dark:text-white">
                            No hay bloques todavía
                        </p>

                        <p
                            class="mt-1 text-sm text-gray-500 dark:text-gray-400"
                        >
                            Agrega el primer bloque para comenzar el flujo.
                        </p>
                    </div>
                </div>

                <div v-else class="mt-6 space-y-4">
                    <div
                        v-for="block in form.blocks"
                        :key="block.id"
                        class="rounded-xl border border-gray-200 bg-gray-50 p-5 dark:border-gray-700 dark:bg-gray-900"
                    >
                        <!-- Header del bloque -->
                        <div class="flex items-start justify-between">
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3
                                        class="font-semibold text-gray-900 dark:text-white"
                                    >
                                        {{ block.name }}
                                    </h3>

                                    <span
                                        v-if="block.is_start"
                                        class="rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-400"
                                    >
                                        Inicio
                                    </span>

                                    <span
                                        v-if="block.is_final"
                                        class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700 dark:bg-green-900/30 dark:text-green-400"
                                    >
                                        Final
                                    </span>
                                </div>

                                <p
                                    class="mt-1 text-sm text-gray-500 dark:text-gray-400"
                                >
                                    {{ block.questions?.length ?? 0 }}
                                    preguntas
                                </p>
                            </div>

                            <button
                                type="button"
                                @click="openQuestionModal(block)"
                                class="text-sm font-medium text-blue-600 hover:underline dark:text-blue-400"
                            >
                                + Agregar pregunta
                            </button>
                        </div>
                        <!-- ↑ aquí cerramos el header -->

                        <!-- Preguntas -->
                        <div
                            v-if="block.questions?.length"
                            class="mt-5 space-y-3"
                        >
                            <div
                                v-for="(question, index) in block.questions"
                                :key="question.id"
                                class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800"
                            >
                                <div
                                    class="flex items-start justify-between gap-4"
                                >
                                    <div class="flex items-start gap-3">
                                        <div
                                            class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-blue-100 text-xs font-semibold text-blue-700 dark:bg-blue-900/30 dark:text-blue-400"
                                        >
                                            {{ index + 1 }}
                                        </div>

                                        <div>
                                            <p
                                                class="font-medium text-gray-900 dark:text-white"
                                            >
                                                {{ question.text }}
                                            </p>

                                            <div
                                                class="mt-2 flex items-center gap-2"
                                            >
                                                <span
                                                    class="rounded-md bg-gray-100 px-2 py-1 text-xs text-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                                >
                                                    {{
                                                        question.type === 'text'
                                                            ? 'Texto'
                                                            : question.type ===
                                                                'number'
                                                              ? 'Número'
                                                              : question.type ===
                                                                  'single_choice'
                                                                ? 'Selección única'
                                                                : 'Selección múltiple'
                                                    }}
                                                </span>

                                                <span
                                                    v-if="question.required"
                                                    class="text-xs font-medium text-red-500"
                                                >
                                                    Obligatoria
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <button
                                        v-if="
                                            question.type === 'single_choice' ||
                                            question.type === 'multiple_choice'
                                        "
                                        type="button"
                                        class="text-sm font-medium text-blue-600 hover:underline dark:text-blue-400"
                                    >
                                        + Opción
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Sin preguntas -->
                        <div
                            v-else
                            class="mt-5 rounded-lg border border-dashed border-gray-300 p-5 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400"
                        >
                            Este bloque todavía no tiene preguntas.
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal agregar bloque -->
        <div
            v-if="showBlockModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
            @click.self="closeBlockModal"
        >
            <div
                class="w-full max-w-lg rounded-xl bg-white p-6 shadow-xl dark:bg-gray-800"
            >
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h2
                            class="text-lg font-semibold text-gray-900 dark:text-white"
                        >
                            Nuevo bloque
                        </h2>

                        <p
                            class="mt-1 text-sm text-gray-500 dark:text-gray-400"
                        >
                            Crea una nueva sección del flujo.
                        </p>
                    </div>

                    <button
                        type="button"
                        @click="closeBlockModal"
                        class="text-xl text-gray-400 hover:text-gray-600 dark:hover:text-white"
                    >
                        ×
                    </button>
                </div>

                <form @submit.prevent="createBlock">
                    <!-- Nombre -->
                    <div class="mb-5">
                        <label
                            class="mb-2 block text-sm font-medium text-gray-900 dark:text-white"
                        >
                            Nombre del bloque
                        </label>

                        <input
                            v-model="blockForm.name"
                            type="text"
                            autofocus
                            placeholder="Ej. Plantas"
                            class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        />

                        <p
                            v-if="blockForm.errors.name"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ blockForm.errors.name }}
                        </p>
                    </div>

                    <!-- Inicio -->
                    <label
                        class="mb-4 flex cursor-pointer items-center gap-3 rounded-lg border border-gray-200 p-4 dark:border-gray-700"
                    >
                        <input
                            v-model="blockForm.is_start"
                            type="checkbox"
                            class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                        />

                        <div>
                            <div
                                class="text-sm font-medium text-gray-900 dark:text-white"
                            >
                                Bloque inicial
                            </div>

                            <div class="text-xs text-gray-500">
                                Aquí comenzará el formulario.
                            </div>
                        </div>
                    </label>

                    <!-- Final -->
                    <label
                        class="flex cursor-pointer items-center gap-3 rounded-lg border border-gray-200 p-4 dark:border-gray-700"
                    >
                        <input
                            v-model="blockForm.is_final"
                            type="checkbox"
                            class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                        />

                        <div>
                            <div
                                class="text-sm font-medium text-gray-900 dark:text-white"
                            >
                                Bloque final
                            </div>

                            <div class="text-xs text-gray-500">
                                El flujo puede terminar después de este bloque.
                            </div>
                        </div>
                    </label>

                    <div class="mt-6 flex justify-end gap-2">
                        <button
                            type="button"
                            @click="closeBlockModal"
                            class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                        >
                            Cancelar
                        </button>

                        <button
                            type="submit"
                            :disabled="blockForm.processing"
                            class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            {{
                                blockForm.processing
                                    ? 'Creando...'
                                    : 'Crear bloque'
                            }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal agregar pregunta -->
        <div
            v-if="showQuestionModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
            @click.self="closeQuestionModal"
        >
            <div
                class="w-full max-w-lg rounded-xl bg-white p-6 shadow-xl dark:bg-gray-800"
            >
                <!-- Header -->
                <div class="mb-6 flex items-start justify-between">
                    <div>
                        <h2
                            class="text-lg font-semibold text-gray-900 dark:text-white"
                        >
                            Nueva pregunta
                        </h2>

                        <p
                            class="mt-1 text-sm text-gray-500 dark:text-gray-400"
                        >
                            Bloque:
                            <span class="font-medium">
                                {{ selectedBlock?.name }}
                            </span>
                        </p>
                    </div>

                    <button
                        type="button"
                        @click="closeQuestionModal"
                        class="text-xl text-gray-400 hover:text-gray-600 dark:hover:text-white"
                    >
                        ×
                    </button>
                </div>

                <form @submit.prevent="createQuestion">
                    <!-- Pregunta -->
                    <div class="mb-5">
                        <label
                            class="mb-2 block text-sm font-medium text-gray-900 dark:text-white"
                        >
                            Pregunta
                        </label>

                        <textarea
                            v-model="questionForm.text"
                            rows="3"
                            placeholder="Ej. ¿Qué te interesa?"
                            class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        />

                        <p
                            v-if="questionForm.errors.text"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ questionForm.errors.text }}
                        </p>
                    </div>

                    <!-- Tipo -->
                    <div class="mb-5">
                        <label
                            class="mb-2 block text-sm font-medium text-gray-900 dark:text-white"
                        >
                            Tipo de respuesta
                        </label>

                        <select
                            v-model="questionForm.type"
                            class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        >
                            <option value="text">Texto</option>

                            <option value="number">Número</option>

                            <option value="single_choice">
                                Selección única
                            </option>

                            <option value="multiple_choice">
                                Selección múltiple
                            </option>
                        </select>

                        <p
                            v-if="questionForm.errors.type"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ questionForm.errors.type }}
                        </p>
                    </div>

                    <!-- Obligatoria -->
                    <label
                        class="flex cursor-pointer items-center gap-3 rounded-lg border border-gray-200 p-4 dark:border-gray-700"
                    >
                        <input
                            v-model="questionForm.required"
                            type="checkbox"
                            class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                        />

                        <div>
                            <div
                                class="text-sm font-medium text-gray-900 dark:text-white"
                            >
                                Respuesta obligatoria
                            </div>

                            <div
                                class="text-xs text-gray-500 dark:text-gray-400"
                            >
                                El prospecto debe responder antes de continuar.
                            </div>
                        </div>
                    </label>

                    <!-- Actions -->
                    <div class="mt-6 flex justify-end gap-2">
                        <button
                            type="button"
                            @click="closeQuestionModal"
                            class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                        >
                            Cancelar
                        </button>

                        <button
                            type="submit"
                            :disabled="questionForm.processing"
                            class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
                        >
                            {{
                                questionForm.processing
                                    ? 'Creando...'
                                    : 'Crear pregunta'
                            }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
