<script setup lang="ts">
import { useForm } from "@inertiajs/vue3";
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from "vue";

export interface RaffleFormData {
    id?: number;
    name: string;
    description: string | null;
    prize: string | null;
    digits: number;
    draw_date: string | null;
}

const props = defineProps<{
    show: boolean;
    raffle?: RaffleFormData | null;
    /** Si ya hay números asignados, las cifras no se pueden cambiar */
    lockDigits?: boolean;
}>();

const emit = defineEmits<{ (e: "close"): void }>();

const isEdit = computed(() => !!props.raffle?.id);
const nameInput = ref<HTMLInputElement | null>(null);

const form = useForm({
    name: "",
    description: "",
    prize: "",
    digits: 5,
    draw_date: "",
});

watch(
    () => props.show,
    async (open) => {
        if (!open) return;
        form.clearErrors();
        form.name = props.raffle?.name ?? "";
        form.description = props.raffle?.description ?? "";
        form.prize = props.raffle?.prize ?? "";
        form.digits = props.raffle?.digits ?? 5;
        form.draw_date = props.raffle?.draw_date ?? "";
        await nextTick();
        nameInput.value?.focus();
    },
);

const capacityText = computed(() => {
    const max = 10 ** form.digits;
    return `${"0".repeat(form.digits)} a ${String(max - 1)} · hasta ${max.toLocaleString("es-MX")} participantes`;
});

const close = () => {
    if (!form.processing) emit("close");
};

const submit = () => {
    const options = { preserveScroll: true, onSuccess: () => emit("close") };
    if (isEdit.value) form.put(`/raffles/${props.raffle!.id}`, options);
    else form.post("/raffles", options);
};

const onKeydown = (e: KeyboardEvent) => {
    if (e.key === "Escape" && props.show) close();
};
onMounted(() => window.addEventListener("keydown", onKeydown));
onBeforeUnmount(() => window.removeEventListener("keydown", onKeydown));

const inputClass = (error?: string) =>
    [
        "mt-1.5 w-full rounded-lg border px-3 py-2 text-sm text-slate-900 placeholder-slate-400 shadow-sm transition-colors focus:outline-none focus:ring-1",
        error ? "border-red-300 focus:border-red-500 focus:ring-red-500" : "border-slate-200 focus:border-blue-500 focus:ring-blue-500",
    ].join(" ");
</script>

<template>
    <Teleport to="body">
        <Transition enter-active-class="transition duration-150 ease-out" enter-from-class="opacity-0"
            enter-to-class="opacity-100" leave-active-class="transition duration-100 ease-in"
            leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-[2px]" @click="close" />

                <div role="dialog" aria-modal="true" aria-labelledby="raffle-form-title"
                    class="relative w-full max-w-lg rounded-xl bg-white shadow-xl ring-1 ring-slate-200">
                    <form @submit.prevent="submit">
                        <div class="px-6 pt-6">
                            <h2 id="raffle-form-title" class="text-lg font-semibold text-slate-900">
                                {{ isEdit ? "Editar rifa" : "Nueva rifa" }}
                            </h2>
                            <p class="mt-1 text-sm text-slate-500">
                                El ganador se define con las últimas cifras del premio mayor de la Lotería Nacional.
                            </p>
                        </div>

                        <div class="max-h-[65vh] space-y-4 overflow-y-auto px-6 py-5">
                            <div>
                                <label for="raffle-name" class="block text-sm font-medium text-slate-700">Nombre</label>
                                <input id="raffle-name" ref="nameInput" v-model="form.name" type="text" maxlength="255"
                                    placeholder="Ej. Rifa Expo Pitahaya 2026" :class="inputClass(form.errors.name)" />
                                <p v-if="form.errors.name" class="mt-1.5 text-xs text-red-600">{{ form.errors.name }}</p>
                            </div>

                            <div>
                                <label for="raffle-prize" class="block text-sm font-medium text-slate-700">
                                    Premios <span class="font-normal text-slate-400">(uno por línea: 1er lugar, 2º, 3º...)</span>
                                </label>
                                <textarea id="raffle-prize" v-model="form.prize" rows="3" maxlength="1000"
                                    placeholder="Botella de mezcal de pitahaya&#10;Dos cajas de pitahaya&#10;Sombrero PITAMEX"
                                    :class="[inputClass(form.errors.prize), 'resize-none']" />
                            </div>

                            <div>
                                <label for="raffle-description" class="block text-sm font-medium text-slate-700">
                                    Descripción <span class="font-normal text-slate-400">(opcional)</span>
                                </label>
                                <textarea id="raffle-description" v-model="form.description" rows="3" maxlength="2000"
                                    placeholder="Se muestra en la página de confirmación"
                                    :class="[inputClass(form.errors.description), 'resize-none']" />
                            </div>

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label for="raffle-date" class="block text-sm font-medium text-slate-700">
                                        Fecha del sorteo <span class="font-normal text-slate-400">(opcional)</span>
                                    </label>
                                    <input id="raffle-date" v-model="form.draw_date" type="date"
                                        :class="inputClass(form.errors.draw_date)" />
                                </div>
                                <div>
                                    <label for="raffle-digits" class="block text-sm font-medium text-slate-700">Cifras del número</label>
                                    <select id="raffle-digits" v-model.number="form.digits" :disabled="lockDigits"
                                        :class="[inputClass(form.errors.digits), 'bg-white disabled:bg-slate-50 disabled:text-slate-400']">
                                        <option :value="5">5 cifras (premio mayor completo)</option>
                                        <option :value="4">4 cifras</option>
                                        <option :value="3">3 cifras</option>
                                    </select>
                                </div>
                            </div>
                            <p class="-mt-2 text-xs text-slate-400">
                                {{ lockDigits ? "No se puede cambiar: ya hay números asignados." : capacityText }}
                            </p>
                        </div>

                        <div class="flex items-center justify-end gap-3 rounded-b-xl border-t border-slate-100 bg-slate-50/60 px-6 py-4">
                            <button type="button" @click="close" :disabled="form.processing"
                                class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 transition-colors hover:bg-slate-100">
                                Cancelar
                            </button>
                            <button type="submit" :disabled="form.processing || !form.name.trim()"
                                class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50">
                                {{ form.processing ? "Guardando..." : isEdit ? "Guardar cambios" : "Crear rifa" }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
