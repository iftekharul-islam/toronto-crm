<template>
  <div class="order-details-box ">
    <div class="box-header">
      <p class="fw-bold text-light-black fs-20 mb-0">Notes</p>
    </div>
    <div class="box-body bg-white">
      <div class="note-chat">

        <div class="chat-item" v-for="noteItem, ni in notes" :key="noteItem.key + ni">
          <div class="chat-name d-flex align-items-center" v-if="noteItem.note">
            <img v-if="noteItem.user && noteItem.user.thumb" :src="noteItem.user.thumb" alt="boston chat image" class="img-fluid"/>
            <img v-else src="/img/user.png" alt="boston chat image" class="img-fluid"/>
            <div class="ms-3">
              <p class="text-600 mb-0">{{ noteItem.user ? noteItem.user.name : '-' }}</p>
              {{ noteItem.title }}
            </div>
          </div>
          <div class="d-inline-block message" v-if="noteItem.note">
                <span class="date-sm">{{ noteItem.date | momentTime }}</span>
                <p class="mb-0" v-if="noteItem.note" v-html="noteItem.note"></p>
                <p class="mb-0" v-else>-</p>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>
<script>
    export default{
        props:{
            order: []
        },
        data:() => ({
          orderData: [],
          notes: []
        }),
        created() {
            this.initNotes(this.order);
            this.$root.$on('wk_update', (res) => {
              this.initNotes(res);
            });
        },
        methods: {
          initNotes(order) {
              this.notes = [];
              this.orderData = order;

              if (order.provider_service && order.provider_service.note != "" && order.provider_service.note != null) {
                this.notes.push({
                    key: 'provided_service',
                    title: "Provided Services",
                    note: order.provider_service.note,
                    user: order.user,
                    date: order.provider_service.created_at
                });
              }

              if ( order.inspection ) {
                this.notes.push({
                    key: 'inspection_schedule',
                    title: "Inspection Schedule",
                    note: order.inspection.note,
                    user: order.inspection.create_by,
                    date: order.inspection?.note_time
                });
              }

              if ( order.initial_review ) {
                this.notes.push({
                    key: 'initial_review',
                    title: "Initial Review",
                    note: order.initial_review.note,
                    user: order.initial_review.create_by,
                    date: order.inspection?.note_time
                });
              }

              if ( order.analysis ) {
                  if (order.analysis.note) {
                    this.notes.push({
                        key: 'analysis',
                        title: "Report Analysys",
                        note: order.analysis.note,
                        user: order.analysis.update_by,
                        date: order.analysis?.note_time
                    });
                  }
                  if (order.analysis.rewrite_note) {
                    this.notes.push({
                        key: 'analysis_rewrite',
                        title: "Report Analysys Rewrite",
                        note: order.analysis.rewrite_note,
                        user: order.analysis.update_by,
                        date: order.analysis?.note_time
                    });
                  }
              }

            if (order.report_rewrite) {
                    this.notes.push({
                        key: 'report_rewrite',
                        title: "Report Rewrites",
                        note: order.report_rewrite.note,
                        user: order.report_rewrite.update_by,
                        date: order.report_rewrite?.note_time
                    });
              }

              if (order.quality_assurance) {
                    this.notes.push({
                        key: 'quality_assurance',
                        title: "Quality Assurance",
                        note: order.quality_assurance.note,
                        user: order.quality_assurance.updated_by,
                        date: order.quality_assurance?.note_time
                    });
              }
          }
        }
    }
</script>

<style lang="scss" scoped>
    .chat-item {
        margin-bottom: 15px!important;
    }
    .chat-item .date-sm {
        font-size: 12px;
    }
</style>
