<script setup lang="ts">
import AppLayout from "@/layouts/AppLayout.vue";
import { forms as formsRoute } from "@/routes";
import type { BreadcrumbItem } from "@/types";
import { Head, Link, router, useForm } from "@inertiajs/vue3";
import { ref, computed, watch, nextTick, onMounted, onBeforeUnmount } from "vue";
import Draggable from "vuedraggable";

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

interface Tipo {
    id: number;
    name: string;
    str: string | null;
}

const props = defineProps<{
    formularioBase: FormularioBase;
    preguntas: Question[];
    tipos: Tipo[];
}>();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { title: "Formularios", href: formsRoute().url },
    { title: props.formularioBase.title, href: `/questions/${props.formularioBase.id}/edit` },
]);

// Copia local para poder reordenar; se resincroniza cuando Inertia trae datos nuevos
const localQuestions = ref<Question[]>([...props.preguntas]);
watch(() => props.preguntas, (value) => (localQuestions.value = [...value]));

const searchQuery = ref("");
const savingOrder = ref(false);

// ---------- Modal: agregar pregunta ----------
const showModal = ref(false);
const textInput = ref<HTMLTextAreaElement | null>(null);

const form = useForm({
    text: "",
    question_type_id: "" as number | "",
    options: ["", ""] as string[],
});

// Selección única / múltiple (str = select) necesitan opciones
const selectedTipo = computed(() => props.tipos.find(t => t.id === form.question_type_id));
const needsOptions = computed(() => selectedTipo.value?.str === "select");
const cleanOptions = computed(() => form.options.map(o => o.trim()).filter(Boolean));

const canSubmit = computed(() =>
    !form.processing &&
    form.text.trim() !== "" &&
    form.question_type_id !== "" &&
    (!needsOptions.value || cleanOptions.value.length >= 2)
);

const optionInputs = ref<(HTMLInputElement | null)[]>([]);

const addOption = async () => {
    form.options.push("");
    await nextTick();
    optionInputs.value[form.options.length - 1]?.focus();
};

const onOptionEnter = (index: number) => {
    if (!form.options[index].trim()) return;
    if (index === form.options.length - 1) addOption();
    else optionInputs.value[index + 1]?.focus();
};

const removeOption = (index: number) => {
    if (form.options.length > 2) form.options.splice(index, 1);
    else form.options[index] = "";
};

const optionsError = computed(() => {
    const errors = form.errors as Record<string, string>;
    const key = Object.keys(errors).find(k => k === "options" || k.startsWith("options."));
    return key ? errors[key] : null;
});

const openModal = async () => {
    form.reset();
    form.clearErrors();
    showModal.value = true;
    await nextTick();
    textInput.value?.focus();
};

const closeModal = () => {
    if (form.processing) return;
    showModal.value = false;
};

const submitQuestion = () => {
    if (!canSubmit.value) return;

    form.transform(data => ({
        ...data,
        options: needsOptions.value ? cleanOptions.value : [],
    })).post(`/questions/${props.formularioBase.id}/questions`, {
        preserveScroll: true,
        onSuccess: () => {
            showModal.value = false;
            form.reset();
        },
    });
};

const onKeydown = (e: KeyboardEvent) => {
    if (e.key === "Escape" && showModal.value) closeModal();
};

onMounted(() => window.addEventListener("keydown", onKeydown));
onBeforeUnmount(() => window.removeEventListener("keydown", onKeydown));

const isSearching = computed(() => searchQuery.value.trim() !== "");

const matches = (question: Question) =>
    question.text.toLowerCase().includes(searchQuery.value.trim().toLowerCase());

const filteredQuestions = computed(() => localQuestions.value.filter(matches));

// ---------- Eliminar pregunta ----------
const deletingId = ref<number | null>(null);

const deleteQuestion = (question: Question) => {
    if (!confirm(`¿Eliminar la pregunta "${question.text}"? Esta acción no se puede deshacer.`)) return;

    deletingId.value = question.id;
    router.delete(`/questions/${props.formularioBase.id}/questions/${question.id}`, {
        preserveScroll: true,
        onFinish: () => (deletingId.value = null),
    });
};

const handleDragEnd = () => {
    localQuestions.value = localQuestions.value.map((question, index) => ({
        ...question,
        order: index + 1,
    }));

    savingOrder.value = true;
    router.post(
        `/questions/${props.formularioBase.id}/reorder`,
        { questions: localQuestions.value.map(q => q.id) },
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
        <div class="flex flex-1 flex-col gap-6 p-6 bg-[#F8FAFC] min-h-screen">

            <Link href="/questions"
                class="inline-flex w-fit items-center gap-1.5 text-sm font-medium text-slate-500 hover:text-slate-900 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
                Volver a cuestionarios
            </Link>

            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm border-l-4 border-l-blue-600">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Pregunta principal</span>
                        <h2 class="text-xl font-bold text-slate-900 mt-0.5">{{ formularioBase.title }}</h2>
                        <p class="text-sm text-slate-500 mt-1">{{ formularioBase.description || 'Sin descripción' }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="openModal"
                            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                            </svg>
                            Agregar pregunta
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="relative w-full sm:max-w-xs">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none"
                                viewBox="0 0 24 24" stroke="#64748B" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </span>
                        <input v-model="searchQuery" type="text" placeholder="Buscar pregunta..."
                            class="w-full pl-9 pr-4 py-1.5 bg-white text-xs text-slate-900 placeholder-slate-400 border border-slate-200 rounded-lg shadow-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" />
                    </div>
                    <p class="text-xs text-slate-400">
                        <template v-if="savingOrder">Guardando orden...</template>
                        <template v-else-if="isSearching">Limpia la búsqueda para reordenar.</template>
                        <template v-else>
                            Consejo: arrastra las filas desde el icono <span class="font-bold">☰</span> para cambiar
                            el orden.
                        </template>
                    </p>
                </div>

                <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
                    <table class="w-full table-auto border-collapse text-left">
                        <thead>
                            <tr
                                class="border-b border-slate-200 bg-slate-50/70 text-xs font-semibold uppercase tracking-wider text-slate-500">
                                <th class="px-6 py-3 w-[80px]">Orden</th>
                                <th class="px-6 py-3">Pregunta</th>
                                <th class="px-6 py-3 w-[180px]">Tipo de pregunta</th>
                                <th class="px-6 py-3 w-[100px] text-end">Acciones</th>
                            </tr>
                        </thead>

                        <Draggable v-model="localQuestions" item-key="id" tag="tbody" handle=".drag-handle"
                            :disabled="isSearching || savingOrder" @end="handleDragEnd"
                            class="divide-y divide-slate-200 text-sm">
                            <template #item="{ element, index }">
                                <tr v-show="matches(element)" class="hover:bg-slate-50/40 transition-colors"
                                    :class="{ 'opacity-50': deletingId === element.id }">
                                    <td class="px-6 py-3.5">
                                        <div class="flex items-center gap-2">
                                            <svg class="drag-handle text-slate-400 hover:text-slate-600"
                                                :class="isSearching ? 'cursor-not-allowed opacity-40' : 'cursor-move'"
                                                xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M4 6h16M4 12h16M4 18h16" />
                                            </svg>
                                            <span class="text-xs font-medium text-slate-600">{{ index + 1 }}</span>
                                        </div>
                                    </td>

                                    <td class="px-6 py-3.5">
                                        <p class="font-medium text-slate-900">{{ element.text }}</p>
                                        <div v-if="element.options?.length" class="mt-1.5 flex flex-wrap gap-1.5">
                                            <span v-for="option in element.options" :key="option"
                                                class="inline-flex items-center rounded-full border border-slate-200 bg-white px-2 py-0.5 text-[11px] font-medium text-slate-600">
                                                {{ option }}
                                            </span>
                                        </div>
                                    </td>

                                    <td class="px-6 py-3.5">
                                        <span
                                            class="inline-flex items-center rounded-md bg-slate-100 px-2 py-1 text-xs font-medium text-slate-600">
                                            {{ element.type || 'Sin tipo' }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-3.5 text-end">
                                        <button type="button" @click="deleteQuestion(element)"
                                            :disabled="deletingId === element.id"
                                            class="text-slate-400 hover:text-red-600 p-1 rounded transition-colors disabled:cursor-wait"
                                            title="Eliminar pregunta">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            </template>

                            <template #footer>
                                <tr v-if="filteredQuestions.length === 0">
                                    <td colspan="4" class="px-6 py-12 text-center text-slate-500">
                                        <div class="flex flex-col items-center justify-center gap-2">
                                            <svg class="text-slate-300" xmlns="http://www.w3.org/2000/svg" width="24"
                                                height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                            </svg>
                                            <p class="text-sm font-medium">
                                                {{ isSearching ? 'Ninguna pregunta coincide con la búsqueda.' : 'Esta pregunta principal aún no tiene preguntas.' }}
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

        <!-- Modal: Agregar pregunta -->
        <Teleport to="body">
            <Transition enter-active-class="transition duration-150 ease-out" enter-from-class="opacity-0"
                enter-to-class="opacity-100" leave-active-class="transition duration-100 ease-in"
                leave-from-class="opacity-100" leave-to-class="opacity-0">
                <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-[2px]" @click="closeModal" />

                    <div role="dialog" aria-modal="true" aria-labelledby="add-question-title"
                        class="relative w-full max-w-lg rounded-xl bg-white shadow-xl ring-1 ring-slate-200">
                        <form @submit.prevent="submitQuestion">
                            <div class="px-6 pt-6">
                                <h2 id="add-question-title" class="text-lg font-semibold text-slate-900">Agregar
                                    pregunta</h2>
                                <p class="mt-1 text-sm text-slate-500">Se agregará al final de
                                    «{{ formularioBase.title }}». Después puedes cambiar su orden arrastrándola.</p>
                            </div>

                            <div class="max-h-[60vh] space-y-4 overflow-y-auto px-6 py-5">
                                <div>
                                    <label for="text" class="block text-sm font-medium text-slate-700">Pregunta</label>
                                    <textarea id="text" ref="textInput" v-model="form.text" rows="3" maxlength="1000"
                                        placeholder="Ej. ¿Qué tipo de fruta te interesa?"
                                        class="mt-1.5 w-full resize-none rounded-lg border px-3 py-2 text-sm text-slate-900 placeholder-slate-400 shadow-sm focus:outline-none focus:ring-1 transition-colors"
                                        :class="form.errors.text
                                            ? 'border-red-300 focus:border-red-500 focus:ring-red-500'
                                            : 'border-slate-200 focus:border-blue-500 focus:ring-blue-500'" />
                                    <p v-if="form.errors.text" class="mt-1.5 text-xs text-red-600">{{ form.errors.text
                                        }}</p>
                                </div>

                                <div>
                                    <label for="question_type_id" class="block text-sm font-medium text-slate-700">Tipo
                                        de pregunta</label>
                                    <select id="question_type_id" v-model="form.question_type_id"
                                        class="mt-1.5 w-full rounded-lg border bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:outline-none focus:ring-1 transition-colors"
                                        :class="form.errors.question_type_id
                                            ? 'border-red-300 focus:border-red-500 focus:ring-red-500'
                                            : 'border-slate-200 focus:border-blue-500 focus:ring-blue-500'">
                                        <option value="" disabled>Selecciona un tipo</option>
                                        <option v-for="tipo in tipos" :key="tipo.id" :value="tipo.id">{{ tipo.name }}
                                        </option>
                                    </select>
                                    <p v-if="form.errors.question_type_id" class="mt-1.5 text-xs text-red-600">{{
                                        form.errors.question_type_id }}</p>
                                </div>

                                <!-- Opciones (Selección única / múltiple) -->
                                <div v-if="needsOptions">
                                    <div class="flex items-center justify-between">
                                        <label class="block text-sm font-medium text-slate-700">Opciones</label>
                                        <span class="text-xs text-slate-400">Mínimo 2 · Enter para agregar otra</span>
                                    </div>

                                    <ul class="mt-1.5 space-y-2">
                                        <li v-for="(option, index) in form.options" :key="index"
                                            class="flex items-center gap-2">
                                            <span
                                                class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-slate-100 text-[11px] font-semibold text-slate-500">
                                                {{ index + 1 }}
                                            </span>
                                            <input :ref="el => (optionInputs[index] = el as HTMLInputElement | null)"
                                                v-model="form.options[index]" type="text" maxlength="255"
                                                :placeholder="`Opción ${index + 1}`"
                                                @keydown.enter.prevent="onOptionEnter(index)"
                                                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-900 placeholder-slate-400 shadow-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors" />
                                            <button type="button" @click="removeOption(index)"
                                                class="shrink-0 rounded p-1 text-slate-400 hover:text-red-600 transition-colors"
                                                :title="form.options.length > 2 ? 'Quitar opción' : 'Limpiar opción'">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                    stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </li>
                                    </ul>

                                    <button type="button" @click="addOption"
                                        class="mt-2 inline-flex items-center gap-1.5 rounded-md px-2 py-1 text-xs font-semibold text-blue-600 hover:bg-blue-50 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                        </svg>
                                        Agregar opción
                                    </button>

                                    <p v-if="optionsError" class="mt-1.5 text-xs text-red-600">{{ optionsError }}</p>
                                </div>
                            </div>

                            <div
                                class="flex items-center justify-end gap-3 rounded-b-xl border-t border-slate-100 bg-slate-50/60 px-6 py-4">
                                <button type="button" @click="closeModal" :disabled="form.processing"
                                    class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100 transition-colors">
                                    Cancelar
                                </button>
                                <button type="submit" :disabled="!canSubmit"
                                    class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                    <svg v-if="form.processing" class="h-4 w-4 animate-spin" viewBox="0 0 24 24"
                                        fill="none">
                                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"
                                            class="opacity-25" />
                                        <path d="M22 12a10 10 0 0 0-10-10" stroke="currentColor" stroke-width="3"
                                            stroke-linecap="round" />
                                    </svg>
                                    {{ form.processing ? 'Guardando...' : 'Agregar pregunta' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </AppLayout>
</template>
