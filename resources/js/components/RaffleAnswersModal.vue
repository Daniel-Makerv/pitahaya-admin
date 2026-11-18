<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted } from 'vue';

export interface AnswerSection {
    title: string;
    total: number;
    answers: { question: string; answer: string }[];
}

export interface AnswersParticipant {
    id: number;
    name: string;
    email: string | null;
    status: 'invited' | 'confirmed';
    number: string | null;
    step: number;
    score: number | null;
    priority: 'A' | 'B' | 'C' | null;
    confirmed_at: string | null;
    answers: AnswerSection[];
}

const props = defineProps<{
    participant: AnswersParticipant | null;
}>();

const emit = defineEmits<{ (e: 'close'): void }>();

const answered = computed(
    () =>
        props.participant?.answers.reduce((n, s) => n + s.answers.length, 0) ??
        0,
);

const priorityClass = (priority: string | null) =>
    ({
        A: 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
        B: 'bg-amber-50 text-amber-700 ring-amber-600/20',
        C: 'bg-slate-100 text-slate-600 ring-slate-500/20',
    })[priority ?? ''] ?? '';

const onKeydown = (e: KeyboardEvent) => {
    if (e.key === 'Escape' && props.participant) emit('close');
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
                v-if="participant"
                class="fixed inset-0 z-50 flex items-center justify-center p-4"
            >
                <div
                    class="absolute inset-0 bg-slate-900/40 backdrop-blur-[2px]"
                    @click="emit('close')"
                />

                <div
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="answers-title"
                    class="relative flex max-h-[90vh] w-full max-w-2xl flex-col rounded-xl bg-white shadow-xl ring-1 ring-slate-200"
                >
                    <!-- Encabezado -->
                    <div
                        class="flex items-start gap-4 border-b border-slate-100 px-6 pt-6 pb-5"
                    >
                        <div
                            v-if="participant.number"
                            class="flex h-14 shrink-0 items-center justify-center rounded-lg bg-slate-900 px-3 font-mono text-lg font-bold tracking-widest text-white"
                        >
                            {{ participant.number }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <h2
                                id="answers-title"
                                class="truncate text-lg font-semibold text-slate-900"
                            >
                                {{ participant.name }}
                            </h2>
                            <p class="truncate text-sm text-slate-500">
                                {{ participant.email || 'Sin correo' }}
                            </p>
                            <div
                                class="mt-2 flex flex-wrap items-center gap-2 text-xs"
                            >
                                <span
                                    v-if="participant.priority"
                                    class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 font-bold ring-1 ring-inset"
                                    :class="priorityClass(participant.priority)"
                                >
                                    Prioridad {{ participant.priority
                                    }}<span class="font-medium opacity-75"
                                        >· {{ participant.score }}%</span
                                    >
                                </span>
                                <span
                                    v-if="participant.status === 'confirmed'"
                                    class="text-slate-500"
                                    >Confirmó
                                    {{ participant.confirmed_at }}</span
                                >
                                <span
                                    v-else
                                    class="rounded-full bg-amber-50 px-2.5 py-1 font-semibold text-amber-700 ring-1 ring-amber-600/20 ring-inset"
                                >
                                    Registro incompleto · paso
                                    {{ participant.step }}/6
                                </span>
                                <span class="text-slate-400"
                                    >{{ answered }} respuestas</span
                                >
                            </div>
                        </div>
                        <button
                            type="button"
                            @click="emit('close')"
                            aria-label="Cerrar"
                            class="shrink-0 rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600"
                        >
                            <svg
                                width="18"
                                height="18"
                                viewBox="0 0 24 24"
                                fill="none"
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
                    </div>

                    <!-- Respuestas por paso -->
                    <div class="min-h-0 flex-1 overflow-y-auto px-6 py-5">
                        <p
                            v-if="!answered"
                            class="py-10 text-center text-sm text-slate-500"
                        >
                            Todavía no ha contestado el registro.
                        </p>

                        <div v-else class="space-y-6">
                            <section
                                v-for="(section, i) in participant.answers"
                                :key="section.title"
                            >
                                <div
                                    class="mb-2 flex items-baseline justify-between gap-3"
                                >
                                    <h3
                                        class="text-xs font-bold tracking-wider text-blue-600 uppercase"
                                    >
                                        <template
                                            v-if="
                                                section.title !==
                                                'Otras respuestas'
                                            "
                                            >{{ i + 1 }} · </template
                                        >{{ section.title }}
                                    </h3>
                                    <span class="text-[11px] text-slate-400"
                                        >{{ section.answers.length }} de
                                        {{ section.total }}</span
                                    >
                                </div>

                                <p
                                    v-if="!section.answers.length"
                                    class="rounded-lg bg-slate-50 px-3 py-2.5 text-xs text-slate-400 italic"
                                >
                                    Sin respuestas en este paso.
                                </p>
                                <dl
                                    v-else
                                    class="divide-y divide-slate-100 rounded-lg border border-slate-200"
                                >
                                    <div
                                        v-for="item in section.answers"
                                        :key="item.question"
                                        class="grid grid-cols-1 gap-0.5 px-3 py-2.5 sm:grid-cols-[minmax(0,1fr)_minmax(0,1fr)] sm:gap-4"
                                    >
                                        <dt class="text-xs text-slate-500">
                                            {{ item.question }}
                                        </dt>
                                        <dd
                                            class="text-sm font-medium break-words whitespace-pre-line text-slate-900"
                                        >
                                            {{ item.answer }}
                                        </dd>
                                    </div>
                                </dl>
                            </section>
                        </div>
                    </div>

                    <div
                        class="flex justify-end rounded-b-xl border-t border-slate-100 bg-slate-50/60 px-6 py-4"
                    >
                        <button
                            type="button"
                            @click="emit('close')"
                            class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-slate-700"
                        >
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
