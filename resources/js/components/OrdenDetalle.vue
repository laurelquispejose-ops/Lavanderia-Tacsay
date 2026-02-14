<template>
    <div class="accordion" :id="'accordionOrden' + idOrden + '_' + index">
        <div class="accordion-item">
            <h2
                class="accordion-header"
                :id="'heading' + idOrden + '_' + index"
            >
                <button
                    class="accordion-button collapsed"
                    type="button"
                    data-bs-toggle="collapse"
                    :data-bs-target="'#collapse' + idOrden + '_' + index"
                    aria-expanded="false"
                    :aria-controls="'collapse' + idOrden + '_' + index"
                >
                    Prenda {{ index + 1 }} - {{ detalle.prenda.TipoPrenda }}
                </button>
            </h2>
            <div
                :id="'collapse' + idOrden + '_' + index"
                class="accordion-collapse collapse"
                :aria-labelledby="'heading' + idOrden + '_' + index"
            >
                <div class="accordion-body">
                    <ul class="list-unstyled">
                        <li v-if="detalle.Cantidad > 0">
                            <strong>Cantidad:</strong>
                            {{ detalle.Cantidad }}
                        </li>
                        <li v-if="detalle.Peso > 0">
                            <strong>Peso:</strong>
                            {{ detalle.Peso }}
                        </li>
                        <!-- Color eliminado por requerimiento -->
                        <li>
                            <strong>Lavado:</strong>
                            {{ detalle.servicio_lavado.Nombre }}
                        </li>
                        <li>
                            <strong>Planchado:</strong>
                            {{ detalle.servicio_planchado.Nombre }}
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    detalle: Object,
    index: Number,
    idOrden: Number,
});
</script>
