<script setup lang="ts">
import { router } from "@inertiajs/vue3";
import { onBeforeUnmount, onMounted, ref, watch } from "vue";

interface RaffleOption {
    id: number;
    name: string;
    prize: string | null;
}

const props = defineProps<{
    show: boolean;
    raffles: RaffleOption[];
    /** Registros de formularios a invitar */
    forms: { id: number; name: string }[];
}>();

const emit = defineEmits<{ (e: "close"): void; (e: "sent"): void }>();

const raffleId = ref<number | "">("");
const sending = ref(false);
const error = ref<string | null>(null);

watch(
    () => props.show,
    (open) => {
        if (!open) return;
        error.value = null;
        raffleId.value = props.raffles.length === 1 ? props.raffles[0].id : "";
    },
);

const close = () => {
    if (!sending.value) emit("close");
};

const send = () => {
    if (!raffleId.value || !props.forms.length) return;

    router.post(
        `/raffles/${raffleId.value}/invite`,
        { form_ids: props.forms.map((f) => f.id) },
        {
            preserveScroll: true,
            preserveState: true,
            onStart: () => (sending.value = true),
            onFinish: () => (sending.value = false),
            onSuccess: () => {
                emit("sent");
                emit("close");
            },
            onError: (errors) => (error.value = Object.values(errors)[0] ?? "No se pudo enviar la invitación."),
        },
    );
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

                <div role="dialog" aria-modal="true" aria-labelledby="form-raffle-title"
                    class="relative w-full max-w-md rounded-xl bg-white shadow-xl ring-1 ring-slate-200">
                    <div class="px-6 pt-6">
                        <h2 id="form-raffle-title" class="text-lg font-semibold text-slate-900">Invitar a rifa</h2>
                        <p class="mt-1 text-sm text-slate-500">
                            <template v-if="forms.length === 1">Se enviará la invitación por correo a {{ forms[0].name }}.</template>
                            <template v-else>Se enviará la invitación por correo a {{ forms.length }} personas.</template>
                        </p>
                    </div>

                    <div class="px-6 py-5">
                        <template v-if="raffles.length">
                            <label for="form-raffle-select" class="block text-sm font-medium text-slate-700">Rifa</label>
                            <select id="form-raffle-select" v-model="raffleId"
                                class="mt-1.5 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none">
                                <option value="" disabled>Selecciona una rifa</option>
                                <option v-for="raffle in raffles" :key="raffle.id" :value="raffle.id">
                                    {{ raffle.name }}<template v-if="raffle.prize"> · {{ raffle.prize }}</template>
                                </option>
                            </select>
                            <p class="mt-2 text-xs text-slate-400">
                                Los registros sin correo o ya invitados a esa rifa se omiten.
                            </p>
                        </template>
                        <p v-else class="rounded-lg bg-slate-50 px-4 py-3 text-sm text-slate-600">
                            No hay rifas abiertas. Crea una en el módulo de Rifas.
                        </p>

                        <p v-if="error" class="mt-3 text-sm text-red-600">{{ error }}</p>
                    </div>

                    <div class="flex items-center justify-end gap-3 rounded-b-xl border-t border-slate-100 bg-slate-50/60 px-6 py-4">
                        <button type="button" @click="close" :disabled="sending"
                            class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 transition-colors hover:bg-slate-100">
                            Cancelar
                        </button>
                        <button type="button" @click="send" :disabled="!raffleId || sending"
                            class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50">
                            {{ sending ? "Enviando..." : "Enviar invitación" }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
