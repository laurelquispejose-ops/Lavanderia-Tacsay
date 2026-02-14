import './bootstrap';
import '../scss/app.scss';
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';


import { createApp } from 'vue'
import home from './components/home.vue';
import navBar from './components/navBar.vue'
import loginForm from './components/login.vue'
import registerForm from './components/registerCliente.vue';
import registerEmpleado from './components/registerEmpleado.vue';
import ordenform from './pages/ordenform.vue';
import ordenesPage from './pages/ordenesPage.vue';
import dashboard from './Pages/dashboard.vue';
import clienteOrdenes from './components/clienteOrdenes.vue';
import perfilCliente from './components/perfilCliente.vue';
import adminClientes from './pages/adminClientes.vue';
import MercadoPagoForm from './components/MercadoPagoForm.vue';
import PagoOrden from './components/PagoOrden.vue';
import AdminEmpleados from './components/AdminEmpleados.vue';
import EmpleadosList from './components/EmpleadosList.vue';


// Crear app para navbar si existe el div
const navbarElement = document.getElementById('navbar');
if (navbarElement) {
    createApp(navBar).mount('#navbar');
}

// Crear app para el contenido si existe el div
const appElement = document.getElementById('app');
if (appElement) {
    const app = createApp({});

    app.component('login-form', loginForm)
        .component('register-form', registerForm)
        .component('register-empleado', registerEmpleado)
        .component('home', home)
        .component('orden-form', ordenform)
        .component('ordenes-page', ordenesPage)
        .component('dashboard', dashboard)
        .component('cliente_ordenes', clienteOrdenes)
        .component('perfil_cliente', perfilCliente)
        .component('admin-clientes', adminClientes)
        .component('pago_orden', PagoOrden)
        .component('mercado-pago-form', MercadoPagoForm)
        .component('admin-empleados', AdminEmpleados)
        .component('empleados-list', EmpleadosList)
        

    app.mount('#app');
}
