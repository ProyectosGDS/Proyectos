import './assets/main.css'

import { createApp } from 'vue'
import { createPinia } from 'pinia'


import App from './App.vue'
import router from './router'


import { library } from '@fortawesome/fontawesome-svg-core'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { fas } from '@fortawesome/free-solid-svg-icons'
import { fab } from '@fortawesome/free-brands-svg-icons'
import { far } from '@fortawesome/free-regular-svg-icons'
import VueDatePicker from '@vuepic/vue-datepicker';


// COMPONENTES INSTANCIADOS DE FORMA GLOBAL
import Button from './components/Button.vue'
import Modal from './components/Modal.vue'
import Input from './components/Input.vue'
import Card from './components/Card.vue'
import ValidateErrors from './components/ValidateErrors.vue'


import axios from 'axios'

library.add(fas)
library.add(fab)
library.add(far)

axios.defaults.baseURL = import.meta.env.VITE_MY_API_URL_BASE
axios.defaults.withCredentials = true
axios.defaults.headers.common['app'] = import.meta.env.VITE_MY_APPNAME

const app = createApp(App)
app.use(createPinia())
app.use(router)

app.component('Icon', FontAwesomeIcon)
.component('Modal', Modal)
.component('Button', Button)
.component('Input', Input)
.component('Card', Card)
.component('Validate-Errors', ValidateErrors)
.component('Date-Picker', VueDatePicker)

app.mount('#app')



