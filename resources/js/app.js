require('./bootstrap');
window.Vue = require('vue').default
import { BootstrapVue, IconsPlugin } from 'bootstrap-vue'
import 'bootstrap/dist/css/bootstrap.css'
import 'bootstrap-vue/dist/bootstrap-vue.css'

import vSelect from 'vue-select'
import 'vue-select/dist/vue-select.css';

import MSelect from "./src/select/MSelect";

import VueSweetalert2 from 'vue-sweetalert2'
import 'sweetalert2/dist/sweetalert2.min.css'

import { ValidationProvider } from "vee-validate/dist/vee-validate.full.esm"
import { ValidationObserver } from 'vee-validate'

import storage from "./store/index"
import * as boston from "./helper/boston"
import "./helper/config"
import "./helper/BostonMixin"

import VueToast from 'vue-toast-notification';
import 'vue-toast-notification/dist/theme-sugar.css';
Vue.use(VueToast, {
    position: 'top-left',
    duration: 8000
});


Vue.component('ValidationProvider', ValidationProvider)
Vue.component('ValidationObserver', ValidationObserver)
Vue.component('v-select', vSelect)

Vue.use(BootstrapVue)
Vue.use(IconsPlugin)
Vue.use(VueSweetalert2)
Vue.use(MSelect);

Vue.config.productionTip = false
Vue.prototype.$boston = boston

axios.defaults.baseURL = window.origin

import "./src/vue_component"

const app = new Vue({
    el: '#app',
    store: storage
});
