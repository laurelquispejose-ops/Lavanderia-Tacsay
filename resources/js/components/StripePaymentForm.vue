<template>
    <div class="stripe-payment-container">
        <!-- Formulario de pago con Stripe -->
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
                    
                    <div class="mb-4">
                        <button 
                            @click="iniciarPagoStripe" 
                            class="btn btn-primary w-100 py-2"
                            :disabled="loading"
                        >
                            <span v-if="loading" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            Pagar ahora con Stripe
                        </button>
                    </div>
                    
                    <div class="text-center text-muted small">
                        <p class="mb-1">Pago seguro procesado por Stripe</p>
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
    name: 'StripePaymentForm',
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
            stripeSessionId: null
        };
    },
    methods: {
        formatPrice(price) {
            return parseFloat(price).toFixed(2);
        },
        
        async iniciarPagoStripe() {
            this.loading = true;
            this.error = null;
            
            try {
                // Crear una sesión de pago en Stripe
                const response = await axios.post('/api/stripe/crear-sesion', {
                    orden_id: this.ordenId
                });
                
                // Redirigir a la página de pago de Stripe
                if (response.data && response.data.url) {
                    window.location.href = response.data.url;
                } else {
                    throw new Error('No se pudo obtener la URL de pago');
                }
                
            } catch (error) {
                console.error('Error al iniciar el pago:', error);
                this.error = error.response?.data?.message || 
                             error.response?.data?.error || 
                             'Error al procesar el pago. Por favor, intenta nuevamente.';
                this.loading = false;
            }
        },
        
        cancelarPago() {
            // Emitir evento para cancelar el pago
            this.$emit('cancel');
        },
        
        verificarEstadoPago() {
            if (this.stripeSessionId) {
                // Verificar el estado del pago con el backend
                axios.get(`/api/stripe/verificar/${this.stripeSessionId}`)
                    .then(response => {
                        if (response.data.success) {
                            this.paymentSuccess = true;
                        }
                    })
                    .catch(error => {
                        console.error('Error al verificar el pago:', error);
                    })
                    .finally(() => {
                        this.loading = false;
                    });
            }
        }
    },
    mounted() {
        // Verificar si hay un ID de sesión en la URL (redirección desde Stripe)
        const urlParams = new URLSearchParams(window.location.search);
        this.stripeSessionId = urlParams.get('session_id');
        
        if (this.stripeSessionId) {
            this.loading = true;
            this.verificarEstadoPago();
        }
    }
};
</script>

<style scoped>
.stripe-payment-container {
    max-width: 600px;
    margin: 0 auto;
}

.payment-method-icons {
    font-size: 1.5rem;
}

.btn-primary {
    background-color: #635BFF;
    border-color: #635BFF;
}

.btn-primary:hover {
    background-color: #4b44e0;
    border-color: #4b44e0;
}
</style>
