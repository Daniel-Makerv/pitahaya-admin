<script setup lang="ts">
import GeneralQuestionPicker from '@/components/GeneralQuestionPicker.vue';
import QuestionFormModal from '@/components/QuestionFormModal.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { forms as formsRoute } from '@/routes';
import type { BreadcrumbItem } from '@/types';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import Draggable from 'vuedraggable';

interface FormularioBase {
    id: number;
    title: string;
    description: string | null;
}

interface Question {
    id: number;
    text: string;
    type: string | null;
    order: number;
    options: string[];
}

interface PreguntaGeneral {
    id: number;
    text: string;
    type: string | null;
    options: string[];
    added: boolean;
}

interface Tipo {
    id: number;
    name: string;
    str: string | null;
}

const props = defineProps<{
    formularioBase: FormularioBase;
    preguntas: Question[];
    preguntasGenerales: PreguntaGeneral[];
    tipos: Tipo[];
}>();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { title: 'Formularios', href: formsRoute().url },
    {
        title: props.formularioBase.title,
        href: `/questions/${props.formularioBase.id}/edit`,
    },
]);

// Copia local para poder reordenar; se resincroniza cuando Inertia trae datos nuevos
const localQuestions = ref<Question[]>([...props.preguntas]);
watch(
    () => props.preguntas,
    (value) => (localQuestions.value = [...value]),
);

const searchQuery = ref('');
const savingOrder = ref(false);

// ---------- Modales ----------
const showModal = ref(false);
const showPicker = ref(false);

// ---------- Búsqueda ----------
const isSearching = computed(() => searchQuery.value.trim() !== '');

const matches = (question: Question) =>
    question.text
        .toLowerCase()
        .includes(searchQuery.value.trim().toLowerCase());

const filteredQuestions = computed(() => localQuestions.value.filter(matches));

// ---------- Eliminar pregunta ----------
const deletingId = ref<number | null>(null);

const deleteQuestion = (question: Question) => {
    if (
        !confirm(
            `¿Eliminar la pregunta "${question.text}"? Esta acción no se puede deshacer.`,
        )
    )
        return;

    deletingId.value = question.id;
    router.delete(
        `/questions/${props.formularioBase.id}/questions/${question.id}`,
        {
            preserveScroll: true,
            onFinish: () => (deletingId.value = null),
        },
    );
};

// ---------- Editar texto de la pregunta ----------
const editingQuestion = ref<Question | null>(null);
const editInput = ref<HTMLTextAreaElement | null>(null);

const editForm = useForm({
    text: '',
});

const openEditModal = async (question: Question) => {
    editingQuestion.value = question;
    editForm.clearErrors();
    editForm.text = question.text;
    await nextTick();
    editInput.value?.focus();
    editInput.value?.select();
};

const closeEditModal = () => {
    if (editForm.processing) return;
    editingQuestion.value = null;
};

const canSaveEdit = computed(
    () =>
        !editForm.processing &&
        editForm.text.trim() !== '' &&
        editForm.text.trim() !== editingQuestion.value?.text,
);

const submitEdit = () => {
    if (!editingQuestion.value || !canSaveEdit.value) return;

    editForm.put(
        `/questions/${props.formularioBase.id}/questions/${editingQuestion.value.id}`,
        {
            preserveScroll: true,
            onSuccess: () => {
                editingQuestion.value = null;
                editForm.reset();
            },
        },
    );
};

const onKeydown = (e: KeyboardEvent) => {
    if (e.key === 'Escape' && editingQuestion.value) closeEditModal();
};

onMounted(() => window.addEventListener('keydown', onKeydown));
onBeforeUnmount(() => window.removeEventListener('keydown', onKeydown));

// ---------- Reordenar ----------
const handleDragEnd = () => {
    localQuestions.value = localQuestions.value.map((question, index) => ({
        ...question,
        order: index + 1,
    }));

    savingOrder.value = true;
    router.post(
        `/questions/${props.formularioBase.id}/reorder`,
        { questions: localQuestions.value.map((q) => q.id) },
        {
            preserveScroll: true,
            preserveState: true,
            onFinish: () => (savingOrder.value = false),
        },
    );
};
</script>

<template>
    <Head :title="formularioBase.title" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex min-h-screen flex-1 flex-col gap-6 bg-[#F8FAFC] p-6">
            <Link
                href="/questions"
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
                Volver a cuestionarios
            </Link>

            <div
                class="rounded-xl border border-l-4 border-slate-200 border-l-blue-600 bg-white p-6 shadow-sm"
            >
                <div
                    class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
                >
                    <div>
                        <span
                            class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                            >Pregunta principal</span
                        >
                        <h2 class="mt-0.5 text-xl font-bold text-slate-900">
                            {{ formularioBase.title }}
                        </h2>
                        <p class="mt-1 text-sm text-slate-500">
                            {{
                                formularioBase.description || 'Sin descripción'
                            }}
                        </p>
                    </div>
                    <div
                        class="flex flex-col items-stretch gap-2 sm:flex-row sm:items-center"
                    >
                        <button
                            type="button"
                            @click="showPicker = true"
                            class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition-colors hover:bg-slate-50"
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
                                    d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"
                                />
                            </svg>
                            Desde preguntas generales
                        </button>
                        <button
                            type="button"
                            @click="showModal = true"
                            class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-blue-700"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="16"
                                height="16"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="2.5"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M12 4v16m8-8H4"
                                />
                            </svg>
                            Agregar pregunta
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-4">
                <div
                    class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center"
                >
                    <div class="relative w-full sm:max-w-xs">
                        <span
                            class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="16"
                                height="16"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="#64748B"
                                stroke-width="2"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                />
                            </svg>
                        </span>
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Buscar pregunta..."
                            class="w-full rounded-lg border border-slate-200 bg-white py-1.5 pr-4 pl-9 text-xs text-slate-900 placeholder-slate-400 shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none"
                        />
                    </div>
                    <p class="text-xs text-slate-400">
                        <template v-if="savingOrder"
                            >Guardando orden...</template
                        >
                        <template v-else-if="isSearching"
                            >Limpia la búsqueda para reordenar.</template
                        >
                        <template v-else>
                            Consejo: arrastra las filas desde el icono
                            <span class="font-bold">☰</span> para cambiar el
                            orden.
                        </template>
                    </p>
                </div>

                <div
                    class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm"
                >
                    <table class="w-full table-auto border-collapse text-left">
                        <thead>
                            <tr
                                class="border-b border-slate-200 bg-slate-50/70 text-xs font-semibold tracking-wider text-slate-500 uppercase"
                            >
                                <th class="w-[80px] px-6 py-3">Orden</th>
                                <th class="px-6 py-3">Pregunta</th>
                                <th class="w-[180px] px-6 py-3">
                                    Tipo de pregunta
                                </th>
                                <th class="w-[100px] px-6 py-3 text-end">
                                    Acciones
                                </th>
                            </tr>
                        </thead>

                        <Draggable
                            v-model="localQuestions"
                            item-key="id"
                            tag="tbody"
                            handle=".drag-handle"
                            :disabled="isSearching || savingOrder"
                            @end="handleDragEnd"
                            class="divide-y divide-slate-200 text-sm"
                        >
                            <template #item="{ element, index }">
                                <tr
                                    v-show="matches(element)"
                                    class="transition-colors hover:bg-slate-50/40"
                                    :class="{
                                        'opacity-50': deletingId === element.id,
                                    }"
                                >
                                    <td class="px-6 py-3.5">
                                        <div class="flex items-center gap-2">
                                            <svg
                                                class="drag-handle text-slate-400 hover:text-slate-600"
                                                :class="
                                                    isSearching
                                                        ? 'cursor-not-allowed opacity-40'
                                                        : 'cursor-move'
                                                "
                                                xmlns="http://www.w3.org/2000/svg"
                                                width="16"
                                                height="16"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                                stroke-width="2.5"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    d="M4 6h16M4 12h16M4 18h16"
                                                />
                                            </svg>
                                            <span
                                                class="text-xs font-medium text-slate-600"
                                                >{{ index + 1 }}</span
                                            >
                                        </div>
                                    </td>

                                    <td class="px-6 py-3.5">
                                        <p class="font-medium text-slate-900">
                                            {{ element.text }}
                                        </p>
                                        <div
                                            v-if="element.options?.length"
                                            class="mt-1.5 flex flex-wrap gap-1.5"
                                        >
                                            <span
                                                v-for="option in element.options"
                                                :key="option"
                                                class="inline-flex items-center rounded-full border border-slate-200 bg-white px-2 py-0.5 text-[11px] font-medium text-slate-600"
                                            >
                                                {{ option }}
                                            </span>
                                        </div>
                                    </td>

                                    <td class="px-6 py-3.5">
                                        <span
                                            class="inline-flex items-center rounded-md bg-slate-100 px-2 py-1 text-xs font-medium text-slate-600"
                                        >
                                            {{ element.type || 'Sin tipo' }}
                                        </span>
                                    </td>

                                    <td
                                        class="px-6 py-3.5 text-end whitespace-nowrap"
                                    >
                                        <button
                                            type="button"
                                            @click="openEditModal(element)"
                                            class="rounded p-1 text-slate-400 transition-colors hover:text-blue-600"
                                            title="Editar pregunta"
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
                                                    d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125"
                                                />
                                            </svg>
                                        </button>
                                        <button
                                            type="button"
                                            @click="deleteQuestion(element)"
                                            :disabled="
                                                deletingId === element.id
                                            "
                                            class="rounded p-1 text-slate-400 transition-colors hover:text-red-600 disabled:cursor-wait"
                                            title="Eliminar pregunta"
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
                                        </button>
                                    </td>
                                </tr>
                            </template>

                            <template #footer>
                                <tr v-if="filteredQuestions.length === 0">
                                    <td
                                        colspan="4"
                                        class="px-6 py-12 text-center text-slate-500"
                                    >
                                        <div
                                            class="flex flex-col items-center justify-center gap-2"
                                        >
                                            <svg
                                                class="text-slate-300"
                                                xmlns="http://www.w3.org/2000/svg"
                                                width="24"
                                                height="24"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                                stroke-width="2"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"
                                                />
                                            </svg>
                                            <p class="text-sm font-medium">
                                                {{
                                                    isSearching
                                                        ? 'Ninguna pregunta coincide con la búsqueda.'
                                                        : 'Esta pregunta principal aún no tiene preguntas.'
                                                }}
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </Draggable>
                    </table>
                </div>
            </div>
        </div>

        <QuestionFormModal
            :show="showModal"
            :tipos="tipos"
            :url="`/questions/${formularioBase.id}/questions`"
            title="Agregar pregunta"
            :description="`Se agregará al final de «${formularioBase.title}». Después puedes cambiar su orden arrastrándola.`"
            @close="showModal = false"
        />

        <!-- Modal: Editar texto de la pregunta -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-150 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition duration-100 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="editingQuestion"
                    class="fixed inset-0 z-50 flex items-center justify-center p-4"
                >
                    <div
                        class="absolute inset-0 bg-slate-900/40 backdrop-blur-[2px]"
                        @click="closeEditModal"
                    />

                    <div
                        role="dialog"
                        aria-modal="true"
                        aria-labelledby="edit-question-title"
                        class="relative w-full max-w-lg rounded-xl bg-white shadow-xl ring-1 ring-slate-200"
                    >
                        <form @submit.prevent="submitEdit">
                            <div class="px-6 pt-6">
                                <h2
                                    id="edit-question-title"
                                    class="text-lg font-semibold text-slate-900"
                                >
                                    Editar pregunta
                                </h2>
                                <p class="mt-1 text-sm text-slate-500">
                                    Solo cambia el texto. El tipo y las opciones
                                    se conservan.
                                </p>
                            </div>

                            <div class="px-6 py-5">
                                <label
                                    for="edit-question-text"
                                    class="block text-sm font-medium text-slate-700"
                                    >Pregunta</label
                                >
                                <textarea
                                    id="edit-question-text"
                                    ref="editInput"
                                    v-model="editForm.text"
                                    rows="3"
                                    maxlength="1000"
                                    class="mt-1.5 w-full resize-none rounded-lg border px-3 py-2 text-sm text-slate-900 placeholder-slate-400 shadow-sm transition-colors focus:ring-1 focus:outline-none"
                                    :class="
                                        editForm.errors.text
                                            ? 'border-red-300 focus:border-red-500 focus:ring-red-500'
                                            : 'border-slate-200 focus:border-blue-500 focus:ring-blue-500'
                                    "
                                    @keydown.enter.exact.prevent="submitEdit"
                                />
                                <p
                                    v-if="editForm.errors.text"
                                    class="mt-1.5 text-xs text-red-600"
                                >
                                    {{ editForm.errors.text }}
                                </p>
                            </div>

                            <div
                                class="flex items-center justify-end gap-3 rounded-b-xl border-t border-slate-100 bg-slate-50/60 px-6 py-4"
                            >
                                <button
                                    type="button"
                                    @click="closeEditModal"
                                    :disabled="editForm.processing"
                                    class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 transition-colors hover:bg-slate-100"
                                >
                                    Cancelar
                                </button>
                                <button
                                    type="submit"
                                    :disabled="!canSaveEdit"
                                    class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    {{
                                        editForm.processing
                                            ? 'Guardando...'
                                            : 'Guardar cambios'
                                    }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <GeneralQuestionPicker
            :show="showPicker"
            :preguntas="preguntasGenerales"
            :url="`/questions/${formularioBase.id}/import`"
            @close="showPicker = false"
        />
    </AppLayout>
</template>
