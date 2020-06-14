require('./bootstrap');

import Vue from 'vue';

import VueRouter from 'vue-router';
import routes from './router/routes';
Vue.use(VueRouter);

import VueAxios from 'vue-axios';
import axios from 'axios';
Vue.use(VueAxios, axios);

// App
import App from './views/App';

const router = new VueRouter({routes, mode: 'history'});
new Vue(Vue.util.extend({ router }, App)).$mount('#app');
