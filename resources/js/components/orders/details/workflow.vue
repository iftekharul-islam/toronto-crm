<template>
  <div class="order-details__left col-md-8">
    <!-- Workflow -->
    <div class="order-details-box bg-white h-100">
      <div class="box-header">
        <p class="fw-bold text-light-black fs-20 mb-0">Workflow</p>
      </div>
      <div class="box-body">
        <div class="workflow-content">
          <!--          step list-->
          <div class="list">
            <div class="item" :class="{'complete' : status.orderCreate === 1, 'activeStep' : isActive == 'order-create' }" @click="changeTab('order-create')">
              <span class="ball"><img src="/img/current-white.png" alt="current step boston"></span>
              <p class="mb-0">Order Creation</p>
            </div>
            <div class="item" :class="{'complete' : status.scheduling === 1, 'current' : currentStep('scheduling'), 'activeStep' : isActive == 'scheduling' }" @click="changeTab('scheduling')">
              <span class="ball"><img src="/img/current-white.png" alt="current step boston"></span>
              <p class="mb-0">Scheduling</p>
            </div>
            <div class="item" :class="{'complete' : status.inspection === 1,'current' : currentStep('inspection'), 'activeStep' : isActive == 'inspection'}" @click="changeTab('inspection')">
              <span class="ball"><img src="/img/current-white.png" alt="current step boston"></span>
              <p class="mb-0">Inspection</p>
            </div>
            <div class="item" :class="{'complete' : status.reportPreparation === 1,'current' : currentStep('report-preparation'), 'activeStep' : isActive == 'report-preparation'}" @click="changeTab('report-preparation')">
                <span class="ball"><img src="/img/current-white.png" alt="current step boston"></span>
              <p class="mb-0">Report Preparation</p>
            </div>
            <div class="item" :class="{'unCompleted': isUnCompleteReview(status.initialReview), 'complete' : isUnCompleteReview(status.initialReview, true), 'current' : currentStep('initial-review'), 'activeStep' : isActive == 'initial-review'}" @click="changeTab('initial-review')">
              <span class="ball">
                    <img src="/icons/info.png" class="uncomplete">
                    <img src="/img/current-white.png" alt="current step boston">
              </span>
              <p class="mb-0">Initial Review</p>
            </div>
            <div class="item" :class="{ 'unCompleted': isUnComplete(status.reportAnalysisReview), 'complete' : checkReportAnalysysCompletation(status.reportAnalysisReview), 'current' : currentStep('report-analysis-review'), 'activeStep' : isActive == 'report-analysis-review'}" @click="changeTab('report-analysis-review')">
              <span class="ball">
                    <img src="/icons/info.png" class="uncomplete">
                    <img src="/img/current-white.png" alt="current step boston">
              </span>
              <p class="mb-0">Report Analysis and Review</p>
            </div>
            <div class="item" :class="{ 'disable' : norewriteReport === 1, 'complete' : status.reWritingReport === 1,'current' : currentStep('rewriting-report'), 'activeStep' : isActive == 'rewriting-report'}" @click="changeTab('rewriting-report', norewriteReport)">
                  <span class="ball">
                      <img src="/img/current-white.png" alt="current step boston">
                  </span>
              <p class="mb-0">Re-writing the report</p>
            </div>
            <div class="item" :class="{'complete' : status.qualityAssurance === 1, 'current' : currentStep('quality-assurance'), 'activeStep' : isActive == 'quality-assurance'}" @click="changeTab('quality-assurance')">
                  <span class="ball">
                      <img src="/img/current-white.png" alt="current step boston">
                  </span>
              <p class="mb-0">Quality Assurance (E&O)</p>
            </div>
            <div class="item" :class="{'complete' : status.submission === 1,'current' : currentStep('submission'), 'activeStep' : isActive == 'submission'}" @click="changeTab('submission')">
                  <span class="ball">
                      <img src="/img/current-white.png" alt="current step boston">
                  </span>
              <p class="mb-0">Submission</p>
            </div>
            <div class="item" :class="{'complete' : status.revision === 1,'current' : currentStep('revision'), 'activeStep' : isActive == 'revision'}" @click="changeTab('revision')">
                  <span class="ball">
                      <img src="/img/current-white.png" alt="current step boston">
                  </span>
              <p class="mb-0">Revision</p>
            </div>
          </div>
          <!-- step item -->
          <div class="step-item ">
            <!-- Order creation -->
            <OrderCreate :order="orderData" v-if="isActive === 'order-create'"></OrderCreate>
            <!-- Scheduling -->
            <Schedule :order="orderData" :appraisers="appraisers" v-if="isActive === 'scheduling'"></Schedule>
            <!-- Inspection -->
            <Inspection :order="orderData" v-if="isActive === 'inspection'"></Inspection>
            <!-- Report preparation -->
            <ReportPreparation v-if="isActive === 'report-preparation'" :role="myRole" :users="users" :order="orderData"></ReportPreparation>
            <!-- Initial Review -->
            <InitialReview :order="orderData" :users="users" v-if="isActive === 'initial-review'"></InitialReview>
            <!-- Report Analysis and Review -->
            <ReportAnalysisReview v-if="isActive === 'report-analysis-review'" :order="orderData" :users="users"></ReportAnalysisReview>
            <!-- Re-writing the report -->
            <RewritingReport :order="orderData" v-if="isActive === 'rewriting-report'"></RewritingReport>
            <!-- Quality Assurance (E&O) -->
            <QualityAssurance :order="order" :users="users" :public-com="publicCom" v-if="isActive === 'quality-assurance'"></QualityAssurance>
            <!-- Submission -->
            <Submission :order="order" :users="users" v-if="isActive === 'submission'"></Submission>
            <!-- Revision -->
            <Revision :order="orderData" v-model="revissionBox"></Revision>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import OrderCreate from '../workflow/OrderCreate'
import Schedule from '../workflow/Schedule'
import Inspection from '../workflow/Inspection'
import ReportPreparation from "../workflow/ReportPreparation";
import InitialReview from "../workflow/InitialReview";
import ReportAnalysisReview from "../workflow/ReportAnalysisReview";
import RewritingReport from "../workflow/RewritingReport";
import QualityAssurance from "../workflow/QualityAssurance";
import Submission from "../workflow/Submission";
import Revision from "../workflow/Revision";
import { integer } from 'vee-validate/dist/rules';

export default {
  name: 'WorkFlow',
  props: {
    norewrite: integer,
    users: Array,
    permissions: Array,
    role: String,
    order: [],
    appraisers: [],
    publicCom: ''
  },
  components: {
    Revision,
    Submission,
    QualityAssurance,
    RewritingReport,
    ReportAnalysisReview,
    InitialReview,
    ReportPreparation,
    OrderCreate,
    Schedule,
    Inspection
  },
  provide() {
    return {
      usersInfo: this.users,
      appraisersInfo: this.appraisers,
    }
  },
  data: () => ({
      isActive: 'order-create',
      status: '',
      myRole: '',
      revissionBox: false,
      orderData: [],
  }),
  created(){
      this.$store.commit('app/storeOrder', this.order);
      this.$root.$emit('sendHistory', this.order);

      this.initOrder(this.order);
      this.status = JSON.parse(this.order.workflow_status) ?? [];
      this.updateRole()

      this.norewriteReport = this.norewrite;

      let getParams = this.params();
      if (getParams['wkf']) {
          this.changeTab(getParams['wkf']);
      }

      this.$root.$on('wk_flow_menu', (res) => {
          this.status = JSON.parse(res.workflow_status);
          this.initOrder(res);
      });

      this.$root.$on('wk_flow_toast', (res) => {
          if (res.error == false) {
              this.$store.commit('app/storeOrder', res.data);
              this.orderData = res.data;
          }
          this.$toast.open({
              message: res.message,
              type: res.error == true ? 'error' : 'success',
          });
      });
      this.$root.$on('toast_msg', (res) => {
          this.$toast.open({
              message: res.message,
              type: res.error == true ? 'error' : 'success',
          });
      });
  },
  mounted(){

  },
  methods: {
    currentStep(step){
      if (this.orderData.currentStep == step) {
          return true;
      }
    },
    initOrder(order) {
        this.orderData = order;
        // this.status = JSON.parse(order.workflow_status) ?? [];
        if (order.analysis){
          let noRewrite = 1;
          if (order.analysis.is_review_send_back && order.analysis.is_review_send_back == 1) {
              noRewrite = 0;
          } else if( order.analysis.is_review_send_back == false && order.report_rewrite && order.report_rewrite.id != null) {
              noRewrite = 0;
          }
          this.norewriteReport = noRewrite;
        }
        this.currentStep(this.orderData.currentStep);
        this.checkReportAnalysysCompletation(this.status.reportAnalysisReview);
        this.isUnComplete(this.status.initialReview);
        this.isUnComplete(this.status.reportAnalysisReview);
        this.isUnCompleteReview(this.status.initialReview);
    },
    updateRole() {
        if (this.role.length) {
          this.myRole = this.role
        }
    },
    changeTab(type, value = 0) {
        if (value === 1) {
            return false;
        }
        this.isActive = type;
        this.addParam('wkf', type);

        if (type == "revision") {
            this.revissionBox = true;
            // this.$root.$emit('open_revision', true);
            document.documentElement.style.overflow = 'hidden'
        }
    },
    checkReportAnalysysCompletation(status) {
        if (status === 1) {
             if (this.orderData.analysis){
                if (this.orderData.analysis.is_check_upload && this.orderData.analysis.is_check_upload == 1) {
                    return true;
                }
            }
        }
        return false;
    },
    isUnComplete(status) {
        if (status === 1) {
             if (this.orderData.analysis && this.orderData.report_rewrite){
                if (this.orderData.analysis.is_review_send_back && this.orderData.analysis.is_review_send_back == 1) {
                    return true;
                }
            }
        }
        return false;
    },
    isUnCompleteReview(status, type) {
        if (status === 1) {
            if (type == true) {
                 if (this.orderData.initial_review && this.orderData.initial_review.is_check_upload == true){
                    return true;
                }
            } else {
                if (this.orderData.initial_review && this.orderData.initial_review.is_check_upload == false){
                    return true;
                }
            }
        }
        return false;
    }
  },
  watch: {
    isActive: function(val){
        if (val === 'revision') {
            this.revissionBox = true;
        } else {
            this.revissionBox = false;
        }
    }
  }
}
</script>
