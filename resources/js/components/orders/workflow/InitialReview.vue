<template>
    <div>
        <div class="initial-review-item step-items" v-if="currentStep == 'create'">
            <div class="group">
                <p class="text-light-black mgb-12">Report creator</p>
                <p class="mb-0 text-light-black fw-bold">{{ initialReview.report_creator_name }}</p>
            </div>
            <div class="group">
                <p class="text-light-black mgb-12">Report reviewer</p>
                <p class="mb-0 text-light-black fw-bold">{{ initialReview.report_reviewer_name }}</p>
            </div>
            <div class="group">
                <p class="text-light-black mgb-12">Report Preparation Notes</p>
                <p class="mb-0 text-light-black fw-bold" v-html="initialReview.report_note"></p>
            </div>
            <br /><br />
        </div>
        <div class="initial-review-item step-items" v-if="currentStep == 'view'">
            <a class="edit-btn" @click="editInitialReview"><span class="icon-edit"><span class="path1"></span><span
                        class="path2"></span></span></a>
            <div class="group">
                <p class="text-light-black mgb-12">Report creator</p>
                <p class="mb-0 text-light-black fw-bold">{{ initialReview.report_creator_name }}</p>
            </div>
            <div class="group">
                <p class="text-light-black mgb-12">Report reviewer</p>
                <p class="mb-0 text-light-black fw-bold">{{ initialReview.report_reviewer_name }}</p>
            </div>
            <div class="group">
                <p class="text-light-black mgb-12">Assigned to</p>
                <p class="mb-0 text-light-black fw-bold">{{ initialReview.assigned_name }}</p>
            </div>
            <div class="group">
                <p class="text-light-black mgb-12">Notes</p>
                <p class="mb-0 text-light-black fw-bold" v-html="initialReview.note"></p>
            </div>
            <div class="mgb-32 d-flex align-items-center">
                <div class="checkbox-group review-check mgr-20" v-if="initialReview.is_review_done == 1">
                    <input type="radio" checked class="checkbox-input check-data">
                    <label for="" class="checkbox-label text-capitalize">Review Done</label>
                </div>
                <div class="checkbox-group review-check" v-if="initialReview.is_check_upload == 1">
                    <input type="radio" checked class="checkbox-input check-data">
                    <label for="" class="checkbox-label text-capitalize">Review Done As Check & upload</label>
                </div>
            </div>
            <br /><br />
        </div>
        <div class="initial-review-form" v-if="currentStep == 'create' || currentStep == 'edit'">
            <ValidationObserver ref="initialReviewForm">
                <div class="mgb-32">
                    <ValidationProvider class="group" name="Assigned to" rules="required" v-slot="{ errors }">
                        <div :class="{ 'invalid-form' : errors[0] }">
                            <label for="" class="d-block mb-2 dashboard-label">Assign to<span
                                    class="text-danger require"></span></label>
                            <!-- <select class="dashboard-input w-100" v-model="initialReview.assigned_to">
                                <option value="">Please select to assign</option>
                                <option v-for="user in users" :key="user.id" :value="user.id">
                                    {{ user.name }}
                                </option>
                            </select> -->
                            <m-select theme="blue" :options="users" object item-text="name" item-value="id" v-model="initialReview.assigned_to"></m-select>
                            <span v-if="errors[0]" class="error-message">{{ errors[0] }}</span>
                        </div>
                    </ValidationProvider>
                </div>
                <div class="mgb-32">
                    <div class="group mb-2">
                        <label for="" class="d-block mb-2 dashboard-label">Add note</label>
                        <text-editor v-model="initialReview.note" placeholder="Enter notes..."></text-editor>
                    </div>
                    <div class="mgb-32 d-flex align-items-center">
                        <div class="checkbox-group review-check mgr-20">
                            <input type="radio" class="checkbox-input check-data" v-model="initialReview.checkbox"
                                value="1">
                            <label for="" class="checkbox-label text-capitalize">Review Done</label>
                        </div>
                        <div class="checkbox-group review-check">
                            <input type="radio" class="checkbox-input check-data" v-model="initialReview.checkbox"
                                value="2">
                            <label for="" class="checkbox-label text-capitalize">Review Done As Check & upload</label>
                        </div>
                    </div>
                </div>
            </ValidationObserver>
            <div class="text-end mgt-32">
                <button class="button button-primary px-4 h-40 d-inline-flex align-items-center" @click="saveInitialReview">
                    Done
                </button>
                <button class="button button-close px-4 h-40 d-inline-flex align-items-center" @click="currentStep = 'view'">
                    Close
                </button>
            </div>
        </div>
    </div>
</template>
<script>
import TextEditor from "../../../src/editor/TextEditor.vue"

export default{
  components: { TextEditor },
        name: 'InitialReview',
        props: {
            order: [],
            users: []
        },
        data: () => ({
            initialReview: {
                orderData: [],
                initial_review_id: 0,
                report_creator_name: '',
                report_reviewer_name: '',
                report_trainee_name: '',
                report_note: '',
                assigned_to: '',
                assigned_name: '',
                note: '',
                checkbox: null,
                is_review_done: false,
                is_check_upload: false,
                order_id: '',
            },
            message: '',
            alreadyInitialReview: 0,
            currentStep: 'create',
        }),
        created() {
            let order = this.order;
            let localOrderData = this.$store.getters['app/orderDetails']
            if(localOrderData){
                order = localOrderData;
            }
            this.getInitialReviewData(order);

            this.$root.$on("wk_update", (res) => {
                this.getInitialReviewData(res);
            });
        },
        methods: {
            getInitialReviewData(order) {
                this.orderData = order;
                this.initialReview.order_id = this.orderData.id;

                if (this.orderData.report) {
                    this.alreadyInitialReview = (JSON.parse(this.orderData.workflow_status)).initialReview
                    this.alreadyInitialReview == 1 ? this.currentStep = 'view' : 'create'

                    if (this.orderData.report) {
                        this.initialReview.report_creator_name = this.orderData.report.creator.name ?? null
                        this.initialReview.report_reviewer_name = this.orderData.report.reviewer.name ?? null
                        this.initialReview.report_trainee_name = this.orderData.report.trainee?.name;
                        this.initialReview.report_note = this.orderData.report.note
                        this.initialReview.assigned_to = this.orderData.report.creator.id
                    }
                    if (this.orderData.initial_review) {
                        this.initialReview.initial_review_id = this.orderData.initial_review.id
                        this.initialReview.note = this.orderData.initial_review.note
                        this.initialReview.assigned_name = this.orderData.initial_review.assignee.name
                        this.initialReview.assigned_to = this.orderData.initial_review.assigned_to
                        this.initialReview.is_review_done = this.orderData.initial_review.is_review_done
                        this.initialReview.is_check_upload = this.orderData.initial_review.is_check_upload
                        if (this.orderData.initial_review.is_review_done == 1) {
                            this.initialReview.checkbox = '1'
                        } else {
                            this.initialReview.checkbox = '2'
                        }
                    }
                }
            },
            saveInitialReview() {
                this.$refs.initialReviewForm.validate().then((status) => {
                    if (status) {
                        let self = this
                        this.$boston.post('save-initial-review', this.initialReview)
                            .then(res => {
                                this.message = res.message
                                this.orderData = res.data
                                this.$root.$emit('wk_update', this.orderData)
                                this.$root.$emit('wk_flow_menu', this.orderData)
                                this.$root.$emit('wk_flow_toast', res);
                                this.getInitialReviewData(this.orderData);
                                this.currentStep = 'view'
                            }).catch(err => {
                                console.log(err);
                            })
                    }
                })
            },
            editInitialReview() {
                this.currentStep = 'edit'
            }
        }
    }
</script>
