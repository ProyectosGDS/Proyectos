<script setup>
import { ref } from 'vue';
import { onClickOutside } from '@vueuse/core'
import { useAuthStore } from '../stores/auth'
import UserPhoto from './UserPhoto.vue'

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
    <div @click="open = !open " ref="target" v-bind="$attrs" class="bg-color-1 text-white px-3 rounded-lg py-2">
        
        <div class="flex items-center space-x-3 cursor-pointer">

            <UserPhoto :user="auth.user" class="h-10 w-10 cursor-pointer" />
            
            <div class="font-bold text-blue-muni text-xs text-center hidden md:block uppercase">
                <p>{{ `${auth.user.nombre }` }}</p>
                <p>{{ auth.user?.perfil ?? '' }}</p>
                <p>{{ auth.user?.dependencia?.nombre ?? '' }}</p>
            </div>
        </div>

        <Transition>
            <div v-show="open"
                class="absolute w-56 px-5 py-3 bg-white rounded shadow-lg border-2 mt-5 right-1 z-10 text-blue-muni text-color-1">
                <ul class="space-y-3">
                    <li class="font-medium">
                        <a href="/plataforma-gds/profile" class="flex gap-2 items-center transform transition-colors duration-200 border-r-4 border-transparent hover:border-blue-700">
                            <Icon icon="far fa-user" />
                            Perfil
                        </a>
                    </li>
                    <!-- <li class="font-medium">
                        <a href="#" @click="openModalHelp = true" class="flex items-center transform transition-colors duration-200 border-r-4 border-transparent hover:border-blue-700">
                            <div class="mr-3">
                                <i class="fas fa-gears"></i>
                            </div>
                            Ayuda ?
                        </a>
                    </li>
                    <hr> -->
                    <li @click="auth.logout()" class="font-medium cursor-pointer">
                        <div class="flex gap-2 items-center transform transition-colors duration-200 border-r-4 border-transparent hover:border-red-600" >
                            <Icon icon="fas fa-arrow-right-from-bracket" class="text-red-500" />
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