<template>
    <div class="row ">
        <div class="col-12">
            <!-- Files -->
            <div class="order-details-box bg-white">
                <div class="box-header">
                    <p class="fw-bold text-light-black fs-20 mb-0">Workflow Files</p>
                </div>
                <div class="box-body">
                    <!-- document -->
                    <div class="document">
                        <div class="row">
                            <div class="col-sm-6 col-md-4 col-lg-3" v-if="inspectionFiles">
                                <p class="fw-bold text-light-black">Inspection Files</p>
                                <div class="d-flex align-items-center mb-3" v-for="file in inspectionFiles">
                                    <img src="/img/zip.svg" alt="boston files" class="img-fluid">
                                    <div class="mgl-12">
                                        <span class="text-light-black mb-0 file-name d-block"><a
                                                :href="file.original_url" target="_blank" download
                                                class="text-light-black">{{ file.name }}</a>
                                            <p class="text-gray mb-0 fs-12">
                                                Uploaded:
                                                {{ orderData.inspection.create_by.name
                                                + ', ' +
                                                orderData.inspection.updated_at }}
                                            </p>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-4 col-lg-3" v-if="reportFiles">
                                <p class="fw-bold text-light-black">Report Preparation Files</p>
                                <div class="d-flex align-items-center mb-3" v-for="file in reportFiles">
                                    <img v-if="file.mime_type == 'image/jpeg'" src="/img/image.svg" alt="boston files" class="img-fluid">
                                    <img v-else-if="file.mime_type == 'application/pdf'" src="/img/pdf.svg" alt="boston files" class="img-fluid">
                                    <img v-else src="/img/common.svg" alt="boston files" class="img-fluid">
                                    <div class="mgl-12">
                                        <span class="text-light-black mb-0 file-name d-block"><a
                                                :href="file.original_url" target="_blank" download
                                                class="text-light-black">{{ file.name }}</a>
                                            <p class="text-gray mb-0 fs-12">
                                                Uploaded:
                                                {{ orderData.report.create_by.name
                                                + ', ' +
                                                orderData.report.updated_at }}
                                            </p>
                                        </span>
                                    </div>

                                </div>
                            </div>
                            <div class="col-sm-6 col-md-4 col-lg-3" v-if="reportAnalysisFiles">
                                <p class="fw-bold text-light-black">Report Analysis Files</p>
                                <div class="d-flex align-items-center mb-3" v-for="file in reportAnalysisFiles">
                                    <img v-if="file.mime_type == 'image/jpeg'" src="/img/image.svg" alt="boston files" class="img-fluid">
                                    <img v-else-if="file.mime_type == 'application/pdf'" src="/img/pdf.svg" alt="boston files" class="img-fluid">
                                    <img v-else src="/img/common.svg" alt="boston files" class="img-fluid">
                                    <div class="mgl-12">
                                        <span class="text-light-black mb-0 file-name d-block"><a
                                                :href="file.original_url" target="_blank" download
                                                class="text-light-black">{{ file.name }}</a>
                                            <p class="text-gray mb-0 fs-12">
                                                Uploaded:
                                                {{ orderData.analysis.updated_by.name
                                                + ', ' +
                                                orderData.analysis.updated_at }}
                                            </p>
                                        </span>
                                    </div>

                                </div>
                            </div>
                            <div class="col-sm-6 col-md-4 col-lg-3" v-if="qaFiles">
                                <p class="fw-bold text-light-black">Quality Assurance Files</p>
                                <div class="d-flex align-items-center mb-3" v-for="file in qaFiles">
                                    <img v-if="file.mime_type == 'image/jpeg'" src="/img/image.svg" alt="boston files" class="img-fluid">
                                    <img v-else-if="file.mime_type == 'application/pdf'" src="/img/pdf.svg" alt="boston files" class="img-fluid">
                                    <img v-else src="/img/common.svg" alt="boston files" class="img-fluid">
                                    <div class="mgl-12">
                                        <span class="text-light-black mb-0 file-name d-block"><a
                                                :href="file.original_url" target="_blank" download
                                                class="text-light-black">{{ file.name }}</a>
                                            <p class="text-gray mb-0 fs-12">
                                                Uploaded:
                                                {{ orderData.quality_assurance.updated_by.name
                                                + ', ' +
                                                orderData.quality_assurance.updated_at }}
                                            </p>
                                        </span>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6 col-md-4 col-lg-3" v-if="reportRewriteFiles">
                                <p class="fw-bold text-light-black">Report Rewrite Files</p>
                                <div class="d-flex align-items-center mb-3" v-for="file in reportRewriteFiles">
                                    <img v-if="file.mime_type == 'image/jpeg'" src="/img/image.svg" alt="boston files" class="img-fluid">
                                    <img v-else-if="file.mime_type == 'application/pdf'" src="/img/pdf.svg" alt="boston files" class="img-fluid">
                                    <img v-else src="/img/common.svg" alt="boston files" class="img-fluid">
                                    <div class="mgl-12">
                                        <span class="text-light-black mb-0 file-name d-block"><a
                                                :href="file.original_url" target="_blank" download
                                                class="text-light-black">{{ file.name }}</a>
                                            <p class="text-gray mb-0 fs-12">
                                                Uploaded:
                                                {{ orderData.report_rewrite.update_by.name
                                                + ', ' +
                                                orderData.report_rewrite.updated_at }}
                                            </p>
                                        </span>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    export default {
        props: {
            order: []
        },
        data() {
            return {
                inspectionFiles: [],
                reportFiles: [],
                reportAnalysisFiles: [],
                reportRewriteFiles: [],
                qaFiles: [],
                orderData: []
            }
        },
        created() {
            this.getWorkflowFiles(this.order)
            this.$root.$on('wk_flow_toast', (res) => {
                this.orderData = res.data;
                this.getWorkflowFiles(this.orderData);
            });
        },
        methods: {
            getWorkflowFiles(order) {
                this.orderData = order;
                this.inspectionFiles = order.inspection && order.inspection.attachments ? order.inspection.attachments : []
                this.reportFiles = order.report && order.report.attachments ? order.report.attachments : []
                this.reportAnalysisFiles = order.analysis && order.analysis.attachments ? order.analysis.attachments : []
                this.reportRewriteFiles = order.report_rewrite && order.report_rewrite.attachments ? order.report_rewrite.attachments : []
                this.qaFiles = order.quality_assurance && order.quality_assurance.attachments ? order.quality_assurance.attachments : []
            }
        }
    }
</script>
