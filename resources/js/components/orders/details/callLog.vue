<template>
  <div class="order-details-box bg-white call-log-box">
        <div class="box-header">
          <p class="fw-bold text-light-black fs-20 mb-0">Call log <span class="fs-15" v-if="isCompleted">(Completed)</span></p>
            <a @click="isModal = true" class="d-inline-flex edit add-call align-items-center fw-bold">Add call log</a>
        </div>
      <div class="box-body" v-if="logs.length">
      <div class="col-log">
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
            <tr>
              <th scope="col">SL</th>
              <th scope="col">Caller name</th>
              <th scope="col">Call date & times</th>
              <th scope="col">Message</th>
              <th scope="col"></th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="(log, index) in logs" :key="index">
              <td>{{ index + 1}}.</td>
              <td>{{ log.caller.name }}</td>
              <td>{{ log.created_at }}</td>
              <td class="log-message" v-html="log.message"></td>
              <td>
                <span class="icon-messages2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></span>
              </td>
            </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
      <div class="text-center mt-3 mb-3" v-else>
          No call added yet !
      </div>
    <add-call-log :showModal="isModal" :order="this.order" :users="this.users" :isCompleted="isCompleted"></add-call-log>
  </div>
</template>
<script>
export default {
    name: 'callLog',
    props: [ 'order', 'users' ],
    data: () => ({
        orderData: {},
        isModal: false,
        logs: [],
        id: null,
        isCompleted: false
    }),
    created() {
        let order = this.order;
        this.fetchData(order);
        this.$root.$on('update_modal', () => {
            this.isModal = false
        })
        this.$root.$on('call_log_update', (res) => {
            this.isModal = false
            this.fetchData(res);
        });
    },
    methods: {
        fetchData(order) {
            this.orderData = order
            this.id = this.orderData.id
            this.logs = !_.isEmpty(this.orderData.call_log) ? this.orderData.call_log : []
            if(this.logs.length){
                this.logs.forEach((log, index) => {
                    if(log.status){
                        this.isCompleted = true
                    }
                })
            }
        },

    }
}
</script>
