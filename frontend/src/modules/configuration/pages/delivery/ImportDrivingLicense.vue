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
                                            <p class="card-text">1.<a :href="baseUrl + 'static/uni_agent_student_upload_file_sample_v4.xlsx'"> Click here to download the sample excel file</a>.</p>
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
                                                    <h1 class="dropzone-custom-title text-primary">Click to upload.</h1>
                                                    <h2 class="dropzone-custom-title text-primary">Maximum 500 rows</h2>
                                                </div>
                                            </vue-dropzone>
                                      </b-col>
                                    </b-row>
                                </div>
                            </div>
                            <div class="card wait_me_to_process" v-show="excelFileImported">
                                <!-- <div class="card-header">
                                    <h4 class="card-title">Email already exist</h4>
                                </div> -->
                                <div class="card-body">
                                    <!-- <div v-for="(item, index) in importedList" :key="index">
                                        <p><span class="badge badge-dark" v-if="item.duplicate_item == 1">{{ item.email }}</span></p>
                                    </div> -->
                                    <button @click="remove_and_re_upload()" class="btn btn-danger mb-2">Remove All Data</button>

                                    <button @click="processToUploadData(importedList)" class="btn btn-success mb-2 pull-right">Process To Upload Data</button>

                                    <div class="table-responsive">
                                        <table class="table table-hover" id="uni_agent_multiple_student_upload">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Banner Code</th>
                                                    <th>Student ID</th>
                                                    <th>First Name</th>
                                                    <th>Last Name</th>
                                                    <!-- <th>DOB</th> -->
                                                    <th>Nationality</th>
                                                    <!-- <th>Subject Name</th> -->
                                                    <!-- <th>Course Level</th> -->
                                                    <!-- <th class="text-center">Intake Month</th> -->
                                                    <!-- <th>Tuition Fee</th> -->
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="(item, index) in importedList" :key="index">
                                                    <td v-html="index + 1"></td>
                                                    <td>
                                                        <div @click="editData(item)">
                                                            <span v-html="item.agent_banner_code" class="mr-1"></span>
                                                            <i v-if="!item.match_agent_banner_code || item.match_agent_banner_code=='0'" class="fa fa-exclamation-triangle text-warning" v-tooltip="'Banner code not match'"></i><br/>
                                                            <small v-show="item.agent_company_name" v-html="item.agent_company_name" class="mr-1"></small>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span @click="editData(item)" v-html="item.student_id" class="mr-1"></span>
                                                        <i v-if="item.duplicate_student_id == 1" class="fa fa-exclamation-triangle text-warning" v-tooltip="'Duplicate entry'"></i>

                                                        <i v-if="item.invalid_student_id" class="fa fa-exclamation-triangle text-warning" v-tooltip="'Invalid Student ID'"></i>

                                                    </td>
                                                    <td v-html="item.first_name"></td>
                                                    <td v-html="item.last_name"></td>
                                                    <!-- <td>
                                                        <span v-html="item.email"></span>
                                                        <i v-if="item.duplicate_item == 1" title="This email already exist!" class="fa fa-clone text-danger ml-1" aria-hidden="true"></i>
                                                    </td> -->
                                                    <!-- <td>
                                                        <span v-if="item.dob" v-html="dDate(item.dob)"></span>
                                                    </td> -->
                                                    <td v-html="item.nationality"></td>
                                                    <!-- <td v-html="item.subject"></td> -->
                                                    <!-- <td v-html="item.course_level"></td> -->
                                                    <!-- <td v-html="dDate(item.intake_month, 'MMM  YYYY')"></td> -->
                                                    <!-- <td class="text-center" v-html="dDate(item.intake_month+'/01/'+item.intake_year, 'MMM  YYYY')"></td> -->
                                                    <!-- <td v-html="item.tuition_fee"></td> -->
                                                    <td>
                                                        <button @click="editData(item)" class="btn btn-outline-info btn-sm mr-1">
                                                            <i v-tooltip="'Edit'" class="fa fa-pencil" aria-hidden="true"></i>
                                                        </button>
                                                        <button @click="deleteItem(index)" class="btn btn-outline-danger btn-sm">
                                                            <i v-tooltip="'Delete'" class="fa fa-trash" aria-hidden="true"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <!-- <button @click="processToUploadData(importedList)" class="btn btn-success mb-2 pull-right">Process To Upload Data</button> -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </section>
            </div>
      </b-card>
    </b-overlay>
  </div>
</template>

<script>
import RestApi, { baseURL } from '@/config'
import swal from 'bootstrap-sweetalert'
import vue2Dropzone from 'vue2-dropzone'
import 'vue2-dropzone/dist/vue2Dropzone.min.css'

// import flatPickr from 'vue-flatpickr-component'
// import 'flatpickr/dist/flatpickr.css'

export default {
  components: {
    vueDropzone: vue2Dropzone
    // flatPickr
  },
  data () {
    return {
      baseUrl: baseURL,
      excelFileImported: false,
      dropzone_configs: {},
      listData: [],
      importedList: [],
      loading: false,
      editItem: '',
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
        url: baseURL + 'api/v1/admin/ajax/multiple_uni_agent_student_excel_file_import',
        thumbnailWidth: 200,
        addRemoveLinks: true,
        multipleUpload: false,
        maxFiles: 1,
        acceptedFiles: '.xls,.xlsx',
        headers: {
          Authorization: 'Bearer ' + localStorage.getItem('api_token'),
          'Access-Control-Allow-Origin': '*',
          Accept: 'application/json'
        }
      }
    },
    sendingEvent (file, xhr, formData) {
      // formData.append('intake_id', this.hash_id(this.$route.params.intake_id, false)[0])
    },
    vsuccess: function (file, response) {
      this.importedList = response.data.imported_list.map((item, index) => {
        const mapData = {
          index: index
        }
        return Object.assign({}, item, mapData)
      })
      this.$refs.dl_list_upload_dropzone.removeAllFiles()
      this.excelFileImported = true
      this.check_duplicate_exists()
    },
    verror: function (file, response) {
      swal(response.msg, '', 'danger')
    },
    clearDropzone: function () {
      this.$refs.dl_list_upload_dropzone.removeAllFiles()
    },
    check_duplicate_exists: async function () {
      var ref = this
      var jq = ref.jq()
      var url = ref.url('api/v1/admin/ajax/uni_as_upload_unique_check')

      const formData = {
        list: ref.importedList.map(item => {
          return {
            match_agent_banner_code: item.match_agent_banner_code,
            student_id: item.student_id,
            duplicate_student_id: item.duplicate_student_id
          }
        }),
        intake_id: ref.hash_id(ref.$route.params.intake_id, false)[0]
      }

      try {
        const res = await jq.post(url, formData)
        ref.importedList = ref.importedList.map(item => {
          const studentObj = res.data.importedList.find(student => student.student_id === item.student_id)

          if (studentObj) {
            item.duplicate_student_id = studentObj.duplicate_student_id
            item.invalid_student_id = studentObj.invalid_student_id
          }

          return Object.assign(item)
        })
        console.log(ref.importedList)
      } catch (error) {
        console.log(error)
      }
    },
    async deliverDrivingLicense (item) {
      this.loading = true
      var result = await RestApi.postData(baseURL, 'api/v1/admin/ajax/deliver_dl_stock_data', item)
      if (result.success) {
        this.$toast.success({ title: 'Success', message: result.message })
        this.loadData()
      }
      this.loading = false
    },
    deleteConfirmation (item) {
      this.$swal({
        title: 'Are you sure to delete?',
        showCancelButton: true,
        confirmButtonText: 'Yes',
        cancelButtonText: 'No',
        focusConfirm: false
      }).then((result) => {
        if (result.isConfirmed) {
          // declare confirmed method to hit api
          this.deleteData(item)
        }
      })
    },
    async deleteData (item) {
      this.loading = true
      var result = await RestApi.postData(baseURL, 'api/v1/admin/ajax/delete_dl_stock_data', item)
      if (result.success) {
        this.$toast.success({
          title: 'Success',
          message: result.message
        })
        this.loadData()
      }
      this.loading = false
    }
  }
}
</script>
