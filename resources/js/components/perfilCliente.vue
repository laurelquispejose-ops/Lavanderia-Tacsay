<template>
    <div class="container mt-4">
        <div class="row mb-4">
            <div class="col-md-8">
                <h2 class="mb-0"><i class="bi bi-person-badge me-2"></i>Mi Perfil</h2>
                <p class="text-muted">Administra tu información personal y preferencias</p>
            </div>
        </div>

        <div class="row">
            <!-- Panel lateral con foto y estadísticas -->
            <div class="col-md-4 mb-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body text-center py-5">
                        <div class="avatar-container mb-3 mx-auto">
                            <div class="avatar bg-primary rounded-circle d-flex align-items-center justify-content-center">
                                <span v-if="cliente.nombre" class="display-4 text-white">{{ cliente.nombre.charAt(0) }}</span>
                                <i v-else class="bi bi-person display-4 text-white"></i>
                            </div>
                        </div>
                        <h4 class="mb-1">{{ cliente.nombre || 'Cliente' }} {{ cliente.apellido || '' }}</h4>
                        <p class="text-muted">{{ cliente.correoElectronico || 'Sin correo registrado' }}</p>
                        <p v-if="cliente.dni" class="mb-0">
                            <span class="badge bg-light text-dark">DNI: {{ cliente.dni }}</span>
                        </p>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Estadísticas</h5>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Total de órdenes:</span>
                            <span class="badge bg-primary rounded-pill">{{ estadisticas.total || 0 }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Órdenes pendientes:</span>
                            <span class="badge bg-warning text-dark rounded-pill">{{ estadisticas.pendientes || 0 }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Órdenes en proceso:</span>
                            <span class="badge bg-info text-dark rounded-pill">{{ estadisticas.enProceso || 0 }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Órdenes finalizadas:</span>
                            <span class="badge bg-success rounded-pill">{{ estadisticas.finalizadas || 0 }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Formulario de perfil -->
            <div class="col-md-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h4 class="card-title mb-4">Información Personal</h4>

                        <div v-if="mensaje" :class="['alert', mensaje.tipo]" role="alert">
                            {{ mensaje.texto }}
                        </div>

                        <form @submit.prevent="actualizarPerfil">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="nombre" class="form-label">Nombre</label>
                                    <input 
                                        type="text" 
                                        class="form-control" 
                                        id="nombre" 
                                        v-model="formData.nombre"
                                        required
                                    >
                                </div>
                                <div class="col-md-6">
                                    <label for="apellido" class="form-label">Apellido</label>
                                    <input 
                                        type="text" 
                                        class="form-control" 
                                        id="apellido" 
                                        v-model="formData.apellido"
                                        required
                                    >
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Correo Electrónico</label>
                                <input 
                                    type="email" 
                                    class="form-control" 
                                    id="email" 
                                    v-model="formData.email"
                                    required
                                >
                            </div>

                            <div class="mb-3">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <input 
                                    type="tel" 
                                    class="form-control" 
                                    id="telefono" 
                                    v-model="formData.telefono"
                                >
                            </div>

                            <div class="mb-3">
                                <label for="direccion" class="form-label">Dirección</label>
                                <textarea 
                                    class="form-control" 
                                    id="direccion" 
                                    v-model="formData.direccion"
                                    rows="2"
                                ></textarea>
                            </div>

                            <hr class="my-4">

                            <h5 class="mb-3">Cambiar Contraseña</h5>
                            <p class="text-muted small mb-3">Deja estos campos en blanco si no deseas cambiar tu contraseña</p>

                            <div class="mb-3">
                                <label for="password_actual" class="form-label">Contraseña Actual</label>
                                <input 
                                    type="password" 
                                    class="form-control" 
                                    id="password_actual" 
                                    v-model="formData.password_actual"
                                >
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="password_nuevo" class="form-label">Nueva Contraseña</label>
                                    <input 
                                        type="password" 
                                        class="form-control" 
                                        id="password_nuevo" 
                                        v-model="formData.password_nuevo"
                                    >
                                </div>
                                <div class="col-md-6">
                                    <label for="password_confirmacion" class="form-label">Confirmar Contraseña</label>
                                    <input 
                                        type="password" 
                                        class="form-control" 
                                        id="password_confirmacion" 
                                        v-model="formData.password_confirmacion"
                                    >
                                </div>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                                <button type="button" class="btn btn-outline-secondary me-md-2" @click="resetForm">Cancelar</button>
                                <button type="submit" class="btn btn-primary" :disabled="cargando">
                                    <span v-if="cargando" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                                    Guardar Cambios
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    name: "PerfilCliente",
    data() {
        return {
            cliente: {},
            formData: {
                nombre: '',
                apellido: '',
                email: '',
                telefono: '',
                direccion: '',
                password_actual: '',
                password_nuevo: '',
                password_confirmacion: ''
            },
            estadisticas: {
                total: 0,
                pendientes: 0,
                enProceso: 0,
                finalizadas: 0
            },
            cargando: false,
            mensaje: null
        };
    },
    mounted() {
        this.cargarDatosCliente();
        this.cargarEstadisticas();
    },
    methods: {
        async cargarDatosCliente() {
            try {
                // Usa el endpoint del cliente definido en rutas
                const response = await axios.get('/cliente/api/user');
                this.cliente = response.data || {};

                // Inicializar el formulario con los campos reales de la tabla clientes
                this.formData.nombre = this.cliente.nombre || '';
                this.formData.apellido = this.cliente.apellido || '';
                this.formData.email = this.cliente.correoElectronico || '';
                this.formData.telefono = this.cliente.telefono || '';
                this.formData.direccion = this.cliente.direccion || '';

                // Si no hay apellido guardado pero el nombre completo contiene apellidos,
                // intentamos separar: últimos 2 tokens como apellidos.
                if ((!this.formData.apellido || this.formData.apellido.trim() === '') && this.formData.nombre) {
                    const tokens = this.formData.nombre.trim().split(/\s+/);
                    if (tokens.length >= 2) {
                        const ap = tokens.slice(-2).join(' ');
                        const nom = tokens.slice(0, Math.max(1, tokens.length - 2)).join(' ');
                        this.formData.nombre = nom;
                        this.formData.apellido = ap;
                    }
                }

                console.log('Datos del cliente cargados:', this.cliente);
            } catch (error) {
                console.error('Error al cargar los datos del cliente:', error);
                this.mostrarMensaje('No se pudieron cargar los datos del perfil.', 'alert-danger');
            }
        },
        
        async cargarEstadisticas() {
            try {
                const response = await axios.get('/api/cliente/ordenes/estadisticas');
                this.estadisticas = response.data;
                console.log('Estadísticas cargadas:', this.estadisticas);
            } catch (error) {
                console.error('Error al cargar estadísticas:', error);
                // No mostramos mensaje de error para las estadísticas
            }
        },
        
        async actualizarPerfil() {
            // Validar contraseñas si se está intentando cambiar
            if (this.formData.password_nuevo || this.formData.password_confirmacion) {
                if (!this.formData.password_actual) {
                    this.mostrarMensaje('Debes ingresar tu contraseña actual para cambiarla.', 'alert-danger');
                    return;
                }
                
                if (this.formData.password_nuevo !== this.formData.password_confirmacion) {
                    this.mostrarMensaje('Las contraseñas nuevas no coinciden.', 'alert-danger');
                    return;
                }
                
                if (this.formData.password_nuevo.length < 8) {
                    this.mostrarMensaje('La contraseña debe tener al menos 8 caracteres.', 'alert-danger');
                    return;
                }
            }
            
            this.cargando = true;
            
            try {
                const datosActualizados = {
                    nombre: this.formData.nombre,
                    apellido: this.formData.apellido,
                    email: this.formData.email,
                    telefono: this.formData.telefono,
                    direccion: this.formData.direccion
                };
                
                // Solo incluir datos de contraseña si se está intentando cambiar
                if (this.formData.password_actual && this.formData.password_nuevo) {
                    datosActualizados.password_actual = this.formData.password_actual;
                    datosActualizados.password_nuevo = this.formData.password_nuevo;
                }
                
                const response = await axios.post('/api/cliente/perfil/actualizar', datosActualizados);
                
                if (response.data.success) {
                    this.mostrarMensaje('Perfil actualizado correctamente.', 'alert-success');
                    // Recargar datos del cliente
                    await this.cargarDatosCliente();
                    
                    // Limpiar campos de contraseña
                    this.formData.password_actual = '';
                    this.formData.password_nuevo = '';
                    this.formData.password_confirmacion = '';
                } else {
                    this.mostrarMensaje(response.data.message || 'Error al actualizar el perfil.', 'alert-danger');
                }
            } catch (error) {
                console.error('Error al actualizar el perfil:', error);
                
                if (error.response && error.response.data && error.response.data.message) {
                    this.mostrarMensaje(error.response.data.message, 'alert-danger');
                } else {
                    this.mostrarMensaje('Error al actualizar el perfil. Intenta nuevamente.', 'alert-danger');
                }
            } finally {
                this.cargando = false;
            }
        },
        
        resetForm() {
            // Restablecer el formulario a los valores originales del cliente
            this.formData.nombre = this.cliente.nombre || '';
            this.formData.apellido = this.cliente.apellido || '';
            this.formData.email = this.cliente.correoElectronico || '';
            this.formData.telefono = this.cliente.telefono || '';
            this.formData.direccion = this.cliente.direccion || '';
            this.formData.password_actual = '';
            this.formData.password_nuevo = '';
            this.formData.password_confirmacion = '';
            
            // Limpiar mensaje
            this.mensaje = null;
        },
        
        mostrarMensaje(texto, tipo = 'alert-info') {
            this.mensaje = { texto, tipo };
            
            // Hacer scroll al inicio del formulario para mostrar el mensaje
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
            
            // Limpiar el mensaje después de 5 segundos
            setTimeout(() => {
                if (this.mensaje && this.mensaje.texto === texto) {
                    this.mensaje = null;
                }
            }, 5000);
        }
    }
};
</script>

<style scoped>
.avatar-container {
    width: 100px;
    height: 100px;
}

.avatar {
    width: 100%;
    height: 100%;
    font-size: 2.5rem;
}

.card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
}

.form-control:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.btn-primary {
    padding: 0.5rem 1.5rem;
    font-weight: 500;
}

.alert {
    border-radius: 0.375rem;
    padding: 1rem;
}
</style>
