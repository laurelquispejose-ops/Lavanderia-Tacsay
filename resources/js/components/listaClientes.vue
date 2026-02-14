<template>
    <div class="max-w-4xl mx-auto">
        <h2 class="text-2xl font-bold mb-4">Lista de Clientes</h2>

        <!-- Barra de búsqueda -->
        <input
            v-model="busqueda"
            type="text"
            placeholder="Buscar por nombre, correo, etc."
            class="w-100 p-2 mb-4 border rounded shadow-sm"
        />

        <!-- Lista de clientes -->
        <table class="table table-striped shadow rounded-lg w-100">
            <thead>
                <tr class="bg-gray-100 text-left">
                    <th class="p-2">Nombre</th>
                    <th class="p-2">Correo</th>
                    <th class="p-2">Teléfono</th>
                    <th class="p-2">Dirección</th>
                </tr>
            </thead>
            <tbody>
                <tr
                    v-for="cliente in clientesFiltrados"
                    :key="cliente.id"
                    class="border-t hover:bg-gray-50"
                >
                    <td class="p-2">{{ cliente.nombre }}</td>
                    <td class="p-2">{{ cliente.correoElectronico }}</td>
                    <td class="p-2">{{ cliente.telefono }}</td>
                    <td class="p-2">{{ cliente.direccion }}</td>
                </tr>
                <tr v-if="clientesFiltrados.length === 0">
                    <td colspan="3" class="text-center p-4 text-gray-400">
                        No se encontraron resultados
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
export default {
    data() {
        return {
            clientes: [],
            busqueda: "",
        };
    },
    computed: {
        clientesFiltrados() {
            const filtro = this.busqueda.toLowerCase();
            return this.clientes.filter(
                (cliente) =>
                    cliente.nombre.toLowerCase().includes(filtro) ||
                    cliente.correoElectronico.toLowerCase().includes(filtro) ||
                    cliente.telefono.toLowerCase().includes(filtro) ||
                    cliente.direccion.toLowerCase().includes(filtro)
            );
        },
    },
    mounted() {
        this.cargarClientes();
    },
    methods: {
        async cargarClientes() {
            try {
                const response = await fetch("/api/clientes");
                const data = await response.json();
                this.clientes = data;
            } catch (error) {
                console.error("Error al cargar los clientes:", error);
            }
        },
    },
};
</script>

<style scoped>
input:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 1px #3b82f6;
}
</style>
