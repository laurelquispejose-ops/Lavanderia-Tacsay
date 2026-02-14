<template>
    <AdminLayout>
        <div class="container mt-3">
            <h1 class="mb-3">Órdenes de Lavandería</h1>

            <!-- Buscador + rango de fechas -->
            <div class="row g-2 align-items-end mb-3">
                <div class="col-12 col-md-4">
                    <label class="form-label">Buscar (N° orden, nombre o DNI)</label>
                    <input class="form-control" v-model="busqueda" placeholder="Ej. 10, Juan, 12345678" />
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label">Desde</label>
                    <input type="date" class="form-control" v-model="fechaInicio" />
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label">Hasta</label>
                    <input type="date" class="form-control" v-model="fechaFin" />
                </div>
                <div class="col-12 col-md-2 d-grid">
                    <button class="btn btn-primary" @click="cargarOrdenes">Buscar</button>
                </div>
            </div>

            <!-- Resumen -->
            <div class="row g-2 mb-3">
                <div class="col-6 col-md-2"><div class="p-3 bg-light border rounded">Total: <strong>{{ ordenesFiltradas.length }}</strong></div></div>
                <div class="col-6 col-md-2"><div class="p-3 bg-light border rounded">Pendiente: <strong>{{ conteoPendiente }}</strong></div></div>
                <div class="col-6 col-md-2"><div class="p-3 bg-light border rounded">En Lavado: <strong>{{ conteoLavado }}</strong></div></div>
                <div class="col-6 col-md-2"><div class="p-3 bg-light border rounded">En Planchado: <strong>{{ conteoPlanchado }}</strong></div></div>
                <div class="col-6 col-md-2"><div class="p-3 bg-light border rounded">Finalizado: <strong>{{ conteoListas }}</strong></div></div>
            </div>

            <!-- Componente de exportación -->
            <ExportarOrdenes />

            <OrdenesSection
                titulo="Órdenes Pendientes"
                :ordenes="ordenesPendientes"
                @refrescar="cargarOrdenes"
            />

            <OrdenesSection
                titulo="Órdenes en Proceso - Lavado"
                :ordenes="ordenesLavado"
                @refrescar="cargarOrdenes"
            />

            <OrdenesSection
                titulo="Órdenes en Proceso - Planchado"
                :ordenes="ordenesPlanchado"
                @refrescar="cargarOrdenes"
            />

            <OrdenesSection
                titulo="Órdenes Listas"
                :ordenes="ordenesListas"
                @refrescar="cargarOrdenes"
            />
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import axios from "axios";

import AdminLayout from "../components/AdminLayout.vue";
import OrdenesSection from "../components/OrdenesSection.vue";
import ExportarOrdenes from "../components/ExportarOrdenes.vue";

const todasOrdenes = ref([]);
const busqueda = ref("");
const fechaInicio = ref("");
const fechaFin = ref("");

// Filtrado por búsqueda (N° orden, nombre o DNI - via pago.NumDocumento)
const ordenesFiltradas = computed(() => {
    const q = busqueda.value.trim().toLowerCase();
    if (!q) return todasOrdenes.value;
    return todasOrdenes.value.filter(o => {
        const porNumero = String(o.IdOrden).includes(q);
        const porNombre = (o.cliente?.nombre || '').toLowerCase().includes(q);
        const porDni = (o.pago?.NumDocumento || '').toLowerCase().includes(q);
        return porNumero || porNombre || porDni;
    });
});

const ordenesPendientes = computed(() => ordenesFiltradas.value.filter(o => o.Estado === 'pendiente'));
const ordenesLavado = computed(() => ordenesFiltradas.value.filter(o => o.Estado === 'en proceso lavado'));
const ordenesPlanchado = computed(() => ordenesFiltradas.value.filter(o => o.Estado === 'en proceso planchado'));
const ordenesListas = computed(() => ordenesFiltradas.value.filter(o => o.Estado === 'finalizado'));

const conteoPendiente = computed(() => ordenesPendientes.value.length);
const conteoLavado = computed(() => ordenesLavado.value.length);
const conteoPlanchado = computed(() => ordenesPlanchado.value.length);
const conteoListas = computed(() => ordenesListas.value.length);

const cargarOrdenes = async () => {
    try {
        const params = {};
        if (fechaInicio.value) params.fecha_inicio = fechaInicio.value;
        if (fechaFin.value) params.fecha_fin = fechaFin.value;
        const response = await axios.get("/api/ordenes", { params });
        todasOrdenes.value = response.data;
    } catch (error) {
        console.error("Error al cargar las órdenes:", error);
    }
};

onMounted(() => {
    cargarOrdenes();
});
</script>
