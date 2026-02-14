<template>
    <div class="container mt-4">
        <div class="row mb-4">
            <div class="col-md-8">
                <h2 class="mb-0"><i class="bi bi-bag-check me-2"></i>Mis Órdenes</h2>
                <p class="text-muted">Gestiona y monitorea el estado de tus servicios de lavandería</p>
            </div>
            <div class="col-md-4 text-md-end">
                <a href="/cliente/nueva-orden" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i> Nueva Orden
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Filtrar por estado</h5>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="filtroEstado" id="todos" value="todos" v-model="filtroEstado" checked>
                            <label class="form-check-label" for="todos">Todos</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="filtroEstado" id="pendiente" value="pendiente" v-model="filtroEstado">
                            <label class="form-check-label" for="pendiente">Pendientes</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="filtroEstado" id="en_proceso" value="en proceso lavado" v-model="filtroEstado">
                            <label class="form-check-label" for="en_proceso">En Proceso</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="filtroEstado" id="finalizado" value="finalizado" v-model="filtroEstado">
                            <label class="form-check-label" for="finalizado">Finalizados</label>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-9">
                <div v-if="cargando" class="d-flex justify-content-center my-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </div>
                
                <div v-else-if="ordenesFiltradas.length > 0" class="orden-list">
                    <transition-group name="fade" tag="div">
                        <OrdenCard
                            v-for="orden in ordenesFiltradas"
                            :key="orden.id"
                            :orden="orden"
                            class="mb-3"
                        />
                    </transition-group>
                </div>
                
                <div v-else class="alert alert-info shadow-sm">
                    <i class="bi bi-info-circle me-2"></i>
                    <span v-if="filtroEstado !== 'todos'">No tienes órdenes con el estado <strong>{{ filtroEstado }}</strong>.</span>
                    <span v-else>No tienes órdenes registradas. ¡Crea tu primera orden!</span>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import OrdenCard from "./OrdenCard.vue";

export default {
    name: "ClienteOrdenes",
    components: { OrdenCard },
    data() {
        return {
            ordenes: [],
            filtroEstado: "todos",
            cargando: true,
            error: null
        };
    },
    computed: {
        ordenesFiltradas() {
            if (this.filtroEstado === "todos") {
                return this.ordenes;
            }
            return this.ordenes.filter(orden => orden.estado === this.filtroEstado);
        },
        estadisticas() {
            return {
                total: this.ordenes.length,
                pendientes: this.ordenes.filter(o => o.estado === "pendiente").length,
                enProceso: this.ordenes.filter(o => o.estado.includes("en proceso")).length,
                finalizadas: this.ordenes.filter(o => o.estado === "finalizado").length
            };
        }
    },
    mounted() {
        this.obtenerOrdenesCliente();
    },
    methods: {
        async obtenerOrdenesCliente() {
            this.cargando = true;
            this.error = null;
            
            try {
                const response = await fetch("/api/cliente/ordenes", {
                    method: "GET",
                    credentials: "include",
                    headers: {
                        Accept: "application/json",
                        "X-CSRF-TOKEN": document
                            .querySelector('meta[name="csrf-token"]')
                            .getAttribute("content"),
                    },
                });

                if (!response.ok) {
                    throw new Error("Error en la respuesta del servidor");
                }

                const data = await response.json();
                console.log("Órdenes cargadas:", data);
                // Depurar estructura de cada orden
                data.forEach(orden => {
                    console.log(`Orden ID: ${orden.id} - Estado: ${orden.estado}`);
                    console.log(`MetodoPago: ${orden.metodo_pago}, EstadoPago: ${orden.estado_pago}`);
                });
                this.ordenes = data;
            } catch (error) {
                console.error("Error al obtener las órdenes", error);
                this.error = "No se pudieron cargar las órdenes. Por favor, intenta nuevamente más tarde.";
            } finally {
                this.cargando = false;
            }
        },
        actualizarFiltro(estado) {
            this.filtroEstado = estado;
        }
    },
};
</script>

<style scoped>
.container {
    max-width: 1200px;
    margin: auto;
}

.card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
}

.btn-primary {
    padding: 0.5rem 1.5rem;
    font-weight: 500;
    border-radius: 0.375rem;
}

.alert {
    border-radius: 0.375rem;
    padding: 1rem;
}

/* Animaciones para la lista de órdenes */
.fade-enter-active, .fade-leave-active {
    transition: opacity 0.5s, transform 0.5s;
}

.fade-enter-from, .fade-leave-to {
    opacity: 0;
    transform: translateY(20px);
}

/* Estilos para los filtros */
.form-check-input:checked {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.form-check-label {
    cursor: pointer;
}

/* Estilos para el indicador de carga */
.spinner-border {
    width: 3rem;
    height: 3rem;
}
</style>
