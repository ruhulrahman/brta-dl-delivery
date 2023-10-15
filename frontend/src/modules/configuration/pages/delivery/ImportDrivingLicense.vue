<template>
  <div class="section-wrapper">
    <b-breadcrumb>
      <b-breadcrumb-item to="/dashboard">
        <b-icon icon="house-fill" scale="1.25" shift-v="1.25" aria-hidden="true"></b-icon>
        Dashboard
      </b-breadcrumb-item>
      <b-breadcrumb-item to="/delivery">Delivery</b-breadcrumb-item>
      <b-breadcrumb-item active>Import Driving License</b-breadcrumb-item>
    </b-breadcrumb>
    <b-overlay :show="loading">
      <b-card>

        <div class="content-body">
                 <!-- Dropzone section start -->
                <section id="dropzone-examples">
                    <div class="row">
                        <div class="col-12">
                            <div class="card" v-show="!excelFileImported">
                                <h4 class="card-title">Add Driving License Through EXCEL File Upload</h4>
                                <div class="card-body">
                                    <b-row>
                                      <b-col>
                                        <b-card>
                                          <b-card-title>Guidelines</b-card-title>
                                          <b-card-body>
                                            <p class="card-text">1.<a :href="baseUrl + 'static/dl_info_upload_file_sample.xlsx'"> Click here to download the sample excel file</a>.</p>
                                            <p class="card-text">2. Please don't remove the header section in the excel.</p>
                                            <p class="card-text">3. The example data is dummy for your understanding. This is just to guide you how to fill up the form. Please remove that example data.</p>
                                            <p class="card-text">4. Please keep the date format as it is in the sample data.</p>
                                            <p class="card-text">5. Fill up the file</p>
                                            <p class="card-text">6. Reupload the completed file</p>
                                          </b-card-body>
                                        </b-card>
                                      </b-col>
                                      <b-col>
                                        <button @click="clearDropzone()" id="clear-dropzone" class="btn btn-outline-primary mb-1">
                                                <i data-feather="trash" class="mr-25"></i>
                                                <span class="align-middle">Clear Dropzone</span>
                                            </button>
                                            <vue-dropzone
                                                ref="dl_list_upload_dropzone"
                                                class="dropzone dropzone-area"
                                                id="dpz-remove-all-thumb"
                                                @vdropzone-success="vsuccess"
                                                @vdropzone-error="verror"
                                                :uploadMultiple=false
                                                :duplicateCheck=false
                                                :useCustomSlot=true
                                                :options="dropzone_configs"
                                                v-on:vdropzone-sending="sendingEvent"
                                                >
                                                <div class="dropzone-custom-content">
                                                    <h1 class="dropzone-custom-title text-primary"><i class="ri-upload-2-line"></i></h1>
                                                    <h1 class="dropzone-custom-title text-primary">Click to upload.</h1>
                                                    <h2 class="dropzone-custom-title text-danger">Maximum 1000 rows</h2>
                                                </div>
                                            </vue-dropzone>
                                      </b-col>
                                    </b-row>
                                </div>
                            </div>
                            <div class="card wait_me_to_process" v-show="excelFileImported">
                                <div class="card-body">
                                  <div class="row mt-2 mb-2">
                                    <div class="col-lg-6">
                                      <p class="pl-2">Total {{ importedList.length }} Rows</p>
                                    </div>
                                    <div class="col-lg-6 text-right">
                                      <button @click="remove_and_re_upload()" class="btn btn-danger mr-2">Remove All Data</button>
                                      <button @click="processToUploadData()" class="btn btn-success mr-2">Process To Upload Data</button>
                                    </div>
                                  </div>
                                  <div class="table-responsive">
                                      <table class="table table-hover" id="uni_agent_multiple_student_upload">
                                          <thead>
                                              <tr>
                                                  <th>SL</th>
                                                  <th>Reference</th>
                                                  <th>Entry Box Number</th>
                                                  <th>Deliver Date</th>
                                                  <th>Receive</th>
                                                  <th>Comment</th>
                                                  <th>Action</th>
                                              </tr>
                                          </thead>
                                          <tbody>
                                              <tr v-for="(item, index) in importedList" :key="index">
                                                  <!-- <td v-html="index + 1"></td> -->
                                                  <td v-html="item.serial_number"></td>
                                                  <td>
                                                      <div @click="editData(item)">
                                                          <span v-html="item.reference_number" class="mr-1"></span>
                                                          <i v-if="item.is_duplicate" v-tooltip="'Duplicate entry'" class="ri-error-warning-fill text-warning"></i>
                                                      </div>
                                                  </td>
                                                  <td>
                                                      <span v-html="item.entry_box_number" class="mr-1"></span>
                                                  </td>
                                                  <td><span v-if="item.delivery_date" v-html="dDate(item.delivery_date)"></span></td>
                                                  <td>
                                                    <span v-if="item.delivery_date" v-tooltip="'Receiving date'" v-html="dDate(item.delivery_date)"></span><br/>
                                                    <span v-if="item.receiving_box_number" v-tooltip="'Receiving box number'" v-html="item.receiving_box_number"></span><br/>
                                                  </td>
                                                  <td v-html="item.comment"></td>
                                                  <td>
                                                      <button @click="editData(item)" class="btn btn-outline-info btn-sm mr-1">
                                                          <i v-tooltip="'Edit'" class="ri-pencil-fill"></i>
                                                      </button>
                                                      <button @click="deleteItem(index)" class="btn btn-outline-danger btn-sm">
                                                          <i v-tooltip="'Delete'" class="ri-delete-bin-2-line"></i>
                                                      </button>
                                                  </td>
                                              </tr>
                                          </tbody>
                                      </table>
                                  </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </section>
            </div>
      </b-card>
    </b-overlay>
    <b-modal id="modal-1" ref="editModal" size="xl" title="DL Stock" centered :hide-footer="true">
      <ValidationObserver ref="form" v-slot="{ handleSubmit, reset }">
        <b-form @submit.prevent="handleSubmit(submitData)" @reset.prevent="reset" autocomplete="off">
          <b-row>
            <b-col lg="4" md="4" sm="12" xs="12">
              <ValidationProvider name="Serial Number" vid="serial_number" rules="required" v-slot="{ errors }">
                <b-form-group label-for="serial_number">
                <template v-slot:label>
                  Serial Number <span class="text-danger">*</span>
                  {{ editItem.serial_number }}
                </template>
                  <b-form-input
                    type="number"
                    id="serial_number"
                    v-model="editItem.serial_number"
                    placeholder="Enter Serial Number"
                    :state="errors[0] ? false : (valid ? true : null)"
                  ></b-form-input>
                  <div class="invalid-feedback">
                    {{ errors[0] }}
                  </div>
                </b-form-group>
              </ValidationProvider>
            </b-col>
            <b-col lg="4" md="4" sm="12" xs="12">
              <ValidationProvider name="Receiving Box Number" vid="receiving_box_number" rules="required" v-slot="{ errors }">
                <b-form-group label-for="receiving_box_number">
                <template v-slot:label>
                  Receiving Box Number <span class="text-danger">*</span>
                </template>
                  <b-form-input
                    id="receiving_box_number"
                    v-model="editItem.receiving_box_number"
                    placeholder="Enter Receiving Box Number"
                    :state="errors[0] ? false : (valid ? true : null)"
                  ></b-form-input>
                  <div class="invalid-feedback">
                    {{ errors[0] }}
                  </div>
                </b-form-group>
              </ValidationProvider>
            </b-col>
            <b-col lg="4" md="4" sm="12" xs="12">
              <ValidationProvider name="Receive Date" vid="receive_date" rules="required" v-slot="{ errors }">
                <b-form-group
                  label-for="receive_date">
                  <template v-slot:label>
                    Receive Date <span class="text-danger">*</span>
                  </template>
                  <flat-pickr
                    id="receive_date"
                    v-model="editItem.receive_date"
                    class="form-control"
                    placeholder="Select Receive Date"
                    :state="errors[0] ? false : (valid ? true : null)"
                    :config="flatPickrConfig"
                  />
                  <div class="d-block invalid-feedback">
                    {{ errors[0] }}
                  </div>
                </b-form-group>
              </ValidationProvider>
            </b-col>
            <b-col lg="4" md="4" sm="12" xs="12">
              <ValidationProvider name="Entry Box Number" vid="entry_box_number" rules="required" v-slot="{ errors }">
                <b-form-group
                  id="entry_box_number"
                  label-for="entry_box_number"
                >
                <template v-slot:label>
                  Entry Box Number <span class="text-danger">*</span>
                </template>
                  <b-form-input
                    id="entry_box_number"
                    v-model="editItem.entry_box_number"
                    placeholder="Enter Entry Box Number"
                    :state="errors[0] ? false : (valid ? true : null)"
                  ></b-form-input>
                  <div class="invalid-feedback">
                    {{ errors[0] }}
                  </div>
                </b-form-group>
              </ValidationProvider>
            </b-col>
            <b-col lg="4" md="4" sm="12" xs="12">
              <ValidationProvider name="Reference Number" vid="reference_number" rules="required" v-slot="{ errors }">
                <b-form-group
                  id="reference_number"
                  label-for="reference_number"
                >
                <template v-slot:label>
                  Reference Number <span class="text-danger">*</span>
                </template>
                  <b-form-input
                    id="reference_number"
                    v-model="editItem.reference_number"
                    placeholder="Enter Reference Number"
                    :state="errors[0] ? false : (valid ? true : null)"
                  ></b-form-input>
                  <div class="invalid-feedback">
                    {{ errors[0] }}
                  </div>
                </b-form-group>
              </ValidationProvider>
            </b-col>
          </b-row>
          <div class="row mt-3">
            <div class="col-sm-3"></div>
            <div class="col text-right">
                <b-button type="submit" variant="primary" class="mr-2">Update</b-button>
                &nbsp;
                <b-button variant="danger" class="mr-1" @click="$bvModal.hide('modal-1')">Cancel</b-button>
            </div>
          </div>
        </b-form>
      </ValidationObserver>
    </b-modal>
  </div>
</template>

<script>
import RestApi, { baseURL } from '@/config'
import swal from 'bootstrap-sweetalert'
import vue2Dropzone from 'vue2-dropzone'
import 'vue2-dropzone/dist/vue2Dropzone.min.css'
// import ImportEditForm from './ImportEditForm.vue'

import flatPickr from 'vue-flatpickr-component'
import 'flatpickr/dist/flatpickr.css'

export default {
  components: {
    vueDropzone: vue2Dropzone,
    // ImportEditForm,
    flatPickr
  },
  data () {
    return {
      baseUrl: baseURL,
      excelFileImported: false,
      dropzone_configs: {},
      listData: [],
      importedList: [],
      loading: false,
      editItem: {},
      errors: [],
      valid: null,
      flatPickrConfig: {
        dateFormat: 'd-m-Y'
      }
    }
  },
  created () {
    this.dropzoneOptions()
  },
  mounted () {
  },
  methods: {
    dropzoneOptions: function () {
      var ref = this
      ref.dropzone_configs = {
        url: baseURL + 'api/v1/admin/ajax/multiple_dl_info_excel_file_import',
        thumbnailWidth: 200,
        addRemoveLinks: true,
        multipleUpload: false,
        maxFiles: 1,
        acceptedFiles: '.xls,.xlsx',
        headers: {
          Authorization: 'Bearer ' + localStorage.getItem('access_token'),
          'Access-Control-Allow-Origin': '*'
          // 'Content-Type': 'multipart/form-data'
          // Accept: 'application/json'
        }
      }
    },
    sendingEvent (file, xhr, formData) {
      // formData.append('user_id', 5)
    },
    vsuccess: function (file, response) {
      this.importedList = response.data.imported_list.map((item, index) => {
        item.index = index
        return Object.assign(item)
      })
      this.$refs.dl_list_upload_dropzone.removeAllFiles()
      this.excelFileImported = true
      console.log('this.importedList', this.importedList)
      // this.check_duplicate_exists()
    },
    verror: function (file, response) {
      swal(response.msg, '', 'danger')
    },
    clearDropzone: function () {
      this.$refs.dl_list_upload_dropzone.removeAllFiles()
    },
    remove_and_re_upload: function () {
      this.$swal({
        title: 'Are you sure you want to remove the data?',
        text: 'Press Yes to Confirm or Cancel',
        type: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes',
        cancelButtonText: 'No',
        focusConfirm: false
      }).then((result) => {
        if (result.isConfirmed) {
          this.$refs.dl_list_upload_dropzone.removeAllFiles()
          this.excelFileImported = false
        }
      })
    },
    deleteItem: function (index) {
      this.$swal({
        title: 'Are you sure?',
        text: 'Press Yes to confirm or Cancel',
        type: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes',
        cancelButtonText: 'No',
        focusConfirm: false
      }).then((result) => {
        if (result.isConfirmed) {
          this.importedList.splice(index, 1)
        }
      })
    },
    editData: function (item) {
      this.editItem = JSON.parse(JSON.stringify(item))
      this.$refs.editModal.show()
    },
    async submitData () {
      this.loading = true

      const objIndex = this.importedList.findIndex(item => item.index === this.editItem.index)
      this.importedList[objIndex].reference_number = this.editItem.reference_number
      this.importedList[objIndex].serial_number = this.editItem.serial_number
      this.importedList[objIndex].entry_box_number = this.editItem.entry_box_number
      this.importedList[objIndex].receiving_box_number = this.editItem.receiving_box_number
      this.importedList[objIndex].delivery_date = this.editItem.delivery_date
      this.importedList[objIndex].comment = this.editItem.comment
      this.importedList[objIndex].is_duplicate = false

      this.$bvModal.hide('modal-1')
      // const result = await RestApi.postData(baseURL, 'api/v1/admin/ajax/check_duplicate_reference_number', this.editItem)
      this.loading = false
      // if (result.success) {
      //   const objIndex = this.importedList.findIndex(item => item.index === this.editItem.index)
      //   this.importedList[objIndex].reference_number = this.editItem.reference_number
      //   this.importedList[objIndex].serial_number = this.editItem.serial_number
      //   this.importedList[objIndex].entry_box_number = this.editItem.entry_box_number
      //   this.importedList[objIndex].receiving_box_number = this.editItem.receiving_box_number
      //   this.importedList[objIndex].delivery_date = this.editItem.delivery_date
      //   this.importedList[objIndex].comment = this.editItem.comment
      //   this.importedList[objIndex].is_duplicate = false

      //   this.$bvModal.hide('modal-1')
      // } else {
      //   this.$refs.form.setErrors(result.errors)
      // }
    },
    processToUploadData: async function () {
      this.loading = true
      this.importedList.map(item => {
        item.reference_number = item.reference_number ? item.reference_number : null
        item.delivery_date = item.delivery_date ? item.delivery_date : null
        item.comment = item.comment ? item.comment : null
        return Object.assign(item)
      })
      console.log('this.importedList', this.importedList)
      const result = await RestApi.postData(baseURL, 'api/v1/admin/ajax/multiple_dl_stock_store', { list: this.importedList })
      this.loading = false
      if (result.success) {
        // this.importedList = []
        // this.$refs.dl_list_upload_dropzone.removeAllFiles()
        this.excelFileImported = false
        this.$toast.success({
          title: 'Success',
          message: result.message
        })
      } else {
        this.$toast.error({
          title: 'Validation Error',
          message: result.errors
        })
      }
    },
    processToUploadData2: async function (data) {
      const isDuplicate = data.find(item => item.is_duplicate)

      if (isDuplicate) {
        this.$toast.error({
          title: 'Failed',
          message: 'Please remove or edit duplicate reference before process'
        })
      } else {
        this.loading = true
        const result = await RestApi.postData(baseURL, 'api/v1/admin/ajax/mulitiple_dl_stock_store', { data })
        this.loading = false
        if (result.success) {
          this.importedList = []
          this.$refs.dl_list_upload_dropzone.removeAllFiles()
          this.excelFileImported = false
          this.$toast.success({
            title: 'Success',
            message: result.message
          })
        } else {
          this.$toast.error({
            title: 'Validation Error',
            message: result.errors
          })
        }
      }
    }
  }
}
</script>
