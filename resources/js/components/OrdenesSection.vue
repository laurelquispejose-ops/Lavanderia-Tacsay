<template>
    <div class="mb-5">
        <h3 class="mb-3">{{ titulo }}</h3>
        <div v-if="ordenes.length > 0" class="row">
            <div
                v-for="orden in ordenes"
                :key="orden.IdOrden"
                class="col-md-4 mb-3"
            >
                <OrdenCard
                    :orden="orden"
                    @ordenEliminada="emit('refrescar')"
                    @estadoActualizado="emit('refrescar')"
                />
            </div>
        </div>
        <div v-else>
            <p class="text-muted">No hay órdenes en esta sección.</p>
        </div>
    </div>
</template>

<script setup>
import OrdenCard from "./OrdenesCard.vue";
const emit = defineEmits(["refrescar"]);
defineProps({
    titulo: String,
    ordenes: Array,
});
</script>
