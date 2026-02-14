<template>
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="card-title mb-0">Exportar Órdenes</h5>
        </div>
        <div class="card-body">
            <!-- Filtros -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Estado de órdenes</label>
                    <select v-model="filtroEstado" class="form-select">
                        <option value="todos">Todos los estados</option>
                        <option value="pendiente">Pendientes</option>
                        <option value="en proceso lavado">En proceso de lavado</option>
                        <option value="en proceso planchado">En proceso de planchado</option>
                        <option value="finalizado">Finalizadas</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Rango de fechas</label>
                    <div class="d-flex">
                        <input 
                            type="date" 
                            class="form-control me-2" 
                            v-model="fechaInicio" 
                            placeholder="Fecha inicio"
                        >
                        <input 
                            type="date" 
                            class="form-control" 
                            v-model="fechaFin" 
                            placeholder="Fecha fin"
                        >
                    </div>
                </div>
            </div>

            <!-- Notificación de estado -->
            <div v-if="notificacionVisible" :class="['alert', notificacionTipo]" role="alert">
                {{ notificacionMensaje }}
                <button type="button" class="btn-close float-end" @click="notificacionVisible = false"></button>
            </div>

            <!-- Botones de exportación -->
            <div class="d-flex justify-content-end gap-2">
                <button 
                    class="btn btn-success" 
                    @click="exportarExcel"
                    :disabled="cargandoExcel"
                >
                    <i class="bi bi-file-earmark-excel me-1"></i>
                    {{ cargandoExcel ? 'Generando CSV...' : 'Exportar a CSV' }}
                </button>
                <button 
                    class="btn btn-danger" 
                    @click="exportarPDF"
                    :disabled="cargandoPDF"
                >
                    <i class="bi bi-file-earmark-pdf me-1"></i>
                    {{ cargandoPDF ? 'Generando PDF...' : 'Exportar a PDF' }}
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';

// Estado de filtros
const filtroEstado = ref('todos');
const fechaInicio = ref('');
const fechaFin = ref('');

// Estado de carga
const cargandoExcel = ref(false);
const cargandoPDF = ref(false);

// Estado de notificación
const notificacionVisible = ref(false);
const notificacionMensaje = ref('');
const notificacionTipo = ref('alert-success');

// Mostrar notificación
function mostrarNotificacion(mensaje, tipo = 'success') {
    notificacionMensaje.value = mensaje;
    notificacionTipo.value = tipo === 'success' ? 'alert-success' : 'alert-danger';
    notificacionVisible.value = true;
    
    // Auto ocultar después de 5 segundos
    setTimeout(() => {
        notificacionVisible.value = false;
    }, 5000);
}

// Exportar a Excel (CSV)
async function exportarExcel() {
    try {
        cargandoExcel.value = true;
        
        // Validar fechas si ambas están presentes
        if (fechaInicio.value && fechaFin.value) {
            const inicio = new Date(fechaInicio.value);
            const fin = new Date(fechaFin.value);
            
            if (inicio > fin) {
                mostrarNotificacion('La fecha de inicio no puede ser posterior a la fecha fin', 'error');
                return;
            }
        }
        
        // Construir parámetros de consulta
        const params = new URLSearchParams();
        if (filtroEstado.value !== 'todos') {
            params.append('estado', filtroEstado.value);
        }
        if (fechaInicio.value) {
            params.append('fecha_inicio', fechaInicio.value);
        }
        if (fechaFin.value) {
            params.append('fecha_fin', fechaFin.value);
        }
        
        // Realizar la petición con responseType blob para descargar el archivo
        const response = await axios.get(`/api/exportar/ordenes/excel?${params.toString()}`, {
            responseType: 'blob'
        });
        
        // Verificar el tipo de contenido
        const contentType = response.headers['content-type'];
        if (contentType && contentType.includes('text/html')) {
            // Si el servidor devuelve HTML en lugar de un archivo, probablemente es un error
            throw new Error('El servidor devolvió una respuesta HTML en lugar de un archivo CSV');
        }
        
        // Crear un objeto URL para el blob
        const url = window.URL.createObjectURL(new Blob([response.data]));
        
        // Crear un elemento <a> para descargar el archivo
        const link = document.createElement('a');
        link.href = url;
        
        // Obtener nombre de archivo de las cabeceras o usar uno predeterminado
        const contentDisposition = response.headers['content-disposition'];
        let filename = 'ordenes.csv';
        if (contentDisposition) {
            const filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
            const matches = filenameRegex.exec(contentDisposition);
            if (matches != null && matches[1]) { 
                filename = matches[1].replace(/['"]/g, '');
            }
        }
        
        link.setAttribute('download', filename);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        mostrarNotificacion('Archivo CSV generado correctamente', 'success');
    } catch (error) {
        console.error('Error al exportar a CSV:', error);
        mostrarNotificacion(
            'Error al generar el archivo CSV. ' + 
            (error.response?.status === 500 ? 'Error interno del servidor.' : error.message || 'Intente nuevamente.'), 
            'error'
        );
    } finally {
        cargandoExcel.value = false;
    }
}

// Exportar a PDF
async function exportarPDF() {
    try {
        cargandoPDF.value = true;
        
        // Validar fechas si ambas están presentes
        if (fechaInicio.value && fechaFin.value) {
            const inicio = new Date(fechaInicio.value);
            const fin = new Date(fechaFin.value);
            
            if (inicio > fin) {
                mostrarNotificacion('La fecha de inicio no puede ser posterior a la fecha fin', 'error');
                return;
            }
        }
        
        // Construir parámetros de consulta
        const params = new URLSearchParams();
        if (filtroEstado.value !== 'todos') {
            params.append('estado', filtroEstado.value);
        }
        if (fechaInicio.value) {
            params.append('fecha_inicio', fechaInicio.value);
        }
        if (fechaFin.value) {
            params.append('fecha_fin', fechaFin.value);
        }
        
        // Realizar la petición con responseType blob para descargar el archivo
        const response = await axios.get(`/api/exportar/ordenes/pdf?${params.toString()}`, {
            responseType: 'blob'
        });
        
        // Crear un objeto URL para el blob
        const url = window.URL.createObjectURL(new Blob([response.data]));
        
        // Crear un elemento <a> para descargar el archivo
        const link = document.createElement('a');
        link.href = url;
        
        // Obtener nombre de archivo de las cabeceras o usar uno predeterminado
        const contentDisposition = response.headers['content-disposition'];
        let filename = 'ordenes.pdf';
        if (contentDisposition) {
            const filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
            const matches = filenameRegex.exec(contentDisposition);
            if (matches != null && matches[1]) { 
                filename = matches[1].replace(/['"]/g, '');
            }
        }
        
        link.setAttribute('download', filename);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        mostrarNotificacion('PDF generado correctamente', 'success');
    } catch (error) {
        console.error('Error al exportar a PDF:', error);
        mostrarNotificacion('Error al generar el PDF', 'error');
    } finally {
        cargandoPDF.value = false;
    }
}
</script>

<style scoped>
.card {
    transition: box-shadow 0.3s;
}
.card:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}
.alert {
    margin-top: 1rem;
    margin-bottom: 1rem;
}
</style>
