<template>
  <div class="mercadopago-container">
    <div v-if="loading" class="text-center mb-4">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Cargando...</span>
      </div>
      <p class="mt-2">Preparando el formulario de pago...</p>
    </div>
    
    <div v-if="error" class="alert alert-danger">
      {{ error }}
    </div>

    <div v-if="debugInfo" class="alert alert-info mb-3">
      <pre style="font-size: 0.8rem;">{{ debugInfo }}</pre>
    </div>
    
    <div class="card mb-4">
      <div class="card-header bg-primary text-white">
        <h5 class="mb-0">Resumen de la orden #{{ ordenId }}</h5>
      </div>
      <div class="card-body">
        <p><strong>Total a pagar:</strong> S/. {{ formatPrice(total) }}</p>
      </div>
    </div>
      
    <!-- Formulario de tarjeta de Mercado Pago Bricks -->
    <div class="card">
      <div class="card-body">
        <!-- Contenedor para el formulario de pago -->
        <div id="cardPaymentBrick_container"></div>
      </div>
    </div>
      
    <!-- Tarjetas de prueba -->
    <div class="card mt-4" v-if="testMode">
      <div class="card-header bg-info text-white">
        <h5 class="mb-0">Tarjetas de prueba</h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-sm">
            <thead>
              <tr>
                <th>Tipo</th>
                <th>Número</th>
                <th>CVV</th>
                <th>Fecha</th>
                <th>Resultado</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>Mastercard</td>
                <td>5031 7557 3453 0604</td>
                <td>123</td>
                <td>11/25</td>
                <td><span class="badge bg-success">Aprobado</span></td>
              </tr>
              <tr>
                <td>Visa</td>
                <td>4509 9535 6623 3704</td>
                <td>123</td>
                <td>11/25</td>
                <td><span class="badge bg-success">Aprobado</span></td>
              </tr>
              <tr>
                <td>Mastercard</td>
                <td>5031 7557 3453 0604</td>
                <td>123</td>
                <td>11/25</td>
                <td><span class="badge bg-warning text-dark">Pendiente</span></td>
              </tr>
              <tr>
                <td>Mastercard</td>
                <td>5031 7557 3453 0604</td>
                <td>123</td>
                <td>11/25</td>
                <td><span class="badge bg-danger">Rechazado</span></td>
              </tr>
            </tbody>
          </table>
        </div>
        <small class="text-muted">Para las tarjetas de prueba, usa cualquier nombre y documento de identidad.</small>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'MercadoPagoForm',
  
  props: {
    ordenId: {
      type: [String, Number],
      required: true
    },
    total: {
      type: [String, Number],
      required: true
    }
  },
  
  data() {
    return {
      loading: true,
      error: null,
      testMode: false,
      debugInfo: ''
    };
  },
  
  mounted() {
    this.debugInfo = 'Iniciando componente de pago para orden #' + this.ordenId + ', total: ' + this.total;
    console.log('MercadoPagoForm montado, preparando carga de Mercado Pago');
    
    // Primero verificamos la conexión con Mercado Pago
    this.verificarConexion()
      .then(conexionExitosa => {
        if (conexionExitosa) {
          // Solo si la conexión es exitosa, continuamos con la configuración
          this.obtenerConfiguracion();
        } else {
          // Si hay error de conexión, mostramos el mensaje pero no avanzamos
          this.loading = false;
        }
      });
  },
  
  methods: {
    verificarConexion() {
      this.debugInfo += '\nVerificando conexión con Mercado Pago...';
      return axios.get('/api/mercadopago/verificar-conexion')
        .then(response => {
          if (response.data.success) {
            this.debugInfo += '\nConexión exitosa con Mercado Pago';
            return true;
          } else {
            this.debugInfo += '\nFallo en conexión con Mercado Pago: ' + response.data.message;
            this.error = 'No se pudo conectar con Mercado Pago: ' + response.data.message;
            return false;
          }
        })
        .catch(error => {
          console.error('Error al verificar conexión:', error);
          this.debugInfo += '\nERROR al verificar conexión: ' + (error.response?.data?.message || error.message);
          this.error = 'Error al verificar conexión con Mercado Pago';
          return false;
        });
    },
    
    obtenerConfiguracion() {
      this.debugInfo += '\nObteniendo configuración de Mercado Pago...';
      
      axios.get('/api/mercadopago/config')
        .then(response => {
          console.log('Configuración recibida:', response.data);
          this.debugInfo += '\nConfiguración recibida exitosamente';
          
          if (!response.data.public_key) {
            this.error = 'No se pudo obtener la clave pública de Mercado Pago';
            this.loading = false;
            return;
          }
          
          this.testMode = response.data.test_mode;
          this.debugInfo += '\nModo de prueba: ' + (this.testMode ? 'Activado' : 'Desactivado');
          
          // Una vez que tenemos la configuración, cargamos el script de Mercado Pago
          this.cargarScriptMercadoPago(response.data.public_key);
        })
        .catch(error => {
          console.error('Error al obtener configuración:', error);
          this.debugInfo += '\nERROR al obtener configuración: ' + (error.response?.data?.message || error.message);
          this.error = 'Error al obtener configuración de Mercado Pago';
          this.loading = false;
        });
    },
    
    cargarScriptMercadoPago(publicKey) {
      this.debugInfo += '\nCargando script de Mercado Pago...';
      
      // Verificar si el script ya está cargado
      if (document.querySelector('script[src="https://sdk.mercadopago.com/js/v2"]')) {
        this.debugInfo += '\nScript ya cargado, inicializando directamente.';
        this.inicializarMercadoPago(publicKey);
        return;
      }
      
      // Cargar el script de Mercado Pago
      const script = document.createElement('script');
      script.src = 'https://sdk.mercadopago.com/js/v2';
      
      script.onload = () => {
        this.debugInfo += '\nScript cargado exitosamente';
        this.inicializarMercadoPago(publicKey);
      };
      
      script.onerror = () => {
        this.debugInfo += '\nERROR al cargar script de Mercado Pago';
        this.error = 'No se pudo cargar el servicio de pago';
        this.loading = false;
      };
      
      document.body.appendChild(script);
    },
    
    inicializarMercadoPago(publicKey) {
      try {
        this.debugInfo += '\nInicializando formulario de pago...';
        
        // Verificar que el objeto MercadoPago existe
        if (!window.MercadoPago) {
          throw new Error('El SDK de Mercado Pago no está disponible');
        }
        
        // Crear instancia de Mercado Pago directamente (sin almacenarla en this)
        const mp = new window.MercadoPago(publicKey, {
          locale: 'es-PE'
        });
        
        // Crear el formulario de pago directamente
        this.crearFormularioPago(mp, parseFloat(this.total));
        
      } catch (error) {
        console.error('Error al inicializar MercadoPago:', error);
        this.debugInfo += '\nERROR al inicializar MercadoPago: ' + error.message;
        this.error = 'Error al inicializar el servicio de pago';
        this.loading = false;
      }
    },
    
    crearFormularioPago(mp, monto) {
      try {
        // Esperar a que el elemento exista
        const interval = setInterval(() => {
          const container = document.getElementById('cardPaymentBrick_container');
          if (container) {
            clearInterval(interval);
            this.renderizarFormulario(mp, monto);
          }
        }, 200);
        
        // Timeout de seguridad
        setTimeout(() => {
          clearInterval(interval);
          if (!document.getElementById('cardPaymentBrick_container')) {
            this.debugInfo += '\nERROR: No se encontró el contenedor del formulario';
            this.error = 'Error al cargar el formulario de pago';
            this.loading = false;
          }
        }, 5000);
      } catch (error) {
        console.error('Error al crear formulario:', error);
        this.debugInfo += '\nERROR al crear formulario: ' + error.message;
        this.error = 'Error al crear el formulario de pago';
        this.loading = false;
      }
    },
    
    renderizarFormulario(mp, monto) {
      try {
        const bricks = mp.bricks();
        
        this.debugInfo += '\nCreando formulario con monto: ' + monto;
        
        bricks.create('cardPayment', 'cardPaymentBrick_container', {
          initialization: {
            amount: monto
          },
          callbacks: {
            onReady: () => {
              this.debugInfo += '\nFormulario cargado exitosamente';
              this.loading = false;
            },
            onSubmit: (cardFormData) => {
              // Mostrar cargando
              this.loading = true;
              this.error = null;
              
              return this.procesarPago(cardFormData);
            },
            onError: (error) => {
              console.error('Error en el formulario:', error);
              this.debugInfo += '\nERROR en el formulario: ' + error.message;
              this.error = 'Error en el formulario de pago';
              this.loading = false;
            }
          },
          customization: {
            visual: {
              style: {
                theme: 'default'
              }
            },
            paymentMethods: {
              maxInstallments: 1
            }
          }
        });
      } catch (error) {
        console.error('Error al renderizar formulario:', error);
        this.debugInfo += '\nERROR al renderizar formulario: ' + error.message;
        this.error = 'Error al mostrar el formulario de pago';
        this.loading = false;
      }
    },
    
    procesarPago(cardFormData) {
      this.debugInfo += '\nProcesando pago...';
      
      return new Promise((resolve, reject) => {
        axios.post('/api/mercadopago/procesar-pago', {
          orden_id: this.ordenId,
          total: this.total,
          payment_data: cardFormData
        })
        .then(response => {
          if (response.data.success) {
            // Detener la animación de carga inmediatamente
            this.loading = false;
            
            this.debugInfo += '\nPago procesado exitosamente';
            // Mostrar información sobre redirección pero no redirigir automáticamente
            this.debugInfo += '\nRedirigiendo a: ' + response.data.redirect_url;
            
            // Mostrar mensaje de éxito
            this.$emit('pago-exitoso', response.data);
            
            // Determinar una URL segura para redirigir
            let redirectUrl = '/cliente/ordenes'; // URL segura por defecto
            
            // Comprobar si hay una redirección exitosa definida
            if (response.data.redirect_success_url) {
              redirectUrl = response.data.redirect_success_url;
            } else if (response.data.redirect_url && !response.data.redirect_url.includes('/error')) {
              redirectUrl = response.data.redirect_url;
            }
            
            // Agregar un botón de redirección con instrucciones claras
            const mensaje = `<div class="alert alert-success">
              <h4>¡Pago procesado exitosamente!</h4>
              <p>Tu pago ha sido procesado correctamente.</p>
              <p class="mb-3"><strong>Número de orden:</strong> #${this.ordenId}</p>
              <p class="text-success"><i class="bi bi-check-circle-fill me-2"></i> El estado de tu orden ha sido actualizado</p>
              <a href="${redirectUrl}" class="btn btn-primary mt-2">Volver a Mis Órdenes</a>
            </div>`;
            
            this.error = null;
            
            // Limpiar el contenedor y mostrar el mensaje de éxito
            const container = document.getElementById('cardPaymentBrick_container');
            if (container) {
              // Eliminar cualquier contenido previo
              container.innerHTML = '';
              // Insertar el mensaje de éxito
              container.innerHTML = mensaje;
            }
            
            resolve();
          } else {
            // Mostrar error en la página en vez de redirigir
            this.debugInfo += '\nError al procesar pago: ' + response.data.message;
            this.error = 'Error en el pago: ' + (response.data.message || 'Error desconocido');
            this.loading = false;
            reject(response.data.message);
          }
        })
        .catch(error => {
          console.error('Error en la petición:', error);
          
          // Extraer la información más detallada del error
          let mensajeError = error.message;
          let statusCode = error.response?.status || '';
          let responseData = '';
          
          try {
            if (error.response?.data) {
              if (typeof error.response.data === 'string') {
                responseData = error.response.data;
              } else {
                responseData = JSON.stringify(error.response.data);
              }
            }
          } catch (e) {
            responseData = 'No se pudo parsear la respuesta';
          }
          
          this.debugInfo += '\nERROR en la petición: ' + mensajeError;
          this.debugInfo += statusCode ? '\nCódigo de error: ' + statusCode : '';
          this.debugInfo += responseData ? '\nRespuesta: ' + responseData : '';
          
          // Mostrar detalles del error para ayudar al diagnóstico
          this.error = `Error al procesar el pago ${statusCode ? '(Código ' + statusCode + ')' : ''}: 
            ${mensajeError}. Por favor, intenta nuevamente o contacta a soporte.`;
          
          this.loading = false;
          reject(error);
        });
      });
    },
    
    formatPrice(price) {
      return parseFloat(price).toFixed(2);
    }
  }
};
</script>

<style scoped>
.mercadopago-container {
  max-width: 600px;
  margin: 0 auto;
}
</style>
