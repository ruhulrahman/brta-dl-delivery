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
                      id="box_number"
                      label="Box Number"
                      label-for="box_number"
                  >
                      <b-form-input
                      id="box_number"
                      v-model="search.box_number"
                      type="text"
                      placeholder="Enter Box Number"
                      required
                      ></b-form-input>
                  </b-form-group>
                </b-col>
                <b-col sm="12" md="3">
                  <b-form-group
                      id="stock_date"
                      label="Stock Date"
                      label-for="stock_date"
                  >
                      <flat-pickr
                        id="stock_date"
                        v-model="search.stock_date"
                        class="form-control"
                        placeholder="Select Stock Date"
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
                  <br>
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
          <h4 class="card-title mb-0 pl-0">Dl Stock List By Search</h4>
        </b-col>
        <b-col class="text-right">
          <b-button v-if="has_permission('add_new_subscription_plan')" size="sm" variant="info" @click="openAddNewModal()">Add New<i class="ri-add-fill"></i></b-button>
        </b-col>
      </b-row>
    </b-card-title>
    <b-row>
      <b-col>
        <b-overlay :show="loading">
          <b-card>
            <div class="table-wrapper table-responsive">
              <table class="table table-striped table-hover table-bordered">
                <thead>
                  <tr style="font-size: 13px;">
                    <th scope="col" class="text-center">SL</th>
                    <th scope="col" class="text-center">Reference</th>
                    <th scope="col" class="text-center">Box Number</th>
                    <!-- <th scope="col" class="text-center">Stock Date</th> -->
                    <th scope="col" class="text-center">Delivery Date</th>
                    <th scope="col" class="text-center">Comment</th>
                    <th scope="col" class="text-center">Action</th>
                  </tr>
                </thead>
                <tbody v-for="(item, index) in listData" :key="index">
                  <tr style="font-size: 12px;">
                    <td scope="row" class="text-center">{{ index + pagination.slOffset }}</td>
                    <td class="text-center">{{ item.reference_number }}</td>
                    <td class="text-center">{{ item.box_number }}</td>
                    <!-- <td class="text-center"><span v-if="item.stock_date" v-html="dDate(item.stock_date)"></span></td> -->
                    <td class="text-center">
                      <span v-if="item.delivery_date" v-html="dDate(item.delivery_date)"></span>
                      <b-form-checkbox v-else @change="deliverDrivingLicense(item)" v-model="item.active" name="check-button" switch>
                      </b-form-checkbox>
                    </td>
                    <td class="text-center">{{ item.comment }}</td>
                    <td class="text-center">
                      <a v-tooltip="'Edit'" v-if="has_permission('edit_subscription_plan')" style="width: 20px !important; height: 20px !important; font-size:10px" href="javascript:" class="action-btn edit" @click="editData(item)"><i class="ri-pencil-fill"></i></a>
                      <a v-tooltip="'Delete'" v-if="has_permission('delete_subscription_plan')" @click="deleteConfirmation(item)" style="width: 20px !important; height: 20px !important; font-size:10px" href="javascript:" class="action-btn delete"><i class="ri-delete-bin-2-line"></i></a>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </b-card>
        </b-overlay>
      </b-col>
    </b-row>
  </b-card>
    <b-modal id="modal-1" ref="editModal" size="lg" title="DL Stock" centered :hide-footer="true">
      <Form @loadList="loadData" :editItem="editItem"/>
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
import RestApi, { baseURL } from '@/config'

import flatPickr from 'vue-flatpickr-component'
import 'flatpickr/dist/flatpickr.css'

export default {
  components: {
    Form,
    flatPickr
  },
  data () {
    return {
      // pagination
      rows: 100,
      currentPage: 1,
      // form data
      search: {
        reference_number: '',
        dl_number: '',
        box_number: '',
        name: '',
        father_name: '',
        dob: '',
        stock_date: '',
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
  methods: {
    openAddNewModal () {
      this.editItem = ''
      this.$refs.editModal.show()
    },
    editData (item) {
      this.editItem = JSON.stringify(item)
      this.$refs.editModal.show()
    },
    searchData () {
      this.loadData()
    },
    clearData () {
      this.search = {
        reference_number: '',
        dl_number: '',
        box_number: '',
        name: '',
        father_name: '',
        dob: '',
        stock_date: '',
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
