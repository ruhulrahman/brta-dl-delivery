<template>
  <div class="main-wrapper" :class="{ closeSidebar: closeSidebar}">
    <div class="sidebar-section sidebarHide desktop-hidden d-none d-md-block">
      <Sidebar :layout="'designer'"></Sidebar>
    </div>
    <div class="main-content-section containerFullWidth">
      <Header :layout="'designer'"></Header>
      <div class="main-content-wrapper">
        <router-view></router-view>
      </div>
      <Footer></Footer>
    </div>
  </div>
</template>
<script>
import RestApi, { baseURL } from '@/config'
import Sidebar from '../components/Sidebar.vue'
import Header from '../components/Header.vue'
// import Footer from '../components/Footer.vue'
import { EventBus } from '@/EventBusLayout'
export default {
  name: 'MainLayout',
  data () {
    return {
      closeSidebar: false
    }
  },
  created () {
    EventBus.$on('toggleNav', this.checkSidebarToggleValue)
    this.getAuthUserData()
    // this.makeEncryptAllBookContent()
  },
  components: {
    Sidebar,
    Header
    // Footer
  },
  methods: {
    checkSidebarToggleValue (param) {
      this.closeSidebar = param
    },
    async getAuthUserData () {
      this.loading = true
      const result = await RestApi.getData(baseURL, 'api/v1/admin/ajax/get_auth_user')
      this.loading = false
      if (result.success) {
        this.$store.dispatch('Auth/updateAuthUser', result.auth_user)
        this.$store.dispatch('Auth/updateUserPermissions', result.user_permissions)
      }
    }
    // async makeEncryptAllBookContent () {
    //   this.loading = true
    //   await RestApi.getData(baseURL, 'api/v1/admin/ajax/make_encrypt_all_book_contents')
    // }
  }
}
</script>
<style>
.main-wrapper .sidebar-section .sidebar-wrapper .sidebar-navigation ul li a {
    padding: 10px 0 10px 12px!important;
}
.main-wrapper .sidebar-section .sidebar-wrapper .sidebar-navigation > ul {
    margin-bottom: 0px!important;
}
.vs__search, .vs__search:focus {
    font-size: 13px !important;
}
</style>
