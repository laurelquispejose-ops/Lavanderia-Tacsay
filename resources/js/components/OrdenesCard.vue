<template>
    <div class="card shadow-sm" :class="estadoCardClass">
        <!-- Notificación personalizada -->
        <div v-if="notificacionVisible" class="notificacion-container position-fixed top-0 end-0 p-3" style="z-index: 1200">
            <div 
                class="notificacion-toast align-items-center text-white p-3 rounded" 
                :class="notificacionClass" 
                role="alert" 
                aria-live="assertive" 
                aria-atomic="true"
            >
                <div class="d-flex">
                    <div class="me-2">
                        <i v-if="notificacionClass === 'bg-success'" class="bi bi-check-circle-fill"></i>
                        <i v-else class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                    <div>
                        {{ notificacionMensaje }}
                    </div>
                    <button type="button" class="btn-close btn-close-white ms-auto" @click="ocultarNotificacion"></button>
                </div>
            </div>
        </div>

        <!-- Modal de confirmación personalizado -->
        <div v-if="modalVisible" class="modal-backdrop" @click="cerrarModal"></div>
        <div v-if="modalVisible" class="modal-custom" tabindex="-1" role="dialog" aria-labelledby="confirmModalLabel">
            <div class="modal-dialog-custom" @click.stop>
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="confirmModalLabel">Confirmar cambio de estado</h5>
                        <button type="button" class="btn-close" @click="cerrarModal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        ¿Estás seguro de que deseas cambiar el estado de la orden a <strong>{{ siguienteEstadoTexto }}</strong>?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="cerrarModal" ref="cancelarBtn">Cancelar</button>
                        <button type="button" class="btn btn-primary" @click="confirmarCambioEstado" ref="confirmarBtn">Confirmar</button>
                    </div>
                </div>
            </div>
        </div>

        <div @click="mostrarDetalles = !mostrarDetalles" class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="card-title mb-0">Orden # {{ orden.IdOrden }}</h5>
                <div class="small text-muted mt-1">
                    <span class="badge text-bg-light me-1">Entrada: {{ formatFecha(orden.FechaEntrada || orden.FechaOrden) }}</span>
                    <span class="badge text-bg-light">Entrega: {{ orden.FechaEntrega ? formatFecha(orden.FechaEntrega) : '-' }}</span>
                </div>
                <h6 class="card-subtitle mt-1 text-muted">
                    {{ orden.cliente?.nombre }}
                </h6>
            </div>
            <div class="estado-badge" :class="estadoBadgeClass">
                <i :class="estadoIcono"></i> {{ estadoTexto }}
            </div>
        </div>
        <div class="card-body p-0">
            <transition name="slide-fade">
                <div v-if="mostrarDetalles" class="p-3">
                    <div
                        v-for="(detalle, index) in orden.detalles"
                        :key="index"
                        class="mb-3"
                    >
                        <OrdenDetalle
                            :detalle="detalle"
                            :index="index"
                            :idOrden="orden.IdOrden"
                        />
                    </div>

                    <!-- Pago -->
                    <div class="mt-3 p-3 border rounded">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>Pago:</strong>
                                <span v-if="orden.pago">{{ orden.pago.MetodoPago }} — S/ {{ orden.ACuenta?.toFixed?.(2) || orden.pago.MontoPagado }}</span>
                                <span v-else class="text-muted">No registrado</span>
                                <div class="small text-muted">Saldo: S/ {{ (orden.Saldo ?? 0).toFixed ? orden.Saldo.toFixed(2) : orden.Saldo }}</div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <select v-model="metodoPago" class="form-select form-select-sm" style="min-width: 180px;">
                                    <option value="Efectivo">Efectivo</option>
                                    <option value="Transferencia">Transferencia</option>
                                    <option value="Tarjeta">Tarjeta</option>
                                    <option value="Yape">Yape</option>
                                </select>
                                <select v-model="estadoPago" class="form-select form-select-sm" style="min-width: 160px;">
                                    <option value="pendiente">Pago / Pendiente</option>
                                    <option value="completado">Pago / Sí</option>
                                </select>
                                <button class="btn btn-sm btn-success" @click.stop="registrarPago">Guardar Pago</button>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-3">
                        <button
                            class="btn btn-danger m-1"
                            @click="eliminarOrden"
                        >
                            <i class="bi bi-trash"></i> Eliminar
                        </button>
                        <button
                            class="btn m-1"
                            :class="estadoBotonClass"
                            @click="iniciarCambioEstado"
                            :disabled="orden.Estado === 'finalizado'"
                        >
                            <i :class="estadoBotonIcono"></i> {{ textoBotonEstado }}
                        </button>
                    </div>
                </div>
            </transition>
        </div>
    </div>
</template>

<script setup>
import OrdenDetalle from "./OrdenDetalle.vue";
import { ref, computed, onMounted, onUnmounted, nextTick } from "vue";
import axios from "axios";

const props = defineProps({
    orden: Object,
});

const mostrarDetalles = ref(false);
const metodoPago = ref('Efectivo');
const estadoPago = ref('pendiente');
const notificacionMensaje = ref('');
const notificacionClass = ref('bg-success');
const toastElement = ref(null);
const confirmModal = ref(null);
let modalInstance = null;
let siguienteEstado = '';

// Estado para el modal
const modalVisible = ref(false);
const cancelarBtn = ref(null);
const confirmarBtn = ref(null);

// Referencias para componentes y manejo de accesibilidad
onMounted(() => {
    // Agregar event listener para la tecla Escape
    window.addEventListener('keydown', handleKeyDown);
});

// Limpiar event listeners cuando el componente se desmonta
onUnmounted(() => {
    window.removeEventListener('keydown', handleKeyDown);
});

// Manejar teclas para accesibilidad
function handleKeyDown(event) {
    if (event.key === 'Escape' && modalVisible.value) {
        cerrarModal();
    }
};

// Función para cerrar el modal de manera accesible
function cerrarModal() {
    modalVisible.value = false;
};

// Sistema de notificaciones simplificado (sin depender de bootstrap global)
const notificacionVisible = ref(false);

function mostrarNotificacion(mensaje, tipo = 'success') {
    notificacionMensaje.value = mensaje;
    notificacionClass.value = tipo === 'success' ? 'bg-success' : 'bg-danger';
    notificacionVisible.value = true;
    
    // Auto ocultar después de 3 segundos
    setTimeout(() => {
        ocultarNotificacion();
    }, 3000);
}

function ocultarNotificacion() {
    notificacionVisible.value = false;
}

function formatFecha(fecha) {
    try {
        const d = new Date(fecha);
        if (isNaN(d)) return fecha;
        return d.toISOString().slice(0, 10);
    } catch {
        return fecha;
    }
}

// Función para eliminar la orden
async function eliminarOrden() {
    if (!confirm("¿Estás seguro de que deseas eliminar esta orden?")) return;

    try {
        await axios.delete(`/api/ordenes/${props.orden.IdOrden}`);
        mostrarNotificacion("Orden eliminada correctamente", "success");
        emit("ordenEliminada"); // Avisar al padre que la orden fue eliminada.
    } catch (error) {
        console.error("Error al eliminar la orden:", error);
        mostrarNotificacion("No se pudo eliminar la orden", "error");
    }
}

// Iniciar el proceso de cambio de estado
function iniciarCambioEstado() {
    if (props.orden.Estado === "finalizado") {
        mostrarNotificacion("La orden ya está finalizada", "error");
        return;
    }
    
    siguienteEstado = obtenerSiguienteEstado();
    modalVisible.value = true;
}

// Confirmar el cambio de estado después de la confirmación
async function confirmarCambioEstado() {
    modalVisible.value = false;
    await avanzarEstado(siguienteEstado);
}

// Obtener el siguiente estado según el estado actual
function obtenerSiguienteEstado() {
    switch (props.orden.Estado) {
        case "pendiente":
            return "en proceso lavado";
        case "en proceso lavado":
            return "en proceso planchado";
        case "en proceso planchado":
            return "finalizado";
        default:
            return props.orden.Estado;
    }
}

// Texto descriptivo del siguiente estado
const siguienteEstadoTexto = computed(() => {
    switch (obtenerSiguienteEstado()) {
        case "en proceso lavado":
            return "En Proceso de Lavado";
        case "en proceso planchado":
            return "En Proceso de Planchado";
        case "finalizado":
            return "Finalizado";
        default:
            return "Desconocido";
    }
});

// Computa el texto del botón según el estado actual
const textoBotonEstado = computed(() => {
    switch (props.orden.Estado) {
        case "pendiente":
            return "Iniciar Lavado";
        case "en proceso lavado":
            return "Iniciar Planchado";
        case "en proceso planchado":
            return "Finalizar Orden";
        case "finalizado":
            return "Orden Finalizada";
        default:
            return "Avanzar Estado";
    }
});

// Función para avanzar al siguiente estado
async function avanzarEstado(nuevoEstado) {
    try {
        await axios.put(`/api/ordenes/${props.orden.IdOrden}/estado`, {
            estado: nuevoEstado,
        });
        mostrarNotificacion(`Estado cambiado a: ${nuevoEstado}`, "success");
        emit("estadoActualizado", props.orden.IdOrden, nuevoEstado);
    } catch (error) {
        console.error(error);
        mostrarNotificacion("Error al actualizar el estado", "error");
    }
}

// Registrar/actualizar pago
async function registrarPago() {
    try {
        const monto = props.orden.Saldo ?? props.orden.PrecioTotal;
        const res = await axios.put(`/api/ordenes/${props.orden.IdOrden}/pago`, {
            metodo: metodoPago.value,
            monto: monto,
            estado: estadoPago.value
        });
        mostrarNotificacion('Pago registrado correctamente', 'success');
        emit('estadoActualizado');
    } catch (e) {
        console.error(e);
        mostrarNotificacion('No se pudo registrar el pago', 'error');
    }
}

// Clases y estilos según el estado
const estadoCardClass = computed(() => {
    switch (props.orden.Estado) {
        case "pendiente":
            return "border-left-warning";
        case "en proceso lavado":
            return "border-left-info";
        case "en proceso planchado":
            return "border-left-primary";
        case "finalizado":
            return "border-left-success";
        default:
            return "";
    }
});

const estadoBadgeClass = computed(() => {
    switch (props.orden.Estado) {
        case "pendiente":
            return "bg-warning text-dark";
        case "en proceso lavado":
            return "bg-info text-white";
        case "en proceso planchado":
            return "bg-primary text-white";
        case "finalizado":
            return "bg-success text-white";
        default:
            return "bg-secondary text-white";
    }
});

const estadoBotonClass = computed(() => {
    switch (props.orden.Estado) {
        case "pendiente":
            return "btn-outline-warning";
        case "en proceso lavado":
            return "btn-outline-info";
        case "en proceso planchado":
            return "btn-outline-primary";
        case "finalizado":
            return "btn-outline-success";
        default:
            return "btn-outline-secondary";
    }
});

const estadoIcono = computed(() => {
    switch (props.orden.Estado) {
        case "pendiente":
            return "bi bi-hourglass-split";
        case "en proceso lavado":
            return "bi bi-droplet";
        case "en proceso planchado":
            return "bi bi-steam";
        case "finalizado":
            return "bi bi-check-circle";
        default:
            return "bi bi-question-circle";
    }
});

const estadoBotonIcono = computed(() => {
    switch (props.orden.Estado) {
        case "pendiente":
            return "bi bi-play-fill";
        case "en proceso lavado":
            return "bi bi-arrow-right";
        case "en proceso planchado":
            return "bi bi-check-lg";
        case "finalizado":
            return "bi bi-check-all";
        default:
            return "bi bi-arrow-right";
    }
});

const estadoTexto = computed(() => {
    switch (props.orden.Estado) {
        case "pendiente":
            return "Pendiente";
        case "en proceso lavado":
            return "En Lavado";
        case "en proceso planchado":
            return "En Planchado";
        case "finalizado":
            return "Finalizado";
        default:
            return props.orden.Estado;
    }
});

const emit = defineEmits(["ordenEliminada", "estadoActualizado"]);
</script>

<style scoped>
/* Animaciones para transiciones */
.slide-fade-enter-active {
    transition: all 0.3s ease-out;
}

.slide-fade-leave-active {
    transition: all 0.3s cubic-bezier(1, 0.5, 0.8, 1);
}

.slide-fade-enter-from,
.slide-fade-leave-to {
    transform: translateY(-10px);
    opacity: 0;
}

/* Estilos para los bordes de colores según estado */
.border-left-warning {
    border-left: 4px solid #f6c23e;
}

.border-left-info {
    border-left: 4px solid #36b9cc;
}

.border-left-primary {
    border-left: 4px solid #4e73df;
}

.border-left-success {
    border-left: 4px solid #1cc88a;
}

/* Estilo para la insignia de estado */
.estado-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    font-weight: bold;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

/* Hover effect para la tarjeta */
.card {
    transition: transform 0.2s, box-shadow 0.2s;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

/* Estilo para el encabezado clickeable */
.card-header {
    cursor: pointer;
    transition: background-color 0.2s;
}

.card-header:hover {
    background-color: rgba(0, 0, 0, 0.03);
}

/* Estilos para la notificación personalizada */
.notificacion-toast {
    box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.1);
    min-width: 250px;
    animation: slide-in 0.3s ease-out forwards;
}

@keyframes slide-in {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

/* Estilos para el modal personalizado */
.modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 1040;
}

.modal-custom {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1050;
}

.modal-dialog-custom {
    background-color: white;
    border-radius: 0.3rem;
    max-width: 500px;
    width: 100%;
    margin: 1.75rem;
    animation: fade-in 0.2s ease-out;
}

@keyframes fade-in {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.modal-content {
    position: relative;
    display: flex;
    flex-direction: column;
    width: 100%;
    pointer-events: auto;
    background-color: #fff;
    background-clip: padding-box;
    border-radius: 0.3rem;
    outline: 0;
}

.modal-header {
    display: flex;
    flex-shrink: 0;
    align-items: center;
    justify-content: space-between;
    padding: 1rem;
    border-bottom: 1px solid #dee2e6;
    border-top-left-radius: calc(0.3rem - 1px);
    border-top-right-radius: calc(0.3rem - 1px);
}

.modal-body {
    position: relative;
    flex: 1 1 auto;
    padding: 1rem;
}

.modal-footer {
    display: flex;
    flex-wrap: wrap;
    flex-shrink: 0;
    align-items: center;
    justify-content: flex-end;
    padding: 0.75rem;
    border-top: 1px solid #dee2e6;
    border-bottom-right-radius: calc(0.3rem - 1px);
    border-bottom-left-radius: calc(0.3rem - 1px);
}
</style>
