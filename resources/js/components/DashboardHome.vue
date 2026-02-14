<template>
    <div>
        <h2 class="mb-4">Bienvenido, Administrador</h2>

        <div class="row">
            <div class="d-flex align-items-center mb-4 gap-2 col">
                <div>
                    <label class="form-label mb-1">Desde:</label>
                    <input
                        type="date"
                        class="form-control"
                        v-model="fechaInicio"
                        @change="cargarDatos"
                    />
                </div>
                <div>
                    <label class="form-label mb-1">Hasta:</label>
                    <input
                        type="date"
                        class="form-control"
                        v-model="fechaFin"
                        @change="cargarDatos"
                    />
                </div>
            </div>
            <div class="col">
                <div class="mb-3 d-flex gap-2">
                    <button
                        class="btn btn-outline-primary"
                        @click="establecerRango('hoy')"
                    >
                        Hoy
                    </button>
                    <button
                        class="btn btn-outline-primary"
                        @click="establecerRango('semana')"
                    >
                        Esta Semana
                    </button>
                    <button
                        class="btn btn-outline-primary"
                        @click="establecerRango('mes')"
                    >
                        Este Mes
                    </button>
                    <button
                        class="btn btn-outline-secondary"
                        @click="establecerRango('todas')"
                    >
                        Todas
                    </button>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-3" v-for="card in resumen" :key="card.titulo">
                <div class="card shadow-sm text-center">
                    <div class="card-body">
                        <h5 class="card-title">{{ card.titulo }}</h5>
                        <p class="display-6">{{ card.cantidad }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="card shadow-sm m-1 col">
                <div class="card-body">
                    <h5 class="card-title">Estado de Órdenes</h5>
                    <div class="chart-container">
                        <canvas ref="estadoChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm m-1 col">
                <div class="card-body">
                    <h5 class="card-title">Órdenes por día</h5>
                    <div class="chart-container">
                        <canvas ref="ventasChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted, ref, onBeforeUnmount } from "vue";
import Chart from "chart.js/auto";
import axios from "axios";

// Estado resumen para las cards
const resumen = ref([
    { titulo: "Órdenes Totales", cantidad: 0 },
    { titulo: "Pendientes", cantidad: 0 },
    { titulo: "En Proceso", cantidad: 0 },
    { titulo: "Finalizadas", cantidad: 0 },
]);

const fechaInicio = ref("");
const fechaFin = ref("");
const estadoChart = ref(null);
const ventasChart = ref(null);
let chartEstadoInstance = null;
let chartVentasInstance = null;
let intervalo = null;

const cargarDatos = async () => {
    try {
        const params = {};
        if (fechaInicio.value) params.fecha_inicio = fechaInicio.value;
        if (fechaFin.value) params.fecha_fin = fechaFin.value;

        const response = await axios.get("/api/ordenes", { params });
        const ordenes = response.data;

        resumen.value[0].cantidad = ordenes.length;
        resumen.value[1].cantidad = ordenes.filter(
            (o) => o.Estado === "pendiente"
        ).length;
        resumen.value[2].cantidad = ordenes.filter((o) =>
            o.Estado.includes("en proceso")
        ).length;
        resumen.value[3].cantidad = ordenes.filter(
            (o) => o.Estado === "finalizado"
        ).length;

        const guardado = localStorage.getItem("totalOrdenes");
        if (guardado !== null && ordenes.length > parseInt(guardado)) {
            const nuevas = ordenes.length - parseInt(guardado);
            mostrarNotificacion(`¡Han llegado ${nuevas} nuevas órdenes!`);
        }
        localStorage.setItem("totalOrdenes", ordenes.length);

        crearGraficoEstado(ordenes);
        crearGraficoVentas(ordenes);
    } catch (error) {
        console.error("Error al cargar las órdenes:", error);
    }
};

const establecerRango = (opcion) => {
    const hoy = new Date();
    let inicio, fin;

    // Función para formatear fecha como YYYY-MM-DD en tu zona horaria
    const formatearFecha = (fecha) => {
        const local = new Date(
            fecha.getTime() - fecha.getTimezoneOffset() * 60000
        );
        return local.toISOString().split("T")[0];
    };

    switch (opcion) {
        case "hoy":
            inicio = fin = formatearFecha(hoy);
            break;

        case "semana":
            const diaSemana = hoy.getDay() === 0 ? 7 : hoy.getDay(); // domingo como 7
            const primerDiaSemana = new Date(hoy);
            primerDiaSemana.setDate(hoy.getDate() - diaSemana + 1);
            inicio = formatearFecha(primerDiaSemana);
            fin = formatearFecha(new Date()); // hoy
            break;

        case "mes":
            const primerDiaMes = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
            inicio = formatearFecha(primerDiaMes);
            fin = formatearFecha(new Date()); // hoy
            break;

        case "todas":
            inicio = "";
            fin = "";
            break;
    }

    fechaInicio.value = inicio;
    fechaFin.value = fin;
    cargarDatos();
};

// Gráfico de Estado (doughnut)
const crearGraficoEstado = (ordenes) => {
    if (chartEstadoInstance) chartEstadoInstance.destroy();

    const datos = [
        ordenes.filter((o) => o.Estado === "pendiente").length,
        ordenes.filter((o) => o.Estado === "en proceso lavado").length,
        ordenes.filter((o) => o.Estado === "en proceso planchado").length,
        ordenes.filter((o) => o.Estado === "finalizado").length,
    ];

    const ctx = estadoChart.value.getContext("2d");
    chartEstadoInstance = new Chart(ctx, {
        type: "doughnut",
        data: {
            labels: ["Pendientes", "Lavado", "Planchado", "Finalizadas"],
            datasets: [
                {
                    data: datos,
                    backgroundColor: [
                        "#ffc107",
                        "#17a2b8",
                        "#007bff",
                        "#28a745",
                    ],
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: "bottom" },
            },
        },
    });
};

// Gráfico de Ventas (barras)
const crearGraficoVentas = (ordenes) => {
    if (chartVentasInstance) chartVentasInstance.destroy();

    // Agrupar ventas por fecha
    const ventasPorDia = {};
    ordenes.forEach((orden) => {
        const fecha = new Date(
            orden.FechaOrden + "T00:00:00"
        ).toLocaleDateString("es-PE", {
            timeZone: "America/Lima",
        });
        if (!ventasPorDia[fecha]) ventasPorDia[fecha] = 0;
        ventasPorDia[fecha]++;
    });

    const labels = Object.keys(ventasPorDia).sort(
        (a, b) => new Date(a) - new Date(b)
    );
    const data = labels.map((fecha) => ventasPorDia[fecha]);

    const ctx = ventasChart.value.getContext("2d");
    chartVentasInstance = new Chart(ctx, {
        type: "bar",
        data: {
            labels,
            datasets: [
                {
                    label: "Órdenes por día",
                    data,
                    backgroundColor: "#007bff",
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true },
            },
        },
    });
};

const mostrarNotificacion = (mensaje) => {
    const toast = document.createElement("div");
    toast.className =
        "toast align-items-center text-white bg-primary border-0 show position-fixed bottom-0 end-0 m-3";
    toast.style.zIndex = 9999;
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${mensaje}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
};

onMounted(() => {
    establecerRango("hoy");

    intervalo = setInterval(() => {
        cargarDatos();
    }, 10000);
});

onBeforeUnmount(() => {
    if (intervalo) clearInterval(intervalo);
});
</script>

<style scoped>
.chart-container {
    position: relative;
    height: 300px;
}
.card-title {
    font-size: 1.1rem;
    font-weight: bold;
}
</style>
