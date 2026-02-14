<template>
    <div class="culqi-payment-container">
        <!-- Formulario de pago con Culqi -->
        <div v-if="!loading && !paymentSuccess">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h4 class="card-title mb-4">
                        <i class="bi bi-credit-card me-2"></i>Pago con Tarjeta
                    </h4>
                    
                    <div v-if="error" class="alert alert-danger mb-4">
                        {{ error }}
                    </div>
                    
                    <div class="mb-4">
                        <p class="mb-1">Orden #{{ ordenId }}</p>
                        <h5 class="mb-3">Total a pagar: S/. {{ formatPrice(total) }}</h5>
                        
                        <div class="d-flex align-items-center mb-3">
                            <div class="payment-method-icons me-3">
                                <i class="bi bi-credit-card me-1"></i>
                                <i class="bi bi-credit-card-2-front me-1"></i>
                                <i class="bi bi-credit-card-2-back"></i>
                            </div>
                            <div class="text-muted small">
                                Aceptamos Visa, Mastercard, American Express y más
                            </div>
                        </div>
                    </div>
                    
                    <!-- Formulario de tarjeta -->
                    <div class="mb-4">
                        <div class="form-group mb-3">
                            <label for="card-number" class="form-label">Número de Tarjeta</label>
                            <div id="card-number" class="form-control"></div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="card-expiry" class="form-label">Fecha de Expiración</label>
                                <div id="card-expiry" class="form-control"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="card-cvc" class="form-label">CVC</label>
                                <div id="card-cvc" class="form-control"></div>
                            </div>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="card-email" class="form-label">Correo Electrónico</label>
                            <input type="email" id="card-email" v-model="email" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <button 
                            @click="procesarPago" 
                            class="btn btn-primary w-100 py-2"
                            :disabled="loading || !email"
                        >
                            <span v-if="loading" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            Pagar ahora
                        </button>
                    </div>
                    
                    <div class="text-center text-muted small">
                        <p class="mb-1">Pago seguro procesado por Culqi</p>
                        <p class="mb-0">Tus datos de tarjeta están protegidos con encriptación SSL</p>
                    </div>
                </div>
            </div>
            
            <div class="text-center">
                <button @click="cancelarPago" class="btn btn-link text-muted">
                    Cancelar y volver
                </button>
            </div>
        </div>
        
        <!-- Pantalla de carga -->
        <div v-if="loading && !paymentSuccess" class="text-center py-5">
            <div class="spinner-border text-primary mb-3" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p>Procesando tu pago, por favor espera...</p>
        </div>
        
        <!-- Pantalla de éxito -->
        <div v-if="paymentSuccess" class="text-center py-5">
            <div class="mb-4">
                <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
            </div>
            <h3 class="mb-3">¡Pago Realizado con Éxito!</h3>
            <p class="lead mb-4">
                Tu pago ha sido procesado correctamente.
            </p>
            <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                <a href="/cliente/ordenes" class="btn btn-primary">
                    <i class="bi bi-bag-check me-2"></i> Ver mis órdenes
                </a>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    name: 'CulqiPaymentForm',
    props: {
        ordenId: {
            type: [Number, String],
            required: true
        },
        total: {
            type: [Number, String],
            required: true
        }
    },
    data() {
        return {
            loading: false,
            error: null,
            paymentSuccess: false,
            email: '',
            culqi: null,
            token: null
        };
    },
    methods: {
        formatPrice(price) {
            return parseFloat(price).toFixed(2);
        },
        
        initCulqi() {
            // Verificar si el script de Culqi ya está cargado
            if (window.Culqi) {
                this.setupCulqi();
                return;
            }
            
            // Cargar el script de Culqi
            const script = document.createElement('script');
            script.src = 'https://checkout.culqi.com/js/v3';
            script.async = true;
            script.onload = () => {
                this.setupCulqi();
            };
            document.head.appendChild(script);
        },
        
        setupCulqi() {
            // Configurar Culqi con la llave pública
            window.Culqi.publicKey = 'pk_test_your_public_key'; // Reemplazar con tu llave pública
            
            // Configurar el callback para cuando se genera el token
            window.Culqi.options({
                lang: 'es',
                modal: true,
                installments: false,
                customButton: 'Pagar',
            });
            
            window.Culqi.settings({
                title: 'TACSAY Lavandería',
                currency: 'PEN',
                description: `Pago de orden #${this.ordenId}`,
                amount: parseFloat(this.total) * 100 // Convertir a centavos
            });
        },
        
        procesarPago() {
            if (!this.email) {
                this.error = 'Por favor, ingresa tu correo electrónico';
                return;
            }
            
            this.loading = true;
            this.error = null;
            
            // Abrir el formulario de Culqi
            window.Culqi.open();
            
            // Configurar el callback para cuando se genera el token
            window.culqi = () => {
                if (window.Culqi.token) {
                    // Se ha creado un token exitosamente
                    this.token = window.Culqi.token.id;
                    this.completarPago();
                } else {
                    // Hubo algún problema
                    this.loading = false;
                    this.error = 'No se pudo generar el token de pago. Por favor, intenta nuevamente.';
                }
            };
        },
        
        async completarPago() {
            try {
                // Enviar el token al servidor para crear el cargo
                const response = await axios.post('/api/culqi/crear-cargo', {
                    orden_id: this.ordenId,
                    token: this.token,
                    email: this.email
                });
                
                if (response.data.success) {
                    this.paymentSuccess = true;
                    
                    // Redirigir a la página de éxito después de 2 segundos
                    setTimeout(() => {
                        window.location.href = `/cliente/pago/exito?orden_id=${this.ordenId}&cargo_id=${response.data.cargo_id}`;
                    }, 2000);
                } else {
                    throw new Error(response.data.message || 'Error al procesar el pago');
                }
                
            } catch (error) {
                console.error('Error al completar el pago:', error);
                this.error = error.response?.data?.message || 
                             error.response?.data?.error || 
                             'Error al procesar el pago. Por favor, intenta nuevamente.';
            } finally {
                this.loading = false;
            }
        },
        
        cancelarPago() {
            // Emitir evento para cancelar el pago
            this.$emit('cancel');
        }
    },
    mounted() {
        this.initCulqi();
    }
};
</script>

<style scoped>
.culqi-payment-container {
    max-width: 600px;
    margin: 0 auto;
}

.payment-method-icons {
    font-size: 1.5rem;
}

.btn-primary {
    background-color: #00a19a;
    border-color: #00a19a;
}

.btn-primary:hover {
    background-color: #008f89;
    border-color: #008f89;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 161, 154, 0.2);
}

/* Estilos para los campos de tarjeta */
.form-control {
    padding: 0.75rem;
    border-radius: 0.375rem;
    border: 1px solid #ced4da;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.form-control:focus {
    border-color: #00a19a;
    box-shadow: 0 0 0 0.25rem rgba(0, 161, 154, 0.25);
}
</style>
