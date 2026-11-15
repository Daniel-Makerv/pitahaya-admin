<script setup lang="ts">
import { useForm } from "@inertiajs/vue3";
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from "vue";

const props = defineProps<{
    show: boolean;
    raffleId: number;
    digits: number;
    confirmedCount: number;
}>();

const emit = defineEmits<{ (e: "close"): void }>();

const numberInput = ref<HTMLInputElement | null>(null);

const form = useForm({
    lottery_number: "",
    lottery_draw: "",
});

watch(
    () => props.show,
    async (open) => {
        if (!open) return;
        form.reset();
        form.clearErrors();
        await nextTick();
        numberInput.value?.focus();
    },
);

const onlyDigits = computed(() => form.lottery_number.replace(/\D/g, ""));
const target = computed(() =>
    onlyDigits.value.length >= props.digits ? onlyDigits.value.slice(-props.digits) : null,
);

const close = () => {
    if (!form.processing) emit("close");
};

const submit = () => {
    if (!target.value) return;
    if (!confirm(`Se sorteará con el número ${target.value}. El resultado no se puede cambiar. ¿Continuar?`)) return;

    form.post(`/raffles/${props.raffleId}/draw`, {
        preserveScroll: true,
        onSuccess: () => emit("close"),
    });
};

const onKeydown = (e: KeyboardEvent) => {
    if (e.key === "Escape" && props.show) close();
};
onMounted(() => window.addEventListener("keydown", onKeydown));
onBeforeUnmount(() => window.removeEventListener("keydown", onKeydown));
</script>

<template>
    <Teleport to="body">
        <Transition enter-active-class="transition duration-150 ease-out" enter-from-class="opacity-0"
            enter-to-class="opacity-100" leave-active-class="transition duration-100 ease-in"
            leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-[2px]" @click="close" />

                <div role="dialog" aria-modal="true" aria-labelledby="draw-title"
                    class="relative w-full max-w-md rounded-xl bg-white shadow-xl ring-1 ring-slate-200">
                    <form @submit.prevent="submit">
                        <div class="px-6 pt-6">
                            <h2 id="draw-title" class="text-lg font-semibold text-slate-900">Sortear con la Lotería Nacional</h2>
                            <p class="mt-1 text-sm text-slate-500">
                                Captura el número del premio mayor. Gana el número más cercano a sus últimas
                                {{ digits }} cifras; 2º y 3er lugar, los siguientes más cercanos. En empate gana el menor.
                            </p>
                        </div>

                        <div class="space-y-4 px-6 py-5">
                            <div>
                                <label for="lottery-number" class="block text-sm font-medium text-slate-700">Número del premio mayor</label>
                                <input id="lottery-number" ref="numberInput" v-model="form.lottery_number" type="text"
                                    inputmode="numeric" maxlength="20" placeholder="Ej. 45821"
                                    class="mt-1.5 w-full rounded-lg border px-3 py-2 text-lg font-semibold tracking-widest text-slate-900 placeholder-slate-300 shadow-sm focus:ring-1 focus:outline-none"
                                    :class="form.errors.lottery_number ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : 'border-slate-200 focus:border-blue-500 focus:ring-blue-500'" />
                                <p v-if="form.errors.lottery_number" class="mt-1.5 text-xs text-red-600">{{ form.errors.lottery_number }}</p>
                            </div>

                            <div>
                                <label for="lottery-draw" class="block text-sm font-medium text-slate-700">
                                    Número de sorteo <span class="font-normal text-slate-400">(opcional)</span>
                                </label>
                                <input id="lottery-draw" v-model="form.lottery_draw" type="text" maxlength="50"
                                    placeholder="Ej. Sorteo Mayor 3912"
                                    class="mt-1.5 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-900 placeholder-slate-400 shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none" />
                            </div>

                            <div class="rounded-lg bg-slate-50 px-4 py-3 text-sm">
                                <template v-if="target">
                                    Número que se buscará:
                                    <span class="ml-1 rounded-md bg-slate-900 px-2 py-0.5 font-mono font-bold tracking-widest text-white">{{ target }}</span>
                                </template>
                                <span v-else class="text-slate-500">Escribe al menos {{ digits }} cifras.</span>
                                <span class="mt-1 block text-xs text-slate-400">
                                    Entre {{ confirmedCount }} participante{{ confirmedCount === 1 ? "" : "s" }} confirmado{{ confirmedCount === 1 ? "" : "s" }}.
                                </span>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 rounded-b-xl border-t border-slate-100 bg-slate-50/60 px-6 py-4">
                            <button type="button" @click="close" :disabled="form.processing"
                                class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 transition-colors hover:bg-slate-100">
                                Cancelar
                            </button>
                            <button type="submit" :disabled="!target || form.processing || !confirmedCount"
                                class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50">
                                {{ form.processing ? "Sorteando..." : "Sortear" }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
