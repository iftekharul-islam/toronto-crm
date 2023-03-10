<template>
    <div class="order-details-box bg-white">
        <div class="box-header">
            <p class="fw-bold text-light-black fs-20 mb-0">Appraisal Details</p>
            <a v-b-modal.appraisal-info class="d-inline-flex edit align-items-center fw-bold cursor-pointer">Edit <span
                    class="icon-edit ms-3"><span class="path1"></span><span class="path2"></span></span></a>
        </div>
        <div class="box-body">
            <div class="list__group">
                <p class="mb-0 left-side">Appraiser Name</p>
                <span>:</span>
                <p class="right-side mb-0">{{ edited.appraiser_name }}</p>
            </div>
            <div class="list__group">
                <p class="mb-0 left-side">Loan #</p>
                <span>:</span>
                <p class="right-side mb-0 word-break">{{ edited.loan_no }}</p>
            </div>
            <div class="list__group">
                <p class="mb-0 left-side">Loan Type </p>
                <span>:</span>
                <p class="right-side mb-0">{{ edited.loan_type_name }}</p>
            </div>
            <div class="list__group" v-if="edited.fha_case_no">
                <p class="mb-0 left-side">FHA Case</p>
                <span>:</span>
                <p class="right-side mb-0 word-break">{{ edited.fha_case_no }}</p>
            </div>
            <div class="list__group">
                <p class="mb-0 left-side">Property Type</p>
                <span>:</span>
                <p class="right-side mb-0 word-break">{{ edited.property_type.type }}</p>
            </div>
        </div>
        <!-- modal -->
        <b-modal id="appraisal-info" size="lg" title="Edit appraisal information">
            <div class="modal-body">
                <ValidationObserver ref="appraisalForm">
                    <div class="row">
                        <div class="col-md-6">
                            <ValidationProvider class="group d-block" name="Appraiser name" rules="required"
                                v-slot="{ errors }">
                                <div class="position-relative" :class="{ 'invalid-form' : errors[0] }">
                                    <label for="" class="d-block mb-2 dashboard-label">Appraiser name <span
                                            class="text-danger require"></span></label>
                                    <m-select v-model="details.appraiser_id" :options="appraisers" item-text="name" item-value="id" object theme="blue"></m-select>
                                    <span v-if="errors[0]" class="error-message">{{ errors[0] }}</span>
                                </div>
                            </ValidationProvider>
                            <ValidationProvider class="group d-block" name="Loan type" rules="required" v-slot="{ errors }">
                                <div class="position-relative" :class="{ 'invalid-form' : errors[0] }">
                                    <label for="" class="d-block mb-2 dashboard-label">Loan type </label>
                                    <m-select @change="loanTypeChange" v-model="details.loan_type" :options="loanTypes" item-text="name" item-value="id" object theme="blue"></m-select>
                                    <span v-if="errors[0]" class="error-message">{{ errors[0] }}</span>
                                </div>
                            </ValidationProvider>
                        </div>
                        <div class="col-md-6">
                            <span class="position-relative group d-block">
                                <label for="" class="d-block mb-2 dashboard-label">Loan no</label>
                                <input type="text" v-model="details.loan_no" class="dashboard-input w-100">
                            </span>
                            <ValidationProvider class="group d-block" name="FHA case no"
                                :rules="{ required: this.fhaExists == 1 }" v-slot="{ errors }">
                                <div :class="{ 'invalid-form' : errors[0] }">
                                    <label for="" class="d-block mb-2 dashboard-label">FHA case no
                                        <span class="text-danger require" v-if="fhaExists == 1"></span>
                                    </label>
                                    <input type="text" class="dashboard-input w-100" v-model="details.fha_case_no">
                                    <span v-if="errors[0]" class="error-message">{{ errors[0] }}</span>
                                </div>
                            </ValidationProvider>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mt-2">
                                <ValidationProvider class="group" name="Property Type" rules="required"
                                    v-slot="{ errors }">
                                    <div :class="{ 'invalid-form' : errors[0] }">
                                        <label for="" class="d-block mb-2 dashboard-label">Property Type <span
                                                class="require"></span></label>
                                        <m-select v-model="details.property_type" :options="propertyTypes" item-text="type" item-value="id" object theme="blue"></m-select>
                                        <span v-if="errors[0]" class="error-message">{{ errors[0] }}</span>
                                    </div>
                                </ValidationProvider>
                            </div>
                        </div>
                    </div>
                </ValidationObserver>
            </div>
            <div slot="modal-footer">
                <b-button variant="secondary" @click="$bvModal.hide('appraisal-info')">Close</b-button>
                <b-button variant="primary" @click="updateAppraisalDetails">Save</b-button>
            </div>
        </b-modal>
    </div>
</template>
<script>
    export default {
        props: {
            orderId: String,
            order: [],
            appraisers: [],
            loanTypes: [],
            propertyTypes: []
        },
        data() {
            return {
                details: {
                    appraiser_name: '',
                    appraiser_id: '',
                    appraiser_type: '',
                    loan_type: '',
                    loan_type_name: '',
                    loan_no: '',
                    fha_case_no: '',
                    property_type: ''
                },
                edited: {},
                fhaExists: 0
            }
        },

        created() {
            // this.details = this.order.app
            let providerService = this.order.provider_service;
            let types = JSON.parse(providerService.appraiser_type_fee);
            if (types.length) {
                this.details.appraiser_type = types[0].type;
                this.details.appraiser_type_id = types[0].typeId;
            }
            this.getAppraisalDetails(this.order);
        },
        methods: {
            loanTypeChange() {
                let fhaExistData = this.loanTypes.filter((item) => {
                    if(item.id == this.details.loan_type) {
                        return item;
                    }
                });
                if(fhaExistData.length > 0){
                    this.fhaExists = fhaExistData[0].is_fha
                }
            },
            getAppraisalDetails(order) {
                this.details.appraiser_id = order.appraisal_detail.appraiser_id
                this.details.loan_type = order.appraisal_detail.loan_type
                this.details.appraiser_name =order.appraisal_detail.appraiser.name
                this.details.loan_type_name = order.appraisal_detail.get_loan_type.name
                this.details.loan_no = order.appraisal_detail.loan_no
                this.details.fha_case_no = order.appraisal_detail.fha_case_no
                this.details.property_type = order.appraisal_detail.property_type
                this.fhaExists = order.appraisal_detail.get_loan_type.is_fha
                this.edited = Object.assign({}, this.details)
            },
            updateAppraisalDetails() {
                this.$refs.appraisalForm.validate().then((status) => {
                    if (status) {
                        this.$boston.post('update-appraisal-info/' + this.orderId, this.details)
                            .then(res => {
                                this.$root.$emit('wk_update', res.data)
                                this.$root.$emit('wk_flow_toast', res)
                                this.$bvModal.hide('appraisal-info')
                                this.getAppraisalDetails(res.data)
                            }).catch(err => {
                                console.error(err)
                            })
                    }
                })
            }
        }
    }
</script>
