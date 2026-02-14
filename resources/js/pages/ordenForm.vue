<template>
    <AdminLayout>
        <div class="container mt-4">
            <!-- Fechas de entrada y entrega -->
            <div class="row g-3 mb-3">
                <div class="col-12 col-sm-6">
                    <label class="form-label" for="fechaEntrada">Fecha de Entrada</label>
                    <input type="date" id="fechaEntrada" class="form-control" v-model="fechaEntrada" />
                </div>
                <div class="col-12 col-sm-6">
                    <label class="form-label" for="fechaEntrega">Fecha de Entrega</label>
                    <input type="date" id="fechaEntrega" class="form-control" v-model="fechaEntrega" />
                </div>
            </div>
            <div v-if="!pasoConfirmacion">
                <div class="row">
                    <div class="col-md-6">
                        <PrendaForm @prenda-agregada="agregarPrenda" />
                    </div>
                    <div class="col-md-6">
                        <ListaPrendas
                            :prendas="prendas"
                            @eliminar="eliminarPrenda"
                            @editar="editarPrenda"
                            @confirmar-prendas="pasarAConfirmacion"
                        />
                    </div>
                </div>
            </div>

            <orden-resumen
                v-else
                :prendas="prendas"
                :precioTotalCalculado="calcularPrecioTotal()"
                :fechaEntrada="fechaEntrada"
                :fechaEntrega="fechaEntrega"
                @orden-confirmada="guardarOrden"
            />
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref } from "vue";
import AdminLayout from "../components/AdminLayout.vue";
import PrendaForm from "../components/prendaForm.vue";
import ListaPrendas from "../components/listaPrendas.vue";
import OrdenResumen from "../components/ordenResumen.vue";

const prendas = ref([]);
const pasoConfirmacion = ref(false);
const fechaEntrada = ref(new Date().toISOString().slice(0, 10));
const fechaEntrega = ref("");

const agregarPrenda = (prenda) => {
    // El precio ya viene calculado desde el componente prendaForm
    // No necesitamos recalcularlo aquí
    prendas.value.push(prenda);
};

const editarPrenda = (index, prendaEditada) => {
    prendas.value[index] = prendaEditada;
};

const eliminarPrenda = (index) => {
    prendas.value.splice(index, 1);
};

const pasarAConfirmacion = () => {
    if (prendas.value.length === 0) {
        alert("Agrega al menos una prenda antes de continuar.");
        return;
    }
    pasoConfirmacion.value = true;
};

const calcularPrecioTotal = () => {
    // Asegurarnos de que cada prenda tenga un precio válido
    const total = prendas.value.reduce((total, p) => {
        // Si el precio es undefined, null o NaN, usar 0
        const precio = p.precio || 0;
        console.log(`Prenda: ${p.tipo}, Precio: ${precio}`);
        return total + precio;
    }, 0);
    
    console.log(`Precio total calculado: ${total}`);
    return total;
};

const guardarOrden = (orden) => {
    console.log("Orden lista para guardar:", orden);
    alert("¡Orden registrada con éxito!");
};
</script>
