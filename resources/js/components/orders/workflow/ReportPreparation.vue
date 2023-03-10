<template>
    <div class="report-preparation-item step-items">
        <div v-if="isAdmin">
            <div v-if="adminDataExist">
                <a class="edit-btn" @click="openEditBox"><span class="icon-edit"><span
                            class="path1"></span><span class="path2"></span></span></a>
                <div class="group">
                    <p class="text-light-black mgb-12">Report creator</p>
                    <p class="mb-0 text-light-black fw-bold">{{ this.creator }}</p>
                </div>
                <div class="group">
                    <p class="text-light-black mgb-12">Report Reviewer</p>
                    <p class="mb-0 text-light-black fw-bold">{{ this.viewer }}</p>
                </div>
            </div>
            <div v-else>
                <ValidationObserver ref="adminForm">
                    <div class="mgb-32">
                        <ValidationProvider class="group" name="Report Creator" rules="required" v-slot="{ errors }">
                            <div :class="{ 'invalid-form' : errors[0] }">
                                <label for="" class="d-block mb-2 dashboard-label">Report Creator </label>
                                <m-select :options="users" object item-text="name" item-value="id" v-model="creatorId">
                                </m-select>
                                <span v-if="errors[0]" class="error-message">{{ errors[0] }}</span>
                            </div>
                        </ValidationProvider>
                    </div>
                    <div class="mgb-32">
                        <label for="" class="d-block mb-2 dashboard-label">Report Reviewer </label>
                        <m-select :options="users" object item-text="name" item-value="id" v-model="viewerId">
                        </m-select>
                    </div>
                    <div class="text-end mgt-32">
                        <button class="button button-primary px-4 h-40 d-inline-flex align-items-center"
                            @click="saveAdminData">Assign</button>
                    </div>
                </ValidationObserver>
            </div>
        </div>
        <div v-else>
            <div v-if="dataExist">
                <a class="edit-btn" @click="dataExist = false;"><span class="icon-edit"><span
                            class="path1"></span><span class="path2"></span></span></a>
                <div class="group">
                    <p class="text-light-black mgb-12">Report creator</p>
                    <p class="mb-0 text-light-black fw-bold">{{ this.creator }}</p>
                </div>
                <div class="group">
                    <p class="text-light-black mgb-12">Report reviewer</p>
                    <p class="mb-0 text-light-black fw-bold">{{ this.viewer }}</p>
                </div>
                <div class="group">
                    <p class="text-light-black mgb-12">Assignee to</p>
                    <p class="mb-0 text-light-black fw-bold">{{ this.assignToName }}</p>
                </div>
                <div class="group">
                    <p class="text-light-black mgb-12">Trainee to</p>
                    <p class="mb-0 text-light-black fw-bold">{{ this.trainee }}</p>
                </div>
                <div class="group">
                    <p class="text-light-black mgb-12">Note</p>
                    <p class="mb-0 text-light-black fw-bold" v-html="this.note"></p>
                </div>
                <div class="group" v-if="orderData.report">
                    <p class="text-light-black mgb-12">Report preparation file</p>
                    <div class="document">
                        <div class="row r-prep">
                            <div class="d-flex align-items-center mb-3"
                                v-for="(file, key) in orderData.report.attachments" :key="key">
                                <img v-if="file.mime_type == 'image/jpeg'" src="/img/image.svg" alt="boston files"
                                    class="img-fluid">
                                <img v-else-if="file.mime_type == 'application/pdf'" src="/img/pdf.svg"
                                    alt="boston files" class="img-fluid">
                                <img v-else src="/img/common.svg" alt="boston files" class="img-fluid">
                                <span class="text-light-black d-inline-block mgl-12 file-name">
                                    <a :href="file.original_url" download>{{ file.name }}</a>
                                    <p class="text-gray mb-0 fs-12">Uploaded: {{ orderData.report.create_by.name
                                        + ', ' +
                                        orderData.report.updated_at }}</p>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else>
                <ValidationObserver ref="assigneeForm">
                    <div class="mgb-32">
                        <div class="group">
                            <p class="text-light-black mgb-12">Report creator</p>
                            <p class="mb-0 text-light-black fw-bold">{{ this.creator ? this.creator : "No creator found" }}</p>
                        </div>
                    </div>
                    <div class="mgb-32">
                        <div class="group">
                            <p class="text-light-black mgb-12">Report reviewer</p>
                            <p class="mb-0 text-light-black fw-bold">{{ this.viewer ? this.viewer : "No report reviewer found" }}</p>
                        </div>
                    </div>
                    <div class="mgb-32">
                        <ValidationProvider class="group" name="Assign to" rules="required" v-slot="{ errors }">
                            <div :class="{ 'invalid-form' : errors[0] }">
                                <label for="" class="d-block mb-2 dashboard-label">Assign to </label>
                                <m-select :options="users" object item-text="name" item-value="id" v-model="assignTo">
                                </m-select>
                                <span v-if="errors[0]" class="error-message">{{ errors[0] }}</span>
                            </div>
                        </ValidationProvider>
                    </div>
                    <div class="mgb-32">
                        <ValidationProvider class="group" name="Trainee Selection" rules="required" v-slot="{ errors }">
                            <div :class="{ 'invalid-form' : errors[0] }">
                                <label for="" class="d-block mb-2 dashboard-label">Trainee Selection </label>
                                <m-select theme="blue" :options="users" object item-text="name" item-value="id"
                                    v-model="traineeId"></m-select>
                                <span v-if="errors[0]" class="error-message">{{ errors[0] }}</span>
                            </div>
                        </ValidationProvider>
                    </div>
                    <div class="mgb-32">
                        <div class="group">
                            <label for="" class="mb-2 text-light-black d-inline-block">Add note</label>
                            <div class="preparation-input w-100 position-relative">
                                <text-editor placeholder="Add notes" v-model="note"></text-editor>
                            </div>
                        </div>
                    </div>
                    <div class="group">
                        <p class="text-light-black mgb-12">Files</p>
                        <div class="position-relative file-upload">
                            <input type="file" multiple v-on:change="addFiles">
                            <label for="" class="py-2">Upload <span class="icon-upload ms-3 fs-20"><span
                                        class="path1"></span><span class="path2"></span><span
                                        class="path3"></span></span></label>
                        </div>
                        <p class="text-light-black mgb-12" v-if="fileData.files.length">{{ fileData.files.length }} Files</p>
                        <span v-if="fileNotFound" class="error-message">Please choose file first</span>
                    </div>
                    <div class="text-end mgt-32">
                        <button class="button button-primary px-4 h-40 d-inline-flex align-items-center" @click="saveAssigneeData" :disabled="isUploading">Done</button>
                        <button class="button button-close px-4 h-40 d-inline-flex align-items-center" @click.prevent="cancelButton">Close</button>
                    </div>
                </ValidationObserver>
            </div>
        </div>
    </div>
</template>
<script>
    export default {
        name: 'ReportPreparation',
        props: {
            users: [],
            role: String,
            order: [],
        },
        data: () => ({
            isUploading: false,
            orderData: [],
            adminDataExist: true,
            dataExist: true,
            fileNotFound: false,
            creator: '',
            viewer: '',
            trainee: '',
            isAdmin: false,
            creatorId: '',
            viewerId: '',
            traineeId: '',
            assignTo: '',
            assignToName: '',
            note: '',
            fileData: {
                file_type: '',
                files: [],
            },
            message: '',
        }),
        methods: {
            openEditBox() {
                this.adminDataExist = false;
                this.fileNotFound = false;
            },
            addFiles(event) {
                this.fileData.files = event.target.files
            },
            updateAdmin() {
                if (this.orderData.report) {
                    let report = this.orderData.report
                    if (report.creator) {
                        this.creator = report.creator.name
                    }
                    if (report.reviewer) {
                        this.viewer = report.reviewer.name
                    }
                    if (report.trainee) {
                        this.trainee = report.trainee.name
                    }
                    if (report.assignee) {
                        this.assignToName = report.assignee.name
                    }
                    this.creatorId = report.creator_id
                    this.viewerId = report.reviewed_by
                    this.traineeId = report.trainee_id
                    this.assignTo = report.assigned_to ?? report.creator_id
                    this.note = report.note
                }

                if (this.creator || this.viewer) {
                    this.isAdmin = false
                    this.adminDataExist = true;
                } else {
                    this.isAdmin = true
                    this.adminDataExist = false;
                }
                if (this.trainee || this.note) {
                    this.dataExist = true
                } else {
                    this.dataExist = false
                }
            },
            saveAdminData() {
                this.$refs.adminForm.validate().then((status) => {
                    if (status) {
                        const data = {
                            'creator_id': this.creatorId,
                            'reviewed_by': this.viewerId
                        }
                        this.$boston.post('admin-report-preparation-create/' + this.orderData.id, data, {
                            headers: {
                                'Content-Type': 'multipart/form-data'
                            }
                        }).then(res => {
                            this.isUploading = false
                            this.fileData.files = []
                            this.orderData = res.data;
                            this.updateAdmin();
                            this.$root.$emit('wk_update', this.orderData);
                            this.$root.$emit('wk_flow_menu', this.orderData);
                            this.$root.$emit('wk_flow_toast', res);
                        }).catch(err => {
                            this.isUploading = false
                        });
                    } else {
                        this.isUploading = false
                    }
                })
            },
            saveAssigneeData() {
                if (this.orderData.report?.attachments.length == 0 && this.fileData.files.length == 0) {
                    this.fileNotFound = true;
                    return false;
                }
                this.isUploading = true
                this.$refs.assigneeForm.validate().then((status) => {
                    if (status) {
                        let formData = new FormData();
                        for (let i = 0; i < this.fileData.files.length; i++) {
                            let file = this.fileData.files[i];
                            formData.append('files[' + i + ']', file)
                        }
                        formData.append('file_type', this.fileData.file_type)
                        formData.append('assigned_to', this.assignTo)
                        formData.append('note', this.note)
                        formData.append('trainee_id', this.traineeId)
                        formData.append('creator_id', this.creatorId)
                        formData.append('reviewed_by', this.viewerId ?? '')
                        this.$boston.post('assignee-report-preparation-create/' + this.orderData.id, formData, {
                            headers: {
                                'Content-Type': 'multipart/form-data'
                            }
                        }).then(res => {
                            this.isUploading = false
                            this.fileData.files = []
                            this.fileData.file_type = ''
                            this.orderData = res.data;
                            this.fileNotFound = false;
                            this.updateAdmin();
                            this.$root.$emit('wk_update', this.orderData);
                            this.$root.$emit('wk_flow_menu', this.orderData);
                            this.$root.$emit('wk_flow_toast', res);
                        }).catch(err => {
                            this.isUploading = false;
                        });
                    } else {
                        this.isUploading = false
                    }
                })
            },
            updateRole() {
                if (this.role == 'admin') {
                    this.isAdmin = true
                }
            },
            cancelButton(){
                this.updateAdmin();
                this.dataExist = true;
            }
        },
        created() {
            let order = this.order;
            let localOrderData = this.$store.getters['app/orderDetails']
            if (localOrderData) {
                order = localOrderData;
            }
            this.orderData = order;
            this.updateAdmin()
        },
        watch: {
            fileData: {
                handler(val) {
                    if (val.files.length > 0) {
                        this.fileNotFound = false;
                    } else {
                        this.fileNotFound = true;
                    }
                },
                deep: true
            }
        }
    }
</script>
