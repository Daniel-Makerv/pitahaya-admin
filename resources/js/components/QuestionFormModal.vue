<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import {
    computed,
    nextTick,
    onBeforeUnmount,
    onMounted,
    ref,
    watch,
} from 'vue';

interface Tipo {
    id: number;
    name: string;
    str: string | null;
}

const props = withDefaults(
    defineProps<{
        show: boolean;
        tipos: Tipo[];
        url: string;
        title: string;
        description?: string;
        submitLabel?: string;
    }>(),
    {
        description: '',
        submitLabel: 'Agregar pregunta',
    },
);

const emit = defineEmits<{
    (e: 'close'): void;
}>();

const textInput = ref<HTMLTextAreaElement | null>(null);

const form = useForm({
    text: '',
    question_type_id: '' as number | '',
    options: ['', ''] as string[],
    required: true,
});

// Selección única / múltiple (str = select) necesitan opciones
const selectedTipo = computed(() =>
    props.tipos.find((t) => t.id === form.question_type_id),
);
const needsOptions = computed(() => selectedTipo.value?.str === 'select');
const cleanOptions = computed(() =>
    form.options.map((o) => o.trim()).filter(Boolean),
);

const canSubmit = computed(
    () =>
        !form.processing &&
        form.text.trim() !== '' &&
        form.question_type_id !== '' &&
        (!needsOptions.value || cleanOptions.value.length >= 2),
);

// ---------- Opciones ----------
const optionInputs = ref<(HTMLInputElement | null)[]>([]);

const addOption = async () => {
    form.options.push('');
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
    else form.options[index] = '';
};

const optionsError = computed(() => {
    const errors = form.errors as Record<string, string>;
    const key = Object.keys(errors).find(
        (k) => k === 'options' || k.startsWith('options.'),
    );
    return key ? errors[key] : null;
});

// ---------- Abrir / cerrar ----------
watch(
    () => props.show,
    async (open) => {
        if (!open) return;
        form.reset();
        form.clearErrors();
        await nextTick();
        textInput.value?.focus();
    },
);

const close = () => {
    if (form.processing) return;
    emit('close');
};

const submit = () => {
    if (!canSubmit.value) return;

    form.transform((data) => ({
        ...data,
        options: needsOptions.value ? cleanOptions.value : [],
    })).post(props.url, {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            emit('close');
        },
    });
};

const onKeydown = (e: KeyboardEvent) => {
    if (e.key === 'Escape' && props.show) close();
};

onMounted(() => window.addEventListener('keydown', onKeydown));
onBeforeUnmount(() => window.removeEventListener('keydown', onKeydown));
</script>

<template>
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
                v-if="show"
                class="fixed inset-0 z-50 flex items-center justify-center p-4"
            >
                <div
                    class="absolute inset-0 bg-slate-900/40 backdrop-blur-[2px]"
                    @click="close"
                />

                <div
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="question-form-title"
                    class="relative w-full max-w-lg rounded-xl bg-white shadow-xl ring-1 ring-slate-200"
                >
                    <form @submit.prevent="submit">
                        <div class="px-6 pt-6">
                            <h2
                                id="question-form-title"
                                class="text-lg font-semibold text-slate-900"
                            >
                                {{ title }}
                            </h2>
                            <p
                                v-if="description"
                                class="mt-1 text-sm text-slate-500"
                            >
                                {{ description }}
                            </p>
                        </div>

                        <div
                            class="max-h-[60vh] space-y-4 overflow-y-auto px-6 py-5"
                        >
                            <div>
                                <label
                                    for="question-text"
                                    class="block text-sm font-medium text-slate-700"
                                    >Pregunta</label
                                >
                                <textarea
                                    id="question-text"
                                    ref="textInput"
                                    v-model="form.text"
                                    rows="3"
                                    maxlength="1000"
                                    placeholder="Ej. ¿Cuál es tu nombre?"
                                    class="mt-1.5 w-full resize-none rounded-lg border px-3 py-2 text-sm text-slate-900 placeholder-slate-400 shadow-sm transition-colors focus:ring-1 focus:outline-none"
                                    :class="
                                        form.errors.text
                                            ? 'border-red-300 focus:border-red-500 focus:ring-red-500'
                                            : 'border-slate-200 focus:border-blue-500 focus:ring-blue-500'
                                    "
                                />
                                <p
                                    v-if="form.errors.text"
                                    class="mt-1.5 text-xs text-red-600"
                                >
                                    {{ form.errors.text }}
                                </p>
                            </div>

                            <div>
                                <label
                                    for="question-type"
                                    class="block text-sm font-medium text-slate-700"
                                    >Tipo de pregunta</label
                                >
                                <select
                                    id="question-type"
                                    v-model="form.question_type_id"
                                    class="mt-1.5 w-full rounded-lg border bg-white px-3 py-2 text-sm text-slate-900 shadow-sm transition-colors focus:ring-1 focus:outline-none"
                                    :class="
                                        form.errors.question_type_id
                                            ? 'border-red-300 focus:border-red-500 focus:ring-red-500'
                                            : 'border-slate-200 focus:border-blue-500 focus:ring-blue-500'
                                    "
                                >
                                    <option value="" disabled>
                                        Selecciona un tipo
                                    </option>
                                    <option
                                        v-for="tipo in tipos"
                                        :key="tipo.id"
                                        :value="tipo.id"
                                    >
                                        {{ tipo.name }}
                                    </option>
                                </select>
                                <p
                                    v-if="form.errors.question_type_id"
                                    class="mt-1.5 text-xs text-red-600"
                                >
                                    {{ form.errors.question_type_id }}
                                </p>
                            </div>

                            <!-- Obligatoria / Opcional -->
                            <div>
                                <span
                                    class="block text-sm font-medium text-slate-700"
                                    >¿Es obligatoria?</span
                                >
                                <div
                                    role="radiogroup"
                                    aria-label="¿Es obligatoria?"
                                    class="mt-1.5 grid grid-cols-2 gap-1 rounded-lg bg-slate-100 p-1"
                                >
                                    <button
                                        type="button"
                                        role="radio"
                                        :aria-checked="form.required"
                                        @click="form.required = true"
                                        class="rounded-md px-3 py-1.5 text-sm font-semibold transition-colors"
                                        :class="
                                            form.required
                                                ? 'bg-white text-slate-900 shadow-sm'
                                                : 'text-slate-500 hover:text-slate-700'
                                        "
                                    >
                                        Obligatoria
                                    </button>
                                    <button
                                        type="button"
                                        role="radio"
                                        :aria-checked="!form.required"
                                        @click="form.required = false"
                                        class="rounded-md px-3 py-1.5 text-sm font-semibold transition-colors"
                                        :class="
                                            !form.required
                                                ? 'bg-white text-slate-900 shadow-sm'
                                                : 'text-slate-500 hover:text-slate-700'
                                        "
                                    >
                                        Opcional
                                    </button>
                                </div>
                                <p class="mt-1.5 text-xs text-slate-400">
                                    {{
                                        form.required
                                            ? 'No se puede avanzar sin contestarla.'
                                            : 'Se podrá saltar con «Omitir».'
                                    }}
                                </p>
                            </div>

                            <!-- Opciones (Selección única / múltiple) -->
                            <div v-if="needsOptions">
                                <div class="flex items-center justify-between">
                                    <label
                                        class="block text-sm font-medium text-slate-700"
                                        >Opciones</label
                                    >
                                    <span class="text-xs text-slate-400"
                                        >Mínimo 2 · Enter para agregar
                                        otra</span
                                    >
                                </div>

                                <ul class="mt-1.5 space-y-2">
                                    <li
                                        v-for="(option, index) in form.options"
                                        :key="index"
                                        class="flex items-center gap-2"
                                    >
                                        <span
                                            class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-slate-100 text-[11px] font-semibold text-slate-500"
                                        >
                                            {{ index + 1 }}
                                        </span>
                                        <input
                                            :ref="
                                                (el) =>
                                                    (optionInputs[index] =
                                                        el as HTMLInputElement | null)
                                            "
                                            v-model="form.options[index]"
                                            type="text"
                                            maxlength="255"
                                            :placeholder="`Opción ${index + 1}`"
                                            @keydown.enter.prevent="
                                                onOptionEnter(index)
                                            "
                                            class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-900 placeholder-slate-400 shadow-sm transition-colors focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none"
                                        />
                                        <button
                                            type="button"
                                            @click="removeOption(index)"
                                            class="shrink-0 rounded p-1 text-slate-400 transition-colors hover:text-red-600"
                                            :title="
                                                form.options.length > 2
                                                    ? 'Quitar opción'
                                                    : 'Limpiar opción'
                                            "
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
                                                    d="M6 18L18 6M6 6l12 12"
                                                />
                                            </svg>
                                        </button>
                                    </li>
                                </ul>

                                <button
                                    type="button"
                                    @click="addOption"
                                    class="mt-2 inline-flex items-center gap-1.5 rounded-md px-2 py-1 text-xs font-semibold text-blue-600 transition-colors hover:bg-blue-50"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="12"
                                        height="12"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        stroke-width="3"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M12 4v16m8-8H4"
                                        />
                                    </svg>
                                    Agregar opción
                                </button>

                                <p
                                    v-if="optionsError"
                                    class="mt-1.5 text-xs text-red-600"
                                >
                                    {{ optionsError }}
                                </p>
                            </div>
                        </div>

                        <div
                            class="flex items-center justify-end gap-3 rounded-b-xl border-t border-slate-100 bg-slate-50/60 px-6 py-4"
                        >
                            <button
                                type="button"
                                @click="close"
                                :disabled="form.processing"
                                class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 transition-colors hover:bg-slate-100"
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                :disabled="!canSubmit"
                                class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <svg
                                    v-if="form.processing"
                                    class="h-4 w-4 animate-spin"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                >
                                    <circle
                                        cx="12"
                                        cy="12"
                                        r="10"
                                        stroke="currentColor"
                                        stroke-width="3"
                                        class="opacity-25"
                                    />
                                    <path
                                        d="M22 12a10 10 0 0 0-10-10"
                                        stroke="currentColor"
                                        stroke-width="3"
                                        stroke-linecap="round"
                                    />
                                </svg>
                                {{
                                    form.processing
                                        ? 'Guardando...'
                                        : submitLabel
                                }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
