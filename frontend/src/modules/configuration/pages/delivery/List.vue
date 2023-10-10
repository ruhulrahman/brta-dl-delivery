<template>
  <div class="section-wrapper">
    <!-- <div class="breadcrumb-wrapper">
      <b-breadcrumb class="custom-bread"></b-breadcrumb>
    </div> -->
    <b-breadcrumb>
      <b-breadcrumb-item to="/dashboard">
        <b-icon icon="house-fill" scale="1.25" shift-v="1.25" aria-hidden="true"></b-icon>
        Dashboard
      </b-breadcrumb-item>
      <b-breadcrumb-item active>Delivery</b-breadcrumb-item>
    </b-breadcrumb>
      <div class="form-wrapper">
      <b-card title="DL Search Criteria">
        <form @submit.prevent="searchData">
          <b-card-text>
              <b-row style="font-size: 13px;">
                <b-col sm="12" md="3">
                  <b-form-group
                      id="reference_number"
                      label="Reference"
                      label-for="reference_number"
                  >
                      <b-form-input
                      id="reference_number"
                      v-model="search.reference_number"
                      type="text"
                      placeholder="Enter Reference"
                      ></b-form-input>
                  </b-form-group>
                </b-col>
                <b-col sm="12" md="3">
                  <b-form-group
                      id="entry_box_number"
                      label="Box Number"
                      label-for="entry_box_number"
                  >
                      <b-form-input
                      id="entry_box_number"
                      v-model="search.entry_box_number"
                      type="text"
                      placeholder="Enter Box Number"
                      ></b-form-input>
                  </b-form-group>
                </b-col>
                <b-col sm="12" md="3">
                  <b-form-group
                      id="receive_date"
                      label="Receive Date"
                      label-for="receive_date"
                  >
                      <flat-pickr
                        id="receive_date"
                        v-model="search.receive_date"
                        class="form-control"
                        placeholder="Select Receive Date"
                        :config="flatPickrConfig"
                      />
                  </b-form-group>
                </b-col>
                <b-col sm="12" md="3">
                  <b-form-group
                      id="delivery_date"
                      label="Delivery Date"
                      label-for="delivery_date"
                  >
                      <flat-pickr
                        id="delivery_date"
                        v-model="search.delivery_date"
                        class="form-control"
                        placeholder="Select Delivery Date"
                        :config="flatPickrConfig"
                      />
                  </b-form-group>
                </b-col>
                <b-col sm="12" md="3">
                  <b-form-group
                      id="delivered_id"
                      label="Delivered By"
                      label-for="delivered_id"
                  >
                  <v-select
                  id="delivered_id"
                  v-model="search.delivered_id"
                  :options="userList"
                  :reduce="item => item.value"
                  placeholder="Select User"
                  ></v-select>
                  </b-form-group>
                </b-col>
                <b-col class="text-left mt-3" sm="12" md="3">
                  <b-button type="submit" size="sm" variant="primary" @click="searchData"><i class="ri-search-line"></i> Search</b-button>
                  <b-button size="sm ml-1" variant="danger" @click="clearData"><i class="ri-close-line"></i> Clear</b-button>
                </b-col>
              </b-row>
          </b-card-text>
        </form>
      </b-card>
  </div>
  <b-card class="mt-3">
    <b-card-title>
      <b-row>
        <b-col>
          <h4 class="card-title mb-0 pl-0">Total <span class="badge badge-success">{{ totalRowCount }}</span> Dl Stock Found By Search</h4>
        </b-col>
        <b-col class="text-right">
          <router-link to="/import-dl" class="btn btn-success btn-sm mr-2">Import DL <i class="ri-upload-line"></i></router-link>
          <b-button v-if="has_permission('add_new_subscription_plan')" size="sm" variant="info" @click="openAddNewModal()">Add New<i class="ri-add-fill"></i></b-button>
        </b-col>
      </b-row>
    </b-card-title>
    <b-overlay :show="loading">
      <b-card>
        <!-- <h6 class="mb-0 mt-4 ml-2">Total 4444</h6> -->
        <div class="table-wrapper table-responsive pt-0 mt-0">
          <table class="table table-striped table-hover table-bordered">
            <thead>
              <tr style="font-size: 13px;">
                <th scope="col" class="text-center">Reference</th>
                <th scope="col" class="text-center">Box Number</th>
                <th scope="col" class="text-center">Serial Number</th>
                <!-- <th scope="col" class="text-center">Receive Date</th> -->
                <th scope="col" class="text-center">Delivery Date</th>
                <th scope="col" class="text-center">Comment</th>
                <th scope="col" class="text-center">Action</th>
              </tr>
            </thead>
            <tbody v-for="(item, index) in listData" :key="index">
              <tr style="font-size: 12px;">
                <!-- <td scope="row" class="text-center">{{ index + pagination.slOffset }}</td> -->
                <td class="text-center">{{ item.reference_number }}</td>
                <td class="text-center">{{ item.entry_box_number }}</td>
                <td scope="row" class="text-center">{{ item.serial_number }}</td>
                <!-- <td class="text-center"><span v-if="item.receive_date" v-html="dDate(item.receive_date)"></span></td> -->
                <td class="text-center">
                  <div v-if="item.delivery_date">
                    <b v-html="dDate(item.delivery_date)"></b><br/>
                    Delivered By <span class="badge badge-primary badge-pill" v-if="item.delivered" v-html="item.delivered.name"></span>
                  </div>
                  <b-form-checkbox v-tooltip="'Click to Deliver'" v-else @change="deliverDrivingLicense(item)" v-model="item.active" name="check-button" switch>
                  </b-form-checkbox>
                </td>
                <td class="text-center">{{ item.comment }}</td>
                <td class="text-center">
                  <a v-tooltip="'View'" v-if="has_permission('edit_subscription_plan')" style="width: 20px !important; height: 19px !important; font-size: 10px;" href="javascript:" class="action-btn active" @click="viewDetails(item)"><i class="ri-eye-fill"></i></a>
                  <a v-tooltip="'Edit'" v-if="has_permission('edit_subscription_plan')" style="width: 20px !important; height: 20px !important; font-size:10px" href="javascript:" class="action-btn edit" @click="editData(item)"><i class="ri-pencil-fill"></i></a>
                  <a v-tooltip="'Delete'" v-if="has_permission('delete_subscription_plan')" @click="deleteData(item)" style="width: 20px !important; height: 20px !important; font-size:10px" href="javascript:" class="action-btn delete"><i class="ri-delete-bin-2-line"></i></a>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </b-card>
    </b-overlay>
  </b-card>
    <b-modal id="modal-1" ref="editModal" size="xl" title="DL Stock" centered :hide-footer="true">
      <Form @loadList="loadData" :editItem="editItem"/>
    </b-modal>
    <b-modal id="modal-1" ref="detailsModal" size="lg" title="DL Details Info" centered :hide-footer="true">
      <Details @loadList="loadData" :editItem="editItem"/>
    </b-modal>
    <!-- pagination -->
    <div class="pagination-wrapper mt-4">
      <span>Showing {{ pagination.slOffset }} from {{ pagination.totalRows }} entries</span>
      <b-pagination
        size="sm"
        v-model="pagination.currentPage"
        :per-page="pagination.perPage"
        :total-rows="pagination.totalRows"
        @input="searchData"
        />
    </div>
  </div>
</template>

<script>
import Form from './Form.vue'
import Details from './Details.vue'
import RestApi, { baseURL } from '@/config'

import flatPickr from 'vue-flatpickr-component'
import 'flatpickr/dist/flatpickr.css'

export default {
  components: {
    Form,
    Details,
    flatPickr
  },
  data () {
    return {
      totalRowCount: 0,
      userList: [],
      search: {
        reference_number: '',
        dl_number: '',
        entry_box_number: '',
        name: '',
        father_name: '',
        dob: '',
        receive_date: '',
        delivery_date: '',
        delivered_id: ''
      },
      value: '',
      listData: [],
      loading: false,
      editItem: '',
      flatPickrConfig: {
        dateFormat: 'd-m-Y'
      }
    }
  },
  created () {
    this.loadData()
  },
  mounted () {
    this.getActiveUserList()
  },
  methods: {
    openAddNewModal () {
      this.editItem = ''
      this.$refs.editModal.show()
    },
    editData (item) {
      this.editItem = JSON.stringify(item)
      this.$refs.editModal.show()
    },
    viewDetails (item) {
      this.editItem = JSON.stringify(item)
      this.$refs.detailsModal.show()
    },
    searchData () {
      this.loadData()
    },
    clearData () {
      this.search = {
        reference_number: '',
        dl_number: '',
        entry_box_number: '',
        name: '',
        father_name: '',
        dob: '',
        receive_date: '',
        delivery_date: '',
        delivered_id: ''
      }
      this.loadData()
    },
    async loadData () {
      this.loading = true
      const params = Object.assign({}, this.search, { page: this.pagination.currentPage, per_page: this.pagination.perPage })
      var result = await RestApi.getData(baseURL, 'api/v1/admin/ajax/get_dl_stock_data_by_search', params)
      if (result.success) {
        this.listData = result.data.data
        this.totalRowCount = result.data.total
        this.paginationData(result.data)
      }
      this.loading = false
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
    async getActiveUserList () {
      var result = await RestApi.getData(baseURL, 'api/v1/admin/ajax/get_total_active_user_list')
      if (result.success) {
        this.userList = result.data
      }
    },
    deleteData (item) {
      this.$swal({
        title: 'Are you sure to delete?',
        showCancelButton: true,
        confirmButtonText: 'Yes',
        cancelButtonText: 'No',
        focusConfirm: false
      }).then((result) => {
        if (result.isConfirmed) {
          // declare confirmed method to hit api
          this.deleteConfirmation(item)
        }
      })
    },
    async deleteConfirmation (item) {
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
