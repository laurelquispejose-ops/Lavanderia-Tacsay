<template>
    <div class="container mt-4">
        <div class="row mb-4">
            <div class="col-md-8">
                <h2 class="mb-0"><i class="bi bi-credit-card me-2"></i>Pago de Orden</h2>
                <p class="text-muted">Completa el pago de tu servicio de lavandería</p>
            </div>
            <div class="col-md-4 text-md-end">
                <a href="/cliente/ordenes" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Volver a mis órdenes
                </a>
            </div>
        </div>

        <div class="row">
            <!-- Detalles de la orden -->
            <div class="col-md-5 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <h4 class="card-title mb-4">Resumen de la Orden</h4>
                        
                        <div v-if="cargando" class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                            <p class="mt-2">Cargando detalles de la orden...</p>
                        </div>
                        
                        <div v-else-if="error" class="alert alert-danger">
                            {{ error }}
                        </div>
                        
                        <div v-else>
                            <div class="d-flex justify-content-between mb-3">
                                <span class="fw-bold">Número de Orden:</span>
                                <span>#{{ orden.IdOrden }}</span>
                            </div>
                            
                            <div class="d-flex justify-content-between mb-3">
                                <span class="fw-bold">Fecha:</span>
                                <span>{{ formatDate(orden.FechaOrden) }}</span>
                            </div>
                            
                            <div class="d-flex justify-content-between mb-3">
                                <span class="fw-bold">Estado:</span>
                                <span class="badge" :class="estadoClass">{{ orden.Estado }}</span>
                            </div>
                            
                            <hr class="my-4">
                            
                            <h5 class="mb-3">Prendas</h5>
                            <ul class="list-group list-group-flush mb-4">
                                <li v-for="(detalle, index) in orden.detalles" :key="index" 
                                    class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="fw-medium">{{ detalle.prenda?.nombre || 'Prenda' }}</span>
                                        <div class="text-muted small">
                                            <span v-if="detalle.Cantidad">Cantidad: {{ detalle.Cantidad }}</span>
                                            <span v-else-if="detalle.Peso">Peso: {{ detalle.Peso }} kg</span>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                            
                            <hr class="my-4">
                            
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <span>S/. {{ formatPrice(orden.PrecioTotal) }}</span>
                            </div>
                            
                            <div class="d-flex justify-content-between fw-bold fs-5 mt-3">
                                <span>Total:</span>
                                <span>S/. {{ formatPrice(orden.PrecioTotal) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Formulario de pago / instrucciones según método -->
            <div class="col-md-7">
                <div v-if="orden && !cargando" class="debug-info alert alert-info mb-3">
                    Orden cargada ID: {{ orden.id || orden.IdOrden }}, Total: {{ orden.total || orden.PrecioTotal }}
                </div>

                <!-- Pago con Tarjeta (integrado con MercadoPago) -->
                <mercado-pago-form 
                    v-if="metodoPago === 'Tarjeta' && orden && !cargando" 
                    :orden-id="orden.id || orden.IdOrden" 
                    :total="orden.total || orden.PrecioTotal"
                    @cancel="volverAOrdenes"
                />

                <!-- Pago con Yape (instrucciones integradas) -->
                <div v-else-if="metodoPago === 'Yape' && orden && !cargando" class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h4 class="card-title mb-3"><i class="bi bi-phone me-2"></i>Pagar con Yape</h4>
                        <p class="text-muted">Realiza el pago sin salir del sitio siguiendo estos pasos:</p>
                        <ol>
                            <li>Abre tu app Yape y escanea el QR o ingresa el número del comercio.</li>
                            <li>Monto a pagar: <strong>S/. {{ formatPrice(orden.total || orden.PrecioTotal) }}</strong></li>
                            <li>Ingresa como referencia: <strong>Orden #{{ orden.id || orden.IdOrden }}</strong></li>
                        </ol>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            Luego de pagar, regresa y presiona el botón para confirmar el pago.
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-success" @click="confirmarPagoManual('Yape')">
                                <i class="bi bi-check2-circle me-2"></i> He realizado el pago
                            </button>
                            <a href="/cliente/ordenes" class="btn btn-outline-secondary">Volver</a>
                        </div>
                    </div>
                </div>

                <!-- Pago por Transferencia bancaria (instrucciones) -->
                <div v-else-if="metodoPago && metodoPago.includes('Transferencia') && orden && !cargando" class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h4 class="card-title mb-3"><i class="bi bi-bank me-2"></i>Transferencia Bancaria</h4>
                        <p class="text-muted">Realiza la transferencia a la cuenta indicada y confirma el pago.</p>
                        <ul class="list-unstyled">
                            <li><strong>Banco:</strong> BCP</li>
                            <li><strong>Cuenta:</strong> 123-45678901-0-12</li>
                            <li><strong>CCI:</strong> 002-123-00456789012-34</li>
                            <li><strong>Monto:</strong> S/. {{ formatPrice(orden.total || orden.PrecioTotal) }}</li>
                            <li><strong>Referencia:</strong> Orden #{{ orden.id || orden.IdOrden }}</li>
                        </ul>
                        <div class="d-flex gap-2">
                            <button class="btn btn-success" @click="confirmarPagoManual('Transferencia')">
                                <i class="bi bi-check2-circle me-2"></i> He realizado la transferencia
                            </button>
                            <a href="/cliente/ordenes" class="btn btn-outline-secondary">Volver</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';
import MercadoPagoForm from './MercadoPagoForm.vue';

export default {
    name: 'PagoOrden',
    components: {
        MercadoPagoForm
    },
    props: {
        orden_id: {
            type: [Number, String],
            required: true
        }
    },
    data() {
        return {
            orden: null,
            cargando: true,
            error: null
        };
    },
    computed: {
        metodoPago() {
            return this.orden?.pago?.MetodoPago || this.orden?.MetodoPago || null;
        },
        estadoClass() {
            const estados = {
                'pendiente': 'bg-warning text-dark',
                'en proceso lavado': 'bg-info text-dark',
                'en proceso planchado': 'bg-primary',
                'finalizado': 'bg-success'
            };
            
            return estados[this.orden?.Estado] || 'bg-secondary';
        }
    },
    methods: {
        formatPrice(price) {
            return parseFloat(price).toFixed(2);
        },
        
        formatDate(dateString) {
            if (!dateString) return '';
            
            const options = { 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            };
            
            return new Date(dateString).toLocaleDateString('es-ES', options);
        },
        
        async cargarOrden() {
            this.cargando = true;
            this.error = null;
            
            try {
                const response = await axios.get(`/api/cliente/orden/${this.orden_id}`);
                this.orden = response.data;
                console.log('Orden cargada:', this.orden);
                
                // Verificar si la orden ya está pagada
                if (this.orden.pago && this.orden.pago.Estado === 'completado') {
                    this.error = 'Esta orden ya ha sido pagada.';
                }
                
            } catch (error) {
                console.error('Error al cargar la orden:', error);
                this.error = error.response?.data?.message || 
                             error.response?.data?.error || 
                             'No se pudo cargar la información de la orden.';
            } finally {
                this.cargando = false;
            }
        },
        
        volverAOrdenes() {
            window.location.href = '/cliente/ordenes';
        },
        async confirmarPagoManual(metodo) {
            try {
                // Marcar pago como registrado en backend utilizando el endpoint existente
                await axios.put(`/api/ordenes/${this.orden.IdOrden}/pago`, {
                    metodo,
                    monto: this.orden.PrecioTotal
                });
                alert('¡Gracias! Tu pago ha sido marcado como registrado.');
                this.volverAOrdenes();
            } catch (e) {
                console.error(e);
                alert('No se pudo confirmar el pago. Intenta nuevamente.');
            }
        }
    },
    mounted() {
        this.cargarOrden();
    }
};
</script>

<style scoped>
.card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
}

.list-group-item {
    border-left: none;
    border-right: none;
}

.badge {
    padding: 0.5em 0.75em;
}
</style>
