<template>
  <div class="clients bg-platinum dashboard-space">
    <div class="clients-box clients-box-main bg-white">
      <div class="clients-top d-flex flex-wrap justify-content-between">
        <div class="left d-flex">
          <button v-for="(type, index) in types" :key="index" class="clients-top-btn px-3 h-40" :class="{'active': isActive === type.type}"
                    @click="getType(type.type)">
              {{ type.type }} <span class="ms-3">{{ type.count }}</span>
            </button>
        </div>
        <div class="right d-flex">
          <!-- Loader -->
<!--          <div v-if="loading" class="loader">-->
<!--              <div class="content">-->
<!--                <div class="loading">-->
<!--                    <p>loading</p>-->
<!--                    <span></span>-->
<!--                </div>-->
<!--              </div>-->
<!--          </div>-->
          <input type="text" v-model="searchText" placeholder="Search ..." @keyup="searchClients" class="px-3 bdr-1 br-4 gray-border me-3 h-40">
          <a v-if="canCreate" :href="createRoute" class="button button-primary h-40 py-2 d-flex align-items-center">Add clients</a>
        </div>
      </div>
      <div class="clients-table mt-3">
        <table class="table">
          <thead>
          <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Client type</th>
            <th>Client city</th>
            <th>Address</th>
            <th></th>
          </tr>
          </thead>
          <tbody>

          <tr v-for="client, clientKey in clients.data" :key="clientKey">
            <td><b>{{ client.name }} </b></td>
            <td>
              <div class="client-contact-limit">
                <span class="d-flex" :key="ei" v-for="email, ei in JSON.parse(client.email)">{{ email }}</span>
              </div>
            </td>
            <td>
              <div class="client-contact-limit">
                <!-- <span class="d-flex" :key="pi" v-for="phone, pi in JSON.parse(client.phone)">{{ phone }}</span> -->
                <span class="d-flex phone-number flat">
                  <input @click="selectText(item)" readonly v-for="item, ik in JSON.parse(client.phone)" :key="ik" :value="item"/>
                </span>
              </div>
            </td>
            <td>{{ client.client_type }}</td>
            <td>{{ client.city }}</td>
            <td>{{ client.address }}</td>
            <td>
              <a v-if="canView" class="eye-btn text-light-black cursor-pointer me-3" @click.prevent="showClientDetails(client.id)">
                <span class="icon-eye fs-20"><span class="path1"></span><span class="path2"></span></span>
              </a>
              <a v-if="canDelete" class="eye-btn text-light-black cursor-pointer" @click.prevent="deleteClient(client.id)">
                <span class="icon-trash fs-20"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></span>
              </a>
            </td>
          </tr>
          </tbody>
        </table>
      </div>
      <div class="pagination justify-content-center mgt-32">
        <pagination v-model="page" :edgeNavigation="false" :options="{chunk: 3,theme : 'bootstrap4',texts:{ count: '',first: '',last: '' }}" :records="parseInt(this.clients.total)" :per-page="this.clients.per_page"
                    @paginate="getClients"/>
      </div>
    </div>
  </div>
</template>
<script>
import Pagination from 'vue-pagination-2'
Vue.component('pagination', Pagination)
export default {
  props: {
    showRoute: String,
    createRoute: String,
    deleteRoute: String,
    permissions: Array,
    role: String,
    isOwner: {
      default: false,
      type: Boolean
    }
  },
  data() {
    return {
      clients: {},
      types: [
        {
          'type': 'All',
          'count': 0,
        },
        {
          'type': 'Amc',
          'count': 0,
        },
        {
          'type': 'Lender',
          'count': 0,
        }
      ],
      all: 0,
      amc: 0,
      lender: 0,
      isActive: false,
      currentType: 'all',
      page: 1,
      searchText: '',
      canCreate: false,
      canUpdate: false,
      canDelete: false,
      canView: false,
    }
  },
  created() {
    this.getType('All')
  },
  mounted(){
    this.checkClientPermission()
  },
  methods: {
    checkClientPermission(){
      let that = this;
      let permissions = that.permissions
      if(that.role === 'admin' || that.isOwner ){
        that.canCreate = true
        that.canView = true
        that.canUpdate = true
        that.canDelete = true
      }
      Object.keys(permissions).map(function(key, index) {
        switch (permissions[key]) {
          case 'create.client':
            that.canCreate = true
            break
          case 'view.client':
            that.canView = true
            break
          case 'update.client':
            that.canUpdate = true
            break
          case 'delete.client':
            that.canDelete = true
            break
        }
      });
    },
    searchClients() {
      this.getClients()
    },
    getType(type) {
      this.currentType = type
      this.isActive = type
      this.page = 1
      this.getClients()
    },
    getClients(page = 1) {
      // this.loading = true;
      axios.get(window.origin + '/get-clients/' + this.currentType + '?page=' + page + '&searchKey=' + (this.searchText).trim())
          .then(res => {
            this.clients = res.data.data.clients
            this.all = res.data.data.all
            this.types[0].count = res.data.data.all
            this.types[1].count = res.data.data.amc
            this.types[2].count = res.data.data.lender
          }).catch(err => {
        console.log(err)
      })
    },
    showClientDetails(clientId){
      window.location.href = this.showRoute + '/' + clientId
    },
    deleteClient(clientId){
      swal({
        title: "Are you sure want to delete this client?",
        text: "Please ensure and then confirm!",
        type: "warning",
        showCancelButton: !0,
        confirmButtonText: "Yes, delete",
        cancelButtonText: "No, cancel!",
        reverseButtons: !0
      }).then((result) => {
        if(result.value) {
          axios.delete(this.deleteRoute + '/' + clientId)
              .then(res => {
                this.$swal('Deleted', 'You successfully deleted this client', 'success')
                setTimeout(function (){
                  location.reload()
                },2000);
              }).catch(err => {
            console.log(err)
          })
        }
      })
    }
  }
}
</script>

<style lang="scss" scoped>
.client-contact-limit {
    .phone-number {
      flex-direction: column;
      input {
        padding: 0;
        margin: 0
      }
    }
}
</style>

