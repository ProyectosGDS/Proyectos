<script setup>
import { ref } from 'vue';
import { onClickOutside } from '@vueuse/core'
import { useAuthStore } from '../stores/auth'
import UserPhoto from './UserPhoto.vue'
import { RouterLink } from 'vue-router';

    const auth = useAuthStore()

    const openModalHelp = ref(false);
    const open = ref(false);
    const target = ref(null)


    onClickOutside(target, (event) => open.value = false)

    defineOptions({
        inheritAttrs: false
    })
    
</script>

 <template>
    <div @click="open = !open " ref="target" v-bind="$attrs" class="">
        
        <div class="flex items-center space-x-3 cursor-pointer">

            <!-- <UserPhoto :user="auth.user" class="h-14 w-14 cursor-pointer" /> -->
             <Icon icon="fas fa-user" class="p-3 text-xl bg-gray-200 rounded-full" />
            
            <div class="font-bold text-blue-muni text-xs text-center hidden md:block">
                <p>{{ `${auth.user.primer_nombre} ${auth.user.primer_apellido}` }}</p>
                <p>{{ auth.user?.roles ? auth.user?.roles[0]?.nombre : '' }}</p>
            </div>
        </div>

        <Transition>
            <div v-show="open"
                class="absolute w-56 px-5 py-3 bg-white rounded shadow-lg border-2 mt-5 right-1 z-10 text-blue-muni">
                <ul class="space-y-3">
                    <li class="font-medium">
                        <a href="../../Ppersonal.php" class="flex items-center transform transition-colors duration-200 border-r-4 border-transparent hover:border-blue-700">
                            <div class="mr-3">
                                <i class="fas fa-address-card"></i>
                            </div>
                            Perfil
                        </a>
                    </li>
                    <li class="font-medium">
                        <RouterLink :to="{name : 'Catalogos'}" class="flex items-center transform transition-colors duration-200 border-r-4 border-transparent hover:border-blue-700">
                            <div class="mr-3">
                                <i class="fas fa-gears"></i>
                            </div>
                            Catalogos
                        </RouterLink>
                    </li>
                    <li class="font-medium">
                        <a href="#" @click="openModalHelp = true" class="flex items-center transform transition-colors duration-200 border-r-4 border-transparent hover:border-blue-700">
                            <div class="mr-3">
                                <i class="fas fa-gears"></i>
                            </div>
                            Ayuda ?
                        </a>
                    </li>
                    <hr>
                    <li @click="auth.logout" class="font-medium cursor-pointer">
                        <div class="flex items-center transform transition-colors duration-200 border-r-4 border-transparent hover:border-red-600" >
                            <div class="mr-3 text-red-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                            </div>
                            Cerrar sesión
                        </div>
                    </li>
                </ul>
            </div>
        </Transition>

    </div>
    <Modal :open="openModalHelp" title="Manual de usuario del módulo" class="w-1/3" icon="fas fa-file-pdf" >
        
        <iframe src="/public/docs/help.pdf" class="w-full h-[42rem]"></iframe>

        <template #footer>
            <Button text="Cerrar" class="btn-danger shadow-red-800" icon="fas fa-xmark"  @click="openModalHelp = false" />
        </template>
    </Modal>
 </template>

<style scoped>
    .v-enter-active, .v-leave-active {
    transition: opacity 0.5s ease-in-out;
    }

    .v-enter-from, .v-leave-to {
    opacity: 0;
    }

</style>